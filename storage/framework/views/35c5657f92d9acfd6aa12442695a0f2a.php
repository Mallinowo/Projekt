<?php $__env->startSection('title', __('onboarding.title')); ?>

<?php
    $interestValues = old('interests', $user->interests->pluck('name')->toArray());
    $artistValues = old('artists', $user->artists->pluck('name')->toArray());
    $startStep = 1;
    if ($errors->has('interests')) $startStep = 2;
    if ($errors->has('bio') || $errors->has('artists') || $errors->has('gender') || $errors->has('orientation')) $startStep = 3;
?>

<?php $__env->startSection('content'); ?>
<div class="h-full overflow-y-auto scrollbar-thin p-4 pb-8 md:p-8 md:pb-10 flex items-start justify-center" style="background:radial-gradient(ellipse at 50% -20%,rgba(168,85,247,.08),transparent 60%)">
    <div class="w-full bg-[#111118] border border-[#2a2a3a] rounded-2xl overflow-hidden shadow-[0_16px_45px_rgba(0,0,0,.45)]" style="max-width:860px;">
        <div class="px-5 py-4 border-b border-[#2a2a3a]">
            <div class="font-crimson text-3xl md:text-4xl font-semibold text-purple-300 leading-tight"><?php echo e(__('onboarding.title')); ?></div>
            <p class="text-sm md:text-base text-[#b7b2ce] mt-1"><?php echo e(__('onboarding.subtitle')); ?></p>

            <div class="mt-4">
                <div class="flex items-center justify-between gap-3">
                    <span class="text-xs font-mono tracking-wide text-[#8f88b7]"><?php echo e(__('onboarding.step')); ?> <span id="stepIndex"><?php echo e($startStep); ?></span>/3</span>
                    <span id="stepName" class="text-xs text-[#8f88b7]"></span>
                </div>
                <div class="mt-2 h-1.5 bg-[#1a1a26] rounded-full overflow-hidden">
                    <div id="progressBar" class="h-full bg-gradient-to-r from-purple-500 to-pink-500 transition-all duration-300"></div>
                </div>
            </div>
        </div>

        <form method="POST" action="<?php echo e(route('onboarding.complete')); ?>" id="onboardingForm">
            <?php echo csrf_field(); ?>

            <div class="overflow-hidden border-y border-[#2a2a3a] bg-[#0f0f17]">
                <div id="onbTrack" class="flex" style="transform:translate3d(0,0,0); align-items:flex-start;">
                    <div class="w-full flex-shrink-0 p-5 md:p-6 onb-step" data-step="1" style="opacity:1; transition:opacity .22s ease; min-height:360px;">
                        <div class="font-cinzel text-xl text-[#f0ecff] mb-1"><?php echo e(__('onboarding.photos_title')); ?></div>
                        <p class="text-sm text-[#b7b2ce] mb-4"><?php echo e(__('onboarding.photos_desc')); ?></p>

                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-4" id="photoGrid">
                            <?php $__currentLoopData = $user->photos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $photo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="aspect-square rounded-lg border border-[#2a2a3a] overflow-hidden relative group" data-photo-id="<?php echo e($photo->id); ?>">
                                <img src="/storage/<?php echo e($photo->path); ?>" class="w-full h-full object-cover">
                                <button type="button" onclick="deletePhoto(<?php echo e($photo->id); ?>, this)" class="absolute top-1 right-1 w-5 h-5 bg-red-600/90 rounded-full hidden group-hover:flex items-center justify-center text-white text-xs border-none cursor-pointer">X</button>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>

                        <input type="file" id="photoInput" class="hidden" accept="image/*" onchange="uploadPhoto(event)">
                        <button type="button" onclick="document.getElementById('photoInput').click()" class="bg-purple-600 hover:bg-purple-500 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
                            <?php echo e(__('onboarding.photos_btn')); ?>

                        </button>

                        <input type="hidden" id="photoCountVal" value="<?php echo e($user->photos->count()); ?>">
                        <p id="photoHint" class="text-xs text-[#5e5880] mt-2"></p>
                        <?php $__errorArgs = ['photos'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-400 text-xs mt-2"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="w-full flex-shrink-0 p-5 md:p-6 onb-step" data-step="2" style="opacity:.45; transition:opacity .22s ease; min-height:360px;">
                        <div class="font-cinzel text-xl text-[#f0ecff] mb-1"><?php echo e(__('onboarding.interests_title')); ?></div>

                        <div class="flex gap-2">
                            <input type="text" id="interestInput" class="field-input flex-1" maxlength="30" placeholder="<?php echo e(__('onboarding.interest_placeholder')); ?>" onkeydown="if(event.key==='Enter'){event.preventDefault();addInterest();}">
                            <button type="button" onclick="addInterest()" class="bg-purple-600 hover:bg-purple-500 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors"><?php echo e(__('onboarding.add')); ?></button>
                        </div>

                        <div class="mt-5">
                            <div class="text-base font-semibold font-crimson tracking-normal leading-6 text-[#ddd8f0] [text-shadow:none]" style="-webkit-font-smoothing:antialiased; text-rendering:optimizeLegibility;"><?php echo e(__('onboarding.interests_suggestions_title')); ?></div>
                            <p class="text-sm text-[#8d87b2] mt-1 mb-3"><?php echo e(__('onboarding.interests_suggestions_desc')); ?></p>
                            <div class="flex flex-wrap gap-2">
                                <?php $__currentLoopData = trans('onboarding.interests_suggestions'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $suggestion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <button
                                    type="button"
                                    class="onb-suggestion text-sm px-2.5 py-1 rounded-full border border-[#2a2a3a] bg-[#16161f] text-[#b7b2ce] hover:border-purple-400 hover:text-purple-300 transition-colors"
                                    data-interest="<?php echo e($suggestion); ?>"
                                    onclick="addInterestSuggestion(this)"
                                >
                                    <?php echo e($suggestion); ?>

                                </button>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>

                        <div class="mt-5 rounded-xl border border-[#2a2a3a] bg-[#13131d] p-4">
                            <div class="text-base font-semibold font-crimson tracking-normal leading-6 text-[#f0ebff] [text-shadow:none]" style="-webkit-font-smoothing:antialiased; text-rendering:optimizeLegibility;"><?php echo e(__('onboarding.interests_selected_title')); ?></div>
                            <p class="text-sm text-[#9b95be] mt-1"><?php echo e(__('onboarding.interests_selected_desc')); ?></p>
                            <div class="flex flex-wrap gap-2 mt-4 min-h-[44px]" id="interestsList">
                                <?php $__currentLoopData = $interestValues; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $interest): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span class="interest-tag interest-tag-selected" data-tag="<?php echo e($interest); ?>">
                                    <?php echo e($interest); ?>

                                    <button type="button" class="text-[#8d87b6] hover:text-red-400 ml-1" onclick="removeTag(this.parentElement)">X</button>
                                    <input type="hidden" name="interests[]" value="<?php echo e($interest); ?>">
                                </span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                        <p id="interestHint" class="text-sm text-[#7c76a0] mt-3"></p>
                        <?php $__errorArgs = ['interests'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-400 text-xs mt-2"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="w-full flex-shrink-0 p-5 md:p-6 onb-step" data-step="3" style="opacity:.45; transition:opacity .22s ease; min-height:360px;">
                        <div class="font-cinzel text-xl text-[#f0ecff] mb-1"><?php echo e(__('onboarding.artists_title')); ?></div>
                        <p class="text-sm text-[#b7b2ce] mb-4"><?php echo e(__('onboarding.artists_desc')); ?></p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                            <div>
                                <label class="field-label"><?php echo e(__('onboarding.gender')); ?></label>
                                <select name="gender" class="field-input" required>
                                    <option value="">— <?php echo e(__('onboarding.choose')); ?> —</option>
                                    <option value="female" <?php echo e(old('gender', $user->gender) === 'female' ? 'selected' : ''); ?>><?php echo e(__('onboarding.gender_female')); ?></option>
                                    <option value="male" <?php echo e(old('gender', $user->gender) === 'male' ? 'selected' : ''); ?>><?php echo e(__('onboarding.gender_male')); ?></option>
                                    <option value="nonbinary" <?php echo e(old('gender', $user->gender) === 'nonbinary' ? 'selected' : ''); ?>><?php echo e(__('onboarding.gender_nonbinary')); ?></option>
                                    <option value="other" <?php echo e(old('gender', $user->gender) === 'other' ? 'selected' : ''); ?>><?php echo e(__('onboarding.gender_other')); ?></option>
                                </select>
                                <?php $__errorArgs = ['gender'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-400 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div>
                                <label class="field-label"><?php echo e(__('onboarding.orientation')); ?></label>
                                <select name="orientation" class="field-input" required>
                                    <option value="">— <?php echo e(__('onboarding.choose')); ?> —</option>
                                    <option value="hetero" <?php echo e(old('orientation', $user->orientation) === 'hetero' ? 'selected' : ''); ?>><?php echo e(__('onboarding.orientation_hetero')); ?></option>
                                    <option value="homo" <?php echo e(old('orientation', $user->orientation) === 'homo' ? 'selected' : ''); ?>><?php echo e(__('onboarding.orientation_homo')); ?></option>
                                    <option value="bi" <?php echo e(old('orientation', $user->orientation) === 'bi' ? 'selected' : ''); ?>><?php echo e(__('onboarding.orientation_bi')); ?></option>
                                    <option value="other" <?php echo e(old('orientation', $user->orientation) === 'other' ? 'selected' : ''); ?>><?php echo e(__('onboarding.orientation_other')); ?></option>
                                </select>
                                <?php $__errorArgs = ['orientation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-400 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                        <label class="field-label"><?php echo e(__('onboarding.bio_label')); ?></label>
                        <textarea name="bio" id="bioInput" rows="4" maxlength="500" class="field-input resize-none mb-4" placeholder="<?php echo e(__('onboarding.bio_placeholder')); ?>"><?php echo e(old('bio', $user->bio)); ?></textarea>
                        <?php $__errorArgs = ['bio'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-400 text-xs -mt-2 mb-3"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                        <div class="flex flex-col sm:flex-row gap-2">
                            <input type="text" id="artistInput" class="field-input flex-1" maxlength="100" autocomplete="off" placeholder="<?php echo e(__('onboarding.artist_placeholder')); ?>" onkeydown="if(event.key==='Enter'){event.preventDefault();searchSpotifyArtists();}">
                            <?php if($spotifyEnabled): ?>
                            <button type="button" onclick="searchSpotifyArtists()" class="bg-[#16161f] border border-[#2a2a3a] text-[#e2e0f0] px-4 py-2 rounded-lg text-sm font-semibold hover:border-purple-400 hover:text-purple-300 transition-colors w-full sm:w-auto">
                                <?php echo e(__('onboarding.spotify_search_btn')); ?>

                            </button>
                            <?php endif; ?>
                        </div>

                        <div class="mt-4 rounded-xl border border-[#2a2a3a] bg-[#13131d] p-4">
                            <div class="text-sm font-semibold text-[#d6d1ee]"><?php echo e(__('onboarding.spotify_title')); ?></div>
                            <?php if($spotifyEnabled): ?>
                            <p id="spotifyInfo" class="text-sm text-[#9b95be] mt-1"><?php echo e(__('onboarding.spotify_search_only_desc')); ?></p>
                            <div id="spotifyResults" class="flex flex-wrap gap-2 mt-3"></div>
                            <?php else: ?>
                            <p class="text-sm text-[#9b95be] mt-1"><?php echo e(__('onboarding.spotify_not_configured')); ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="mt-5 rounded-xl border border-[#2a2a3a] bg-[#13131d] p-4">
                            <div class="text-sm font-semibold text-[#d6d1ee]"><?php echo e(__('onboarding.artists_selected_title')); ?></div>
                            <p class="text-sm text-[#9b95be] mt-1"><?php echo e(__('onboarding.artists_selected_desc')); ?></p>
                            <div class="flex flex-wrap gap-2 mt-4 min-h-[44px]" id="artistsList">
                                <?php $__currentLoopData = $artistValues; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $artist): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span class="interest-tag" data-tag="<?php echo e($artist); ?>">
                                    <?php echo e($artist); ?>

                                    <button type="button" class="text-[#8d87b6] hover:text-red-400 ml-1" onclick="removeTag(this.parentElement)">X</button>
                                    <input type="hidden" name="artists[]" value="<?php echo e($artist); ?>">
                                </span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-5 py-4 border-t border-[#2a2a3a] bg-[#0f0f17] flex items-center justify-between gap-3 flex-wrap">
                <button type="button" id="prevBtn" onclick="prevStep()" class="text-xs border border-[#2a2a3a] px-4 py-2 rounded text-[#b7b2ce] hover:border-purple-400 hover:text-purple-300 transition-colors hidden">
                    <?php echo e(__('onboarding.back')); ?>

                </button>
                <span class="text-xs text-[#5e5880] flex-1 min-w-[180px]" id="wizardInfo"></span>
                <button type="button" id="nextBtn" onclick="nextStep()" class="bg-gradient-to-r from-purple-500 to-pink-500 text-white px-5 py-2 rounded-lg text-sm font-semibold">
                    <?php echo e(__('onboarding.next')); ?>

                </button>
                <button
                    type="submit"
                    id="finishBtn"
                    class="hidden px-5 py-2 rounded-lg text-sm font-semibold text-white border border-green-500/40 shadow-[0_0_12px_rgba(168,85,247,.3)] hover:shadow-[0_0_22px_rgba(168,85,247,.5)] transition-all"
                    style="background:linear-gradient(90deg,#8b5cf6,#ec4899);"
                >
                    <?php echo e(__('onboarding.finish')); ?>

                </button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
const CSRF = document.querySelector('meta[name=csrf-token]').content;
const TOTAL_STEPS = 3;
const START_STEP = <?php echo e($startStep); ?>;
const PHOTO_HINT_TEMPLATE = <?php echo json_encode(__('onboarding.photos_hint', ['count' => '__COUNT__']), 512) ?>;
const INTEREST_HINT_TEMPLATE = <?php echo json_encode(__('onboarding.interests_count', ['count' => '__COUNT__']), 512) ?>;
const STEP_NAMES = {
    1: <?php echo json_encode(__('onboarding.photos_title'), 15, 512) ?>,
    2: <?php echo json_encode(__('onboarding.interests_title'), 15, 512) ?>,
    3: <?php echo json_encode(__('onboarding.artists_title'), 15, 512) ?>,
};
const NEED_PHOTO = <?php echo json_encode(__('onboarding.need_photo'), 15, 512) ?>;
const MIN_INTERESTS = <?php echo json_encode(__('onboarding.min_interests'), 15, 512) ?>;
const SPOTIFY_ENABLED = <?php echo json_encode($spotifyEnabled, 15, 512) ?>;
const SPOTIFY_SEARCH_URL = <?php echo json_encode(route('spotify.search'), 15, 512) ?>;
const SPOTIFY_SEARCH_MIN = <?php echo json_encode(__('onboarding.spotify_search_min'), 15, 512) ?>;
const SPOTIFY_SEARCHING = <?php echo json_encode(__('onboarding.spotify_searching'), 15, 512) ?>;
const SPOTIFY_NOT_FOUND = <?php echo json_encode(__('onboarding.spotify_not_found'), 15, 512) ?>;
const SPOTIFY_ERROR = <?php echo json_encode(__('onboarding.spotify_error'), 15, 512) ?>;
const SPOTIFY_ADDED_TEMPLATE = <?php echo json_encode(__('onboarding.spotify_added', ['name' => '__NAME__']), 512) ?>;

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

lockInputTheme('artistInput');

let currentStep = 1;
let photoCount = parseInt(document.getElementById('photoCountVal').value, 10) || 0;
const onbTrack = document.getElementById('onbTrack');
let trackAnimation = null;

function showStep(step, options = {}) {
    const nextStep = Math.max(1, Math.min(TOTAL_STEPS, step));
    const prevStep = currentStep;
    const direction = nextStep > prevStep ? 1 : (nextStep < prevStep ? -1 : 0);
    currentStep = nextStep;
    const targetTranslate = (currentStep - 1) * -100;

    snapTrack(targetTranslate, direction, !!options.immediate);

    document.querySelectorAll('.onb-step').forEach((section) => {
        const secStep = parseInt(section.dataset.step, 10);
        section.style.opacity = secStep === currentStep ? '1' : '.45';
        section.style.pointerEvents = secStep === currentStep ? 'auto' : 'none';
    });

    document.getElementById('stepIndex').textContent = String(currentStep);
    document.getElementById('stepName').textContent = STEP_NAMES[currentStep] || '';
    document.getElementById('progressBar').style.width = `${(currentStep / TOTAL_STEPS) * 100}%`;
    document.getElementById('prevBtn').classList.toggle('hidden', currentStep === 1);
    document.getElementById('nextBtn').classList.toggle('hidden', currentStep === TOTAL_STEPS);
    document.getElementById('finishBtn').classList.toggle('hidden', currentStep !== TOTAL_STEPS);

    updatePhotoHint();
    updateInterestHint();
}

function snapTrack(targetPercent, direction, immediate = false) {
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (immediate || direction === 0 || reduceMotion || typeof onbTrack.animate !== 'function') {
        if (trackAnimation) {
            trackAnimation.cancel();
            trackAnimation = null;
        }
        onbTrack.style.willChange = 'auto';
        onbTrack.style.transform = `translate3d(${targetPercent}%,0,0)`;
        return;
    }

    if (trackAnimation) {
        trackAnimation.cancel();
        trackAnimation = null;
    }

    onbTrack.style.willChange = 'transform';
    const fromPercent = getCurrentTranslatePercent();
    const overshootPercent = direction > 0 ? targetPercent - 4.5 : targetPercent + 4.5;

    trackAnimation = onbTrack.animate(
        [
            { transform: `translate3d(${fromPercent}%,0,0)`, offset: 0 },
            { transform: `translate3d(${overshootPercent}%,0,0)`, offset: 0.78 },
            { transform: `translate3d(${targetPercent}%,0,0)`, offset: 1 },
        ],
        {
            duration: 420,
            easing: 'cubic-bezier(.22,.61,.36,1)',
            fill: 'forwards',
        }
    );

    trackAnimation.onfinish = () => {
        onbTrack.style.transform = `translate3d(${targetPercent}%,0,0)`;
        onbTrack.style.willChange = 'auto';
        trackAnimation = null;
    };
    trackAnimation.oncancel = () => {
        onbTrack.style.willChange = 'auto';
        trackAnimation = null;
    };
}

function getCurrentTranslatePercent() {
    const computed = window.getComputedStyle(onbTrack).transform;
    if (!computed || computed === 'none') return 0;

    const matrixMatch = computed.match(/^matrix\((.+)\)$/);
    if (matrixMatch) {
        const values = matrixMatch[1].split(',').map((v) => parseFloat(v.trim()));
        const tx = values[4] || 0;
        const width = onbTrack.parentElement.clientWidth || 1;
        return (tx / width) * 100;
    }

    const matrix3dMatch = computed.match(/^matrix3d\((.+)\)$/);
    if (matrix3dMatch) {
        const values = matrix3dMatch[1].split(',').map((v) => parseFloat(v.trim()));
        const tx = values[12] || 0;
        const width = onbTrack.parentElement.clientWidth || 1;
        return (tx / width) * 100;
    }

    return 0;
}

function nextStep() {
    if (currentStep === 1 && photoCount < 1) {
        setWizardInfo(NEED_PHOTO, true);
        return;
    }

    if (currentStep === 2 && tagCount('interestsList') < 5) {
        setWizardInfo(MIN_INTERESTS, true);
        return;
    }

    setWizardInfo('');
    showStep(currentStep + 1);
}

function prevStep() {
    setWizardInfo('');
    showStep(currentStep - 1);
}

function setWizardInfo(message, isError = false) {
    const el = document.getElementById('wizardInfo');
    el.textContent = message || '';
    el.classList.toggle('text-red-400', isError);
    el.classList.toggle('text-[#5e5880]', !isError);
}

function tagCount(listId) {
    return document.querySelectorAll(`#${listId} [data-tag]`).length;
}

function removeTag(el) {
    el.remove();
    updateInterestHint();
    refreshInterestSuggestionState();
}

function addInterest() {
    addTag('interestInput', 'interestsList', 'interests[]', 15);
    updateInterestHint();
    refreshInterestSuggestionState();
}

function addInterestSuggestion(button) {
    const value = String(button?.dataset?.interest || '').trim();
    if (!value) return;

    addTagValue(value, 'interestsList', 'interests[]', 15);
    updateInterestHint();
    refreshInterestSuggestionState();
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

    artists.forEach((artist) => {
        if (!artist || !artist.name) return;
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'text-sm px-2.5 py-1 rounded-full border border-[#2a2a3a] bg-[#16161f] text-[#b7b2ce] hover:border-purple-400 hover:text-purple-300 transition-colors';
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
    if (!SPOTIFY_ENABLED) {
        return;
    }

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

function addTag(inputId, listId, name, max) {
    const input = document.getElementById(inputId);
    if (!input) return false;

    const val = input.value.trim();
    const added = addTagValue(val, listId, name, max);
    if (added) input.value = '';
    return added;
}

function addTagValue(val, listId, name, max) {
    const list = document.getElementById(listId);
    if (!list) return false;
    if (!val) return false;
    if (tagCount(listId) >= max) return false;

    const exists = Array.from(list.querySelectorAll('[data-tag]')).some((el) =>
        (el.dataset.tag || '').toLowerCase() === val.toLowerCase()
    );
    if (exists) {
        return false;
    }

    const span = document.createElement('span');
    span.className = listId === 'interestsList' ? 'interest-tag interest-tag-selected' : 'interest-tag';
    span.dataset.tag = val;
    span.innerHTML = `${escapeHtml(val)} <button type="button" class="text-[#8d87b6] hover:text-red-400 ml-1" onclick="removeTag(this.parentElement)">X</button><input type="hidden" name="${name}" value="${escapeAttr(val)}">`;
    list.appendChild(span);
    return true;
}

function updatePhotoHint() {
    const hint = document.getElementById('photoHint');
    if (!hint) return;
    hint.textContent = PHOTO_HINT_TEMPLATE.replace('__COUNT__', String(photoCount));
}

function updateInterestHint() {
    const hint = document.getElementById('interestHint');
    if (!hint) return;
    hint.textContent = INTEREST_HINT_TEMPLATE.replace('__COUNT__', String(tagCount('interestsList')));
}

function refreshInterestSuggestionState() {
    const picked = new Set(
        Array.from(document.querySelectorAll('#interestsList [data-tag]'))
            .map((el) => String(el.dataset.tag || '').toLowerCase())
    );

    document.querySelectorAll('.onb-suggestion').forEach((btn) => {
        const val = String(btn.dataset.interest || '').toLowerCase();
        const active = picked.has(val);
        btn.classList.toggle('border-purple-500', active);
        btn.classList.toggle('text-purple-300', active);
        btn.classList.toggle('bg-purple-500/10', active);
    });
}

async function uploadPhoto(event) {
    const file = event.target.files[0];
    event.target.value = '';
    if (!file) return;

    const fd = new FormData();
    fd.append('photo', file);
    fd.append('_token', CSRF);

    try {
        const response = await fetch('/profile/photo', { method: 'POST', body: fd });
        const data = await response.json();

        if (!response.ok || data.error) {
            setWizardInfo(data.error || NEED_PHOTO, true);
            return;
        }

        appendPhoto(data.id, data.url);
        photoCount += 1;
        updatePhotoHint();
        setWizardInfo('');
    } catch (e) {
        setWizardInfo('Upload failed.', true);
    }
}

function appendPhoto(id, url) {
    const grid = document.getElementById('photoGrid');
    if (!grid) return;

    const card = document.createElement('div');
    card.className = 'aspect-square rounded-lg border border-[#2a2a3a] overflow-hidden relative group';
    card.dataset.photoId = String(id);
    card.innerHTML = `<img src="${escapeAttr(url)}" class="w-full h-full object-cover"><button type="button" onclick="deletePhoto(${id}, this)" class="absolute top-1 right-1 w-5 h-5 bg-red-600/90 rounded-full hidden group-hover:flex items-center justify-center text-white text-xs border-none cursor-pointer">X</button>`;
    grid.appendChild(card);
}

async function deletePhoto(id, btn) {
    try {
        const response = await fetch(`/profile/photo/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF },
        });

        if (!response.ok) return;
        const card = btn.closest('[data-photo-id]');
        if (card) card.remove();
        photoCount = Math.max(0, photoCount - 1);
        updatePhotoHint();
    } catch (e) {}
}

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

const sliderViewport = onbTrack.parentElement;
let touchStartX = 0;

sliderViewport.addEventListener('touchstart', (event) => {
    const target = event.target instanceof Element ? event.target : null;
    if (target && target.closest('input, textarea, button')) return;
    touchStartX = event.changedTouches[0].clientX;
}, { passive: true });

sliderViewport.addEventListener('touchend', (event) => {
    const target = event.target instanceof Element ? event.target : null;
    if (target && target.closest('input, textarea')) return;
    const endX = event.changedTouches[0].clientX;
    const delta = endX - touchStartX;
    if (Math.abs(delta) < 45) return;
    if (delta < 0) nextStep();
    else prevStep();
}, { passive: true });

document.addEventListener('keydown', (event) => {
    const tag = event.target?.tagName;
    if (tag === 'INPUT' || tag === 'TEXTAREA') return;
    if (event.key === 'ArrowRight') nextStep();
    if (event.key === 'ArrowLeft') prevStep();
});

showStep(START_STEP, { immediate: true });
refreshInterestSuggestionState();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\malin\Desktop\altermatch\resources\views\onboarding\index.blade.php ENDPATH**/ ?>