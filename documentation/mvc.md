# Loom73 MVC

## Routing
The basics are extremely straightforward. 
- The first URL fragment routes to your controller.
- The second one is your chosen method.
- All other fragments are parameters fed to your method. 

Note that for routing, you do not need to respect the uppercase/lowercase of the controller names, or the dashes in the methods. These get automatically cleaned up and optimized by the `toMachine()` function in `/lib/functions.php`.

So a route like `example.com\the-fancy-pants\more-pants` can be used. It would route to controller `TheFancyPants` and execute method `more_pants()`.

The base Controller Class, `Loom73\Woodframe\Ctrl` located in `/lib/Ctrl.class.php` takes care of passing data over to the template parts.

The `Loom73\Woodframe\Template` Class in `/lib/Template.class.php` assembles the view. You rarely need to fiddle with this class.  

The default Controller is `MainCtrl`, with one method, `index`. 

Every Controller needs to have a Model. The model needs to extend the core `Loom73\Beam\Model` abstract class. It must also define 3 protected properties:
- `protected string $table` a table name, can be null
- `protected string $pkey` the primary key of table, can be null
- `protected bool $dates` a true/false flag to signal existence of `created_at` and `modified_at` fields.

The Model will be automatically instantiated in the Controller under property `$this->_model`.

All views are located in `/application/views/`. A directory should be created there to hold all relevant templates, named with the same name of the controller, minus the `Ctrl` particle, in lowercase. For example: 

`MainCtrl → /application/views/main/`

Within this directory one can add all necessary views, named with the same name as the method that needs to be executed. For example: 
`MainCtrl→index()` → `/application/views/main/index.php`

