<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(config('larecipe.forum.enabled')): ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(config('larecipe.forum.default') == 'disqus' && config('larecipe.forum.services.disqus.site_name')): ?>
    <hr>
    <div id="disqus_thread"></div>
    <script async>
        var disqus_config = function () {
            this.page.url = '<?php echo e(url($canonical)); ?>';
            this.page.identifier = '/<?php echo e($currentSection); ?>';
        };

        (function() {
        var d = document, s = d.createElement('script');
        s.src = "https://<?php echo e(config('larecipe.forum.services.disqus.site_name')); ?>.disqus.com/embed.js";
        s.setAttribute('data-timestamp', +new Date());
        (d.head || d.body).appendChild(s);
        })();
    </script>
    <noscript>Please enable JavaScript to view the <a href="https://disqus.com/?ref_noscript">comments powered by Disqus.</a></noscript>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            
<?php /**PATH /home/johnsonsebire/www/college/vendor/binarytorch/larecipe/resources/views/plugins/forum.blade.php ENDPATH**/ ?>