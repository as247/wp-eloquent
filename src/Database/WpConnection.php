<?php


namespace As247\WpEloquent\Database;

use DateTime;
use Exception;
use JsonSerializable;
use Serializable;

class WpConnection extends MySqlConnection
{
    /**
     * The active PDO connection.
     *
     * @var WpPdo
     */
    protected $pdo;

    /**
     * Run a select statement against the database.
     *
     * @param  string $query
     * @param  array $bindings
     * @param  bool $useReadPdo
     * @throws QueryException
     *
     * @return array
     */
    public function select($query, $bindings = [], $useReadPdo = true)
    {
        return $this->run($query, $bindings, function ($query, $bindings) use ($useReadPdo) {
            if ($this->pretending()) {
                return [];
            }
            $query = $this->bindParams($query, $bindings);
            $error=$this->getPdo()->suppress_errors();
            $result = $this->getPdo()->get_results($query);
            $this->getPdo()->suppress_errors($error);
            if ($this->getPdo()->last_error)
                throw new QueryException($query, $bindings, new Exception($this->getPdo()->last_error));
            return $result;
        });

    }
    /**
     * Run a select statement against the database and returns a generator.
     *
     * @param  string  $query
     * @param  array  $bindings
     * @param  bool  $useReadPdo
     * @return \Generator
     */
    public function cursor($query, $bindings = [], $useReadPdo = true)
    {
        $result=$this->select($query,$bindings,$useReadPdo);
        foreach ($result as $row){
            yield $row;
        }
    }


    /**
     * Execute an SQL statement and return the boolean result.
     *
     * @param  string $query
     * @param  array $bindings
     *
     * @return bool
     */
    public function statement($query, $bindings = array())
    {
        $new_query = $this->bindParams($query, $bindings);
        return $this->unprepared($new_query);
    }
    /**
     * Run an SQL statement and get the number of rows affected.
     *
     * @param  string $query
     * @param  array $bindings
     *
     * @return int
     */
    public function affectingStatement($query, $bindings = array())
    {
        $new_query = $this->bindParams($query, $bindings);
        return $this->runRawQuery($new_query);
    }
    /**
     * Run a raw, unprepared query against the PDO connection.
     *
     * @param  string $query
     *
     * @return bool
     */
    public function unprepared($query)
    {
        return (bool)$this->runRawQuery($query);
    }

    /**
     * Run raw sql query
     * @param $query
     * @return int
     */
    protected function runRawQuery($query){
        return $this->run($query, [], function ($query) {
            if ($this->pretending()) {
                return 1;
            }
            $error=$this->getPdo()->suppress_errors();
            $result = $this->getPdo()->get_results($query);
            $this->getPdo()->suppress_errors($error);
            if($this->getPdo()->last_error){
                throw new QueryException($query, [], new Exception($this->getPdo()->last_error));
            }
            return intval($result);
        });
    }

    /**
     * A hacky way to emulate bind parameters into SQL query
     *
     * @param $query
     * @param $bindings
     *
     * @return mixed
     */
    private function bindParams($query, $bindings)
    {
        $query = str_replace('"', '`', $query);
        $bindings = $this->prepareBindings($bindings);
        if (!$bindings) {
            return $query;
        }
        $bindings = array_map(function ($replace) {
            if (is_string($replace)) {
                $replace = "'" . esc_sql($replace) . "'";
            } elseif ($replace === null) {
                $replace = "null";
            }
            return $replace;
        }, $bindings);
        $query = str_replace(array('%', '?'), array('%%', '%s'), $query);
        $query = vsprintf($query, $bindings);
        return $query;
    }
    /**
     * Prepare the query bindings for execution.
     *
     * @param  array $bindings
     *
     * @return array
     */
    public function prepareBindings(array $bindings)
    {
        $grammar = $this->getQueryGrammar();
        foreach ($bindings as $key => $value) {
            // Micro-optimization: check for scalar values before instances
            if (is_bool($value)) {
                $bindings[$key] = intval($value);
            } elseif (is_scalar($value) || is_null($value)) {//null is not scalar but expected to keep
                continue;
            } elseif ($value instanceof DateTime) {
                // We need to transform all instances of the DateTime class into an actual
                // date string. Each query grammar maintains its own date string format
                // so we'll just ask the grammar for the format to get from the date.
                $bindings[$key] = $value->format($grammar->getDateFormat());
            } elseif(is_object($value)){
                if($value instanceof Serializable){
                    $bindings[$key] = $value->serialize();
                }elseif($value instanceof JsonSerializable){
                    $bindings[$key] = json_encode($value->jsonSerialize());
                }else{
                    $bindings[$key] = (string) $value;
                }
            }else{
                $bindings[$key] = (string) $value;
            }
        }
        return $bindings;
    }

    /**
     * @return WpPdo|\Closure|\PDO
     */
    function getPdo()
    {
        return parent::getPdo();
    }
}
