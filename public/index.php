<?php
namespace myframe;

//加载composer自动加载文件
require_once '../vendor/autoload.php';
define('VIEW_PATH', '../resources/views/');
App::getInstance()->run()->send();

