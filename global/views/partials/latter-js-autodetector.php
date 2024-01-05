<?php

if (! is_array($this->latter_js)) {
    $this->latter_js = [$this->latter_js];
}

foreach ($this->latter_js as $js) {
    if (file_exists(ROOT . '/' . ASSETS . '/' . $js)) {
        echo "\r\n\t<script type='text/javascript' src='{$js}'></script>";
    }
}
