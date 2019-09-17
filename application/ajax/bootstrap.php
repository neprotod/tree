<?php

chdir('../..');
$application = 'application';

$modules = 'modules';

$system = 'system';


/*
 * Расширение файлов
 */

define('EXT', '.php');

/*
 * Отображение ошибок
 */

//error_reporting(E_ALL | E_STRICT);

/*
 * Путь к корневому каталогу
 */
define('DOCROOT', realpath(getcwd()).DIRECTORY_SEPARATOR);

/*
 * Проверить путь отностельно корневого каталога
 */
 
if (!is_dir($application) AND is_dir(DOCROOT.$application))
    $application = DOCROOT.$application;

if (!is_dir($modules) AND is_dir(DOCROOT.$modules))
    $modules = DOCROOT.$modules;

if (!is_dir($system) AND is_dir(DOCROOT.$system))
    $system = DOCROOT.$system;

/*
 * Создаем абсолютные пути
 */
define('APPPATH', realpath($application).DIRECTORY_SEPARATOR);
define('MODPATH', realpath($modules).DIRECTORY_SEPARATOR);
define('SYSPATH', realpath($system).DIRECTORY_SEPARATOR);

// Удаляем лишнее
unset($application, $modules, $system);

require SYSPATH.'classes/core/Core'.EXT;

// Устанавливаем временную зону
date_default_timezone_set('Europe/Kiev');

// Установка локали
setlocale(LC_ALL, 'ru_RU.utf-8');

/*Работа с auto-load*/
spl_autoload_register(array('Core', 'auto_load'));

// Включаем афтолоад для unserialization
//ini_set('unserialize_callback_func', 'spl_autoload_call');

/*
 * Переключалка языка
 */
 
 /******************/
 
/*
 * Описание методов и функций для инициализации
 */
Core::init();
/*
 * Запускаем пути подключения путей
 */
Core::$config->attach(new Config_File);

/*
 * Стандартные модули
 */
/**/
/*
Module::module_path(array(
    'database', 
    'arrays',
    'codebench',
    'fileManager',
));*/
Module::module_path(TRUE);

session_start();
// Проверка сессии для защиты от xss
/*if(!Request::check_session()){
    unset($_POST);
    exit('Session expired');
}*/
