<form method="post">
<input type="hidden" name="page" value="plek-options" />
<?php 

$notifications = new PlekNotificationHandler();
$notifications -> prepare_backend_notification_items();
$notifications -> display();
?>
</form>