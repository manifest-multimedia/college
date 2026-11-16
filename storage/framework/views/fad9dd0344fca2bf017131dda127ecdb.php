<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(config('larecipe.search.enabled')): ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(config('larecipe.search.default') == 'algolia'): ?>
        <algolia-search-box
            v-if="searchBox"
            @close="searchBox = false"
            algolia-key="<?php echo e(config('larecipe.search.engines.algolia.key')); ?>"
            algolia-index="<?php echo e(config('larecipe.search.engines.algolia.index')); ?>"
            version="<?php echo e($currentVersion); ?>"
        ></algolia-search-box>
    <?php elseif(config('larecipe.search.default') == 'internal'): ?>
        <internal-search-box
            v-if="searchBox"
            @close="searchBox = false"
            version-url="<?php echo e(route('larecipe.show', ['version' => $currentVersion])); ?>"
            search-url="<?php echo e(route('larecipe.search', ['version' => $currentVersion])); ?>"
        ></internal-search-box>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php /**PATH /home/johnsonsebire/www/college/vendor/binarytorch/larecipe/resources/views/plugins/search.blade.php ENDPATH**/ ?>