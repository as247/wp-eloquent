<?php


namespace As247\WpEloquent\Database;


use PDO;
use PDOException;

/**
 *
 */
class WpPdo extends PDO
{
    /**
     * @var \wpdb
     */
    protected $db;
    protected $in_transaction;

    protected static $attributeCache=[];

    public function __construct($wpdb, $dsn, $username, $password, $options)
    {
        //parent::__construct($dsn, $username, $password, $options);
        $this->db = $wpdb;
    }

    #[\ReturnTypeWillChange]
    public function beginTransaction()
    {
        if ($this->in_transaction) {
            throw new PDOException("Failed to start transaction. Transaction is already started.");
        }
        $this->in_transaction = true;
        return $this->exec('START TRANSACTION');
    }

    #[\ReturnTypeWillChange]
    public function commit()
    {
        if (!$this->in_transaction) {
            throw new PDOException("There is no active transaction to commit");
        }
        $this->in_transaction = false;
        return $this->exec('COMMIT');
    }

    #[\ReturnTypeWillChange]
    public function rollBack()
    {
        if (!$this->in_transaction) {
            throw new PDOException("There is no active transaction to rollback");
        }
        $this->in_transaction = false;
        return $this->exec('ROLLBACK');
    }

    #[\ReturnTypeWillChange]
    public function inTransaction()
    {
        return $this->in_transaction;
    }

    #[\ReturnTypeWillChange]
    public function exec($statement)
    {
        $error = $this->db->suppress_errors();
        $result = $this->db->query($statement);
        $this->db->suppress_errors($error);
        if ($this->db->last_error) {
            throw new \Exception($this->db->last_error);
        }
        return $result;
    }

    #[\ReturnTypeWillChange]
    function query($query, $pM = null, $pA = null, $pC = null, ...$args)
    {
        $statement = $this->prepare($query);
        $args = func_get_args();
        array_shift($args);//Remove first param
        $statement->setFetchMode(...$args);
        $statement->execute();
        return $statement;
    }

    #[\ReturnTypeWillChange]
    public function prepare($query, $options = null)
    {
        $statement = new WpPdoStatement($this);
        $statement->sqlQueryString = $query;
        return $statement;
    }

    #[\ReturnTypeWillChange]
    public function lastInsertId($name = null)
    {
        return $this->db->insert_id;
    }

    #[\ReturnTypeWillChange]
    public function getAttribute($attribute)
    {
        switch ($attribute){
            case PDO::ATTR_DRIVER_NAME:
                return 'mysql';
            case PDO::ATTR_SERVER_VERSION:
                return $this->getServerVersion();
        }
        return null;
    }

    #[\ReturnTypeWillChange]
    public function setAttribute($attribute, $value)
    {
        static::$attributeCache[$attribute]=$value;
        return true;
    }

    protected function getServerVersion( ) {
        if(!isset(static::$attributeCache['version'])){
            $version=null;
            if ( method_exists( $this->db, 'db_server_info' ) ) {
                $version = $this->db->db_server_info();
            }

            if ( ! $version ) {
                $version = $this->db->get_var( 'SELECT VERSION()' );
            }

            if(!$version){
                $version='Unknown';
            }
            static::$attributeCache['version']=$version;
        }
        return static::$attributeCache['version'];
    }

    public function __call($name, $arguments)
    {
        return $this->db->$name(...$arguments);
    }

    public function __get($name)
    {
        return $this->db->$name;
    }

    public function __isset($name)
    {
        return isset($this->db->$name);
    }

    public function getWpdb()
    {
        return $this->db;
    }

}
