<?php
namespace dashboard\admin;
use PDO;
use PDOException;
use processors\uFunc;
//use uSes;

require_once "processors/classes/uFunc.php";
//require_once "processors/uSes.php";

class common{
    private $uFunc;
    private $uCore;
    public function text($str) {
        return $this->uCore->text(array('dashboard','common'),$str);
    }


    private $site_files_dirnames= array(
//        "uAuth/avatars",

        "uCat/art_avatars",
        "uCat/cat_avatars",
        "uCat/item_avatars",
        "uCat/item_pictures",
        "uCat/sect_avatars",

        "uDrive/files",

        "uEditor/files",
        "uEditor/page_avatars",

        "uEvents/events_files",
        "uEvents/events_types_files",

        "uForms/field_files",
        "uForms/form_files",

        "uKnowbase/files",

        "uNavi/item_icons",

        "uPage/preview_images",

        "uPeople/avatars",

        "uSlider/files",
        "uSlider/slides_bg",

        "uSubscr/files",

        "uSupport/com_avatars",
        "uSupport/msgs_files"
    );
    public function calculate_site_disk_space($site_id=site_id) {
        $dirs_count=count($this->site_files_dirnames);
        $query_params="";
        $query_values_ar=[];
        $query_values="";
        $dir_size=[];
        $results_ar=[];
        for($i=0;$i<$dirs_count;$i++) {
            $dirname=$this->site_files_dirnames[$i];
            $dir_size[$i]=(int)$this->uFunc->dir_size($dirname."/".$site_id);

            $results_ar[$i]=[];
            $results_ar[$i]["dirname"]=$this->text($dirname);
            $results_ar[$i]["dirsize"]=$dir_size[$i];

            $rowname=str_replace("/","_",$dirname);
            $query_params.=$rowname.",\n";
            $query_values_ar[$i]=$rowname;
            $query_values.=":".$rowname.",\n";
        }

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm = $this->uFunc->pdo("common")->prepare("REPLACE INTO 
            sites_size (
            ".$query_params."
            site_id
            ) VALUES (
            " . $query_values . "
            :site_id
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);

            for($i=0;$i<$dirs_count;$i++) {
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindValue(":".$query_values_ar[$i], $dir_size[$i], PDO::PARAM_INT);
            }
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        } catch (PDOException $e) {$this->uFunc->error('dashboard common 10'. $e->getMessage(),1);}

        return $results_ar;
    }
    public function get_site_disk_space($site_id=site_id) {
        $results_ar=$this->calculate_site_disk_space($site_id);//В боевом режиме должно быть закомменчено
        return $results_ar;//В боевом режиме должно быть закомменчено


        return $results_ar;
    }
    public function get_site_uDrive_db_space($site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uDrive")->prepare("SELECT 
            SUM(file_size) AS total_size 
            FROM 
            u235_files 
            WHERE 
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) return $qr->total_size;
        }
        catch(PDOException $e) {$this->uFunc->error('0'/*.$e->getMessage()*/);}
        return 0;
    }
    public function get_allowed_disk_space($site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("common")->prepare("SELECT 
            allowed_disk_space 
            FROM 
            sites_conf
            WHERE 
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) return (int)$qr->allowed_disk_space;;
        }
        catch(PDOException $e) {$this->uFunc->error('0'/*.$e->getMessage()*/);}
        return 0;
    }
    public function get_sites() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("common")->prepare("SELECT 
            site_name,
            site_id
            FROM 
            u235_sites 
            WHERE
            main=1 AND
            site_id!=0
            ORDER BY 
                     site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('0'/*.$e->getMessage()*/);}
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();
        $this->uFunc=new uFunc($this->uCore);

    }
}