<?php
namespace MasterAWSS\LivewireCrudGenerator\Service\Getters\Show;
use MasterAWSS\LivewireCrudGenerator\Service\BaseGenerator;
class ShowComponentGetter extends BaseGenerator{
    public function __construct($name, $options){
        parent::__construct($name, $options);
    }
    public function getComponentPaths(){
        return [
            [
                'template_path' => 'component/Show',
                'destination_path' =>  $this->getControllerPath($this->name.'\Show.php'),
            ],
        ];
    }
    public function getComponentTemplateVariables(){
        $variables = [
            '{{namespace}}'                 => $this->getNamespace(),
            '{{model_namespace}}'           => $this->getModelNamespace(),
            '{{view_helper_path}}'          => $this->getViewHelperPath('show'),
            '{{model}}'                     => $this->name,
            '{{variable_model}}'            => $this->getVariableModel(),
        ];
        return $variables;
    }
}
