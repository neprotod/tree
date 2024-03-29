<?php defined('SYSPATH') OR exit();
/**
 * Database query wrapper.  See [Prepared Statements](database/query/prepared) for usage and examples.
 *
 * @package    Core/Database
 * @category   Query
 */
class Core_Database_Database_Query {

    // Query type
    protected $_type;

    // Cache lifetime
    protected $_lifetime = NULL;

    // SQL statement
    protected $_sql;

    // Quoted query parameters
    protected $_parameters = array();

    // Return results as associative arrays or objects
    protected $_as_object = FALSE;

    // Parameters for __construct when using object results
    protected $_object_params = array();

    /**
     * Creates a new SQL query of the specified type.
     *
     * @param   integer  query type: Database::SELECT, Database::INSERT, etc
     * @param   string   query string
     * @return  void
     */
    public function __construct($type, $sql)
    {
        $this->_type = $type;
        $this->_sql = $sql;
    }

    /**
     * Return the SQL query string.
     *
     * @return  string
     */
    final public function __toString()
    {
        try
        {
            // Return the SQL string
            return $this->compile(Database::instance());
        }
        catch (Exception $e)
        {
            return Core_Exception::text($e);
        }
    }

    /**
     * Get the type of the query.
     *
     * @return  integer
     */
    public function type()
    {
        return $this->_type;
    }

    /**
     * Enables the query to be cached for a specified amount of time.
     *
     * @param   integer  number of seconds to cache
     * @return  $this
     * @uses    Core::$cache_life
     */
    public function cached($lifetime = NULL)
    {
        if ($lifetime === NULL)
        {
            // Use the global setting
            $lifetime = Core::$cache_life;
        }

        $this->_lifetime = $lifetime;

        return $this;
    }

    /**
     * Returns results as associative arrays
     *
     * @return  $this
     */
    public function as_assoc()
    {
        $this->_as_object = FALSE;

        $this->_object_params = array();

        return $this;
    }

    /**
     * Returns results as objects
     *
     * @param   string  classname or TRUE for stdClass
     * @return  $this
     */
    public function as_object($class = TRUE, array $params = NULL)
    {
        $this->_as_object = $class;

        if ($params)
        {
            // Add object parameters
            $this->_object_params = $params;
        }

        return $this;
    }

    /**
     * Set the value of a parameter in the query.
     *
     * @param   string   parameter key to replace
     * @param   mixed    value to use
     * @return  $this
     */
    public function param($param, $value)
    {
        // Add or overload a new parameter
        $this->_parameters[$param] = $value;

        return $this;
    }

    /**
     * Bind a variable to a parameter in the query.
     *
     * @param   string  parameter key to replace
     * @param   mixed   variable to use
     * @return  $this
     */
    public function bind($param, & $var)
    {
        // Bind a value to a variable
        $this->_parameters[$param] =& $var;

        return $this;
    }

    /**
     * Add multiple parameters to the query.
     *
     * @param   array  list of parameters
     * @return  $this
     */
    public function parameters(array $params)
    {
        // Merge the new parameters in
        $this->_parameters = $params + $this->_parameters;

        return $this;
    }

    /**
     * Compile the SQL query and return it. Replaces any parameters with their
     * given values.
     *
     * @param   object  Database instance
     * @return  string
     */
    public function compile(Database $db)
    {
        // Import the SQL locally
        $sql = $this->_sql;

        if ( ! empty($this->_parameters))
        {
            // Quote all of the values
            $values = array_map(array($db, 'quote'), $this->_parameters);

            // Replace the values in the SQL
            $sql = strtr($sql, $values);
        }

        return $sql;
    }

    /**
     * Execute the current query on the given database.
     *
     * @param   mixed    Database instance or name of instance
     * @return  object   Database_Result for SELECT queries
     * @return  mixed    the insert id for INSERT queries
     * @return  integer  number of affected rows for all other queries
     */
    public function execute($db = NULL)
    {
        if ( ! is_object($db))
        {
            // Get the database instance
            $db = Database::instance($db);
        }

        // Compile the SQL query
        $sql = $this->compile($db);

        if ($this->_lifetime !== NULL AND $this->_type === Database::SELECT)
        {
            // Set the cache key based on the database instance name and SQL
            $cache_key = 'Database::query("'.$db.'", "'.$sql.'")';

            if ($result = Core::cache($cache_key, NULL, $this->_lifetime))
            {
                // Return a cached result
                return new Database_Result_Cached($result, $sql, $this->_as_object, $this->_object_params);
            }
        }

        // Execute the query
        $result = $db->query($this->_type, $sql, $this->_as_object, $this->_object_params);

        if (isset($cache_key))
        {
            // Cache the result array
            Core::cache($cache_key, $result->as_array(), $this->_lifetime);
        }

        return $result;
    }

} // End Database_Query
