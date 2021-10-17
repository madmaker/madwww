<?php
namespace uConf;

use PDO;
use PDOException;
use processors\uFunc;

require_once "processors/classes/uFunc.php";

class sitesize {
    private $uFunc;
    private $uCore;
    private function check_data() {

    }

    public function text($str) {
        return $this->uCore->text(array('uConf','sitesize'),$str);
    }

    function insert_or_update ($dirname, $site_id=site_id) {
        $stringsql = '';

        foreach ($dirname as $key => $value) {
            $stringsql .= $key . "=:".$key.",";
        }

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stmins = $this->uFunc->pdo("common")->prepare("REPLACE INTO 
  sites_size (
              site_id
              ) VALUES (
                        :site_id
                        ) ON DUPLICATE KEY UPDATE " . $stringsql . "site_id=:site_id");
            /** @noinspection PhpUndefinedMethodInspection */
            $stmins->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            foreach ($dirname as $key => $value) {
                $filepath = $_SERVER['DOCUMENT_ROOT'] . $value . $site_id . "/";
                $kolbyte = $this->uFunc->dir_size($filepath);
                /** @noinspection PhpUndefinedMethodInspection */
                $stmins->bindValue(":".$key, $kolbyte, PDO::PARAM_INT);
            }
            /** @noinspection PhpUndefinedMethodInspection */
            $stmins->execute();
        } catch (PDOException $e) {
            $this->uFunc->error('10'/*. $e->getMessage()*/);
        }
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();
        $this->uFunc=new uFunc($this->uCore);

        $this->check_data();

        $this->uCore->uInt_js('uPage','setup_uPage_page');





        $dirname = array(
            "uAuth_authavatars" => "/uAuth/avatars/",

            "uCat_artavatars" => "/uCat/art_avatars/",
            "uCat_catavatars" => "/uCat/cat_avatars/",
            "uCat_itemavatars" => "/uCat/item_avatars/",
            "uCat_itempictures" => "/uCat/item_pictures/",
            "uCat_sectavatars" => "/uCat/sect_avatars/",

            "uDrive_files" => "/uDrive/files/",

            "uEditor_files" => "/uEditor/files/",
            "uEditor_pageavatars" => "/uEditor/page_avatars/",

            "uEvents_eventsfiles" => "/uEvents/events_files/",
            "uEvents_eventstypesfiles" => "/uEvents/events_types_files/",

            "uForms_fieldfiles" => "/uForms/field_files/",
            "uForms_files" => "/uForms/form_files/",

            "uKnowbase_files" => "/uKnowbase/files/",

            "uNavi_itemicons" => "uNavi/item_icons/",

            "uPage_preview_images" => "uPage/preview_images",

            "uPeople_peopleavatars" => "/uPeople/avatars/",

            "uSlider_files" => "/uSlider/files/",
            "uSlider_slidesbg" => "/uSlider/slides_bg/",

            "uSubscr_files" => "/uSubscr/files/",

            "uSup_comavatars" => "/uSupport/com_avatars/",
            "uSup_msgsfiles" => "/uSupport/msgs_files/"
        );

        $modname = array(
            "uAuth" => $this->text("FSuAuth"),
            "uCat" => $this->text("FSuCat"),
            "uDrive" => $this->text("FSuDrive"),
            "uEditor" => $this->text("FSuEditor"),
            "uEvents" => $this->text("FSuEvents"),
            "uForms" => $this->text("FSuForms"),
            "uKnowbase" => $this->text("FSuKnowbase"),
            "uNavi" => $this->text("FSuNavi"),
            "uPeople" => $this->text("FSuPeople"),
            "uSlider" => $this->text("FSuSlider"),
            "uSup" => $this->text("FSuSup"),
            "uSubscr" => $this->text("FSuSubscr")
        );

        $prefname = array(
            "authavatars" => $this->text("FSauthavatars"),
            "catavatars" => $this->text("FScatavatars"),
            "artavatars" => $this->text("FSartavatars"),
            "itemavatars" => $this->text("FSitemavatars"),
            "sectavatars" => $this->text("FSsectavatars"),
            "itempictures" => $this->text("FSitempictures"),
            "files" => $this->text("FSfiles"),
            "pageavatars" => $this->text("FSpageavatars"),
            "eventsfiles" => $this->text("FSeventsfiles"),
            "eventstypesfiles" => $this->text("FSeventstypesfiles"),
            "fieldfiles" => $this->text("FSfieldfiles"),
            "peopleavatars" => $this->text("FSpeopleavatars"),
            "itemicons" => $this->text("FSitemicons"),
            "slidesbg" => $this->text("FSslidesbg"),
            "comavatars" => $this->text("FScomavatars"),
            "msgsfiles" => $this->text("FSmsgsfiles")
        );

        $linknamest = $this->text("Site");



        if (isset($_POST["siteid"])) {
            $postsiteid = $_POST["siteid"];
            $this->insert_or_update($dirname, $postsiteid);

            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $postres = $this->pdo("common")->prepare("SELECT
                *
                FROM
                sites_size
                WHERE
                site_id=:site_id
                ");
                /** @noinspection PhpUndefinedMethodInspection */$postres->bindParam(':site_id', $postsiteid, PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */
                $postres->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('0'/*.$e->getMessage()*/);}


            /** @noinspection PhpUndefinedMethodInspection */
            $arrpostres = $postres->fetch(PDO::FETCH_ASSOC);
            array_shift($arrpostres);
            $fullsizest = array_sum($arrpostres);
            $result_array = array();
            $result_array_full = array();

            foreach ($arrpostres as $key => $value) {
                $modsearchkey = explode("_", $key);
                $modkey = $modsearchkey[0];
                if ($this->uFunc->mod_installed($modkey) || $modkey == "uAuth" || $modkey == "uDrive" || $modkey == "uEditor" || $modkey == "uForms" || $modkey == "uNavi" || $modkey == "uSlider") {
                    $fsize = $this->uFunc->format_size($value);
                    $modskey = explode("_", $key);
                    $modkey = $modskey[0];
                    $modpref = $modskey[1];
                    if ($value > 0) {
                        $resquerydata = array(
                            "name" => $modname[$modkey] . ' / ' . $prefname[$modpref],
                            "size" => $fsize
                        );
                        $result_array[] = $resquerydata;
                        unset($resquerydata);
                    }
                }
            }
            $result_array_full["fullsize"] = $linknamest.": ".$this->uFunc->format_size($fullsizest);
            $result_array_full["modulesize"] = $result_array;
            echo json_encode($result_array_full, JSON_UNESCAPED_UNICODE);
        }
        else {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm = $this->uFunc->pdo("common")->prepare("SELECT DISTINCT
                site_id
                FROM
                u235_sites
                WHERE
                status='active' AND
                main=1
                ");
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('0'/*.$e->getMessage()*/);}

            /** @noinspection PhpUndefinedMethodInspection */
            /** @noinspection PhpUndefinedVariableInspection */
            while($qr=$stm->fetch(PDO::FETCH_OBJ)) {
                $this->insert_or_update($dirname, $qr->site_id);
            }
        }
    }
}
new sitesize($this);