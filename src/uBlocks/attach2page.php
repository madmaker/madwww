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
    !($query = $this->query(
        "pages",
        "SELECT DISTINCT
    `u235_ublocks_list`.`block_html`,
    `u235_ublocks_list`.`block_pos`
    FROM
    `u235_ublocks_list`
    WHERE
    `u235_ublocks_list`.`site_id`='" .
            site_id .
            "' AND
    `u235_ublocks_list`.`block_id`='" .
            $block_id .
            "'
    ORDER BY `u235_ublocks_list`.`block_title` ASC"
    ))
) {
    $this->error(4);
}
if (!mysqli_num_rows($query)) {
    $this->error(5);
}

$data = $query->fetch_assoc();

if (
    !$this->query(
        "pages",
        "INSERT INTO `u235_ublocks_pages` (
`page_id`,
`block_id`,
`site_id`
) VALUES (
'" .
            $page_id .
            "',
'" .
            $block_id .
            "',
'" .
            site_id .
            "'
)"
    )
) {
    $this->error(6);
}

echo "{'status' : 'success', 'block_id' : '" .
    $block_id .
    "' , 'block_html':'" .
    rawurlencode(uString::sql2text($data['block_html'], true)) .
    "', 'block_pos':'" .
    $data['block_pos'] .
    "'}";
