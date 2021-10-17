<?php
namespace uPage\admin;
use processors\uFunc;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once 'uPage/inc/common.php';

class admin_edit_col_bg{
    private $uFunc;
    private $uPage;
    private $uSes;
    private $uCore,$col_id,
        $md_width,$lg_width,$sm_width,$xs_width,
        $row_id,$col_pos;
    private function check_data() {
        if(!isset($_POST['col_id'],$_POST['field'])) $this->uFunc->error(10);
        
        $this->col_id=$_POST['col_id'];
        if(!uString::isDigits($this->col_id)) $this->uFunc->error(20);

        if($_POST['field']=='width') {
            if(!isset($_POST['md_width'],$_POST['lg_width'],$_POST['sm_width'],$_POST['xs_width'])) $this->uFunc->error(30);

            $this->md_width=trim($_POST['md_width']);
            if(!uString::isDigits($this->md_width)) die('{"status":"error","msg":"md has wrong format"}');
            $this->md_width=(int)$this->md_width;
            if($this->md_width>12||$this->md_width<0) die('{"status":"error","msg":"md is out of range"}');

                $this->lg_width=trim($_POST['lg_width']);
                $this->sm_width=trim($_POST['sm_width']);
                $this->xs_width=trim($_POST['xs_width']);

                if(!uString::isDigits($this->lg_width)) die('{"status":"error","msg":"lg has wrong format"}');
                if(!uString::isDigits($this->sm_width)) die('{"status":"error","msg":"sm has wrong format"}');
                if(!uString::isDigits($this->xs_width)) die('{"status":"error","msg":"xs has wrong format"}');

                $this->lg_width=(int)$this->lg_width;
                $this->sm_width=(int)$this->sm_width;
                $this->xs_width=(int)$this->xs_width;

                if($this->lg_width>12||$this->lg_width<0) die('{"status":"error","msg":"lg is out of range"}');
                if($this->sm_width>12||$this->sm_width<0) die('{"status":"error","msg":"sm is out of range"}');
                if($this->xs_width>12||$this->xs_width<0) die('{"status":"error","msg":"xs is out of range"}');
        }
        elseif($_POST['field']=='col_pos') {
            if(!isset($_POST['col_pos'],$_POST['row_id'])) $this->uFunc->error(40);
            $this->col_pos=$_POST['col_pos'];
            $this->row_id=$_POST['row_id'];
            if(!uString::isDigits($this->col_pos)) $this->uFunc->error(50);
            if(!uString::isDigits($this->row_id)) $this->uFunc->error(60);
        }
        elseif($_POST['field']=='col_css') {
            if(!isset($_POST['col_css'])) $this->uFunc->error(70);
        }
        else $this->uFunc->error(7);
    }
    private function update_col_width() {
        if(!$this->uCore->query("uPage","UPDATE
        `u235_cols`
        SET
        `lg_width`='".$this->lg_width."',
        `md_width`='".$this->md_width."',
        `sm_width`='".$this->sm_width."',
        `xs_width`='".$this->xs_width."'
        WHERE
        `col_id`='".$this->col_id."' AND
        `site_id`='".site_id."'
        ")) $this->uFunc->error(8);
    }
    private function move_down_cols_since_col_pos($col_pos) {
        if(!$this->uCore->query("uPage","UPDATE
        `u235_cols`
        SET
        `col_pos`=`col_pos`+1
        WHERE
        `col_pos`>=".$col_pos." AND
        `row_id`='".$this->row_id."' AND
        `site_id`='".site_id."'
        ")) $this->uFunc->error(9);
    }
    private function define_new_col_pos() {
        $col_pos=(int)$_POST['col_pos'];
        if($col_pos==0) {//Если 0, то вставляем на самый верх. Нужно посмотреть col_pos самого верхнего col и поставить над ним (значение может быть отрицательным). Создавать col с col_pos=0 нельзя. Это зарезервировано!
            //Смотрим col_pos самого верхнего элемента
            if(!$query=$this->uCore->query("uPage","SELECT
            `col_pos`
            FROM
            `u235_cols`
            WHERE
            `row_id`='".$this->row_id."' AND
            `site_id`='".site_id."'
            ORDER BY
            `col_pos` ASC
            LIMIT 1
            ")) $this->uFunc->error(10);
            if(mysqli_num_rows($query)) {
                $qr=$query->fetch_object();
                $next_col_pos=(int)$qr->col_pos;
                $new_col_pos=$next_col_pos-1;
                if($new_col_pos==0) $new_col_pos=-1;
            }
            else $new_col_pos=1;
        }
        else {//Вставляем под какой-то уже существующий col
            //Ищем col, у которого col_pos идет следующим за тем, под которым мы вставляем, чтобы понять, между какими значениями col_pos нам нужно впихнуть наш новый col
            if(!$query=$this->uCore->query("uPage","SELECT
            `col_pos`
            FROM
            `u235_cols`
            WHERE
            `col_pos`>".$col_pos." AND
            `row_id`='".$this->row_id."' AND
            `site_id`='".site_id."'
            ORDER BY
            `col_pos` ASC
            LIMIT 1
            ")) $this->uFunc->error(11);
            if(mysqli_num_rows($query)) {
                $qr=$query->fetch_object();
                $next_col_pos=(int)$qr->col_pos;
                if($next_col_pos-$col_pos>1) {
                    $new_col_pos=$col_pos+1;
                    if(!$new_col_pos) {
                        $new_col_pos=$col_pos+2;
                        if($new_col_pos>=$next_col_pos) {
                            //next_col_pos и ниже нужно подвинуть вниз
                            $this->move_down_cols_since_col_pos($next_col_pos);
                            if($next_col_pos<0) {//Нужно все, что 0 и выше опять подвинуть вниз
                                $this->move_down_cols_since_col_pos(0);
                            }
                        }
                    }


                }
                else {
                    //next_col_pos и ниже нужно подвинуть вниз
                    $this->move_down_cols_since_col_pos($next_col_pos);
                    if($next_col_pos<0) {//Нужно все, что 0 и выше опять подвинуть вниз
                        $this->move_down_cols_since_col_pos(0);
                    }
                    $new_col_pos=$col_pos+1;
                }
            }
            else $new_col_pos=$col_pos+1;
        }
        return $new_col_pos;
    }
    private function move_col() {
        $col_pos=$this->define_new_col_pos();
        //save new col

        if(!$this->uCore->query("uPage","UPDATE
        `u235_cols`
        SET
        `col_pos`='".$col_pos."',
        `row_id`='".$this->row_id."'
        WHERE
        `col_id`='".$this->col_id."' AND
        `site_id`='".site_id."'
        ")) $this->uFunc->error(12);

        //return result
        //Достаем все col с col_id и col_pos, чтобы передать браузеру информацию об изменениях
        if(!$query=$this->uCore->query("uPage","SELECT
        `col_id`,
        `col_pos`
        FROM
        `u235_cols`
        WHERE
        `row_id`='".$this->row_id."' AND
        `site_id`='".site_id."'
        ")) $this->uFunc->error(13);
        $result='{';
        while($col=$query->fetch_object()) {
            $result.='"col_'.$col->col_id.'":"'.$col->col_pos.'",';
        }
        $result.='"status":"done",
        "row_id":"'.$this->row_id.'",
        "col_id":"'.$this->col_id.'"
        }';

        $this->clear_cache();

        die($result);
    }
    private function save_col_css() {
        $this->uPage->save_col_css($this->col_id,$_POST['col_css']);

        die('{
        "status":"done",
        "col_id":"'.$this->col_id.'",
        "col_css":"'.rawurlencode($_POST['col_css']).'"
        }');
    }

    private function clear_cache() {
        $page_id=$this->uPage->get_page_id('col',$this->col_id);
        //clear cache
        $this->uPage->clear_cache($page_id);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new \uSes($this->uCore);
        if(!$this->uSes->access(7)) die("{'status' : 'forbidden'}");
        $this->uFunc=new uFunc($this->uCore);
        $this->uPage=new \uPage\common($this->uCore);

        $this->check_data();
        if($_POST['field']=='width') {
            $this->update_col_width();
            $this->clear_cache();
            die('{
            "status":"done",
            "col_id":"'.$this->col_id.'",
            "lg_width":"'.$this->lg_width.'",
            "md_width":"'.$this->md_width.'",
            "sm_width":"'.$this->sm_width.'",
            "xs_width":"'.$this->xs_width.'"
            }');
        }
        elseif($_POST['field']=='col_pos') {
            $this->move_col();
        }
        elseif($_POST['field']=='col_css') $this->save_col_css();
        else $this->uFunc->error(16);
    }
}
new admin_edit_col_bg($this);