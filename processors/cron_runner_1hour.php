<?php
use processors\uFunc;
require_once 'uConf.php';
require_once 'classes/uFunc.php';
class cron_runner {
    private $uConf,$db_handler,
    $q_sites;
    private function query($db,$query) {
        if(!isset($this->db_handler[$db])) {
            $this->db_handler[$db]=new mysqli('localhost' , $this->uConf->sql['user'], $this->uConf->sql['pass'], 'madmakers_'.$db);
            if(!$this->db_handler) $this->error('db1');
            $this->db_handler[$db]->set_charset("utf8");
        }
        return $this->db_handler[$db]->query($query);
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
        //uDrive
        uFunc::POST(cloud_url.'uDrive/cron_recycled_cleaner',array('uSecret'=>'HNJKsfyisdo2778923423lHJs'));
        //uCat
        uFunc::POST(cloud_url.'uCat/cron_unused_item_cleaner',array('uSecret'=>'JKLkljsdf789ljk12jkl1230987897alkjlklsdf832k2js72ioLKs72jkkjsdfkljsdfkj2kjslk'));
        //uForms
        $ret=uFunc::POST(cloud_url.'uForms/cron_rec_cleaner',array('uSecret'=>'HJlds9872kljdfglxv65'));
        uFunc::POST(cloud_url.'uConf/sitesize',array('uSecret'=>'JKLkljsdf789ljk12jkl1231007897alkjlklsdf832k2js72ioLKs72jkkjsdfkljsdfkj2kjslk'));

        //obooking
        uFunc::POST(cloud_url.'obooking/cron_update_calendar_demo.php',array('uSecret'=>'JKLkljsdf789ljk12jkl1230987897alkjlklsdf832k2js72ioLKs72jkkjsdfkljsdfkj2kjslk'));

        /*if(!empty($ret['content'])) {
            echo '<hr><p>'.cloud_url.'uForms/cron_rec_cleaner</p>';
            echo $ret['header'];
            echo $ret['content'];
        }*/

        while($site=$this->q_sites->fetch_object()) {
            //uForms
            /*$ret=uFunc::POST(cloud_url.'uForms/cron_rec_cleaner',array('uSecret'=>'HJlds9872kljdfglxv65'));
            if(!empty($ret['content'])) {
                echo '<hr><p>'.cloud_url.'uForms/cron_rec_cleaner</p>
            <p>site_id:'.$site->site_id.'</p>';
                echo $ret['header'];
                echo $ret['content'];
            }*/
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
