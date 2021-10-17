<?php
class uBlocks_blocks_updater
{
    private $uCore, $page_id;
    private function check_data()
    {
        if (!isset($_POST['page_id'])) {
            $this->uCore->error(1);
        }
        $this->page_id = $_POST['page_id'];
        if (!uString::isDigits($this->page_id)) {
            $this->uCore->error(2);
        }
    }
    private function get_blocks()
    {
        if (
            !($q_pos = $this->uCore->query(
                "pages",
                "SELECT
        `pos_id`
        FROM
        `u235_uBlocks_positions`
        WHERE
        `site_id`='" .
                    site_id .
                    "'
        "
            ))
        ) {
            $this->uCore->error(3);
        }

        if (
            !($query = $this->uCore->query(
                "pages",
                "SELECT DISTINCT
        `block_html`,
        `block_pos`
        FROM
        `u235_ublocks_list`,
        `u235_ublocks_pages`
        WHERE
        `u235_ublocks_pages`.`page_id`='" .
                    $this->page_id .
                    "' AND
        `u235_ublocks_pages`.`site_id`='" .
                    site_id .
                    "' AND
        `u235_ublocks_list`.`block_id`=`u235_ublocks_pages`.`block_id` AND
        `u235_ublocks_list`.`site_id`='" .
                    site_id .
                    "'
        ORDER BY
        `block_pos` ASC,
        `u235_ublocks_list`.`block_id` ASC
        "
            ))
        ) {
            $this->uCore->error(4);
        }

        $block_pos2cnt = [];
        while ($block = $query->fetch_object()) {
            if (!isset($block_pos2cnt[$block->block_pos])) {
                $block_pos2cnt[$block->block_pos] = '';
            }
            $block_pos2cnt[$block->block_pos] .=
                '<div class="uBlocks_block_' .
                $block->block_pos .
                ' uBlocks_block">' .
                uString::sql2text($block->block_html, true) .
                '</div>';
        }
        $answer = "{'pos_list':'";
        while ($pos = $q_pos->fetch_object()) {
            $answer .= $pos->pos_id . ",";
        }
        $answer .= "s',";
        mysqli_data_seek($q_pos, 0);
        while ($pos = $q_pos->fetch_object()) {
            if (!isset($block_pos2cnt[$pos->pos_id])) {
                $block_pos2cnt[$pos->pos_id] = 'none';
            }
            $answer .=
                "'pos_" .
                $pos->pos_id .
                "':'" .
                rawurlencode($block_pos2cnt[$pos->pos_id]) .
                "',";
        }
        $answer .= "'status':'done'}";
        die($answer);
    }
    function __construct(&$uCore)
    {
        $this->uCore = &$uCore;
        if (!$this->uCore->access(7)) {
            die("{'status' : 'forbidden'}");
        }

        $this->check_data();
        $this->get_blocks();
    }
}
$uBlocks = new uBlocks_blocks_updater($this);
