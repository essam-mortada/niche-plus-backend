<?php
// Temporary debug file - DELETE after fixing the issue

echo "<h1>Laravel Debug Info</h1>";

// Check PHP version
echo "<h2>PHP Version</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Required: 8.1+<br><br>";

// Check if vendor exists
echo "<h2>Vendor Directory</h2>";
echo "Vendor exists: " . (file_exists(__DIR__.'/../vendor/autoload.php') ? 'YES ✓' : 'NO ✗') . "<br><br>";

// Check if bootstrap exists
echo "<h2>Bootstrap File</h2>";
echo "Bootstrap exists: " . (file_exists(__DIR__.'/../bootstrap/app.php') ? 'YES ✓' : 'NO ✗') . "<br><br>";

// Check .env file
echo "<h2>.env File</h2>";
echo ".env exists: " . (file_exists(__DIR__.'/../.env') ? 'YES ✓' : 'NO ✗') . "<br><br>";

// Check storage permissions
echo "<h2>Storage Permissions</h2>";
$storage = __DIR__.'/../storage';
echo "Storage exists: " . (is_dir($storage) ? 'YES ✓' : 'NO ✗') . "<br>";
echo "Storage writable: " . (is_writable($storage) ? 'YES ✓' : 'NO ✗') . "<br>";
echo "Storage/logs writable: " . (is_writable($storage.'/logs') ? 'YES ✓' : 'NO ✗') . "<br>";
echo "Storage/framework writable: " . (is_writable($storage.'/framework') ? 'YES ✓' : 'NO ✗') . "<br><br>";

// Check bootstrap/cache permissions
echo "<h2>Bootstrap Cache Permissions</h2>";
$bootstrap = __DIR__.'/../bootstrap/cache';
echo "Bootstrap/cache exists: " . (is_dir($bootstrap) ? 'YES ✓' : 'NO ✗') . "<br>";
echo "Bootstrap/cache writable: " . (is_writable($bootstrap) ? 'YES ✓' : 'NO ✗') . "<br><br>";

// Check required PHP extensions
echo "<h2>Required PHP Extensions</h2>";
$required = ['openssl', 'pdo', 'mbstring', 'tokenizer', 'xml', 'ctype', 'json', 'bcmath', 'fileinfo'];
foreach ($required as $ext) {
    echo "$ext: " . (extension_loaded($ext) ? 'YES ✓' : 'NO ✗') . "<br>";
}

echo "<br><h2>Try to load Laravel</h2>";
try {
    require __DIR__.'/../vendor/autoload.php';
    echo "Autoload: SUCCESS ✓<br>";
    
    $app = require_once __DIR__.'/../bootstrap/app.php';
    echo "Bootstrap: SUCCESS ✓<br>";
    
    echo "<br><strong>Laravel loaded successfully! The issue might be in your routes or controllers.</strong>";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
}

echo "<br><br><strong>DELETE THIS FILE (debug.php) AFTER FIXING!</strong>";
?>
