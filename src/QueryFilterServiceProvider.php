<?php

namespace XiDanko\QueryFilter;

use Illuminate\Support\ServiceProvider;
use XiDanko\QueryFilter\CreateFilterCommand;

class QueryFilterServiceProvider extends ServiceProvider
{
    public function register()
    {
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateFilterCommand::class,
            ]);
        }
    }
}
