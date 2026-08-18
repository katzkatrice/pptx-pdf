<?php
declare(strict_types=1);

header('Content-Type: text/html; charset=UTF-8');

$phpVersion = PHP_VERSION;
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$time = date('Y-m-d H:i:s');
$appEnv = getenv('APP_ENV') ?: 'production';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Favicon -->
    <link rel="icon" type="assets/image/png" href="assets/images/favicon.png">
    <link rel="shortcut icon" href="assets/images/favicon.png">

    <title>PPTX to PDF Converter</title>

    <style>

    </style>
</head>
<body>

</body>
</html>
