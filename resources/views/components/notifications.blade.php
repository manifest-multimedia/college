<div class="d-flex align-items-center me-2 me-lg-4">
    <a href="#"
        class="bg-white bg-opacity-10 btn btn-icon btn-borderless btn-color-white btn-active-primary position-relative"
        data-kt-menu-trigger="click" data-kt-menu-attach="parent"
        data-kt-menu-placement="bottom-end">
        <i class="text-white ki-duotone ki-notification-bing fs-1">
            <span class="path1"></span>
            <span class="path2"></span>
            <span class="path3"></span>
        </i>
        <span id="notification-indicator"
            class="top-0 bullet bullet-dot bg-success h-6px w-6px position-absolute translate-middle start-50 animation-blink d-none"></span>
    </a>
    <!--begin::Menu-->
    <div class="menu menu-sub menu-sub-dropdown menu-column w-350px w-lg-375px"
        data-kt-menu="true" id="kt_menu_notifications">
        <!--begin::Heading-->
        <div class="d-flex flex-column bgi-no-repeat rounded-top"
            style="background-image:url('{{ asset('dashboard/assets/media/misc/menu-header-bg.jpg') }}')">
            <!--begin::Title-->
            <h3 class="px-9 mt-10 mb-6 text-white fw-semibold">Notifications
                <span class="opacity-75 fs-8 ps-3" id="notification-count">0</span>
            </h3>
            <!--end::Title-->
            <!--begin::Tabs-->
            <ul class="px-9 nav nav-line-tabs nav-line-tabs-2x nav-stretch fw-semibold">
                <li class="nav-item">
                    <a class="pb-4 text-white opacity-75 nav-link opacity-state-100 active"
                        data-bs-toggle="tab"
                        href="#kt_topbar_notifications_all">All</a>
                </li>
                <li class="nav-item">
                    <a class="pb-4 text-white opacity-75 nav-link opacity-state-100"
                        data-bs-toggle="tab"
                        href="#kt_topbar_notifications_unread">Unread</a>
                </li>
                <li class="nav-item">
                    <a class="pb-4 text-white opacity-75 nav-link opacity-state-100"
                        data-bs-toggle="tab" href="#kt_topbar_notifications_read">Read</a>
                </li>
            </ul>
            <!--end::Tabs-->
        </div>
        <!--end::Heading-->
        <!--begin::Tab content-->
        <div class="tab-content">
            <!--begin::Tab panel-->
            <div class="tab-pane fade show active" id="kt_topbar_notifications_all" role="tabpanel">
                <!--begin::Items-->
                <div class="px-8 my-5 scroll-y mh-325px" id="notifications-all-container">
                    <div class="text-center py-10">
                        <span class="text-gray-500">No notifications found</span>
                    </div>
                </div>
                <!--end::Items-->
                <!--begin::View more-->
                <div class="py-3 text-center border-top">
                    <a href="{{ route('notifications.index') }}"
                        class="btn btn-color-gray-600 btn-active-color-primary">View All
                        <i class="ki-duotone ki-arrow-right fs-5">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i></a>
                </div>
                <!--end::View more-->
            </div>
            <!--end::Tab panel-->
            <!--begin::Tab panel-->
            <div class="tab-pane fade" id="kt_topbar_notifications_unread" role="tabpanel">
                <!--begin::Items-->
                <div class="px-8 my-5 scroll-y mh-325px" id="notifications-unread-container">
                    <div class="text-center py-10">
                        <span class="text-gray-500">No unread notifications</span>
                    </div>
                </div>
                <!--end::Items-->
            </div>
            <!--end::Tab panel-->
            <!--begin::Tab panel-->
            <div class="tab-pane fade" id="kt_topbar_notifications_read" role="tabpanel">
                <!--begin::Items-->
                <div class="px-8 my-5 scroll-y mh-325px" id="notifications-read-container">
                    <div class="text-center py-10">
                        <span class="text-gray-500">No read notifications</span>
                    </div>
                </div>
                <!--end::Items-->
            </div>
            <!--end::Tab panel-->
        </div>
        <!--end::Tab content-->
    </div>
    <!--end::Menu-->
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize the notification system
        initNotifications();
    });

    function initNotifications() {
        // Fetch notifications on page load
        fetchNotifications();

        // Setup Pusher for real-time notifications
        setupPusherNotifications();
    }

    function fetchNotifications() {
        fetch('/notifications/get')
            .then(response => response.json())
            .then(data => {
                renderNotifications(data);
                updateNotificationCount(data);
            })
            .catch(error => console.error('Error fetching notifications:', error));
    }

    function setupPusherNotifications() {
        // Setup Pusher connection if it's enabled
        if (typeof Echo !== 'undefined') {
            // Listen to the private user channel
            Echo.private(`App.Models.User.${userId}`)
                .notification((notification) => {
                    // Add the new notification to the display
                    addNewNotification(notification);
                    
                    // Show the indicator and update the count
                    updateNotificationIndicator(true);
                    
                    // Sound notification if enabled
                    playNotificationSound();
                });
                
            // Listen to broadcast notifications (for all users)
            Echo.channel('notifications')
                .listen('.broadcast-notification', (data) => {
                    // Add the new broadcast notification to the display
                    addNewNotification(data.notification);
                    
                    // Show the indicator and update the count
                    updateNotificationIndicator(true);
                    
                    // Sound notification if enabled
                    playNotificationSound();
                });
        }
    }

    function renderNotifications(notifications) {
        const allContainer = document.getElementById('notifications-all-container');
        const unreadContainer = document.getElementById('notifications-unread-container');
        const readContainer = document.getElementById('notifications-read-container');
        
        // Clear the containers
        allContainer.innerHTML = '';
        unreadContainer.innerHTML = '';
        readContainer.innerHTML = '';
        
        if (notifications.length === 0) {
            allContainer.innerHTML = '<div class="text-center py-10"><span class="text-gray-500">No notifications found</span></div>';
            unreadContainer.innerHTML = '<div class="text-center py-10"><span class="text-gray-500">No unread notifications</span></div>';
            readContainer.innerHTML = '<div class="text-center py-10"><span class="text-gray-500">No read notifications</span></div>';
            return;
        }
        
        let unreadCount = 0;
        let readCount = 0;
        
        notifications.forEach(notification => {
            const notificationElement = createNotificationElement(notification);
            
            // Add to all notifications tab
            allContainer.appendChild(notificationElement.cloneNode(true));
            
            // Add to appropriate tab based on read status
            if (notification.read_at) {
                readContainer.appendChild(notificationElement.cloneNode(true));
                readCount++;
            } else {
                unreadContainer.appendChild(notificationElement.cloneNode(true));
                unreadCount++;
            }
        });
        
        // Show empty message if no notifications in a category
        if (unreadCount === 0) {
            unreadContainer.innerHTML = '<div class="text-center py-10"><span class="text-gray-500">No unread notifications</span></div>';
        }
        
        if (readCount === 0) {
            readContainer.innerHTML = '<div class="text-center py-10"><span class="text-gray-500">No read notifications</span></div>';
        }
    }

    function createNotificationElement(notification) {
        const element = document.createElement('div');
        element.className = 'py-4 d-flex flex-stack';
        element.setAttribute('data-notification-id', notification.id);
        
        let iconClass = 'ki-duotone ki-abstract-28 fs-2 text-primary';
        let bgClass = 'bg-light-primary';
        
        // Customize icon based on notification type
        if (notification.data && notification.data.type) {
            switch (notification.data.type) {
                case 'success':
                    iconClass = 'ki-duotone ki-check-circle fs-2 text-success';
                    bgClass = 'bg-light-success';
                    break;
                case 'warning':
                    iconClass = 'ki-duotone ki-information fs-2 text-warning';
                    bgClass = 'bg-light-warning';
                    break;
                case 'danger':
                    iconClass = 'ki-duotone ki-cross-circle fs-2 text-danger';
                    bgClass = 'bg-light-danger';
                    break;
                case 'info':
                    iconClass = 'ki-duotone ki-information-5 fs-2 text-info';
                    bgClass = 'bg-light-info';
                    break;
            }
        }
        
        // Calculate time ago
        const timeAgo = calculateTimeAgo(notification.created_at);
        
        element.innerHTML = `
            <div class="d-flex align-items-center">
                <div class="symbol symbol-35px me-4">
                    <span class="symbol-label ${bgClass}">
                        <i class="${iconClass}">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </span>
                </div>
                <div class="mb-0 me-2">
                    <a href="${notification.data.action_url || '#'}" 
                       class="text-gray-800 fs-6 text-hover-primary fw-bold notification-link"
                       data-id="${notification.id}">
                        ${notification.data.title || 'Notification'}
                    </a>
                    <div class="text-gray-500 fs-7">${notification.data.message || ''}</div>
                </div>
            </div>
            <span class="badge badge-light fs-8">${timeAgo}</span>
        `;
        
        // Add event listener to mark as read when clicked
        const notificationLink = element.querySelector('.notification-link');
        notificationLink.addEventListener('click', function(e) {
            if (!notification.read_at) {
                markNotificationAsRead(notification.id);
            }
        });
        
        return element;
    }

    function addNewNotification(notification) {
        // Create the notification element
        const notificationElement = createNotificationElement(notification);
        
        // Add to unread and all containers
        const unreadContainer = document.getElementById('notifications-unread-container');
        const allContainer = document.getElementById('notifications-all-container');
        
        // Remove empty message if present
        const unreadEmptyMsg = unreadContainer.querySelector('.text-center');
        if (unreadEmptyMsg) {
            unreadContainer.innerHTML = '';
        }
        
        const allEmptyMsg = allContainer.querySelector('.text-center');
        if (allEmptyMsg) {
            allContainer.innerHTML = '';
        }
        
        // Add the new notification at the top
        unreadContainer.prepend(notificationElement.cloneNode(true));
        allContainer.prepend(notificationElement);
    }

    function updateNotificationCount(notifications) {
        const unreadCount = notifications.filter(n => !n.read_at).length;
        document.getElementById('notification-count').textContent = notifications.length;
        
        // Show/hide the notification indicator
        updateNotificationIndicator(unreadCount > 0);
    }

    function updateNotificationIndicator(show) {
        const indicator = document.getElementById('notification-indicator');
        if (show) {
            indicator.classList.remove('d-none');
        } else {
            indicator.classList.add('d-none');
        }
    }

    function markNotificationAsRead(id) {
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
                // Refresh notifications
                fetchNotifications();
            }
        })
        .catch(error => console.error('Error marking notification as read:', error));
    }

    function playNotificationSound() {
        const audio = new Audio('/sounds/notification.mp3');
        audio.play().catch(e => console.log('Auto-play prevented: User interaction required.'));
    }

    function calculateTimeAgo(timestamp) {
        const now = new Date();
        const notificationTime = new Date(timestamp);
        const diff = Math.floor((now - notificationTime) / 1000); // seconds
        
        if (diff < 60) return 'Just now';
        if (diff < 3600) return `${Math.floor(diff / 60)} min`;
        if (diff < 86400) return `${Math.floor(diff / 3600)} hr`;
        if (diff < 2592000) return `${Math.floor(diff / 86400)} day`;
        return notificationTime.toLocaleDateString();
    }
</script>
@endpush