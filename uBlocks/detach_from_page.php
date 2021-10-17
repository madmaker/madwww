<?php
if (!$this->access(7)) {
    die("{'status' : 'forbidden'}");
}

if (!isset($_POST['page_id'], $_POST['block_id'])) {
    $this->error(1);
}
$page_id = $_POST['page_id'];
$block_id = $_POST['block_id'];
if (!uString::isDigits($page_id)) {
    $this->error(2);
}
if (!uString::isDigits($block_id)) {
    $this->error(3);
}

if (
    !$this->query(
        "pages",
        "DELETE FROM
`u235_ublocks_pages`
WHERE
`page_id`='" .
            $page_id .
            "' AND
`block_id`='" .
            $block_id .
            "' AND
`site_id`='" .
            site_id .
            "'
"
    )
) {
    $this->error(4);
}

echo "{'status' : 'success', 'block_id' : '" . $block_id . "'}";
