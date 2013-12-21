<?php /*
<form class="largeform" action="load.php?id=<?= $plugin_id ?>&<?= $plugin_id ?>_edit=edit&<?= $plugin_id ?>_id=<?= $plugin_item_id ?>" method="post" accept-charset="utf-8">
TODO: find a clean way to avoid to have to pass the id as _GET
*/ ?>
<form class="largeform" action="load.php?id=<?= $plugin_id ?>" method="post" accept-charset="utf-8">
<?php // TODO: add the id for edits and make sure that the contentfields can't clash with the id and the buttons! ?>
<?= $list_hidden ?>
<table>
    <?php foreach ($list_field as $item) : ?>
    <tr class="user_sub_tr">
        <?= $item ?>
    </tr>
    <?php endforeach; ?>
</table>
<div id="submit_line" >
    <span>
        <input class="submit" type="submit" name="save" value="<?= i18n_r('SAVE') ?>" />
    </span>

    <?php // TODO: do a more general implementation of the additional actions and maybe put it in GS_UI ?>
    <?php // TODO: why does it not use the right styling? fix the css...?>
    <div id="dropdown">
        <h6 class="dropdownaction"><?= i18n_r('ADDITIONAL_ACTIONS') ?></h6>
        <ul class="dropdownmenu">
            <li id="save-close" ><a href="#" ><?= i18n_r('SAVE_AND_CLOSE') ?></a></li>
            <li><a href="load.php?id=<?= $plugin_id ?>&amp;action=clone" ><?= i18n_r('CLONE') ?></a></li>
            <li id="cancel-updates" class="alertme"><a href="load.php?cancel" ><?= i18n_r('CANCEL'); ?></a></li>
            <?php if (!empty($id)) : // TODO: not implemented yet ?>
            <li class="alertme" ><a href="load.php?id=<?= $plugin_id ?>" ><?= i18n_r('DELETE') ?></a></li>
            <?php endif; ?>
        </ul>
    </div>
</div>

</form>
