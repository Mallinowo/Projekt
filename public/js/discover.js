// discover.js - Swipe logic + async API calls
let queue = [...PROFILES];
let dragging = false, startX = 0, startY = 0, curCard = null;
let matchedData = null;
let matchPollInterval = null;
let isMatchPollRunning = false;
const knownMatchIds = new Set();
const pendingMatchPopups = [];
let latestKnownMatchId = 0;
let matchFxRunId = 0;
const FLY_OUT_MS = 240;
const STACK_ANIM_MS = 220;
const REDUCED_MOTION = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

function subIco(s) {
    return {
        emo: '&#x1F5A4;',
        scene: '&#x1F3AC;',
        goth: '&#x1F987;',
        punk: '&#x26A1;',
        metalhead: '&#x1F918;'
    }[s] || '&#x2726;';
}

function toInt(value) {
    const parsed = parseInt(String(value ?? ''), 10);
    return Number.isInteger(parsed) ? parsed : null;
}

function formatDistanceTag(distanceKm) {
    const km = toInt(distanceKm);
    if (km === null || km < 0) return '';
    return `${TRANS.distance} ${km} ${TRANS.km}`;
}

function initKnownMatches() {
    const initial = Array.isArray(INITIAL_MATCHES) ? INITIAL_MATCHES : [];
    initial.forEach((match) => {
        const matchId = toInt(match?.match_id);
        if (!matchId) return;
        knownMatchIds.add(matchId);
        latestKnownMatchId = Math.max(latestKnownMatchId, matchId);
    });
}

function escapeHtml(s) {
    return String(s)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}

function escapeAttr(s) {
    return String(s)
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}

function updateSidebarCount() {
    const counter = document.getElementById('sbCount');
    if (!counter) return;
    const count = document.querySelectorAll('#sbList .sb-match-item').length;
    counter.textContent = String(count);
}

function hideSidebarEmptyState() {
    const empty = document.getElementById('sbEmpty');
    if (empty) empty.remove();
}

function appendMatchToSidebar(match) {
    const list = document.getElementById('sbList');
    if (!list) return;
    const matchId = toInt(match?.match_id);
    if (!matchId || list.querySelector(`.sb-match-item[data-match="${matchId}"]`)) return;

    hideSidebarEmptyState();

    const avatar = match?.avatar
        ? `<img src="${escapeAttr(match.avatar)}" class="w-full h-full object-cover">`
        : `<span class="text-sm">&#x1F5A4;</span>`;
    const lastMsg = escapeHtml(String(match?.last_msg || 'Nowe dopasowanie!')).slice(0, 42);

    const row = document.createElement('div');
    row.className = 'sb-match-item flex items-center gap-2 px-3 py-2.5 border-b border-[#2a2a3a] cursor-pointer hover:bg-[#16161f] transition-colors';
    row.dataset.match = String(matchId);
    row.onclick = () => { window.location.href = `/chat?match=${matchId}`; };
    row.innerHTML = `
        <div class="w-9 h-9 rounded-full border-2 border-[#2a2a3a] flex-shrink-0 overflow-hidden flex items-center justify-center bg-gradient-to-br from-purple-900 to-blue-900">
            ${avatar}
        </div>
        <div class="flex-1 min-w-0">
            <div class="font-mono text-xs font-bold text-[#e2e0f0] truncate">${escapeHtml(String(match?.name || ''))}</div>
            <div class="text-xs text-[#5e5880] truncate">${lastMsg}</div>
        </div>
    `;

    if (!REDUCED_MOTION) {
        row.classList.add('sb-enter');
        row.addEventListener('animationend', () => row.classList.remove('sb-enter'), { once: true });
    }

    list.prepend(row);
    updateSidebarCount();
}

function queueMatchPopup(match) {
    const popup = document.getElementById('matchPop');
    if (popup && popup.style.display === 'flex') {
        pendingMatchPopups.push(match);
        return;
    }
    showMatchPop(match);
}

function openNextPendingPopup() {
    if (!pendingMatchPopups.length) return;
    const popup = document.getElementById('matchPop');
    if (popup && popup.style.display === 'flex') return;
    const next = pendingMatchPopups.shift();
    if (next) showMatchPop(next);
}

function playMatchFxBurst() {
    const layer = document.getElementById('matchFx');
    if (!layer) return;

    const runId = ++matchFxRunId;
    layer.innerHTML = '';
    const symbols = ['&#x1F49C;', '&#x2726;', '&#x2727;', '&#x1F496;'];

    const emit = (count, minDur, maxDur) => {
        for (let i = 0; i < count; i++) {
            const particle = document.createElement('span');
            particle.className = 'mp-fx hearts';
            particle.innerHTML = symbols[Math.floor(Math.random() * symbols.length)];
            particle.style.setProperty('--x', `${Math.round((Math.random() - 0.5) * 170)}px`);
            particle.style.setProperty('--drift', `${Math.round((Math.random() - 0.5) * 120)}px`);
            particle.style.setProperty('--dur', `${(Math.random() * (maxDur - minDur) + minDur).toFixed(2)}s`);
            particle.style.setProperty('--size', `${Math.round(Math.random() * 14 + 12)}px`);
            particle.addEventListener('animationend', () => particle.remove());
            layer.appendChild(particle);
        }
    };

    emit(16, 1.1, 1.7);
    setTimeout(() => {
        if (runId !== matchFxRunId) return;
        emit(12, 1.0, 1.45);
    }, 190);
}

function animateMatchPopupOpen() {
    const popup = document.getElementById('matchPop');
    if (!popup) return;
    popup.classList.remove('is-open');
    void popup.offsetWidth;
    popup.classList.add('is-open');
    playMatchFxBurst();
}

function registerMatch(rawMatch, showPopup = true) {
    const matchId = toInt(rawMatch?.match_id);
    if (!matchId) return false;
    if (knownMatchIds.has(matchId)) return false;

    const match = {
        match_id: matchId,
        user_id: toInt(rawMatch?.user_id),
        name: String(rawMatch?.name || ''),
        avatar: rawMatch?.avatar || '',
        last_msg: rawMatch?.last_msg || '',
    };

    knownMatchIds.add(matchId);
    latestKnownMatchId = Math.max(latestKnownMatchId, matchId);
    appendMatchToSidebar(match);

    if (showPopup) queueMatchPopup(match);
    return true;
}

async function pollMatchUpdates() {
    if (isMatchPollRunning || !MATCH_UPDATES_URL) return;
    isMatchPollRunning = true;

    try {
        const url = `${MATCH_UPDATES_URL}?after_id=${latestKnownMatchId}`;
        const res = await fetch(url, {
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        });
        if (!res.ok) return;

        const data = await res.json().catch(() => null);
        const updates = Array.isArray(data?.matches) ? data.matches : [];
        updates
            .slice()
            .sort((a, b) => (toInt(a?.match_id) || 0) - (toInt(b?.match_id) || 0))
            .forEach((match) => registerMatch(match, true));

        const latestId = toInt(data?.latest_match_id);
        if (latestId) latestKnownMatchId = Math.max(latestKnownMatchId, latestId);
    } catch (err) {
        console.warn('Match updates poll error', err);
    } finally {
        isMatchPollRunning = false;
    }
}

function startMatchPolling() {
    if (!MATCH_UPDATES_URL) return;
    pollMatchUpdates();
    if (matchPollInterval) clearInterval(matchPollInterval);
    matchPollInterval = setInterval(pollMatchUpdates, 5000);
}
// ---- STACK BUILDING ----
function buildStack() {
    const stack  = document.getElementById('stack');
    const actRow = document.getElementById('actRow');
    [...stack.children].forEach(c => { if(c !== actRow) c.remove(); });

    if(!queue.length) { showNoMore(); return; }

    const slice = queue.slice(0, 3);
    // Insert from lowest depth (2) to highest (0).
    // Each card is inserted before actRow, then z-index handles visual stacking.
    for(let i = slice.length - 1; i >= 0; i--) {
        const card = makeCard(slice[i], i);
        stack.insertBefore(card, actRow);
    }

    updateIP(queue[0]);
    bindDrag(getTopCard());
}

function updateStackDepth() {
    const cards = document.querySelectorAll('.swipe-card');

    cards.forEach((card, index) => {
        card.dataset.depth = index;
        applyDepthStyle(card, index, false);
    });
}

function getTopCard() {
    return document.querySelector('#stack .swipe-card[data-depth="0"]');
}

function applyDepthStyle(card, depth, animate = true) {

    const scale = 1 - depth * 0.06;
    const translateY = depth * 14;
    const opacity = depth > 2 ? 0 : 1;

    card.style.zIndex = 100 - depth;
    card.style.opacity = opacity;
    card.style.transform = `translateY(${translateY}px) scale(${scale})`;

    if (animate) {
        card.style.transition = `transform ${STACK_ANIM_MS}ms ease, opacity ${STACK_ANIM_MS}ms ease`;
    } else {
        card.style.transition = "none";
    }
}

function makeCard(p, depth) {
    const div = document.createElement('div');
    div.className = 'swipe-card';
    div.dataset.pid   = p.id;
    div.dataset.depth = depth;
    applyDepthStyle(div, depth, false);
    applyCardEnterAnimation(div, depth);

    const hasImg = p.photos && p.photos.length > 0;
    const dots   = hasImg && p.photos.length > 1
        ? `<div class="ph-dots">${p.photos.map((_,i) => `<div class="ph-dot${i===0?' on':''}"></div>`).join('')}</div>`
        : '';

    let photoHtml;
    if(hasImg)      
        photoHtml = `<img src="${p.photos[0]}" style="width:100%;height:100%;object-fit:cover;" draggable="false">`;
    else if
        (p.avatar) photoHtml = `<img src="${p.avatar}" style="width:100%;height:100%;object-fit:cover" draggable="false">`;
    else              
        photoHtml = `<span style="pointer-events:none;font-size:5rem">${subIco(p.subculture)}</span>`;

    const distanceTag = formatDistanceTag(p.distance_km);
    const distanceTagHtml = distanceTag
        ? `<span class="ctag ctag-loc">${escapeHtml(distanceTag)}</span>`
        : '';

    div.innerHTML = `
        <div class="card-ph" data-photos='${JSON.stringify(p.photos||[])}' data-idx="0">
            ${photoHtml}${dots}
            <div class="card-grad"></div>
            <div class="card-info">
                <div class="card-nm">${escapeHtml(String(p.name || ''))}</div>
                <div class="card-tags">
                    <span class="ctag ctag-loc">${TRANS.loc} ${escapeHtml(String(p.city || ''))}</span>
                    ${distanceTagHtml}
                    <span class="ctag ctag-age">${escapeHtml(String(p.age || ''))} ${TRANS.years}</span>
                    <span class="ctag ctag-sub">${subIco(p.subculture)} ${escapeHtml(String(p.subculture || ''))}</span>
                </div>
            </div>
        </div>
        <div class="stamp s-nope">NOPE</div>
        <div class="stamp s-yep">YEP!</div>
        <div class="stamp s-super">SUPER &#x2B50;</div>
    `;

    const ph = div.querySelector('.card-ph');
    let clickStartX = 0;
    ph.addEventListener('mousedown',  e => { clickStartX = e.clientX; });
    ph.addEventListener('touchstart', e => { clickStartX = e.touches[0].clientX; }, {passive:true});
    ph.addEventListener('click', e => {
        if(Math.abs(e.clientX - clickStartX) > 8) return;
        const photos = JSON.parse(ph.dataset.photos || '[]');
        if(photos.length < 2) return;
        let idx = parseInt(ph.dataset.idx || '0');
        idx = (e.clientX - ph.getBoundingClientRect().left < ph.offsetWidth / 2)
            ? (idx - 1 + photos.length) % photos.length
            : (idx + 1) % photos.length;
        ph.dataset.idx = idx;
        const img = ph.querySelector('img');
        if(img) img.src = photos[idx];
        ph.querySelectorAll('.ph-dot').forEach((d,i) => d.classList.toggle('on', i === idx));
    });

    return div;
}

function applyCardEnterAnimation(card, depth = 0) {
    if (!card || REDUCED_MOTION) return;

    card.classList.remove('discover-card-enter');
    card.style.setProperty('--enter-delay', `${Math.min(depth * 46, 140)}ms`);
    void card.offsetWidth;
    card.classList.add('discover-card-enter');

    card.addEventListener('animationend', () => {
        card.classList.remove('discover-card-enter');
    }, { once: true });
}

// ---- INFO PANEL ----
function updateIP(p) {
    if(!p) {
        document.getElementById('ipName').textContent = '-';
        document.getElementById('ipTags').innerHTML   = '';
        document.getElementById('ipBio').textContent  = '';
        document.getElementById('ipInt').innerHTML    = '';
        document.getElementById('ipArt').innerHTML    = '';
        animateInfoPanel();
        return;
    }
    document.getElementById('ipName').textContent = p.name;
    const distanceTag = formatDistanceTag(p.distance_km);
    const distanceTagHtml = distanceTag
        ? `<span class="ctag ctag-loc">${escapeHtml(distanceTag)}</span>`
        : '';
    document.getElementById('ipTags').innerHTML   = `
        <span class="ctag ctag-loc">${TRANS.loc} ${escapeHtml(String(p.city || ''))}</span>
        ${distanceTagHtml}
        <span class="ctag ctag-age">${escapeHtml(String(p.age || ''))} ${TRANS.years}</span>
        <span class="ctag ctag-sub">${subIco(p.subculture)} ${escapeHtml(String(p.subculture || ''))}</span>`;
    document.getElementById('ipBio').textContent = p.bio || '';
    document.getElementById('ipInt').innerHTML   = (p.interests||[]).map(i => `<span class="itag">${i}</span>`).join('');
    document.getElementById('ipArt').innerHTML   = (p.artists||[]).map(a =>
        `<div class="flex items-center gap-2 py-1">
            <div class="w-6 h-6 rounded bg-gradient-to-br from-purple-900 to-blue-900 flex items-center justify-center text-xs border border-[#2a2a3a]">&#x1F3B5;</div>
            <span class="text-sm text-[#9991bb]">${a}</span>
        </div>`).join('');
    animateInfoPanel();
}

function animateInfoPanel() {
    const panel = document.getElementById('ipPanel');
    if (!panel || REDUCED_MOTION) return;

    panel.classList.remove('ip-panel-anim');
    void panel.offsetWidth;
    panel.classList.add('ip-panel-anim');
}

function showNoMore() {
    const stack  = document.getElementById('stack');
    const actRow = document.getElementById('actRow');
    const el     = document.createElement('div');
    el.className = 'no-more-enter absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-center text-[#5e5880]';
    el.innerHTML = `<div class="text-5xl mb-3">&#x1F5A4;</div>
        <div class="font-cinzel text-sm text-[#9991bb]">${TRANS.no_more}</div>
        <p class="text-xs mt-1">${TRANS.come_back}</p>`;
    stack.insertBefore(el, actRow);
    updateIP(null);
}

// ---- DRAG ----
function bindDrag(card) {
    if(!card) return;
    if(card._ds) {
        card.removeEventListener('mousedown',  card._ds);
        card.removeEventListener('touchstart', card._ds);
    }
    card._ds = e => onDragStart(e, card);
    card.addEventListener('mousedown',  card._ds);
    card.addEventListener('touchstart', card._ds, {passive:true});
}

function onDragStart(e, card) {
    if(dragging) return;
    dragging = true;
    curCard  = card;
    const pt = e.touches ? e.touches[0] : e;
    startX   = pt.clientX;
    startY   = pt.clientY;
    curCard.style.transition = 'none';
    document.addEventListener('mousemove', onDragMove);
    document.addEventListener('mouseup',   onDragEnd);
    document.addEventListener('touchmove', onDragMove, {passive:false});
    document.addEventListener('touchend',  onDragEnd);
}

function onDragMove(e) {
    if(!dragging || !curCard) return;
    if(e.cancelable) e.preventDefault();
    const pt = e.touches ? e.touches[0] : e;
    const dx = pt.clientX - startX;
    const dy = pt.clientY - startY;
    curCard.style.transform = `translate(${dx}px,${dy}px) rotate(${dx * .07}deg)`;
    curCard.querySelector('.s-nope').style.opacity  = dx < -25 ? Math.min(1, Math.abs(dx)/80) : 0;
    curCard.querySelector('.s-yep').style.opacity   = dx >  25 ? Math.min(1, dx/80)           : 0;
    curCard.querySelector('.s-super').style.opacity = dy < -30 ? Math.min(1, Math.abs(dy)/60) : 0;
}

function onDragEnd(e) {
    if(!dragging || !curCard) return;
    dragging = false;
    document.removeEventListener('mousemove', onDragMove);
    document.removeEventListener('mouseup',   onDragEnd);
    document.removeEventListener('touchmove', onDragMove);
    document.removeEventListener('touchend',  onDragEnd);

    const pt = e.changedTouches ? e.changedTouches[0] : e;
    const dx = pt.clientX - startX;
    const dy = pt.clientY - startY;

    if     (dx < -85) { pulseActionButton('left'); flyOut(curCard, 'left');  curCard = null; }
    else if(dx >  85) { pulseActionButton('right'); flyOut(curCard, 'right'); curCard = null; }
    else if(dy < -85) { pulseActionButton('up'); flyOut(curCard, 'up');    curCard = null; }
    else {
        const c = curCard; curCard = null;
        c.style.transition = 'transform .35s cubic-bezier(.25,.46,.45,.94)';
        applyDepthStyle(c, 0, false);
        ['.s-nope','.s-yep','.s-super'].forEach(s => c.querySelector(s).style.opacity = 0);
        setTimeout(() => { c.style.transition = 'none'; bindDrag(c); }, 360);
    }
}

function flyOut(card, dir) {
    const pid = parseInt(card.dataset.pid);
    card.style.transition    = `transform ${FLY_OUT_MS}ms ease, opacity ${FLY_OUT_MS}ms ease`;
    const coords = {left:[-700,0,-25], right:[700,0,25], up:[0,-700,0]};
    const [tx, ty, rot]      = coords[dir];
    card.style.transform     = `translate(${tx}px,${ty}px) rotate(${rot}deg)`;
    card.style.opacity       = '0';
    card.style.pointerEvents = 'none';
    setTimeout(() => { card.remove(); recordSwipe(pid, dir); }, FLY_OUT_MS + 10);
}

function doSwipe(dir) {
    const top = getTopCard();
    if(top) {
        pulseActionButton(dir);
        flyOut(top, dir);
    }
}

function pulseActionButton(dir) {
    if (REDUCED_MOTION) return;

    const selector = dir === 'left'
        ? '.act-btn.skip'
        : dir === 'right'
            ? '.act-btn.like'
            : '.act-btn.sup';

    const button = document.querySelector(selector);
    if (!button) return;

    button.classList.remove('is-hit');
    void button.offsetWidth;
    button.classList.add('is-hit');
}

// ---- AFTER SWIPE ----
async function recordSwipe(pid, dir) {
    const dirMap = {left:'dislike', right:'like', up:'superlike'};
    if(dir === 'left') showToast(TRANS.skipped);
    if(dir === 'up')   showToast(TRANS.superliked, 'acc');

    queue = queue.filter(p => p.id !== pid);
    afterSwipe();

    try {
        const res  = await fetch('/swipe', {
            method:  'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},
            body:    JSON.stringify({swiped_id: pid, direction: dirMap[dir]})
        });
        const data = await res.json();
        if(data.matched && data.match) {
            registerMatch(data.match, true);
        } else if(dir === 'right') {
            showToast(TRANS.liked, 'ok');
        }
    } catch(err) {
        console.error('Swipe error', err);
    }
}

function afterSwipe() {
    const stack    = document.getElementById('stack');
    const actRow   = document.getElementById('actRow');
    const allCards = [...stack.querySelectorAll('.swipe-card')];

    if(!allCards.length && !queue.length) { showNoMore(); return; }
    if(!allCards.length)                  { buildStack(); return; }

    // Move all cards one level up (depth--).
    allCards.forEach(c => {
        const nd      = parseInt(c.dataset.depth) - 1;
        c.dataset.depth = nd;
        applyDepthStyle(c, nd, true);
    });

    // Add a new card at stack bottom if there are still unseen profiles.
    const newDepth = allCards.length; // after removing the top card
    if(newDepth < 3 && queue.length > newDepth) {
        const nc        = makeCard(queue[newDepth], newDepth);
        // Insert before the first DOM card (lowest z-index).
        const firstCard = stack.querySelector('.swipe-card');
        if(firstCard) stack.insertBefore(nc, firstCard);
        else          stack.insertBefore(nc, actRow);
    }

    setTimeout(() => bindDrag(getTopCard()), STACK_ANIM_MS + 20);
    updateIP(queue[0] || null);
}

// ---- MATCH POPUP ----
function showMatchPop(match) {
    matchedData = match || null;
    const popup = document.getElementById('matchPop');
    if (!popup) return;

    const myAv    = document.getElementById('mpMyAv');
    const theirAv = document.getElementById('mpTheirAv');
    myAv.innerHTML    = MY_AVATAR
        ? `<img src="${MY_AVATAR}" class="w-full h-full object-cover">`
        : `<span class="text-xl font-bold">${MY_INITIAL}</span>`;
    theirAv.innerHTML = match.avatar
        ? `<img src="${match.avatar}" class="w-full h-full object-cover">`
        : `<span class="text-xl">&#x1F5A4;</span>`;
    document.getElementById('mpName').textContent     = match.name;
    popup.style.display = 'flex';
    animateMatchPopupOpen();
}
function mpClose()  {
    const popup = document.getElementById('matchPop');
    if (popup) {
        popup.classList.remove('is-open');
        popup.style.display = 'none';
    }
    const layer = document.getElementById('matchFx');
    if (layer) layer.innerHTML = '';
    setTimeout(openNextPendingPopup, 80);
}
function mpGoChat() {
    mpClose();
    const matchId = parseInt(matchedData?.match_id, 10);
    if (Number.isInteger(matchId)) {
        window.location.href = `/chat?match=${matchId}`;
        return;
    }
    window.location.href = '/chat';
}

// ---- TOAST ----
let _tt;
function showToast(msg, type) {
    const t = document.getElementById('toast');
    t.textContent       = msg;
    t.style.borderColor = '';
    t.style.color       = '';
    t.className = `fixed bottom-6 left-1/2 -translate-x-1/2 bg-[#111118] border border-[#2a2a3a] px-5 py-2 rounded-full font-mono text-xs tracking-wide z-[700] whitespace-nowrap shadow-2xl transition-all duration-300 opacity-100 translate-y-0`;
    if(type === 'ok')  { t.style.borderColor = '#10b981'; t.style.color = '#10b981'; }
    if(type === 'acc') { t.style.borderColor = '#a855f7'; t.style.color = '#a855f7'; }
    clearTimeout(_tt);
    _tt = setTimeout(() => { t.style.opacity = '0'; }, 2400);
}

// ---- KEYBOARD ----
document.addEventListener('keydown', e => {
    if(e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
    if(e.key === 'ArrowLeft')  doSwipe('left');
    if(e.key === 'ArrowRight') doSwipe('right');
    if(e.key === 'ArrowUp')    doSwipe('up');
});

// ---- INIT ----
initKnownMatches();
buildStack();
startMatchPolling();

window.addEventListener('beforeunload', () => {
    if (matchPollInterval) clearInterval(matchPollInterval);
});


