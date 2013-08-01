<?php

/**
 * 
 * This file is part of the S framework for PHP, a framework based on the O framework.
 * 
 * @license http://opensource.org/licenses/GPL-3.0 GPL-3.0
 * 
 * @author Vitor de Souza <vitor_souza@outlook.com>
 * @date 19/07/2013
 * 
 * This file is responsible for the main view creation, using the ExtJS framework
 */
//
// use O Plugin for integration with ExtJS
use O\UI\Plugins\ExtJS\Manager as m;
// use S App
use S\App;

// get the config
$cfg = S\App::cfg();

// base path
$base_path = rtrim($cfg['paths']['base_path'], '/');

// get the menus
$cfg_menus = is_array($cfg['menus']) ? $cfg['menus'] : call_user_func($cfg['menus']);

// besides the basic menu config, we have the modules (it expects that each module has a Module.php that returns a config object containing in it a key 'menus'
$module_key = ' @-> ';
if (isset($cfg['paths']['modules_path'])) {
    $modules_path = $cfg['paths']['modules_path'];

    // iterates the modules directory
    foreach (new DirectoryIterator($modules_path) as $fileInfo) {
        if ($fileInfo->isDir() && !$fileInfo->isDot()) {

            // include the Module.php config file
            $module_cfg = include $fileInfo->getPathname() . DIRECTORY_SEPARATOR . 'Module.php';

            // get the menus
            $cfg_menus[$module_cfg['name']] = $module_cfg['menus'];

            // apply to each menu leaf an replace, so we can now below when the menu is from a module and when the menu is not
            array_walk_recursive($cfg_menus[$module_cfg['name']], function(&$v) use($module_key, $fileInfo) {

                        // the menu link of a module changes to: module_name . module_key . menu_id
                        $v = $fileInfo->getFilename() . $module_key . $v;
                    });
        }
    }
}

// encode the menus, so we can work with them in the javascript code below
$menus = json_encode($cfg_menus);
?>
<!DOCTYPE html>
<html>
    <head>
        <title><?= ($title = $cfg['app']['name'] . ' - v' . $cfg['app']['version']); ?></title>
        <?= m::scripts(); ?>
    </head>
    <body>
        <script>
            // creates a S global var (the only one!) that contains some useful functions
            var S = (function() {
                return {
                    /**
                     * 
                     * Load a page
                     * 
                     * @param url string The URL where the page will be loaded (using JsonP)
                     * @param params object Parameters to be send via GET
                     * 
                     * @return void
                     * 
                     * @author Vitor de Souza <vitor_souza@outlook.com>
                     * @date 25/07/2013
                     */
                    load: function(url, params) {
                        Ext.data.JsonP.request({
                            url: url,
                            params: params,
                            success: Ext.getCmp('s-win') ? this.success.win : this.success.normal,
                            failure: this.failure
                        });
                    },
                    failure: function() {
                        Ext.Msg.alert('App', 'App failed!');
                        console.log('FAILED');
                    },
                    success: {
                        /**
                         * 
                         * Executed when request is successful and we ARE NOT using S SystemWindow
                         * 
                         * @return void
                         * 
                         * @author Vitor de Souza <vitor_souza@outlook.com>
                         * @date 31/07/2013
                         */
                        normal: function(cfg) {
                            
                            // check for messages
                            if('success' in cfg && cfg.msg) {
                                Ext.Msg.alert('App', cfg.msg);
                                return;
                            }
                                    
                            // create the widget
                            var widget = Ext.widget(cfg.xtype, cfg);
                                        
                            // if the widget is not rendered yet, we render it inside a wrapper panel
                            if(widget.xtype !== 'window' && !widget.renderTo) {
                                            
                                // widget title
                                var title = widget.title;
                                delete widget.title;
                                            
                                // creates a wrapper
                                Ext.create('Ext.window.Window', {
                                    id: 's-wrapper',
                                    title: title,
                                    bodyStyle: 'padding: 5px',
                                    layout: 'fit',
                                    maximized: true,
                                    items: widget,
                                    autoShow: true
                                });
                            }
                        },
                        /**
                         * 
                         * Executed when request is successful and we ARE using S SystemWindow
                         * 
                         * @return void
                         * 
                         * @author Vitor de Souza <vitor_souza@outlook.com>
                         * @date 31/07/2013
                         */
                        win: function(cfg) {
                            
                            // check for messages
                            if('success' in cfg && cfg.msg) {
                                Ext.Msg.alert('App', cfg.msg);
                                return;
                            }
                            
                            // create the widget
                            var widget = Ext.widget(cfg.xtype, cfg),
                            win = Ext.getCmp('s-win');
                            
                            // remove and show
                            win.removeAll();
                            cfg.autoShow || win.add(widget);
                        }
                    }
                }
            })();
            
            // create the main component
            Ext.onReady(function() {
                
                // create the menus
                var menus = (function buildMenus(m) {
                    var menu = [];
                    for(var i in m) m.hasOwnProperty(i) && menu.push({
                        text: i,
                        padding: '5px 0 5px 0',
                        menu: Ext.isObject(m[i]) ? {
                            items: buildMenus(m[i])
                        } : null,
                        handler: Ext.isObject(m[i]) ? null : (function(id) {
                            return function() {
                                
                                // modules treatment
                                var module_key = '<?= $module_key; ?>', 
                                module_key_len = module_key.length, 
                                index = id.indexOf(module_key), 
                                is_module = index >= 0, 
                                module_name = '';
                                    
                                if(is_module) {
                                    module_name = id.substring(0, index);
                                    id = id.substring(index + module_key_len);
                                }
                                
                                // make the request
                                S.load('<?= $base_path; ?>/systems/' + id, {
                                        
                                    // if the menu is from a module
                                    module: is_module,
                                        
                                    // module name
                                    module_name: module_name
                                });
                            }
                        })(m[i])
                    });
                    return menu;
                })(<?= $menus; ?>);
                
                // creates the Toolbar
                Ext.create('Ext.Toolbar', {
                    items: Ext.Array.merge(menus, [
                        '->', '-',
                        {xtype: 'tbspacer', width: 50},
                        // Logout button
                        {
                            text: '<?= App::t('LOGOUT'); ?>',
                            handler: function() {document.location.href += 'logout';}
                        }
                    ]),
                    renderTo: Ext.getBody()
                });
            });
        </script>
    </body>
</html>