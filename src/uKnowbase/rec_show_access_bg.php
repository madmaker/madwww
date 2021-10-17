<?php
class rec_show_access_bg {
    private $uCore,$rec_id;
    private function check_data() {
        if(!isset($_POST['rec_id'])) $this->uCore->error(10);
        $this->rec_id=$_POST['rec_id'];
        if(!uString::isDigits($this->rec_id)) $this->uCore->error(20);
    }
    private function com_id2title($com_id) {
        if(!$query=$this->uCore->query("uSup","SELECT
        `com_title`
        FROM
        `u235_comps`
        WHERE
        `com_id`='".$com_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(30);
        if(mysqli_num_rows($query)) {
            $com=$query->fetch_object();
            return uString::sql2text($com->com_title);
        }
        else return $com_id;
    }
    private function check_if_access_limited(){
        if(!$query=$this->uCore->query("uKnowbase","SELECT
        `access_limited`
        FROM
        `u235_records`
        WHERE
        `rec_id`='".$this->rec_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(40);
        if(!mysqli_num_rows($query)) die('forbidden');
        $rec=$query->fetch_object();
        if($rec->access_limited=='1') return true;
        return false;
    }
    private function unlimit_access(){
        if(!$query=$this->uCore->query("uKnowbase","UPDATE
        `u235_records`
        SET
        `access_limited`='0'
        WHERE
        `rec_id`='".$this->rec_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(50);
    }
    private function check_if_com_has_access($com_id) {
        if(!$query=$this->uCore->query("uKnowbase","SELECT
        `com_id`
        FROM
        `u235_records_comps`
        WHERE
        `rec_id`='".$this->rec_id."' AND
        `com_id`='".$com_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(60);
        return mysqli_num_rows($query);
    }
    private function get_comps() {
        if(isset($_POST['set'])) {
            if(!$query=$this->uCore->query("uSup","SELECT
            `com_id`,
            `com_title`
            FROM
            `u235_comps`
            WHERE
            `site_id`='".site_id."'
            ")) $this->uCore->error(70);

            if(mysqli_num_rows($query)) {
                echo '<h3>Кто будет видеть это решение?:<br><small>Выберите компании, которые должны видеть это решение - для остальных пользователей оно будет скрыто</small></h3>
                <table class="table table-condensed table-hover">';
                while($com=$query->fetch_object()){
                    echo'<tr '.($this->check_if_com_has_access($com->com_id)?'class="text-success uTooltip" title="Этой компании доступ открыт"':'').'>
                    <td>'.($this->uCore->access(9)||$this->uCore->access(8)?'<a href="'.u_sroot.'uSupport/company_info/'.$com->com_id.'">':'').$com->com_id.($this->uCore->access(9)||$this->uCore->access(8)?'</a>':'').'</td>
                    <td>'.($this->uCore->access(9)||$this->uCore->access(8)?'<a href="'.u_sroot.'uSupport/company_info/'.$com->com_id.'">':'').uString::sql2text($com->com_title,1).($this->uCore->access(9)||$this->uCore->access(8)?'</a>':'').'</td>
                    <td><button class="btn '.($this->check_if_com_has_access($com->com_id)?'btn-danger':'btn-success').' btn-xs" onclick="uKnowbase.change_access('.$this->rec_id.','.$com->com_id.')">'.($this->check_if_com_has_access($com->com_id)?'Закрыть':'Открыть').' доступ</button></td>
                    </tr>';
                }
                echo '</table>
                <div class="bs-callout bs-callout-primary">Обратите внимание, что если не выбрана ни одна компания, то это решение будут видеть все</div> ';
            }
        }
        else {
            if(!$query=$this->uCore->query("uKnowbase","SELECT
            `com_id`
            FROM
            `u235_records_comps`
            WHERE
            `rec_id`='".$this->rec_id."' AND
            `site_id`='".site_id."'
            ")) $this->uCore->error(80);

            if(mysqli_num_rows($query)) {
                echo '<h3>Это решение видно только пользователям следующих компаний:</h3>
                <table class="table table-condensed table-striped table-hover">';
                while($com=$query->fetch_object()){
                    echo'<tr>
                    <td>'.($this->uCore->access(9)||$this->uCore->access(8)?'<a href="'.u_sroot.'uSupport/company_info/'.$com->com_id.'">':'').$com->com_id.($this->uCore->access(9)||$this->uCore->access(8)?'</a>':'').'</td>
                    <td>'.($this->uCore->access(9)||$this->uCore->access(8)?'<a href="'.u_sroot.'uSupport/company_info/'.$com->com_id.'">':'').$this->com_id2title($com->com_id).($this->uCore->access(9)||$this->uCore->access(8)?'</a>':'').'</td>
                    </tr>';
                }
                echo '</table>';
            }
            else {
                $this->unlimit_access();
                die('<h3>Это решение видно всем</h3><p>Видно произошла накладка, но она уже исправлена.<br>Обновите страницу</p>');
            }
        }
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(33)) die('forbidden');

        $this->check_data();
        if(isset($_POST['set'])) {
            $this->get_comps();
        }
        else {
            if($this->check_if_access_limited()) {
                $this->get_comps();
            }
            else die('<h3>Это решение видно всем</h3>');
        }
    }
}
$uKb=new rec_show_access_bg($this);
