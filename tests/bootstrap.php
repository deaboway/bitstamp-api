<?php

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Debug\Debug;
use Symfony\Component\Debug\DebugClassLoader;
use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\Debug\ExceptionHandler;

// enable debug
Debug::enable();
ErrorHandler::register();
ExceptionHandler::register();
DebugClassLoader::enable();
