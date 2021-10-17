<?php
namespace uNavi\admin;
use PDO;
use PDOException;
use processors\uFunc;
use uString;

require_once "processors/classes/uFunc.php";

class sitemap_admin_page_attach_menu {
    private $uCore;
    private $page_id;

    private function check_data() {
        if(!isset($_POST['page_id'],$_POST['dir'],$_POST['ids'])) $this->uFunc->error(10);
        if(!uString::isDigits(str_replace('s','',$_POST['page_id']))) $this->uFunc->error(20);
        $this->page_id=&$_POST['page_id'];
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);

        $this->check_data();

        $idsAr=  explode('#',$_POST['ids'] );
        if(count($idsAr)>1) {
            $idsAr_count=count($idsAr);
            if($_POST['dir']=='attach') {
                for($i=1;$i<$idsAr_count;$i++) {
                    if(!uString::isDigits($idsAr[$i])) $this->uFunc->error(30);

                    try {
                        /** @noinspection PhpUndefinedMethodInspection */
                        $stm=$this->uFunc->pdo("uNavi")->prepare("SELECT
                        cat_id
                        FROM
                        u235_pagemenu
                        WHERE
                        site_id=:site_id AND
                        cat_id=:cat_id AND
                        page_id=:page_id
                        ");
                        $site_id=site_id;
                        /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->page_id,PDO::PARAM_INT);
                        /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_id', $idsAr[$i],PDO::PARAM_INT);
                        /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                        /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                    }
                    catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}

                    /** @noinspection PhpUndefinedVariableInspection PhpUndefinedMethodInspection */
                    if(!$stm->fetch(PDO::FETCH_OBJ)) {

                        try {
                            /** @noinspection PhpUndefinedMethodInspection */
                            $stm=$this->uFunc->pdo("uNavi")->prepare("INSERT INTO
                             u235_pagemenu (
                             page_id,
                             cat_id,
                             site_id
                             ) VALUES (
                             :page_id,
                             :cat_id,
                             :site_id
                             )");
                            $site_id=site_id;
                            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_id', $idsAr[$i],PDO::PARAM_INT);
                            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->page_id,PDO::PARAM_INT);
                            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                        }
                        catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/);}
                    }
                }
            }
            else {
                for($i=1;$i<$idsAr_count;$i++) {
                    if(!uString::isDigits($idsAr[$i])) $this->uFunc->error(60);

                    try {
                        /** @noinspection PhpUndefinedMethodInspection */
                        $stm=$this->uFunc->pdo("uNavi")->prepare("DELETE FROM
                        u235_pagemenu
                        WHERE
                        site_id=:site_id AND
                        page_id=:page_id AND
                        cat_id=:cat_id
                        ");
                        $site_id=site_id;
                        /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->page_id,PDO::PARAM_INT);
                        /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                        /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                    }
                    catch(PDOException $e) {$this->uFunc->error('70'/*.$e->getMessage()*/);}
                }
            }
        }
        echo 'done';
    }
}
/*$newClass=*/new sitemap_admin_page_attach_menu($this);