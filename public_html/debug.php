<?php
// Enhanced Debug Script for Laravel
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "<h1>Enhanced Laravel Debug Information</h1>";
echo "<style>body { font-family: monospace; } .success { color: green; } .error { color: red; } .section { margin: 20px 0; padding: 10px; border: 1px solid #ccc; }</style>";

// 1. PHP Version
echo "<div class='section'>";
echo "<h2>1. PHP Version</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Required: >= 8.1<br>";
if (version_compare(phpversion(), '8.1.0', '>=')) {
    echo "<span class='success'>✓ PHP version OK</span>";
} else {
    echo "<span class='error'>✗ PHP version too old</span>";
}
echo "</div>";

// 2. Required Extensions
echo "<div class='section'>";
echo "<h2>2. Required PHP Extensions</h2>";
$required = ['pdo', 'mbstring', 'openssl', 'tokenizer', 'xml', 'ctype', 'json', 'bcmath'];
foreach ($required as $ext) {
    if (extension_loaded($ext)) {
        echo "<span class='success'>✓ $ext</span><br>";
    } else {
        echo "<span class='error'>✗ $ext (MISSING)</span><br>";
    }
}
echo "</div>";

// 3. File Paths
echo "<div class='section'>";
echo "<h2>3. File Paths</h2>";
echo "Current directory: " . __DIR__ . "<br>";
echo "Parent directory: " . dirname(__DIR__) . "<br>";

$autoload = dirname(__DIR__) . '/vendor/autoload.php';
$bootstrap = dirname(__DIR__) . '/bootstrap/app.php';
$env = dirname(__DIR__) . '/.env';

echo "<br>Autoload file: $autoload<br>";
echo file_exists($autoload) ? "<span class='success'>✓ EXISTS</span>" : "<span class='error'>✗ MISSING</span>";

echo "<br><br>Bootstrap file: $bootstrap<br>";
echo file_exists($bootstrap) ? "<span class='success'>✓ EXISTS</span>" : "<span class='error'>✗ MISSING</span>";

echo "<br><br>.env file: $env<br>";
echo file_exists($env) ? "<span class='success'>✓ EXISTS</span>" : "<span class='error'>✗ MISSING</span>";
echo "</div>";

// 4. Storage Permissions
echo "<div class='section'>";
echo "<h2>4. Storage Permissions</h2>";
$storage = dirname(__DIR__) . '/storage';
$bootstrap_cache = dirname(__DIR__) . '/bootstrap/cache';

echo "Storage directory: $storage<br>";
if (is_dir($storage)) {
    echo is_writable($storage) ? "<span class='success'>✓ WRITABLE</span>" : "<span class='error'>✗ NOT WRITABLE</span>";
} else {
    echo "<span class='error'>✗ DOES NOT EXIST</span>";
}

echo "<br><br>Bootstrap cache: $bootstrap_cache<br>";
if (is_dir($bootstrap_cache)) {
    echo is_writable($bootstrap_cache) ? "<span class='success'>✓ WRITABLE</span>" : "<span class='error'>✗ NOT WRITABLE</span>";
} else {
    echo "<span class='error'>✗ DOES NOT EXIST</span>";
}
echo "</div>";

// 5. Try to load Laravel with detailed error catching
echo "<div class='section'>";
echo "<h2>5. Try to Load Laravel</h2>";

try {
    echo "Loading autoloader...<br>";
    require $autoload;
    echo "<span class='success'>✓ Autoloader loaded</span><br><br>";
    
    echo "Loading Laravel bootstrap...<br>";
    $app = require_once $bootstrap;
    echo "<span class='success'>✓ Bootstrap loaded</span><br><br>";
    
    echo "Creating kernel...<br>";
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    echo "<span class='success'>✓ Kernel created</span><br><br>";
    
    echo "Handling request...<br>";
    $response = $kernel->handle(
        $request = Illuminate\Http\Request::capture()
    );
    echo "<span class='success'>✓ Request handled successfully!</span><br><br>";
    
    echo "<h3>Laravel is working! Your API should be accessible.</h3>";
    echo "Try: <a href='/api/test'>/api/test</a><br>";
    
} catch (\Throwable $e) {
    echo "<span class='error'>✗ ERROR LOADING LARAVEL</span><br><br>";
    echo "<strong>Error Type:</strong> " . get_class($e) . "<br>";
    echo "<strong>Error Message:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>File:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Line:</strong> " . $e->getLine() . "<br><br>";
    echo "<strong>Stack Trace:</strong><br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
echo "</div>";

// 6. Check Laravel Log
echo "<div class='section'>";
echo "<h2>6. Recent Laravel Log Entries</h2>";
$log_file = dirname(__DIR__) . '/storage/logs/laravel.log';
if (file_exists($log_file)) {
    $log_content = file_get_contents($log_file);
    $log_lines = explode("\n", $log_content);
    $recent_logs = array_slice($log_lines, -50); // Last 50 lines
    echo "<pre style='background: #f5f5f5; padding: 10px; overflow-x: auto;'>";
    echo htmlspecialchars(implode("\n", $recent_logs));
    echo "</pre>";
} else {
    echo "<span class='error'>Log file not found</span>";
}
echo "</div>";

// 7. Environment Variables
echo "<div class='section'>";
echo "<h2>7. Key Environment Variables</h2>";
if (file_exists($env)) {
    $env_content = file_get_contents($env);
    $env_lines = explode("\n", $env_content);
    echo "<pre style='background: #f5f5f5; padding: 10px;'>";
    foreach ($env_lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) continue;
        
        // Hide sensitive values
        if (preg_match('/^([^=]+)=(.*)$/', $line, $matches)) {
            $key = $matches[1];
            $value = $matches[2];
            
            // Show these keys fully
            if (in_array($key, ['APP_NAME', 'APP_ENV', 'APP_DEBUG', 'APP_URL', 'DB_CONNECTION'])) {
                echo htmlspecialchars($line) . "\n";
            } else {
                echo htmlspecialchars($key) . "=" . (empty($value) ? "(empty)" : "***") . "\n";
            }
        }
    }
    echo "</pre>";
}
echo "</div>";

echo "<hr>";
echo "<p>Debug completed at " . date('Y-m-d H:i:s') . "</p>";
