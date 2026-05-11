@extends('layouts.app')
@section('title', __('nav.chat'))
@section('content')
<div class="flex h-full overflow-hidden">
    {{-- Sidebar --}}
    <div class="w-60 flex-shrink-0 bg-[#111118] border-r border-[#2a2a3a] flex flex-col">
        <div class="px-4 py-3 border-b border-[#2a2a3a] font-mono text-xs tracking-widest text-purple-400 uppercase flex-shrink-0">
            {{ __('nav.chat') }}
        </div>
        <div class="overflow-y-auto flex-1 scrollbar-thin" id="chatList">
            @forelse($matches as $m)
            <div class="flex items-center gap-3 px-4 py-3 border-b border-[#2a2a3a] cursor-pointer hover:bg-[#16161f] transition-colors chat-item {{ request('match')==$m['match_id']?'bg-[#16161f] border-l-2 border-l-purple-500':'' }}"
                 data-match="{{ $m['match_id'] }}"
                 data-name="{{ e($m['name']) }}"
                 data-avatar="{{ $m['avatar'] }}"
                 data-selected="{{ request('match')==$m['match_id'] ? '1' : '0' }}"
                 onclick="openChatFromSidebarItem(this)">
                <div class="w-10 h-10 rounded-full border-2 border-[#2a2a3a] flex-shrink-0 overflow-hidden flex items-center justify-center bg-gradient-to-br from-purple-900 to-blue-900">
                    @if($m['avatar'])<img src="{{ $m['avatar'] }}" class="w-full h-full object-cover">@else<span>🖤</span>@endif
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-1.5 min-w-0">
                        @if($m['unread'] > 0)
                        <span class="chat-unread-dot w-2 h-2 rounded-full bg-purple-500 flex-shrink-0"></span>
                        @endif
                        <div class="font-mono text-xs font-bold text-[#e2e0f0] truncate">{{ $m['name'] }}</div>
                    </div>
                    <div class="text-xs text-[#5e5880] truncate">{{ Str::limit($m['last_msg'] ?? __('chat.new_match'), 28) }}</div>
                </div>
            </div>
            @empty
            <div class="p-6 text-center text-[#5e5880] text-sm leading-loose">
                {{ __('chat.no_chats') }}<br>{{ __('chat.match_first') }}
            </div>
            @endforelse
        </div>
    </div>

    {{-- Chat main --}}
    <div class="flex-1 flex flex-col bg-[#0a0a0f] min-w-0" id="chatMain">
        <div class="flex-1 flex items-center justify-center flex-col gap-3 text-[#5e5880]">
            <span class="text-4xl">🖤</span>
            <span class="font-mono text-xs uppercase tracking-widest">{{ __('chat.select') }}</span>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<style>
@keyframes chatHeadIn {
    from { opacity: 0; transform: translateY(-8px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes chatBodyIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes chatComposerIn {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes msgInMine {
    from { opacity: 0; transform: translateX(16px) scale(.98); }
    to { opacity: 1; transform: translateX(0) scale(1); }
}
@keyframes msgInTheirs {
    from { opacity: 0; transform: translateX(-16px) scale(.98); }
    to { opacity: 1; transform: translateX(0) scale(1); }
}
.chat-head-enter { animation: chatHeadIn .22s ease-out both; }
.chat-body-enter { animation: chatBodyIn .24s ease-out both; }
.chat-composer-enter { animation: chatComposerIn .24s ease-out both; }
.msg-row.msg-enter.mine { animation: msgInMine .2s cubic-bezier(.22,.61,.36,1) both; }
.msg-row.msg-enter.theirs { animation: msgInTheirs .22s cubic-bezier(.22,.61,.36,1) both; }
@media (prefers-reduced-motion: reduce) {
    .chat-head-enter,
    .chat-body-enter,
    .chat-composer-enter,
    .msg-row.msg-enter.mine,
    .msg-row.msg-enter.theirs {
        animation: none !important;
    }
}
</style>
<script>
const CSRF = document.querySelector('meta[name=csrf-token]').content;
const MY_AVATAR = "{{ auth()->user()->getAvatarUrl() }}";
const MY_INITIAL = "{{ strtoupper(substr(auth()->user()->name,0,1)) }}";
const INITIAL_MATCH_ID = @json(request('match'));
const TRANS = {
    online: "{{ __('chat.online') }}",
    placeholder: "{{ __('chat.placeholder') }}",
    matched: "{{ __('chat.matched') }}",
    emoji: "{{ __('chat.emoji') }}",
    gif: "{{ __('chat.gif') }}",
    search_gif: "{{ __('chat.search_gif') }}",
    no_gifs: "{{ __('chat.no_gifs') }}",
    react: "{{ __('chat.react') }}",
    sent: "{{ __('chat.sent') }}",
    read: "{{ __('chat.read') }}",

};
</script>
<script src="{{ asset('js/chat.js') }}?v={{ filemtime(public_path('js/chat.js')) }}"></script>
@endpush

