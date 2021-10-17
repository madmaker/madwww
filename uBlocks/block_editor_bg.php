<?php
class uBlocks_block_editor
{
    private $uCore;
    public $block_id, $block_title, $block_html, $block_pos, $q_pos;
    private function check_data()
    {
        if (!isset($_POST['block_id'])) {
            $this->uCore->error(1);
        }
        $this->block_id = $_POST['block_id'];
        if (!uString::isDigits($this->block_id)) {
            $this->uCore->error(2);
        }
    }
    private function get_block_data()
    {
        if (
            !($query = $this->uCore->query(
                "pages",
                "SELECT
        `block_title`,
        `block_html`,
        `block_pos`
        FROM
        `u235_ublocks_list`
        WHERE
        `block_id`='" .
                    $this->block_id .
                    "' AND
        `site_id`='" .
                    site_id .
                    "'
        "
            ))
        ) {
            $this->uCore->error(3);
        }
        if (!mysqli_num_rows($query)) {
            $this->uCore->error(4);
        }
        $block = $query->fetch_object();
        $this->block_title = uString::sql2text($block->block_title, 1);
        $this->block_html = uString::sql2text($block->block_html, 1);
        $this->block_pos = $block->block_pos;
    }
    private function get_blocks_pos()
    {
        if (
            !($this->q_pos = $this->uCore->query(
                "pages",
                "SELECT
        `pos_id`,
        `pos_title`
        FROM
        `u235_uBlocks_positions`
        WHERE
        `site_id`='" .
                    site_id .
                    "'
        "
            ))
        ) {
            $this->uCore->error(5);
        }
    }
    function __construct(&$uCore)
    {
        $this->uCore = &$uCore;
        if (!$this->uCore->access(7)) {
            die("{'status' : 'forbidden'}");
        }

        $this->check_data();

        $this->get_block_data();
        $this->get_blocks_pos();
    }
}
$uBlocks = new uBlocks_block_editor($this);
?>
<input type="hidden" id="uEditor_block_edit_id" value="<?= $uBlocks->block_id ?>">
<div class="form-group">
    <label>Название вставки</label>
    <input class="form-control" id="uEditor_block_edit_title" type="text" value="<?= addslashes(
        $uBlocks->block_title
    ) ?>">
</div>
<div class="form-group">
    <label>Местоположение вставки</label>
    <select id="uEditor_block_edit_pos" class="form-control">
        <?while($pos=$uBlocks->q_pos->fetch_object()) {?>
            <option value="<?= $pos->pos_id ?>" <?= $pos->pos_id ==
$uBlocks->block_pos
    ? 'selected'
    : '' ?>><?= $pos->pos_title ?></option>
        <?}?>
    </select>
</div>
<div id="uEditor_block_edit_html"><?= $uBlocks->block_html ?></div>
