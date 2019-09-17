<?php defined('SYSPATH') OR exit();
/*
 * @package   Tree
 * @category  Base
 */
class Core{
    // Версия и индентифмкатор
    const VERSION = '0.0.1 alfa';
    const TREE_ID = 'admin';
    
    const TREE_HOST = 'tree';
    
    // Режимы
    const PRODUCTION  = 1;
    const STAGING     = 2;
    const TESTING     = 3;
    const DEVELOPMENT = 4;
    
    
    /*
    * @var string Используемый режим
    */
    public static $selected_mode = Core::DEVELOPMENT;

    /*
    * @var string Логи ошибок
    */
    public static $log_errors = FALSE;
    
        /**
     * @var  string Тип контента
     */
    public static $content_type = 'text/html';

    /**
     * @var  string  Кодировка
     */
    public static $charset = 'utf-8';
    
        /**
     * @var  string  Имя сервера
     */
    public static $server_name = '';

    /**
     * @var  array   Лист хостов
     */
    public static $hostnames = array();
    
    /*
    * @var bool кешировать ли
    */
    public static $caching = FALSE;
    
    /*
    * @var string деректория кеширования
    */
    public static $cache_dir;
    
    /*
    * @var string время жизни кеша
    */
    public static $cache_life = 60;
    
    /**
     * @var  string  Базовый URL
     */
    public static $base_url = '/';
    
    /**
     * @var  string  Индексовый файл
     */
    public static $index_file = 'index.php';

    /**
     * @var  bool  Проверка на windows
     */
    public static $is_windows;
    
    /**
     * @var  bool  Включить ли отработку ошибков
     */
    public static $errors = TRUE;
    
    /**
     * @var  bool  Хость и протокол
     */
    public static $root_url = TRUE;
    
    /**
     * @var  bool Протокол
     */
    public static $protocol = TRUE;
    
    /**
     * @var  bool  Объект конфигурации
     */
    public static $config = TRUE;
    
    /**
     * @var  array  Проверка хоста
     */
    public static $check_host = FALSE;
    /**
     * @var  array  Типы ошибок для отображения при выключении
     */
    public static $shutdown_errors = array(E_PARSE, E_ERROR, E_USER_ERROR);
    /**
     * @var  boolean  Проверяет, была ли вызвана основная функция
     */
    protected static $_init = FALSE;
    
    /**
     * @var  array  основные пути
     */
    protected static $_paths = array(APPPATH, SYSPATH);
    
    /**
     * @var  array  Список модулйе
     */
    protected static $_modules = array();
    
    static $sample = TRUE;
    /*********methods***********/
    
    // Отключаем возможность вызывать конструктор.
    protected function __construct(){}
    
    /*
    * @param   array  Для сохранения глобальных настроек.
    * @return  void
    */
    static function init(array $settings = NULL){
        if (Core::$_init){
            // Запрет повторного запуска
            return;
        }
        if(Core::$sample === TRUE){
            $sample = new Core;
            Core::$sample = get_class($sample);
        }
        // Инициализирует запуск
        Core::$_init = TRUE;
        
        // Запускаем буфиринизацию
        //ob_start();
        
        // Отлавливаем E_FATAL.
        //register_shutdown_function(array('Core', 'shutdown_handler'));
        // Является ли windows
        Core::$is_windows = (DIRECTORY_SEPARATOR === '\\');
        
        // Задаем директорию кеша
        if (isset($settings['cache_dir'])){
            if (!is_dir($settings['cache_dir'])){
                try{
                    // Создаем кеш директорию
                    mkdir($settings['cache_dir'], 0755, TRUE);

                    // Задать разрешение
                    chmod($settings['cache_dir'], 0755);
                }catch (Exception $e){
                    echo 'Данную директорию нельзя создать';
                }
            }
            
            // Задать путь к каталогу кэша
            Core::$cache_dir = realpath($settings['cache_dir']);
        }else{
            // Стандартный путь кеширования
            Core::$cache_dir = APPPATH.'cache';
        }
        
        //Настройки ошибок
        if (isset($settings['errors'])){
            // Включить обработку ошибок
            Core::$errors = (bool) $settings['errors'];
        }
        
        if (Core::$errors === TRUE){
            // Включить обработку исключений
            switch(Core::$selected_mode){
                case Core::DEVELOPMENT:
                    set_exception_handler(array('Core_Exception', 'handler'));
                    break;
                default:
                    set_exception_handler(array('Core_Exception_Production', 'handler'));
                    break;
            }
            
            // Включить обработку ошибок
            set_error_handler(array('Core', 'error_handler'));
        }
        if(Core::$check_host === FALSE){
            $host = str_replace('www.','',$_SERVER['HTTP_HOST']);
            if($host == Core::TREE_HOST)
                Core::$check_host = TRUE;
        }
        
        if (Core::$root_url === TRUE){
            // Протокол
            $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https'? 'https' : 'http';
            if($_SERVER["SERVER_PORT"] == 443)
                $protocol = 'https';
            Core::$protocol = $protocol;        
            Core::$root_url = $protocol.'://'.rtrim($_SERVER['HTTP_HOST']);
        }
        // Регистрация функции отключения
        //register_shutdown_function(array('Core', 'shutdown_handler'));
        
        // Создаем экземпляр класса конфигураций
        Core::$config = Config::instance();
    }
    /*
     * Загрузка классов
     * @param   string   имя класса
     * @return  bool
     */
    static function auto_load($class){
        try{
            // Transform the class name into a path
            $file = str_replace('_', DIRECTORY_SEPARATOR, strtolower($class));

            if($path = Core::find_file('classes', $file)){
                // Подключаем класс файл
                require $path;

                // Класс найден
                return TRUE;
            }

            // Не найден данный класс
            return FALSE;
        }catch (Exception $e){
            echo 'Не удалось подключить<pre>';
            print_r($e->getTrace());
        }
    }

    /*
     * Возвращает абсолютный путь к файлу
     *         Примеры: 
     *     // Вовзращает views/template.php
     *     Core::find_file('views', 'template');
     *
     *     // Возвратит media/css/style.css
     *     Core::find_file('media', 'css/style', 'css');
     *
     * @param   string   Имя директории
     * @param   string   Имя файла с подкаталогом
     * @param   string   Расширение
     * @param   bool  Вернуть массив файлов?
     * @return  array    Список файлов, когда $array является TRUE
     * @return  string   Один путь к файлу
     */
    static function find_file($dir, $file, $ext = NULL, $array = FALSE){
        if ($ext === NULL){
            // Используем расширение по умолчанию
            $ext = EXT;
        }
        elseif ($ext){
            // Используем заданное расширение
            $ext = ".{$ext}";
        }else{
            // без расширения
            $ext = '';
        }
        
        
        
        $dir = str_replace('_', DIRECTORY_SEPARATOR, strtolower($dir));
        
        // Создание частичного пути имени файла
        $path = $dir.DIRECTORY_SEPARATOR.$file.$ext;
        
        // создаем два имени фала с большой буквой и маленькой
        $explode = explode(DIRECTORY_SEPARATOR, $path);
        $exClass = array_pop($explode);
        $exDir = implode(DIRECTORY_SEPARATOR, $explode);
        $pathUpper = $exDir.DIRECTORY_SEPARATOR.ucfirst($exClass);

        ////////////////////////////////
        //// Ищем файл
        ///////////////////////////////
        if ($array){
            // Включенные пути надо искать в обратном порядке
            $paths = array_reverse(Core::$_paths);

            // Массив файлов, которые были найдены
            $found = array();

            foreach ($paths as $direct){
                if(is_file($direct.$pathUpper)){
                    // Этот путь имеет файл, добавить его в список
                    $found[] = $direct.$pathUpper;
                }
                elseif(is_file($direct.$path)){
                    // Этот путь имеет файл, добавить его в список
                    $found[] = $direct.$path;
                }
            }
        }else{
            // Файл ненайден
            $found = FALSE;
            //echo $path;
            foreach (Core::$_paths as $direct){
                if (is_file($direct.$pathUpper)){
                    // Путь был найден
                    $found = $direct.$pathUpper;

                    // Остановка поиска
                    break;
                }
                elseif(is_file($direct.$path)){
                    // Этот путь имеет файл, добавить его в список
                    $found = $direct.$path;
                    
                    // Остановка поиска
                    break;
                }
            }
        }
        /*// Если пустой found
        if(empty($found) OR $found === FALSE){
            $contig = array();
            foreach(Core::$_paths as $direct)
                $contig[] = $direct.$dir;
                
            $paths = Direct::directory_out($contig,TRUE);
            foreach($paths as $direct){
                if (is_file($direct.$file.$ext)){
                    // Путь был найден
                    $found = $direct.$file.$ext;

                    // Остановка поиска
                    break;
                }
            }
        }*/
        
        return $found;
    }
    
    /*
     
     * Получаем массив путей
     *
     * @param   string   Имя директории
     * @param   arrey   пути
     * return void
     */
     static function list_files($directory = NULL, array $paths = NULL){
        $root = FALSE;
        if ($directory !== NULL){
            if($directory == '.')
                $root = TRUE;
            // Добавление разделителя каталогов
            $directory .= DIRECTORY_SEPARATOR;
        }

        if ($paths === NULL){
            // Использовать пути по умолчанию
            $paths = Core::$_paths;
        }

        // Создайте массив для файлов
        $found = array();

        foreach ($paths as $path){
            if (is_dir($path.$directory)){
                // Создайте новый каталог итератор
                $dir = new DirectoryIterator($path.$directory);

                foreach ($dir as $file){
                    // Получить имя файла
                    $filename = $file->getFilename();
                    /*if(Core::$is_windows)
                        $filename = iconv('cp1251','UTF-8',$filename);*/
                        
                    if ($filename[0] === '.' OR $filename[strlen($filename)-1] === '~'){
                        // Пропустить все скрытые файлы и резервные UNIX файлы
                        continue;
                    }

                    // Относительное имя файла
                    $key = $directory.$filename;
                    if ($file->isDir()){
                        if ($sub_dir = Core::list_files($directory.$filename, $paths)){
                            if (isset($found[$key])){
                                // Добавляет список подкаталог
                                $found[$key] += $sub_dir;
                            }else{
                                // Создайте новый список подкаталог
                                $found[$key] = $sub_dir;
                            }
                        }
                    }
                    else{
                        if ( ! isset($found[$key])){
                            // Добавить новые файлы в список
                            /*if(Core::$is_windows)
                                $found[$key] = iconv('cp1251','UTF-8',realpath($file->getPathName()));
                            else
                                $found[$key] = realpath($file->getPathName());*/
                                $found[$key] = realpath($file->getPathName());
                                
                        }
                    }
                }
            }
        }

        // Отсортировать результаты по алфавиту
        ksort($found);
        return $found;
    }
     
    /**
     * Возвращает массив конфигурации для запрошенной группы.  Посмотреть
     * [configuration files](core/files/config) для более конкретной информации.
     *
     *     // Получите всю конфигурацию config/database.php
     *     $config = Core::config('database');
     *
     *     // Получите только типовую connection конфигурацию
     *     $default = Kohana::config('database.default')
     *
     *     // Получить только имя узла соединения по умолчанию
     *     $host = Kohana::config('database.default.connection.hostname')
     *
     * @param   string   имя группы конфигураций
     * @return  Config
     */
    static function config($group){
        static $config;
        // Если нужна подпапка
        $group = str_replace('_', DIRECTORY_SEPARATOR, strtolower($group));
        
        if (strpos($group, '.') !== FALSE){
            // Разделить группу конфигурации и пути
            list ($group, $path) = explode('.', $group, 2);
        }

        if (!isset($config[$group])){
            // Загрузка конфигурации группы в кэш
            $config[$group] = Core::$config->load($group);
        }

        if (isset($path)){
            return Arr::path($config[$group], $path, NULL, '.');
        }else{
            return $config[$group];
        }
    }
    
    /*
     * Загружает файл
     *
     * @param   string
     * @return  mixed
     */
    public static function load($file){
        return require $file;
    }
    /*
     * Разделяем директория/файл
     *
     * @param   string
     * @return  string
     */
    
    /* 
     * Функция оработики ошибок.
     * return bool
     */
    
    static function error_handler($code, $error, $file = NULL, $line = NULL){
        if (error_reporting() & $code){
            // Эта ошибка не подавляется текущих настроек отчетности ошибки
            // Преобразовать ошибки в ErrorException
            throw new ErrorException($error, $code, 0, $file, $line);
        }
        // Не выполнять обработчик ошибок PHP
        return TRUE;
    }
    
    
    /* 
     * Функция завершение работы скрипта.
     * return void
     */
    static function shutdown_handler(){
        if (!Core::$_init){
            // Небыл активирован
            return;
        }
            
        if (Core::$errors AND $error = error_get_last() AND in_array($error['type'], Core::$shutdown_errors)){
            // Clean the output buffer
            ob_get_level() and ob_clean();

            // Fake an exception for nice debugging
            Core_Exception::handler(new ErrorException($error['message'], $error['type'], 0, $error['file'], $error['line']));

            // Shutdown now to avoid a "death loop"
            exit(1);
        }
    }
    
}
