<?php if (isset($component)) { $__componentOriginal78458ab11901cb5e12c9728346766652 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78458ab11901cb5e12c9728346766652 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.default','data' => ['title' => 'Notifications']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.default'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Notifications']); ?>
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <h1 class="card-title">
                    <i class="ki-duotone ki-notification-bing fs-1 me-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                    </i>
                    Notifications
                </h1>
            </div>
            <div class="card-toolbar">
                <button type="button" class="btn btn-sm btn-light-primary" id="mark-all-read">
                    <i class="ki-duotone ki-check fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    Mark All as Read
                </button>
            </div>
        </div>
        
        <div class="card-body">
            <div class="d-flex flex-column">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($notifications->isEmpty()): ?>
                    <div class="text-center py-10">
                        <i class="ki-duotone ki-notification-bing fs-5x text-gray-300 mb-5">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        <div class="text-gray-600 fw-semibold fs-4">You have no notifications</div>
                    </div>
                <?php else: ?>
                    <ul class="nav nav-tabs nav-line-tabs mb-5 fs-6">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#all_notifications">All Notifications</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#unread_notifications">Unread</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#read_notifications">Read</a>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="notificationsTabContent">
                        <div class="tab-pane fade show active" id="all_notifications" role="tabpanel">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="d-flex flex-stack py-4 border-bottom notification-item" data-id="<?php echo e($notification->id); ?>" data-read="<?php echo e($notification->read_at ? 'true' : 'false'); ?>">
                                    <?php
                                        $type = $notification->data['type'] ?? 'info';
                                        $iconClass = 'ki-duotone ki-abstract-28 fs-2 text-primary';
                                        $bgClass = 'bg-light-primary';
                                        
                                        switch ($type) {
                                            case 'success':
                                                $iconClass = 'ki-duotone ki-check-circle fs-2 text-success';
                                                $bgClass = 'bg-light-success';
                                                break;
                                            case 'warning':
                                                $iconClass = 'ki-duotone ki-information fs-2 text-warning';
                                                $bgClass = 'bg-light-warning';
                                                break;
                                            case 'danger':
                                                $iconClass = 'ki-duotone ki-cross-circle fs-2 text-danger';
                                                $bgClass = 'bg-light-danger';
                                                break;
                                            case 'info':
                                                $iconClass = 'ki-duotone ki-information-5 fs-2 text-info';
                                                $bgClass = 'bg-light-info';
                                                break;
                                        }
                                        
                                        $title = $notification->data['title'] ?? 'Notification';
                                        $message = $notification->data['message'] ?? '';
                                        $actionUrl = $notification->data['action_url'] ?? '#';
                                        $timeAgo = $notification->created_at->diffForHumans();
                                    ?>
                                    
                                    <div class="d-flex align-items-center">
                                        <div class="symbol symbol-50px me-4">
                                            <span class="symbol-label <?php echo e($bgClass); ?>">
                                                <i class="<?php echo e($iconClass); ?>">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </span>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <a href="<?php echo e($actionUrl); ?>" class="fs-5 text-dark text-hover-primary fw-semibold notification-link" data-id="<?php echo e($notification->id); ?>">
                                                <?php echo e($title); ?>

                                            </a>
                                            <div class="text-gray-600"><?php echo e($message); ?></div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex flex-column align-items-end">
                                        <span class="text-gray-500 fs-7"><?php echo e($timeAgo); ?></span>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$notification->read_at): ?>
                                            <span class="badge badge-light-primary mt-1">New</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <div class="mt-2">
                                            <button type="button" class="btn btn-sm btn-icon btn-light delete-notification" data-id="<?php echo e($notification->id); ?>">
                                                <i class="ki-duotone ki-trash fs-2 text-gray-500">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                    <span class="path3"></span>
                                                </i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            
                            <div class="mt-5">
                                <?php echo e($notifications->links()); ?>

                            </div>
                        </div>
                        
                        <div class="tab-pane fade" id="unread_notifications" role="tabpanel">
                            <?php $hasUnread = false; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$notification->read_at): ?>
                                    <?php $hasUnread = true; ?>
                                    <div class="d-flex flex-stack py-4 border-bottom notification-item" data-id="<?php echo e($notification->id); ?>" data-read="false">
                                        <?php
                                            $type = $notification->data['type'] ?? 'info';
                                            $iconClass = 'ki-duotone ki-abstract-28 fs-2 text-primary';
                                            $bgClass = 'bg-light-primary';
                                            
                                            switch ($type) {
                                                case 'success':
                                                    $iconClass = 'ki-duotone ki-check-circle fs-2 text-success';
                                                    $bgClass = 'bg-light-success';
                                                    break;
                                                case 'warning':
                                                    $iconClass = 'ki-duotone ki-information fs-2 text-warning';
                                                    $bgClass = 'bg-light-warning';
                                                    break;
                                                case 'danger':
                                                    $iconClass = 'ki-duotone ki-cross-circle fs-2 text-danger';
                                                    $bgClass = 'bg-light-danger';
                                                    break;
                                                case 'info':
                                                    $iconClass = 'ki-duotone ki-information-5 fs-2 text-info';
                                                    $bgClass = 'bg-light-info';
                                                    break;
                                            }
                                            
                                            $title = $notification->data['title'] ?? 'Notification';
                                            $message = $notification->data['message'] ?? '';
                                            $actionUrl = $notification->data['action_url'] ?? '#';
                                            $timeAgo = $notification->created_at->diffForHumans();
                                        ?>
                                        
                                        <div class="d-flex align-items-center">
                                            <div class="symbol symbol-50px me-4">
                                                <span class="symbol-label <?php echo e($bgClass); ?>">
                                                    <i class="<?php echo e($iconClass); ?>">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                    </i>
                                                </span>
                                            </div>
                                            <div class="d-flex flex-column">
                                                <a href="<?php echo e($actionUrl); ?>" class="fs-5 text-dark text-hover-primary fw-semibold notification-link" data-id="<?php echo e($notification->id); ?>">
                                                    <?php echo e($title); ?>

                                                </a>
                                                <div class="text-gray-600"><?php echo e($message); ?></div>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex flex-column align-items-end">
                                            <span class="text-gray-500 fs-7"><?php echo e($timeAgo); ?></span>
                                            <span class="badge badge-light-primary mt-1">New</span>
                                            <div class="mt-2">
                                                <button type="button" class="btn btn-sm btn-icon btn-light delete-notification" data-id="<?php echo e($notification->id); ?>">
                                                    <i class="ki-duotone ki-trash fs-2 text-gray-500">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                        <span class="path3"></span>
                                                    </i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$hasUnread): ?>
                                <div class="text-center py-10">
                                    <i class="ki-duotone ki-check-circle fs-5x text-gray-300 mb-5">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    <div class="text-gray-600 fw-semibold fs-4">You have no unread notifications</div>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        
                        <div class="tab-pane fade" id="read_notifications" role="tabpanel">
                            <?php $hasRead = false; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($notification->read_at): ?>
                                    <?php $hasRead = true; ?>
                                    <div class="d-flex flex-stack py-4 border-bottom notification-item" data-id="<?php echo e($notification->id); ?>" data-read="true">
                                        <?php
                                            $type = $notification->data['type'] ?? 'info';
                                            $iconClass = 'ki-duotone ki-abstract-28 fs-2 text-primary';
                                            $bgClass = 'bg-light-primary';
                                            
                                            switch ($type) {
                                                case 'success':
                                                    $iconClass = 'ki-duotone ki-check-circle fs-2 text-success';
                                                    $bgClass = 'bg-light-success';
                                                    break;
                                                case 'warning':
                                                    $iconClass = 'ki-duotone ki-information fs-2 text-warning';
                                                    $bgClass = 'bg-light-warning';
                                                    break;
                                                case 'danger':
                                                    $iconClass = 'ki-duotone ki-cross-circle fs-2 text-danger';
                                                    $bgClass = 'bg-light-danger';
                                                    break;
                                                case 'info':
                                                    $iconClass = 'ki-duotone ki-information-5 fs-2 text-info';
                                                    $bgClass = 'bg-light-info';
                                                    break;
                                            }
                                            
                                            $title = $notification->data['title'] ?? 'Notification';
                                            $message = $notification->data['message'] ?? '';
                                            $actionUrl = $notification->data['action_url'] ?? '#';
                                            $timeAgo = $notification->created_at->diffForHumans();
                                        ?>
                                        
                                        <div class="d-flex align-items-center">
                                            <div class="symbol symbol-50px me-4">
                                                <span class="symbol-label <?php echo e($bgClass); ?>">
                                                    <i class="<?php echo e($iconClass); ?>">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                    </i>
                                                </span>
                                            </div>
                                            <div class="d-flex flex-column">
                                                <a href="<?php echo e($actionUrl); ?>" class="fs-5 text-dark text-hover-primary fw-semibold">
                                                    <?php echo e($title); ?>

                                                </a>
                                                <div class="text-gray-600"><?php echo e($message); ?></div>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex flex-column align-items-end">
                                            <span class="text-gray-500 fs-7"><?php echo e($timeAgo); ?></span>
                                            <div class="mt-2">
                                                <button type="button" class="btn btn-sm btn-icon btn-light delete-notification" data-id="<?php echo e($notification->id); ?>">
                                                    <i class="ki-duotone ki-trash fs-2 text-gray-500">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                        <span class="path3"></span>
                                                    </i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$hasRead): ?>
                                <div class="text-center py-10">
                                    <i class="ki-duotone ki-eye fs-5x text-gray-300 mb-5">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>
                                    <div class="text-gray-600 fw-semibold fs-4">You have no read notifications</div>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php $__env->startPush('scripts'); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle mark as read on notification click
            document.querySelectorAll('.notification-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    const id = this.getAttribute('data-id');
                    const item = this.closest('.notification-item');
                    
                    if (item.getAttribute('data-read') === 'false') {
                        markAsRead(id);
                    }
                });
            });
            
            // Handle delete notification
            document.querySelectorAll('.delete-notification').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const item = this.closest('.notification-item');
                    
                    deleteNotification(id, item);
                });
            });
            
            // Handle mark all as read
            document.getElementById('mark-all-read').addEventListener('click', function() {
                markAllAsRead();
            });
            
            function markAsRead(id) {
                fetch(`/notifications/${id}/mark-as-read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update the UI to reflect the read status
                        const items = document.querySelectorAll(`.notification-item[data-id="${id}"]`);
                        items.forEach(item => {
                            item.setAttribute('data-read', 'true');
                            const badge = item.querySelector('.badge');
                            if (badge) {
                                badge.remove();
                            }
                        });
                        
                        // Show success message
                        toastr.success('Notification marked as read');
                    }
                })
                .catch(error => {
                    console.error('Error marking notification as read:', error);
                    toastr.error('Failed to mark notification as read');
                });
            }
            
            function deleteNotification(id, item) {
                if (confirm('Are you sure you want to delete this notification?')) {
                    fetch(`/notifications/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Remove all instances of this notification from the page
                            const items = document.querySelectorAll(`.notification-item[data-id="${id}"]`);
                            items.forEach(item => {
                                item.remove();
                            });
                            
                            // Show success message
                            toastr.success('Notification deleted');
                            
                            // Check if any tabs are empty now
                            checkEmptyTabs();
                        }
                    })
                    .catch(error => {
                        console.error('Error deleting notification:', error);
                        toastr.error('Failed to delete notification');
                    });
                }
            }
            
            function markAllAsRead() {
                fetch('/notifications/mark-all-read', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update all notifications to read status
                        const unreadItems = document.querySelectorAll('.notification-item[data-read="false"]');
                        unreadItems.forEach(item => {
                            item.setAttribute('data-read', 'true');
                            const badge = item.querySelector('.badge');
                            if (badge) {
                                badge.remove();
                            }
                        });
                        
                        // Show success message
                        toastr.success('All notifications marked as read');
                        
                        // Force refresh the page to update the tabs
                        location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error marking all notifications as read:', error);
                    toastr.error('Failed to mark all notifications as read');
                });
            }
            
            function checkEmptyTabs() {
                // Check all notifications tab
                const allTab = document.querySelector('#all_notifications');
                if (allTab.querySelectorAll('.notification-item').length === 0) {
                    allTab.innerHTML = `
                        <div class="text-center py-10">
                            <i class="ki-duotone ki-notification-bing fs-5x text-gray-300 mb-5">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                            <div class="text-gray-600 fw-semibold fs-4">You have no notifications</div>
                        </div>
                    `;
                }
                
                // Check unread notifications tab
                const unreadTab = document.querySelector('#unread_notifications');
                if (unreadTab.querySelectorAll('.notification-item').length === 0) {
                    unreadTab.innerHTML = `
                        <div class="text-center py-10">
                            <i class="ki-duotone ki-check-circle fs-5x text-gray-300 mb-5">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            <div class="text-gray-600 fw-semibold fs-4">You have no unread notifications</div>
                        </div>
                    `;
                }
                
                // Check read notifications tab
                const readTab = document.querySelector('#read_notifications');
                if (readTab.querySelectorAll('.notification-item').length === 0) {
                    readTab.innerHTML = `
                        <div class="text-center py-10">
                            <i class="ki-duotone ki-eye fs-5x text-gray-300 mb-5">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                            <div class="text-gray-600 fw-semibold fs-4">You have no read notifications</div>
                        </div>
                    `;
                }
            }
        });
    </script>
    <?php $__env->stopPush(); ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78458ab11901cb5e12c9728346766652)): ?>
<?php $attributes = $__attributesOriginal78458ab11901cb5e12c9728346766652; ?>
<?php unset($__attributesOriginal78458ab11901cb5e12c9728346766652); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78458ab11901cb5e12c9728346766652)): ?>
<?php $component = $__componentOriginal78458ab11901cb5e12c9728346766652; ?>
<?php unset($__componentOriginal78458ab11901cb5e12c9728346766652); ?>
<?php endif; ?><?php /**PATH /home/johnsonsebire/www/college/resources/views/notifications/index.blade.php ENDPATH**/ ?>