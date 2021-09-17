<?php

namespace As247\WpEloquent\Contracts\Support;

interface DeferringDisplayableValue
{
    /**
     * Resolve the displayable value that the class is deferring.
     *
     * @return \As247\WpEloquent\Contracts\Support\Htmlable|string
     */
    public function resolveDisplayableValue();
}
