<!DOCTYPE html>
<html>
<head>
    <title>New Feature Request: <?php echo e($feature_title); ?></title>
</head>
<body>
    <h1>New Feature Request: <?php echo e($feature_title); ?></h1>
    <p><strong>Description:</strong></p>
    <p><?php echo e($feature_description); ?></p>
    <p><strong>User Name:</strong> <?php echo e($user_name ?? 'N/A'); ?></p>
    <p><strong>User Email:</strong> <?php echo e($user_email ?? 'N/A'); ?></p>
</body>
</html><?php /**PATH /home/johnsonsebire/www/college/resources/views/emails/feature_request.blade.php ENDPATH**/ ?>