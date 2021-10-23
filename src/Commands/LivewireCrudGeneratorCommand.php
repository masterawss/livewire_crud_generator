<?php

namespace MasterAWSS\LivewireCrudGenerator\Commands;

use Illuminate\Console\Command;
use MasterAWSS\LivewireCrudGenerator\Service\BaseGenerator;
use MasterAWSS\LivewireCrudGenerator\Service\Builder;
use MasterAWSS\LivewireCrudGenerator\Service\ControllerGenerator;
use MasterAWSS\LivewireCrudGenerator\Service\ViewGenerator;

class LivewireCrudGeneratorCommand extends Builder
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lw:crud {name} {-s|--subfolder=} {-t|--type=crud-merged} {-m|--mode=page}';

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
        $this->build();
    }
}
