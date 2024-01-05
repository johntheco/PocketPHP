<?php

/**
 * This file contains the Response class, which handles
 * templating system with public `render` method. It
 * checks, whether requested view and layout exists,
 * and then either shows an error, or renders HTML page.
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


class Response
{
    private Request $request;
    private string $view;
    private string $viewName;
    private string $title = "PocketPHP";
    private array $sections = [];
    private array $json = [];
    private array $args = [];
    private array $staticCss = [];
    private array $staticJs = [];
    private array $dynamicCss = [];
    private array $dynamicJs = [];
    private $data;
    private $layout = "main";
    private $globalLayout;
    private $favicon;
    private $description;
    private $body;
    private $void;
    private $css;
    private $js;
    private $injectCSS;
    private $injectJS;
    private $latter_js;


    public function injectRequest($request)
    {
        $this->request = $request;
    }

    public function title($title) : Response
    {
        $this->title = $title;
        return $this;
    }

    public function description($description) : Response
    {
        $this->description = $description;
        return $this;
    }

    public function section($section)
    {
        $sectionAppPath = APP_ROOT . "/views/sections/{$section}.php";
        $sectionPagePath = APP_ROOT . "/views/pages/{$this->viewName}/sections/{$section}.php";
        $sectionGlobalPath = ROOT . "/globals/views/sections/{$section}.php";

        if (file_exists($sectionPagePath)) {
            $this->sections[$section] = $sectionPagePath;
            require_once($sectionPagePath);
            return;
        }

        if (file_exists($sectionAppPath)) {
            $this->sections[$section] = $sectionAppPath;
            require_once($sectionAppPath);
            return;
        }

        if (file_exists($sectionGlobalPath)) {
            $this->sections[$section] = $sectionGlobalPath;
            require_once($sectionGlobalPath);
            return;
        }
    }

    public function json($json) : Response
    {
        $this->json = $json;
        return $this;
    }

    public function args($args) : Response
    {
        $this->args = $args;
        return $this;
    }

    private function layoutExists() : bool
    {
        $appLayoutPath = APP_ROOT . "/views/layouts/{$this->layout}.php";

        if (file_exists($appLayoutPath)) {
            $this->layout = $appLayoutPath;

            return true;
        }

        $globalLayoutPath = ROOT . "/global/views/layouts/{$this->layout}.php";

        if (file_exists($globalLayoutPath)) {
            $this->layout = $globalLayoutPath;

            return true;
        }

        return false;
    }

    public function preloadStyles($prestyleFilePath) : Response
    {
        $prestylesPath = Functions::RootPath() . "/global/views/prestyles/";
        $prestylePath = "{$prestylesPath}{$prestyleFilePath}.php";
        $explodedPrestylePath = explode('/', $prestyleFilePath);
        $className = ucfirst(end($explodedPrestylePath)) . "Prestyle";

        if (file_exists($prestylePath)) {
            require_once($prestylePath);
        }

        $classInstance = (class_exists($className)) ? new $className() : null;

        if ($classInstance) {
            $args = $classInstance->getPrestyleArgs();

            foreach ($args as $key => $value) {
                if (is_array($value)) {
                    if (! is_array($this->{$key})) {
                        $this->{$key} = [];
                    }

                    $this->{$key} = array_merge($this->{$key}, $value);
                } else {
                    $this->{$key} = $value;
                }
            }
        }

        return $this;
    }

    private function viewExists() : bool
    {
        $this->viewName = $this->view;
        $appViewPath = APP_ROOT . "/views/pages/{$this->view}/page.php";

        if (file_exists($appViewPath)) {
            $this->view = $appViewPath;

            return true;
        }

        $globalViewPath = ROOT . "/global/views/layouts/{$this->layout}.php";

        if (file_exists($globalViewPath)) {
            $this->view = $globalViewPath;
            
            return true;
        }

        return false;
    }

    private function recursiveSearch($directory, $extension = null) {
        $assets = [];

        $resources = scandir($directory);

        foreach ($resources as $resource) {
            if (in_array($resource, [".", ".."])) {
                continue;
            }

            $resourcePath = "{$directory}/{$resource}";
            $resourcePathExplodedByPoint = explode(".", $resourcePath);
            $resourceExtension = end($resourcePathExplodedByPoint);

            if (is_file($resourcePath)) {
                if (is_null($extension) || $extension == $resourceExtension) {
                    $assets[] = $resourcePath;
                }
            }

            if (is_dir($resourcePath)) {
                $assets = array_merge($assets, $this->recursiveSearch($resourcePath));
            }
        }

        return $assets;
    }

    public function staticCss($css)
    {
        $css = (is_array($css)) ? $css : [$css];

        $this->staticCss = array_merge($this->staticCss, $css);

        return $this;
    }

    public function staticJs($js)
    {
        $js = (is_array($js)) ? $js: [$js];

        $this->staticJs = array_merge($this->staticJs, $js);

        return $this;
    }

    public function getDynamicAssets(string $view) : array
    {
        $dynamicAssets = ['css' => [], 'js' => [], 'vite_css' => [], 'vite_js' => []];

        $assetsDirectory = APP_ROOT . "/views/pages/{$view}/assets";

        if (! file_exists($assetsDirectory)) {
            return $dynamicAssets;
        }

        // Loading dynamic css and js assets
        $dynamicCssDirectory = APP_ROOT . "/views/pages/{$view}/assets/css";
        $dynamicJsDirectory = APP_ROOT . "/views/pages/{$view}/assets/js";

        $explodedAppPath = explode("/", APP_ROOT);
        $appName = end($explodedAppPath);
        $dynamicViteJsDirectory = ROOT . "/dist/assets/{$appName}/views/{$view}/assets/js";

        if (file_exists($dynamicCssDirectory)) {
            $dynamicAssets['css'] = $this->recursiveSearch($dynamicCssDirectory, "css");
        }

        if (file_exists($dynamicJsDirectory)) {
            $dynamicAssets['js'] = $this->recursiveSearch($dynamicJsDirectory, "js");
        }

        if (file_exists($dynamicViteJsDirectory)) {
            $dynamicAssets['vite_js'] = $this->recursiveSearch($dynamicViteJsDirectory, "js");
        }

        if (count($dynamicAssets['vite_js']) > 0) {
            $dynamicAssets['js'] = $dynamicAssets['vite_js'];
        }

        return $dynamicAssets;
    }

    public function render($view)
    {
        $this->view = $view;
        $this->title = ($this->title) ?: "Standard title";
        $this->layout = ($this->layout) ?: "Standard layout";
        $this->favicon = ($this->favicon) ?: "assets/images/favicon.ico";
        $this->description = ($this->description) ?: "Standard description";

        // Loading dynamic assets
        $dynamicAssets = $this->getDynamicAssets($this->view);
        $this->dynamicCss = $dynamicAssets['css'];
        $this->dynamicJs = $dynamicAssets['js'];

        if (! $this->layoutExists()) {
            echo '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>PocketPHP :: No Layout Error</title><link rel="stylesheet" href="assets/css/style.css"></head><body><h1>PocketPHP :: No Layout Error</h1><h2>Warning!</h2><p>You didn\'t configured layout PHP file for your views.<br>Please, create file with name \'' . $this->layout . '\' in \'views/layouts/\' directory.</p><br><br><br><p>Developed by John Theco<br>URL: <i>john.theco.dev@gmail.com</i><br>Version: <i>0.0.1</i></p></body></html>';
        }
        
        if (! $this->viewExists()) {
            echo '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>PocketPHP :: No View Error</title><link rel="stylesheet" href="assets/css/style.css"></head><body><h1>PocketPHP :: No View Error</h1><h2>Warning!</h2><p>You didn\'t configured view PHP file for this route.<br>Please, create file with name \'' . $this->view . '\' in \'views\' directory.</p><br><br><br><p>Developed by John Theco<br>URL: <i>john.theco.dev@gmail.com</i><br>Version: <i>0.0.1</i></p></body></html>';
        }

        foreach ($this->args as $key => $value) {
            if (is_array($value)) {
                if (! is_array($this->{$key})) {
                    $this->{$key} = [];
                }

                $this->{$key} = array_merge($this->{$key}, $value);
            } else {
                $this->{$key} = $value;
            }
        }

        require_once($this->layout);
    }
}
