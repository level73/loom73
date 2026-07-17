# Loom73 Shuttle

Shuttle is the Loom73 CLI tool for quick ops on the basic blueprint. 

It supports two commands, at this time.

→ `php shuttle loom73.info` - this returns the versions of Loom73 and Shuttle

→ `php shuttle user.admin {username} {email} {password}` - creates a new Admin user in the system

## Creating new commands
Add a new file in the `commands` directory. 

The naming convention is important to automate things without tinkering too much.

The file should be named as the command you want to execute. Multiple words need to be dot-separated. 

Inside the file, define a class with a name that is a Capitalized version of the filename, without the dot.

> **Example**  
> `database.update.php` needs to contain a class named `DatabaseUpdate`

The class should be namespaced under `Loom73\Shuttle`. It does not *need* to extend the main `Shuttle` class.

Inside the class, define the constructor and have it contain all the logic of your command. 

## The CLI class

We integrated a fun little class to manage colorization of the CLI output and support conditional questions. 
To use these functions, create a `$cli` variable to hold the object, then you can access the methods. 

### Example
````php
$cli = new CLI(); 
echo $cli->cout_color("Hey there, I'm going to be red!", "red"); 
if($cli->confirm("Are you sure you want to do this?")): echo "it is true"; endif;
````