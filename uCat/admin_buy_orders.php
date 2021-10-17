<?
class uCat_admin_buy_orders{
    private $uCore;
    public $status,$headerStatus,$hash;
    public $q_cats;
    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        $this->defStatus();
        $this->getOrders();
        $this->setPanel();

        $this->hash=$this->uCore->uFunc->sesHack();
    }
    private function defStatus(){
        if(isset($_GET['deleted'])) {
            $this->status="deleted";
            $this->headerStatus=' (Удаленные)';
        }
        else {
            $this->status="";
            $this->headerStatus='';
        }
    }
    private function getOrders(){
        //Orders list
        if(!$this->q_cats=$this->uCore->query('uCat',"SELECT
        `order_id`,
        `user_name`,
        `item_id`,
        `item_title`,
        `user_email`,
        `comment`,
        `timestamp`
        FROM
        `u235_buy_form_orders`
        WHERE
        (`order_status`='".$this->status."' ".($this->status==""?" OR `order_status` IS NULL ":'').") AND
        `site_id`='".site_id."'
        ORDER BY
        `order_id` ASC
        ")) $this->uCore->error(1);
    }
    private function setPanel() {
        $this->uCore->page_panel='<ul class="u235_top_menu">';
        if($this->status=='deleted') { $this->uCore->page_panel.='
            <li><a href="javascript:void(0)" id="uCat_restoreBtn">Восстановить</a></li>
            <li><a href="javascript:void(0)" id="uCat_watchBtn">Просмотр</a></li>
            <li><a href="'.u_sroot.$this->uCore->mod.'/'.$this->uCore->page_name.'" id="but_trash">Активные</a></li>
            ';
        } else { $this->uCore->page_panel.='
            <li><a href="javascript:void(0)" id="uCat_watchBtn">Просмотр</a></li>
            <li><a class="delBtn" href="javascript:void(0)" id="uCat_deleteBtn">Удалить</a></li>
            <li><a href="'.u_sroot.$this->uCore->mod.'/'.$this->uCore->page_name.'?deleted" id="but_trash">Удаленные</a></li>
            ';
        }
        $this->uCore->page_panel.='</ul>';
    }
}
$uCat=new uCat_admin_buy_orders($this);

$this->uFunc->incJs(u_sroot.'uCat/js/admin_buy_orders.min.js');
ob_start();
?>

<h1><?=$this->page['page_title']?></h1>
    <div class="uCat admin_cats u235_admin"><div class="list"></div></div>
    <script type="text/javascript">
        if(typeof uCat==="undefined") uCat={};
        //Articles
        uCat.order_id=[];
        uCat.user_name=[];
        uCat.order_item_id=[];
        uCat.item_title=[];
        uCat.user_email=[];
        uCat.comment=[];
        uCat.timestamp=[];
        uCat.order_show=[];
        uCat.order_sel=[];
        uCat.order_id2index=[];
        <? for($i=0;$data=$uCat->q_cats->fetch_object();$i++) { ?>
        i=<?=$i?>;
        uCat.order_id[i]=<?=$data->order_id?>;
        uCat.user_name[i]="<?=rawurlencode(uString::sql2text($data->user_name))?>";
        uCat.order_item_id[i]="<?=rawurlencode(uString::sql2text($data->item_id))?>";
        uCat.item_title[i]="<?=rawurlencode(uString::sql2text($data->item_title))?>";
        uCat.user_email[i]="<?=rawurlencode(uString::sql2text($data->user_email))?>";
        uCat.comment[i]="<?=rawurlencode(uString::sql2text($data->comment))?>";
        uCat.timestamp[i]="<?=date('d.m.Y H:i:s',$data->timestamp);?>";
        uCat.order_show[i]=true;
        uCat.order_sel[i]=false;
        uCat.order_id2index[uCat.order_id[i]]=i;
        <?}?>

        uCat.page_status="<?=$uCat->status?>";
    </script>


<? $this->page_content=ob_get_contents();
ob_end_clean();
include "templates/u235/template.php";
?>
