          <tr class="<?= ($hidden ? 'hidden' : 'sortable') ?>">
              <td><input type="text" class="text" style="width:80px;padding:2px;" name="<?= CONTENTFIELDS_REQUEST_PREFIX.$i ?>_name" value="<?= $name ?>"/></td>
              <td><input type="text" class="text" style="width:140px;padding:2px;" name="<?= CONTENTFIELDS_REQUEST_PREFIX.$i ?>_label" value="<?= $label ?>"/></td>
              <td>
                  <select name="<?= CONTENTFIELDS_REQUEST_PREFIX.$i ?>_type" class="text short" style="width:180px;padding:2px;" >
                  <?php foreach (array('text', 'longtext', 'dropdown', 'checkbox', 'wysiwyg', 'hidden', 'uploader') as $item) : ?>
                      <option value="<?= $item ?>"<?= ($type == $item ? ' selected="selected"' : ''); ?> ><?php i18n('ContentFields/FORM_'.strtoupper($item).'_LABEL'); ?></option>
                  <?php endforeach; ?>
                  </select>
                  <textarea class="text" style="width:170px;height:50px;padding:2px;<?= $options_visible ? '' : 'display:none' ?>" name="<?= CONTENTFIELDS_REQUEST_PREFIX.$i ?>_options"><?= $options ?></textarea>
              </td>
              <td><input type="text" class="text" style="width:100px;padding:2px;" name="<?= CONTENTFIELDS_REQUEST_PREFIX.$i ?>_value" value="<?= $value ?>"/></td>
              <td class="delete"><a href="#" class="delete" title="<?php i18n('ContentFields/FORM_DELETE'); ?>">X</a></td>
          </tr>
