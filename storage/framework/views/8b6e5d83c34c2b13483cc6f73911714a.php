<?php $__env->startSection('title', __('nav.chat')); ?>
<?php $__env->startSection('content'); ?>
<div class="flex h-full overflow-hidden">
    
    <div class="w-60 flex-shrink-0 bg-[#111118] border-r border-[#2a2a3a] flex flex-col">
        <div class="px-4 py-3 border-b border-[#2a2a3a] font-mono text-xs tracking-widest text-purple-400 uppercase flex-shrink-0">
            <?php echo e(__('nav.chat')); ?>

        </div>
        <div class="overflow-y-auto flex-1 scrollbar-thin" id="chatList">
            <?php $__empty_1 = true; $__currentLoopData = $matches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="flex items-center gap-3 px-4 py-3 border-b border-[#2a2a3a] cursor-pointer hover:bg-[#16161f] transition-colors chat-item <?php echo e(request('match')==$m['match_id']?'bg-[#16161f] border-l-2 border-l-purple-500':''); ?>"
                 data-match="<?php echo e($m['match_id']); ?>"
                 data-name="<?php echo e(e($m['name'])); ?>"
                 data-avatar="<?php echo e($m['avatar']); ?>"
                 data-selected="<?php echo e(request('match')==$m['match_id'] ? '1' : '0'); ?>"
                 onclick="openChatFromSidebarItem(this)">
                <div class="w-10 h-10 rounded-full border-2 border-[#2a2a3a] flex-shrink-0 overflow-hidden flex items-center justify-center bg-gradient-to-br from-purple-900 to-blue-900">
                    <?php if($m['avatar']): ?><img src="<?php echo e($m['avatar']); ?>" class="w-full h-full object-cover"><?php else: ?><span>🖤</span><?php endif; ?>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-1.5 min-w-0">
                        <?php if($m['unread'] > 0): ?>
                        <span class="chat-unread-dot w-2 h-2 rounded-full bg-purple-500 flex-shrink-0"></span>
                        <?php endif; ?>
                        <div class="font-mono text-xs font-bold text-[#e2e0f0] truncate"><?php echo e($m['name']); ?></div>
                    </div>
                    <div class="text-xs text-[#5e5880] truncate"><?php echo e(Str::limit($m['last_msg'] ?? __('chat.new_match'), 28)); ?></div>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="p-6 text-center text-[#5e5880] text-sm leading-loose">
                <?php echo e(__('chat.no_chats')); ?><br><?php echo e(__('chat.match_first')); ?>

            </div>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="flex-1 flex flex-col bg-[#0a0a0f] min-w-0" id="chatMain">
        <div class="flex-1 flex items-center justify-center flex-col gap-3 text-[#5e5880]">
            <span class="text-4xl">🖤</span>
            <span class="font-mono text-xs uppercase tracking-widest"><?php echo e(__('chat.select')); ?></span>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
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
const MY_AVATAR = "<?php echo e(auth()->user()->getAvatarUrl()); ?>";
const MY_INITIAL = "<?php echo e(strtoupper(substr(auth()->user()->name,0,1))); ?>";
const INITIAL_MATCH_ID = <?php echo json_encode(request('match'), 15, 512) ?>;
const TRANS = {
    online: "<?php echo e(__('chat.online')); ?>",
    placeholder: "<?php echo e(__('chat.placeholder')); ?>",
    matched: "<?php echo e(__('chat.matched')); ?>",
    emoji: "<?php echo e(__('chat.emoji')); ?>",
    gif: "<?php echo e(__('chat.gif')); ?>",
    search_gif: "<?php echo e(__('chat.search_gif')); ?>",
    no_gifs: "<?php echo e(__('chat.no_gifs')); ?>",
    react: "<?php echo e(__('chat.react')); ?>",
    sent: "<?php echo e(__('chat.sent')); ?>",
    read: "<?php echo e(__('chat.read')); ?>",

};
</script>
<script src="<?php echo e(asset('js/chat.js')); ?>?v=<?php echo e(filemtime(public_path('js/chat.js'))); ?>"></script>
<?php $__env->stopPush(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\malin\Desktop\altermatch\resources\views\chat\index.blade.php ENDPATH**/ ?>