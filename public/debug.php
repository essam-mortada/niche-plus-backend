<?php
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache');
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug - <?php echo time(); ?></title>
    <style>
        body { font-family: monospace; padding: 20px; }
        .ok { color: green; font-weight: bold; }
        .bad { color: red; font-weight: bold; }
        .section { margin: 20px 0; padding: 15px; border: 2px solid #333; background: #f5f5f5; }
        pre { background: #fff; padding: 10px; overflow: auto; }
    </style>
</head>
<body>

<h1>🔍 Laravel Debug - <?php echo date('H:i:s'); ?></h1>

<div class="section">
    <h2>1. PHP Version</h2>
    <p>Version: <?php echo phpversion(); ?> 
    <?php echo version_compare(phpversion(), '8.1.0', '>=') ? '<span class="ok">✓ OK</span>' : '<span class="bad">✗ TOO OLD</span>'; ?>
    </p>
</div>

<div class="section">
    <h2>2. PHP Extensions</h2>
    <?php
    foreach (['pdo', 'mbstring', 'openssl', 'tokenizer', 'xml', 'ctype', 'json'] as $ext) {
        $loaded = extension_loaded($ext);
        echo "$ext: " . ($loaded ? '<span class="ok">✓</span>' : '<span class="bad">✗</span>') . "<br>";
    }
    ?>
</div>

<div class="section">
    <h2>3. Required Files</h2>
    <?php
    $autoload = __DIR__.'/../vendor/autoload.php';
    $bootstrap = __DIR__.'/../bootstrap/app.php';
    $env = __DIR__.'/../.env';
    
    echo "vendor/autoload.php: " . (file_exists($autoload) ? '<span class="ok">✓</span>' : '<span class="bad">✗</span>') . "<br>";
    echo "bootstrap/app.php: " . (file_exists($bootstrap) ? '<span class="ok">✓</span>' : '<span class="bad">✗</span>') . "<br>";
    echo ".env: " . (file_exists($env) ? '<span class="ok">✓</span>' : '<span class="bad">✗</span>') . "<br>";
    ?>
</div>

<div class="section">
    <h2>4. Directory Permissions</h2>
    <?php
    $storage = __DIR__.'/../storage';
    $cache = __DIR__.'/../bootstrap/cache';
    
    echo "storage: " . (is_writable($storage) ? '<span class="ok">✓ writable</span>' : '<span class="bad">✗ not writable</span>') . "<br>";
    echo "bootstrap/cache: " . (is_writable($cache) ? '<span class="ok">✓ writable</span>' : '<span class="bad">✗ not writable</span>') . "<br>";
    ?>
</div>

<div class="section">
    <h2>5. 🚀 LOADING LARAVEL</h2>
    <?php
    try {
        echo "<p>→ Loading autoloader...</p>";
        flush();
        
        require __DIR__.'/../vendor/autoload.php';
        echo "<p class='ok'>✓ Autoloader loaded</p>";
        flush();
        
        echo "<p>→ Loading bootstrap...</p>";
        flush();
        
        $app = require_once __DIR__.'/../bootstrap/app.php';
        echo "<p class='ok'>✓ Bootstrap loaded</p>";
        flush();
        
        echo "<p>→ Creating kernel...</p>";
        flush();
        
        $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
        echo "<p class='ok'>✓ Kernel created</p>";
        flush();
        
        echo "<p>→ Handling request...</p>";
        flush();
        
        $response = $kernel->handle(
            $request = Illuminate\Http\Request::capture()
        );
        
        echo "<p class='ok'>✓✓✓ REQUEST HANDLED SUCCESSFULLY!</p>";
        echo "<h3 class='ok'>🎉 LARAVEL IS WORKING!</h3>";
        echo "<p>Your API should be accessible at /api/test</p>";
        
    } catch (\Throwable $e) {
        echo "<p class='bad'>✗✗✗ ERROR OCCURRED</p>";
        echo "<p><strong>Error Type:</strong> " . htmlspecialchars(get_class($e)) . "</p>";
        echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
        echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
        echo "<details><summary><strong>Click for full stack trace</strong></summary>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        echo "</details>";
    }
    ?>
</div>

<div class="section">
    <h2>6. Laravel Log (Last 40 Lines)</h2>
    <?php
    $logFile = __DIR__.'/../storage/logs/laravel.log';
    if (file_exists($logFile)) {
        echo "<p class='ok'>Log file found</p>";
        $lines = file($logFile);
        $recent = array_slice($lines, -40);
        echo "<pre>" . htmlspecialchars(implode('', $recent)) . "</pre>";
    } else {
        echo "<p class='bad'>No log file found at: " . htmlspecialchars($logFile) . "</p>";
    }
    ?>
</div>

<hr>
<p><strong>⚠️ DELETE THIS FILE after debugging!</strong></p>
<p>Page loaded at: <?php echo date('Y-m-d H:i:s'); ?></p>

</body>
</html>
