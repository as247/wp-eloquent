<?php

namespace As247\WpEloquent\Events;

use Closure;

if (! function_exists('As247\WpEloquent\Events\queueable')) {
    /**
     * Create a new queued Closure event listener.
     *
     * @param  \Closure  $closure
     * @return \As247\WpEloquent\Events\QueuedClosure
     */
    function queueable(Closure $closure)
    {
        return new QueuedClosure($closure);
    }
}
