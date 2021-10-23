# Livewire crud generator for Laravel
## Una librería para no perder el tiempo en esos cruds de siempre


[![Build Status](https://app.travis-ci.com/masterawss/livewire_crud_generator.svg?branch=main)](https://app.travis-ci.com/masterawss/livewire_crud_generator)

Especifica el modelo y la librería detectará la tabla, fillables, relaciones, etc. para generar los siguientes archivos:

- Views:
-- create
-- show
-- view
- Livewire file
-- validation rules
-- pagination filters
-- crud functions


## Requerimientos
- Boostrap 5

## Instalación

Instala la librería por composer.

```sh
composer require master_awss/livewire_crud_generator
```
## Generar

Para generar el crud:

### Crud en un solo archivo Livewire:

```sh
lw:crud [ModelName] --type=crud-merged
```
O simplemente dejarlo así:
```sh
lw:crud [ModelName]
```
ya que se trata de un parámetro por defecto.
Esto generará un solo archivo con TODAS las funcionalidades de INDEX, SHOW, CREATE, EDIT, DELETE. Las vistas se incluirán como modals en la vista principal index.
### Crud en archivos separados Livewire:
```sh
lw:crud [ModelName] --type=crud-splited
```
Esto generará un crud con los siguientes componentes separados:  INDEX, SHOW, CREATE, EDIT, DELETE. El sistema de vistas ya no se desarrollará con modals, en cambio serán por redireccionamiento, por lo que deberá especificar cada componente en routes/web.php. (La funcionalidad DELETE se econtrará ubicada en el componente SHOW)

### Solo el componente Index:
```sh
lw:crud [ModelName] --type=index
```
Generará solo el componente INDEX e insertará filtros de búsqueda con eloquent
### Solo el componente Create:
```sh
lw:crud [ModelName] --type=create
```
Generará solo el componente CREATE e insertará las reglas automáticamente de acuerdo al modelo
### Solo el componente Show:
```sh
lw:crud [ModelName] --type=show
```
Generará solo el componente SHOW
## Opciones

Puedes añadir opciones al comando

| Opción | Descripción |
| ------ | ------ |
| --s=[sub_folder] | Si necesitas que los archivos se generen en subdirectorios, añade la opción y escribe el subfolder (Preferiblemente en snake case). La vista se generará en snake case: /views/livewire/[sub_folder]/ ... Y el controlador Livewire en camel case: Http/Livewire/[SubFolder]/ |

## License

MIT

**Free Software, Hell Yeah!**

[//]: # (These are reference links used in the body of this note and get stripped out when the markdown processor does its job. There is no need to format nicely because it shouldn't be seen. Thanks SO - http://stackoverflow.com/questions/4823468/store-comments-in-markdown-syntax)

   [dill]: <https://github.com/joemccann/dillinger>
   [git-repo-url]: <https://github.com/joemccann/dillinger.git>
   [john gruber]: <http://daringfireball.net>
   [df1]: <http://daringfireball.net/projects/markdown/>
   [markdown-it]: <https://github.com/markdown-it/markdown-it>
   [Ace Editor]: <http://ace.ajax.org>
   [node.js]: <http://nodejs.org>
   [Twitter Bootstrap]: <http://twitter.github.com/bootstrap/>
   [jQuery]: <http://jquery.com>
   [@tjholowaychuk]: <http://twitter.com/tjholowaychuk>
   [express]: <http://expressjs.com>
   [AngularJS]: <http://angularjs.org>
   [Gulp]: <http://gulpjs.com>

   [PlDb]: <https://github.com/joemccann/dillinger/tree/master/plugins/dropbox/README.md>
   [PlGh]: <https://github.com/joemccann/dillinger/tree/master/plugins/github/README.md>
   [PlGd]: <https://github.com/joemccann/dillinger/tree/master/plugins/googledrive/README.md>
   [PlOd]: <https://github.com/joemccann/dillinger/tree/master/plugins/onedrive/README.md>
   [PlMe]: <https://github.com/joemccann/dillinger/tree/master/plugins/medium/README.md>
   [PlGa]: <https://github.com/RahulHP/dillinger/blob/master/plugins/googleanalytics/README.md>
