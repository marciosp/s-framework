<?php

// use O Plugin for integration with ExtJS
use O\UI\Plugins\ExtJS\Manager as m;

// get the config
$cfg = S\App::cfg();

// encode the menus
$menus = json_encode($this->menus);

// get the base path
$base_path = rtrim($cfg['paths']['base_path'], '/');
?>
<?php m::start(); ?>
<script>
    
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
                    Ext.data.JsonP.request({
                        url: '<?= $base_path; ?>/systems/' + id,
                        success: function(cfg) {
                            var widget = Ext.widget(cfg.xtype, cfg),
                                win = Ext.getCmp('s-win');
                            
                            win.removeAll();
                            cfg.autoShow || win.add(widget);
                        }
                    });
                }
            })(m[i])
        });
        return menu;
    })(<?= $menus; ?>);
    
    // send the window with the JsonP callback function
<?= m::cb(); ?>({
    xtype: 'window',
    id: 's-win',
    title: '<?= $this->title; ?>',
    autoShow: true,
    maximized: true,
    tbar: menus,
    layout: 'fit',
    bodyStyle: 'padding:5px'
});
</script>
<?php m::end(); ?>