<?php

if (! is_array($this->json)) {
    $this->json = [$this->json];
}

foreach ($this->json as $name => $data) {
    $json = json_encode($data);
    echo "<script>let {$name} = JSON.parse('{$json}');</script>";
}