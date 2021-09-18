<?php

namespace MasterAWSS\LivewireCrudGenerator;

use Illuminate\Support\ServiceProvider;
use MasterAWSS\LivewireCrudGenerator\Commands\LivewireCrudGeneratorCommand;

class LivewireCrudGeneratorServiceProvider extends ServiceProvider {

    public function boot(){
        if ($this->app->runningInConsole()) {
            $this->commands([
                LivewireCrudGeneratorCommand::class,
            ]);
        }
    }
}
