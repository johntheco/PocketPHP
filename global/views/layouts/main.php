<!DOCTYPE html>

<!-- Автоматически детектируем язык пользователя (русский/английский) -->
<html lang="<?php require_once(ROOT . '/global/views/partials/lang-detector.php'); ?>">

<!-- Head -->
<head>

    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Additional meta tags -->
    <meta name="author" content="John Theco">

    <!-- Description -->
    <meta name="description" content="<?=$this->description?>">

    <!-- Title -->
    <title><?=$this->title?></title>

    <!-- Generator -->
    <?php if ($this->generator) : ?>
        <meta name="generator" content="<?=$this->generator?>">
    <?php endif; ?>

    <!-- Canonical -->
    <?php if ($this->canonical) : ?>
        <link rel="canonical" href="<?=$this->canonical?>">
    <?php endif; ?>

    <!-- Icon -->
    <link rel="shortcut icon" type="image/x-icon" href="<?=$this->favicon?>">

    <!-- JSON autoloader -->
    <?php ($this->json) ? require_once(ROOT . '/global/views/partials/json-autoloader.php') : "" ?>

    <!-- Static CSS & JS autoloader -->
    <?php ($this->staticCss) ? require_once(ROOT . '/global/views/partials/css-static-autoloader.php') : "" ?>
    <?php ($this->staticJs) ? require_once(ROOT . '/global/views/partials/js-static-autoloader.php') : "" ?>

    <!-- Dynamic CSS & JS autoloader -->
    <?php ($this->dynamicCss) ? require_once(ROOT . '/global/views/partials/css-dynamic-autoloader.php') : ""; ?>
    <?php ($this->dynamicJs) ? require_once(ROOT . '/global/views/partials/js-dynamic-autoloader.php') : ""; ?>

</head>

<!-- Body -->

<?php

echo (! is_null($this->body)) ? "<body {$this->body}>" : "<body>";

if (! $this->void) {
    require_once($this->view);
} else {
    echo $this->view;
}

?>

<script>

let userLang = navigator.language || navigator.userLanguage;
let userLang_detected = userLang.toLowerCase();

</script>

</body>
</html>
