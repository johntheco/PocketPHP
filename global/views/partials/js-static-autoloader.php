<?php

if (! is_array($this->staticJs)) {
    $this->staticJs = [$this->staticJs];
}

foreach ($this->staticJs as $js) {
    if (file_exists(ROOT . '/' . ASSETS . '/' . $js)) {
        echo "\r\n\t<script type='text/javascript' src='/{$js}'></script>";
    }
}

