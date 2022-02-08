<?php

namespace XiDanko\QueryFilter;

use Illuminate\Database\Eloquent\Builder;

trait HasFilter
{
    public function scopeUseFilter(Builder $builder, Filter $filter): Builder
    {
        return $filter->apply($builder);
    }
}
