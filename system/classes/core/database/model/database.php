<?php defined('SYSPATH') OR exit();
/**
 * Database Model base class.
 *
 * @package    Core/Database
 * @category   Models
 */
abstract class Core_Database_Model_Database extends Model {

    /**
     * Create a new model instance. A [Database] instance or configuration
     * group name can be passed to the model. If no database is defined, the
     * "default" database group will be used.
     *
     *     $model = Model::factory($name);
     *
     * @param   string   model name
     * @param   mixed    Database instance object or string
     * @return  Model
     */
    public static function factory($name, $db = NULL)
    {
        // Add the model prefix
        $class = 'Model_'.$name;

        return new $class($db);
    }

    // Database instance
    protected $_db = 'default';

    /**
     * Loads the database.
     *
     *     $model = new Foo_Model($db);
     *
     * @param   mixed  Database instance object or string
     * @return  void
     */
    public function __construct($db = NULL)
    {
        if ($db !== NULL)
        {
            // Set the database instance name
            $this->_db = $db;
        }

        if (is_string($this->_db))
        {
            // Load the database
            $this->_db = Database::instance($this->_db);
        }
    }

}
