<?php

/**
 * This file contains the Router class. This is the main
 * system, which handles all request/respond functionality
 * for usual, or regexp, or static assets requests, while
 * catching errors, such as 404 and 405.
 * 
 * 
 * @package PocketPHP
 * @version 0.0.1
 * @author  John Theco <john.theco.dev@gmail.com>
 * @license MIT
 * 
 * @link    https://github.com/johntheco/pocketphp
 * @see     https://github.com/johntheco/pocketphp#readme
 */


class Router
{
    private $request;
    private $response;
    private $supportedHttpMethods = ['get', 'post', 'put', 'patch', 'delete'];
    private $serviceMethods = ['prefix', 'middleware', 'controller', 'view'];
    private $get    = [];
    private $post   = [];
    private $put    = [];
    private $patch  = [];
    private $delete = [];
    private $loadingApplication = null;
    private $currentApplication = null;
    private bool $singularCall = false;
    private string $prefix = "";
    private array $middleware = [];
    private array $routes = [];
    private closure|null $requestHandler;
    private string $requestedRoute;
    private string $requestedMethod;


    public function __construct(Request $request, Response $response)
    {
        // Setting up request and response
        $this->request = $request;
        $this->response = $response;

        // Preparing two main fields of router
        $this->requestHandler = null;
        $this->requestedRoute = $this->formatRoute($request->requestUri);
        $this->requestedMethod = $request->requestMethod;

        // Preparing routes array
        foreach ($this->supportedHttpMethods as $method) {
            $this->routes[$method] = [];
        }
    }

    public function setApplication($application)
    {
        $this->application = $application;
    }

    public function unsetApplication()
    {
        $this->application = null;
    }

    public function __call($method, $arguments)
    {
        $argumentsCount = count($arguments);
        $route = null;
        $routeHandler = null;

        switch ($argumentsCount) {
            // Simple callback
            case 2: {
                list($route, $routeHandler) = $arguments;
            } break;

            // Controller callback
            case 3: {
                list($route, $controllerClass, $controllerMethod) = $arguments;
                $controllerInstance = new $controllerClass();
                $reflection = new ReflectionClass($controllerClass);
                $routeHandler = $reflection->getMethod($controllerMethod)->getClosure($controllerInstance);
            } break;

            default: {
                $route = $arguments[0];
                die("Error in route declaration {$route}");
            }
        }

        if ($this->prefix && $this->prefix !== "/") {
            $route = $this->formatRoute("{$this->prefix}{$route}");
        }

        if (! in_array($method, $this->supportedHttpMethods)) {
            header("{$this->request->serverProtocol} 405 Method Not Allowed");
            die("Unsupported http method: {$method}");
        }

        if (array_key_exists($route, $this->routes[$method])) {
            die("Conflicting routes: {$route}");
        }

        $this->routes[$method][$route] = [
            'application' => $this->application,
            'middleware'  => $this->middleware,
            'controller'  => $this->controller,
            'view'        => $this->view,
            'callback'    => $routeHandler,
        ];

        if (isset($_REQUEST['callDebug'])) {
            echo "Method: {$method}<br>";
            echo "Current application: {$this->loadingApplication}<br>";
            echo "Route: {$route}<br>";
            echo "Handler type: " . gettype($routeHandler) . "<br>";
            if (count($this->middleware) > 0) {
                echo "Middleware: " . implode(', ', $this->middleware) . "<br>";
            }
            echo "<hr>";
        }

        // If route gets called by a singular method
        // cleaning up all our additional parameters
        if ($this->singularCall) {
            $this->middleware = [];
            $this->prefix = "";
        }
    }

    public function middleware($middleware, $callback = null)
    {
        $this->middleware = (is_array($middleware)) ? $middleware : [$middleware];
        $this->singularCall = ($callback) ? false : true;

        if ($callback) {
            $callback();
            $this->middleware = [];
        }

        return $this;
    }

    public function prefix($prefix, $callback = null)
    {
        $this->prefix = $prefix;
        $this->singularCall = ($callback) ? false : true;

        if ($callback) {
            $callback();
            $this->prefix = "";
        }

        return $this;
    }

    private function formatRoute($route)
    {
        $result = rtrim(rtrim($route, '/'), '?');
        return ($result === '') ? '/' : $result;
    }

    private function regexpRequestHandler($requestedRoute, $methodDictionary)
    {
        $routeList = $this->{strtolower($this->request->requestMethod)};

        if (! is_null($routeList)) {
            foreach ($routeList as $applicationName => $routes) {
                foreach ($routes as $key => $applicationObject) {
                    $originalKey = $key;

                    if (str_contains($key, "<0-9>")) {
                        $key = str_replace("/", "\/", $key);
                        $key = str_replace("<0-9>", "[0-9]*", $key);
                        $key = "#{$key}#";
                    }

                    if (@preg_match($key, null) !== false) {

                        // Need to check if static file with the same name exists
                        $sPos = strpos($key, 'assets/');
                        $sRoute = substr($key, $sPos);
                        $sPath = ROOT . '/' . ASSETS . '/' . $sRoute;

                        if (file_exists($sPath)) {
                            continue;
                        }

                        if (preg_match($key, $requestedRoute)) {
                            $this->currentApplication = Pocket::Applications()[$applicationName];
                            return $this->{$this->request->requestMethod}[$applicationName][$originalKey];
                        }
                    } else {
                        continue;
                    }
                }
            }
        }
    }

    private function staticRequestHandler() {
        $pos = strpos($this->requestedRoute, 'assets/');
        $route = substr($this->requestedRoute, $pos);
        $path = rawurldecode(ROOT . '/' . ASSETS . '/' . $route);
        $slash = strrpos($path, '/');
        $dot = strrpos($path, '.');

        if ($dot < $slash || ! file_exists($path)) {
            header("HTTP/1.1 404 Not Found");
            header("HTTP/1.1 200 Forbidden");
            die(header('Location: /404'));
        }

        if (substr($path, strlen($path) - 4) === ".css") {
            header("Content-Type: text/css");
            die(readfile($path));
        } else if (substr($path, strlen($path) - 3) === ".js") {
            header("Content-Type: text/javascript");
            die(readfile($path));
        } else {
            $mimeType = mime_content_type($path);
            header("Content-Type: {$mimeType}");
            die(readfile($path));
        }
    }

    private function defaultRequestHandler()
    {
        die(strpos($this->requestedRoute, "assets") !== false ?: header("Location: /404"));
    }

    private function resolve() {
        $route = $this->routes[$this->requestedMethod][$this->requestedRoute];

        if (is_null($route)) {
            if ($this->requestedRoute == "/404") {
                die("404");
            }

            if ($this->requestHandler = $this->regexpRequestHandler($this->requestedRoute, $this->supportedHttpMethods)) {
                $this->handle();
            }

            $this->staticRequestHandler($this->requestedRoute);
            $this->defaultRequestHandler($this->requestedRoute);
        }

        $application = Pocket::Applications()[$route['application']];
        define("APP_ROOT", $application->path);

        foreach ($route['middleware'] as $middleware) {
            if (! in_array($middleware, array_keys($application->middleware()))) {
                continue;
            }

            $this->request->setMiddlewareResult($middleware, (new $middleware())->process($this->request));
        }

        $this->requestHandler = $route['callback'];
        $this->response->injectRequest($this->request);
        
        $this->handle();
    }

    private function handle()
    {
        die(call_user_func_array($this->requestHandler, [$this->request, $this->response]));
    }

    public function __destruct() {
        $this->resolve();
    }
}
