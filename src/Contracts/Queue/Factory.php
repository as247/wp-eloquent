<?php

namespace As247\WpEloquent\Contracts\Queue;

interface Factory
{
    /**
     * Resolve a queue connection instance.
     *
     * @param  string|null  $name
     * @return \As247\WpEloquent\Contracts\Queue\Queue
     */
    public function connection($name = null);
}
