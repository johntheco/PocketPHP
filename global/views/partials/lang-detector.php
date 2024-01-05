<?php

/**
 * Original: https://stackoverflow.com/questions/3770513/detect-browser-language-in-php
 */

$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
$acceptLang = ['ru', 'eng'];
$lang = in_array($lang, $acceptLang) ? $lang : 'en';
echo $lang;
