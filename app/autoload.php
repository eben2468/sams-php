<?php

/*
|--------------------------------------------------------------------------
| Autoloader (plain PHP)
|--------------------------------------------------------------------------
| Registers:
|   1. A PSR-4 loader for the App\ namespace (app/).
|   2. Composer's ClassLoader for third-party packages (Carbon, Dompdf) —
|      WITHOUT executing Composer's "files" autoload, so Laravel's global
|      helper functions are never loaded and cannot collide with ours.
|   3. The application helper functions.
*/

$root = dirname(__DIR__);

// 1. App\ namespace -> app/
spl_autoload_register(function (string $class) use ($root) {
    if (!str_starts_with($class, 'App\\')) {
        return;
    }
    $relative = str_replace('\\', '/', substr($class, 4));
    $file = $root . '/app/' . $relative . '.php';
    if (is_file($file)) {
        require $file;
    }
});

// 2. Third-party packages via Composer's ClassLoader (no helper files).
$classLoaderFile = $root . '/vendor/composer/ClassLoader.php';
if (is_file($classLoaderFile)) {
    require_once $classLoaderFile;
    $loader = new \Composer\Autoload\ClassLoader();

    $psr4File = $root . '/vendor/composer/autoload_psr4.php';
    if (is_file($psr4File)) {
        foreach ((require $psr4File) as $namespace => $paths) {
            if ($namespace === 'App\\') {
                continue; // handled by our own loader above
            }
            $loader->setPsr4($namespace, $paths);
        }
    }

    $classmapFile = $root . '/vendor/composer/autoload_classmap.php';
    if (is_file($classmapFile)) {
        $classmap = require $classmapFile;
        if (is_array($classmap) && $classmap !== []) {
            foreach ($classmap as $class => $path) {
                if (str_starts_with($class, 'App\\')) {
                    unset($classmap[$class]);
                }
            }
            $loader->addClassMap($classmap);
        }
    }

    $loader->register(true);
}

// 3. Helpers.
require_once $root . '/app/helpers.php';
