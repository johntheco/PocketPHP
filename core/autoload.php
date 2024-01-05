<?php


function autoload($directory, $directories = [], $interfaces = [], $implementations = []) {
    foreach (glob("{$directory}/*") as $source) {
        if (is_file($source) && strpos($source, "Interface") !== false) {
            $interfaces[] = $source;
        }
        if (is_file($source) && strpos($source, "Interface") === false) {
            $implementations[] = $source;
        }
        if (is_dir($source)) {
            $directories[] = $source;
        }
    }

    // Interfaces are first to require
    foreach ($interfaces as $interface) {
        require_once($interface);
    }

    // Implementations are second
    foreach ($implementations as $implementation ) {
        require_once($implementation);
    }

    // Deeper level source files are last
    foreach ($directories as $directory) {
        autoload($directory);
    }
}


// Loading core
autoload("core/*");


// Initializing server's configuration and environment settings
Pocket::Configuration((new Configuration())->initializeEnvironmentConfiguration());


list($user, $password) = ($_SERVER['SERVER_NAME'] == 'pocketphp')
    ? ['root', '']
    : ['root', 'password'];


Pocket::SQL(new SqlDatabase([
    'type'      => 'mysql',
    'host'      => 'localhost',
    'port'      => '3306',
    'user'      => $user,
    'pass'      => $password,
    'charset'   => 'utf8',
    'options'   => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
]));


Pocket::Memcached(new Memcached());
Pocket::Memcached()->addServer('localhost', 11211);


// Debug information
if (isset($_REQUEST['debug'])) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

// Initializing all apps we have
Pocket::Router(new Router(new Request(), new Response()));
Pocket::Builder(new ApplicationBuilder());
