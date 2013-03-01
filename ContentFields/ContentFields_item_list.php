<?php

class ContentFields_item_list {
    protected $list = array();

    static public function factory() {
        return new ContentFields_item_list();
    }

    public function get() {
        return $this->list;
    }

    public function add($a, $b = null) {
        if (is_null($b)) {
            $this->list[] = $a;
        } else {
            $this->list[$a] = $b;
        }
        return $this;
    }

    public function read($e, $prefix) {
        $i = 0;
        // debug('key', CONTENTFIELDS_REQUEST_PREFIX.$i.'_name');
        for ($i = 0; array_key_exists(CONTENTFIELDS_REQUEST_PREFIX.$i.'_name', $_REQUEST); $i++) {
            $entity = ContentFields_item_entity::factory()->read($_REQUEST, CONTENTFIELDS_REQUEST_PREFIX.$i.'_');
            if (!$entity->is_name('')) {
                $entity->set_order($i);
                $this->list[] = $entity;
            }

        }
        // debug('list', $this->list);
        return $this;
    }
}
