<?php

namespace As247\WpEloquent\Contracts\Support;

interface MessageProvider
{
    /**
     * Get the messages for the instance.
     *
     * @return \As247\WpEloquent\Contracts\Support\MessageBag
     */
    public function getMessageBag();
}
