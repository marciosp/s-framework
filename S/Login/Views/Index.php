<?php

// use O Plugin for integration with ExtJS
use O\UI\Plugins\ExtJS\Manager as m;

// get the config
$cfg = S\App::cfg();

// get the base path
$base_path = $cfg['paths']['base_path'];
?>
<!DOCTYPE html>
<html>
    <head>
        <title><?= ($title = $cfg['app']['name'] . ' - v' . $cfg['app']['version']); ?></title>
        <?= m::scripts(); ?>
    </head>
    <body>
        <script>
            Ext.onReady(function() {
                Ext.create('Ext.window.Window', {
                    title: 'Sign In | <?= $title; ?>',
                    height: 170,
                    width: 300,
                    modal: true,
                    resizable: false,
                    draggable: false,
                    defaultFocus: 'user',
                    closable: false,
                    layout: 'fit',
                    items: [{
                            xtype: 'form',
                            layout: 'fit',
                            url: '<?= $base_path; ?>',
                            bodyStyle: {padding: '10px'},
                            buttons: [{
                                    text: 'Reset',
                                    handler: function() {this.up('form').getForm().reset();}
                                }, {
                                    xtype: 'button',
                                    text: 'Sign In',
                                    formBind: true,
                                    id: 'signin',
                                    disabled: true,
                                    handler: function() {
                                        var form = this.up('form').getForm();
                                        form.isValid() && form.submit({
                                            success: function(form, action) {document.location.reload();},
                                            failure: function(form, action) {Ext.Msg.alert('Failed', action.result.msg || 'Try again later!');}
                                        });
                                    }
                                }],
                            items: [{
                                    xtype: 'fieldset',
                                    style: 'margin-top: 10px',
                                    border: false,
                                    defaults: {
                                        listeners: {
                                            specialkey: function(field, e) {e.getKey() === e.ENTER && field.up('form').down('#signin').handler();}
                                        },
                                        allowBlank: false,
                                        xtype: 'textfield'
                                    },
                                    items: [{
                                            fieldLabel: 'User',
                                            name: 'user',
                                            id: 'user'
                                        }, {
                                            fieldLabel: 'Password',
                                            name: 'pass',
                                            id: 'pass',
                                            inputType: 'password'
                                        }]
                                }]
                        }]
                }).show();
            });
        </script>
    </body>
</html>