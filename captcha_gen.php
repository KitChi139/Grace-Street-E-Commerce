<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


spl_autoload_register(function ($class) {
    $prefix = 'Gregwar\\Captcha\\';
    $base_dir = __DIR__ . '/Captcha-master/src/Gregwar/Captcha/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

use Gregwar\Captcha\CaptchaBuilder;

if (!extension_loaded('gd')) {
    header('Content-type: image/png');
    // Create a simple error image if GD is somehow missing but this file is called
    // Wait, if GD is missing, we can't even create a PNG image here easily.
    // Let's just output a text error if it's not an image request, or just die.
    die('GD extension is required for captcha generation.');
}

$builder = new CaptchaBuilder;
$builder->build();
$_SESSION['captcha_phrase'] = $builder->getPhrase();

header('Content-type: image/jpeg');
$builder->output();
?>
