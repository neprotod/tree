<?php defined('SYSPATH') OR exit();
/**
 * Array helper. 
 *
 * @package    Tree
 * @category   Helpers
 */

class Core_Arr {
    
    /**
     * @var  string  разделитель по умолчанию для path()
     */
    public static $delimiter = '.';

    /**
     * Проверяет, если массив ассоциативен или нет.
     *
     *     // Returns TRUE
     *     Arr::is_assoc(array('username' => 'john.doe'));
     *
     *     // Returns FALSE
     *     Arr::is_assoc('foo', 'bar');
     *
     * @param   array   массив для проверки
     * @return  boolean
     */
    public static function is_assoc(array $array){
        // Ключи массива
        $keys = array_keys($array);

        // If the array keys of the keys match the keys, then the array must
        // not be associative (e.g. the keys array looked like {0:0, 1:1...}).
        return array_keys($keys) !== $keys;
    }

    /**
     * Test if a value is an array with an additional check for array-like objects.
     *
     *     // Returns TRUE
     *     Arr::is_array(array());
     *     Arr::is_array(new ArrayObject);
     *
     *     // Returns FALSE
     *     Arr::is_array(FALSE);
     *     Arr::is_array('not an array!');
     *     Arr::is_array(Database::instance());
     *
     * @param   mixed    value to check
     * @return  boolean
     */
    public static function is_array($value){
        if (is_array($value)){
            // Определенно массив
            return TRUE;
        }else{
            // Возможно просматриваемые объект, функционально так же, как массив
            return (is_object($value) AND $value instanceof Traversable);
        }
    }

    /**
     * Получает значение из массива array, используя путь, разделенных точкой.
     *
     *     // Получите значение $array['foo']['bar']
     *     $value = Arr::path($array, 'foo.bar');
     *
     * С помощью подстановочного знака "*" будет искать промежуточные массивы и возвращать массив.
     *
     *     // Получить значения "color" в теме
     *     $colors = Arr::path($array, 'theme.*.color');
     *
     *     // С помощью массива ключей
     *     $colors = Arr::path($array, array('theme', '*', 'color'));
     *
     * @param   array   массив для поиска
     * @param   mixed   строка(отделенный разделитель) ключевого пути или массив ключей
     * @param   mixed   значение по умолчанию, если путь не задан
     * @param   string  разделитель ключевые пути
     * @return  mixed
     */
    public static function path($array, $path, $default = NULL, $delimiter = NULL){
        if ( ! Arr::is_array($array)){
            // Это не массив!
            return $default;
        }

        if (is_array($path)){
            // Путь уже разделены на ключи
            $keys = $path;
        }else{
            if (array_key_exists($path, $array)){
                // Нет необходимости сделать особенно обработку
                return $array[$path];
            }

            if ($delimiter === NULL){
                // Используйте разделитель по умолчанию
                $delimiter = Arr::$delimiter;
            }

            // Удалите начальный разделители и пробелы
            $path = ltrim($path, "{$delimiter} ");

            // Удаление конечного разделителей, пространства и подстановочные знаки
            $path = rtrim($path, "{$delimiter} *");

            // Разделить ключи, разделитель
            $keys = explode($delimiter, $path);
        }

        do{
            $key = array_shift($keys);

            if (ctype_digit($key)){
                // Сделайте ключ целым числом
                $key = (int) $key;
            }

            if (isset($array[$key])){
                if ($keys){
                    if (Arr::is_array($array[$key])){
                        // Докопайте в следующей части пути
                        $array = $array[$key];
                    }else{
                        // Копать глубже
                        break;
                    }
                }else{
                    // Found the path requested
                    return $array[$key];
                }
            }
            elseif ($key === '*'){
                // Handle wildcards

                $values = array();
                foreach ($array as $arr){
                    if ($value = Arr::path($arr, implode('.', $keys))){
                        $values[] = $value;
                    }
                }

                if ($values){
                    // Found the values requested
                    return $values;
                }else{
                    // Unable to dig deeper
                    break;
                }
            }else{
                // Unable to dig deeper
                break;
            }
        }
        while ($keys);

        // Unable to find the value requested
        return $default;
    }

    /**
    * Set a value on an array by path.
    *
    * @see Arr::path()
    * @param array   $array     Array to update
    * @param string  $path      Path
    * @param mixed   $value     Value to set
    * @param string  $delimiter Path delimiter
    */
    public static function set_path( & $array, $path, $value, $delimiter = NULL){
        if ( ! $delimiter){
            // Use the default delimiter
            $delimiter = Arr::$delimiter;
        }

        // Split the keys by delimiter
        $keys = explode($delimiter, $path);

        // Set current $array to inner-most array path
        while (count($keys) > 1){
            $key = array_shift($keys);

            if (ctype_digit($key)){
                // Make the key an integer
                $key = (int) $key;
            }

            if ( ! isset($array[$key])){
                $array[$key] = array();
            }

            $array = & $array[$key];
        }

        // Set key on inner-most array
        $array[array_shift($keys)] = $value;
    }

    /**
     * Заполнить массив диапазоне чисел .
     *
     *     // Fill an array with values 5, 10, 15, 20
     *     $values = Arr::range(5, 20);
     *
     * @param   integer  stepping
     * @param   integer  ending number
     * @return  array
     */
    public static function range($step = 10, $max = 100){
        if ($step < 1)
            return array();

        $array = array();
        for ($i = $step; $i <= $max; $i += $step){
            $array[$i] = $i;
        }

        return $array;
    }

    /**
     * Получить один ключ из массива. Если ключ не существует в
     * массиве, будет возвращено значение по умолчанию.
     *
     *     // Get the value "username" from $_POST, if it exists
     *     $username = Arr::get($_POST, 'username');
     *
     *     // Get the value "sorting" from $_GET, if it exists
     *     $sorting = Arr::get($_GET, 'sorting');
     *
     * @param   array   array to extract from
     * @param   string  key name
     * @param   mixed   default value
     * @return  mixed
     */
    public static function get($array, $key, $default = NULL){
        return isset($array[$key]) ? $array[$key] : $default;
    }

    /**
     * Извлекает несколько ключей из массива. Если ключ не существует в
     * массиве, будет возвращено значение по умолчанию.
     *
     *     // Get the values "username", "password" from $_POST
     *     $auth = Arr::extract($_POST, array('username', 'password'));
     *
     * @param   array   array to extract keys from
     * @param   array   list of key names
     * @param   mixed   default value
     * @return  array
     */
    public static function extract($array, array $keys, $default = NULL){
        $found = array();
        foreach ($keys as $key){
            $found[$key] = isset($array[$key]) ? $array[$key] : $default;
        }

        return $found;
    }

    /**
     * Retrieves muliple single-key values from a list of arrays.
     *
     *     // Get all of the "id" values from a result
     *     $ids = Arr::pluck($result, 'id');
     *
     * [!!] A list of arrays is an array that contains arrays, eg: array(array $a, array $b, array $c, ...)
     *
     * @param   array   list of arrays to check
     * @param   string  key to pluck
     * @return  array
     */
    public static function pluck($array, $key){
        $values = array();

        foreach ($array as $row){
            if (isset($row[$key])){
                // Found a value in this row
                $values[] = $row[$key];
            }
        }

        return $values;
    }

    /**
     * Добавляет значения к началу ассоциативного массива.
     *
     *     // Add an empty value to the start of a select list
     *     Arr::unshift($array, 'none', 'Select a value');
     *
     * @param   array   array to modify
     * @param   string  array key name
     * @param   mixed   array value
     * @return  array
     */
    public static function unshift( array & $array, $key, $val){
        $array = array_reverse($array, TRUE);
        $array[$key] = $val;
        $array = array_reverse($array, TRUE);

        return $array;
    }

    /**
     * Recursive version of [array_map](http://php.net/array_map), applies the
     * same callback to all elements in an array, including sub-arrays.
     *
     *     // Apply "strip_tags" to every element in the array
     *     $array = Arr::map('strip_tags', $array);
     *
     * [!!] Unlike `array_map`, this method requires a callback and will only map
     * a single array.
     *
     * @param   mixed   callback applied to every element in the array
     * @param   array   array to map
     * @return  array
     */
    public static function map($callback, $array){
        foreach ($array as $key => $val){
            if (is_array($val)){
                $array[$key] = Arr::map($callback, $val);
            }else{
                $array[$key] = call_user_func($callback, $val);
            }
        }

        return $array;
    }

    /**
     * Объединяет один или несколько массивов рекурсивно и сохраняет все ключи.
     * Note that this does not work the same as [array_merge_recursive](http://php.net/array_merge_recursive)!
     *
     *     $john = array('name' => 'john', 'children' => array('fred', 'paul', 'sally', 'jane'));
     *     $mary = array('name' => 'mary', 'children' => array('jane'));
     *
     *     // John and Mary are married, merge them together
     *     $john = Arr::merge($john, $mary);
     *
     *     // The output of $john will now be:
     *     array('name' => 'mary', 'children' => array('fred', 'paul', 'sally', 'jane'))
     *
     * @param   array  initial array
     * @param   array  array to merge
     * @param   array  ...
     * @return  array
     */
    public static function merge(array $a1, array $a2){
        $result = array();
        for ($i = 0, $total = func_num_args(); $i < $total; $i++){
            // Get the next array
            $arr = func_get_arg($i);

            // Is the array associative?
            $assoc = Arr::is_assoc($arr);

            foreach ($arr as $key => $val){
                if (isset($result[$key])){
                    if (is_array($val) AND is_array($result[$key])){
                        if (Arr::is_assoc($val)){
                            // Associative arrays are merged recursively
                            $result[$key] = Arr::merge($result[$key], $val);
                        }else{
                            // Find the values that are not already present
                            $diff = array_diff($val, $result[$key]);

                            // Indexed arrays are merged to prevent duplicates
                            $result[$key] = array_merge($result[$key], $diff);
                        }
                    }
                    else{
                        if ($assoc){
                            // Associative values are replaced
                            $result[$key] = $val;
                        }
                        elseif ( ! in_array($val, $result, TRUE)){
                            // Indexed values are added only if they do not yet exist
                            $result[] = $val;
                        }
                    }
                }else{
                    // New values are added
                    $result[$key] = $val;
                }
            }
        }

        return $result;
    }

    /**
     * Перезаписываем значения одного массива на значения другого
     * Keys that do not exist in the first array will not be added!
     *
     *     $a1 = array('name' => 'john', 'mood' => 'happy', 'food' => 'bacon');
     *     $a2 = array('name' => 'jack', 'food' => 'tacos', 'drink' => 'beer');
     *
     *     // Overwrite the values of $a1 with $a2
     *     $array = Arr::overwrite($a1, $a2);
     *
     *     // The output of $array will now be:
     *     array('name' => 'jack', 'mood' => 'happy', 'food' => 'tacos')
     *
     * @param   array   master array
     * @param   array   input arrays that will overwrite existing values
     * @return  array
     */
    public static function overwrite($array1, $array2){
        foreach (array_intersect_key($array2, $array1) as $key => $value){
            $array1[$key] = $value;
        }

        if (func_num_args() > 2){
            foreach (array_slice(func_get_args(), 2) as $array2){
                foreach (array_intersect_key($array2, $array1) as $key => $value){
                    $array1[$key] = $value;
                }
            }
        }

        return $array1;
    }

    /**
     * Creates a callable function and parameter list from a string representation.
     * Note that this function does not validate the callback string.
     *
     *     // Get the callback function and parameters
     *     list($func, $params) = Arr::callback('Foo::bar(apple,orange)');
     *
     *     // Get the result of the callback
     *     $result = call_user_func_array($func, $params);
     *
     * @param   string  callback string
     * @return  array   function, params
     */
    public static function callback($str){
        // Overloaded as parts are found
        $command = $params = NULL;

        // command[param,param]
        if (preg_match('/^([^\(]*+)\((.*)\)$/', $str, $match)){
            // command
            $command = $match[1];

            if ($match[2] !== ''){
                // param,param
                $params = preg_split('/(?<!\\\\),/', $match[2]);
                $params = str_replace('\,', ',', $params);
            }
        }
        else{
            // command
            $command = $str;
        }

        if (strpos($command, '::') !== FALSE){
            // Create a static method callable command
            $command = explode('::', $command, 2);
        }

        return array($command, $params);
    }

    /**
     * Преобразуйте многомерный массив в одномерный массив.
     *
     *     $array = array('set' => array('one' => 'something'), 'two' => 'other');
     *
     *     // Flatten the array
     *     $array = Arr::flatten($array);
     *
     *     // The array will now be
     *     array('one' => 'something', 'two' => 'other');
     *
     * [!!] The keys of array values will be discarded.
     *
     * @param   array   array to flatten
     * @return  array
     * @since   3.0.6
     */
    public static function flatten($array){
        $flat = array();
        foreach ($array as $key => $value)
        {
            if (is_array($value))
            {
                $flat += Arr::flatten($value);
            }
            else
            {
                $flat[$key] = $value;
            }
        }
        return $flat;
    }

}
