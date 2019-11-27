<?php

namespace As247\WpEloquent\Database\Events;

use As247\WpEloquent\Contracts\Database\Events\MigrationEvent as MigrationEventContract;
use As247\WpEloquent\Database\Migrations\Migration;

abstract class MigrationEvent implements MigrationEventContract
{
    /**
     * An migration instance.
     *
     * @var \As247\WpEloquent\Database\Migrations\Migration
     */
    public $migration;

    /**
     * The migration method that was called.
     *
     * @var string
     */
    public $method;

    /**
     * Create a new event instance.
     *
     * @param  \As247\WpEloquent\Database\Migrations\Migration  $migration
     * @param  string  $method
     * @return void
     */
    public function __construct(Migration $migration, $method)
    {
        $this->method = $method;
        $this->migration = $migration;
    }
}
