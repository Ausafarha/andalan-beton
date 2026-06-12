<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/cloudinary.php';

// Test upload dummy
$testFile = [
    'tmp_name' => 'https://picsum.photos/200/300', // URL dummy
    'name' => 'test.jpg'
];

echo "Cloudinary config loaded!";