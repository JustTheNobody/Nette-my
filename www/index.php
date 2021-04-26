<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

define('WWW_DIR', dirname(__FILE__)); // path to the web root
define('IMG_DIR', WWW_DIR . '/img'); // path to the img root

App\Bootstrap::boot()
    ->createContainer()
    ->getByType(Nette\Application\Application::class)
    ->run();
