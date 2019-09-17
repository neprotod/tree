<?php
///////////////////////////////////////////
///// АДМИНИСТРАТИВНАЯ ПАНЕЛЬ
//////////////////////////////////////////
/*
* Tree CMS
* Version: 0.0.1 alfa.
* Author: WebDzen
*/
// Определяем корневую директиву
define('DOCROOT', realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR);

// Выходим на каталог ниже
chdir('..');
/*
 * Пути
 */
$designAdmin = 'design';

$modulesAdmin = 'modules';

$systemAdmin = 'system';

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
 * Путь к корневому каталогу ядра
 */
define('COREROOT', realpath(getcwd()).DIRECTORY_SEPARATOR);

/*
 * Проверить путь отностельно корневого каталога
 */
 
if (is_dir(DOCROOT.$designAdmin))
    $designAdmin = DOCROOT.$designAdmin;
    
if (!is_dir($application) AND is_dir(COREROOT.$application))
    $application = COREROOT.$application;
    

if (is_dir(DOCROOT.$modulesAdmin))
    $modulesAdmin = DOCROOT.$modulesAdmin;
    
if (!is_dir($modules) AND is_dir(COREROOT.$modules))
    $modules = COREROOT.$modules;

    
if (is_dir(DOCROOT.$systemAdmin))
    $systemAdmin = DOCROOT.$systemAdmin;
    
if (!is_dir($system) AND is_dir(COREROOT.$system))
    $system = COREROOT.$system;

/*
 * Создаем абсолютные пути
 */
define('DESIGN_ADMIN', realpath($designAdmin).DIRECTORY_SEPARATOR);
define('MODPATH_ADMIN', realpath($modulesAdmin).DIRECTORY_SEPARATOR);
define('SYSPATH_ADMIN', realpath($systemAdmin).DIRECTORY_SEPARATOR);

define('APPPATH', realpath($application).DIRECTORY_SEPARATOR);
define('MODPATH', realpath($modules).DIRECTORY_SEPARATOR);
define('SYSPATH', realpath($system).DIRECTORY_SEPARATOR);

// Удаляем лишнее
unset($designAdmin, $modulesAdmin, $systemAdmin, $application, $modules, $system);

/*
 * Время начала выполнения
 */
if (!defined('START_TIME'))
    define('START_TIME', microtime(TRUE));

/*
 * Затрачиваемая память
 */
if (!defined('START_MEMORY'))
    define('START_MEMORY', memory_get_usage());
    
// Устанавливаем временную зону
date_default_timezone_set('Europe/Kiev');

// Установка локали
setlocale(LC_ALL, 'ru_RU.utf-8');

// Жизнь сесиии
@ini_set('session.gc_maxlifetime', 86400); // 86400 = 24 часа
@ini_set('session.cookie_lifetime', 0); // 0 - пока браузер не закрыт
// Отключаем кеширование
header("Cache-control: no-store,max-age=0");
// Тип кодировки
header("Content-type: text/html; charset=utf-8");

// Загружаем ядро
require SYSPATH.'classes/core/Core'.EXT;

// Загружаем ядро администратора
require SYSPATH_ADMIN.'classes/admin/Admin'.EXT;


/*Работа с auto-load*/
spl_autoload_register(array('Admin', 'auto_load'));

$core = new Admin;

$content = $core->fetch();

echo Admin_Template::factory(Registry::i()->settings['admin_theme'],'index',array('content' => $content));

