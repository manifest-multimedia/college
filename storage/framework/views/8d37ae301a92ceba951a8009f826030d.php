<style>
    :root {
        --primary: <?php echo e(config('larecipe.ui.colors.primary')); ?>;
        --secondary: <?php echo e(config('larecipe.ui.colors.secondary')); ?>;
    }

    :not(pre)>code[class*=language-], pre[class*=language-] {
        border-top: 3px solid <?php echo e(config('larecipe.ui.colors.primary')); ?>;
    }
    
    .bg-gradient-primary {
        background: linear-gradient(87deg, <?php echo e(config('larecipe.ui.colors.primary')); ?> 0, <?php echo e(config('larecipe.ui.colors.secondary')); ?> 100%) !important;
    }

    [v-cloak] > * { 
        display: none; 
    }
    
    [v-cloak]::before { 
        content: " ";
        position: absolute;
        width: 100%;
        height: 100%;
        background-color: #F2F6FA;
    }
</style><?php /**PATH /home/johnsonsebire/www/college/vendor/binarytorch/larecipe/resources/views/style.blade.php ENDPATH**/ ?>