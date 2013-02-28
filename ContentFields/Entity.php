<?php
class Entity {
    protected $__entity_array_associative = array();
    public function get_entity_array_flat($array, $a_key = null) {
        $result = array();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (in_array($a_key, $this->__entity_array_associative)) {
                    foreach($value as $vvalue) {
                        $result[] = $key;
                        $result[] = $vvalue;
                    }
                } else {
                    $result = array_merge($result, $this->get_entity_array_flat($value, $key));
                }
            } elseif (is_object($value)) {
                $result[] = $value->get_string();
            } else {
                if (in_array($a_key, $this->__entity_array_associative)) {
                    $result[] = $key;
                }
                $result[] = $value;
            }
        }
        return $result;
    }
    protected function __get_object_vars() {
        $result = array();
        $result = get_object_vars($this);
        foreach ($result as $key => $value) {if (substr($key, 0, 2) == '__') unset($result[$key]);}
        return $result;
    }
    public function __call($method, $args) {
        $parameter = substr($method, strpos($method, '_') + 1);
        if (substr($method, 0, 4) == 'get_') {
            if (isset($this->$parameter)) {
                return $this->$parameter;
            } elseif ($method == 'get_array') {
                return $this->__get_object_vars();
            } elseif ($method == 'get_csv') {
                return str_putcsv($this->get_entity_array_flat(array_values($this->__get_object_vars())));
            }
        } elseif (substr($method, 0, 4) == 'set_') {
            if (isset($this->$parameter)) {
                $this->$parameter = $args[0];
                return $this;
            }
        } elseif (substr($method, 0, 4) == 'add_') {
            if (isset($this->$parameter) && is_array($this->$parameter)) {
                if (count($args) == 1) {
                    $this->{$parameter}[] = $args[0];
                    return $this;
                } elseif (count($args) == 2) {
                    $this->__entity_array_associative[$parameter] = true;
                    if (array_key_exists($args[0], $this->{$parameter})) {
                        $this->{$parameter}[$args[0]] = array($this->{$parameter}[$args[0]]);
                        $this->{$parameter}[$args[0]][] = $args[1];
                    } else {
                        $this->{$parameter}[$args[0]] = $args[1];
                    }
                    return $this;
                }
            }
        } elseif (substr($method, 0, 3) == 'is_') {
            if (!isset($this->$parameter)) {
                return false;
            } elseif (is_bool($this->$parameter)) {
                return $this->$parameter;
            } elseif (count($args) == 1) {
                return $this->$parameter == $args[0];
            } elseif (count($args) == 2) {
                switch ($args[1]) {
                    case '>' :
                        return $this->$parameter > $args[1];
                    break;
                    case '<' :
                        return $this->$parameter < $args[1];
                    break;
                }
            }
        }
        debug('method is not defined',  $method, true); die();
    } // Entity::__call()
    public function read($e, $prefix = '') {
        foreach ($this->__get_object_vars() as $key => $value) {
            $e_key = $prefix.$key;
            if (array_key_exists($e_key, $e)) {
                if (method_exists($this, 'read_'.$key)) {
                    $method = 'read_'.$key;
                    $this->$method($e[$e_key]);
                } else if (
                    (is_array($this->{$key}) && is_array($e[$e_key])) ||
                    (is_int($this->{$key}) && is_numeric($e[$e_key])) ||
                    (is_bool($this->{$key}) && is_numeric($e[$e_key])) ||
                    (is_string($this->{$key}) && is_string($e[$e_key]))
                ) {
                    $this->{$key} = $e[$e_key];
                    if (is_array($e[$e_key]) && !is_int(key($e[$e_key]))) {
                        $this->__entity_array_associative[$key] = true;
                    }

                }
            }
        }
        return $this;
    } // Entity::read()
} // Entity

