<table id="ContentFieldsList" class="edittable highlight">
    <thead>
        <tr>
            <th><?php i18n('ContentFields/FORM_COLUMN_NAME'); ?></th>
            <th><?php i18n('ContentFields/FORM_COLUMN_LABEL'); ?></th>
            <th style="width:100px;"><?php i18n('ContentFields/FORM_COLUMN_TYPE'); ?></th>
            <th><?php i18n('ContentFields/FORM_COLUMN_DEFAULT_VALUE'); ?></th>
            <th></th>
      </tr>
    </thead>
    <tbody>
    <?= $field_rows ?>
  <tr>
    <td colspan="4"><a href="#" class="add"><?php i18n('ContentFields/FORM_ADD'); ?></a></td>
    <td class="secondarylink"><a href="#" class="add" title="<?php i18n('ContentFields/FORM_ADD'); ?>">+</a></td>
  </tr>
</tbody>
</table>

<?php
// TODO: put this javascript code at the right place...
?>
<script type="text/javascript">
<?php
// TODO: check if this renumbering does the right thing... or if it's the better way to do this
?>
  function renumberCustomFields() {
    $('#ContentFieldsList tbody tr').each(function(i,tr) {
      $(tr).find('input, select, textarea').each(function(k,elem) {
        var name = $(elem).attr('name').replace(/_\d+_/, '_'+(i)+'_');
        $(elem).attr('name', name);
      });
    });
  }
  $(function() {
    $('select[name$=_type]').change(function(e) {
      var val = $(e.target).val();
      var $ta = $(e.target).closest('td').find('textarea');
      if ((val == 'dropdown') || (val == 'wysiwyg')) $ta.css('display','inline'); else $ta.css('display','none');
    });
    $('a.delete').click(function(e) {
      $(e.target).closest('tr').remove();
      renumberCustomFields();
    });
    $('a.add').click(function(e) {
      var $tr = $(e.target).closest('tbody').find('tr.hidden');
      $tr.before($tr.clone(true).removeClass('hidden').addClass('sortable'));
      renumberCustomFields();
    });
    $('#ContentFieldsList tbody').sortable({
      items:"tr.sortable", handle:'td',
      update:function(e,ui) { renumberCustomFields(); }
    });
    renumberCustomFields();
  });
</script>
