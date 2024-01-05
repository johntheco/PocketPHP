<?php

if (! is_array($this->staticCss)) {
    $this->staticCss = [$this->staticCss];
}

foreach ($this->staticCss as $css) {
    if (file_exists(ROOT . '/' . ASSETS . '/' . $css)) {
        echo "\r\n\t<link type='text/css' rel='stylesheet' href='/{$css}'>";
    }
}
