<?php

namespace XiDanko\QueryFilter;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;

abstract class Filter
{
    protected Builder $builder;
    protected Request $request;
    private array $filterMethods = [];
    private array $orderByMethods = [];
    private array $orderByParams = [];

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->analyseChildMethods();
        $this->parseOrderByParams();
    }

    public function apply(Builder $builder): Builder
    {
        $this->builder = $builder;
        $this->filter();
        $this->sort();
        return $this->builder;
    }

    private function filter()
    {
        foreach ($this->filterMethods as $method) {
            if (!$this->request->has($method)) continue;
            $this->builder = $this->$method($this->builder, $this->request->$method);
        }
    }

    private function sort()
    {
        foreach ($this->orderByMethods as $method) {
            $key = Str::of($method)->after('orderBy')->camel()->__toString();
            if (! array_key_exists($key, $this->orderByParams)) continue;
            $this->builder = $this->$method($this->builder, $this->orderByParams[$key]);
        }
    }

    private function analyseChildMethods()
    {
        $reflection = new ReflectionClass($this);
        $childMethods = array_filter($reflection->getMethods(), fn (ReflectionMethod $method) => $method->class === $reflection->name);
        $childMethods = array_map(fn ($method) => $method->name, $childMethods);
        $this->orderByMethods = array_filter($childMethods, fn ($method) => str_starts_with($method, 'orderBy'));
        $this->filterMethods = array_diff($childMethods, $this->orderByMethods);
    }

    private function parseOrderByParams()
    {
        if (! $this->request->filled('orderBy')) return;
        foreach ((array) $this->request->orderBy as $param) {
            if (str_contains($param, ':')) [$name, $direction] = explode(':', $param);
            else [$name, $direction] = [$param, 'asc'];
            $this->orderByParams[$name] = $direction;
        }
    }

    final function orderBy() {
        // This method can't be used by the child class because it's part of the orderBy methods detection convention.
    }
}
