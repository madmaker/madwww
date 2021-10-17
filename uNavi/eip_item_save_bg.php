<?php
namespace uNavi\admin;
use PDO;
use PDOException;
use processors\uFunc;
use uNavi\common\uNavi;
use uString;

//require_once 'lib/htmlpurifier/library/HTMLPurifier.auto.php';
require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "processors/uMenu.php";
require_once "uNavi/classes/uNavi.php";

class eip_item_save_bg {
    public $uSes;
    public $uFunc;
    public $uMenu;
    public $purifier;
    private $new_item;
    private $uNavi;
    private $is_system_btn;
    private $show_label;
    private $uCore,
        $item_id,$access,$position,$title,$link,$target,$indent,$apply4children,
        $item,$cat_id,
        $q_item_indent;
    private function check_data() {
        if(!isset(
        $_POST['item_id'],
        $_POST['cat_id'],
        $_POST['access'],
        $_POST['position'],
        $_POST['title'],
        $_POST['link'],
        $_POST['target'],
        $_POST['indent'],
        $_POST['apply4children'],
        $_POST['show_label'],
        $_POST['is_system_btn']
        )) $this->uFunc->error(40);

        $this->item_id=$_POST['item_id'];
        $this->cat_id=(int)$_POST['cat_id'];
        $this->access=(int)$_POST['access'];
        $indent=(int)$_POST['indent'];
        $this->position=(int)$_POST['position'];
        $this->title=trim($_POST['title']);
        $this->link=$_POST['link'];
        $this->target=$_POST['target'];
        $this->show_label=(int)$_POST['show_label'];
        $this->is_system_btn=(int)$_POST['is_system_btn'];

        if(!uString::isDigits($this->item_id)&&$this->item_id!='new') $this->uFunc->error(50);
        if(!strlen($this->title)) die('{"error","title_is_empty"}');

        if($this->target!='_blank') $this->target='_self';

        $this->apply4children=$_POST['apply4children'];
        if($this->apply4children=='1') $this->apply4children=true;
        else $this->apply4children=false;


        if($this->item_id!='new') {
            $this->new_item=0;
            //get item's info
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uNavi")->prepare("SELECT
                id,
                position,
                indent,
                cat_id
                FROM
                u235_menu
                WHERE
                id=:item_id AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_id', $this->item_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                if(!$this->item=$stm->fetch(PDO::FETCH_OBJ)) return 0;
            }
            catch(PDOException $e) {$this->uFunc->error('100'/*.$e->getMessage()*/);}

            $indent=$indent-$this->item->indent;
            if($indent>0) $this->q_item_indent="indent=`indent`+".$indent;
            else $this->q_item_indent="indent=`indent`-".abs($indent);
        }
        else {
            $this->new_item=1;
            $this->move_down_starting_with_pos($this->position,$this->cat_id);
            $this->item_id=$this->uNavi->create_new_item($this->cat_id,$this->title,$indent,$this->position);
            $this->q_item_indent="indent=0";
        }
        return 0;
    }

    private function has_children($cat_id,$position) {
        //check if next item is child
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uNavi")->prepare("SELECT
            id,
            indent
            FROM
            u235_menu
            WHERE
            position>:position AND
            cat_id=:cat_id AND
            site_id=:site_id
            ORDER BY
            position ASC
            LIMIT 1
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_id', $cat_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':position', $position,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($item=$stm->fetch(PDO::FETCH_OBJ)) {
                if($item->indent>$this->item->indent) return true;
                else return false;
            }
            else return false;
        }
        catch(PDOException $e) {$this->uFunc->error('120'/*.$e->getMessage()*/);}
        return 0;
    }
    private function get_first_non_child_pos($cat_id,$indent,$position) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uNavi")->prepare(/** @lang mysql */
                "SELECT
                position
                FROM
                u235_menu
                WHERE
                position>:position AND
                indent<=:indent AND
                cat_id=:cat_id AND
                site_id=:site_id
                ORDER BY
                position ASC
                LIMIT 1
                ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_id', $cat_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':indent', $indent,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':position', $position,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($item=$stm->fetch(PDO::FETCH_OBJ)) return $item->position;
            else return $this->find_last_position($cat_id)+1;
        }
        catch(PDOException $e) {$this->uFunc->error('130'/*.$e->getMessage()*/);}
        return 0;
    }
    private function get_children($cat_id,$first_non_child_pos,$position) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uNavi")->prepare("SELECT
            id,
            position
            FROM
            u235_menu
            WHERE
            position>:position AND
            position<:first_non_child_pos AND
            cat_id=:cat_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_id', $cat_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':first_non_child_pos', $first_non_child_pos,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':position', $position,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('140'/*.$e->getMessage()*/);}
        return 0;
    }

    private function check_if_position_is_free($item_position,$cat_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uNavi")->prepare("SELECT
            id
            FROM
            u235_menu
            WHERE
            position=:position AND
            cat_id=:cat_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_id', $cat_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':position', $item_position,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($stm->fetch(PDO::FETCH_OBJ)) return 0;
            else return 1;
        }
        catch(PDOException $e) {$this->uFunc->error('150'/*.$e->getMessage()*/);}
        return 0;
    }
    private function check_if_any_positions_exists_after($item_position,$cat_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uNavi")->prepare(/** @lang mysql */
                "SELECT
            id
            FROM
            u235_menu
            WHERE
            position>:position AND
            cat_id=:cat_id AND
            site_id=:site_id
            LIMIT 1
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_id', $cat_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':position', $item_position,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($stm->fetch(PDO::FETCH_OBJ)) return 1;
            else return 0;
        }
        catch(PDOException $e) {$this->uFunc->error('160'/*.$e->getMessage()*/);}

        return 0;
    }
    private function save_position($item_position,$item_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uNavi")->prepare("UPDATE
            u235_menu
            SET
            position=:position,
            ".$this->q_item_indent."
            WHERE
            id=:item_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':position', $item_position,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('170'/*.$e->getMessage()*/);}
    }
    private function find_last_position($cat_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uNavi")->prepare(/** @lang mysql */
                "SELECT
            position
            FROM
            u235_menu
            WHERE
            cat_id=:cat_id AND
            site_id=:site_id
            ORDER BY
            position DESC
            LIMIT 1
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_id', $cat_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($item=$stm->fetch(PDO::FETCH_OBJ)) return $item->position;
            else return 0;
        }
        catch(PDOException $e) {$this->uFunc->error('180'/*.$e->getMessage()*/);}

        return 0;
    }
    private function move_down_starting_with_pos($pos,$cat_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uNavi")->prepare(/** @lang mysql */
                "UPDATE
            u235_menu
            SET
            position=`position`+1
            WHERE
            position>=:position AND
            cat_id=:cat_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_id', $cat_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':position', $pos,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('190'/*.$e->getMessage()*/);}
    }
    private function move_up_starting_with_pos($pos,$cat_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uNavi")->prepare(/** @lang mysql */
                "UPDATE
            u235_menu
            SET
            position=`position`-1
            WHERE
            cat_id=:cat_id AND
            position>=:position AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':position', $pos,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_id', $cat_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('200'/*.$e->getMessage()*/);}
    }
    private function item_move($cat_id,$position,$item_id,$cur_position) {
        //check if new position is free
        if($this->check_if_position_is_free($position,$cat_id)) {
            //check if there are positions after this
            if($this->check_if_any_positions_exists_after($position,$cat_id)) {//Next positions are occupied
                //find last position
//                $last_pos=$this->find_last_position($cat_id);
                //move all next positions down
                $this->move_down_starting_with_pos($position+1,$cat_id);
                //save new position
                $this->save_position($position,$item_id);
            }
            else {//Next positions are free
                //save new position
                $this->save_position($position,$item_id);
            }
        }
        else {//new position is occupied
            //move all positions down starting with new one
            $this->move_down_starting_with_pos($position,$cat_id);
            //save new position
            $this->save_position($position,$item_id);
            if($position>$cur_position)//only if we move down - move all positions up starting with old one
                $this->move_up_starting_with_pos($cur_position,$cat_id);
        }
    }
    private function prevent_duplicate_positions($cat_id) {
        //get duplicate id
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uNavi")->prepare("SELECT 
                id,
                cat_id
                FROM
                u235_menu
                WHERE
                cat_id=:cat_id AND
                site_id=:site_id
                GROUP BY
                position
                HAVING COUNT(DISTINCT id)>1
                LIMIT 1
                ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_id', $cat_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('210'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedVariableInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        if($item=$stm->fetch(PDO::FETCH_OBJ)) {
            //move position down
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uNavi")->prepare("UPDATE
                u235_menu
                SET
                position=`position`+1
                WHERE
                id=:id AND
                cat_id=:cat_id AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_id', $cat_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':id', $item->id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('220'/*.$e->getMessage()*/);}
            $this->prevent_duplicate_positions($item->cat_id);
        }
    }


    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new \uSes($this->uCore);
        $this->uMenu=new \uMenu($this->uCore);
        $this->uNavi=new uNavi($this->uCore);

        $config = \HTMLPurifier_Config::createDefault();
        $this->purifier = new \HTMLPurifier($config);


        if(!$this->uSes->access(7)) die("{'status' : 'forbidden'}");

        $this->check_data();

        if(!$this->new_item) {
            if ($this->apply4children) {//Move with children is checked
                if ($this->has_children($this->item->cat_id, $this->item->position)) {//item has children
                    $children = $this->get_children($this->item->cat_id, $this->get_first_non_child_pos($this->item->cat_id, $this->item->indent, $this->item->position), $this->item->position);
                    $this->item_move($this->item->cat_id, $this->position, $this->item_id, $this->item->position);
                    /** @noinspection PhpUndefinedMethodInspection */
                    while ($item = $children->fetch(PDO::FETCH_OBJ)) {
                        $this->position++;
                        $this->item_move($this->item->cat_id, $this->position, $item->id, $item->position);
                    }
                } //item has no children
                else $this->item_move($this->item->cat_id, $this->position, $this->item_id, $this->item->position);
            } //Move without children
            else $this->item_move($this->item->cat_id, $this->position, $this->item_id, $this->item->position);
        }

        $this->prevent_duplicate_positions($this->cat_id);

        $this->uNavi->update_item($this->item_id,$this->access,$this->target,$this->show_label,$this->is_system_btn,$this->link,$this->title);
        echo "{
        'status':'done',
        'cat_id2update':'".$this->cat_id."',
        'apply4children':'".$this->apply4children."',
        'cat_new_html':'".rawurlencode($this->uMenu->return_cat_id_content($this->cat_id,1))."'
        }";

        $this->uMenu->clean_cache($this->cat_id);
    }
}
new eip_item_save_bg($this);
