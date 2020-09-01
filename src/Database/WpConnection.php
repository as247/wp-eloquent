<?php


namespace As247\WpEloquent\Database;

use DateTime;
use Exception;
use JsonSerializable;
use Serializable;
use wpdb;

class WpConnection extends MySqlConnection
{
    /**
     * @var wpdb
     */
    public $db;
    /**
     * The database connection configuration options.
     *
     * @var array
     */
    protected $config = [];
    /**
     * Initializes the Database class
     *
     * @return static
     */
    public static function instance()
    {
        static $instance = false;
        global $wpdb;
        if (!$instance) {
            $instance = new self($wpdb,DB_NAME,$wpdb->prefix);
        }
        return $instance;
    }

    public function __construct($wpdb, $database = '', $tablePrefix = '', array $config = [])
    {
        $this->db = $wpdb;
        $pdo=new WpPdo($this);
        parent::__construct($pdo, $database, $tablePrefix, $config);
    }

    /**
     * Get wpdb object
     * @return wpdb
     */
    function getWpdb(){
        return $this->db;
    }

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
            $error=$this->db->suppress_errors();
            $result = $this->db->get_results($query);
            $this->db->suppress_errors($error);
            if ($this->db->last_error)
                throw new QueryException($query, $bindings, new Exception($this->db->last_error));
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
        return $this->runRawQuery($query,true);
    }

    /**
     * Run raw sql query
     * @param $query
     * @param bool $returnBoolean
     * @return mixed
     */
    protected function runRawQuery($query, $returnBoolean=false){
        return $this->run($query, [], function ($query) use($returnBoolean) {
            if ($this->pretending()) {
                return true;
            }
            $error=$this->db->suppress_errors();
            $result = $this->db->query($query);
            $this->db->suppress_errors($error);
            if($this->db->last_error){
                throw new QueryException($query, [], new Exception($this->db->last_error));
            }
            if($returnBoolean){
                true;
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
            } elseif (is_scalar($value)) {
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
}
