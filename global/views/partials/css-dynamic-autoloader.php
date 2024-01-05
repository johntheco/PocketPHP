<?php

if (! is_array($this->dynamicCss)) {
    $this->dynamicCss = [$this->dynamicCss];
}

foreach ($this->dynamicCss as $css) {
    echo "\r\n<style rel='stylesheet' type='text/css'>\r\n" . file_get_contents($css) . "\r\n</style>\r\n";
}
