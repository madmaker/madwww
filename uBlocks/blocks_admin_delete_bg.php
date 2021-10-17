<?php
class uBlocks_blocks_admin_delete
{
    private $uCore, $block_id;
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
    private function del_block()
    {
        if (
            !$this->uCore->query(
                "pages",
                "DELETE FROM
        `u235_ublocks_list`
        WHERE
        `site_id`='" .
                    site_id .
                    "' AND
        `block_id`='" .
                    $this->block_id .
                    "'"
            )
        ) {
            $this->uCore->error(3);
        }

        if (
            !$this->uCore->query(
                "pages",
                "DELETE FROM
        `u235_ublocks_pages`
        WHERE
        `site_id`='" .
                    site_id .
                    "' AND
        `block_id`='" .
                    $this->block_id .
                    "'
        "
            )
        ) {
            $this->uCore->error(4);
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
        $this->del_block();
    }
}
$uBlocks = new uBlocks_blocks_admin_delete($this);
