<?php
namespace MasterAWSS\LivewireCrudGenerator\Service\Getters\Create;

use MasterAWSS\LivewireCrudGenerator\Service\BaseGenerator;

class CreateViewGetter extends BaseGenerator{
    public function __construct($name, $options){
        parent::__construct($name, $options);
    }
    public function getTemplatePaths(){
        $variables = [
            [
                'template_path' => 'views/components/modal_create',
                'destination_path' =>  $this->getResourcePath('create.blade.php'),
            ]
        ];
        return $variables;
    }
    protected function getViewTemplateVariables(){
        $variables = [
            '{{model}}'                 => $this->name,
            '{{create_form}}'           => $this->getCreateForm(),
            '{{list_show}}'             => $this->getListShow(),
            '{{variable_model}}'        => $this->variable_model,
            '{{section_create}}'        => $this->getSection(),
        ];
        return $variables;
    }
    public function getCreateForm(){
        $columns = $this->getColumnsFromModel();

        $viewTemplate = '';
        foreach ($columns as $col) {
            $variables = [
                '{{model}}'     => $col['field'],
                '{{label}}'     => $col['label'],
                '{{type}}'      => $this->getTypeInput($col),
                '{{extra}}'     => $this->getExtraInput($col),
            ];

            if($col['foreign_status']['is_foreign']){
                $variables = array_merge($variables, [
                    '{{object_variable}}' => '$'.$col['foreign_status']['method'].'s'
                ]);
                $stub = 'views/components/select-field';
            }
            else $stub = 'views/components/input-field';

            $viewTemplate .= str_replace(
                array_keys($variables),
                array_values($variables),
                $this->getStubTemplate($stub)
            );
        }
        return $viewTemplate;
    }
    public function getListShow(){
        $columns = $this->getColumnsFromModel();
        $viewTemplate = '';
        foreach ($columns as $col) {
            $variables = [
                '{{label}}'     => $col['label'],
                '{{value}}'     => str_replace('$row', '$'.$this->getVariableModel(), $this->getRowVariable($col)),
            ];

            $viewTemplate .= $this->tab(6).str_replace(
                array_keys($variables),
                array_values($variables),
                $this->getStubTemplate('views/components/simple-list')
            );
        }
        return $viewTemplate;
    }
    private function getTypeInput($col){
        if(strpos($col['type'], 'int'))
            return 'number';
        if(strpos($col['type'], 'decimal'))
            return 'number';
        if(strpos($col['type'], 'date'))
            return 'date';
        return 'text';
    }
    private function getExtraInput($col){
        $extra = '';

        if(!$col['is_null']) $extra .= " required ";

        if(strpos($col['type'], 'int'))
            $extra .= "step='1'";
        if(strpos($col['type'], 'decimal'))
            $extra .= "step='0.01'";
        return $extra;
    }
    public function getSection(){
        $variables = [
            '{{create_form}}' => $this->getCreateForm()
        ];
        return str_replace(
            array_keys($variables),
            array_values($variables),
            $this->getStubTemplate('views/components/section-create')
        );
    }
}


