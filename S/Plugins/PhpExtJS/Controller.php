<?php

/**
 * 
 * This file is part of the PhpExtJS plugin for S framework.
 * 
 * @license http://opensource.org/licenses/GPL-3.0 GPL-3.0
 * 
 * @author Vitor de Souza <vitor_souza@outlook.com>
 * @date 31/07/2013
 * 
 */

namespace S\Plugins\PhpExtJS;

// O ExtJS Plugin
use O\UI\Plugins\ExtJS\Manager as m;

/**
 * 
 * Your controller must extend this class
 * 
 * @author Vitor de Souza <vitor_souza@outlook.com>
 * @date 31/07/2013
 * 
 */
abstract class Controller extends \O\Controller
{

    /**
     * 
     * Your View class
     * 
     * @var View
     * 
     * @author Vitor de Souza <vitor_souza@outlook.com>
     * @date 31/07/2013
     * 
     */
    private $view;

    /**
     * 
     * Store your Controller's ID (will be used in the Repo class)
     * 
     * @var string
     * 
     * @author Vitor de Souza <vitor_souza@outlook.com>
     * @date 31/07/2013
     * 
     */
    private $id;

    /**
     * 
     * Store the URL ID that opened this control (this is set by the Loader class)
     * 
     * @var string
     * 
     * @author Vitor de Souza <vitor_souza@outlook.com>
     * @date 31/07/2013
     * 
     */
    private $url_id;

    /**
     * 
     * Store if we already started ob_start
     * 
     * @var bool
     * 
     * @author Vitor de Souza <vitor_souza@outlook.com>
     * @date 31/07/2013
     * 
     */
    private $ob_start;

    /**
     * 
     * Init the application
     * 
     * @return Controller
     * 
     * @author Vitor de Souza <vitor_souza@outlook.com>
     * @date 31/07/2013
     */
    public function __construct()
    {
        // generates your's controller ID
        $this->id = self::id(get_class($this));

        // calls O Controller's constructor
        parent::__construct();
    }

    /**
     * 
     * Generates the ID of your controller
     * 
     * @param string $class_name Your controller's name
     * 
     * @return string The ID
     * 
     * @author Vitor de Souza <vitor_souza@outlook.com>
     * @date 31/07/2013
     */
    public static function id($class_name = null)
    {
        // late static binding
        return sha1($class_name ? $class_name : get_called_class());
    }

    /**
     * 
     * Every child of this class must implement the init method (the first method your app will execute)
     * 
     * @return array Must return a call to this class fetch method
     * 
     * @author Vitor de Souza <vitor_souza@outlook.com>
     * @date 31/07/2013
     */
    abstract public function init();

    /**
     * 
     * Stores a view object inside this class, so we are able to call view's methods when we want to
     * 
     * @return Controller
     * 
     * @author Vitor de Souza <vitor_souza@outlook.com>
     * @date 31/07/2013
     */
    final protected function setView(View $view)
    {
        $this->view = $view;
        $this->view->controller = $this;
        return $this;
    }

    /**
     * 
     * When we want to execute some ExtJS code, like: Ext.getCmp('myGrid').store.load(); we will do this in PHP for now on, so we need to first call this method
     * It will return an object of Exec class, and this class will be responsible to create the JS code and output it correctly.
     * 
     * @return Exec
     * 
     * @author Vitor de Souza <vitor_souza@outlook.com>
     * @date 31/07/2013
     */
    final protected function exec()
    {
        $this->ob_start || ob_start();
        return new Exec;
    }

    /**
     * 
     * Acessor for private properties
     * 
     * @return mixed
     * 
     * @author Vitor de Souza <vitor_souza@outlook.com>
     * @date 31/07/2013
     */
    final public function __get($name)
    {
        return $this->$name;
    }

    /**
     * 
     * Acessor for the View object
     * 
     * @return View
     * 
     * @author Vitor de Souza <vitor_souza@outlook.com>
     * @date 31/07/2013
     */
    final public function getView()
    {
        return $this->view;
    }

    /**
     * 
     * The response, fetching the view
     * 
     * @param string $view_method The view method we want to execute
     * 
     * @return array
     * 
     * @author Vitor de Souza <vitor_souza@outlook.com>
     * @date 31/07/2013
     */
    final protected function fetch($view_method)
    {
        return array(
            'body' => $this->getTemplate()->fetch($this->getView()->$view_method())
        );
    }

    /**
     * 
     * Returns a message
     * 
     * @param string $msg The message
     * 
     * @return array
     * 
     * @author Vitor de Souza <vitor_souza@outlook.com>
     * @date 31/07/2013
     */
    final protected function say($msg)
    {
        $this->ob_start = false;
        return array(
            'headers' => array(
                array('Content-type', 'text/javascript')
            ),
            'body' => ob_get_clean() . m::cb() . '(' . Encoder::encode(array(
                'msg' => $msg,
                'success' => true
            )) . ');'
        );
    }

    /**
     * 
     * Tells the App everything worked fine
     * 
     * @return array
     * 
     * @author Vitor de Souza <vitor_souza@outlook.com>
     * @date 31/07/2013
     */
    final protected function ok()
    {
        $this->ob_start = false;
        return array(
            'headers' => array(
                array('Content-type', 'text/javascript')
            ),
            'body' => ob_get_clean() . m::cb() . '(' . Encoder::encode(array(
                'success' => true
            )) . ');'
        );
    }

    /**
     * 
     * store the Controller with all the changes in the Repo in each request
     * 
     * @return void
     * 
     * @author Vitor de Souza <vitor_souza@outlook.com>
     * @date 31/07/2013
     */
    final public function __destruct()
    {
        Repo::store($this->id, $this);
    }

    /**
     * 
     * store the Controller with all the changes in the Repo in each request
     * 
     * @param string $url_id The URL_ID
     * 
     * @return Controller
     * 
     * @author Vitor de Souza <vitor_souza@outlook.com>
     * @date 01/08/2013
     */
    final public function setUrlId($url_id)
    {
        $this->url_id = $url_id;
        return $this;
    }

}