// chat.js - async chat with emoji, GIF messages and reactions
let activeMatchId = null;
let pollInterval = null;
let reactPickerBound = false;
let gifSearchTimer = null;
let gifSearchRequestId = 0;
let currentMessages = [];
let lastLoadedMessageId = 0;
let lastReadOwnMessageId = 0;
let isLoadingMessages = false;
let pollCycle = 0;

const CHAT_EMOJIS = ['😀', '😁', '😂', '😊', '😍', '😘', '🤔', '😎', '😭', '😡', '🥳', '🔥', '🖤', '💜', '✨', '👍'];
const REACTION_EMOJIS = ['❤️', '🔥', '😂', '😍', '😮', '😭', '😡', '👍', '👎', '✨'];
const GIF_FALLBACK_LIBRARY = [
    { url: 'https://media.giphy.com/media/3o7aD2saalBwwftBIY/giphy.gif', tags: ['hello', 'wave', 'hi'] },
    { url: 'https://media.giphy.com/media/ICOgUNjpvO0PC/giphy.gif', tags: ['happy', 'dance', 'fun'] },
    { url: 'https://media.giphy.com/media/26u4lOMA8JKSnL9Uk/giphy.gif', tags: ['lol', 'laugh', 'funny'] },
    { url: 'https://media.giphy.com/media/l3q2K5jinAlChoCLS/giphy.gif', tags: ['wow', 'surprised'] },
    { url: 'https://media.giphy.com/media/3oriO0OEd9QIDdllqo/giphy.gif', tags: ['love', 'heart'] },
    { url: 'https://media.giphy.com/media/l0MYt5jPR6QX5pnqM/giphy.gif', tags: ['yes', 'ok', 'agree'] },
    { url: 'https://media.giphy.com/media/3oEjI6SIIHBdRxXI40/giphy.gif', tags: ['no', 'nope', 'disagree'] },
    { url: 'https://media.giphy.com/media/xT0xeJpnrWC4XWblEk/giphy.gif', tags: ['clap', 'nice', 'good'] },
    { url: 'https://media.giphy.com/media/26ufdipQqU2lhNA4g/giphy.gif', tags: ['coffee', 'morning'] },
    { url: 'https://media.giphy.com/media/3ohs4BSacFKI7A717y/giphy.gif', tags: ['party', 'celebrate'] },
    { url: 'https://media.giphy.com/media/l0HU7JIx5T9Z5b8aQ/giphy.gif', tags: ['cry', 'sad'] },
    { url: 'https://media.giphy.com/media/l4FGuhL4U2WyjdkaY/giphy.gif', tags: ['angry', 'mad'] },
    { url: 'https://media.giphy.com/media/3o7TKTDn976rzVgky4/giphy.gif', tags: ['thinking', 'hmm'] },
    { url: 'https://media.giphy.com/media/xT9IgG50Fb7Mi0prBC/giphy.gif', tags: ['kiss', 'love'] },
    { url: 'https://media.giphy.com/media/26FPpSuhgHvYo9Kyk/giphy.gif', tags: ['cat', 'cute'] },
    { url: 'https://media.giphy.com/media/3o6ZtaO9BZHcOjmErm/giphy.gif', tags: ['dog', 'cute'] },
    { url: 'https://media.giphy.com/media/26u4cqiYI30juCOGY/giphy.gif', tags: ['music', 'dance'] },
    { url: 'https://media.giphy.com/media/l2JHPB58MjfV8W3K0/giphy.gif', tags: ['sleep', 'night'] },
];
const LAST_CHAT_STORAGE_KEY = 'altermatch:last_chat_match_id';

function toInt(value) {
    const parsed = parseInt(String(value ?? ''), 10);
    return Number.isInteger(parsed) ? parsed : null;
}

function buildAnimateIdSet(ids) {
    const set = new Set();
    if (!Array.isArray(ids)) return set;
    ids.forEach((id) => {
        const parsed = toInt(id);
        if (parsed) set.add(parsed);
    });
    return set;
}

function getChatItemByMatchId(matchId) {
    return document.querySelector(`.chat-item[data-match="${matchId}"]`);
}

function getStoredMatchId() {
    try {
        return toInt(localStorage.getItem(LAST_CHAT_STORAGE_KEY));
    } catch (e) {
        return null;
    }
}

function saveStoredMatchId(matchId) {
    try {
        localStorage.setItem(LAST_CHAT_STORAGE_KEY, String(matchId));
    } catch (e) {
        // Ignore storage errors (private mode, quota, etc.)
    }
}

function syncMatchInUrl(matchId) {
    try {
        const url = new URL(window.location.href);
        url.searchParams.set('match', String(matchId));
        window.history.replaceState({}, '', `${url.pathname}${url.search}`);
    } catch (e) {
        // Ignore URL API errors.
    }
}

async function openChatFromSidebarItem(item) {
    if (!(item instanceof HTMLElement)) return;
    const matchId = toInt(item.dataset.match);
    if (!matchId) return;
    const name = item.dataset.name || '';
    const avatar = item.dataset.avatar || '';
    await openChat(matchId, name, avatar);
}

async function openChat(matchId, name, avatar) {
    const parsedMatchId = toInt(matchId);
    if (!parsedMatchId) return;
    activeMatchId = parsedMatchId;
    saveStoredMatchId(parsedMatchId);
    syncMatchInUrl(parsedMatchId);

    document.querySelectorAll('.chat-item').forEach((el) => {
        const isActive = parseInt(el.dataset.match, 10) === parsedMatchId;
        el.classList.toggle('bg-[#16161f]', isActive);
        el.classList.toggle('border-l-2', isActive);
        el.classList.toggle('border-l-purple-500', isActive);
        if (isActive) {
            el.dataset.selected = '1';
            el.querySelector('.chat-unread-dot')?.remove();
        } else {
            el.dataset.selected = '0';
        }
    });

    const safeName = escHtml(name);
    const safeAvatar = avatar ? escAttr(avatar) : '';
    currentMessages = [];
    lastLoadedMessageId = 0;
    lastReadOwnMessageId = 0;
    pollCycle = 0;
    const main = document.getElementById('chatMain');
    main.innerHTML = `
        <div class="px-5 py-3 border-b border-[#2a2a3a] bg-[#111118] flex items-center gap-3 flex-shrink-0 chat-head-enter">
            <div class="w-9 h-9 rounded-full border-2 border-purple-400 overflow-hidden flex items-center justify-center bg-gradient-to-br from-purple-900 to-blue-900 shadow-[0_0_10px_rgba(168,85,247,.3)]">
                ${safeAvatar ? `<img src="${safeAvatar}" class="w-full h-full object-cover">` : '🖤'}
            </div>
            <div>
                <div class="font-cinzel text-sm font-semibold">${safeName}</div>
                <div class="text-xs text-emerald-400 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse inline-block"></span>
                    ${TRANS.online}
                </div>
            </div>
        </div>
        <div class="flex-1 px-6 py-4 overflow-y-auto flex flex-col gap-3 scrollbar-thin chat-body-enter" id="msgs">
            <div class="self-center text-xs text-[#5e5880] bg-[#111118] border border-[#2a2a3a] px-4 py-1.5 rounded-full">${TRANS.matched}</div>
        </div>
        <div class="px-5 py-3 border-t border-[#2a2a3a] bg-[#111118] flex-shrink-0 chat-composer-enter">
            <div class="relative">
                <div id="emojiPanel" class="hidden absolute bottom-14 left-0 bg-[#111118] border border-[#2a2a3a] rounded-xl p-2 w-56 z-30 shadow-2xl">
                    <div class="grid grid-cols-8 gap-1">
                        ${CHAT_EMOJIS.map((emoji) => `<button type="button" class="p-1 rounded hover:bg-[#16161f] text-base" onclick="insertEmoji('${encodeURIComponent(emoji)}')">${emoji}</button>`).join('')}
                    </div>
                </div>
                <div id="gifPanel" class="hidden absolute bottom-14 left-12 bg-[#111118] border border-[#2a2a3a] rounded-xl p-2 w-72 z-30 shadow-2xl">
                    <input id="gifSearch" class="w-full mb-2 bg-[#16161f] border border-[#2a2a3a] rounded-lg px-3 py-2 text-[#e2e0f0] text-xs outline-none focus:border-purple-500" placeholder="${TRANS.search_gif}">
                    <div id="gifGrid" class="grid grid-cols-2 gap-2 max-h-56 overflow-y-auto scrollbar-thin"></div>
                </div>
                <div class="flex gap-2 items-center">
                    <button id="emojiBtn" type="button" onclick="toggleEmojiPanel()" class="w-10 h-10 rounded-full border border-[#2a2a3a] bg-[#16161f] text-lg hover:border-purple-400 transition-colors" title="${TRANS.emoji}">😀</button>
                    <button id="gifBtn" type="button" onclick="toggleGifPanel()" class="px-3 h-10 rounded-full border border-[#2a2a3a] bg-[#16161f] text-xs font-mono tracking-wider text-[#9991bb] hover:border-purple-400 hover:text-purple-400 transition-colors" title="${TRANS.gif}">${TRANS.gif}</button>
                    <input class="flex-1 bg-[#16161f] border border-[#2a2a3a] rounded-full px-4 py-2 text-[#e2e0f0] font-crimson text-sm outline-none focus:border-purple-500 transition-colors min-w-0"
                        id="msgInp" placeholder="${TRANS.placeholder}" maxlength="1000" onkeydown="if(event.key==='Enter')sendMsg()">
                    <button onclick="sendMsg()" class="w-10 h-10 flex-shrink-0 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white hover:scale-110 transition-transform shadow-[0_0_10px_rgba(168,85,247,.3)]">➤</button>
                </div>
            </div>
        </div>
    `;

    const gifSearch = document.getElementById('gifSearch');
    if (gifSearch) {
        gifSearch.addEventListener('input', () => {
            clearTimeout(gifSearchTimer);
            gifSearchTimer = setTimeout(() => renderGifGrid(gifSearch.value), 220);
        });
    }

    await renderGifGrid('');
    bindOutsidePickerClose();
    await loadMessages(true);
    document.getElementById('msgInp')?.focus();

    clearInterval(pollInterval);
    pollInterval = setInterval(() => {
        pollCycle += 1;
        const forceFullSync = pollCycle % 4 === 0;
        loadMessages(forceFullSync);
    }, 1500);
}

async function loadMessages(forceFull = false) {
    if (!activeMatchId || isLoadingMessages) return;
    isLoadingMessages = true;
    const matchIdAtRequest = activeMatchId;
    try {
        const sinceId = !forceFull && lastLoadedMessageId > 0 ? lastLoadedMessageId : 0;
        const url = sinceId > 0
            ? `/chat/${matchIdAtRequest}/messages?since_id=${sinceId}`
            : `/chat/${matchIdAtRequest}/messages`;

        const res = await fetch(url, {
            headers: { 'X-CSRF-TOKEN': CSRF },
        });
        if (!res.ok) return;
        const payload = await res.json();
        if (matchIdAtRequest !== activeMatchId) return;
        const incomingMessages = Array.isArray(payload) ? payload : (payload.messages || []);
        const incomingLastReadOwn = Array.isArray(payload) ? 0 : toInt(payload.last_read_own_message_id) || 0;
        let shouldRender = forceFull;
        let addedMessageIds = [];

        if (forceFull || sinceId === 0) {
            currentMessages = incomingMessages;
            shouldRender = true;
        } else if (incomingMessages.length) {
            addedMessageIds = mergeIncomingMessages(incomingMessages);
            shouldRender = true;
        }

        if (incomingLastReadOwn > lastReadOwnMessageId) {
            lastReadOwnMessageId = incomingLastReadOwn;
            markOwnMessagesAsRead(lastReadOwnMessageId);
            shouldRender = true;
        }

        if (currentMessages.length) {
            lastLoadedMessageId = currentMessages[currentMessages.length - 1].id;
        }

        if (shouldRender) {
            renderMessages(currentMessages, { animateIds: buildAnimateIdSet(addedMessageIds) });
        }
    } catch (e) {
        console.error('Load messages error', e);
    } finally {
        isLoadingMessages = false;
    }
}

function renderMessages(messages, options = {}) {
    const container = document.getElementById('msgs');
    if (!container) return;
    const wasAtBottom = container.scrollTop + container.clientHeight >= container.scrollHeight - 10;
    const animateIds = options.animateIds instanceof Set ? options.animateIds : new Set();

    container.innerHTML = `<div class="self-center text-xs text-[#5e5880] bg-[#111118] border border-[#2a2a3a] px-4 py-1.5 rounded-full">${TRANS.matched}</div>`;

    messages.forEach((msg) => {
        const row = document.createElement('div');
        const shouldAnimate = animateIds.has(msg.id);
        row.className = `msg-row ${msg.mine ? 'mine' : 'theirs'}${shouldAnimate ? ' msg-enter' : ''}`;

        const avHtml = msg.mine
            ? (MY_AVATAR ? `<img src="${escAttr(MY_AVATAR)}" class="w-full h-full object-cover">` : `<span class="text-xs font-bold">${escHtml(MY_INITIAL)}</span>`)
            : (msg.avatar ? `<img src="${escAttr(msg.avatar)}" class="w-full h-full object-cover">` : '🖤');

        const isGif = msg.type === 'gif' && !!msg.gif_url;
        const bubbleClass = isGif ? 'msg-bubble msg-bubble-gif' : 'msg-bubble';
        const bodyHtml = isGif
            ? `<img src="${escAttr(msg.gif_url)}" class="msg-gif" loading="lazy" alt="gif">`
            : escHtml(msg.body ?? '');
        const mineStatus = msg.mine ? (msg.is_read ? TRANS.read : TRANS.sent) : '';
        const timeLabel = msg.mine ? `${msg.time} | ${mineStatus}` : msg.time;

        row.innerHTML = `
            <div class="msg-av">${avHtml}</div>
            <div>
                <div class="${bubbleClass}">${bodyHtml}</div>
                <div class="msg-reacts">
                    ${(msg.reactions || []).map((r) => `
                        <button type="button" class="msg-react ${r.mine ? 'mine' : ''}" onclick="reactMsg(${msg.id}, '${encodeURIComponent(r.emoji)}')">
                            <span>${escHtml(r.emoji)}</span>
                            <span>${r.count}</span>
                        </button>
                    `).join('')}
                    <button type="button" class="msg-react-add" onclick="toggleReactionPicker(${msg.id}, this)" title="${TRANS.react}">
                        +
                    </button>
                </div>
                <div class="msg-time ${msg.mine ? 'text-right' : ''}">${timeLabel}</div>
            </div>
        `;
        container.appendChild(row);
    });

    if (wasAtBottom) container.scrollTop = container.scrollHeight;
}

function mergeIncomingMessages(incomingMessages) {
    const knownIds = new Set(currentMessages.map((msg) => msg.id));
    const addedIds = [];
    incomingMessages.forEach((msg) => {
        if (knownIds.has(msg.id)) return;
        currentMessages.push(msg);
        if (toInt(msg.id)) addedIds.push(msg.id);
    });
    currentMessages.sort((a, b) => a.id - b.id);
    return addedIds;
}

function markOwnMessagesAsRead(lastReadId) {
    currentMessages = currentMessages.map((msg) => {
        if (msg.mine && msg.id <= lastReadId && !msg.is_read) {
            return { ...msg, is_read: true };
        }
        return msg;
    });
}

async function sendMsg() {
    const inp = document.getElementById('msgInp');
    if (!inp || !activeMatchId) return;
    const matchIdAtRequest = activeMatchId;
    const body = inp.value.trim();
    if (!body) return;
    inp.value = '';
    closeComposerPanels();

    try {
        const res = await fetch(`/chat/${matchIdAtRequest}/messages`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ body, type: 'text' }),
        });
        if (!res.ok) {
            inp.value = body;
            return;
        }

        const created = await res.json();
        if (matchIdAtRequest !== activeMatchId) return;
        created.is_read = false;
        created.reactions = created.reactions || [];
        const addedIds = mergeIncomingMessages([created]);
        lastLoadedMessageId = Math.max(lastLoadedMessageId, created.id || 0);
        renderMessages(currentMessages, { animateIds: buildAnimateIdSet(addedIds) });
    } catch (e) {
        inp.value = body;
        console.error('Send error', e);
    }
}

async function sendGif(encodedUrl) {
    if (!activeMatchId) return;
    const matchIdAtRequest = activeMatchId;
    const gifUrl = decodeURIComponent(encodedUrl || '');
    if (!gifUrl) return;
    closeComposerPanels();

    try {
        const res = await fetch(`/chat/${matchIdAtRequest}/messages`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ type: 'gif', gif_url: gifUrl }),
        });
        if (!res.ok) {
            const data = await res.json().catch(() => ({}));
            console.error('Send GIF failed', data);
            return;
        }

        const created = await res.json();
        if (matchIdAtRequest !== activeMatchId) return;
        created.is_read = false;
        created.reactions = created.reactions || [];
        const addedIds = mergeIncomingMessages([created]);
        lastLoadedMessageId = Math.max(lastLoadedMessageId, created.id || 0);
        renderMessages(currentMessages, { animateIds: buildAnimateIdSet(addedIds) });
    } catch (e) {
        console.error('Send GIF error', e);
    }
}

async function reactMsg(messageId, encodedEmoji) {
    if (!activeMatchId) return;
    const matchIdAtRequest = activeMatchId;
    const emoji = decodeURIComponent(encodedEmoji || '');
    if (!emoji) return;
    hideReactionPicker();

    try {
        const res = await fetch(`/chat/${matchIdAtRequest}/messages/${messageId}/react`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ emoji }),
        });
        if (!res.ok) return;

        const data = await res.json().catch(() => null);
        if (matchIdAtRequest !== activeMatchId) return;
        if (!data || !data.message_id) return;

        currentMessages = currentMessages.map((msg) => {
            if (msg.id === data.message_id) {
                return { ...msg, reactions: data.reactions || [] };
            }
            return msg;
        });
        renderMessages(currentMessages);
        setTimeout(() => loadMessages(true), 250);
    } catch (e) {
        console.error('Reaction error', e);
    }
}

function toggleEmojiPanel() {
    const panel = document.getElementById('emojiPanel');
    const gifPanel = document.getElementById('gifPanel');
    if (!panel) return;
    if (gifPanel) gifPanel.classList.add('hidden');
    panel.classList.toggle('hidden');
}

function insertEmoji(encodedEmoji) {
    const inp = document.getElementById('msgInp');
    if (!inp) return;
    inp.value += decodeURIComponent(encodedEmoji || '');
    inp.focus();
}

function toggleGifPanel() {
    const panel = document.getElementById('gifPanel');
    const emojiPanel = document.getElementById('emojiPanel');
    if (!panel) return;
    if (emojiPanel) emojiPanel.classList.add('hidden');
    panel.classList.toggle('hidden');

    if (!panel.classList.contains('hidden')) {
        const q = document.getElementById('gifSearch')?.value || '';
        renderGifGrid(q);
    }
}

async function renderGifGrid(query) {
    const grid = document.getElementById('gifGrid');
    if (!grid) return;

    const requestId = ++gifSearchRequestId;
    grid.innerHTML = `<div class="col-span-2 text-xs text-[#5e5880] py-2 text-center">...</div>`;
    const items = await fetchGifResults(query);

    if (requestId !== gifSearchRequestId) return;
    if (!items.length) {
        grid.innerHTML = `<div class="col-span-2 text-xs text-[#5e5880] py-2 text-center">${TRANS.no_gifs}</div>`;
        return;
    }

    grid.innerHTML = items.map((gif) => `
        <button type="button" class="rounded-lg overflow-hidden border border-[#2a2a3a] hover:border-purple-400 transition-colors" onclick="sendGif('${encodeURIComponent(gif.url)}')">
            <img src="${escAttr(gif.url)}" class="w-full h-20 object-cover" loading="lazy" alt="gif">
        </button>
    `).join('');
}

async function fetchGifResults(query) {
    const q = String(query || '').trim();
    try {
        const res = await fetch(`/chat/gifs/search?q=${encodeURIComponent(q)}&limit=18`, {
            headers: { 'X-CSRF-TOKEN': CSRF },
        });
        if (res.ok) {
            const urls = await res.json();
            if (Array.isArray(urls) && urls.length) {
                return urls
                    .filter((url) => typeof url === 'string' && url.length > 0)
                    .map((url) => ({ url }));
            }
        }
    } catch (e) {
        console.warn('GIF API fallback', e);
    }

    const ql = q.toLowerCase();
    return GIF_FALLBACK_LIBRARY.filter((gif) => !ql || gif.tags.some((tag) => tag.includes(ql)));
}

function closeComposerPanels() {
    const emojiPanel = document.getElementById('emojiPanel');
    const gifPanel = document.getElementById('gifPanel');
    if (emojiPanel) emojiPanel.classList.add('hidden');
    if (gifPanel) gifPanel.classList.add('hidden');
}

function ensureReactionPicker() {
    let picker = document.getElementById('reactPicker');
    if (picker) return picker;

    picker = document.createElement('div');
    picker.id = 'reactPicker';
    picker.className = 'hidden fixed bg-[#111118] border border-[#2a2a3a] rounded-xl p-1.5 z-[900] shadow-2xl';
    document.body.appendChild(picker);
    return picker;
}

function toggleReactionPicker(messageId, anchor) {
    const picker = ensureReactionPicker();
    if (!picker || !anchor) return;

    picker.dataset.messageId = String(messageId);
    picker.innerHTML = REACTION_EMOJIS.map((emoji) => `
        <button type="button" class="w-8 h-8 rounded-lg hover:bg-[#16161f] transition-colors text-base" onclick="pickReaction('${encodeURIComponent(emoji)}')">
            ${emoji}
        </button>
    `).join('');

    const rect = anchor.getBoundingClientRect();
    const pickerWidth = Math.min(320, REACTION_EMOJIS.length * 34);
    let left = rect.left - pickerWidth / 2 + rect.width / 2;
    left = Math.max(8, Math.min(window.innerWidth - pickerWidth - 8, left));
    const top = Math.max(8, rect.top - 48);

    picker.style.left = `${left}px`;
    picker.style.top = `${top}px`;
    picker.classList.remove('hidden');
}

function pickReaction(encodedEmoji) {
    const picker = document.getElementById('reactPicker');
    if (!picker) return;
    const messageId = parseInt(picker.dataset.messageId || '', 10);
    if (!Number.isInteger(messageId)) return;
    reactMsg(messageId, encodedEmoji);
}

function hideReactionPicker() {
    const picker = document.getElementById('reactPicker');
    if (picker) picker.classList.add('hidden');
}

function bindOutsidePickerClose() {
    if (reactPickerBound) return;
    reactPickerBound = true;

    document.addEventListener('click', (e) => {
        const target = e.target;
        if (!(target instanceof Element)) return;

        const insideComposerPanels = target.closest('#emojiPanel, #gifPanel, #emojiBtn, #gifBtn, #gifSearch');
        if (!insideComposerPanels) closeComposerPanels();

        const insideReactionPicker = target.closest('#reactPicker') || target.closest('.msg-react-add');
        if (!insideReactionPicker) hideReactionPicker();
    });
}

async function openChatByMatchId(matchId) {
    const item = getChatItemByMatchId(matchId);
    if (!item) return false;
    await openChatFromSidebarItem(item);
    return true;
}

function getRequestedMatchId() {
    const serverValue = typeof INITIAL_MATCH_ID === 'undefined' ? null : INITIAL_MATCH_ID;
    const requestedFromServer = toInt(serverValue);
    if (requestedFromServer) return requestedFromServer;

    const queryValue = new URLSearchParams(window.location.search).get('match');
    return toInt(queryValue);
}

async function bootstrapInitialChat() {
    const requestedMatchId = getRequestedMatchId();
    if (requestedMatchId && await openChatByMatchId(requestedMatchId)) return;

    const storedMatchId = getStoredMatchId();
    if (storedMatchId && await openChatByMatchId(storedMatchId)) return;

    const selectedItem = document.querySelector('.chat-item[data-selected="1"]');
    if (selectedItem) {
        await openChatFromSidebarItem(selectedItem);
        return;
    }

    const firstItem = document.querySelector('.chat-item');
    if (firstItem) {
        await openChatFromSidebarItem(firstItem);
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootstrapInitialChat);
} else {
    bootstrapInitialChat();
}

window.addEventListener('beforeunload', () => {
    if (pollInterval) clearInterval(pollInterval);
});

function escHtml(s) {
    return String(s)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}

function escAttr(s) {
    return String(s)
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}
