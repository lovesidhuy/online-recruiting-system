<?php
require_once __DIR__ . '/../models/Database.php';
$dbInstance = new Database();
$DB = $dbInstance->connect();
?>
