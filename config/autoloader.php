<?php
spl_autoload_register(function ($class) {
  if (strpos($class, 'App\\') === 0) {
    $file = dirname(__DIR__) . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
    if (is_file($file)) require_once $file;
  }
});

$db = (new App\Core\Database())->connection();
