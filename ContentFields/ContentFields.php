<?php
/**
 */
class ContentFields {
    protected $message = null;

    static protected $plugin_id = '';
    public static function set_plugin_id($id) {self::$plugin_id = $id;}
    public static function get_plugin_id() {return self::$plugin_id;}
    static protected $plugin_info = array();
    public static function set_plugin_info(& $plugin_info) {self::$plugin_info = & $plugin_info;}

    protected static $want_css = false;
    public static function set_want_css($want = true) {self::$want_css = $want;}
    public static function is_want_css() {return self::$want_css;}

    public static function load_css() {
        // <style type="text/css">
        // </style>
    }

    public function ContentFields($message) {
        $this->message = $message;
    }

    public static function initialize() {
        if (!class_exists('Entity'))
            include(GSPLUGINPATH.ContentFields::get_plugin_id().'/Entity.php');
        if (!class_exists('ContentFields_item_list'))
            include(GSPLUGINPATH.ContentFields::get_plugin_id().'/ContentFields_item_list.php');
        if (!class_exists('ContentFields_item_entity'))
            include(GSPLUGINPATH.ContentFields::get_plugin_id().'/ContentFields_item_entity.php');
        /*
        include(GSPLUGINPATH.self::$plugin_id.'/Lists_message.php');
        include(GSPLUGINPATH.self::$plugin_id.'/Lists_storage.php');
        self::$storage = new Lists_storage();
        include(GSPLUGINPATH.self::$plugin_id.'/Lists_settings.php');
        self::$settings = Lists_settings::get_instance();
        self::$settings->read();
        */
    }

    private function get_filename($id) {
        return CONTENTFIELDS_DATAITEMSSPATH.$id.'.xml';
    }

    /**
     * read the ContentFields, from the $_REQUEST or from the storage
     * @param string/array $data the id of the contentstorage to be read from the file (string) or the data to be
     * read from the $_REQUEST (array)
     */
    public function read($data, $data_prefix) {
        $result = false;
        if (is_string($data)) {
            $filename = $this->get_filename($data);
            if (file_exists($filename)) {
                $list = getXML($filename);
                if (property_exists($list, 'settings')) {
                    $settings = $list->settings;
                    // debug('settings', $settings);
                    foreach (array('id', 'title', 'page_create', 'page_show') as $item) {
                        $setter = 'set_'.$item;
                        $this->item->$setter(property_exists($settings, $item) ? (string) $settings->$item : '');
                    }
                }
                if ($this->item->get_id() == $data) {
                    if (property_exists($list, 'field')) {
                        // debug('field', $list->field);
                    }
                    if (property_exists($list, 'entry')) {
                        // debug('entry', $list->entry);
                    }
                    $result = true;
                }
            }
        }

    }

    /**
     * @param ContentFields_item_entity $value 
     */
    public function write($prefix, $list = null) {
        if (is_null($list)) {
            $list = ContentFields_item_list::factory()->read($_REQUEST, CONTENTFIELDS_REQUEST_PREFIX);
            debug('list', $list);
        }
        if (!copy(CONTENTFIELDS_DATAITEM_PATH.$file, CONTENTFIELDS_BACKUP_FILE)) {
            $this->message->add_warning(i18n_r('CustomFields/BACKUP_FAILED'));
        }

        $data = new SimpleXMLExtended('<?xml version="1.0" encoding="UTF-8"?><fields></fields>');
        foreach ($list as $key => $value) {
                $item = $data->addChild('item');
                $item->addChild('order', $value->get_order());
                $item->addChild('name')->addCData(htmlspecialchars($value->get_name()));
                $item->addChild('label')->addCData(htmlspecialchars($value->get_label()));
                $item->addChild('type')->addCData(htmlspecialchars($value->get_type()));
                $item->addChild('value')->addCData(htmlspecialchars($value->get_value()));
                foreach ($value->get_options() as $key => $value)
                    $option = $item->addChild('option');
                    $option->addChild('key')->addCData(htmlspecialchars($key));
                    $option->addChild('value')->addCData(htmlspecialchars($value));
                // TODO: options should be an array of key => values in the entity!
                /*
                if ($_POST['cf_'.$i.'_options']) {
                    $options = preg_split("/\r?\n/", rtrim(stripslashes($_POST['cf_'.$i.'_options'])));
                    foreach ($options as $option) {
                        $item->addChild('option')->addCData(htmlspecialchars($option, ENT_QUOTES));
                    }
                }
                */
        }
        XMLsave($data, GSDATAOTHERPATH . IM_CUSTOMFIELDS_FILE);
        return true;
    }
    public function get_list() {
        $result = array();
        return $result;
    }

    public function render_admin_list() {
        $result = '';
        if (!class_exists('Template')) {
            include(GSPLUGINPATH.Lists::get_plugin_id().'/Template.php');
        }
        /*
        items_customfields_confline($i, $def, 'sortable');
        $i++;
        }
        items_customfields_confline($i, array(), 'hidden');
        */
        $field_row = array();
        $template = Template::factory();
        $i = 0; // TODO: is there a better way?
        foreach ($this->get_list() as $key => $value) {
            $field_row[] = $template->clear()->
                set('i', ++$i)->
                set('hidden', false)->
                set('name', $value['name'])->
                set('label', $value['label'])->
                set('type', $value['type'])->
                set('options_visible', in_array($value['type'], array('dropdown', 'wysiwyg')))->
                set('options', $value['options'])->
                set('value', $value['value'])->
                fetch(CONTENTFIELDS_TEMPLATE_PATH.'admin_item.php');
        }
        $field_row[] = $template->clear()->
            set('i', ++$i)->
            set('hidden', true)->
            set('name', '')->
            set('label', '')->
            set('type', '')->
            set('options_visible', false)->
            set('options', '')->
            set('value', '')->
            fetch(CONTENTFIELDS_TEMPLATE_PATH.'admin_item.php');
        $result = $template->clear()->
            set('field_rows', implode("\n", $field_row))->
            fetch(CONTENTFIELDS_TEMPLATE_PATH.'admin_list.php');
        return $result;
    }

    public static function get($file) {
        $result = array();
        if(!file_exists(CONTENTFIELDS_DATAITEMSSPATH.$file.'.xml')) {
		}
        return $result;
    }
}
