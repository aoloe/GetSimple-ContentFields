<?php
/**
 */
class ContentFields {
    protected $message = null;

    static protected $plugin_id = '';
    static public function set_plugin_id($id) {self::$plugin_id = $id;}
    static public function get_plugin_id() {return self::$plugin_id;}
    static protected $plugin_info = array();
    static public function set_plugin_info(& $plugin_info) {self::$plugin_info = & $plugin_info;}

    static protected $want_css = false;
    static public function set_want_css($want = true) {self::$want_css = $want;}
    static public function is_want_css() {return self::$want_css;}
    public static function load_css() {
        // <style type="text/css">
        // </style>
    }

    protected $list = null;

    public function ContentFields($message) {
        $this->message = $message;
    }

    public static function initialize() {
        if (!class_exists('Entity'))
            include(CONTENTFIELDS_PLUGIN_PATH.'Entity.php');
        if (!class_exists('ContentFields_item_list'))
            include(CONTENTFIELDS_PLUGIN_PATH.'ContentFields_item_list.php');
        if (!class_exists('ContentFields_item_entity'))
            include(CONTENTFIELDS_PLUGIN_PATH).'ContentFields_item_entity.php');
        /*
        include(CONTENTFIELDS_PLUGIN_PATH.'Lists_message.php');
        include(CONTENTFIELDS_PLUGIN_PATH.'Lists_storage.php');
        self::$storage = new Lists_storage();
        include(CONTENTFIELDS_PLUGIN_PATH.'Lists_settings.php');
        self::$settings = Lists_settings::get_instance();
        self::$settings->read();
        */
    }

    private function get_filename($id) {
        return CONTENTFIELDS_DATAITEM_PATH.$id.'.xml';
    }



    /**
     * read the ContentFields, from the $_REQUEST or from the storage
     * @param string/array $data the id of the contentstorage to be read from the file (string) or the data to be
     * read from the $_REQUEST (array)
     */
    public function read($data, $data_prefix = '') {
        $result = false;
        if (is_string($data)) {
            $filename = $this->get_filename($data);
            // debug('filename', $filename);
            if (file_exists($filename)) {
                $xml = getXML($filename);
                // debug('xml', $xml);
                // TODO: why does is_array() return false?
                if (property_exists($xml, 'item')/* && is_array($xml->item)*/) {
                    $this->list = ContentFields_item_list::factory();
                    // debug('list', $this->list);
                    foreach ($xml->item as $item) {
                        $content_field = ContentFields_item_entity::factory();
                        if (property_exists($item, 'name')) {
                            foreach (array('order', 'name', 'label', 'type') as $iitem) {
                                $setter = 'set_'.$iitem;
                                $content_field->$setter(property_exists($item, $iitem) ? (string) $item->$iitem : '');
                            }
                        }
                        // debug('option', $item->option);
                        if (property_exists($item, 'option')) {
                            foreach ($item->option as $iitem) {
                                $content_field->add_options((string)$iitem->key, (string)$iitem->value);
                            }
                        }
                        $this->list->add($content_field);
                    }
                }
            }
            // debug('this->list', $this->list);
            $result = true;
        }

    } // ContentFields::read()

    /**
     * @param string $prefix
     * @param ContentFields_item_list $list 
     */
    public function write($prefix, $list = null) {
        $result = false;
        if (is_null($list)) {
            $list = ContentFields_item_list::factory()->read($_REQUEST, CONTENTFIELDS_REQUEST_PREFIX);
            $this->list = $list;
            // debug('list', $list);
        }
        $filename = $this->get_filename($prefix);
        // debug('filename', $filename);
        if (file_exists($filename)) {
            if (!copy($filename, CONTENTFIELDS_BACKUP_DATAITEM)) {
                $this->message->add_warning(i18n_r('ContentFields/ERROR_BACKUPFAILED'));
            }
        }

        $data = new SimpleXMLExtended('<?xml version="1.0" encoding="UTF-8"?><fields></fields>');
        foreach ($list->get() as $key => $value) {
                $item = $data->addChild('item');
                $item->addChild('order', $value->get_order());
                $item->addChild('name')->addCData(htmlspecialchars($value->get_name()));
                $item->addChild('label')->addCData(htmlspecialchars($value->get_label()));
                $item->addChild('type')->addCData(htmlspecialchars($value->get_type()));
                foreach ($value->get_options() as $kkey => $vvalue) {
                    $option = $item->addChild('option');
                    $option->addChild('key')->addCData(htmlspecialchars($kkey));
                    $option->addChild('value')->addCData(htmlspecialchars($vvalue));
                }
        }
        // debug('saving to...', $filename);
        if (
            is_writable(
                file_exists($filename) ?
                $filename :
                CONTENTFIELDS_DATAITEM_PATH
            )
        ) {
            XMLsave($data, $filename);
            $result = true;
        } else {
            trigger_error("Cannot write ".$filename);
            $this->message->add_error(sprintf(i18n_r('ContentFields/ERROR_NOWRITEITEM')));
        }
        return $result;
    }
    public function get_list() {
        $result = array();
        return $result;
    }

    public function render_admin_list() {
        $result = '';
        if (!class_exists('Template')) {
            include(CONTENTFIELDS_PLUGIN_PATH.'Template.php');
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
        // debug('list', $this->list);
        if (isset($this->list)) {
            foreach ($this->list->get() as $key => $value) {
                $field_row[] = $template->clear()->
                    set('i', ++$i)->
                    set('hidden', false)->
                    set('name', $value->get_name())->
                    set('label', $value->get_label())->
                    set('type', $value->get_type())->
                    set('options_visible', in_array($value->get_type(), array('dropdown', 'wysiwyg')))->
                    set('options', $value->get_options_as_csv())->
                    set('value', $value->get_value())->
                    fetch(CONTENTFIELDS_TEMPLATE_PATH.'admin_item.php');
            }
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
