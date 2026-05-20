@extends('layouts.app')
@section('title', __('nav.profile'))
@section('content')
@php
    $preferredSubcultures = old('preferred_subcultures', is_array($user->preferred_subcultures ?? null) ? $user->preferred_subcultures : []);
    $subcultureOptions = ['emo' => 'Emo', 'scene' => 'Scene', 'goth' => 'Goth', 'punk' => 'Punk', 'metalhead' => 'Metalhead'];
@endphp
<div class="profile-shell overflow-y-auto h-full p-6 flex flex-col gap-5 items-center scrollbar-thin" style="background:radial-gradient(ellipse at 50% -20%,rgba(168,85,247,.06),transparent 60%)">

    @if(session('success'))
    <div class="profile-success w-full max-w-3xl bg-green-900/30 border border-green-500/40 text-green-400 rounded-lg px-4 py-2 text-sm">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('profile.update') }}" class="w-full max-w-3xl">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

            <div class="profile-card bg-[#111118] border border-[#2a2a3a] rounded-xl overflow-hidden">
                <div class="px-4 py-2.5 border-b border-[#2a2a3a] font-mono text-xs uppercase tracking-widest text-[#5e5880]">{{ __('profile.info') }}</div>
                <div class="p-5">
                    <div class="flex items-start gap-4 mb-4">
                        <div class="relative flex-shrink-0">
                            <div id="avWrap" class="w-20 h-20 rounded-full border-2 border-purple-400 overflow-hidden flex items-center justify-center bg-gradient-to-br from-purple-900 to-blue-900 cursor-pointer shadow-[0_0_20px_rgba(168,85,247,.3)]"
                                 onclick="document.getElementById('avIn').click()">
                                @if($user->getAvatarUrl())
                                    <img src="{{ $user->getAvatarUrl() }}" class="w-full h-full object-cover" id="avPreview">
                                @else
                                    <span class="text-3xl font-bold">{{ strtoupper(substr($user->name,0,1)) }}</span>
                                @endif
                            </div>
                            <div class="absolute bottom-0 right-0 w-6 h-6 bg-purple-500 rounded-full flex items-center justify-center text-xs cursor-pointer border-2 border-[#111118]"
                                 onclick="document.getElementById('avIn').click()">+</div>
                        </div>
                        <div class="flex-1">
                            <div class="mb-2">
                                <label class="field-label">{{ __('profile.username') }}</label>
                                <input type="text" name="name" value="{{ old('name',$user->name) }}" required maxlength="30" class="field-input">
                                @error('name')<p class="field-error">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div>
                            <label class="field-label">{{ __('profile.age') }}</label>
                            <input type="number" name="age" value="{{ old('age',$user->age) }}" min="18" max="99" required class="field-input">
                        </div>
                        <div>
                            <label class="field-label">{{ __('profile.city') }}</label>
                            <input type="text" id="cityInput" name="city" value="{{ old('city',$user->city) }}" required class="field-input" autocomplete="off">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="field-label">{{ __('profile.subculture') }}</label>
                        <select name="subculture" class="field-input">
                            @foreach(['emo' => 'Emo', 'scene' => 'Scene', 'goth' => 'Goth', 'punk' => 'Punk', 'metalhead' => 'Metalhead'] as $val => $label)
                                <option value="{{ $val }}" {{ $user->subculture === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                        <div>
                            <label class="field-label">{{ __('profile.gender') }}</label>
                            <select name="gender" class="field-input" required>
                                <option value="">— {{ __('profile.choose') }} —</option>
                                <option value="female" {{ old('gender', $user->gender) === 'female' ? 'selected' : '' }}>{{ __('profile.gender_female') }}</option>
                                <option value="male" {{ old('gender', $user->gender) === 'male' ? 'selected' : '' }}>{{ __('profile.gender_male') }}</option>
                                <option value="nonbinary" {{ old('gender', $user->gender) === 'nonbinary' ? 'selected' : '' }}>{{ __('profile.gender_nonbinary') }}</option>
                                <option value="other" {{ old('gender', $user->gender) === 'other' ? 'selected' : '' }}>{{ __('profile.gender_other') }}</option>
                            </select>
                            @error('gender')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="field-label">{{ __('profile.orientation') }}</label>
                            <select name="orientation" class="field-input" required>
                                <option value="">— {{ __('profile.choose') }} —</option>
                                <option value="hetero" {{ old('orientation', $user->orientation) === 'hetero' ? 'selected' : '' }}>{{ __('profile.orientation_hetero') }}</option>
                                <option value="homo" {{ old('orientation', $user->orientation) === 'homo' ? 'selected' : '' }}>{{ __('profile.orientation_homo') }}</option>
                                <option value="bi" {{ old('orientation', $user->orientation) === 'bi' ? 'selected' : '' }}>{{ __('profile.orientation_bi') }}</option>
                                <option value="other" {{ old('orientation', $user->orientation) === 'other' ? 'selected' : '' }}>{{ __('profile.orientation_other') }}</option>
                            </select>
                            @error('orientation')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div>
                        <label class="field-label">{{ __('profile.bio') }}</label>
                        <textarea name="bio" rows="3" maxlength="500" class="field-input resize-none" placeholder="{{ __('profile.bio_placeholder') }}">{{ old('bio',$user->bio) }}</textarea>
                    </div>
                </div>
            </div>

            <div id="match-preferences" class="profile-card bg-[#111118] border border-[#2a2a3a] rounded-xl overflow-hidden scroll-mt-6">
                <div class="px-4 py-2.5 border-b border-[#2a2a3a] font-mono text-xs uppercase tracking-widest text-[#5e5880]">{{ __('profile.match_prefs') }}</div>
                <div class="p-5">
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div>
                            <label class="field-label">{{ __('profile.pref_age_min') }}</label>
                            <input type="number" name="preferred_age_min" min="18" max="99" class="field-input" value="{{ old('preferred_age_min', $user->preferred_age_min ?? 18) }}" required>
                            @error('preferred_age_min')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="field-label">{{ __('profile.pref_age_max') }}</label>
                            <input type="number" name="preferred_age_max" min="18" max="99" class="field-input" value="{{ old('preferred_age_max', $user->preferred_age_max ?? 99) }}" required>
                            @error('preferred_age_max')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-2">
                            <label class="field-label mb-0">{{ __('profile.pref_distance') }}</label>
                            <span id="distanceLabel" class="text-xs font-mono text-purple-300"></span>
                        </div>
                        <input
                            id="distanceRange"
                            type="range"
                            name="preferred_max_distance_km"
                            min="5"
                            max="500"
                            step="5"
                            value="{{ old('preferred_max_distance_km', $user->preferred_max_distance_km ?? 120) }}"
                            class="w-full accent-purple-500"
                            oninput="updateDistanceLabel(this.value)"
                        >
                        @error('preferred_max_distance_km')<p class="field-error">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <div class="field-label mb-1">{{ __('profile.preferred_subcultures') }}</div>
                        <p class="text-xs text-[#7f79a6] mb-3">{{ __('profile.preferred_subcultures_desc') }}</p>
                        <p id="prefSubcultureHint" class="text-xs text-[#9b95be] mb-3"></p>
                        <div id="prefSubculturePicker" class="flex flex-wrap gap-2">
                            @foreach($subcultureOptions as $value => $label)
                            <button
                                type="button"
                                class="pref-sub-btn text-sm px-2.5 py-1 rounded-full border border-[#2a2a3a] bg-[#16161f] text-[#b7b2ce] hover:border-purple-400 hover:text-purple-300 transition-colors"
                                data-subculture="{{ $value }}"
                                onclick="togglePreferredSubculture(this)"
                            >
                                {{ $label }}
                            </button>
                            @endforeach
                        </div>
                        <div id="prefSubcultureInputs">
                            @foreach($preferredSubcultures as $value)
                            <input type="hidden" name="preferred_subcultures[]" value="{{ $value }}" data-pref-sub="{{ $value }}">
                            @endforeach
                        </div>
                        @error('preferred_subcultures')<p class="field-error mt-2">{{ $message }}</p>@enderror
                        @error('preferred_subcultures.*')<p class="field-error mt-2">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="profile-card bg-[#111118] border border-[#2a2a3a] rounded-xl overflow-hidden">
                <div class="px-4 py-2.5 border-b border-[#2a2a3a] font-mono text-xs uppercase tracking-widest text-[#5e5880]">
                    {{ __('profile.photos') }} <span class="text-[.44rem]">(max 6)</span>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-3 gap-2" id="phGrid">
                        @foreach($user->photos as $photo)
                        <div class="photo-tile aspect-square rounded-lg border border-[#2a2a3a] overflow-hidden relative group" data-photo-id="{{ $photo->id }}">
                            <img src="/storage/{{ $photo->path }}" class="w-full h-full object-cover">
                            <button type="button" onclick="deletePhoto({{ $photo->id }}, this.closest('[data-photo-id]'))"
                                class="absolute top-1 right-1 w-5 h-5 bg-red-600/90 rounded-full hidden group-hover:flex items-center justify-center text-white text-xs border-none cursor-pointer">X</button>
                        </div>
                        @endforeach
                        @if($user->photos->count() < 6)
                        <div class="photo-add-tile aspect-square rounded-lg border border-dashed border-[#2a2a3a] flex items-center justify-center cursor-pointer hover:border-purple-400 hover:bg-purple-900/10 transition-all text-[#5e5880] text-2xl"
                             onclick="document.getElementById('phIn').click()">+</div>
                        @endif
                    </div>
                    <input type="file" id="phIn" class="hidden" accept="image/*" onchange="uploadPhoto(event)">
                    <input type="file" id="avIn" class="hidden" accept="image/*" onchange="uploadPhoto(event, true)">
                </div>
            </div>

            <div class="profile-card bg-[#111118] border border-[#2a2a3a] rounded-xl overflow-hidden">
                <div class="px-4 py-2.5 border-b border-[#2a2a3a] font-mono text-xs uppercase tracking-widest text-[#5e5880]">{{ __('profile.interests') }}</div>
                <div class="p-5">
                    <div class="mb-4">
                        <div class="text-base font-semibold font-crimson tracking-normal leading-6 text-[#ddd8f0] [text-shadow:none]" style="-webkit-font-smoothing:antialiased; text-rendering:optimizeLegibility;">{{ __('onboarding.interests_suggestions_title') }}</div>
                        <p class="text-sm text-[#8d87b2] mt-1 mb-3">{{ __('onboarding.interests_suggestions_desc') }}</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach(trans('onboarding.interests_suggestions') as $suggestion)
                            <button
                                type="button"
                                class="onb-suggestion text-sm px-2.5 py-1 rounded-full border border-[#2a2a3a] bg-[#16161f] text-[#b7b2ce] hover:border-purple-400 hover:text-purple-300 transition-colors"
                                data-interest="{{ $suggestion }}"
                                onclick="addInterestSuggestion(this)"
                            >
                                {{ $suggestion }}
                            </button>
                            @endforeach
                        </div>
                    </div>
                    <div class="rounded-xl border border-[#2a2a3a] bg-[#13131d] p-4">
                        <div class="text-sm font-semibold text-[#d6d1ee]">{{ __('onboarding.interests_selected_title') }}</div>
                        <p class="text-sm text-[#9b95be] mt-1">{{ __('onboarding.interests_selected_desc') }}</p>
                        <div class="flex flex-wrap gap-2 mt-4 min-h-[44px]" id="interestsList">
                            @foreach($user->interests as $interest)
                            <span class="interest-tag" data-val="{{ $interest->name }}">
                                {{ $interest->name }} <span onclick="removeTag(this.parentElement)" class="cursor-pointer text-[#5e5880] hover:text-red-400 ml-1">X</span>
                                <input type="hidden" name="interests[]" value="{{ $interest->name }}">
                            </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="profile-card bg-[#111118] border border-[#2a2a3a] rounded-xl overflow-hidden">
                <div class="px-4 py-2.5 border-b border-[#2a2a3a] font-mono text-xs uppercase tracking-widest text-[#5e5880]">{{ __('profile.artists') }}</div>
                <div class="p-5">
                    <div class="flex flex-wrap gap-2 mb-3" id="artistsList">
                        @foreach($user->artists as $artist)
                        <span class="interest-tag" data-val="{{ $artist->name }}">
                            {{ $artist->name }} <span onclick="removeTag(this.parentElement)" class="cursor-pointer text-[#5e5880] hover:text-red-400 ml-1">X</span>
                            <input type="hidden" name="artists[]" value="{{ $artist->name }}">
                        </span>
                        @endforeach
                    </div>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <input type="text" id="artistInput" class="field-input flex-1" placeholder="{{ __('profile.artist_placeholder') }}" maxlength="100" autocomplete="off" onkeydown="if(event.key==='Enter'){event.preventDefault();searchSpotifyArtists();}">
                        @if($spotifyEnabled)
                        <button type="button" onclick="searchSpotifyArtists()" class="bg-[#16161f] border border-[#2a2a3a] text-[#e2e0f0] px-4 py-2 rounded-lg text-sm font-semibold hover:border-purple-400 hover:text-purple-300 transition-colors w-full sm:w-auto">
                            {{ __('profile.spotify_search_btn') }}
                        </button>
                        @endif
                    </div>

                    <div class="mt-4 rounded-xl border border-[#2a2a3a] bg-[#13131d] p-4">
                        <div class="text-sm font-semibold text-[#d6d1ee]">{{ __('profile.spotify_title') }}</div>
                        @if($spotifyEnabled)
                        <p id="spotifyInfo" class="text-sm text-[#9b95be] mt-1">{{ __('profile.spotify_search_only_desc') }}</p>
                        <div id="spotifyResults" class="flex flex-wrap gap-2 mt-3"></div>
                        @else
                        <p class="text-sm text-[#9b95be] mt-1">{{ __('profile.spotify_not_configured') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end mt-5">
            <button type="submit" class="profile-save-btn bg-gradient-to-br from-purple-500 to-violet-700 text-white px-8 py-2.5 rounded-lg font-mono text-xs uppercase tracking-widest shadow-[0_0_12px_rgba(168,85,247,.3)] hover:shadow-[0_0_22px_rgba(168,85,247,.5)] hover:-translate-y-0.5 transition-all">
                {{ __('profile.save') }}
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<style>
@keyframes profileCardIn {
    from { opacity: 0; transform: translateY(14px) scale(.985); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
@keyframes successDrop {
    from { opacity: 0; transform: translateY(-8px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes avatarHalo {
    0%, 100% { opacity: .38; transform: scale(1); }
    50% { opacity: .7; transform: scale(1.08); }
}
@keyframes tagPop {
    from { opacity: 0; transform: translateY(8px) scale(.94); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
@keyframes tagOut {
    from { opacity: 1; transform: scale(1); }
    to { opacity: 0; transform: scale(.82); }
}
@keyframes photoIn {
    from { opacity: 0; transform: scale(.92); }
    to { opacity: 1; transform: scale(1); }
}
@keyframes photoOut {
    from { opacity: 1; transform: scale(1); }
    to { opacity: 0; transform: scale(.9); }
}
@keyframes chipIn {
    from { opacity: 0; transform: translateY(6px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes savePulse {
    0%, 100% { box-shadow: 0 0 10px rgba(168, 85, 247, .28); }
    50% { box-shadow: 0 0 18px rgba(168, 85, 247, .42); }
}

.profile-success { animation: successDrop .28s ease-out both; }
.profile-card {
    opacity: 0;
    transform: translateY(14px) scale(.985);
    transition: border-color .22s ease, transform .22s ease;
}
.profile-card.is-visible { animation: profileCardIn .45s cubic-bezier(.22,.61,.36,1) both; }
.profile-card:hover { border-color: #3a3156; transform: translateY(-2px); }

#avWrap { position: relative; isolation: isolate; }
#avWrap::after {
    content: "";
    position: absolute;
    inset: -6px;
    border-radius: 9999px;
    background: radial-gradient(circle, rgba(168,85,247,.32), rgba(59,130,246,.08) 62%, transparent 70%);
    filter: blur(3px);
    animation: avatarHalo 3.2s ease-in-out infinite;
    z-index: -1;
    pointer-events: none;
}

.interest-tag { transition: border-color .18s ease, transform .18s ease, color .18s ease; }
.interest-tag:hover { transform: translateY(-1px); border-color: #6d4bc4; }
.interest-tag.tag-enter { animation: tagPop .24s ease-out both; }
.interest-tag.tag-exit { animation: tagOut .2s ease-in forwards; }

.pref-sub-btn.is-active {
    border-color: #8b5cf6;
    color: #c4b5fd;
    background: rgba(168, 85, 247, .15);
    box-shadow: inset 0 0 0 1px rgba(168, 85, 247, .18);
}
.pref-sub-btn.is-disabled {
    opacity: .45;
}

.photo-tile { transition: transform .2s ease, border-color .2s ease; }
.photo-tile:hover { transform: translateY(-2px); border-color: #5f4a94; }
.photo-tile.photo-enter { animation: photoIn .24s ease-out both; }
.photo-tile.photo-exit { animation: photoOut .2s ease-in forwards; }

.spotify-chip { animation: chipIn .24s ease-out both; }
.profile-save-btn { animation: savePulse 2.6s ease-in-out infinite; }

@media (prefers-reduced-motion: reduce) {
    .profile-success,
    .profile-card,
    .profile-card.is-visible,
    #avWrap::after,
    .interest-tag.tag-enter,
    .interest-tag.tag-exit,
    .photo-tile.photo-enter,
    .photo-tile.photo-exit,
    .spotify-chip,
    .profile-save-btn {
        animation: none !important;
    }
}
</style>
<script>
const CSRF = document.querySelector('meta[name=csrf-token]').content;
const SPOTIFY_ENABLED = @json($spotifyEnabled);
const SPOTIFY_SEARCH_URL = @json(route('spotify.search'));
const SPOTIFY_SEARCH_MIN = @json(__('profile.spotify_search_min'));
const SPOTIFY_SEARCHING = @json(__('profile.spotify_searching'));
const SPOTIFY_NOT_FOUND = @json(__('profile.spotify_not_found'));
const SPOTIFY_ERROR = @json(__('profile.spotify_error'));
const SPOTIFY_ADDED_TEMPLATE = @json(__('profile.spotify_added', ['name' => '__NAME__']));
const DISTANCE_LABEL_TEMPLATE = @json(__('profile.distance_km', ['km' => '__KM__']));
const PREF_SUBCULTURE_HINT_TEMPLATE = @json(__('profile.selected_count', ['count' => '__COUNT__', 'max' => '__MAX__']));
const SUBCULTURE_LIMIT = 5;

function lockInputTheme(id) {
    const el = document.getElementById(id);
    if (!el) return;
    const apply = () => {
        el.style.backgroundColor = '#16161f';
        el.style.color = '#e2e0f0';
    };
    ['input', 'change', 'focus', 'blur', 'keydown'].forEach((evt) => {
        el.addEventListener(evt, () => requestAnimationFrame(apply));
    });
    apply();
}

lockInputTheme('cityInput');
lockInputTheme('artistInput');

function escapeHtml(s) {
    return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

function escapeAttr(s) {
    return String(s)
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}

function removeTag(el) {
    if (!el) return;
    el.classList.add('tag-exit');

    let removed = false;
    const finalize = () => {
        if (removed) return;
        removed = true;
        el.remove();
        refreshInterestSuggestionState();
    };

    el.addEventListener('animationend', finalize, { once: true });
    setTimeout(finalize, 230);
}

function tagCount(listId) {
    return document.querySelectorAll(`#${listId} [data-val]`).length;
}

function addTagValue(value, listId, fieldName, max) {
    const list = document.getElementById(listId);
    if (!list) return false;
    if (value === '') return false;
    if (tagCount(listId) >= max) return false;

    const exists = Array.from(list.querySelectorAll('[data-val]')).some((el) =>
        String(el.dataset.val || '').toLowerCase() === value.toLowerCase()
    );
    if (exists) return false;

    const span = document.createElement('span');
    span.className = 'interest-tag tag-enter';
    span.dataset.val = value;
    span.innerHTML = `${escapeHtml(value)} <span onclick="removeTag(this.parentElement)" class="cursor-pointer text-[#5e5880] hover:text-red-400 ml-1">X</span><input type="hidden" name="${fieldName}" value="${escapeAttr(value)}">`;
    list.appendChild(span);
    if (listId === 'interestsList') {
        refreshInterestSuggestionState();
    }
    return true;
}

function addInterestSuggestion(button) {
    const value = String(button?.dataset?.interest || '').trim();
    if (!value) return;
    addTagValue(value, 'interestsList', 'interests[]', 15);
}

function refreshInterestSuggestionState() {
    const selected = new Set(
        Array.from(document.querySelectorAll('#interestsList [data-val]'))
            .map((el) => String(el.dataset.val || '').toLowerCase())
    );

    document.querySelectorAll('.onb-suggestion').forEach((btn) => {
        const val = String(btn.dataset.interest || '').toLowerCase();
        const active = selected.has(val);
        btn.classList.toggle('border-purple-500', active);
        btn.classList.toggle('text-purple-300', active);
        btn.classList.toggle('bg-purple-500/10', active);
    });
}

function updateDistanceLabel(km) {
    const label = document.getElementById('distanceLabel');
    if (!label) return;
    label.textContent = DISTANCE_LABEL_TEMPLATE.replace('__KM__', String(km || '0'));
}

function getPreferredSubcultureValues() {
    return Array.from(document.querySelectorAll('#prefSubcultureInputs [data-pref-sub]'))
        .map((el) => String(el.dataset.prefSub || ''))
        .filter((value) => value !== '');
}

function removePreferredSubcultureInput(value) {
    document.querySelectorAll('#prefSubcultureInputs [data-pref-sub]').forEach((el) => {
        if (String(el.dataset.prefSub || '') === value) {
            el.remove();
        }
    });
}

function addPreferredSubcultureInput(value) {
    const container = document.getElementById('prefSubcultureInputs');
    if (!container) return;

    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'preferred_subcultures[]';
    input.value = value;
    input.dataset.prefSub = value;
    container.appendChild(input);
}

function refreshPreferredSubcultureState() {
    const selectedValues = getPreferredSubcultureValues();
    const selected = new Set(selectedValues);
    const maxReached = selectedValues.length >= SUBCULTURE_LIMIT;

    document.querySelectorAll('.pref-sub-btn').forEach((btn) => {
        const value = String(btn.dataset.subculture || '');
        const active = selected.has(value);
        btn.classList.toggle('is-active', active);
        btn.classList.toggle('is-disabled', !active && maxReached);
    });

    const hint = document.getElementById('prefSubcultureHint');
    if (hint) {
        hint.textContent = PREF_SUBCULTURE_HINT_TEMPLATE
            .replace('__COUNT__', String(selectedValues.length))
            .replace('__MAX__', String(SUBCULTURE_LIMIT));
    }
}

function togglePreferredSubculture(button) {
    if (!(button instanceof HTMLElement)) return;

    const value = String(button.dataset.subculture || '');
    if (value === '') return;

    const selected = getPreferredSubcultureValues();
    if (selected.includes(value)) {
        removePreferredSubcultureInput(value);
        refreshPreferredSubcultureState();
        return;
    }

    if (selected.length >= SUBCULTURE_LIMIT) {
        return;
    }

    addPreferredSubcultureInput(value);
    refreshPreferredSubcultureState();
}

function setSpotifyInfo(message, isError = false) {
    const info = document.getElementById('spotifyInfo');
    if (!info) return;

    info.textContent = message || '';
    info.classList.toggle('text-red-400', isError);
    info.classList.toggle('text-[#9b95be]', !isError);
}

function renderSpotifyResults(artists) {
    const list = document.getElementById('spotifyResults');
    if (!list) return;

    list.innerHTML = '';

    if (!Array.isArray(artists) || artists.length === 0) {
        setSpotifyInfo(SPOTIFY_NOT_FOUND, false);
        return;
    }

    artists.forEach((artist, index) => {
        if (!artist || !artist.name) return;
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'spotify-chip text-sm px-2.5 py-1 rounded-full border border-[#2a2a3a] bg-[#16161f] text-[#b7b2ce] hover:border-purple-400 hover:text-purple-300 transition-colors';
        button.style.animationDelay = `${Math.min(index * 40, 280)}ms`;
        button.textContent = artist.name;
        button.addEventListener('click', () => {
            const added = addTagValue(String(artist.name), 'artistsList', 'artists[]', 10);
            if (added) {
                setSpotifyInfo(SPOTIFY_ADDED_TEMPLATE.replace('__NAME__', String(artist.name)), false);
            }
        });
        list.appendChild(button);
    });
}

async function searchSpotifyArtists() {
    if (!SPOTIFY_ENABLED) return;

    const input = document.getElementById('artistInput');
    const query = String(input?.value || '').trim();
    if (query.length < 2) {
        setSpotifyInfo(SPOTIFY_SEARCH_MIN, true);
        return;
    }

    setSpotifyInfo(SPOTIFY_SEARCHING, false);

    try {
        const response = await fetch(`${SPOTIFY_SEARCH_URL}?q=${encodeURIComponent(query)}`, {
            headers: { 'Accept': 'application/json' },
        });
        const data = await response.json();

        if (!response.ok) {
            setSpotifyInfo(data?.message || SPOTIFY_ERROR, true);
            renderSpotifyResults([]);
            return;
        }

        renderSpotifyResults(Array.isArray(data?.artists) ? data.artists : []);
    } catch (e) {
        setSpotifyInfo(SPOTIFY_ERROR, true);
        renderSpotifyResults([]);
    }
}

function uploadPhoto(e, isAvatar = false) {
    const file = e.target.files[0];
    e.target.value = '';
    if (!file) return;

    const fd = new FormData();
    fd.append('photo', file);
    fd.append('_token', CSRF);

    fetch('/profile/photo', { method: 'POST', body: fd })
        .then((r) => r.json())
        .then((data) => {
            if (data.error) {
                alert(data.error);
                return;
            }

            if (isAvatar) {
                let av = document.getElementById('avPreview');
                if (!av) {
                    const wrap = document.getElementById('avWrap');
                    if (wrap) {
                        wrap.innerHTML = `<img src="${escapeAttr(data.url)}" class="w-full h-full object-cover" id="avPreview">`;
                        return;
                    }
                }
                av = document.getElementById('avPreview');
                if (av) av.src = data.url;
                return;
            }

            const grid = document.getElementById('phGrid');
            if (!grid) return;

            const plus = grid.querySelector('.border-dashed');
            const div = document.createElement('div');
            div.className = 'photo-tile photo-enter aspect-square rounded-lg border border-[#2a2a3a] overflow-hidden relative group';
            div.dataset.photoId = String(data.id);
            div.innerHTML = `<img src="${escapeAttr(data.url)}" class="w-full h-full object-cover"><button type="button" onclick="deletePhoto(${data.id}, this.closest('[data-photo-id]'))" class="absolute top-1 right-1 w-5 h-5 bg-red-600/90 rounded-full hidden group-hover:flex items-center justify-center text-white text-xs border-none cursor-pointer">X</button>`;

            if (plus) grid.insertBefore(div, plus);
            else grid.appendChild(div);
        });
}

function deletePhoto(id, el) {
    fetch(`/profile/photo/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF } })
        .then((r) => r.json())
        .then(() => {
            if (!el) return;
            el.classList.add('photo-exit');
            el.addEventListener('animationend', () => el.remove(), { once: true });
        });
}

function initProfileAnimations() {
    const cards = Array.from(document.querySelectorAll('.profile-card'));
    cards.forEach((card, index) => {
        setTimeout(() => card.classList.add('is-visible'), 70 + (index * 90));
    });
    const distanceRange = document.getElementById('distanceRange');
    if (distanceRange) updateDistanceLabel(distanceRange.value);
    refreshInterestSuggestionState();
    refreshPreferredSubcultureState();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initProfileAnimations);
} else {
    initProfileAnimations();
}
</script>
@endpush
