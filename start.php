<?php

/**
 * @author  Foma Tuturov <fomiash@yandex.ru>
 */

$componentList = searchAllComponents();

if (end($argv) === '--help') {
    die (
        PHP_EOL . "Test functional update for the HLEB project." .
        PHP_EOL . "--remove (delete files from all components)" .
        PHP_EOL . "--add    (update files from all components)" . PHP_EOL .
        PHP_EOL . "Components:" . PHP_EOL . "   " . implode(PHP_EOL . "   ", array_flip($componentList)) . PHP_EOL
    );
}

if (end($argv) === '--remove') {
    $action = false;
} else if (end($argv) === '--add') {
    $action = true;
} else {
    exit(PHP_EOL . 'For details, repeat the command with the `--help` flag.' . PHP_EOL);
}

foreach ($componentList as $name => $path) {
    if ($action) {
        echo PHP_EOL . '[++++++++++] INSTALL COMPONENT: ' . $name . PHP_EOL;
    } else {
        echo PHP_EOL . '[----------] REMOVE COMPONENT: ' . $name . PHP_EOL;
    }

    executeComponent($path, $argv);
}

function searchAllComponents(string $vendor = __DIR__ . '/../../', int $level = 0): array
{
    $result = [];
    $dir = realpath($vendor);
    $vendorDir = opendir($dir);
    while ($file = readdir($vendorDir)) {
        $path = $dir . DIRECTORY_SEPARATOR . $file;
        if (is_dir($path) && $file != '.' && $file != '..') {
            if (empty($level)) {
                $components = searchAllComponents($path, 1);
                if ($components) {
                    foreach ($components as $key => $componentPath) {
                        $result[$file . '/' . $key] = $componentPath;
                    }
                }
            } else {
                $excluded = ['combine', 'updater'];
                if (file_exists($path . DIRECTORY_SEPARATOR . 'start.php') && !in_array($file, $excluded)) {
                    $result[$file] = $path;
                }
            }
        }
    }

    return $result;
}

function executeComponent($path, $argv)
{
    $info = $path . DIRECTORY_SEPARATOR . 'combine-info.txt';
    if (file_exists($info)) {
        echo PHP_EOL . file_get_contents($info) . PHP_EOL;
    }
    echo PHP_EOL;
    require $path . DIRECTORY_SEPARATOR . 'start.php';
}

