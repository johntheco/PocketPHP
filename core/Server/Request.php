<?php

/**
 * This file contains Request class for initializing
 * objects that contain information about HTTP request.
 * 
 * 
 * @package PocketPHP
 * @version 0.1.0
 * @author  John Theco <john.theco.dev@gmail.com>
 * @license MIT
 * 
 * @link    https://github.com/johntheco/pocketphp
 * @see     https://github.com/johntheco/pocketphp#readme
 */


class Request
{
    private $middlewareResults = [];
    public $args = [];


    function __construct()
    {
        $this->prepareRequestData();
    }

    public function setMiddlewareResult($middleware, $result)
    {
        $this->middlewareResults[$middleware] = $result;
    }

    public function getMiddlewareResult($middleware)
    {
        return $this->middlewareResults[$middleware];
    }

    public function isSet($key) {
        return (array_key_exists($key, $this->args)) ? true : false;
    }

    private function prepareRequestData()
    {
        foreach ($_SERVER as $key => $value) {
            if ($key === "REQUEST_URI") {
                $explodedUri = explode('?', $value);
                $reversedExplodedUri = array_reverse($explodedUri);
                $requestUri = array_pop($reversedExplodedUri);
                $this->{Functions::ToCamelCase($key)} = $requestUri;
                $this->args = $this->getArgs($value);
            } else if ($key === "REQUEST_METHOD") {
                $this->{Functions::ToCamelCase($key)} = strtolower($value);
            } else {
                $this->{Functions::ToCamelCase($key)} = $value;
            }
        }
    }

    private function getArgs($url)
    {
        $args = [];

        $explodedUri = explode('?', $url);
        $uriArguments = end($explodedUri);
        $arguments = explode('&', $uriArguments);

        foreach ($arguments as $value) {
            if ($value == $this->requestUri) {
                continue;
            }
            list($key, $value) = explode('=', $value);
            $args[$key] = $value;
        };

        foreach ($_POST as $key => $value) {
            $args[$key] = $value;
        }

        return $args;
    }

    public function getBody()
    {
        if ($this->requestMethod === "GET") {
            var_dump($_SERVER['REQUEST_URI']);
            return;
        }


        if (in_array($this->requestMethod, ["POST", "PUT", "PATCH", "DELETE"])) {
            $body = [];

            foreach ($_POST as $key => $value) {
                $body[$key] = filter_input(INPUT_POST, $value, FILTER_SANITIZE);
            }
        }

        return $body;
    }
}
