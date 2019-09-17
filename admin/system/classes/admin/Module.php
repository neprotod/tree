<?php
/*
 * empty class
 */
class Admin_Module{
    // Содержит имена модулей
    protected static $_modules = array();
    
    // Содержит массив модуль - путь
    protected static $_modules_path = array();
    
    // Пути для дополнительных включений
    public static $app = array();
    
    public static $path = array(MODPATH_ADMIN);
    /*
     * Создает пути к модулю
     *
     * @param array модули которым нужно создать пути
     * @return  void
     */
    static function module_path($modules = NULL){
        // Пути по умолчанию для модулей
        $default_array = array();
        
        
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
        if ($modules === NULL){
            // Вернуть подключенные модули
            return self::$_modules;
        }
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
    static function load($module = NULL, $index = 'index', array $settings = null){
        // Проверяет, выполнить все модули или нет
        if($modules == null)
            $modules = self::$_modules;
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
        return $module . "_Admin";
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
     * @return string путь модуля, FALSE в случае неудачи
     */
    static function mod_path($module){
        $module = strtolower($module);
        if(isset(self::$_modules_path[$module]))
            return self::$_modules_path[$module];
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
}