<?php

// Load composer autoloader if available
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

// Load Yii class
if (file_exists(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php')) {
    require_once __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';
}

// Simple autoloader for testing without composer (fallback)
spl_autoload_register(function ($class) {
    // Convert namespace to file path
    $file = str_replace(['App\\', '\\'], ['src/', '/'], $class) . '.php';
    
    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    
    // Handle Tests namespace
    $file = str_replace(['Tests\\', '\\'], ['tests/', '/'], $class) . '.php';
    
    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    
    return false;
});

// Define basic constants that might be needed
if (!defined('YII_DEBUG')) {
    define('YII_DEBUG', true);
}

if (!defined('YII_ENV')) {
    define('YII_ENV', 'test');
}