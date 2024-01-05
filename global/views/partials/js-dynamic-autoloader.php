<?php


if (! is_array($this->dynamicJs)) {
    $this->dynamicJs = [$this->dynamicJs];
}

foreach ($this->dynamicJs as $js) {
    echo (strpos($js, "js/modules") !== false)
        ? "\r\n<script type='module'>\r\n" . file_get_contents($js) . "\r\n</script>\r\n"
        : "\r\n<script>\r\n" . file_get_contents($js) . "\r\n</script>\r\n";
}
