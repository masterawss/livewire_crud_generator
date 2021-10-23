<?php
namespace MasterAWSS\LivewireCrudGenerator\Service\Generators\Create;

use MasterAWSS\LivewireCrudGenerator\Service\Getters\Create\CreateComponentGetter;

class CreateComponentGenerator extends CreateComponentGetter{
    public function __construct($name, $options){
        parent::__construct($name, $options);
    }
    public function build(){
        $template_paths = $this->getComponentPaths();

        foreach ($template_paths as $template_path) {
            $this->setFileTemplate(
                $this->getComponentTemplateVariables(),
                $template_path['template_path'],
                $template_path['destination_path']
            );
        }
    }
}
