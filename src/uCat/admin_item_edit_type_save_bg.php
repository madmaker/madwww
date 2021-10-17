<?php
require_once 'uPage/inc/common.php';

class uCat_admin_item_edit_type_save {
    private $uCore,$uDrive_common,$uCat_common,$type_id,$base_type_id,$type_title,$item_id;
    private function check_data() {
        if(!isset($_POST['type_id'],$_POST['item_id'])) $this->uCore->error(10);

        if(!uString::isDigits($_POST['type_id'])) $this->uCore->error(20);
        $this->type_id=(int)$_POST['type_id'];

        $this->item_id=$_POST['item_id'];
        if(!uString::isDigits($this->item_id)) $this->uCore->error(21);

        if(isset($_POST['base_type_id'],$_POST['type_title'])) {
            $this->base_type_id=(int)$_POST['base_type_id'];
            $this->type_title=trim($_POST['type_title']);
            if(!strlen($this->type_title)) {
                die("{'status':'error','msg':'title is empty'}");
            }

            $this->update_type();
        }
        elseif(isset($_POST['delete'])) {
            $this->delete_type();

            echo "{
           'status':'done',
           'type_id':'".$this->type_id."'
            }";
        }
        else $this->uCore->error(30);


        //Clean uPage cache
        if (!isset($this->uPage)) $this->uPage = new \uPage\common($this->uCore);
        $this->uPage->clear_cache4uCat_latest();

    }
    private function delete_type() {
        //check if this type_id is used anywhere
        if($this->uCat_common->item_type_is_used($this->type_id)) die("{
        'status':'error',
        'msg':'type is used'
        }");

        if(!$this->uCore->query("uCat","DELETE FROM
        `items_types`
        WHERE
        `type_id`='".$this->type_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(40);
        if(!$this->uCore->query("uCat","UPDATE
        `u235_items`
        SET
        `item_type`='0'
        ")) $this->uCore->error(50);
        //check if 0 type_id exists
        if(!$query=$this->uCore->query("uCat","SELECT
        `type_id`
        FROM
        `items_types`
        WHERE
        `type_id`='0' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(60);
        if(!mysqli_num_rows($query)) {
            if(!$this->uCore->query("uCat","INSERT INTO
            `items_types` (
            `base_type_id`,
            `type_id`,
            `type_title`,
            `site_id`
            )
            VALUES (
            '0',
            '0',
            'Товар',
            '".site_id."'
            )
            ")) $this->uCore->error(70);
        }
    }
    private function update_type() {
        //get old base_type_id
        $old_base_type_id=(int)$this->uCat_common->item_type_id2data($this->type_id)->base_type_id;
        if(!$this->uCore->query("uCat","UPDATE
        `items_types`
        SET
        `base_type_id`='".$this->base_type_id."',
        `type_title`='".uString::text2sql($this->type_title)."'
        WHERE
        `type_id`='".$this->type_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(80);

        if($old_base_type_id!=$this->base_type_id) {
            if($this->base_type_id==1) {
                //set dontshow for all items with file_id=0
                $avail_id=$this->uCat_common->get_any_dontshow_avail_id();
                if(!$this->uCore->query("uCat","UPDATE
                `u235_items`
                SET
                `item_avail`='".$avail_id."'
                WHERE
                `item_type`='".$this->type_id."' AND
                `file_id`='0' AND
                `site_id`='".site_id."'
                ")) $this->uCore->error(90);
                //set available for all items with file!=0
                $avail_id=$this->uCat_common->get_any_available_avail_id();
                if(!$this->uCore->query("uCat","UPDATE
                `u235_items`
                SET
                `item_avail`='".$avail_id."'
                WHERE
                `item_type`='".$this->type_id."' AND
                `file_id`!='0' AND
                `site_id`='".site_id."'
                ")) $this->uCore->error(100);


                //set dontshow for all variants with file_id=0
                $avail_id=$this->uCat_common->get_any_dontshow_avail_id();
                if(!$this->uCore->query("uCat","UPDATE
                `items_variants`,
                `items_variants_types`
                SET
                `avail_id`='".$avail_id."'
                WHERE
                `items_variants`.`var_type_id`=`items_variants_types`.`var_type_id` AND

                `items_variants`.`site_id`='".site_id."' AND
                `items_variants_types`.`site_id`='".site_id."' AND

                `item_type_id`='".$this->type_id."' AND
                `file_id`='0'
                ")) $this->uCore->error(110);
                //set available for all variants with file_id!=0
                $avail_id=$this->uCat_common->get_any_available_avail_id();
                if(!$this->uCore->query("uCat","UPDATE
                `items_variants`,
                `items_variants_types`
                SET
                `avail_id`='".$avail_id."'
                WHERE
                `items_variants`.`var_type_id`=`items_variants_types`.`var_type_id` AND

                `items_variants`.`site_id`='".site_id."' AND
                `items_variants_types`.`site_id`='".site_id."' AND

                `item_type_id`='".$this->type_id."' AND
                `file_id`!='0'
                ")) $this->uCore->error(120);

            }
        }
        $item_data=$this->uCat_common->item_id2data($this->item_id,"`item_type`,`item_avail`,`file_id`");
        $item_type=(int)$item_data->item_type;
        $item_avail=(int)$item_data->item_avail;
        $file_id=(int)$item_data->file_id;
        if($item_type==$this->type_id) {
            $avail_type_id=(int)$this->uCat_common->avail_id2avail_data($item_avail)->avail_type_id;
            $avail_label=uString::sql2text($this->uCat_common->avail_id2avail_data($item_avail)->avail_label,1);
            $avail_descr=uString::sql2text($this->uCat_common->avail_id2avail_data($item_avail)->avail_descr,1);
            $avail_class=$this->uCat_common->avail_type_id2class($avail_type_id);
        }
        echo "{
           'status':'done',
           'type_id':'".$this->type_id."',
           ".($item_type==$this->type_id?("
               'base_type_id':'".$this->base_type_id."',
               'type_title':'".rawurlencode($this->type_title)."'
               ".($old_base_type_id!=$this->base_type_id?"
                    ,'item_type':'".$item_type."',
                   'item_avail':'".$this->uCat_common->item_id2data($this->item_id,"`item_avail`")->item_avail."',
                   'avail_label':'".rawurlencode($avail_label)."',
                   'avail_descr':'".rawurlencode($avail_descr)."',
                   'avail_class':'".$avail_class."',
                   'file_id':'".$file_id."'
                   ".($file_id?"
                        ,'file_name':'".rawurlencode($this->uDrive_common->file_id2data($file_id)->file_name)."',
                        'file_hashname':'".rawurlencode($this->uDrive_common->file_id2data($file_id)->file_hashname)."'
                   ":'')."
               ":'')."
           "):'')."
            }";
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(25)) die("{'status' : 'forbidden'}");

        require_once 'uCat/classes/common.php';
        $this->uCat_common=new \uCat\common($this->uCore);

        require_once 'uDrive/classes/common.php';
        $this->uDrive_common=new \uDrive\common($this->uCore);

        $this->check_data();
    }
}
/*$uCat=*/new uCat_admin_item_edit_type_save($this);
