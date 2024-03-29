<?php defined('SYSPATH') OR exit();
/**
 * PDO database connection.
 *
 * @package    Core/Database
 * @category   Drivers
 */
class Core_Database_Database_PDO extends Database {
    
    public $a = 11;
    // PDO uses no quoting for identifiers
    protected $_identifier = '';

    protected function __construct($name, array $config)
    {
        parent::__construct($name, $config);

        if (isset($this->_config['identifier']))
        {
            // Allow the identifier to be overloaded per-connection
            $this->_identifier = (string) $this->_config['identifier'];
        }
    }

    public function connect()
    {
        if ($this->_connection)
            return;

        // Extract the connection parameters, adding required variabels
        extract($this->_config['connection'] + array(
            'dsn'        => '',
            'username'   => NULL,
            'password'   => NULL,
            'persistent' => FALSE,
        ));

        // Clear the connection parameters for security
        unset($this->_config['connection']);

        // Force PDO to use exceptions for all errors
        $attrs = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);

        if ( ! empty($persistent))
        {
            // Make the connection persistent
            $attrs[PDO::ATTR_PERSISTENT] = TRUE;
        }

        try
        {
            // Create a new PDO connection
            $this->_connection = new PDO($dsn, $username, $password, $attrs);
        }
        catch (PDOException $e)
        {
            throw new Database_Exception($e->getCode(), '<b>[:code]</b> :error', array(
                ':code' => $e->getMessage(),
                ':error' => $e->getCode(),
            ), $e->getCode());
        }

        if ( ! empty($this->_config['charset']))
        {
            // Set the character set
            $this->set_charset($this->_config['charset']);
        }
    }

    public function disconnect()
    {
        // Destroy the PDO object
        $this->_connection = NULL;

        return TRUE;
    }

    public function set_charset($charset)
    {
        // Make sure the database is connected
        $this->_connection or $this->connect();

        // Execute a raw SET NAMES query
        $this->_connection->exec('SET NAMES '.$this->quote($charset));
    }

    public function query($type, $sql, $as_object = FALSE, array $params = NULL)
    {
        // Make sure the database is connected
        $this->_connection or $this->connect();

        /*if ( ! empty($this->_config['profiling']))
        {
            // Benchmark this query for the current instance
            $benchmark = Profiler::start("Database ({$this->_instance})", $sql);
        }*/

        try
        {
            $result = $this->_connection->query($sql);
        }
        catch (Exception $e)
        {
            /*if (isset($benchmark))
            {
                // This benchmark is worthless
                Profiler::delete($benchmark);
            }*/

            // Convert the exception in a database exception
            throw new Database_Exception($e->getCode(), '<b>[:code]</b> :error ( :query )', array(
                ':code' => $e->getMessage(),
                ':error' => $e->getCode(),
                ':query' => $sql,
            ), $e->getCode());
        }

        /*if (isset($benchmark))
        {
            Profiler::stop($benchmark);
        }*/

        // Set the last query
        $this->last_query = $sql;

        if ($type === Database::SELECT)
        {
            // Convert the result into an array, as PDOStatement::rowCount is not reliable
            if ($as_object === FALSE)
            {
                $result->setFetchMode(PDO::FETCH_ASSOC);
            }
            elseif (is_string($as_object))
            {
                $result->setFetchMode(PDO::FETCH_CLASS, $as_object, $params);
            }
            else
            {
                $result->setFetchMode(PDO::FETCH_CLASS, 'stdClass');
            }

            return $result = $result->fetchAll();

            // Return an iterator of results
            //return new Database_Result_Cached($result, $sql, $as_object, $params);
        }
        elseif ($type === Database::INSERT)
        {
            // Return a list of insert id and rows created
            return array(
                $this->_connection->lastInsertId(),
                $result->rowCount(),
            );
        }
        else
        {
            // Return the number of rows affected
            return $result->rowCount();
        }
    }

    public function begin($mode = NULL)
    {
        // Make sure the database is connected
        $this->_connection or $this->connect();

        return $this->_connection->beginTransaction();
    }

    public function commit()
    {
        // Make sure the database is connected
        $this->_connection or $this->connect();

        return $this->_connection->commit();
    }

    public function rollback()
    {
        // Make sure the database is connected
        $this->_connection or $this->connect();

        return $this->_connection->rollBack();
    }

    public function list_tables($like = NULL)
    {
        throw new Core_Exception('Database method :method is not supported by :class',
            array(':method' => __FUNCTION__, ':class' => __CLASS__));
    }

    public function list_columns($table, $like = NULL, $add_prefix = TRUE)
    {
        throw new Core_Exception('Database method :method is not supported by :class',
            array(':method' => __FUNCTION__, ':class' => __CLASS__));
    }

    public function escape($value)
    {
        // Make sure the database is connected
        $this->_connection or $this->connect();

        return $this->_connection->quote($value);
    }

} // End Database_PDO
