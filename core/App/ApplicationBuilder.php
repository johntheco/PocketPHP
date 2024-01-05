<?php


class ApplicationBuilder
{
    public function __construct()
    {
        $this->applications = [];

        Functions::Walk(Functions::AppPath(), function($path) {
            $explodedPath = explode('/', $path);

            if (end($explodedPath) === "app.php") {
                $applicationPath = substr($path, 0, mb_strlen($path) - 8);
                $explodedApplicationPath = explode('/', $applicationPath);
                $applicationName = end($explodedApplicationPath);

                $this->applications[$applicationName] = new Application(
                    $applicationName,
                    $applicationPath
                );
            }
        });

        Pocket::Applications($this->applications);
    }
}
