<?php
namespace uCat\admin;
use PDO;
use PDOException;
use processors\uFunc;
use uCat\common;
use uSes;

require_once "processors/uSes.php";
require_once 'uPage/inc/common.php';
require_once 'uCat/classes/common.php';
class variant_update {
    private $uPage;
    private $uSes;
    private $uFunc;
    private $uCore,$uCat,
    $var_type_id,$var_title,$item_type;
    private function check_data() {
        if(!isset($_POST['var_type_id'],$_POST['var_title'],$_POST['item_type'])) $this->uFunc->error(10);
        $this->var_type_id=$_POST['var_type_id'];
        if(!\uString::isDigits($this->var_type_id)) $this->uFunc->error(20);
        if(!$this->uCat->var_type_id2data($this->var_type_id)) $this->uFunc->error(30);

        $this->var_title=trim($_POST['var_title']);
        if(!strlen($this->var_title)) die("{
        'status':'error',
        'msg':'title is empty'
        }");

        $this->item_type=$_POST['item_type'];
        if(!$this->uCat->item_type_id2data($this->item_type)) $this->uFunc->error(40);
    }
    private function update_variant() {
        $old_base_type_id=(int)$this->uCat->item_type_id2data($this->uCat->var_type_id2data($this->var_type_id)->item_type_id)->base_type_id;
        $new_base_type_id=(int)$this->uCat->item_type_id2data($this->item_type)->base_type_id;
        if($old_base_type_id!=$new_base_type_id) {//we should update item_type for all items with this var_type_id as default variant
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE
                u235_items,
                items_variants,
                items_variants_types
                SET
                u235_items.item_type=:item_type
                WHERE
                u235_items.site_id=:site_id AND
                items_variants.site_id=:site_id AND
                items_variants_types.site_id=:site_id AND
    
                u235_items.item_id=items_variants.item_id AND
                items_variants.var_type_id=items_variants_types.var_type_id AND
                items_variants_types.var_type_id=:var_type_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':var_type_id', $this->var_type_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_type', $this->item_type,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/);}

            if($new_base_type_id==1) {//is file - set avail_id = available for all variants and items with file and avail_id = dontshow for all variants and items with no file
                $available_avail_id=$this->uCat->get_any_available_avail_id();
                $dontshow_avail_id=$this->uCat->get_any_dontshow_avail_id();

                //items with file
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE
                    u235_items,
                    items_variants,
                    items_variants_types
                    SET
                    u235_items.item_avail=:item_avail
                    WHERE
                    u235_items.site_id=:site_id AND
                    items_variants.site_id=:site_id AND
                    items_variants_types.site_id=:site_id AND
    
                    u235_items.file_id!=0 AND
    
                    u235_items.item_id=items_variants.item_id AND
                    items_variants.var_type_id=items_variants_types.var_type_id AND
                    items_variants_types.var_type_id=:var_type_id
                    ");
                    $site_id=site_id;
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':var_type_id', $this->var_type_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $available_avail_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_avail', $site_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                }
                catch(PDOException $e) {$this->uFunc->error('60'/*.$e->getMessage()*/);}

                //items with NO file
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE
                    u235_items,
                    items_variants,
                    items_variants_types
                    SET
                    u235_items.item_avail=:item_avail
                    WHERE
                    u235_items.site_id=:site_id AND
                    items_variants.site_id=:site_id AND
                    items_variants_types.site_id=:site_id AND
    
                    u235_items.file_id=0 AND
    
                    u235_items.item_id=items_variants.item_id AND
                    items_variants.var_type_id=items_variants_types.var_type_id AND
                    items_variants_types.var_type_id=:var_type_id
                    ");
                    $site_id=site_id;
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':var_type_id', $this->var_type_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_avail', $dontshow_avail_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                }
                catch(PDOException $e) {$this->uFunc->error('70'/*.$e->getMessage()*/);}

                //variants with file
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE
                    items_variants,
                    items_variants_types
                    SET
                    avail_id=:avail_id
                    WHERE
                    items_variants.site_id=:site_id AND
                    items_variants_types.site_id=:site_id AND
    
                    file_id!=0 AND
    
                    items_variants.var_type_id=items_variants_types.var_type_id AND
                    items_variants_types.var_type_id=:var_type_id
                    ");
                    $site_id=site_id;
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':var_type_id', $this->var_type_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':avail_id', $available_avail_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                }
                catch(PDOException $e) {$this->uFunc->error('80'/*.$e->getMessage()*/);}

                //variants with NO file
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE
                    items_variants,
                    items_variants_types
                    SET
                    avail_id=:avail_id
                    WHERE
                    items_variants.site_id=:site_id AND
                    items_variants_types.site_id=:site_id AND
    
                    file_id=0 AND
    
                    items_variants.var_type_id=items_variants_types.var_type_id AND
                    items_variants_types.var_type_id=:var_type_id
                    ");
                    $site_id=site_id;
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':var_type_id', $this->var_type_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':avail_id', $dontshow_avail_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                }
                catch(PDOException $e) {$this->uFunc->error('90'/*.$e->getMessage()*/);}
            }
        }
        $set_sql="
        `var_type_title`='".\uString::text2sql($this->var_title)."',
        `item_type_id`='".$this->item_type."'
        ";
        $this->uCat->update_variant_type($this->var_type_id,$set_sql);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(25)) die("{'status' : 'forbidden'}");
        $this->uFunc=new uFunc($this->uCore);
        $this->uCat=new common($this->uCore);

        $this->check_data();

        $this->uPage = new \uPage\common($this->uCore);

        $this->update_variant();

        echo "{
        'status':'done',
        'base_type_id':'".$this->uCat->item_type_id2data($this->item_type)->base_type_id."',
        'item_type':'".$this->item_type."'
        }";

        //Clean uPage cache
        $this->uPage->clear_cache4uCat_latest();
    }
}
new variant_update($this);