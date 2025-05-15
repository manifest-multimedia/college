# Configuration

---

- [General Settings](#general-settings)
- [Mail Configuration](#mail-configuration)
- [Storage Settings](#storage-settings)
- [Queue Configuration](#queue-configuration)
- [Broadcasting Setup](#broadcasting-setup)
- [External Services Integration](#external-services)

<a name="general-settings"></a>
## General Settings

After installing the College Portal, you'll need to configure various application settings. Most configurations are available through the web interface in the Settings section, accessible to users with administrative privileges.

### System Settings

Navigate to **Settings → General** to configure the following:

- Institution name
- Academic year settings
- Logo and branding
- Default language
- Time zone settings
- Date and time formats

### Department Configuration

The system requires departments to be set up before adding users and courses:

1. Navigate to **Settings → Departments**
2. Use the "Add Department" button to create departments
3. Configure department details, including:
   - Department name
   - Department code
   - Department head
   - Contact information

<a name="mail-configuration"></a>
## Mail Configuration

Email functionality is crucial for notifications, password resets, and communication. Configure email settings in your `.env` file:

```
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your-email@example.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=info@college.example.com
MAIL_FROM_NAME="College Portal"
```

For more advanced mail configuration, you can modify `config/mail.php` to set up multiple mailers or configure driver-specific options.

### Mail Testing

After configuring email, test it using the artisan command:

```bash
php artisan mail:test recipient@example.com
```

<a name="storage-settings"></a>
## Storage Settings

### File Storage

The College Portal uses Laravel's filesystem to handle file storage. By default, files are stored in the `storage/app` directory. Configure your preferred storage driver in `.env`:

```
FILESYSTEM_DISK=local
```

Available options include:
- `local` - Local file system
- `public` - Public directory (accessible via web)
- `s3` - Amazon S3
- `sftp` - SFTP server

For production environments, we recommend using cloud storage like Amazon S3:

```
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your-key
AWS_SECRET_ACCESS_KEY=your-secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket
```

### Public Storage Link

To make uploaded files accessible through the web, create a symbolic link between `storage/app/public` and `public/storage`:

```bash
php artisan storage:link
```

<a name="queue-configuration"></a>
## Queue Configuration

The College Portal processes time-consuming tasks (like sending mass emails or generating reports) using Laravel's queue system. Configure your preferred queue driver in `.env`:

```
QUEUE_CONNECTION=database
```

Common queue drivers include:
- `sync` - Synchronous processing (not recommended for production)
- `database` - Uses the database to store jobs
- `redis` - Uses Redis to store jobs
- `beanstalkd` - Uses Beanstalkd to store jobs

For production environments, we recommend using Redis or a dedicated queue server:

```
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Queue Worker

To process queued jobs, start the queue worker:

```bash
php artisan queue:work
```

For production environments, use a process monitor like Supervisor to ensure the queue worker runs continuously.

<a name="broadcasting-setup"></a>
## Broadcasting Setup

The College Portal uses real-time broadcasting for notifications and chat features. Configure broadcasting in your `.env` file:

```
BROADCAST_DRIVER=pusher
```

### Pusher Configuration

The recommended broadcasting driver is Pusher. Register at [https://pusher.com/](https://pusher.com/) and create an app. Then update your `.env` file:

```
PUSHER_APP_ID=your-pusher-app-id
PUSHER_APP_KEY=your-pusher-key
PUSHER_APP_SECRET=your-pusher-secret
PUSHER_APP_CLUSTER=mt1
```

### Frontend Setup

Make sure that the Echo instance in your JavaScript has been configured with your Pusher credentials:

```javascript
// resources/js/bootstrap.js
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

<a name="external-services"></a>
## External Services Integration

### SMS Gateway

For SMS notifications and alerts, configure your preferred SMS gateway in `.env`:

```
SMS_PROVIDER=africas_talking
AFRICAS_TALKING_USERNAME=your-username
AFRICAS_TALKING_API_KEY=your-api-key
```

Supported SMS providers include:
- `africas_talking` - Africa's Talking
- `twilio` - Twilio
- `vonage` - Vonage (formerly Nexmo)

### Payment Gateway Integration

For online fee payments, configure your payment gateway:

```
PAYMENT_GATEWAY=flutterwave
FLUTTERWAVE_PUBLIC_KEY=your-public-key
FLUTTERWAVE_SECRET_KEY=your-secret-key
FLUTTERWAVE_ENCRYPTION_KEY=your-encryption-key
```

Supported payment gateways include:
- `flutterwave` - Flutterwave
- `paystack` - Paystack
- `stripe` - Stripe

### AI Services

For AI-powered features like the AI Sensei chat:

```
AI_PROVIDER=openai
OPENAI_API_KEY=your-api-key
```