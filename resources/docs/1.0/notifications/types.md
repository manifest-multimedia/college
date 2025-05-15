# Notification Types

---

- [Introduction](#introduction)
- [System Notifications](#system-notifications)
- [Exam Notifications](#exam-notifications)
- [Finance Notifications](#finance-notifications)
- [Course Registration Notifications](#course-registration-notifications)
- [Creating Custom Notifications](#custom-notifications)

<a name="introduction"></a>
## Introduction

The College Portal notification system supports multiple notification types, each designed for specific use cases with appropriate styling and behavior. All notification types extend the base `BaseNotification` class while adding specialized functionality.

Each notification type:
- Has a specific visual appearance
- Contains contextual information
- Links to relevant parts of the system
- Can be directed to specific users or groups

<a name="system-notifications"></a>
## System Notifications

### Purpose

`SystemNotification` is a general-purpose notification type for system-wide announcements and general information. Use this for:
- Maintenance announcements
- System updates
- Administrative communications
- General announcements

### Implementation

```php
namespace App\Notifications;

class SystemNotification extends BaseNotification
{
    /**
     * Create a new notification instance.
     */
    public function __construct(string $title, string $message, string $actionUrl = null, string $type = 'info')
    {
        $this->data = [
            'title' => $title,
            'message' => $message,
            'action_url' => $actionUrl,
        ];
        
        $this->type = $type;
    }
}
```

### Usage

```php
use App\Notifications\SystemNotification;
use App\Models\User;

// Direct usage
$user = User::find(1);
$user->notify(new SystemNotification(
    'System Maintenance',
    'The system will be down for maintenance on Friday at 10 PM.',
    null,
    'warning'
));

// Using the notification service
$notificationService = app(App\Services\NotificationService::class);
$notificationService->notifyAll(
    'System Update',
    'New features have been added to the portal.',
    route('dashboard'),
    'info'
);
```

<a name="exam-notifications"></a>
## Exam Notifications

### Purpose

`ExamNotification` is designed specifically for exam-related communications. Use this for:
- Exam schedule announcements
- Results availability
- Exam registration confirmations
- Schedule changes

### Implementation

```php
namespace App\Notifications;

class ExamNotification extends BaseNotification
{
    /**
     * Create a new notification instance.
     */
    public function __construct(string $title, string $message, string $examId = null, string $type = 'info')
    {
        $this->data = [
            'title' => $title,
            'message' => $message,
            'action_url' => $examId ? route('student.exams.show', $examId) : null,
            'exam_id' => $examId,
        ];
        
        $this->type = $type;
    }
}
```

### Usage

```php
use App\Notifications\ExamNotification;
use App\Models\User;
use App\Models\Exam;

$exam = Exam::find(1);
$student = User::find(1);

// Direct usage
$student->notify(new ExamNotification(
    'Exam Results Available',
    'Your results for ' . $exam->title . ' are now available.',
    $exam->id,
    'success'
));

// Using the notification service
$notificationService = app(App\Services\NotificationService::class);
$notificationService->notifyExam(
    $student,
    'Exam Schedule Updated',
    'The schedule for ' . $exam->title . ' has been updated.',
    $exam->id,
    'warning'
);
```

<a name="finance-notifications"></a>
## Finance Notifications

### Purpose

`FinanceNotification` communicates financial information to users. Use this for:
- Fee payment confirmations
- Payment due reminders
- Financial clearance status
- Scholarship notifications

### Implementation

```php
namespace App\Notifications;

class FinanceNotification extends BaseNotification
{
    /**
     * Create a new notification instance.
     */
    public function __construct(string $title, string $message, string $transactionId = null, string $type = 'info')
    {
        $this->data = [
            'title' => $title,
            'message' => $message,
            'action_url' => $transactionId ? route('student.finance.transaction', $transactionId) : null,
            'transaction_id' => $transactionId,
        ];
        
        $this->type = $type;
    }
}
```

### Usage

```php
use App\Notifications\FinanceNotification;
use App\Models\User;
use App\Models\Transaction;

$transaction = Transaction::find(1);
$student = User::find(1);

// Direct usage
$student->notify(new FinanceNotification(
    'Payment Confirmed',
    'Your payment of ' . $transaction->amount . ' has been received.',
    $transaction->id,
    'success'
));

// Using the notification service
$notificationService = app(App\Services\NotificationService::class);
$notificationService->notifyFinance(
    $student,
    'Payment Due',
    'Your tuition payment of ' . $transaction->amount . ' is due in 7 days.',
    $transaction->id,
    'warning'
);
```

<a name="course-registration-notifications"></a>
## Course Registration Notifications

### Purpose

`CourseRegistrationNotification` handles updates related to course registration. Use this for:
- Registration confirmation
- Approval/rejection notifications
- Course add/drop confirmations
- Registration deadline reminders

### Implementation

```php
namespace App\Notifications;

class CourseRegistrationNotification extends BaseNotification
{
    /**
     * Create a new notification instance.
     */
    public function __construct(string $title, string $message, string $courseId = null, string $type = 'info')
    {
        $this->data = [
            'title' => $title,
            'message' => $message,
            'action_url' => $courseId ? route('student.courses.show', $courseId) : null,
            'course_id' => $courseId,
        ];
        
        $this->type = $type;
    }
}
```

### Usage

```php
use App\Notifications\CourseRegistrationNotification;
use App\Models\User;
use App\Models\Course;

$course = Course::find(1);
$student = User::find(1);

// Direct usage
$student->notify(new CourseRegistrationNotification(
    'Registration Confirmed',
    'You have been successfully registered for ' . $course->title,
    $course->id,
    'success'
));

// Using the notification service
$notificationService = app(App\Services\NotificationService::class);
$notificationService->notifyCourseRegistration(
    $student,
    'Registration Approved',
    'Your registration for ' . $course->title . ' has been approved.',
    $course->id,
    'success'
);
```

<a name="custom-notifications"></a>
## Creating Custom Notifications

You can create additional notification types for specific needs by extending the `BaseNotification` class.

### Basic Steps

1. Create a new notification class that extends `BaseNotification`
2. Implement the constructor to format the notification data
3. Override any methods from the parent class as needed

### Example: Creating an Attendance Notification

```php
namespace App\Notifications;

class AttendanceNotification extends BaseNotification
{
    /**
     * Create a new notification instance.
     */
    public function __construct(string $title, string $message, string $classId = null, string $type = 'info')
    {
        $this->data = [
            'title' => $title,
            'message' => $message,
            'action_url' => $classId ? route('student.attendance.show', $classId) : null,
            'class_id' => $classId,
        ];
        
        $this->type = $type;
    }
    
    /**
     * Optional: Override via method if this notification type
     * should use different delivery channels
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', 'mail']; // Add mail channel
    }
}
```

### Extending the Notification Service

To integrate your custom notification with the notification service, add a new method:

```php
// In App\Services\NotificationService

public function notifyAttendance(User $user, string $title, string $message, ?string $classId = null, string $type = 'info')
{
    try {
        $notification = new AttendanceNotification($title, $message, $classId, $type);
        $user->notify($notification);
        
        Log::info('Attendance notification sent to user', [
            'user_id' => $user->id,
            'title' => $title,
            'class_id' => $classId,
            'type' => $type
        ]);
        
        return true;
    } catch (\Exception $e) {
        Log::error('Failed to send attendance notification', [
            'user_id' => $user->id,
            'title' => $title,
            'class_id' => $classId,
            'error' => $e->getMessage()
        ]);
        
        return false;
    }
}