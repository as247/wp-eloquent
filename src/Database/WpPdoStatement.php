<?php

namespace As247\WpEloquent\Database;

use PDO;
class WpPdoStatement extends \PDOStatement
{
    public $sqlQueryString;
    protected $pdo;
    protected $executed = false;
    protected $result = [];
    protected $bindingParams = [];
    protected $cursor = -1;
    protected $resultCount = 0;
    protected $columnCount = 0;
    protected $defaultFetchMode;

    public function __construct(WpPdo $pdo)
    {
        $this->pdo = $pdo;
    }
    #[\ReturnTypeWillChange]
    function execute($params = null)
    {
        if ($this->executed) {
            return false;
        }
        $this->cursor = -1;
        $this->resultCount = $this->columnCount = 0;
        $this->pdo->exec($this->bindParamsForQuery($this->sqlQueryString));
        $this->result = $this->pdo->last_result;
        $this->resultCount = count($this->result);
        if (isset($this->result[0])) {
            $this->columnCount = count(get_object_vars($this->result[0]));
        }
        $this->executed = true;
        return true;
    }
    #[\ReturnTypeWillChange]
    function setFetchMode($mode, $p1 = null, $p2 = null, ...$params8)
    {
        $this->defaultFetchMode = func_get_args();
        return true;
    }

    protected function proccessRowForMode($row, $mode, ...$args)
    {
        switch ($mode) {
            case PDO::FETCH_BOTH:
            default:
                $assoc = get_object_vars($row);
                return array_merge(array_values($assoc), $assoc);
            case PDO::FETCH_LAZY:
            case PDO::FETCH_OBJ:
                return $row;
            case PDO::FETCH_ASSOC:
            case PDO::FETCH_NAMED:
                return get_object_vars($row);

            case PDO::FETCH_COLUMN:
                $assoc = array_values(get_object_vars($row));
                $columnIndex = $args[0] ?? $this->defaultFetchMode[1] ?? 0;
                return $assoc[$columnIndex] ?? false;
            case PDO::FETCH_NUM:
                return array_values(get_object_vars($row));
            case PDO::FETCH_CLASS:
                $class = $args[0] ?? $this->defaultFetchMode[1] ?? 'stdClass';
                if ($class === 'stdClass') {
                    return $row;
                }
                $constructorArgs = $args[1] ?? $this->defaultFetchMode[2] ?? [];
                if (!is_array($constructorArgs)) {
                    $constructorArgs = (array)$constructorArgs;
                }
                $obj = new $class(...$constructorArgs);
                foreach (get_object_vars($row) as $key => $value) {
                    $obj->$key = $value;
                }
                return $obj;
            case PDO::FETCH_INTO:
                $obj = $args[0] ?? $this->defaultFetchMode[1];
                if (is_object($obj)) {
                    foreach (get_object_vars($row) as $key => $value) {
                        $obj->$key = $value;
                    }
                    return $obj;
                }
                return false;

        }
    }
    #[\ReturnTypeWillChange]
    function fetch($mode = PDO::FETCH_BOTH, $cursorOrientation = PDO::FETCH_ORI_NEXT, $cursorOffset = 0)
    {
        if (func_num_args() === 0) {
            $mode = $this->defaultFetchMode[0] ?? PDO::FETCH_BOTH;
        }
        switch ($cursorOrientation) {
            case PDO::FETCH_ORI_NEXT:
            default:
                $rowIndex = ++$this->cursor;
                break;
            case PDO::FETCH_ORI_REL:
                $rowIndex = max(0, $this->cursor++);
                break;
            case PDO::FETCH_ORI_ABS:
            case PDO::FETCH_ORI_FIRST:
                $rowIndex = 0;
                break;
            case PDO::FETCH_ORI_LAST:
                $rowIndex = $this->resultCount;
                break;
            case PDO::FETCH_ORI_PRIOR:
                $rowIndex = $this->resultCount - (++$this->cursor);
                break;

        }
        $rowIndex = $rowIndex + $cursorOffset;
        $row = $this->result[$rowIndex] ?? false;
        if (!$row) {
            return false;
        }
        return $this->proccessRowForMode($row, $mode);

    }
    #[\ReturnTypeWillChange]
    function fetchAll($mode = null, $map = NULL, $ctor_args = NULL, ...$args8)
    {
        return array_map(function ($row) use ($mode, $map, $ctor_args) {
            return $this->proccessRowForMode($row, $mode, $map, $ctor_args);
        }, $this->result);
    }
    #[\ReturnTypeWillChange]
    function fetchObject($class = "stdClass", $constructorArgs = [])
    {
        return $this->proccessRowForMode($this->fetch(PDO::FETCH_OBJ), PDO::FETCH_CLASS, $class, $constructorArgs);
    }
    #[\ReturnTypeWillChange]
    function fetchColumn($column = 0)
    {
        $row = $this->fetch(PDO::FETCH_NUM);
        return $row[$column] ?? false;
    }
    #[\ReturnTypeWillChange]
    function rowCount()
    {
        return $this->resultCount;
    }
    #[\ReturnTypeWillChange]
    function columnCount()
    {
        return $this->columnCount;
    }
    #[\ReturnTypeWillChange]
    public function closeCursor()
    {
        $this->executed = false;
        $this->result = [];
        return true;
    }

    #[\ReturnTypeWillChange]
    function bindParam($param, &$var, $type = NULL, $maxLength = NULL, $driverOptions = NULL)
    {
        $this->bindingParams[$param] = [
            &$var, $type, $maxLength, $driverOptions
        ];
        return true;
    }
    #[\ReturnTypeWillChange]
    public function bindValue($param, $value, $type = null)
    {
        //Simple param convert
        if ($type === PDO::PARAM_INT) {
            $value = intval($value);
        } elseif ($type === PDO::PARAM_BOOL) {
            $value = $value ? 1 : 0;
        } else {
            $value = (string)$value;
        }
        $this->bindingParams[$param] = [
            $value, $type
        ];
        return true;
    }

    /**
     * A hacky way to emulate bind parameters into SQL query
     *
     * @param $query
     *
     * @return string
     */
    private function bindParamsForQuery($query)
    {
        $query = str_replace('"', '`', $query);
        $bindings = $this->bindingParams;
        if (!$bindings) {
            return $query;
        }
        $indexBindings = [];
        $keyBindings = [];
        foreach ($this->bindingParams as $param => $replace) {
            $value = $replace[0];
            $type = $replace[1];
            $maxLength = $replace[2] ?? null;
            $driverOptions = $replace[3] ?? null;
            $param = trim($param, ':');
            if ($value === null || $type === PDO::PARAM_NULL) {
                $value = 'null';
            } else {
                if ($type === PDO::PARAM_INT) {
                    $value = intval($value);
                } elseif ($type === PDO::PARAM_BOOL) {
                    $value = $value ? 1 : 0;
                } else {
                    $value = "'" . $this->escSql((string)$value) . "'";
                }
            }
            if ($maxLength) {
                $value = mb_substr($value, 0, $maxLength);
            }
            if (is_numeric($param)) {
                $indexBindings[$param] = $value;
            } else {
                $keyBindings[$param] = $value;
            }
        }
        if ($indexBindings) {
            $query = str_replace(array('%', '?'), array('%%', '%s'), $query);
            $query = vsprintf($query, $indexBindings);
        }
        foreach ($keyBindings as $key => $value) {
            $query = str_replace(":$key", $value, $query);
        }
        return $query;
    }

    protected function escSql($sql)
    {
        if (function_exists('esc_sql')) {
            return esc_sql($sql);
        }
        return addslashes($sql);
    }
}