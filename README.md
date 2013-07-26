S.Framework
===========

S.Framework is a framework for easily creation of web apps. It is based on [O.framework][] using the `O\ExtJS` plugin (so your views will be created using `ExtJS`).

This framework, differently from [O.framework][], is not that flexible, because it set up a lot of things for you (that you had to do manually using just [O.framework][]).


[O.framework]: https://github.com/venkon/o-framework

## An example
First of all, let's create a folder named `TestApp` inside your `$_SERVER['DOCUMENT_ROOT']` folder (you can create wherever you want, but in this example let assume you'll create it there). Inside this folder, create the following files and folders:

```
apis
  Ws1.php
libs
  sencha-extjs
    // ExtJS files here
systems
  Example1
    Menu1
      Controller.php
      Model.php
      Views
        Index.php
.htaccess
ApiLocator.php
app-config.php
app.php
SystemLocator.php
```

Let go through one by one. First, let's check the  `.htaccess`:

```
RewriteEngine on
RewriteBase /TestApp

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule .* app.php [L,QSA]
```

If you know the `.htaccess` sintax you'll see that we are redirecting every request to a single file (our app will be a single page app). Alright, the `.htaccess` is just for this.

Now the `app.php` file:

```php
<?php

// includes the S app file
include 'path/to/S/App.php';

// creates the App with the config
$app = new S\App(include 'app-config.php');

// run it
$app->run();
```

Very simple, as explained in the docs on the code, it creates the `S\App` class passing an array of configs, stored in the `app-config.php`. Of course you can name this config file as you want, `app-config.php` is the name I suggested :D.

Before showing you the config, let's see the two `Locator`s. Maybe you won't undertand them now, but just pay attention to what the code does, so when we get to the `Locator` point in the config, it will be clear what does it do.

`ApiLocator`:

```php
<?php

// use the LocatorInterface of O.framework
use \O\LocatorInterface;

/**
 * 
 * This class will be used to translate a parameter of the URL to a class (an API)
 * 
 */
class ApiLocator implements LocatorInterface
{

    /**
     * 
     * This property will store a pair of key and value, the key being the URL parameter and the value being the Api class
     * 
     * @var array
     * 
     * In this example, I've just created one element in this array, but you'll have to fill this array with all of your APIs
     */
    public static $locations = array(
        'ws1' => 'apis\\Ws1'
    );

    /**
     * 
     * Find the right API class by an URL parameter ($name)
     * 
     * @param string $name The URL parameter
     * 
     * @return must return an instance of an API
     */
    public function find($name)
    {
        if (isset(self::$locations[$name])) {
            $class = self::$locations[$name];
            return new $class;
        }
    }

}
```

This `ApiLocator` class must implement the `O\LocatorInterface` interface. This above implementation is my example implementation, you can do this your own way - you just need to implement the interface and return an API instance in the `find` method.

Now the `SystemLocator` class (it has the same objective, but instead of finding APIs it find systems controlllers):

```php
<?php

use \O\LocatorInterface;

class SystemLocator implements LocatorInterface
{

    private static $locations = array(
        '1' => 'systems\\Example1\\Menu1'
    );

    public function find($name)
    {
        if (isset(self::$locations[$name])) {
            $class = self::$locations[$name] . '\\Controller';
            return new $class;
        }
    }

}
```

I didn't commented this file because it follows the same logic as `ApiLocator`. The only difference is that, instead of returning an API instance in the `find` method, it returns and `Controller` instance.

Alright, now let's check the `app-config.php` file:

```php
<?php

return array(
    // App name and version
    'app' => array(
        'name' => 'Test App',
        'version' => '1.0'
    ),
    // Locale (en_US by default) - you can use pt_BR too
    'locale' => 'en_US',
    //
    // all paths needed by S framework
    'paths' => array(
        //
        // The V project files path
        'V' => 'path/to/V/',
        //
        // The O framework files path
        'O' => 'path/to/O/',
        //
        // Your app base path
        'base_path' => '/TestApp/'
    ),
    //
    // Auth (this example says you have to use user:admin password:admin to enter the systems and the APIs
    'auth' => array(
        // if you return false here, S will present the user with a login form
        // if you just return true always, you app will have no login form
        'systems' => function($user, $pass) {
            return $user == $pass && $pass == 'admin';
        },
        // protecting our APIs (via HTTP BASIC AUTH)
        'apis' => function($user, $pass) {
            return $user == $pass && $pass == 'admin';
        }
    ),
    //
    // Locators
    'locator' => array(
        'apis' => function() {
            return new ApiLocator();
        },
        'systems' => function() {
            return new SystemLocator();
        }
    ),
    // use EXTJS (in this example i'm using ExtJS 4.2.1)
    'UI' => array(
        'plugin' => 'ExtJS',
        // 
        // where ExtJS files are
        'paths' => array(
            'extjs' => '/TestApp/libs/sencha-extjs' // this folder contains all the ExtJS files
        )
    ),
    // use REST webservices
    'REST' => array(
        'base_url' => 'http://myurl.com/',
        'errors' => array(
            // example error handling
            '404' => function() {
                echo 'Page not found';
            },
            '500' => function() {
                echo 'Internal server error';
            },
            '501' => function() {
                echo 'Not implemented';
            }
        )
    ),
    // Menu Tree (to understand this menu part the best is to see how S renders it in your app)
    'menus' => array(
        'Systems' => array(
            'Example 1' => array(
                'Menu 1' => '1' // the leaf must containg a name and an ID (this id will be passed to your SystemLocator :D)
                // in this example, I used integer IDs, but you can create it the way you want to
            )
        )
    )
);
```

Now the APIs. Let enter the `apis` folder. You must have an `Ws1.php` file.

```php
<?php

namespace apis;

class Ws1
{

    public function get($request)
    {
        return array(
            'body' => json_encode(array(1, 2, 3, 4, 5)),
            'headers' => array(
                array('Content-type', 'application/json')
            )
        );
    }

}
```

This example API implement a `get` service, returning an `[1, 2, 3, 4, 5]` json array.

Now the systems. I used and MVC logic in here, but if you think for a while, you'll see that you can make this part the way you want to, it's very flexible.

The `Controller` (`systems/Example1/Menu1/Controller.php`):

```php
<?php

namespace systems\Example1\Menu1;

class Controller extends \O\Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->setModel(new Model);
    }

    public function init()
    {
        return array(
            'body' => $this->getTemplate()->fetch(__DIR__ . '/Views/Index.php')
        );
    }

}
```

The `Model`(an empty model because this example is very simple):

```php
<?php

namespace systems\Example1\Menu1;

class Model extends \O\Model
{

}
```

The example view (`systems/Example1/Menu1/Views/Index.php`):

```php
<?php

// Use the O plugin for ExtJS integration
use O\UI\Plugins\ExtJS\Manager as m;
?>

<?php m::start(); ?>
<script>
<?= m::cb(); ?>
    ({
        xtype: 'window',
        title: 'Hello!',
        height: 200,
        width: 300,
        autoShow: true
    });
</script>
<?php m::end(); ?>
```

Very simple, isn't it? Now if you run your app, you will be presented to a login form, put `admin` in the user and `admin` again in the password, hit the `Enter` or press `Sign in` button. 
Now, at your top-left, you may see a `Systems` button (as you created in the `menus` config). Click on it and navigate untill the `Menu1` menuitem. Press it and you will now be presented to your `Hello!` window.

What about my webservices? Alright, enter in this url `http://myurl.com/TestApp/webservices/ws1`. You may now see the `[1, 2, 3, 4, 5]` json array :D. This webservices part follows the REST architecture, so if you POST this URL, it will search for an `post` method in this api. Also, if you want to pass custom methods instead of the defaults (`get`, `post`, `put` and `delete`), you can do it by passing a `__METHOD` parameter in the request, containing the API method name.

Later I'll post more advanced examples, using modules and a default window systems view for large menu trees.
