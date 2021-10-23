<?php
namespace MasterAWSS\LivewireCrudGenerator\Service\Getters\Index;

use MasterAWSS\LivewireCrudGenerator\Service\BaseGenerator;
use MasterAWSS\LivewireCrudGenerator\Service\Generators\Create\CreateViewGenerator;
use MasterAWSS\LivewireCrudGenerator\Service\Generators\Show\ShowViewGenerator;

class IndexViewGetter extends BaseGenerator{
    public function __construct($name, $options){
        parent::__construct($name, $options);
        // $this->base = $base;
    }
    public function getTemplatePaths(){
        $variables = [
            [
                'template_path' => 'views/index',
                'destination_path' =>  $this->getResourcePath('index.blade.php'),
            ]
        ];
        if($this->type == 'crud-merged'){
            $variables = array_merge($variables, [
                [
                    'template_path' => 'views/components/modal_create',
                    'destination_path' =>  $this->getResourcePath('create.blade.php'),
                ],
                [
                    'template_path' => 'views/components/modal_show',
                    'destination_path' =>  $this->getResourcePath('show.blade.php'),
                ],
            ]);
        }
        return $variables;
    }
    protected function getViewTemplateVariables(){
        $variables = [
            '{{title}}'                 => $this->getTitle(),
            '{{head_actions}}'          => $this->getHeadActions(),
            '{{t_head}}'                => $this->getTHead(),
            '{{td_body}}'               => $this->getTDBody(),
            '{{td_action}}'             => $this->getTDAction(),
            '{{extra}}'                 => $this->getExtra(),

        ];
        if($this->type == 'crud-merged'){
            $createView = new CreateViewGenerator($this->name, $this->options);
            $showView = new ShowViewGenerator($this->name, $this->options);
            $variables = array_merge($variables, [
                '{{model}}'                 => $this->name,
                '{{create_form}}'           => $createView->getCreateForm(),
                '{{list_show}}'             => $createView->getListShow(),
                '{{variable_model}}'        => $this->variable_model,
                '{{section_create}}'        => $createView->getSection(),
                '{{section_show}}'          => $showView->getSection() ,
            ]);
        }
        return $variables;
    }

    protected function getHeadActions(){
        if($this->type == 'crud-merged'){
            $variables = [
                '{{create_modal_name}}' => $this->getCreateModalName(),
            ];
            return str_replace(
                array_keys($variables),
                array_values($variables),
                $this->getStubTemplate('views/components/btn-add-modal')
            );
        }else{
            $variables = [
                '{{create_route}}' => $this->getRoute('create'),
            ];
            return str_replace(
                array_keys($variables),
                array_values($variables),
                $this->getStubTemplate('views/components/btn-add-redirect')
            );
        }
    }
    protected function getExtra(){
        if($this->type == 'crud-merged'){
            $variables = [
                '{{component_create_path}}' => $this->getComponentViewPath('create'),
                '{{component_show_path}}' => $this->getComponentViewPath('show'),
            ];
            return str_replace(
                array_keys($variables),
                array_values($variables),
                $this->getStubTemplate('views/components/index_extra_modal_create_show')
            );
        }else{
            return "";
        }

    }

    protected function getTHead(){
        $columns = $this->getColumnsFromModel();
        $thead = "<th>#</th>\n";
        foreach ($columns as $column)
            $thead .= $this->tab(9)."<th>".$column['label']."</th> \n";

        $thead.= $this->tab(9).'<th></th>';
        $thead = <<<HTML
            <thead>
                                        <tr>
                                            {$thead}
                                        </tr>
                                    </thead>
        HTML;
        return $thead;
    }

    protected function getTDBody(){
        $columns = $this->getColumnsFromModel();
        $tbody = '';
        foreach ($columns as $column)
            $tbody .= "<td>{{ ".$this->getRowVariable($column)." }}</td> \n".$this->tab(9);

        return $tbody;
    }

    protected function getComponentViewPath($type){
        $path = 'livewire.';
        if($this->subfolder) $path .= $this->subfolder.'.';
        $path .= "$this->variable_model.$type";
        return $path;
    }

    private function getTDAction(){
        if($this->type == 'crud-merged'){
            $variables = [
                '{{model}}' => $this->name,
            ];
            return str_replace(
                array_keys($variables),
                array_values($variables),
                $this->getStubTemplate('views/components/td_action_modal')
            );
        }else{
            $variables = [
                '{{route_name_show}}' => $this->getRouteName('show'),
                '{{route_name_edit}}' => $this->getRouteName('edit'),
            ];
            return str_replace(
                array_keys($variables),
                array_values($variables),
                $this->getStubTemplate('views/components/td_action_redirect')
            );
        }
    }
}
