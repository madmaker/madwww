<?php
/**DEPRECATED. USE uCat/classes/common.php. Если там нет такой функции, то нужно ее там создать*/
class uCat_admin_count_helper {
    private $uCore;
    function __construct(&$uCore) {
        $this->uCore=&$uCore;
    }

    public function get_sect_cats($q_sect_id) {
        if(!$query=$this->uCore->query("uCat","SELECT DISTINCT
        `cat_id`
        FROM
        `u235_sects_cats`
        WHERE
        (".$q_sect_id.") AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(108);
        return $query;
    }
}
