<?php
namespace MasterAWSS\LivewireCrudGenerator\Service;

use Illuminate\Console\Command;
use MasterAWSS\LivewireCrudGenerator\Service\Generators\Create\CreateComponentGenerator;
use MasterAWSS\LivewireCrudGenerator\Service\Generators\Create\CreateViewGenerator;
use MasterAWSS\LivewireCrudGenerator\Service\Generators\Index\IndexComponentGenerator;
use MasterAWSS\LivewireCrudGenerator\Service\Generators\Index\IndexViewGenerator;
use MasterAWSS\LivewireCrudGenerator\Service\Generators\Show\ShowComponentGenerator;
use MasterAWSS\LivewireCrudGenerator\Service\Generators\Show\ShowViewGenerator;

class Builder extends Command {
    public function __construct()
    {
        parent::__construct();
    }
    public function build(){
        $name = $this->argument('name');
        $options = $this->options();

        $view = null;
        $component = null;
        switch ($this->option('type')) {
            case 'crud-splited':
            case 'crud-merged':
            case 'index':
                $view = new IndexViewGenerator($name, $options);
                $component = new IndexComponentGenerator($name, $options);
                break;
            case 'create':
                $view = new CreateViewGenerator($name, $options);
                $component = new CreateComponentGenerator($name, $options);
                break;
            case 'show':
                $view = new ShowViewGenerator($name, $options);
                $component = new ShowComponentGenerator($name, $options);
                break;
            default:
                $this->error("No existe la opción {$this->option('type')} para la opción type");
                break;
        }
        $view->build();
        foreach ($view->getTemplatePaths() as $path)
            $this->info('Se creó la vista: '. $path['destination_path']);
        $component->build();
        foreach ($component->getComponentPaths() as $path)
            $this->info('Se creó el componente: '. $path['destination_path']);
    }
}
