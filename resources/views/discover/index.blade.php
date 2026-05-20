@extends('layouts.app')
@section('title', __('nav.discover'))
@section('content')
<div class="flex h-full overflow-hidden" id="vOdk">
    {{-- Sidebar - matches --}}
    <div class="w-52 flex-shrink-0 bg-[#111118] border-r border-[#2a2a3a] flex flex-col overflow-hidden">
        <div class="px-4 py-3 border-b border-[#2a2a3a] font-mono text-xs tracking-widest text-purple-400 uppercase flex items-center justify-between flex-shrink-0">
            {{ __('discover.matches') }}
            <span class="bg-purple-600 text-white rounded-full px-2 py-0.5 text-[.48rem]" id="sbCount">{{ count($matches) }}</span>
        </div>
        <div class="overflow-y-auto flex-1 scrollbar-thin" id="sbList">
            @forelse($matches as $m)
            <div class="sb-match-item flex items-center gap-2 px-3 py-2.5 border-b border-[#2a2a3a] cursor-pointer hover:bg-[#16161f] transition-colors"
                 data-match="{{ $m['match_id'] }}"
                 onclick="window.location='{{ route('chat', ['match' => $m['match_id']]) }}'">
                <div class="w-9 h-9 rounded-full border-2 border-[#2a2a3a] flex-shrink-0 overflow-hidden flex items-center justify-center bg-gradient-to-br from-purple-900 to-blue-900">
                    @if($m['avatar'])<img src="{{ $m['avatar'] }}" class="w-full h-full object-cover">@else<span class="text-sm">&#x1F5A4;</span>@endif
                </div>
                <div class="flex-1 min-w-0">
                    <div class="font-mono text-xs font-bold text-[#e2e0f0] truncate">{{ $m['name'] }}</div>
                    <div class="text-xs text-[#5e5880] truncate">{{ Str::limit($m['last_msg'] ?? __('discover.new_match'), 24) }}</div>
                </div>
            </div>
            @empty
            <div id="sbEmpty" class="p-4 text-center text-[#5e5880] text-sm leading-relaxed">{{ __('discover.no_matches') }}<br>{{ __('discover.swipe_hint') }}</div>
            @endforelse
        </div>
    </div>

    {{-- Main discover area --}}
    <div class="discover-main-stage flex-1 flex items-center justify-center p-4 overflow-hidden" style="background:radial-gradient(ellipse at 50% 0%,rgba(168,85,247,.05),transparent 60%)">
        <div class="max-w-5xl w-full">
            <div class="discover-filter-bar mb-4">
                <div class="discover-filter-head">
                    <span class="discover-filter-kicker">{{ __('discover.active_filters') }}</span>
                    <a href="{{ route('profile') }}#match-preferences" class="discover-filter-change">{{ __('discover.change_filters') }}</a>
                </div>
                <div class="discover-filter-grid">
                    <span class="discover-filter-chip discover-filter-chip-age">{{ __('discover.filter_age', ['min' => $discoverFilters['age_min'], 'max' => $discoverFilters['age_max']]) }}</span>
                    @if($discoverFilters['has_coords'])
                        <span class="discover-filter-chip discover-filter-chip-loc">{{ __('discover.filter_distance', ['km' => $discoverFilters['max_distance_km']]) }}</span>
                    @else
                        <span class="discover-filter-chip discover-filter-chip-warn">{{ __('discover.filter_distance_missing') }}</span>
                    @endif
                    <span class="discover-filter-chip discover-filter-chip-sub-label">{{ __('discover.filter_subcultures_label') }}</span>
                    @foreach($discoverFilters['subcultures'] as $subculture)
                        <span class="discover-filter-chip discover-filter-chip-sub">{{ Str::ucfirst((string) $subculture) }}</span>
                    @endforeach
                </div>
            </div>
            <div class="flex gap-6 items-start w-full">
            {{-- Card stack --}}
            <div class="relative w-96 h-[580px] flex-shrink-0" id="stack" style="height:650px">
                {{-- Cards rendered by JS --}}
                <div class="absolute bottom-0 left-0 right-0 flex justify-center gap-5 pb-1 z-50" id="actRow">
                    <button onclick="doSwipe('left')"  class="act-btn skip" title="{{ __('discover.skip') }} [Left]">&#x2715;<span class="act-lbl">{{ __('discover.skip') }}</span></button>
                    <button onclick="doSwipe('up')"    class="act-btn sup" title="{{ __('discover.super') }} [Up]">&#x2606;<span class="act-lbl">{{ __('discover.super') }}</span></button>
                    <button onclick="doSwipe('right')" class="act-btn like" title="{{ __('discover.like') }} [Right]">&#x2661;<span class="act-lbl">{{ __('discover.like') }}</span></button>
                </div>
            </div>

            {{-- Info panel --}}
            <div id="ipPanel" class="flex-1 bg-[#111118] border border-[#2a2a3a] rounded-xl overflow-hidden max-h-[540px] flex flex-col">
                <div class="px-5 py-4 border-b border-[#2a2a3a] bg-[#16161f] flex-shrink-0">
                    <div class="font-cinzel text-xl font-semibold" id="ipName">&mdash;</div>
                    <div class="flex gap-1 mt-1 flex-wrap" id="ipTags"></div>
                </div>
                <div class="overflow-y-auto flex-1 scrollbar-thin">
                    <div class="px-5 py-3 border-b border-[#2a2a3a]">
                        <div class="font-mono text-[.5rem] tracking-widest uppercase text-[#5e5880] mb-1">{{ __('discover.bio') }}</div>
                        <p class="text-sm text-[#9991bb] leading-relaxed" id="ipBio"></p>
                    </div>
                    <div class="px-5 py-3 border-b border-[#2a2a3a]">
                        <div class="font-mono text-[.5rem] tracking-widest uppercase text-[#5e5880] mb-2">{{ __('discover.interests') }}</div>
                        <div id="ipInt" class="flex flex-wrap gap-1"></div>
                    </div>
                    <div class="px-5 py-3">
                        <div class="font-mono text-[.5rem] tracking-widest uppercase text-[#5e5880] mb-2">{{ __('discover.artists') }}</div>
                        <div id="ipArt"></div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
</div>

{{-- Match popup --}}
<div id="matchPop" class="fixed inset-0 z-[400] hidden items-center justify-center bg-black/80 backdrop-blur-sm">
    <div class="match-card relative bg-[#111118] border border-[#2a2a3a] rounded-2xl p-10 text-center max-w-xs overflow-hidden">
        <div class="match-glow"></div>
        <div id="matchFx" class="pointer-events-none absolute inset-0 overflow-hidden"></div>
        <div class="relative z-10">
        <div class="match-badge text-4xl mb-2">&#x1F49C;</div>
        <h2 class="match-title font-cinzel text-2xl text-purple-400 mb-1">{{ __('discover.match_title') }}</h2>
        <div class="match-avatars flex justify-center gap-2 my-5">
            <div class="w-16 h-16 rounded-full border-2 border-purple-400 overflow-hidden flex items-center justify-center bg-gradient-to-br from-purple-900 to-blue-900" id="mpMyAv"></div>
            <div class="w-16 h-16 rounded-full border-2 border-purple-400 overflow-hidden flex items-center justify-center bg-gradient-to-br from-purple-900 to-blue-900" id="mpTheirAv"></div>
        </div>
        <p class="match-text text-sm text-[#9991bb] mb-6">{{ __('discover.match_text') }} <strong id="mpName"></strong>!</p>
        <div class="match-actions flex gap-3 justify-center">
            <button onclick="mpGoChat()" class="match-primary bg-gradient-to-r from-purple-500 to-pink-500 text-white px-5 py-2 rounded-lg font-mono text-xs uppercase tracking-widest">{{ __('discover.write_now') }}</button>
            <button onclick="mpClose()" class="match-secondary border border-[#2a2a3a] text-[#5e5880] px-5 py-2 rounded-lg font-mono text-xs uppercase tracking-widest hover:border-[#5e5880]">{{ __('discover.continue') }}</button>
        </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<style>
.discover-main-stage {
    position: relative;
    isolation: isolate;
}
.discover-filter-bar {
    position: relative;
    border: 1px solid #2f2746;
    border-radius: 16px;
    padding: 14px 16px 16px;
    background:
        linear-gradient(170deg, rgba(24, 21, 38, .96), rgba(14, 13, 25, .96)),
        radial-gradient(circle at 12% -40%, rgba(236, 72, 153, .13), transparent 55%);
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, .05),
        0 10px 24px rgba(0, 0, 0, .28);
}
.discover-filter-bar::after {
    content: "";
    position: absolute;
    inset: 0;
    border-radius: inherit;
    padding: 1px;
    background: linear-gradient(120deg, rgba(168, 85, 247, .48), rgba(236, 72, 153, .28), rgba(34, 211, 238, .22));
    -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
    pointer-events: none;
    opacity: .55;
}
.discover-filter-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}
.discover-filter-kicker {
    font-family: Space Mono, monospace;
    font-size: .76rem;
    letter-spacing: .24em;
    text-transform: uppercase;
    color: #a195cb;
}
.discover-filter-change {
    position: relative;
    z-index: 1;
    flex-shrink: 0;
    border: 1px solid rgba(168, 85, 247, .45);
    border-radius: 9999px;
    padding: 6px 12px;
    font-family: Space Mono, monospace;
    font-size: .62rem;
    font-weight: 700;
    letter-spacing: .08em;
    line-height: 1;
    text-transform: uppercase;
    color: #d8b4fe;
    background: rgba(168, 85, 247, .12);
    transition: border-color .18s ease, background-color .18s ease, color .18s ease;
}
.discover-filter-change:hover {
    border-color: rgba(216, 180, 254, .75);
    color: #f5e8ff;
    background: rgba(168, 85, 247, .2);
}
.discover-filter-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 10px;
}
.discover-filter-chip {
    display: inline-flex;
    align-items: center;
    border-radius: 9999px;
    border: 1px solid #322d4b;
    padding: 6px 11px;
    font-family: Space Mono, monospace;
    font-size: .63rem;
    font-weight: 700;
    letter-spacing: .04em;
    background: rgba(19, 18, 31, .88);
    color: #d3cdec;
    line-height: 1;
}
.discover-filter-chip-age {
    border-color: rgba(168, 85, 247, .42);
    color: #d8b4fe;
    background: rgba(168, 85, 247, .14);
}
.discover-filter-chip-loc {
    border-color: rgba(34, 211, 238, .4);
    color: #67e8f9;
    background: rgba(34, 211, 238, .12);
}
.discover-filter-chip-warn {
    border-color: rgba(239, 68, 68, .4);
    color: #fda4af;
    background: rgba(239, 68, 68, .1);
}
.discover-filter-chip-sub-label {
    border-color: rgba(236, 72, 153, .35);
    color: #f9a8d4;
}
.discover-filter-chip-sub {
    border-color: rgba(94, 88, 128, .5);
    color: #e2def3;
    background: rgba(28, 26, 43, .9);
}
.discover-main-stage::before {
    content: "";
    position: absolute;
    inset: 3% 14% auto 14%;
    height: 64%;
    border-radius: 9999px;
    background:
        radial-gradient(circle at 30% 22%, rgba(236, 72, 153, .13), transparent 48%),
        radial-gradient(circle at 70% 72%, rgba(168, 85, 247, .15), transparent 54%);
    filter: blur(28px);
    opacity: .72;
    pointer-events: none;
    z-index: -1;
    animation: discoverAura 8.6s ease-in-out infinite;
}

#stack .swipe-card.discover-card-enter {
    opacity: 0;
    animation: discoverCardIn .36s cubic-bezier(.22,.61,.36,1) var(--enter-delay, 0ms) both;
}
#stack .swipe-card.discover-card-enter .card-ph img {
    animation: discoverImageIn .5s ease-out var(--enter-delay, 0ms) both;
}

#ipPanel {
    transition: border-color .22s ease, box-shadow .22s ease;
}
#ipPanel.ip-panel-anim {
    animation: infoPanelPulse .28s ease-out;
}
#ipPanel.ip-panel-anim #ipName { animation: infoFadeUp .28s ease-out both; }
#ipPanel.ip-panel-anim #ipTags { animation: infoFadeUp .3s .04s ease-out both; }
#ipPanel.ip-panel-anim #ipBio { animation: infoFadeUp .28s .06s ease-out both; }
#ipPanel.ip-panel-anim #ipInt { animation: infoFadeUp .28s .09s ease-out both; }
#ipPanel.ip-panel-anim #ipArt { animation: infoFadeUp .28s .12s ease-out both; }

.act-btn.is-hit {
    animation: actBtnHit .28s cubic-bezier(.22,.61,.36,1);
}

#sbList .sb-match-item.sb-enter {
    animation: sbMatchIn .34s cubic-bezier(.22,.61,.36,1) both;
}

#stack .no-more-enter {
    animation: noMoreIn .38s cubic-bezier(.22,.61,.36,1) both;
}

#matchPop {
    opacity: 0;
    transition: opacity .22s ease;
}
#matchPop.is-open {
    opacity: 1;
}
#matchPop .match-card {
    opacity: 0;
    transform: translateY(22px) scale(.9);
}
#matchPop.is-open .match-card {
    animation: matchCardIn .46s cubic-bezier(.34,1.56,.64,1) forwards;
}
#matchPop .match-glow {
    position: absolute;
    inset: -30%;
    background:
        radial-gradient(circle at 30% 22%, rgba(236, 72, 153, .26), transparent 45%),
        radial-gradient(circle at 72% 78%, rgba(168, 85, 247, .32), transparent 52%);
    filter: blur(16px);
    opacity: .6;
}
#matchPop.is-open .match-glow {
    animation: matchGlowPulse 2.4s ease-in-out infinite;
}
#matchPop .match-badge,
#matchPop .match-title,
#matchPop .match-text,
#matchPop .match-primary,
#matchPop .match-secondary {
    opacity: 0;
}
#matchPop.is-open .match-badge { animation: matchBadgeIn .62s .05s both; }
#matchPop.is-open .match-title { animation: matchFadeUp .4s .12s both; }
#matchPop.is-open .match-text { animation: matchFadeUp .4s .2s both; }
#matchPop.is-open .match-primary { animation: matchFadeUp .36s .28s both; }
#matchPop.is-open .match-secondary { animation: matchFadeUp .36s .34s both; }
#matchPop #mpMyAv,
#matchPop #mpTheirAv {
    opacity: 0;
}
#matchPop.is-open #mpMyAv { animation: matchAvatarInL .38s .18s both; }
#matchPop.is-open #mpTheirAv { animation: matchAvatarInR .38s .24s both; }
#matchPop .mp-fx {
    position: absolute;
    left: 50%;
    bottom: -22px;
    font-size: var(--size, 14px);
    transform: translateX(var(--x, 0px));
    opacity: .85;
    text-shadow: 0 0 12px rgba(236,72,153,.45);
}
#matchPop .mp-fx.hearts {
    animation: matchParticle var(--dur, 1.3s) linear forwards;
}
@keyframes matchCardIn {
    from { opacity: 0; transform: translateY(22px) scale(.9); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
@keyframes matchGlowPulse {
    0%, 100% { transform: scale(1); opacity: .52; }
    50% { transform: scale(1.06); opacity: .78; }
}
@keyframes matchBadgeIn {
    0% { opacity: 0; transform: scale(.5) rotate(-18deg); }
    70% { opacity: 1; transform: scale(1.18) rotate(8deg); }
    100% { opacity: 1; transform: scale(1) rotate(0deg); }
}
@keyframes matchAvatarInL {
    from { opacity: 0; transform: translateX(-18px) scale(.86) rotate(-6deg); }
    to { opacity: 1; transform: translateX(0) scale(1) rotate(0); }
}
@keyframes matchAvatarInR {
    from { opacity: 0; transform: translateX(18px) scale(.86) rotate(6deg); }
    to { opacity: 1; transform: translateX(0) scale(1) rotate(0); }
}
@keyframes matchFadeUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes matchParticle {
    0% {
        opacity: 0;
        transform: translateX(var(--x, 0px)) translateY(8px) scale(.8) rotate(0deg);
    }
    20% { opacity: .95; }
    100% {
        opacity: 0;
        transform: translateX(calc(var(--x, 0px) + var(--drift, 0px))) translateY(-255px) scale(1.25) rotate(170deg);
    }
}
@keyframes discoverAura {
    0%, 100% { opacity: .66; transform: scale(1) translateY(0); }
    50% { opacity: .88; transform: scale(1.06) translateY(6px); }
}
@keyframes discoverCardIn {
    from { opacity: 0; transform: translateY(24px) scale(.92); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
@keyframes discoverImageIn {
    from { transform: scale(1.06); filter: saturate(.88) brightness(.9); }
    to { transform: scale(1); filter: saturate(1) brightness(1); }
}
@keyframes infoPanelPulse {
    0% { border-color: #2a2a3a; box-shadow: 0 0 0 rgba(168,85,247,0); }
    50% { border-color: #4c3a71; box-shadow: 0 0 22px rgba(168,85,247,.12); }
    100% { border-color: #2a2a3a; box-shadow: 0 0 0 rgba(168,85,247,0); }
}
@keyframes infoFadeUp {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes actBtnHit {
    0% { transform: scale(1); }
    50% { transform: scale(1.16); }
    100% { transform: scale(1); }
}
@keyframes sbMatchIn {
    from { opacity: 0; transform: translateX(-12px); }
    to { opacity: 1; transform: translateX(0); }
}
@keyframes noMoreIn {
    from { opacity: 0; transform: translate(-50%, -44%) scale(.94); }
    to { opacity: 1; transform: translate(-50%, -50%) scale(1); }
}
@media (prefers-reduced-motion: reduce) {
    .discover-main-stage::before,
    #stack .swipe-card.discover-card-enter,
    #stack .swipe-card.discover-card-enter .card-ph img,
    #ipPanel.ip-panel-anim,
    #ipPanel.ip-panel-anim #ipName,
    #ipPanel.ip-panel-anim #ipTags,
    #ipPanel.ip-panel-anim #ipBio,
    #ipPanel.ip-panel-anim #ipInt,
    #ipPanel.ip-panel-anim #ipArt,
    .act-btn.is-hit,
    #sbList .sb-match-item.sb-enter,
    #stack .no-more-enter,
    #matchPop,
    #matchPop * {
        animation: none !important;
        transition: none !important;
    }
}
@media (max-width: 640px) {
    .discover-filter-head {
        align-items: flex-start;
    }
    .discover-filter-kicker {
        font-size: .68rem;
        letter-spacing: .18em;
    }
    .discover-filter-change {
        padding: 6px 10px;
        font-size: .58rem;
    }
}
</style>
<script>
const PROFILES = @json($profiles);
const INITIAL_MATCHES = @json($matches);
const MATCH_UPDATES_URL = @json(route('discover.matches.updates'));
const MY_AVATAR = "{{ auth()->user()->getAvatarUrl() }}";
const MY_INITIAL = "{{ strtoupper(substr(auth()->user()->name,0,1)) }}";
const CSRF = document.querySelector('meta[name=csrf-token]').content;
const TRANS = {
    skip: "{{ __('discover.skip') }}",
    like: "{{ __('discover.like') }}",
    super: "{{ __('discover.super') }}",
    skipped: "{{ __('discover.skipped') }}",
    liked: "{{ __('discover.liked') }}",
    superliked: "{{ __('discover.superliked') }}",
    no_more: "{{ __('discover.no_more') }}",
    come_back: "{{ __('discover.come_back') }}",
    loc: "{{ __('discover.loc') }}",
    distance: "{{ __('discover.distance') }}",
    distance_unknown: "{{ __('discover.distance_unknown') }}",
    km: "{{ __('discover.km') }}",
    years: "{{ __('discover.years') }}",
};
</script>
<script src="{{ asset('js/discover.js') }}?v={{ filemtime(public_path('js/discover.js')) }}"></script>
@endpush
