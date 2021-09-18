<?php
namespace MasterAWSS\LivewireCrudGenerator\Service;

use ErrorException;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Filesystem\Filesystem;
use ReflectionClass;
use ReflectionMethod;

class ViewGenerator {
    private BaseGenerator $base;
    public function __construct(BaseGenerator $base)
    {
        $this->base = $base;
    }

    public function buildViews(){
        // $this->warn('Creando :<info> Views ...</info>');

        $template_variables = $this->generateTemplateVariables();

        $template_paths = [
            [
                'template_path' => 'views/view',
                'destination_path' =>  $this->base->getResourcePath('view.blade.php'),
            ],
            [
                'template_path' => 'views/create',
                'destination_path' =>  $this->base->getResourcePath('create.blade.php'),
            ],
            [
                'template_path' => 'views/show',
                'destination_path' =>  $this->base->getResourcePath('show.blade.php'),
            ],
        ];

        foreach ($template_paths as $template_path) {
            $this->base->setFileTemplate(
                $this->generateTemplateVariables(),
                $template_path['template_path'],
                $template_path['destination_path']
            );
        }
        return $this->base->getResourcePath('');
    }


    private function generateTemplateVariables(){
        return [
            '{{t_head}}'    => $this->getTHead(),
            '{{td_body}}'   => $this->getTDBody(),
            '{{title}}'     => $this->base->getTitle(),
            '{{component_create_path}}' => $this->getComponentCreatePath(),
            '{{component_show_path}}' => $this->getComponentShowPath(),
            '{{create_form}}'=> $this->getCreateForm(),
            '{{list_show}}'=> $this->getListShow()
        ];
    }
    private function getTHead(){
        $columns = $this->base->getColumnsFromModel();
        $thead = "<th>#</th>\n";
        foreach ($columns as $column)
            $thead .= $this->base->tab(9)."<th>".$column['label']."</th> \n";

        $thead.= $this->base->tab(9).'<th></th>';
        $thead = <<<HTML
            <thead>
                                        <tr>
                                            {$thead}
                                        </tr>
                                    </thead>
        HTML;
        return $thead;
    }
    private function getTDBody(){
        $columns = $this->base->getColumnsFromModel();
        $tbody = '';
        foreach ($columns as $column)
            $tbody .= "<td>{{ ".$this->getRowVariable($column['field'])." }}</td> \n".$this->base->tab(9);

        return $tbody;
    }
    public function getRowVariable($field){
        if($this->base->isForeignInput($field)){
            $d = substr($field, 0, -3);
            return "optional(".'$row->'."{$d})->descripcion";
        }
        return '$row->'.$field;
    }

    private function getComponentCreatePath(){
        $path = 'livewire.';
        if($this->base->subfolder) $path .= $this->base->subfolder.'.';
        $path .= $this->base->camel_name.'.create';
        return $path;
    }
    private function getComponentShowPath(){
        $path = 'livewire.';
        if($this->base->subfolder) $path .= $this->base->subfolder.'.';
        $path .= $this->base->camel_name.'.show';
        return $path;
    }
    private function getCreateForm(){
        $columns = $this->base->getColumnsFromModel();

        $viewTemplate = '';
        foreach ($columns as $col) {
            $variables = [
                '{{model}}'     => $col['field'],
                '{{label}}'     => $col['label'],
                '{{type}}'      => $this->getTypeInput($col),
                '{{extra}}'     => $this->getExtraInput($col),
                '{{object_variable}}'    => $this->base->getObjectVariable($col),
            ];

            if($col['is_foreign'])  $stub = 'views/components/select-field';
            else                    $stub = 'views/components/input-field';

            $viewTemplate .= str_replace(
                array_keys($variables),
                array_values($variables),
                $this->base->getStubTemplate($stub)
            );
        }
        return $viewTemplate;
    }
    private function getListShow(){
        $columns = $this->base->getColumnsFromModel();
        $viewTemplate = '';
        foreach ($columns as $col) {
            $variables = [
                '{{label}}'     => $col['label'],
                '{{value}}'     => str_replace('$row', '$record', $this->getRowVariable($col['field'])),
            ];

            $viewTemplate .= $this->base->tab(6).str_replace(
                array_keys($variables),
                array_values($variables),
                $this->base->getStubTemplate('views/components/simple-list')
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

}
