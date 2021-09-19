<?php
namespace MasterAWSS\LivewireCrudGenerator\Service;

use ErrorException;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Filesystem\Filesystem;
use ReflectionClass;
use ReflectionMethod;

class ControllerGenerator {
    private BaseGenerator $base;
    private $model_name;
    public function __construct(BaseGenerator $base)
    {
        $this->base = $base;
        $this->model_name = $this->base->name.'s';
    }
    public function buildFile(){
        $file_path = $this->base->getControllerPath($this->model_name.'.php');
        $this->base->setFileTemplate(
            $this->generateTemplateVariables(),
            'livewire/LivewireModel',
            $file_path
        );
        return $file_path;
    }

    public function generateTemplateVariables(){
        return [
            '{{namespace}}'             => $this->getNamespace(),
            '{{model_namespace}}'       => $this->base->getModelNamespace(),
            '{{rules}}'                 => $this->getRules(),
            '{{view_component_path}}'   => $this->getViewPath(),
            '{{model}}'                 => $this->base->name,
            '{{eloquent_filter}}'       => $this->getEloquentFilter(),
            '{{model_lw_name}}'         => $this->model_name,
        ];
    }
    public function getViewPath(){
        $path = "livewire.";
        if($this->base->subfolder)
            $path .= "{$this->base->subfolder}.";
        $path .= strtolower($this->base->name).".view";
        return $path;
    }
    public function getEloquentFilter(){
        $filter = '';
        foreach ($this->base->getColumnsFromModel() as $column) {
            if($column['is_foreign']){
                $filter .= $this->base->tab(3).'->orWhereHas("'.$column['belongs_to_function'].'", function($q) use ($search){
                    return $q->where("id", "LIKE", $search);
                    // ->orWhere("", "LIKE", $search);
                })';
            }else{
                $filter .= $this->base->tab(3).'->orWhere("'.$column['field'].'", "LIKE", $search)';
            }
            $filter .= "\n";
        }

        return $filter;
    }

    public function getNamespace(){
        $namespace = 'App\Http\Livewire';
        if($this->base->subfolder){
            $subfolder = str_replace(' ', '', ucwords(str_replace("_", " ", $this->base->subfolder)));
            return "App\Http\Livewire\\".$subfolder;
        }
        return $namespace;
    }
    public function getRules(){
        $rules = '';
        // dd($this->base->getColumnsFromModel());
        foreach ($this->base->getColumnsFromModel() as $column) {
            $rules .= $this->base->tab(3)."'form.{$column['field']}' => '";

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
}
