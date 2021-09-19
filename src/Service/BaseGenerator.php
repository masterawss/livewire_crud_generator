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
    public $camel_name;
    public $options;
    public $subfolder;
    public function __construct($name, $options)
    {
        $this->name = $name;
        $this->options = $options;
        $this->subfolder = $options['s'];

        $this->camel_name = $this->camelToSnake($name);

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
                    'is_foreign'=> $this->isForeignInput($column->Field),
                    'is_numeric'=> $this->isNumeric($column->Type),
                    'is_date'   => $this->isDate($column->Type),
                    'is_unique' => $this->isUnique($column->Type),
                    'belongs_to_function' => str_replace('_id', '',$column->Field),
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
    public function isForeignInput($field){
        return substr($field, -3) == '_id';
    }
    public function getObjectVariable($column){
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
        $path .= $this->camel_name.'/'.$file_name;
        return $path;
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
                        'type' => (new ReflectionClass($return))->getShortName(),
                        'model' => (new ReflectionClass($return->getRelated()))->getName()
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
        // dd($resource_path);
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
        return "Route::get('/{$this->camel_name}', {$this->name}s::class)->name('{$this->camel_name}.index');";
    }

    function camelToSnake($input)
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
    }
    // private function getCamelName($input)
    // {
    //     return $this->camelToSnake($input);
    // }
}
