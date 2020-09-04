<?php

declare(strict_types=1);

namespace As247\WpEloquent\Doctrine\Inflector;

interface WordInflector
{
    public function inflect(string $word) : string;
}
