<?php
session_start();
$lang = $_GET['lang'] ?? ($_COOKIE['lang'] ?? 'en');
session_destroy();
header("Location: index.php?lang=$lang");
exit();