<?php


namespace As247\WpEloquent\Database;


use PDO;
use PDOException;

class WpPdo extends PDO
{
    /**
     * @var WpConnection
     */
    protected $db;
    protected $in_transaction;
    public function __construct($wpdb)
    {
        try {
            parent::__construct(null);
        }catch (PDOException $e){

        }
        $this->db=$wpdb;
    }

    /**
     * (PHP 5 &gt;= 5.1.0, PHP 7, PECL pdo &gt;= 0.1.0)<br/>
     * Initiates a transaction
     * <p>
     * Turns off autocommit mode. While autocommit mode is turned off,
     * changes made to the database via the PDO object instance are not committed
     * until you end the transaction by calling {@link PDO::commit()}.
     * Calling {@link PDO::rollBack()} will roll back all changes to the database and
     * return the connection to autocommit mode.
     * </p>
     * <p>
     * Some databases, including MySQL, automatically issue an implicit COMMIT
     * when a database definition language (DDL) statement
     * such as DROP TABLE or CREATE TABLE is issued within a transaction.
     * The implicit COMMIT will prevent you from rolling back any other changes
     * within the transaction boundary.
     * </p>
     * @link https://php.net/manual/en/pdo.begintransaction.php
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     * @throws PDOException If there is already a transaction started or
     * the driver does not support transactions <br/>
     * <b>Note</b>: An exception is raised even when the <b>PDO::ATTR_ERRMODE</b>
     * attribute is not <b>PDO::ERRMODE_EXCEPTION</b>.
     */
    public function beginTransaction () {
        if($this->in_transaction){
            throw new PDOException("Failed to start transaction. Transaction is already started.");
        }
        $this->in_transaction=true;
        return $this->db->unprepared('START TRANSACTION');
    }

    /**
     * (PHP 5 &gt;= 5.1.0, PHP 7, PECL pdo &gt;= 0.1.0)<br/>
     * Commits a transaction
     * @link https://php.net/manual/en/pdo.commit.php
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     * @throws PDOException if there is no active transaction.
     */
    public function commit () {
        if(!$this->in_transaction){
            throw new PDOException("There is no active transaction to commit");
        }
        $this->in_transaction=false;
        return $this->db->unprepared('COMMIT');
    }

    /**
     * (PHP 5 &gt;= 5.1.0, PHP 7, PECL pdo &gt;= 0.1.0)<br/>
     * Rolls back a transaction
     * @link https://php.net/manual/en/pdo.rollback.php
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     * @throws PDOException if there is no active transaction.
     */
    public function rollBack () {
        if(!$this->in_transaction){
            throw new PDOException("There is no active transaction to rollback");
        }
        $this->in_transaction=false;
        return $this->db->unprepared('ROLLBACK');
    }

    /**
     * (PHP 5 &gt;= 5.3.3, Bundled pdo_pgsql, PHP 7)<br/>
     * Checks if inside a transaction
     * @link https://php.net/manual/en/pdo.intransaction.php
     * @return bool <b>TRUE</b> if a transaction is currently active, and <b>FALSE</b> if not.
     */
    public function inTransaction () {
        return $this->in_transaction;
    }


    /**
     * (PHP 5 &gt;= 5.1.0, PHP 7, PECL pdo &gt;= 0.1.0)<br/>
     * Execute an SQL statement and return the number of affected rows
     * @link https://php.net/manual/en/pdo.exec.php
     * @param string $statement <p>
     * The SQL statement to prepare and execute.
     * </p>
     * <p>
     * Data inside the query should be properly escaped.
     * </p>
     * @return int|false <b>PDO::exec</b> returns the number of rows that were modified
     * or deleted by the SQL statement you issued. If no rows were affected,
     * <b>PDO::exec</b> returns 0.
     * </p>
     * This function may
     * return Boolean <b>FALSE</b>, but may also return a non-Boolean value which
     * evaluates to <b>FALSE</b>. Please read the section on Booleans for more
     * information. Use the ===
     * operator for testing the return value of this
     * function.
     * <p>
     * The following example incorrectly relies on the return value of
     * <b>PDO::exec</b>, wherein a statement that affected 0 rows
     * results in a call to <b>die</b>:
     * <code>
     * $db->exec() or die(print_r($db->errorInfo(), true));
     * </code>
     */
    public function exec ($statement) {
        return $this->db->unprepared($statement);
    }

    /**
     * (PHP 5 &gt;= 5.1.0, PHP 7, PECL pdo &gt;= 0.1.0)<br/>
     * Returns the ID of the last inserted row or sequence value
     * @link https://php.net/manual/en/pdo.lastinsertid.php
     * @param string $name [optional] <p>
     * Name of the sequence object from which the ID should be returned.
     * </p>
     * @return string If a sequence name was not specified for the <i>name</i>
     * parameter, <b>PDO::lastInsertId</b> returns a
     * string representing the row ID of the last row that was inserted into
     * the database.
     * </p>
     * <p>
     * If a sequence name was specified for the <i>name</i>
     * parameter, <b>PDO::lastInsertId</b> returns a
     * string representing the last value retrieved from the specified sequence
     * object.
     * </p>
     * <p>
     * If the PDO driver does not support this capability,
     * <b>PDO::lastInsertId</b> triggers an
     * IM001 SQLSTATE.
     */
    public function lastInsertId($name=null)
    {
        return $this->db->getWpdb()->insert_id;
    }
}
