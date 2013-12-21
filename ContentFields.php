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

$contentfields_plugin_id = basename(__FILE__, ".php");

if (method_exists('GS', 'load_plugin')) {
    GS::load_plugin('PHPFived');
} elseif (!defined('PHPFIVED') && array_key_exists('PHPFived.php', $live_plugins) && $live_plugins['PHPFived.php'])  {
    require_once('plugins/PHPFived.php');
    PHPFived::initialize();
}

GS::register_plugin(array(
    'id' => $contentfields_plugin_id,
    'name' => 'ContentFields',
    'version' => '0.1',
    'author' => 'Ale Rimoldi',
    'url' => 'http://www.ideale.ch/',
    'description' => 'Manage Content Fields for Web Forms',
    'page_type' => 'pages',
    'main_function' => 'Contentfields_routing'
));

// register the javascript libraries used
GS_UI::register_javascript_library('ckeditor', GS_JAVASCRIP_URL.'ckeditor/ckeditor.js');

define('CONTENTFIELDS_FIELD_TEXTAREA_TOOLBAR_ADVANCED', "
    ['Bold', 'Italic', 'Underline', 'NumberedList', 'BulletedList', 'JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock', 'Table', 'TextColor', 'BGColor', 'Link', 'Unlink', 'Image', 'RemoveFormat', 'Source'],
    '/',
    ['Styles','Format','Font','FontSize']
");
define('CONTENTFIELDS_FIELD_TEXTAREA_TOOLBAR_BASIC', "
    ['Bold', 'Italic', 'Underline', 'NumberedList', 'BulletedList', 'JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock', 'Link', 'Unlink', 'Image', 'RemoveFormat', 'Source']
");
define('CONTENTFIELDS_FIELD_TEXTAREA_HEIGHT', '200px');
define('CONTENTFIELDS_FIELD_TEXTAREA_NOPARAGRAPH', false);

define('CONTENTFIELDS_PLUGIN_PATH', GS_PLUGIN_PATH.$contentfields_plugin_id.'/');
define('CONTENTFIELDS_DATA_PATH', GS_DATA_OTHER_PATH.$contentfields_plugin_id.'/');
define('CONTENTFIELDS_DATA_FILE', GSDATAOTHERPATH.strtolower($contentfields_plugin_id).'.xml'); 
define('CONTENTFIELDS_BACKUP_PATH', GS_BACKUP_PATH.'other/'.$contentfields_plugin_id.'/');
define('CONTENTFIELDS_TEMPLATE_PATH', CONTENTFIELDS_PLUGIN_PATH.'template/');
define('CONTENTFIELDS_TEMPLATE_URL', GS_SITE_URL.'plugins/'.$contentfields_plugin_id.'/template/');
define('CONTENTFIELDS_DATA_SETTINGS', CONTENTFIELDS_DATA_PATH.'settings.xml'); // TODO: is it needed?
define('CONTENTFIELDS_BACKUP_SETTINGS', CONTENTFIELDS_BACKUP_PATH.'settings.xml');
define('CONTENTFIELDS_DATAITEM_PATH', CONTENTFIELDS_DATA_PATH.'field/');
define('CONTENTFIELDS_BACKUP_DATAITEM', CONTENTFIELDS_BACKUP_PATH.'field.xml');
define('CONTENTFIELDS_DATAENTRY_PATH', CONTENTFIELDS_DATA_PATH.'entry/');
define('CONTENTFIELDS_BACKUP_DATAENTRY', CONTENTFIELDS_BACKUP_PATH.'entry.xml');
define('CONTENTFIELDS_REQUEST_PREFIX', 'contentfields_field_');
define('CONTENTFIELDS_REQUEST_FIELD_PREFIX', 'contentfields_');

if (!is_frontend()) {
    i18n_merge($contentfields_plugin_id, substr($LANG,0,2)); 
}

include(CONTENTFIELDS_PLUGIN_PATH.'ContentFields.php');
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
