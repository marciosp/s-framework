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
 * A widget
 * 
 * @author Vitor de Souza <vitor_souza@outlook.com>
 * @date 31/07/2013
 * 
 */
class Widget
{

    /**
     * 
     * Stores alias to ExtJS xtypes, so plugins can define their own ExtJS Widgets
     * 
     * @var array
     * 
     * @author Vitor de Souza <vitor_souza@outlook.com>
     * @date 31/07/2013
     * 
     */
    private static $alias = array();

    /**
     * 
     * Private construct
     * 
     * @return Widget
     * 
     * @author Vitor de Souza <vitor_souza@outlook.com>
     * @date 31/07/2013
     */
    private function __construct()
    {
        
    }

    /**
     * 
     * Generates a widget
     * 
     * @param string $name The widget name
     * @param array $args An array containing in the first element an array of configs to that widget
     * 
     * @return Widget
     * 
     * @author Vitor de Souza <vitor_souza@outlook.com>
     * @date 31/07/2013 | 06/08/2013
     */
    public static function __callStatic($name, $args)
    {

        // normalize the XTYPE
        $name = strtolower($name);

        // create an empty widget
        $widget = new self;

        // widget properties, it expects W::myWidget(array('key' => 'value', ...));
        foreach ($args[0] as $k => $v)
            $widget->$k = $v;

        // set the widget xtype
        $widget->xtype = isset(self::$alias[$name]) ? self::$alias[$name] : $name;

        // get the config
        $cfg = \S\App::cfg();

        // get the custom properties config
        if (isset($cfg['plugins']['PhpExtJS']['widgets'])) {
            if (isset($cfg['plugins']['PhpExtJS']['widgets'][$widget->xtype])) {
                if (isset($cfg['plugins']['PhpExtJS']['widgets'][$widget->xtype]['default'])) {
                    foreach ($cfg['plugins']['PhpExtJS']['widgets'][$widget->xtype]['default'] as $key => $value) {
                        if (!isset($widget->$key))
                            $widget->$key = $value;
                    }
                }
                if (isset($cfg['plugins']['PhpExtJS']['widgets'][$widget->xtype]['overwrite'])) {
                    foreach ($cfg['plugins']['PhpExtJS']['widgets'][$widget->xtype]['overwrite'] as $key => $value) {
                        $widget->$key = $value;
                    }
                }
            }
        }

        // return the widget
        return $widget;
    }

    /**
     * 
     * This way, we can have a plugin that extend default ExtJS Xtypes with custom Widgets (widgets extending the ExtJS default widgets)
     * 
     * @param string $alias The alias (window, form, etc. - the widget extjs xtype)
     * @param string $xtype The real xtype defined in ExtJS
     * 
     * @return void
     * 
     * @author Vitor de Souza <vitor_souza@outlook.com>
     * @date 31/07/2013
     */
    public static function addAlias($alias, $xtype)
    {
        self::$alias[$alias] = $xtype;
    }

}