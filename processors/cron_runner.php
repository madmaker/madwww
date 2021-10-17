<?php
require_once 'uConf.php';

class cron_runner {
    private $uConf,$db_handler,
        $mod_installed_ar,
    $q_sites;
    private function POST($url, $data, $referer='') {
        $data = http_build_query($data);// Convert the data array into URL Parameters like a=b&foo=bar etc.
        $url = parse_url($url);// parse the given URL
        $host = $url['host'];// extract host and path:
        $path = $url['path'];
        if ($url['scheme'] != 'http') {
            $fp = fsockopen("ssl://".$host, 443, $errno, $errstr, 30);// open a socket connection on port 80 - timeout: 30 sec
        }
        else {
            $fp = fsockopen($host, 80, $errno, $errstr, 30);// open a socket connection on port 80 - timeout: 30 sec
        }
        if ($fp){// send the request headers:
            fputs($fp, "POST $path HTTP/1.1\r\n");
            fputs($fp, "Host: $host\r\n");
            if ($referer != '')
                fputs($fp, "Referer: $referer\r\n");
            fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
            fputs($fp, "Content-length: ". strlen($data) ."\r\n");
            fputs($fp, "Connection: close\r\n\r\n");
            fputs($fp, $data);
            $result = '';
            while(!feof($fp)) {$result .= fgets($fp, 128);}// receive the results of the request
        }
        else {
            return array(
                'status' => 'err',
                'error' => "$errstr ($errno)"
            );
        }
        fclose($fp);// close the socket connection:
        $result = explode("\r\n\r\n", $result, 2);// split the result header from the content
        $header = isset($result[0]) ? $result[0] : '';
        $content = isset($result[1]) ? $result[1] : '';
        return array(// return as structured array:
            'status' => 'ok',
            'header' => $header,
            'content' => $content
        );
    }
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
        while($site=$this->q_sites->fetch_object()) {
            if($this->mod_installed("uSubscr",$site->site_id)) {
                //uSubscr
                /*echo '<hr>site '.$site->site_id;
                echo '<p>'.cloud_url.'uSubscr/cron_prepare_mailing</p>';
                $ret=*/$this->POST(cloud_url.'uSubscr/cron_prepare_mailing',array('secret'=>'NhX%wc!!&TmQxBxnXHnMM88maS','site_id'=>$site->site_id));
                /*if(!empty($ret['content'])) {
                    echo $ret['header'];
                    echo '<p></p>';
                    echo $ret['content'];
                }

                echo '<p>'.cloud_url.'uSubscr/cron_send</p>';
                $ret=*/$this->POST(cloud_url.'uSubscr/cron_send',array('secret'=>'NhX%wc!!&TmQxBxnXHnMM88maS','site_id'=>$site->site_id));
                /*if(!empty($ret['content'])) {
                    echo $ret['header'];
                    echo '<p></p>';
                    echo $ret['content'];
                }*/
            }
            //uSupport
            if($this->mod_installed("uSup",$site->site_id)) {
                /*$ret=*/$this->POST(cloud_url.'uSupport/cron_check_new_emails',array('uSecret'=>'oZmcDvhvmnwJ!lwMnS7FQb?r4q','site_id'=>$site->site_id));
                /*if(!empty($ret['content'])) {
                    echo '<hr><p>'.cloud_url.'uSupport/cron_check_new_emails</p>
                <p>site_id:'.$site->site_id.'</p>';
                    //echo $ret['header'];
                    echo $ret['content'];
                }*/
            }
        }
        //uAuth
        $this->POST(cloud_url.'uAuth/cron_pass_one_off_cleaner',array('uSecret'=>'HKLJS^f78w6874h2kjHY&*^8o2h3jknrk.weyf78YIUh2k.3hrw67es8yHJLHKL298737r9pujLKAJL:SKhs]19[-i'));
        //uForms
        $this->POST(cloud_url.'uForms/create_field_value',array('uSecret'=>'HKLJS^f78w6874h2kjHY&*^8o2h3jknrk.weyf78YIUh2k.3hrw67es8yHJLHKL298737r9pujLKAJL:SKhs]19[-i'));
        //uCat import
        $this->POST(cloud_url.'uCat/cron_items_list_importer',array('uSecret'=>'HKLJS^f78w6874h2kjHY&*^8o2h3jknrk.weyf78YIUh2k.3hrw67es8yHJLHKL298737r9pujLKAJL:SKhs]19[-i'));
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
