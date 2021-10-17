<?php
if (!$this->access(7)) {
    die('forbidden');
}

if (!isset($_POST['page_id'], $_POST['type'])) {
    $this->error(1);
}
$page_id = $_POST['page_id'];
if (!uString::isDigits($page_id)) {
    $this->error(2);
}

if (
    !($query = $this->query(
        "pages",
        "SELECT DISTINCT
    `u235_ublocks_list`.`block_id`,
    `u235_ublocks_list`.`block_title`,
    `u235_ublocks_list`.`block_html`,
    `u235_ublocks_list`.`block_pos`
    FROM
    `u235_ublocks_pages`,
    `u235_ublocks_list`
    WHERE
    `u235_ublocks_list`.`site_id`='" .
            site_id .
            "' AND
    `u235_ublocks_pages`.`site_id`='" .
            site_id .
            "' AND
    `u235_ublocks_pages`.`page_id`='" .
            $page_id .
            "' AND
    `u235_ublocks_list`.`block_id`=`u235_ublocks_pages`.`block_id`
    ORDER BY
    `u235_ublocks_list`.`block_title` ASC
    "
    ))
) {
    $this->error(3);
}

if ($_POST['type'] == 'unattached') {
    while ($data = $query->fetch_assoc()) {
        $attached[$data['block_id']] = 1;
    }
    if (
        !($query = $this->query(
            "pages",
            "SELECT
    `u235_ublocks_list`.`block_id`,
    `u235_ublocks_list`.`block_title`,
    `u235_ublocks_list`.`block_html`,
    `u235_ublocks_list`.`block_pos`
    FROM
    `u235_ublocks_list`
    WHERE
    `u235_ublocks_list`.`site_id`='" .
                site_id .
                "'
    ORDER BY
    `u235_ublocks_list`.`block_title` ASC
    "
        ))
    ) {
        $this->error(4);
    }
}
?>
<table class="table table-condensed table-striped">
<?
if($_POST['type']=='unattached') {
    while($data=$query->fetch_assoc()) {
        if(isset($attached[$data['block_id']])) continue;
        $block_title=uString::sql2text($data['block_title'],1);?>
        <tr>
            <td><button class="btn btn-xs btn-default uTooltip" title="Редактировать вставку" onclick="uEditor.edit_block_init(<?= $data[
                'block_id'
            ] ?>)"><span class="glyphicon glyphicon-pencil"></span></button></td>
            <td><button class="btn btn-xs btn-danger uTooltip" title="Удалить вставку" onclick="uEditor.delete_block_init(<?= $data[
                'block_id'
            ] ?>)"><span class="glyphicon glyphicon-remove"></span></button></td>
            <td><?= $block_title ?></td>
            <td><button class="btn btn-xs btn-success" onclick="uEditor.attachBlock(<?= $data[
                'block_id'
            ] ?>,<?= $page_id ?>);"><span class="glyphicon glyphicon-plus"></span> Вставить</button></td>
        </tr>
    <?}
}
else {
    while($data=$query->fetch_assoc()) {
        $block_title=uString::sql2text($data['block_title'],1);?>
        <tr>
            <td><button class="btn btn-xs btn-default uTooltip" title="Редактировать вставку" onclick="uEditor.edit_block_init(<?= $data[
                'block_id'
            ] ?>)"><span class="glyphicon glyphicon-pencil"></span></button></td>
            <td><button class="btn btn-xs btn-danger uTooltip" title="Удалить вставку" onclick="uEditor.delete_block_init(<?= $data[
                'block_id'
            ] ?>)"><span class="glyphicon glyphicon-remove"></span></button></td>
            <td><?= $block_title ?></td>
            <td><button class="btn btn-xs btn-danger" onclick="uEditor.detachBlock(<?= $data[
                'block_id'
            ] ?>,<?= $page_id ?>);"><span class="glyphicon glyphicon-minus"></span> Убрать</button>
            </td>
        </tr>
    <?}
}?>
</table>
