<?php
class uBlocks_blocks_admin_edit
{
    private $uCore, $block_id, $block_title, $block_pos, $block_html;
    private function check_data()
    {
        if (
            !isset(
                $_POST['block_id'],
                $_POST['block_title'],
                $_POST['block_pos'],
                $_POST['block_html']
            )
        ) {
            $this->uCore->error(1);
        }
        $this->block_id = $_POST['block_id'];
        if (!uString::isDigits($this->block_id)) {
            $this->uCore->error(2);
        }
        $this->block_title = trim($_POST['block_title']);
        if (!strlen($this->block_title)) {
            die("{'status' : 'error', 'msg' : 'title_empty'}");
        }
        $this->block_pos = $_POST['block_pos'];
        if (!uString::isDigits($this->block_pos)) {
            $this->uCore->error(3);
        }
        if (
            !($query = $this->uCore->query(
                "pages",
                "SELECT
        `pos_id`
        FROM
        `u235_uBlocks_positions`
        WHERE
        `pos_id`='" .
                    $this->block_pos .
                    "' AND
        `site_id`='" .
                    site_id .
                    "'
        "
            ))
        ) {
            $this->uCore->error(4);
        }
        $this->block_html = trim($_POST['block_html']);
    }
    private function save_block()
    {
        if (
            !$this->uCore->query(
                "pages",
                "UPDATE
        `u235_ublocks_list`
        SET
        `block_title`='" .
                    uString::text2sql($this->block_title) .
                    "',
        `block_pos`='" .
                    $this->block_pos .
                    "',
        `block_html`='" .
                    uString::text2sql($this->block_html) .
                    "'
        WHERE
        `block_id`='" .
                    $this->block_id .
                    "' AND
        `site_id`='" .
                    site_id .
                    "'
        "
            )
        ) {
            $this->uCore->error(5);
        }

        echo "{'status' : 'done'}";
    }
    function __construct(&$uCore)
    {
        $this->uCore = &$uCore;
        if (!$this->uCore->access(7)) {
            die('forbidden');
        }

        $this->check_data();
        $this->save_block();
    }
}
$uBlocks = new uBlocks_blocks_admin_edit($this);
