<?php

// Сразу исключаем первые два элемента массива "." и ".."
$backgroundList = array_diff(scandir(ROOT . '/public/assets/background'));

// Выбираем случайный бэкграунд
$randomBackground = rawurlencode($backgroundList[rand(2, count($backgroundList) + 1)]);

// Выдаем его напрямую в CSS
echo "assets/backgrounds/{$randomBackground}";
