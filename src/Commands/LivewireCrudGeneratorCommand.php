<?php

namespace MasterAWSS\LivewireCrudGenerator\Commands;

use Illuminate\Console\Command;
use MasterAWSS\LivewireCrudGenerator\Service\BaseGenerator;
use MasterAWSS\LivewireCrudGenerator\Service\ControllerGenerator;
use MasterAWSS\LivewireCrudGenerator\Service\ViewGenerator;

class LivewireCrudGeneratorCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lwcrud:generate {name} {--s=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */

    protected BaseGenerator $base;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->base = new BaseGenerator($this->argument('name'), $this->options());
        $this->generateViews();
        $this->generateController();

        $this->info('No olvides agregar la ruta');
        $this->warn("Sugerencia: <info> {$this->base->getRouteSugerence()} </info>");
    }

    private function generateViews(){
        $this->warn('Creando :<info> Views ...</info>');
        $view_generator = new ViewGenerator($this->base);
        $folder = $view_generator->buildViews();
        $this->info('Views generados con éxito en: '.$folder);
        $this->newLine();
    }
    private function generateController(){
        $this->warn('Creando :<info> Controlador ...</info>');
        $view_generator = new ControllerGenerator($this->base);
        $file = $view_generator->buildFile();
        $this->info('Controlador generado con éxito en: '.$file);
        $this->newLine();
    }
}
