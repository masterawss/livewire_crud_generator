<?php
namespace MasterAWSS\LivewireCrudGenerator\Service\Getters\Show;

use MasterAWSS\LivewireCrudGenerator\Service\BaseGenerator;

class ShowViewGetter extends BaseGenerator{
    public function __construct($name, $options){
        parent::__construct($name, $options);
    }
    public function getTemplatePaths(){
        return [
            [
                'template_path' => 'views/components/modal_show',
                'destination_path' =>  $this->getResourcePath('show.blade.php'),
            ],
        ];
    }
    public function getViewTemplateVariables(){
        $variables = [
            '{{section_show}}'              => $this->getSection(),
            '{{model}}'                     => $this->name,
            '{{variable_model}}'            => $this->getVariableModel(),
        ];
        return $variables;
    }
    public function getListShow(){
        $columns = $this->getColumnsFromModel();
        $viewTemplate = '';
        foreach ($columns as $col) {
            $variables = [
                '{{label}}'     => $col['label'],
                '{{value}}'     => str_replace('$row', "$".$this->getVariableModel(), $this->getRowVariable($col)),
            ];

            $viewTemplate .= $this->tab(6).str_replace(
                array_keys($variables),
                array_values($variables),
                $this->getStubTemplate('views/components/simple-list')
            );
        }
        return $viewTemplate;
    }
    public function getSection(){
        $variables = [
            '{{list_show}}' => $this->getListShow(),
            '{{variable_model}}' => $this->variable_model,
        ];
        return str_replace(
            array_keys($variables),
            array_values($variables),
            $this->getStubTemplate('views/components/section-show')
        );
    }
}


