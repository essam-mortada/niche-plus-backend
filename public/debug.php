<?php
// Enhanced Debug v<?php echo time(); ?> - NO CACHE
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Enhanced Laravel Debug - " . date('H:i:s') . "</h1>";
echo "<style>body{font-family:monospace;}.ok{color:green;}.bad{color:red;}.box{margin:15px 0;padding:10px;border:1px solid #ddd;background:#f9f9f9;}</style>";

// 1. PHP Version
echo "<div class='box'><h2>1. PHP Version</h2>";
echo "Version: " . phpversion() . " ";
echo version_compare(phpversion(), '8.1.0', '>=') ? "<span class='ok'>✓</span>" : "<span class='bad'>✗ Need 8.1+</span>";
echo "</div>";

// 2. Extensions
echo "<div class='box'><h2>2. PHP Extensions</h2>";
foreach (['pdo', 'mbstring', 'openssl', 'tokenizer', 'xml', 'ctype', 'json'] as $ext) {
    echo "$ext: " . (extension_loaded($ext) ? "<span class='ok'>✓</span>" : "<span class='bad'>✗</span>") . "<br>";
}
echo "</div>";

// 3. Files
echo "<div class='box'><h2>3. Required Files</h2>";
$files = [
    'vendor/autoload.php' => __DIR__.'/../vendor/autoload.php',
    'bootstrap/app.php' => __DIR__.'/../bootstrap/app.php',
    '.env' => __DIR__.'/../.env'
];
foreach ($files as $name => $path) {
    echo "$name: " . (file_exists($path) ? "<span class='ok'>✓</span>" : "<span class='bad'>✗ MISSING</span>") . "<br>";
}
echo "</div>";

// 4. Permissions
echo "<div class='box'><h2>4. Directory Permissions</h2>";
$dirs = [
    'storage' => __DIR__.'/../storage',
    'storage/logs' => __DIR__.'/../storage/logs',
    'storage/framework' => __DIR__.'/../storage/framework',
    'bootstrap/cache' => __DIR__.'/../bootstrap/cache'
];
foreach ($dirs as $name => $path) {
    if (is_dir($path)) {
        echo "$name: " . (is_writable($path) ? "<span class='ok'>✓ writable</span>" : "<span class='bad'>✗ not writable</span>") . "<br>";
    } else {
        echo "$name: <span class='bad'>✗ doesn't exist</span><br>";
    }
}
echo "</div>";

// 5. LOAD LARAVEL
echo "<div class='box'><h2>5. 🚀 Loading Laravel...</h2>";
ob_start();
try {
    echo "→ Loading autoloader...<br>";
    require __DIR__.'/../vendor/autoload.php';
    echo "<span class='ok'>✓ Autoloader OK</span><br><br>";
    
    echo "→ Loading bootstrap...<br>";
    $app = require_once __DIR__.'/../bootstrap/app.php';
    echo "<span class='ok'>✓ Bootstrap OK</span><br><br>";
    
    echo "→ Creating kernel...<br>";
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    echo "<span class='ok'>✓ Kernel OK</span><br><br>";
    
    echo "→ Handling request...<br>";
    $response = $kernel->handle(
        $request = Illuminate\Http\Request::capture()
    );
    echo "<span class='ok'>✓✓✓ REQUEST HANDLED!</span><br><br>";
    
    echo "<h3 class='ok'>🎉 LARAVEL IS WORKING!</h3>";
    echo "Your API should be accessible now.<br>";
    
} catch (\Throwable $e) {
    $output = ob_get_clean();
    echo $output;
    echo "<br><span class='bad'>✗✗✗ LARAVEL FAILED TO LOAD</span><br><br>";
    echo "<strong>Error:</strong> " . get_class($e) . "<br>";
    echo "<strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "<strong>File:</strong> " . htmlspecialchars($e->getFile()) . ":" . $e->getLine() . "<br><br>";
    echo "<details><summary>Click for Stack Trace</summary>";
    echo "<pre style='font-size:11px;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</details>";
}
$output = ob_get_clean();
echo $output;
echo "</div>";

// 6. Laravel Log
echo "<div class='box'><h2>6. Laravel Log (last 30 lines)</h2>";
$logFile = __DIR__.'/../storage/logs/laravel.log';
if (file_exists($logFile)) {
    $lines = file($logFile);
    $recent = array_slice($lines, -30);
    echo "<pre style='font-size:11px;background:#fff;padding:10px;overflow:auto;max-height:400px;'>";
    echo htmlspecialchars(implode('', $recent));
    echo "</pre>";
} else {
    echo "<span class='bad'>No log file found</span>";
}
echo "</div>";

echo "<hr><p><strong>⚠️ DELETE THIS FILE after debugging!</strong></p>";
echo "<p>Loaded at: " . date('Y-m-d H:i:s') . "</p>";
?>
