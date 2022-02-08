<?php

namespace XiDanko\QueryFilter;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

abstract class Filter
{
    protected Builder $builder;
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    abstract protected function filters(): array;

    public function apply(Builder $builder): Builder
    {
        $this->builder = $builder;
        $this->filter();
        if ($this->request->filled('orderBy')) $this->sort();
        return $this->builder;
    }

    private function filter()
    {
        foreach ($this->filters() as $filter) {
            if ($filter === 'orderBy') throw new MethodNotAllowedException('The orderBy method is reserved by the master class.');
            if (!$this->request->has($filter)) continue;
            $this->builder = $this->$filter($this->builder, $this->request->$filter);
        }
    }

    private function sort()
    {
        $orderByParams = explode(',', $this->request->orderBy);
        foreach ($orderByParams as $param) {
            [$name, $value] = explode(':', $param);
            $method = Str::of($name)->studly()->prepend('orderBy')->__toString();
            $this->builder = $this->$method($this->builder, $value);
        }
    }
}
