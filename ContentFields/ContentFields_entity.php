<?php

class ContentFields_entity extends GS_Entity {
    protected $order = '';
    protected $name = '';
    protected $label = '';
    protected $type = '';
    protected $options = array();
    protected $default_value = '';
    protected $value = ''; // only for rendering; should not be stored in the fields list file

    static public function factory() {
        return new ContentFields_entity();
    }

    public function get_options_as_csv() {
        $result = '';
        $i = 0;
        // debug('options', $this->options);
        if (!empty($this->options)) {
            $list = array();
            foreach ($this->options as $key => $value) {
                if (is_numeric($key) && ($key == $i++)) {
                    $list[] = $value;
                } else {
                    $list[] = str_putcsv(array($key, trim($value)));
                }
            }
            // debug('list', $list);
            $result = implode("\n", $list);
            // debug('result', $result);
        }
        return $result;
    }
    public function read_options($options) {
        // debug('options', $options);
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

    public function render($data_prefix = '') {
        $result = '';
        $value = isset($this->value) ? $this->value : $this->default_value;
        $field = '';
        $field_name = $data_prefix.$this->name;
        $attribute_value = null;
        if (is_string($value)) {
            $attribute_value = ($value == '' ? '' : ' value="'.$value.'"');
        }
        $width = 1; // TODO: set the width as a property so that it can be queried by the "form builder"
        // debug('type', $this->type);
        switch ($this->type) {
			case 'text':
				$field = '<input class="text shorts" type="text" id="'.$field_name.'" name="'.$field_name.'"'.$attribute_value.' />';
  			break;
            case "hidden":
                $width = 0;
                $field = '<input type="hidden" id="'.$field_name.'" name="'.$field_name.'"'.$attribute_value.' />'; 
            break;
            case 'longtext' :
            $width = 2;
            $field = '<input class="text" type="text" style="width:533px;" id="'.$field_name.'" name="'.$field_name.'"'.$attribute_value.' />';
            break;
            // TODO: add radio buttons
            case 'dropdown' :
				$field = '<select id="'.$field_name.'" name="'.$field_name.'" class="text shorts">'."\n";
                foreach ($this->options as $key => $value) {
                  $field .= '<option value="'.$key.'"'.($this->value == $key ? ' selected="selected"' : '').'>'.$value.'</option>'."\n";
                }
                $field .= '</select>';
            break;
            case 'checkbox':
                // debug('options', $this->options);
                if (empty($this->options)) {
                    $field = '<input type="checkbox" class="checkp" id="'.$field_name.'" name="'.$field_name.'" value="'.$this->default_value.'" '.($value ? 'checked="checked"' : '').'/>';
                } else {
                    // debug('value', $value);
                    $field_item = array();
                    foreach ($this->options as $kkey => $vvalue) {
                        $field_item[] = '<input type="checkbox" class="checkp" id="'.$field_name.'" name="'.$field_name.'[]" value="'.$kkey.'" '.(!empty($value) && in_array($kkey, $value) ? 'checked="checked"' : '').'/>'.($vvalue == '' ? i18n_r('ContentFields/FORM_OPTION_NOVALUE') : $vvalue);
                    }
                    $field = implode("<br />\n", $field_item);
                }
            break;
            case "wysiwyg":
                $options = array();
                $toolbar = '';
                // debug('options', $this->options);
                if (!empty($this->options)) {
                    parse_str(html_entity_decode(implode(' ', $this->options)), $options);
                }
                // debug('options', $options);
                if (array_key_exists('toolbar', $options))
                {
                    if ($options['toolbar'] == "basic") {
                        $toolbar = "[".CONTENTFIELDS_FIELD_TEXTAREA_TOOLBAR_BASIC."]";
                    } elseif ($options['toolbar'] == "advanced") {
                        $toolbar = "[".CONTENTFIELDS_FIELD_TEXTAREA_TOOLBAR_ADVANCED."]";
                    } elseif ($options['toolbar'] != "") {
                        $toolbar = "['".implode("','", explode(',', $options['toolbar']))."']";
                    }
                }
                $height = (array_key_exists('height', $options) ? $options['height'] : CONTENTFIELDS_FIELD_TEXTAREA_HEIGHT);
                $no_paragraph =  (array_key_exists('no-paragraph', $options) || CONTENTFIELDS_FIELD_TEXTAREA_NOPARAGRAPH);
                $field = '<textarea id="'.$field_name.'" name="'.$field_name.'" class="content_fields_edit" style="height:'.$height.';">'.$value.'</textarea>';
                if ($toolbar != "") :
                    // TODO: ticket for asking to add ckeditor to the assets
                    // debug('$GS_script_assets', $GS_script_assets);
                    // ./admin/inc/plugin_functions.php:	register_script('jquery', $GS_script_assets['jquery']['local']['url'], $GS_script_assets['jquery']['local']['ver'], FALSE);
                    // TODO: here it's too late to register a script! the header has already been echoed
                    // register_script('ckeditor', SITEURL."template/js/ckeditor/ckeditor.js", '3.6.2');
                    // TODO: find a good way to ask if the editor is already registered and if it is not, register and output the link now
                    // What is the role of register_script and load_script?
                    // TODO: check what EDLANG and GSEDITORLANG are, if they are supposed to be used and how to get them to be set. All in all: how to correctly get the editor's language?
                    GS_UI::load_javascript_library('ckeditor', GS_UI_JAVASCRIPT_LIBRARY_LOAD_NOW);
                    ?>
                    <script type="text/javascript" src="template/js/ckeditor/ckeditor.js"></script>
                    <script type="text/javascript">
                      // missing border around text area, too much padding on left side, ...
                      // TODO: the code below should be dymamically generated by GS... the loading above, too
                      $(function() {
                        CKEDITOR.replace( '<?= $field_name ?>', {
                                skin : 'getsimple',
                                forcePasteAsPlainText : false,
                                language : '<?= GSEDITORLANG ?>',
                                defaultLanguage : '<?= GSEDITORLANG ?>',
                                entities : false,
                                uiColor : '#FFFFFF',
                                height: '<?php echo $height; ?>',
                                baseHref : '<?= SITEURL ?>',
                                toolbar : <?= $toolbar ?>,
                                <?= ($no_paragraph ? "enterMode : CKEDITOR.ENTER_BR,\n" : "") ?>
                                <?= '' // $EDOPTIONS; */ ?>
                                filebrowserBrowseUrl : 'filebrowser.php?type=all',
                                filebrowserImageBrowseUrl : 'filebrowser.php?type=images',
                                filebrowserWindowWidth : '730',
                                filebrowserWindowHeight : '500'
                        })
                      });
                    </script>
                    <?php
                endif; // if item_toolbar != []
            break;
        }
        if ($width == 0) {
            $result = $field;
        } else {
            $result = "<td".($width == 2 ? ' colspan="2"' : '')."><strong>".$this->label."</strong><br />\n".$field."</td>";
        }
        return $result;
    }

    public function read($e, $prefix = '') {
        return parent::read($e, $prefix);
        /*
        if ($this->is_id("") && !$this->is_title("")) {
            $id = preg_replace('/^[A-Za-z0-9]+$/', '', $this->get_title());
            while ($id == "" || array_key_exists($id, $this->list)) {
                $id = "";
            }
            $this->item->set_id($id);
        }
        */
    }
}
