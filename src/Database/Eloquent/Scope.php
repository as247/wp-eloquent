<?php

namespace As247\WpEloquent\Database\Eloquent;

interface Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \As247\WpEloquent\Database\Eloquent\Builder  $builder
     * @param  \As247\WpEloquent\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model);
}
