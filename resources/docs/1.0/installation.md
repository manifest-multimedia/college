# Installation

---

- [Requirements](#requirements)
- [Server Setup](#server-setup)
- [Application Installation](#application-installation)
- [Database Setup](#database-setup)
- [Environment Configuration](#environment-configuration)
- [Running the Application](#running-the-application)

<a name="requirements"></a>
## Requirements

Before installing the College Portal, make sure your server meets the following requirements:

- PHP 8.2 or higher
- MySQL 8.0+ or MariaDB 10.3+
- Composer 2.0+
- Node.js 16+ and NPM
- Apache or Nginx web server
- SSL certificate (recommended for production)
- PHP Extensions:
  - BCMath PHP Extension
  - Ctype PHP Extension
  - Fileinfo PHP Extension
  - JSON PHP Extension
  - Mbstring PHP Extension
  - OpenSSL PHP Extension
  - PDO PHP Extension
  - Tokenizer PHP Extension
  - XML PHP Extension
  - GD PHP Extension
  - Zip PHP Extension

<a name="server-setup"></a>
## Server Setup

### Apache Configuration

If you're using Apache, ensure the `mod_rewrite` module is enabled and that your virtual host configuration allows `.htaccess` files to override settings:

```apache
<VirtualHost *:80>
    ServerName college.example.com
    DocumentRoot /path/to/college.local/public

    <Directory "/path/to/college.local/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### Nginx Configuration

If using Nginx, a typical configuration would look like:

```nginx
server {
    listen 80;
    server_name college.example.com;
    root /path/to/college.local/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

<a name="application-installation"></a>
## Application Installation

### Clone the Repository

```bash
git clone https://your-repository-url/college.local.git
cd college.local
```

### Install Dependencies

```bash
composer install --optimize-autoloader --no-dev  # For production
# OR
composer install  # For development

npm install
npm run build     # For production
# OR
npm run dev       # For development
```

<a name="database-setup"></a>
## Database Setup

1. Create a new database for your application:

```sql
CREATE DATABASE college_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'college_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT ALL PRIVILEGES ON college_db.* TO 'college_user'@'localhost';
FLUSH PRIVILEGES;
```

2. Run the migrations and seed the database:

```bash
php artisan migrate --seed
```

<a name="environment-configuration"></a>
## Environment Configuration

1. Copy the example environment file:

```bash
cp .env.example .env
```

2. Generate the application key:

```bash
php artisan key:generate
```

3. Configure your environment settings:

```
APP_NAME="College Portal"
APP_ENV=production
APP_KEY=base64:your-generated-key
APP_DEBUG=false
APP_URL=https://college.example.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=college_db
DB_USERNAME=college_user
DB_PASSWORD=strong_password

# Mail configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your-email@example.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=info@college.example.com
MAIL_FROM_NAME="${APP_NAME}"

# Pusher for real-time features
PUSHER_APP_ID=your-pusher-app-id
PUSHER_APP_KEY=your-pusher-key
PUSHER_APP_SECRET=your-pusher-secret
PUSHER_APP_CLUSTER=mt1

# Additional service configurations
# ...
```

<a name="running-the-application"></a>
## Running the Application

After completing the installation and configuration, set proper directory permissions:

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

For the scheduler and queue worker, add the following to your server's crontab:

```bash
* * * * * cd /path/to/college.local && php artisan schedule:run >> /dev/null 2>&1
```

Start the queue worker (preferably using Supervisor):

```bash
php artisan queue:work --queue=high,default,low
```

You can now access your College Portal by visiting your configured domain in a web browser.

### Initial Login

After installation, you can log in using the default administrator account:

- **Email**: admin@example.com
- **Password**: password

> {warning} Make sure to change this password immediately after your first login!