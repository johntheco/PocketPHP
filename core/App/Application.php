<?php


class Application
{
    public $name = "";
    public $path = "";
    private $configs = [];
    private $controllers = [];
    private $middleware = [];
    private $services = [];
    private $models = [];
    private $routes = [];
    private $views = [];
    private $components = [
        'app',
        'config',
        'controllers',
        'middleware',
        'services',
        'models',
        'routes',
    ];


    /** Ctor */
    public function __construct($name, $path)
    {
        $this->name = $name;
        $this->path = $path;

        Pocket::Router()->setApplication($name);

        foreach ($this->components as $component) {
            $sourceFiles = [];

            $componentPath = "{$path}/{$component}";
            $sourceFile = "{$componentPath}.php";

            if (is_file($sourceFile) && file_exists($sourceFile)) {
                $sourceFiles[] = $sourceFile;
                require_once($sourceFile);
            }

            if (is_dir($componentPath)) {
                Functions::Walk($componentPath, function($sourceFile) use (&$sourceFiles) {
                    if (is_file($sourceFile) && file_exists($sourceFile) && end(explode(".", $sourceFile)) === "php") {
                        $sourceFiles[] = $sourceFile;
                        require_once($sourceFile);
                    }
    
                    return $sourceFile;
                });
            }

            $this->{$component} = $sourceFiles;
        }

        Pocket::Router()->unsetApplication();
    }

    public function middleware()
    {
        $middleware = [];

        foreach ($this->middleware as $middlewarePath) {
            $explodedMiddlewarePath = explode('/', $middlewarePath);
            $middlewareFile = end($explodedMiddlewarePath);
            $explodedMiddlewareFile = explode('.', $middlewareFile);
            $middlewareName = array_shift($explodedMiddlewareFile);

            $middleware[$middlewareName] = [
                'path'      => $middlewarePath,
                'file'      => $middlewareFile,
                'name'      => $middleware,
            ];
        }

        return $middleware;
    }
}
