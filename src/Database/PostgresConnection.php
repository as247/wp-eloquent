<?php

namespace As247\WpEloquent\Database;

use Doctrine\DBAL\Driver\PDOPgSql\Driver as DoctrineDriver;
use As247\WpEloquent\Database\Query\Grammars\PostgresGrammar as QueryGrammar;
use As247\WpEloquent\Database\Query\Processors\PostgresProcessor;
use As247\WpEloquent\Database\Schema\Grammars\PostgresGrammar as SchemaGrammar;
use As247\WpEloquent\Database\Schema\PostgresBuilder;

class PostgresConnection extends Connection
{
    /**
     * Get the default query grammar instance.
     *
     * @return \As247\WpEloquent\Database\Query\Grammars\PostgresGrammar
     */
    protected function getDefaultQueryGrammar()
    {
        return $this->withTablePrefix(new QueryGrammar);
    }

    /**
     * Get a schema builder instance for the connection.
     *
     * @return \As247\WpEloquent\Database\Schema\PostgresBuilder
     */
    public function getSchemaBuilder()
    {
        if (is_null($this->schemaGrammar)) {
            $this->useDefaultSchemaGrammar();
        }

        return new PostgresBuilder($this);
    }

    /**
     * Get the default schema grammar instance.
     *
     * @return \As247\WpEloquent\Database\Schema\Grammars\PostgresGrammar
     */
    protected function getDefaultSchemaGrammar()
    {
        return $this->withTablePrefix(new SchemaGrammar);
    }

    /**
     * Get the default post processor instance.
     *
     * @return \As247\WpEloquent\Database\Query\Processors\PostgresProcessor
     */
    protected function getDefaultPostProcessor()
    {
        return new PostgresProcessor;
    }

    /**
     * Get the Doctrine DBAL driver.
     *
     * @return \Doctrine\DBAL\Driver\PDOPgSql\Driver
     */
    protected function getDoctrineDriver()
    {
        return new DoctrineDriver;
    }
}
