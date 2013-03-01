<?php
/*
Plugin Name: Content Fields
Description: Manage Content Fields
Version: 1.0
Author: a.l.e
Author URI: ideale.ch
Based on CustomFields' ItemsManager version by mikehenken, which is a  Modified Version of Mvlcek's Plugin
TODO: check if the static parts make sense or if it alwaays has to be instantiated from a client plugin (does it make sense as a standalone?)
*/

define('CONTENTFIELDS_DATA_FILE', GSDATAOTHERPATH.'contentfields.xml'); 

$contentfields_plugin_id = basename(__FILE__, ".php");
#
# register plugin
register_plugin(
  $contentfields_plugin_id, //Plugin id
  'ContentFields',  //Plugin name
  '1.0',    //Plugin version
  'Ale Rimoldi',  //Plugin author
  'http://www.ideale.ch/', //author website
  'Manage Content Fields for Web Forms', //Plugin description
  'pages', //page type - on which admin tab to display
  'contentfields_admin'  //main function (administration)
);

define('CONTENTFIELDS_DATA_PATH', GSDATAOTHERPATH.$contentfields_plugin_id.'/');
define('CONTENTFIELDS_BACKUP_PATH', GSBACKUPSPATH.'other/'.$contentfields_plugin_id.'/');
define('CONTENTFIELDS_TEMPLATE_PATH', GSPLUGINPATH.$contentfields_plugin_id.'/template/');
define('CONTENTFIELDS_TEMPLATE_URL', $SITEURL.'plugins/'.$contentfields_plugin_id.'/template/');
define('CONTENTFIELDS_DATA_SETTINGS', CONTENTFIELDS_DATA_PATH.'settings.xml'); // TODO: is it needed?
define('CONTENTFIELDS_BACKUP_SETTINGS', CONTENTFIELDS_BACKUP_PATH.'settings.xml');
define('CONTENTFIELDS_DATAITEM_PATH', CONTENTFIELDS_DATA_PATH.'field/');
define('CONTENTFIELDS_BACKUP_DATAITEM', CONTENTFIELDS_BACKUP_PATH.'field.xml');
define('CONTENTFIELDS_REQUEST_PREFIX', 'contentfields_field_');

if (!is_frontend()) {
    i18n_merge($contentfields_plugin_id, substr($LANG,0,2)); 
}

include(GSPLUGINPATH.$contentfields_plugin_id.'/ContentFields.php');
ContentFields::set_plugin_id($contentfields_plugin_id);
ContentFields::set_plugin_info($plugin_info[$contentfields_plugin_id]);
ContentFields::initialize();

i18n_merge($contentfields_plugin_id) || i18n_merge($contentfields_plugin_id, 'en_US');

add_action('header', $contentfields_plugin_id.'_load_css'); // TODO: i guess that we can do it as a static call from the plugin calling

function ContentFields_load_css() {
    if (ContentFields::is_want_css()) {
        ContentFields::load_css();
    }
}
