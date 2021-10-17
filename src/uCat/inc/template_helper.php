<?php
class uCat_helper {
    private $uCore;
    function uCat_helper(&$uCore) {
        $this->uCore=&$uCore;
    }
    public function get_all_sects() {
        if(!$query=$this->uCore->query("uCat","SELECT
        `sect_id`,
        `sect_title`,
        `sect_url`,
        `sect_avatar_time`,
        `cat_count`
        FROM
        `u235_sects`
        WHERE
        `cat_count`>'0' AND
        `item_count`>'0' AND
        `site_id`='".site_id."'
        ORDER BY
        `sect_pos` ASC,
        `sect_title` ASC
        ")) $this->uCore->error("uCat_1");
        return $query;
    }
    public function get_show_in_menu_sects() {
        if(!$query=$this->uCore->query("uCat","SELECT
        `sect_id`,
        `sect_title`,
        `sect_url`,
        `sect_avatar_time`,
        `cat_count`
        FROM
        `u235_sects`
        WHERE
        `cat_count`>'0' AND
        `item_count`>'0' AND
        `show_in_menu`='1' AND
        `site_id`='".site_id."'
        ORDER BY
        `sect_pos` ASC,
        `sect_title` ASC
        ")) $this->uCore->error("uCat_1");
        return $query;
    }
    public function get_sect_cats($sect_id,$limit=false,$hp_only=false){
        if(!$query=$this->uCore->query("uCat","SELECT
        `u235_cats`.`cat_id`,
        `u235_cats`.`cat_url`,
        `cat_title`
        FROM
        `u235_cats`,
        `u235_sects_cats`
        WHERE
        ".($hp_only?"`show_on_hp`='1' AND ":"")."
        `item_count`>'0' AND
        `u235_cats`.`cat_id`=`u235_sects_cats`.`cat_id` AND
        `u235_sects_cats`.`sect_id`='".$sect_id."' AND
        `u235_sects_cats`.`site_id`='".site_id."' AND
        `u235_cats`.`site_id`='".site_id."'
        ORDER BY
        `cat_pos` ASC,
        `item_count` DESC,
        `cat_title` ASC
        ".($limit?(' LIMIT '.$limit):'')."
        ")) $this->uCore->error("uCat_2");
        return $query;
    }
    public function get_cat_last_items() {
        if(!$query=$this->uCore->query("uCat","SELECT DISTINCT
        `item_id`,
        `item_title`,
        `item_url`,
        `item_img_time`
        FROM
        `u235_items`,
        `u235_items_avail_values`
        WHERE
        `u235_items`.`item_avail`=`u235_items_avail_values`.`avail_id` AND
        `u235_items_avail_values`.`avail_type_id`!='2' AND
        `u235_items_avail_values`.`site_id`='".site_id."' AND
        `cat_count`!='0' AND
        `u235_items`.`site_id`='".site_id."'
        ORDER BY
        `item_id` DESC
        LIMIT 20
        ")) $this->uCore->error("uCat_3");
        return $query;
    }
    public function get_cat_last_articles() {
        if(!$query=$this->uCore->query("uCat","SELECT
        `art_id`,
        `art_title`,
        `art_avatar_time`,
        `art_text`
        FROM
        `u235_articles`
        WHERE
        `site_id`='".site_id."'
        ORDER BY
        `art_id` DESC
        LIMIT 5
        ")) $this->uCore->error("uCat_4");
        return $query;
    }
    public function get_item_articles($item_id) {
        if(!$query=$this->uCore->query("uCat","SELECT DISTINCT
        `u235_articles`.`art_id`,
        `art_title`,
        `art_avatar_time`,
        `art_text`
        FROM
        `u235_articles`,
        `u235_articles_items`
        WHERE
        `u235_articles_items`.`art_id`=`u235_articles`.`art_id` AND
        `u235_articles_items`.`item_id`='".$item_id."' AND
        `u235_articles`.`site_id`='".site_id."' AND
        `u235_articles_items`.`site_id`='".site_id."'
        ORDER BY
        `u235_articles`.`art_id` DESC
        ")) $this->uCore->error("uCat_5");
        return $query;
    }
}
