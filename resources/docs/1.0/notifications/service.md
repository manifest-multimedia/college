# Notification Service

---

- [Introduction](#introduction)
- [Service Methods](#service-methods)
- [Error Handling](#error-handling)
- [Customizing the Service](#customizing)
- [Usage Examples](#examples)

<a name="introduction"></a>
## Introduction

The `NotificationService` provides a centralized way to send notifications throughout the College Portal system. It abstracts the details of notification creation and delivery, offering a simple API for sending different types of notifications to users, roles, or all users in the system.

### Core Functionality

The service handles:
- Creating appropriate notification instances
- Delivering notifications to the right users
- Proper error handling and logging
- Supporting different notification types and channels

<a name="service-methods"></a>
## Service Methods

The `NotificationService` provides several methods for sending notifications:

### User Notifications

```php
/**
 * Send a notification to a specific user
 */
public function notifyUser(
    User $user, 
    string $title, 
    string $message, 
    ?string $actionUrl = null, 
    string $type = 'info'
): bool
```

### Role-based Notifications

```php
/**
 * Send a notification to all users with a specific role
 */
public function notifyRole(
    string $role, 
    string $title, 
    string $message, 
    ?string $actionUrl = null, 
    string $type = 'info'
): bool
```

### Broadcast Notifications

```php
/**
 * Send a notification to all users in the system
 */
public function notifyAll(
    string $title, 
    string $message, 
    ?string $actionUrl = null, 
    string $type = 'info'
): bool
```

### Specialized Notifications

```php
/**
 * Send an exam notification to a user
 */
public function notifyExam(
    User $user, 
    string $title, 
    string $message, 
    ?string $examId = null, 
    string $type = 'info'
): bool

/**
 * Send a finance notification to a user
 */
public function notifyFinance(
    User $user, 
    string $title, 
    string $message, 
    ?string $transactionId = null, 
    string $type = 'info'
): bool

/**
 * Send a course registration notification to a user
 */
public function notifyCourseRegistration(
    User $user, 
    string $title, 
    string $message, 
    ?string $courseId = null, 
    string $type = 'info'
): bool
```

<a name="error-handling"></a>
## Error Handling

The `NotificationService` implements robust error handling to ensure notifications don't break application flow:

```php
public function notifyUser(User $user, string $title, string $message, ?string $actionUrl = null, string $type = 'info'): bool
{
    try {
        $notification = new SystemNotification($title, $message, $actionUrl, $type);
        $user->notify($notification);
        
        Log::info('Notification sent to user', [
            'user_id' => $user->id,
            'title' => $title,
            'type' => $type
        ]);
        
        return true;
    } catch (\Exception $e) {
        Log::error('Failed to send notification', [
            'user_id' => $user->id,
            'title' => $title,
            'error' => $e->getMessage()
        ]);
        
        return false;
    }
}
```

Each method follows this pattern:
1. Try to create and send the notification
2. Log success with relevant context
3. Catch any exceptions and log errors
4. Return a boolean indicating success/failure

<a name="customizing"></a>
## Customizing the Service

### Adding New Notification Types

To add support for a new notification type:

1. Create a new notification class (see [Notification Types](/{{route}}/{{version}}/notifications/types#custom-notifications))
2. Add a new method to the `NotificationService` class:

```php
public function notifyCustomType(
    User $user, 
    string $title, 
    string $message, 
    ?string $contextId = null, 
    string $type = 'info'
): bool
{
    try {
        $notification = new CustomTypeNotification($title, $message, $contextId, $type);
        $user->notify($notification);
        
        Log::info('CustomType notification sent', [
            'user_id' => $user->id,
            'title' => $title,
            'context_id' => $contextId,
            'type' => $type
        ]);
        
        return true;
    } catch (\Exception $e) {
        Log::error('Failed to send CustomType notification', [
            'user_id' => $user->id,
            'title' => $title,
            'error' => $e->getMessage()
        ]);
        
        return false;
    }
}
```

### Adding Additional Channels

To send notifications through additional channels:

1. Extend the base notification class to include the new channel:

```php
public function via(object $notifiable): array
{
    return ['database', 'broadcast', 'mail', 'slack'];
}
```

2. Add the channel-specific methods to your notification class:

```php
public function toMail(object $notifiable): MailMessage
{
    return (new MailMessage)
        ->subject($this->data['title'])
        ->line($this->data['message'])
        ->action('View Details', $this->data['action_url'] ?? '#')
        ->line('Thank you for using our application!');
}

public function toSlack(object $notifiable): SlackMessage
{
    return (new SlackMessage)
        ->success()
        ->content($this->data['title'])
        ->attachment(function ($attachment) {
            $attachment->title($this->data['message'])
                ->action('View', $this->data['action_url'] ?? '#');
        });
}
```

<a name="examples"></a>
## Usage Examples

### System Notifications

```php
// Inject the service
public function __construct(private NotificationService $notificationService) {}

// Notify a single user
$this->notificationService->notifyUser(
    $user,
    'Welcome to College Portal',
    'Your account has been successfully set up.',
    route('dashboard'),
    'success'
);

// Notify all faculty members
$this->notificationService->notifyRole(
    'Faculty',
    'Grade Submission Reminder',
    'Please submit all grades by Friday, May 20th.',
    route('faculty.grades.index'),
    'warning'
);

// Notify everyone in the system
$this->notificationService->notifyAll(
    'System Update',
    'The system will be down for maintenance on Saturday from 2AM to 4AM.',
    null,
    'info'
);
```

### Specialized Notifications

```php
// Exam notification
$this->notificationService->notifyExam(
    $student,
    'Final Exam Schedule',
    'Your final exam for ' . $course->name . ' is scheduled for May 25th at 9AM.',
    $exam->id,
    'info'
);

// Finance notification
$this->notificationService->notifyFinance(
    $student,
    'Payment Received',
    'We have received your tuition payment of $1,500.',
    $payment->id,
    'success'
);

// Course registration notification
$this->notificationService->notifyCourseRegistration(
    $student,
    'Registration Approved',
    'Your registration for ' . $course->name . ' has been approved.',
    $course->id,
    'success'
);
```

### Handling Success/Failure

```php
if ($this->notificationService->notifyUser($user, $title, $message)) {
    return response()->json(['message' => 'Notification sent successfully']);
} else {
    return response()->json(['message' => 'Failed to send notification'], 500);
}
```