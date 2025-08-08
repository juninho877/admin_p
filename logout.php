<?php
require_once 'config/config.php';

$authController = new AuthController();
$authController->logout();

header('Location: login.php');
exit;