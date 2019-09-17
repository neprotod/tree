<?php defined('SYSPATH') OR exit();
/**
 * Tree exception class. Translates exceptions using the [I18n] class.
 *
 * @package    Tree
 * @category   Exceptions
 */
class Core_Exception_Exception extends Exception {

    /**
     * @var  array  PHP error code => human readable name
     */
    public static $php_errors = array(
        E_ERROR              => 'Fatal Error',
        E_USER_ERROR         => 'User Error',
        E_PARSE              => 'Parse Error',
        E_WARNING            => 'Warning',
        E_USER_WARNING       => 'User Warning',
        E_STRICT             => 'Strict',
        E_NOTICE             => 'Notice',
        E_RECOVERABLE_ERROR  => 'Recoverable Error',
    );
    public static $error_full = array();
    /**
     * Обработчик исключения
     * @param   string   error message
     * @param   array    translation variables
     * @param   integer  the exception code
     * @return  void
     */
    /*function __construct($message, array $variables = NULL, $code = 0)
    {

        // Pass the message to the parent
        parent::__construct($message, $code);
    }*/
    protected static $directory_error = 'error';
    
    static function handler(Exception $e){
        try{
            // Получите информацию исключения
            $type    = get_class($e);
            $code    = $e->getCode();
            $message = $e->getMessage();
            $file    = $e->getFile();
            $line    = $e->getLine();
            // Получить след исключения
            $trace = $e->getTrace();
            if ($e instanceof ErrorException){
                if (isset(Core_Exception::$php_errors[$code])){
                    // Use the human-readable error name
                    $code = Core_Exception::$php_errors[$code];
                }
            }

            // Create a text version of the exception
            $error = Core_Exception::text($e);

            // Убедится что заголовки отправлены
            /*
            if ( ! headers_sent())
            {
                // Убедитесь, что надлежащее http заголовок отправляется
                $http_header_status = ($e instanceof HTTP_Exception) ? $code : 500;

                header('Content-Type: text/html; charset='.Kohana::$charset, TRUE, $http_header_status);
            }
            */
            
            // Включаем буфиринизацию
            ob_start();

            // Include the exception HTML
            if ($error_file = Core::find_file(Core_Exception::$directory_error, 'error')){
                include $error_file;
            }else{
                exit('Нет даже файла ошибки!');
            }

            // Выводим буфер
            echo ob_get_clean();
            return TRUE;
        }
        catch (Exception $e){
            // Clean the output buffer if one exists
            ob_get_level() and ob_clean();
            echo 1;
            // Покажите текст исключения
            echo Core_Exception::text($e), "\n";
            
            // Выход с состоянием ошибки
            exit(1);
        }
    }

    /**
     * Получите одну строку текста, представляющий исключение:
     *
     * Error [ Code ]: Message ~ File [ Line ]
     *
     * @param   object  Exception
     * @return  string
     */
    public static function text(Exception $e){
        return sprintf('%s [ %s ]: %s ~ %s [ %d ]',
            get_class($e), $e->getCode(), strip_tags($e->getMessage()), Debug::path($e->getFile()), $e->getLine());
    }
    
    public function __construct($message, array $variables = NULL, $code = 0){

        // Set the message
        $message = STR::__($message, $variables);
        // Pass the message to the parent
        parent::__construct($message, $code);
    }
}
