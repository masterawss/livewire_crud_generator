<?php
namespace MasterAWSS\LivewireCrudGenerator\Service\Getters\Index;
use MasterAWSS\LivewireCrudGenerator\Service\BaseGenerator;
use MasterAWSS\LivewireCrudGenerator\Service\Getters\Create\CreateComponentGetter;

class IndexComponentGetter extends BaseGenerator{
    public function __construct($name, $options){
        parent::__construct($name, $options);
    }
    public function getComponentPaths(){
        if($this->type == 'crud-merged') $template_path = 'component/IndexComplex';
        else $template_path = 'component/IndexSimple';

        return [
            [
                'template_path' => $template_path,
                'destination_path' =>  $this->getControllerPath($this->name.'\Index.php'),
            ],
        ];
    }
    public function getComponentTemplateVariables(){
        $variables = [
            '{{namespace}}'             => $this->getNamespace(),
            '{{model_namespace}}'       => $this->getModelNamespace(),
            '{{view_helper_path}}'      => $this->getViewHelperPath('index'),
            '{{model}}'                 => $this->name,
            '{{eloquent_filter}}'       => $this->getEloquentFilter(),
        ];
        $createComponent = new CreateComponentGetter($this->name, $this->options);
        if($this->type == 'crud-merged'){
            $variables = array_merge($variables, [
                '{{rules}}'                     => $createComponent->getRules(),
                '{{foreigns_variable_declare}}' => $createComponent->getForeignsVariableDeclare(),
                '{{foreigns_variable_mount}}'   => $createComponent->getForeignsVariableMount(),
                '{{variable_model}}'            => $this->getVariableModel($this->name),
            ]);
        }
        return $variables;
    }
    public function getEloquentFilter(){
        $columns = $this->getColumnsFromModel();
        foreach ($columns as $index => $column) {
            if ($index === array_key_first($columns))
                $filter = $this->tab(4).'return $q'."\n";


            if($column['foreign_status']['is_foreign']){
                $filter .= $this->tab(4).'->orWhereHas("'.$column['foreign_status']['method'].'", function($q) use ($search){
                    return $q->where("descripcion", "LIKE", $search);
                    // ->orWhere("", "LIKE", $search);
                })';
            }else{
                $filter .= $this->tab(4).'->orWhere("'.$column['field'].'", "LIKE", $search)';
            }
            if ($index === array_key_last($columns))
                $filter .= ";";
            $filter .= "\n";
        }
        return $filter;
    }



}
