<?php
require_once __DIR__ . '/../core/auth.php';
Auth::startSession();
Auth::logout();
header('Location: index.php');
exit;
