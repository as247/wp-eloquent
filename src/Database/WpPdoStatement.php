<?php

namespace As247\WpEloquent\Database;

class WpPdoStatement extends \PDOStatement
{
    public $sqlQueryString;
    protected $pdo;
    public function __construct(WpPdo $pdo)
    {
        $this->pdo=$pdo;
    }
    function execute($params = null)
    {
        $this->pdo->exec($this->sqlQueryString);
    }
}