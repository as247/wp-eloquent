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

    public function __construct($wpdb, $dsn, $username, $password, $options)
    {
        parent::__construct($dsn, $username, $password, $options);
        $this->db = $wpdb;
    }


    public function beginTransaction()
    {
        if ($this->in_transaction) {
            throw new PDOException("Failed to start transaction. Transaction is already started.");
        }
        $this->in_transaction = true;
        return $this->exec('START TRANSACTION');
    }

    public function commit()
    {
        if (!$this->in_transaction) {
            throw new PDOException("There is no active transaction to commit");
        }
        $this->in_transaction = false;
        return $this->exec('COMMIT');
    }

    public function rollBack()
    {
        if (!$this->in_transaction) {
            throw new PDOException("There is no active transaction to rollback");
        }
        $this->in_transaction = false;
        return $this->exec('ROLLBACK');
    }

    public function inTransaction()
    {
        return $this->in_transaction;
    }

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

    function query($query, $pM = null, $pA = null, $pC = null, ...$args)
    {
        $statement = $this->prepare($query);
        $args = func_get_args();
        array_shift($args);//Remove first param
        $statement->setFetchMode(...$args);
        $statement->execute();
        return $statement;
    }

    public function prepare($query, $options = null)
    {
        $statement = new WpPdoStatement($this);
        $statement->sqlQueryString = $query;
        return $statement;
    }

    public function lastInsertId($name = null)
    {
        return $this->db->insert_id;
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
