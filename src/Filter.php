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
        $this->filterQuery();
        $this->sortQuery();
        return $this->builder;
    }

    private function filterQuery()
    {
        foreach ($this->filterMethods as $method) {
            if (!$this->request->filled($method)) continue;
            $this->builder = $this->$method($this->builder, $this->request->$method);
        }
    }

    private function sortQuery()
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
        if (str_contains($this->request->orderBy, ':')) [$name, $direction] = explode(':', $this->request->orderBy);
        else [$name, $direction] = [$this->request->orderBy, 'asc'];
        $this->orderByParams[$name] = $direction;
    }

    final function orderBy() {
        // This method can't be used by the child class because it's part of the orderBy methods detection convention.
    }
}
