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

/**
 * 
 * A class to write ExtJS code in PHP
 * 
 * @author Vitor de Souza <vitor_souza@outlook.com>
 * @date 31/07/2013
 * 
 */
class Exec
{

    /**
     * 
     * Stores the current ID of the component that have the method we want to execute
     * For example, imagine: Ext.getCmp('myGrid').store.load();, this ID in this class will be: "myGrid"
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
     * Stores the current code generated
     * 
     * @var string
     * 
     * @author Vitor de Souza <vitor_souza@outlook.com>
     * @date 31/07/2013
     * 
     */
    private $code;

    /**
     * 
     * Starts the execution of the ExtJS code
     * 
     * @param string $id The component ID
     * 
     * @return Exec
     * 
     * @author Vitor de Souza <vitor_souza@outlook.com>
     * @date 31/07/2013
     */
    public function to($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * 
     * After you execute this class "to" method, you will type the name of your ExtJS function will want,
     * for example, imagine Ext.getCmp('myGrid').getStore().load();, you will type in PHP in your controller like: 
     * $this->exec()->to('myGrid')->getStore()->load()->flush();, the "getStore" call and "load" call will get here, 
     * because the aren't really this class methods
     * 
     * @param string $name The ExtJS method name
     * @param array $args Array of arguments to be passed to the ExtJS method (pass it in PHP code, here it will be converted to Javascript, unless it is a javascript function, then you'll have to pass the js function code inside an string surrounded by '%'
     * 
     * @return Exec
     * 
     * @author Vitor de Souza <vitor_souza@outlook.com>
     * @date 31/07/2013
     * 
     * If you want to pass a Javascript function or something to be evaluated as pure JS, just pass it in a string surrounded by '%', like:
     * '%function(){ Ext.Msg.alert('Title', 'Msg'); }%'
     */
    public function __call($name, $args)
    {

        // you must execute first the "to" method
        if ($this->id) {

            // $name in this case will be the ExtJS method name
            $this->code .= ".{$name}(" . substr(str_replace(array('"%', '%"'), '', Encoder::encode($args)), 1, -1) . ")";

            // return $this so we can continue calling unexisting methods to create the ExtJS function call
            return $this;
        }
    }

    /**
     * 
     * Same objective as __call, but to ExtJS properties instead of ExtJS methods, like:
     * Ext.getCmp('myGrid').store.load(); This will call this __get method when we type the ->store-> and the __call methd when we type the ->load()-> method.
     * 
     * @param string $name The property
     * 
     * @return Exec
     * 
     * @author Vitor de Souza <vitor_souza@outlook.com>
     * @date 31/07/2013
     * 
     * If we need to access arrays elements, like in .store.data.items[0]. we will use ->store->data->items->_0->
     */
    public function __get($name)
    {
        $this->code .= preg_match("@^_[0-9]+$@", $name) ? ('[' . substr($name, 1) . ']') : ".{$name}";
        return $this;
    }

    /**
     * 
     * Finish the PHP -> ExtJS rewrite
     * 
     * @return void
     * 
     * @author Vitor de Souza <vitor_souza@outlook.com>
     * @date 31/07/2013
     * 
     */
    public function flush()
    {

        // if we already passed by "to" and ("__call" or "__get")
        if ($this->id && $this->code) {

            // flush the code
            echo "Ext.getCmp('{$this->id}'){$this->code};";

            // reset the control variables
            $this->code = $this->id = '';
        }
    }

}