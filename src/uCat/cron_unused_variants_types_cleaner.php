<?php
namespace uCat\cron;
use PDOException;
use processors\uFunc;

require_once 'processors/classes/uFunc.php';

class unused_variants_types_cleaner {
    private $uCore;
    private function check_data() {
        if(!isset($_POST['uSecret'])) $this->uFunc->error(10);
        if($this->secret!=$_POST['uSecret']) $this->uFunc->error(20);
    }
    private function clean_variants_types() {
        //SET hidden=0 for variants_types that are used
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE
            items_variants_types
            LEFT JOIN
            items_variants
            ON
            (items_variants.var_type_id=items_variants_types.var_type_id AND
            items_variants.site_id=items_variants_types.site_id)
            SET 
            hidden=0
            WHERE
            var_id IS NOT NULL
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}

        //DELETE hidden variants_types
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("DELETE FROM
            items_variants_types
            WHERE
            hidden=1");
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}
        //SET unused variants_types to hidden
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE
            items_variants_types
            LEFT JOIN
            items_variants
            ON
            (items_variants.var_type_id=items_variants_types.var_type_id AND
            items_variants.site_id=items_variants_types.site_id)
            SET hidden=1
            WHERE
            var_id IS NULL
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/);}
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);

        $this->secret='HKLJS^f78w6874h2kjHY&*^8o2h3jknrk.weyf78YIUh2k.3hrw67es8yHJLHKL298737r9pujLKAJL:SKhs]19[-i';

        $this->check_data();

        $this->clean_variants_types();
    }
}
/*$newClass=*/new unused_variants_types_cleaner($this);