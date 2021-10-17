<?php
use processors\uFunc;
require_once 'uConf.php';
require_once 'classes/uFunc.php';
class cron_runner {
    private $uConf,$db_handler,
        $mod_installed_ar,
    $q_sites;
    private function query($db,$query) {
        if(!isset($this->db_handler[$db])) {
            $this->db_handler[$db]=new mysqli('localhost' , $this->uConf->sql['user'], $this->uConf->sql['pass'], 'madmakers_'.$db);
            if(!$this->db_handler) $this->error('db1');
            $this->db_handler[$db]->set_charset("utf8");
        }
        return $this->db_handler[$db]->query($query);
    }

    private function mod_installed($mod_name,$site_id=site_id) {
        if(!isset($this->mod_installed_ar[$site_id][$mod_name])) {
            if(!$query=$this->query("common","SELECT
            `mod_id`
            FROM
            `u235_sites_modules`
            WHERE
            `mod_name`='".$mod_name."' AND
            `site_id`='".$site_id."'
            ")) die('er2');
            if(mysqli_num_rows($query)) $this->mod_installed_ar[$site_id][$mod_name]=true;
            else $this->mod_installed_ar[$site_id][$mod_name]=false;
        }
        return $this->mod_installed_ar[$site_id][$mod_name];
    }
    private function get_sites() {
        if(!$this->q_sites=$this->query("common","SELECT DISTINCT
        `site_id`
        FROM
        `u235_sites`
        WHERE
        `status`='active' AND
        `main`='1'
        ")) die('er1');
    }
    private function run_scripts() {
        //uSupport
        uFunc::POST(cloud_url.'uSupport/cron_unconfirmed_request_inaction_delete',array('uSecret'=>'yrcl8Pbb6LvtQymJ$LVKIP6Q'));
        uFunc::POST(cloud_url.'uSupport/cron_create_solution_reminder',array('uSecret'=>'teSozwY1htiuAhcwlKqQr3CLMu'));
        //uAuth
        uFunc::POST(cloud_url.'uAuth/cron_session_cleaner',array('uSecret'=>'77PoP6InbmiMnF'));
        //uEvents
        uFunc::POST(cloud_url.'uEvents/cron_events_cleaner',array('uSecret'=>'Hjksdfsdf798254jilkjo(**(uihksdf'));
        //uEditor
        uFunc::POST(cloud_url.'uEditor/cron_page_delete_empty_folders',array('uSecret'=>'LKlksjdf8097324534jklk**9uoisjlkdf_)(*^s'));
        //uCat
        uFunc::POST(cloud_url.'uCat/cron_unused_field_columns_cleaner',array('uSecret'=>'YUIOiuosf97832o234mnLssdf09871NBMBNbnnbnbz11b34yshk2h3f5vHg234hjjh2nbhjgGHj4nm2jh34299809'));
        uFunc::POST(cloud_url.'uCat/cron_unused_field_cleaner',array('uSecret'=>'LKklsdf098238092-0340981knjlLKJsd69f8hkj2nm3klsdfoisdf789LKJljmk2873942lkjIOsd9f82lkj2l3kj4987'));
        uFunc::POST(cloud_url.'uCat/cron_unused_variants_types_cleaner',array('uSecret'=>'HKLJS^f78w6874h2kjHY&*^8o2h3jknrk.weyf78YIUh2k.3hrw67es8yHJLHKL298737r9pujLKAJL:SKhs]19[-i'));
        uFunc::POST(cloud_url.'uCat/cron_orders_cleaner',array('uSecret'=>'99PoP6hgjmiMnF'));
        // Sitemap
        uFunc::POST(cloud_url.'Sitemap/sitemap_generator',array('uSecret'=>'87fdsgjn4e7yg45'));

        //site-dependent
        while($site=$this->q_sites->fetch_object()) {
            if($this->mod_installed("uSup",$site->site_id)) {
                //uSupport
                uFunc::POST(cloud_url.'uSupport/cron_request_inaction_close',array('uSecret'=>'yrcl8Pbb6LvtQymJ$LVKIP6Q','site_id'=>$site->site_id));
                uFunc::POST(cloud_url.'uSupport/cron_request_inaction_reminder',array('uSecret'=>'JzXM5lQn@Ys$Dc%udk8Z4Pn!h#','site_id'=>$site->site_id));
            }
        }
    }
    function __construct () {
        $this->uCore=&$uCore;
        $this->uConf=new uConf();
        $this->uConf->define_site_root();

        $this->get_sites();
        $this->run_scripts();
    }
}
$cron_runner=new cron_runner();
//echo 'ok';
