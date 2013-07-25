<?php

use O\UI\Plugins\ExtJS\Manager as m;

// get the config
$cfg = S\App::cfg();

// base path
$base_path = rtrim($cfg['paths']['base_path'], '/');

// get the menus
$cfg_menus = $cfg['menus'];

// besides the basic menu config, we have the modules (it expects that each module has a Module.php that returns a config object (in her we expect a key 'menus')
$modules_path = $cfg['paths']['modules_path'];
if ($modules_path) {
    $module_key = ' @-> ';
    foreach (new DirectoryIterator($modules_path) as $fileInfo) {
        if ($fileInfo->isDir() && !$fileInfo->isDot()) {
            $module_cfg = include $fileInfo->getPathname() . DIRECTORY_SEPARATOR . 'Module.php';
            $cfg_menus[$module_cfg['name']] = $module_cfg['menus'];
            array_walk_recursive($cfg_menus[$module_cfg['name']], function(&$v) use($module_key, $fileInfo) {
                        $v = $fileInfo->getFilename() . $module_key . $v;
                    });
        }
    }
}

// encode the menus, so we can work with them in the javascript
$menus = json_encode($cfg_menus);

// app name
$app_name = $cfg['app']['name'];
?>
<!DOCTYPE html>
<html>
    <head>
        <title><?= ($title = $app_name . ' - v' . $cfg['app']['version']); ?></title>
        <?= m::scripts(); ?>
    </head>
    <body>
        <script>
            var S = (function() {
                return {
                    load: function(url, params) {
                        Ext.data.JsonP.request({
                            url: url,
                            params: params,
                            success: function(cfg) {
                                        
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
                            }
                        });
                    }
                }
            })();
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
                        {
                            text: 'Logout',
                            handler: function() {document.location.href += 'logout';}
                        }
                    ]),
                    renderTo: Ext.getBody()
                });
            });
        </script>
    </body>
</html>