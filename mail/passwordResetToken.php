<?php
/* @var $this yii\web\View */
/* @var $user app\models\User */

$resetLink = "https://ftasks.local/reset-password?token={$user->password_reset_token}";
?>
Hello <?= $user->username ?>,

Follow the link below to reset your password:

<?= $resetLink ?>
