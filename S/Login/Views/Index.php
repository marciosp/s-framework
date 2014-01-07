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
 * This file is responsible for the login form creation, using the ExtJS framework
 */
//
// use O Plugin for integration with ExtJS
use O\UI\Plugins\ExtJS\Manager as m;
// use S App
use S\App;

// get the config
$cfg = S\App::cfg();

// extra head files (.css & .js, for example)
$e_head = '';
if (isset($cfg['head']))
    $e_head = implode("\n", $cfg['head']);

// get the base path
$base_path = $cfg['paths']['base_path'];
?>
<!DOCTYPE html>
<html>
    <head>
        <title><?= ($title = $cfg['app']['name'] . ' - v' . $cfg['app']['version']); ?></title>
        <?= m::scripts(); ?>
        <?= $e_head; ?>
    </head>
    <body>
        <script>
            Ext.onReady(function() {
                Ext.create('Ext.window.Window', {
                    title: '<?= App::t('SIGNIN'); ?> | <?= $title; ?>',
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
                                    xtype: 'button',
                                    text: '<?= App::t('SIGNIN'); ?>',
                                    formBind: true,
                                    id: 'signin',
                                    disabled: true,
                                    handler: function() {
                                        var form = this.up('form').getForm();
                                        form.isValid() && form.submit({
                                            success: function(form, action) {document.location.reload();},
                                            failure: function(form, action) {Ext.Msg.alert('<?= App::t('FAILED'); ?>', action.result.msg || '<?= App::t('TRYAGAINLATER'); ?>');}
                                        });
                                    }
                                }, {
                                    text: '<?= App::t('RESET'); ?>',
                                    handler: function() {this.up('form').getForm().reset();}
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
                                            fieldLabel: '<?= App::t('USERFIELD'); ?>',
                                            name: 'user',
                                            id: 'user'
                                        }, {
                                            fieldLabel: '<?= App::t('PASSWORDFIELD'); ?>',
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