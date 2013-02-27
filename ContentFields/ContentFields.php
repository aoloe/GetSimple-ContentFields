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

    private function get_filename($id) {
        return CONTENTFIELDDS_DATAITEMSSPATH.$id.'.xml';
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
    public function write($field, $file) {
        // XXX: what to do if it can't create a backup? adding a message seems to be a better idea than failing!
        if (!copy(GSDATAOTHERPATH . IM_CUSTOMFIELDS_FILE, GSBACKUPSPATH . 'other/' . IM_CUSTOMFIELDS_FILE)) return false;

        $data = new SimpleXMLExtended('<?xml version="1.0" encoding="UTF-8"?><fields></fields>');
        for ($i=0; isset($_POST['cf_'.$i.'_key']); $i++) {
            if ($_POST['cf_'.$i.'_key']) {
                $item = $data->addChild('item');
                $item->addChild('desc')->addCData(htmlspecialchars(stripslashes($_POST['cf_'.$i.'_key']), ENT_QUOTES));
                $item->addChild('label')->addCData(htmlspecialchars(stripslashes($_POST['cf_'.$i.'_label']), ENT_QUOTES));
                $item->addChild('type')->addCData(htmlspecialchars(stripslashes($_POST['cf_'.$i.'_type']), ENT_QUOTES));
                if ($_POST['cf_'.$i.'_value']) {
                    $item->addChild('value')->addCData(htmlspecialchars(stripslashes($_POST['cf_'.$i.'_value']), ENT_QUOTES));
                }
                if ($_POST['cf_'.$i.'_options']) {
                    $options = preg_split("/\r?\n/", rtrim(stripslashes($_POST['cf_'.$i.'_options'])));
                    foreach ($options as $option) {
                        $item->addChild('option')->addCData(htmlspecialchars($option, ENT_QUOTES));
                    }
                }
            }
        }
        XMLsave($data, GSDATAOTHERPATH . IM_CUSTOMFIELDS_FILE);
        return true;
    }
    public static function get($file) {
        $result = array();
        if(!file_exists(CONTENTFIELDDS_DATAITEMSSPATH.$file.'.xml')) {
		}
        return $result;
    }
}
