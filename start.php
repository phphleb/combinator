<?php

/**
 * @author  Foma Tuturov <fomiash@yandex.ru>
 */

use Phphleb\Updater\Classes\Data;

$componentList = searchAllComponents();

if (end($argv) === '--help') {
    die (
        PHP_EOL . "Test functional update for the HLEB project." .
        PHP_EOL . "--remove (delete files from all components)" .
        PHP_EOL . "--add    (update files from all components)" . PHP_EOL .
        PHP_EOL . "Components:" . PHP_EOL . "   " . implode(PHP_EOL . "   ", array_flip($componentList)) . PHP_EOL
    );
}

$quiet = false;
$configPath = '';

foreach($argv as $key => $param) {
    if ($param === '--quiet') {
        $quiet = true;
        unset($argv[$key]);
    }
    if (strpos($param, '--with-config-file-path=') === 0) {
        $configList = explode('--with-config-file-path=', $param);
        if (count($configList) === 2) {
            $configPath = $configList[1];
            unset($argv[$key]);
        }
    }
}

if (end($argv) === '--remove') {
    $action = false;
} else if (end($argv) === '--add') {
    $action = true;
} else {
    exit(PHP_EOL . 'For details, repeat the command with the `--help` flag.' . PHP_EOL);
}

include_once __DIR__ . '/../updater/classes/Data.php';

Data::setDisableConfirmationOfDelete();

foreach ($componentList as $name => $path) {
    !$quiet or ob_start();
    if ($action) {
        echo PHP_EOL . '[++++++++++] INSTALL COMPONENT: ' . $name . PHP_EOL;
    } else {
        echo PHP_EOL . '[----------] REMOVE COMPONENT: ' . $name . PHP_EOL;
    }
    executeComponent($path, $argv, $configPath);
    !$quiet or ob_end_clean();
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
                $excluded = ['combinator', 'updater', 'combine'];
                if (file_exists($path . DIRECTORY_SEPARATOR . 'start.php') && !in_array($file, $excluded)) {
                    $result[$file] = $path;
                }
            }
        }
    }

    return $result;
}

function executeComponent(string $path, array $argv, string $configPath)
{
    $config = prepareConfigData(trim($configPath, '/\\ .'));
    Data::setConfig($config);

    $info = $path . DIRECTORY_SEPARATOR . 'combinator-info.txt';
    if (file_exists($info)) {
        echo PHP_EOL . file_get_contents($info) . PHP_EOL;
    }
    require $path . DIRECTORY_SEPARATOR . 'start.php';
}

function prepareConfigData(string $configPath): array
{
    $parts = explode(DIRECTORY_SEPARATOR, rtrim(__DIR__, '/\\'));
    $count = count($parts);
    for ($i = ($count - 1); $i > 0; $i--) {
        unset($parts[$i]);
        $path = implode(DIRECTORY_SEPARATOR, $parts) . DIRECTORY_SEPARATOR;
        if (file_exists($path . 'default.start.hleb.php') || file_exists($path . 'start.hleb.php')) {
            $realPath = $path . ($configPath ? $configPath : 'updater.json');

            if (file_exists($realPath)) {
                return json_decode(file_get_contents($realPath), true);
            } else if ($configPath) {
                throw new ErrorException("The configuration file specified in `/$configPath` was not found in the project root!");
            }
            break;
        }
    }
    return [];
}

