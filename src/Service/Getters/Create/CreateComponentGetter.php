<?php
namespace MasterAWSS\LivewireCrudGenerator\Service\Getters\Create;
use MasterAWSS\LivewireCrudGenerator\Service\BaseGenerator;
class CreateComponentGetter extends BaseGenerator{
    public function __construct($name, $options){
        parent::__construct($name, $options);
    }
    public function getComponentPaths(){
        return [
            [
                'template_path' => 'component/Create',
                'destination_path' =>  $this->getControllerPath($this->name.'\Create.php'),
            ],
        ];
    }
    public function getComponentTemplateVariables(){
        $variables = [
            '{{namespace}}'                 => $this->getNamespace(),
            '{{model_namespace}}'           => $this->getModelNamespace(),
            '{{view_helper_path}}'          => $this->getViewHelperPath('create'),
            '{{model}}'                     => $this->name,
            '{{variable_model}}'            => $this->getVariableModel($this->name),
            '{{foreigns_variable_declare}}' => $this->getForeignsVariableDeclare(),
            '{{foreigns_variable_mount}}'   => $this->getForeignsVariableMount(),
            '{{rules}}'                     => $this->getRules(),
        ];
        return $variables;
    }
    public function getRules(){
        $rules = '';
        // dd($this->getColumnsFromModel());
        foreach ($this->getColumnsFromModel() as $column) {
            $rules .= $this->tab(3)."'form.{$column['field']}' => '";

            if($column['is_null'])
                 $rules .= 'nullable|';
            else $rules .= 'required|';

            if($column['is_numeric']) $rules .= 'numeric';
            if($column['is_date']) $rules .= 'date';

            $rules = rtrim($rules, '|')."";
            $rules .= "', \n";
            // TODO: Is unique rule
        }
        return $rules;
    }
    public function getForeignsVariableDeclare (){
        $foreigns = $this->getRelationsFromModel();
        $value = '';
        if(!empty($foreigns)){
            $value = $this->tab(1).'public ';
            foreach($foreigns as $foreign)
                $value .= '$'.$foreign['method'].'s, ';
            $value = \Str::finish(rtrim($value, ", "), ';');
        }
        return $value;
    }
    public function getForeignsVariableMount (){
        $value = '';
        $foreigns = $this->getRelationsFromModel();
        foreach($foreigns as $foreign)
            $value .= $this->tab(2).'$this->'.$foreign['method']."s = \\{$foreign['model']}::get(); \n";
        return $value;
    }
}
