<?php
namespace MasterAWSS\LivewireCrudGenerator\Service;

use ErrorException;
use Exception;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Filesystem\Filesystem;
use ReflectionClass;
use ReflectionMethod;
use Illuminate\Console\Command;

class BaseGenerator{
    public $name;
    public $variable_model;
    public $options;
    public $subfolder;
    public $type;
    public $mode;

    public function __construct($name, $options)
    {
        $this->name = $name;
        $this->options = $options;
        $this->subfolder = $options['subfolder'];
        $this->type = $options['type'];
        $this->mode = $options['mode'];

        $this->variable_model = $this->camelToSnake($name);

        try {
            $this->getModelInstance();
        } catch (\Throwable $th) {
            return 'No existe el modelo';
        }
    }
    public function getColumnsFromModel(){
        $model = $this->getModelInstance();
        $columns = [];
        $columns_db = \DB::select('describe '.$model->getTable());
        $fillables = $model->getFillable();
        foreach ($columns_db as $column) {
            if( in_array($column->Field, $fillables)){
                $columns[] = [
                    'field'     => $column->Field,
                    'type'      => $column->Type,
                    'title'     => str_replace('_', ' ',$column->Field),
                    'is_null'   => $column->Null == 'YES',
                    'label'     => str_replace('_', ' ', ucfirst(str_replace('_id', '',$column->Field))),
                    'is_numeric'=> $this->isNumeric($column->Type),
                    'is_date'   => $this->isDate($column->Type),
                    'is_unique' => $this->isUnique($column->Type),
                    'foreign_status' => $this->getForeignStatus($column->Field),
                ];
            }
        }
        return $columns;
    }
    public function isNumeric($type){
        return strpos($type, 'int') !== false || strpos($type, 'decimal') !== false;
    }
    public function isDate($type){
        return strpos($type, 'date')  !== false;
    }
    public function isUnique($type){
        return strpos($type, 'unique')  !== false;
    }
    public function getForeignStatus($field){
        $foreigns = $this->getRelationsFromModel();
        foreach ($foreigns as $method => $foreign) {
            if($foreign['foreign_key'] == $field)
                return array_merge($foreign, ['is_foreign' => true]);
        }
        return [ 'is_foreign' => false ];
    }
    public function getObjectVariable($column){
        dd($column);
        return \Str::start(\Str::finish(str_replace('_id', '',$column['field']), 's'), '$');
    }
    public function getModelInstance(){
        $model_str = "App\\Models\\".$this->name;

        $filesystem = new Filesystem;
        $model = new $model_str;
        return $model;
    }
    public function getModelNamespace(){
        return get_class($this->getModelInstance());
    }
    public function getResourcePath($file_name){
        $path = $this->getViewPath($file_name);
        return resource_path($path);
    }
    public function getViewPath($file_name){
        $path = '/views/livewire/';
        if($this->subfolder) $path .= $this->subfolder.'/';
        $path .= $this->variable_model.'/'.\Str::snake($file_name);
        return $path;
    }
    public function getViewHelperPath($file_name){
        return str_replace('/', '.', str_replace('/views/', '', $this->getViewPath($file_name)));
    }
    public function getControllerPath($file_name){
        $path = '/app/Http/Livewire/';
        if($this->subfolder) $path .= ucfirst(\Str::camel($this->subfolder)).'/';
        $path .= $file_name;
        return base_path($path);
    }
    public function getTableName(){
        return $this->getModelInstance()->getTable();
    }
    public function getTitle(){
        $table_name = $this->getTableName();
        return ucfirst(str_replace("_", ' ',$table_name));
    }
    public function makeDirectory($path)
    {
        $filesystem = new Filesystem;
        if (!$filesystem->isDirectory(dirname($path))) {
            $filesystem->makeDirectory(dirname($path), 0755, true, true);
        }

        return $path;
    }
    public function getRelationsFromModel(){
        $model = $this->getModelInstance();
        $relationships = [];
        foreach((new ReflectionClass($model))->getMethods(ReflectionMethod::IS_PUBLIC) as $method)
        {
            if ($method->class != get_class($model) ||
                !empty($method->getParameters()) ||
                $method->getName() == __FUNCTION__) {
                continue;
            }

            try {
                $return = $method->invoke($model);
                if ($return instanceof Relation) {
                    $relationships[$method->getName()] = [
                        'type'          => (new ReflectionClass($return))->getShortName(),
                        'model'         => (new ReflectionClass($return->getRelated()))->getName(),
                        'foreign_key'   => (new ReflectionClass($return))->hasMethod('getForeignKey')
                                            ? $return->getForeignKey()
                                            : $return->getForeignKeyName(),
                        'method'        => $method->getName()
                        // 'ownerKey' => $ownerKey,
                    ];
                }
            } catch(ErrorException $e) {}
        }
        return $relationships;
    }

    public function setFileTemplate($template_variables, $stub_path, $destination_path){
        $viewTemplate = str_replace(
            array_keys($template_variables),
            array_values($template_variables),
            $this->getStubTemplate($stub_path)
        );
        // dd($viewTemplate);
        $path = $this->makeDirectory($destination_path);
        // dd($path);
        $this->setFile($path, $viewTemplate);
    }

    public function setFile($path, $content){
        $filesystem = new Filesystem;
        $filesystem->put($path, $content);
    }
    public function getStubTemplate($type, $content = true)
    {
        $stub_path = config('livewire-crud.stub_path', 'default');
        if ($stub_path == 'default') {
            $stub_path = __DIR__ . '/../Templates/';
        }

        $path = \Str::finish($stub_path, '/') . "{$type}.stub";

        if (!$content) {
            return $path;
        }

        $filesystem = new Filesystem;
        return $filesystem->get($path);
    }
    public function tab($count){
        $tab  = '';
        for ($i=0; $i < $count; $i++)
            $tab.="\t";
        return $tab;
    }
    public function getRouteSugerence(){
        return "Route::get('/{$this->variable_model}', {$this->name}s::class)->name('{$this->variable_model}.index');";
    }

    function camelToSnake($input)
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
    }

    public function getCreateModalName(){

    }
    public function getRoute($type){
        return "route('".$this->getRouteName($type)."')";
    }
    public function getRouteName($type){
        $path = '';
        if($this->subfolder) $path .= $this->subfolder.'/';
        $path .= $this->variable_model;
        return "$path.$type";
    }
    // private function getCamelName($input)
    // {
    //     return $this->camelToSnake($input);
    // }

    public function getNamespace(){
        $namespace = 'App\Http\Livewire';
        if($this->subfolder){
            $subfolder = str_replace(' ', '', ucwords(str_replace("_", " ", $this->subfolder)));
            return "$namespace"."\\".$subfolder."\\".$this->name;
        }else{
            return "$namespace"."\\".$this->name;
        }
    }
    protected function getRowVariable($field){
        if($field['foreign_status']['is_foreign']){
            return "optional(".'$row->'."{$field['foreign_status']['method']})->descripcion";
        }
        if($field['is_date'])
            return "optional(".'$row->'."{$field['field']})->format('Y-m-d')";
        return '$row->'.$field['field'];
    }
    public function getVariableModel(){
        return $this->camelToSnake($this->name);
    }
}
