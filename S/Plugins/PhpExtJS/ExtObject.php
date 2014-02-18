<?php

/**
 * 
 * This file is part of the PhpExtJS plugin for S framework.
 * 
 * @license http://opensource.org/licenses/GPL-3.0 GPL-3.0
 * 
 * @author Vitor de Souza <vitor_souza@outlook.com>
 * @date 03/02/2014
 * 
 */

namespace S\Plugins\PhpExtJS;

/**
 * 
 * Creates a ext-object
 * 
 * @author Vitor de Souza <vitor_souza@outlook.com>
 * @date 03/02/2014
 * 
 */
class ExtObject
{

    /**
     * 
     * Creates a ext object
     * 
     * @param string $name The object name
     * @param array $args An array with the properties
     * 
     * @return string The ExtJS javascript code to create the object
     * 
     * @author Vitor de Souza <vitor_souza@outlook.com>
     * @date 03/02/2014
     */
    public static function create($name, $properties)
    {
        return "%(function(){ return Ext.create('{$name}', " . Encoder::encode($properties) . ');})()%';
    }

}