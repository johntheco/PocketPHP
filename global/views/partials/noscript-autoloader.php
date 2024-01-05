<?php

if (! is_array($this->noscript)) {
    $this->noscript = [$this->noscript];
}

foreach ($this->noscript as $css) {
    if (file_exists(ROOT . '/' . ASSETS . '/' . $css)) {
        echo "\r\n\t<noscript><link rel='stylesheet' type='text/css' href='{$css}'></noscript>";
    }
}
