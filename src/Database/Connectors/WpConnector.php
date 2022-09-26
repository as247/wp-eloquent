<?php

namespace As247\WpEloquent\Database\Connectors;

use As247\WpEloquent\Database\WpPdo;

class WpConnector extends MySqlConnector
{
    /**
     * Create a new PDO connection instance.
     *
     * @param  string  $dsn
     * @param  string  $username
     * @param  string  $password
     * @param  array  $options
     * @return \PDO
     */
    protected function createPdoConnection($dsn, $username, $password, $options)
    {
        global $wpdb;
        return new WpPdo($wpdb);
    }
}