<!DOCTYPE html>
<html>
<head>
    <title>Support Request: <?php echo e($user_name ?? "N/A"); ?></title>
</head>
<body>
    <h1>New Support Request Received from: <?php echo e($user_name ?? 'N/A'); ?></h1>
    <p><strong>Message:</strong></p>
    <p><?php echo e($support_message); ?></p>
    <p><strong>User Name:</strong> <?php echo e($user_name ?? 'N/A'); ?></p>
    <p><strong>User Email:</strong> <?php echo e($user_email ?? 'N/A'); ?></p>
</body>
</html><?php /**PATH /home/johnsonsebire/www/college/resources/views/emails/support_request.blade.php ENDPATH**/ ?>