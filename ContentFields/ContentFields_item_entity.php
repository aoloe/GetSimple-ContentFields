<?php

class ContentFields_item_entity extends Entity {
    protected $order = '';
    protected $name = '';
    protected $label = '';
    protected $type = '';
    protected $options = array();
    protected $value = '';

    static public function factory() {
        return new ContentFields_item_entity();
    }

    public function read_options($options) {
        debug('options', $options);
        if (is_array($options)) {
            $this->options = $options;
        } elseif (!empty($options)) {
            foreach (explode("\n", $options) as $value) {
                $value = str_getcsv($value);
                if (count($value) == 0) { // default value
                    $this->options[0] = '';
                } elseif (count($value) == 1) {
                    $this->options[] = $value[0];
                } else {
                    $this->options[$value[0]] = $value[1];
                }
            }
        }
    }

    /*
    public function read($e, $prefix = '') {
        parent::read($e, $prefix);
        if ($this->is_id("") && !$this->is_title("")) {
            $id = preg_replace('/^[A-Za-z0-9]+$/', '', $this->get_title());
            while ($id == "" || array_key_exists($id, $this->list)) {
                $id = "";
            }
            $this->item->set_id($id);
        }
    }
    */
}
