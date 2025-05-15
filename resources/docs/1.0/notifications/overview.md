# Notification System

---

- [Introduction](#introduction)
- [Architecture](#architecture)
- [Components](#components)
- [User Interface](#user-interface)
- [Configuration](#configuration)

<a name="introduction"></a>
## Introduction

The College Portal includes a comprehensive real-time notification system that keeps users informed about important events and updates across the platform. The notification system is designed to be:

- **Real-time**: Instant delivery of notifications using Pusher
- **Persistent**: All notifications are stored in the database
- **Customizable**: Different types of notifications with appropriate styling
- **Multi-channel**: Support for in-app, email, and potentially SMS notifications
- **User-specific**: Notifications are targeted to relevant users

<a name="architecture"></a>
## Architecture

The notification system is built on Laravel's native notification system, enhanced with real-time capabilities through Pusher. The system follows these principles:

1. **Event-driven**: System events trigger notifications
2. **Queueable**: Notifications are processed asynchronously using Laravel's queue system
3. **Modular**: Different notification types extend a base notification class
4. **Service-oriented**: A dedicated NotificationService manages notification delivery

### Flow Diagram

```
[System Event] → [NotificationService] → [Notification Class] → [Delivery Channels]
                                                              ↓
                                                        [Database] → [Pusher] → [User Interface]
```

<a name="components"></a>
## Components

### Base Notification Class

All notification types inherit from the `BaseNotification` class, which provides common functionality:

```php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

abstract class BaseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $data;
    protected $type = 'info';

    public function via(object $notifiable): array
    {
        // By default, send via database and broadcast channels
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        // Default structure for database notifications
        return array_merge([
            'type' => $this->type,
            'created_at' => now()->toIso8601String(),
        ], $this->data);
    }
    
    public function toBroadcast(object $notifiable): array
    {
        return $this->toArray($notifiable);
    }
}
```

### Specialized Notification Types

The system includes several specialized notification types:

1. **SystemNotification**: General system messages
2. **ExamNotification**: Exam-related notifications
3. **FinanceNotification**: Fee and payment notifications
4. **CourseRegistrationNotification**: Course registration updates

### Notification Service

The `NotificationService` class provides methods to send notifications to different audiences:

```php
// Send to a specific user
$notificationService->notifyUser($user, $title, $message, $actionUrl, $type);

// Send to users with a specific role
$notificationService->notifyRole('Student', $title, $message, $actionUrl, $type);

// Send to all users
$notificationService->notifyAll($title, $message, $actionUrl, $type);
```

<a name="user-interface"></a>
## User Interface

### Notification Component

The notification bell in the dashboard header is implemented as a Blade component:

```html
<x-notifications />
```

This component includes:
- Notification bell icon with unread indicator
- Dropdown menu showing recent notifications
- Tabs for All, Unread, and Read notifications
- Visual indicators for different notification types

### Notification Index Page

A dedicated page at `/notifications` shows all user notifications with:
- Pagination for large numbers of notifications
- Filtering options (All/Read/Unread)
- Options to mark as read or delete notifications

<a name="configuration"></a>
## Configuration

### Broadcasting Setup

For real-time notification delivery, ensure Pusher is properly configured in your `.env` file:

```
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your-pusher-app-id
PUSHER_APP_KEY=your-pusher-key
PUSHER_APP_SECRET=your-pusher-secret
PUSHER_APP_CLUSTER=mt1
```

### Laravel Echo Setup

Ensure Laravel Echo is configured in your JavaScript:

```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true
});
```

### Queue Configuration

For optimal performance, notifications are queued. Ensure your queue worker is running:

```bash
php artisan queue:work
```