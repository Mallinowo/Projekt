<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\UserMatch;
use App\Services\GifService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    public function __construct(private readonly GifService $gifService)
    {
    }

    public function index()
    {
        $user = Auth::user();

        $matches = UserMatch::with(['user1.photos', 'user2.photos', 'messages'])
            ->where('user1_id', $user->id)
            ->orWhere('user2_id', $user->id)
            ->latest()
            ->get()
            ->map(function ($match) use ($user) {
                $other = $match->getOtherUser($user->id);
                $last = $match->getLastMessage();
                $lastPreview = $last?->type === 'gif' ? 'GIF' : $last?->body;

                return [
                    'match_id' => $match->id,
                    'user_id' => $other->id,
                    'name' => $other->name,
                    'avatar' => $other->getAvatarUrl(),
                    'last_msg' => $lastPreview,
                    'last_time' => $last?->created_at?->format('H:i'),
                    'unread' => $match->getUnreadCount($user->id),
                ];
            });

        return view('chat.index', compact('matches'));
    }

    public function messages(Request $request, int $matchId): JsonResponse
    {
        $user = Auth::user();
        $match = $this->authorizeMatch($matchId, $user->id);
        $sinceId = max(0, (int) $request->query('since_id', 0));

        $match->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messagesQuery = $match->messages()->with(['sender', 'reactions']);
        if ($sinceId > 0) {
            $messagesQuery->where('id', '>', $sinceId);
        }

        $messages = $messagesQuery->get()->map(fn($m) => [
            'id' => $m->id,
            'type' => $m->type ?? 'text',
            'body' => $m->body,
            'gif_url' => $m->gif_url,
            'mine' => $m->sender_id === $user->id,
            'sender' => $m->sender->name,
            'avatar' => $m->sender->getAvatarUrl(),
            'time' => $m->created_at->format('H:i'),
            'is_read' => !is_null($m->read_at),
            'reactions' => $this->formatReactions($m->reactions, $user->id),
        ]);

        $lastReadOwnMessageId = (int) ($match->messages()
            ->where('sender_id', $user->id)
            ->whereNotNull('read_at')
            ->max('id') ?? 0);

        return response()->json([
            'messages' => $messages,
            'last_read_own_message_id' => $lastReadOwnMessageId,
        ]);
    }

    public function gifSearch(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        $limit = max(1, min(30, (int) $request->query('limit', 18)));

        return response()->json($this->gifService->search($q, $limit));
    }

    public function send(Request $request, int $matchId): JsonResponse
    {
        $user = Auth::user();
        $match = $this->authorizeMatch($matchId, $user->id);

        $data = $request->validate([
            'body' => 'nullable|string|max:1000',
            'type' => 'nullable|in:text,gif',
            'gif_url' => 'nullable|url|max:2048',
        ]);

        $type = $data['type'] ?? (!empty($data['gif_url']) ? 'gif' : 'text');
        $body = trim((string) ($data['body'] ?? ''));
        $gifUrl = $data['gif_url'] ?? null;

        if ($type === 'gif' && empty($gifUrl)) {
            return response()->json(['message' => 'GIF URL is required for GIF messages.'], 422);
        }

        if ($type === 'text' && $body === '') {
            return response()->json(['message' => 'Message body is required for text messages.'], 422);
        }

        $message = Message::create([
            'match_id' => $match->id,
            'sender_id' => $user->id,
            'type' => $type,
            'body' => $type === 'text' ? $body : null,
            'gif_url' => $type === 'gif' ? $gifUrl : null,
        ]);

        Log::info('Message sent', [
            'match_id' => $matchId,
            'sender' => $user->id,
            'type' => $type,
        ]);

        return response()->json([
            'id' => $message->id,
            'type' => $message->type,
            'body' => $message->body,
            'gif_url' => $message->gif_url,
            'mine' => true,
            'avatar' => $user->getAvatarUrl(),
            'time' => $message->created_at->format('H:i'),
            'reactions' => [],
        ]);
    }

    public function react(Request $request, int $matchId, int $messageId): JsonResponse
    {
        $user = Auth::user();
        $match = $this->authorizeMatch($matchId, $user->id);
        $data = $request->validate(['emoji' => 'required|string|max:16']);

        $emoji = trim($data['emoji']);
        if ($emoji === '') {
            return response()->json(['message' => 'Emoji is required.'], 422);
        }

        $message = $match->messages()->with('reactions')->where('id', $messageId)->firstOrFail();
        $existing = $message->reactions()->where('user_id', $user->id)->first();

        if ($existing && $existing->emoji === $emoji) {
            $existing->delete();
        } elseif ($existing) {
            $existing->update(['emoji' => $emoji]);
        } else {
            $message->reactions()->create([
                'user_id' => $user->id,
                'emoji' => $emoji,
            ]);
        }

        $message->load('reactions');

        return response()->json([
            'message_id' => $message->id,
            'reactions' => $this->formatReactions($message->reactions, $user->id),
        ]);
    }

    private function authorizeMatch(int $matchId, int $userId): UserMatch
    {
        return UserMatch::where('id', $matchId)
            ->where(function ($q) use ($userId) {
                $q->where('user1_id', $userId)->orWhere('user2_id', $userId);
            })
            ->firstOrFail();
    }

    private function formatReactions(Collection $reactions, int $userId): array
    {
        return $reactions
            ->groupBy('emoji')
            ->map(function (Collection $group, string $emoji) use ($userId) {
                return [
                    'emoji' => $emoji,
                    'count' => $group->count(),
                    'mine' => $group->contains('user_id', $userId),
                ];
            })
            ->sortByDesc('count')
            ->values()
            ->all();
    }
}
