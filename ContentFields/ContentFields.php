<?php
/**
 */
class ContentFields {

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

    public function ContentFields() {
    }

    public static function initialize() {
        if (!class_exists('ContentFields_list'))
            include(CONTENTFIELDS_PLUGIN_PATH.'ContentFields_list.php');
        if (!class_exists('ContentFields_entity'))
            include(CONTENTFIELDS_PLUGIN_PATH.'ContentFields_entity.php');
        /*
        include(CONTENTFIELDS_PLUGIN_PATH.'Lists_message.php');
        include(CONTENTFIELDS_PLUGIN_PATH.'Lists_storage.php');
        self::$storage = new Lists_storage();
        include(CONTENTFIELDS_PLUGIN_PATH.'Lists_settings.php');
        self::$settings = Lists_settings::get_instance();
        self::$settings->read();
        */
    }

    private function get_filename($id, $path) {
        return $path.$id.'.xml';
    }



    /**
     * read the ContentFields, from the $_REQUEST or from the storage
     * @param string/array $data the id of the contentstorage to be read from the file (string) or the data to be
     * read from the $_REQUEST (array)
     */
    public function read($data, $data_prefix = '') {
        $result = false;
        if (is_string($data)) {
            $filename = $this->get_filename($data, CONTENTFIELDS_DATAITEM_PATH);
            // GS_debug('filename', $filename);
            if (file_exists($filename)) {
                $xml = getXML($filename);
                // GS_debug('xml', $xml);
                // TODO: why does is_array() return false?
                if (property_exists($xml, 'item')/* && is_array($xml->item)*/) {
                    $this->list = ContentFields_list::factory();
                    // GS_debug('list', $this->list);
                    foreach ($xml->item as $item) {
                        $content_field = ContentFields_entity::factory();
                        if (property_exists($item, 'name')) {
                            foreach (array('order', 'name', 'label', 'type') as $iitem) {
                                $setter = 'set_'.$iitem;
                                $content_field->$setter(property_exists($item, $iitem) ? (string) $item->$iitem : '');
                            }
                        }
                        // GS_debug('option', $item->option);
                        if (property_exists($item, 'option')) {
                            foreach ($item->option as $iitem) {
                                $content_field->add_options((string)$iitem->key, (string)$iitem->value);
                            }
                        }
                        $this->list->add($content_field);
                    }
                }
            }
            // backtrace();
            // GS_debug('this->list', $this->list);
            $result = true;
        } elseif (is_array($data)) {
            // only read the values for the content fields from the array (which is mostly _REQUEST)
            // GS_debug('data_prefix', $data_prefix);
            // GS_debug('list', $this->list);
            // GS_debug('data', $data);
            foreach ($this->list->get() as $item) {
                $field = $data_prefix.$item->get_name();
                if (array_key_exists($field, $data)) {
                    $item->set_value($data[$field]);
                }
            }
            $result = true;
        }

    } // ContentFields::read()

    /**
     * @param string $prefix
     * @param ContentFields_list $list 
     */
    public function write($prefix, $list = null) {
        $result = false;
        if (is_null($list)) {
            $list = ContentFields_list::factory()->read($_REQUEST, CONTENTFIELDS_REQUEST_PREFIX);
            $this->list = $list;
            // GS_debug('list', $list);
        }
        $filename = $this->get_filename($prefix, CONTENTFIELDS_DATAITEM_PATH);
        // GS_debug('filename', $filename);
        if (file_exists($filename)) {
            if (!copy($filename, CONTENTFIELDS_BACKUP_DATAITEM)) {
                GS_Message::get_instance()->add_warning(i18n_r('ContentFields/ERROR_BACKUPFAILED'));
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
        // GS_debug('saving to...', $filename);
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
            GS_Message::get_instance()->add_error(sprintf(i18n_r('ContentFields/ERROR_NOWRITEITEM')));
        }
        return $result;
    }

    /**
     * @param string $id the name of the list
     * @return SimpleXMLExtended
     */
    public function get_content_xml($id) {
        $result = null;
        // GS_debug('id', $id);
        $filename = $this->get_filename($id, CONTENTFIELDS_DATAENTRY_PATH);
        GS_debug('filename', $filename);
        if (file_exists($filename)) {
            $result = getXML($filename);
        } else {
            $result = new SimpleXMLExtended('<?xml version="1.0" encoding="UTF-8"?><content></content>');
        }
        // GS_debug('result', $result);
        return $result;
    }

    public function get_content($id, $prefix = null) {
        $result = array();
        // GS_debug('id', $id);
        // GS_backtrace();
        if (is_array($id)) {
            $result = array(
                'id' => 0,
                'item' => array(),
            );
            if (array_key_exists(CONTENTFIELDS_REQUEST_FIELD_PREFIX.'_id', $id)) {
                $result['id'] = $id[CONTENTFIELDS_REQUEST_FIELD_PREFIX.'_id'];
            } else {
                $result['id'] = uniqid(); // TODO: check if it's unique enough and there is no better way to do it
            }
            if (isset($prefix)) {
                $prefix_length = strlen($prefix);
                // GS_debug('prefix_length', $prefix_length);
                foreach ($id as $key => $value) {
                    $kkey = substr($key, 0, $prefix_length);
                    // GS_debug('kkey', $kkey);
                    if ($kkey == $prefix) {
                        $result['item'][substr($key, $prefix_length)] = $value;
                    }
                }
            }
            GS_debug('result', $result);
        } else {
            $xml = $this->get_content_xml($id);
            GS_debug('xml', $xml);
            // TODO: check if xml is an array with multiple items and an object with one item or anything else
            if (property_exists('item', $xml) && property_exists('id', $xml->item) && property_exists('content', $xml->item)) {
                $result[$xml->item->get_id()] = array(
                    'id' => 0,
                    'item' => array(),
                );
            }
            
        }
        return $result;
    }

    public function set_item($content, $item) {
        $content[$item['id']] = $item;
    }

    /**
     * @param string $id the name of the list
     * @param SimpleXMLExtended $data
     */
    public function write_content_xml($id, $data) {
        $filename = $this->get_filename($id, CONTENTFIELDS_DATAENTRY_PATH);
        GS_debug('filename', $filename);
        if (file_exists($filename)) {
            if (!copy($filename, CONTENTFIELDS_BACKUP_DATAENTRY)) {
                GS_Message::get_instance()->add_warning(i18n_r('ContentFields/ERROR_BACKUPFAILED'));
            }
        }
    }

    /**
     * @param string $id the name of the list
     * @param array $content the items in the list
     */
    public function write_content($id, $content) {
        GS_backtrace();
        GS_debug('list', $this->list);

        // $data = $this->get_content($id);
        // if $this->list->get() has an id, then try first to update the element in the current data structure. if it fails or if there is no id, append the item

        GS_debug('id', $id);
        GS_debug('content', $content);
        $data_list = $data->addChild('list')->addCData(htmlspecialchars($id));
        foreach ($this->list->get() as $item) {
            GS_debug('item', $item);
            $data_item = $data->addChild('item');
            $data_item->addChild('name')->addCData(htmlspecialchars($item->get_name()));
            $value = $item->get_value();
            // GS_debug('value', $value);
            if (is_string($value)) {
                $data_item->addChild('value')->addCData(htmlspecialchars($value));
            } elseif (is_array($value)) {
                foreach ($value as $iitem) {
                    $data_item->addChild('value')->addCData(htmlspecialchars($iitem));
                }
            }
            /*
            foreach ($value->get_options() as $kkey => $vvalue) {
                $option = $item->addChild('option');
                $option->addChild('key')->addCData(htmlspecialchars($kkey));
                $option->addChild('value')->addCData(htmlspecialchars($vvalue));
            }
            */
        }
        // TODO: each item should have a (automatic defined) id. should it be defined when creating the form for a new item? or when reading the form and noticing that there is no id? or when storing and there is no id?
        /*
<item>
  <id>asfjasjfklasjfdkl</id>
  <content>
    <abc>def</abc>
  </content>
</item>
<item>
  <id>akldsjfsjdfiopjiou</id>
  <content>
    <abc>cba</abc>
  </content>
</item>
         */
        GS_debug('data', $data);
        GS_debug('id', $id);
        $filename = CONTENTFIELDS_DATAENTRY_PATH.$id.".xml";
        GS_debug('saving to...', $filename);
        GS_backtrace();
        if (
            is_writable(
                file_exists($filename) ?
                $filename :
                CONTENTFIELDS_DATAENTRY_PATH
            )
        ) {
            XMLsave($data, $filename);
            $result = true;
        } else {
            trigger_error("Cannot write ".$filename);
            GS_Message::get_instance()->add_error(sprintf(i18n_r('ContentFields/ERROR_NOWRITEITEM')));
        }
    }

    public function render_admin_list() {
        $result = '';
        /*
        items_customfields_confline($i, $def, 'sortable');
        $i++;
        }
        items_customfields_confline($i, array(), 'hidden');
        */
        $field_row = array();
        $template = GS_Template::factory();
        $i = 0; // TODO: is there a better way?
        // GS_debug('list', $this->list);
        if (isset($this->list)) {
            foreach ($this->list->get() as $key => $value) {
                // GS_debug('value', $value);
                $field_row[] = $template->clear()->
                    set('i', ++$i)->
                    set('hidden', false)->
                    set('name', $value->get_name())->
                    set('label', $value->get_label())->
                    set('type', $value->get_type())->
                    set('options_visible', in_array($value->get_type(), array('dropdown', 'checkbox', 'wysiwyg')))->
                    set('options', $value->get_options_as_csv())->
                    set('default_value', $value->get_default_value())->
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
            set('default_value', '')->
            fetch(CONTENTFIELDS_TEMPLATE_PATH.'admin_item.php');
        $result = $template->clear()->
            set('field_rows', implode("\n", $field_row))->
            fetch(CONTENTFIELDS_TEMPLATE_PATH.'admin_list.php');
        return $result;
    }

    public function get() {
        // GS_backtrace();
        return isset($this->list) ? $this->list->get() : array();
    }
}
