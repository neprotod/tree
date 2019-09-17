<?php defined('SYSPATH') OR exit();
/*
 * empty class
 */
class Core_Module extends Core{
    // Содержит имена модулей
    protected static $_modules = array();
    
    // Содержит массив модуль - путь
    protected static $_modules_path = array();
    
    // Пути для дополнительных включений
    public static $app = array(APPPATH);
    
    public static $path = array(MODPATH);
    /*
     * Создает пути к модулю
     *
     * @param array модули которым нужно создать пути
     * @return  void
     */
    static function module_path($modules = NULL){
        // Пути по умолчанию для модулей
        $default_array = array();
        
        if ($modules === NULL){
            // Вернуть подключенные модули
            return self::$_modules;
        }
        
        // Добавляем к пути приложения папку модуль
        if(!empty(self::$app))
            foreach(self::$app as $app)
                $default_array[] = $app.'modules'.DIRECTORY_SEPARATOR;
        
        // Пути по умолчанию для модулей
        if(!empty(self::$path))
            foreach(self::$path as $path)
                $default_array[] = $path;

        // Сливаем два массива.
        if(!empty(self::$_modules)){
            $difference = Arr::merge(self::$_modules,$modules);
            //sort($difference);
            $modules = $difference;
        }
        ///////////////
        // Создаем директории включения
        ///////////////
        
        //Если TRUE заполнить с имен папок
        elseif($modules === TRUE){
            $modules = self::generator_module_path($default_array);
        }
        elseif(!is_array($modules)){
            throw new Core_Exeption('Пришел не массив');
        }
        ///////////////
        // Заполняем пути
        ///////////////
        foreach ($default_array as $key => $path){
            foreach($modules as $key => $module){
                if(is_dir($path.$module)){

                    // временный путь
                    $temp_path = realpath($path.$module).DIRECTORY_SEPARATOR;

                    self::$_modules[] = $modules[$key];    
                    
                    // сохраняеем модуль - путь для подвключения моделей, контроллеров и view
                    self::$_modules_path[strtolower($modules[$key])] = $temp_path;
                    
                    unset($modules[$key]);
                }
            }
        }
        
        // Удаляем все не найденые модули
        unset($modules);
        
    }
    
    /*
     * Подключает найденые модули
     *
     * @param array модули которые нужно выполнить
     * @param array настройки модуля
     * @param string метод который нужно запустить
     * @return string  выводод всех модулей
     */
    static function load($module = NULL, array $settings = null, $index = 'index'){
        // Если пришел массив, выполнить все модули в массиве
        self::factory($module);
        // Создаем правельное имя модуля
        $module = self::name($module);

        if(method_exists($module, $index)){
            $return = self::execution($module,$index,$settings);
        }else{
            throw new Core_Exception('Нет метода <b>'.$index.'</b> в модуле <b>'.$module.'</b>');
        }
    
        return $return;
    }
    
    /*
     * Проверка на параметры
     *
     * @param object reflectionMethod
     * @return void
     */
    private static function check($method){
        // Берем параметры метода
        $params = $method->getParameters();
        
        // Если нет параметров выводим исключение
        if(empty($params))
            throw new Core_Exception('Нет аргумента в классе <b>'.$method->class.'</b> методе <b>'.$method->name.'</b>');
    }
    
    /*
     * Создаем имя модуля
     *
     * @param object reflectionMethod
     * @return void
     */
    static function name($module){
        return $module . "_Module";
    }
    
    /*
     * Выполнение модуля
     *
     * @param имя модуля
     * @param имя метода который нужно запустить
     * @param настройки модуля
     * @return string вывод модуля
     */
    private static function execution($module,$index,$settings){
        $method = new ReflectionMethod($module, $index);
        //self::check($method);
        // Включаем буферинизацию
        //ob_start();
            return $method->invoke(new $module, $settings);
        // выводим буфер
        //return ob_get_clean();
    }
    
    /*
     * Выдает модудль - путь
     *
     * @param string имя модуля
     * @param bool если нужно заменить слеши и превратить в относительный путь TRUE
     * @return string путь модуля, FALSE в случае неудачи
     */
    static function mod_path($module,$bool = FALSE){
        $module = strtolower($module);
        if(isset(self::$_modules_path[$module])){
            if($bool === FALSE){
                return self::$_modules_path[$module];
            }else{
                $return = str_replace(DOCROOT, '/', self::$_modules_path[$module]);
                return str_replace('\\', '/', $return);
            }
        }
        // если не найдено
        throw new Core_Exception('Модуль '.$module.' не найден');
    }
    
    /*
     * Генератор путей модуля
     *
     * @return array пути всех модулей
     */
     static function generator_module_path($default_array){
        
        if(!is_array($default_array))
            throw new Core_Exception('Пришел не массив');
        
        foreach($default_array as $path){
            $scans = scandir($path);
            foreach($scans as $scan){
                if(($scan != '.' AND $scan != '..') AND is_dir($path.$scan))    
                    $result[] = $scan; 
            }
        }
        $result = array_unique($result);
        return $result;
     }
     
    /*
     * Выдает моудль - путь
     *
     * @param string имя модуля
     * @return string путь модуля, FALSE в случае неудачи
     */
    static function factory($module, $bool = FALSE){
        // Абсолютный путь к файлу
        $path = self::mod_path($module).'classes'.DIRECTORY_SEPARATOR.$module.EXT;
        
        // Создаем имя
        $class = self::name($module);
        
        // Проверка счуществут ли данный Модуль
        if(is_file($path)){
            if(!class_exists($class)){
                require $path;
            }
        }else{
            throw new Core_Exception('Модуль  '.$module.' не найден');
        }
        if($bool === TRUE)
            return new $class;
    }
    
    /*
     * Получить путь к файлу из папки модуля
     *
     * @param string имя файла
     * @param string директория внутри модуля
     * @param string имя модуля
     * @param string расширение
     * @return string буфиринизированый файл
     */
    static function file_path($file, $dir = NULL, $module, $ext = NULL){
        // Абсолютный путь к файлу
        $path = self::mod_path($module);
        
        if(!empty($dir)){
            $dir = str_replace('_', DIRECTORY_SEPARATOR, strtolower($dir)).DIRECTORY_SEPARATOR;
        }
        if($ext === NULL){
            $ext = EXT;
        }else{
            $ext = '.'.trim($ext, '.');
        }
        $file = str_replace('_', DIRECTORY_SEPARATOR, strtolower($file));
        $path .= $dir.$file.$ext;
        if(is_file($path)){
            return $path;
        }
        return FALSE;

    }
    /*
     * Получить путь к файлу из папки модуля
     *
     * @param string имя файла
     * @param string директория внутри модуля
     * @param string имя модуля
     * @param string расширение
     * @return string буфиринизированый файл
     */
    static function file_load($file, $setting = array()){
        if(!empty($setting)){
            if(($module = $setting['module'])){
                $dir = (isset($setting['dir']))? $setting['dir'] : NULL;
                $ext = (isset($setting['ext']))? $setting['ext'] : NULL;
                $file = Self::file_path($file,$dir,$module,$ext);
            }else{
                return FALSE;
            }
        }
        if(is_file($file)){
            ob_start();
                require $file;
            return ob_get_clean();
        }else{
            return FALSE;
        }
    }
}