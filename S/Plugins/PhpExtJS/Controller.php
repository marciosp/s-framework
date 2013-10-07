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
        // error handling
        set_error_handler($this->errorHandler());

        // exception handling
        set_exception_handler($this->exceptionHandler());

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
        $this->view->setController($this);
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
    final protected function say($msg, $success = true)
    {
        $this->ob_start = false;
        return array(
            'headers' => array(
                array('Content-type', 'text/javascript')
            ),
            'body' => ob_get_clean() . m::cb() . '(' . Encoder::encode(array(
                'msg' => $msg,
                'success' => $success
            )) . ');'
        );
    }

    /**
     * 
     * Tells the App everything worked fine - static
     * 
     * @return array
     * 
     * @author Vitor de Souza <vitor_souza@outlook.com>
     * @date 31/07/2013 | 04/09/2013
     */
    final public static function finish()
    {
        die(ob_get_clean() . m::cb() . '(' . Encoder::encode(array(
                    'success' => true
                )) . ');');
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

    /**
     * 
     * Send an error to the browser
     * 
     * @param string $msg The error msg
     * 
     * @return void
     * 
     * @author Vitor de Souza <vitor_souza@outlook.com>
     * @date 01/08/2013
     */
    final public function error($msg)
    {
        self::err($msg);
    }

    /**
     * 
     * Send an error to the browser (static)
     * 
     * @param string $msg The error msg
     * 
     * @return void
     * 
     * @author Vitor de Souza <vitor_souza@outlook.com>
     * @date 29/08/2013
     */
    final public static function err($msg)
    {
        die(m::cb() . '(' . Encoder::encode(array(
                    'success' => false,
                    'msg' => $msg
                )) . ');');
    }

    /**
     * 
     * Get the error handler function (you can use this function to apply to the config of S framework)
     * 
     * @return callable
     * 
     * @author Vitor de Souza <vitor_souza@outlook.com>
     * @date 01/08/2013
     */
    public static function errorHandler()
    {
        return function($errno, $errstr, $errfile, $errline) {

                    // clear the buffer
                    ob_get_contents() && ob_clean();

                    // if we are using the @ before the sentence
                    if (error_reporting() === 0)
                        return;

                    // get the constant (E_*)
                    $constants = get_defined_constants(true);
                    $errtype = array_search($errno, $constants['Core']);

                    // check for REST Request (the error must be sent in json instead of HTML)
                    $cfg = \S\App::cfg();
                    if (false !== strpos(trim($_SERVER['REQUEST_URI'], '/'), trim($cfg['paths']['base_path'], '/') . '/webservices/')) {
                        die(Encoder::encode(array(
                                    'errtype' => $errtype,
                                    'err' => $errstr,
                                    'errfile' => $errfile,
                                    'errline' => $errline
                                )));
                    }

                    // generates the trace
                    $exception = new \Exception('');
                    $trace = str_replace(array("#", "\n"), array("<br />#", ''), $exception->getTraceAsString());

                    // the error message
                    $error_msg = "<div style='overflow:auto;max-height:300px;max-width:570px;white-space:nowrap;'><b>Type:</b> {$errtype}<br/><b>Err:</b> {$errstr}<br/><b>Errfile:</b> $errfile<br/><b>Errline:</b> {$errline}<br/><b>Trace:</b> <br/>{$trace}</div>";

                    // send the error
                    Controller::err($error_msg);

                    // kill the rest of the execution
                    die();
                };
    }

    /**
     * 
     * Get the exception handler function (you can use this function to apply to the config of S framework)
     * 
     * @return callable
     * 
     * @author Vitor de Souza <vitor_souza@outlook.com>
     * @date 29/08/2013
     */
    public static function exceptionHandler()
    {
        return function(\Exception $e) {

                    // clear the buffer
                    ob_get_contents() && ob_clean();

                    // check for REST Request (the error must be sent in json instead of HTML)
                    $cfg = \S\App::cfg();
                    if (false !== strpos(trim($_SERVER['REQUEST_URI'], '/'), trim($cfg['paths']['base_path'], '/') . '/webservices/')) {
                        die(Encoder::encode(array(
                                    'exceptiontype' => get_class($e),
                                    'exception' => $e->getMessage(),
                                    'exceptionfile' => $e->getFile(),
                                    'exceptionline' => $e->getLine()
                                )));
                    }

                    // generates the trace
                    $trace = str_replace(array("#", "\n"), array("<br />#", ''), $e->getTraceAsString());

                    // the message
                    $msg = nl2br($e->getMessage());

                    // the exception message
                    $exception_msg = "<div style='overflow:auto;max-height:300px;max-width:570px;white-space:nowrap;'><b>Type:</b> " . get_class($e) . "<br/><b>Exception:</b> {$msg}<br/><b>Exceptionfile:</b> {$e->getFile()}<br/><b>Exceptionline:</b> {$e->getLine()}<br/><b>Trace:</b> <br/>{$trace}</div>";

                    // send the exception
                    Controller::err($exception_msg);

                    // kill the rest of the execution
                    die();
                };
    }

}