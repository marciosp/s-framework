<?php

/**
 * 
 * This file is part of the PhpExtJS plugin for S framework.
 * 
 * @license http://opensource.org/licenses/GPL-3.0 GPL-3.0
 * 
 * @author Vitor de Souza <vitor_souza@outlook.com>
 * @date 29/08/2013
 * 
 */

namespace S\Plugins\PhpExtJS;

/**
 * 
 * Creates a minihelp (a button that will open a grid in a window with lots of results to be chosen and then completes some fields with the record doubleclicked)
 * 
 * @author Vitor de Souza <vitor_souza@outlook.com>
 * @date 29/08/2013
 * 
 */
class Minihelp
{

    /**
     * 
     * Factory method to create minihelps
     * 
     * @param Widget $grid The Grid Widget
     * @param array $cfg Minihelp config (title, height, width, to)
	 * @param array $b_cfg Button config
     * 
     * @return Minihelp widget
     * 
     * @author Vitor de Souza <vitor_souza@outlook.com>
     * @date 31/07/2013
     * 
     * 
     * The 'to' config must me like:
     *      array(
     *          'storeField1' => 'fieldId1',
     *          ...
     *      )
     * 
     * If you want to filter the minihelp by just typing in the fields and typing pressing ENTER you need to implement a function "filter"
     * in the GRID Component, that will receive the dataIndex (store_field) in the first argument and the value that will be used to filter in the second 
     * and a third parameter that will receive true when we need to clear the filters before applying a new one.
     * It does work without this plus, but it speeds up the process.
     */
    public static function create(Widget $grid, array $cfg = array(), array $b_cfg = array())
    {
        
        // minihelp id
        $minihelp_id = 's-minihelp-' . rand();

        // grid id
        isset($grid->id) || $grid->id = 'grid' . rand();

        // add the doubleclick event to the grid
        $grid->listeners = isset($grid->listeners) ? $grid->listeners : array();
        $fn = array();
        foreach ($cfg['to'] as $store_field => $field_id)
            $fn[] = "Ext.getCmp('{$field_id}').setValue(arguments[1].get('{$store_field}'));";
        $grid->listeners['itemdblclick'] = Handler::js(array_merge($fn, array("Ext.getCmp('{$minihelp_id}').hide()")));

        // the button id
        $button_id = isset($b_cfg['id']) ? $b_cfg['id'] : 'btn' . rand();

        // the fields events
        $on = array();
        foreach ($cfg['to'] as $store_field => $field_id) {

            // setValue with mhValue
            $on[] = "(function(){var setValue = Ext.getCmp('{$field_id}').setValue;Ext.getCmp('{$field_id}').setValue = function() {setValue.apply(this, arguments);this.mhValue = this.getValue();this.validate();}})();";

            // on enter
            $on[] = "Ext.getCmp('{$field_id}').on('specialkey', function(field, e){if(e.getKey() !== e.ENTER){return;}field.el.swallowEvent(['keypress','keydown']);Ext.getCmp('{$button_id}').mh_key('{$field_id}', '{$store_field}');});";

            // minihelp value - validation
            $on[] = "Ext.getCmp('{$field_id}').mhValue = Ext.getCmp('{$field_id}').value;"; // initial "value" property
            $on[] = "Ext.getCmp('{$field_id}').validator = function(val){var f = Ext.getCmp('{$field_id}'); if(f.allowBlank && !val)return true;if(f.mhValue !== f.getValue())return 'Check the minihelp values!';return true;};";
        }

        // after store load, pick the record (if we have only one)
        $pick = "Ext.getCmp('{$button_id}').win.items[0].store.on('load', function(){var mh=Ext.getCmp('{$minihelp_id}');if(!mh)return;var g=mh.down('grid'),s=g.getStore(); s.data.items.length === 1 && g.fireEvent('itemdblclick', g, s.getAt(0));})";

        // the button
        $button = Widget::Button(array_merge($b_cfg, array(
                    'id' => $button_id,
                    'text' => '?',
                    'listeners' => array(
                        'render' => Handler::js(array_merge($on, array($pick)))
                    ),
                    'mh_key' => Handler::js(array(
                        "var mh = Ext.getCmp('{$minihelp_id}'), field_id, store_field, val",
                        "field_id = arguments[0]",
                        "store_field = arguments[1]",
                        "val = Ext.getCmp(field_id).getValue()",
                        "if(mh){mh.isVisible() || mh.show();}",
                        "if(!mh){Ext.getCmp('{$button_id}').handler();}",
                        "mh = mh || Ext.getCmp('{$minihelp_id}')",
                        "val && mh.down('grid').filter(store_field, val, true)"
                    )),
					'reconfigure' => Handler::js(array(
						"var store = arguments[0]",
						"var mh = Ext.getCmp('{$minihelp_id}')",
						"if(mh){mh.down('grid').reconfigure(store);}else {this.win.items[0].store = store;}"
					)),
                    'handler' => Handler::js(array(
                        "var mh = Ext.getCmp('{$minihelp_id}')",
                        "if(mh){mh.show();return;}",
                        "Ext.createWidget(this.win.xtype, this.win).show()"
                    ))
                )));

        // creates the window
        $window = Widget::Window(array(
                    // $cfg
                    'height' => isset($cfg['height']) ? $cfg['height'] : 350,
                    'width' => isset($cfg['width']) ? $cfg['width'] : 600,
                    'title' => isset($cfg['title']) ? $cfg['title'] : 'Minihelp',
                    // fim $cfg
                    'id' => $minihelp_id,
                    'layout' => 'fit',
                    'items' => array($grid)
                ));

        // put the window inside the button
        $button->win = $window;

        // return the minihelp widget
        return $button;
    }

}