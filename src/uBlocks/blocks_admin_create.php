<?php
class uBlocks_block_admin_create
{
    private $uCore, $block_title, $block_id;
    private function check_data()
    {
        if (!isset($_POST['block_title'])) {
            $this->uCore->error(1);
        }
        $this->block_title = trim($_POST['block_title']);
        if (!strlen($this->block_title)) {
            die("{'status' : 'error', 'msg' : 'title_empty'}");
        }
    }
    private function get_new_block_id()
    {
        if (
            !($query = $this->uCore->query(
                "pages",
                "SELECT
        `block_id`
        FROM
        `u235_ublocks_list`
         WHERE
         `site_id`='" .
                    site_id .
                    "'
        ORDER BY
        `block_id` DESC
        LIMIT 1
        "
            ))
        ) {
            $this->uCore->error(2);
        }
        if (mysqli_num_rows($query)) {
            $qr = $query->fetch_object();
            $this->block_id = $qr->block_id + 1;
        } else {
            $this->block_id = 1;
        }
    }
    private function create_block()
    {
        $this->get_new_block_id();

        if (
            !$this->uCore->query(
                "pages",
                "INSERT INTO `u235_ublocks_list` (
        `block_id`,
        `block_title`,
        `timestamp`,
        `site_id`
        ) VALUES (
        '" .
                    $this->block_id .
                    "',
        '" .
                    uString::text2sql($this->block_title, 1) .
                    "',
        '" .
                    time() .
                    "',
        '" .
                    site_id .
                    "'
        )
        "
            )
        ) {
            $this->uCore->error(3);
        }

        echo "{
        'status' : 'done',
        'block_id' : '" .
            $this->block_id .
            "',
        'block_title' : '" .
            rawurlencode($this->block_title) .
            "'
        }";
    }
    function __construct(&$uCore)
    {
        $this->uCore = &$uCore;
        if (!$this->uCore->access(7)) {
            die("{'status' : 'forbidden'}");
        }

        $this->check_data();
        $this->create_block();
    }
}
$uRubrics = new uBlocks_block_admin_create($this);
