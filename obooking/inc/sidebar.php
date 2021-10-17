<?php
namespace obooking;
use processors\uFunc;

require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";
require_once "obooking/classes/common.php";

class sidebar {
    /**
     * @var bool
     */
    private $is_admin;
    private $obooking;
    public function draw_panel() {
        if($this->is_admin) {?>
            <div id="obooking_right_panel"  class="col-md-2">
                <div id="obooking_right_panel_container">
                    <img src="obooking/img/logo.png" class="img-responsive" style="margin: 0 auto">

                    <p>&nbsp;</p>
    <!--                <button class="btn btn-primary"><span class="icon-play"></span> Начать работу</button>-->
                    <p>&nbsp;</p>
                    <ul class="list-unstyled">
                        <?$q_offices=$this->obooking->get_offices();
                        while($office=$q_offices->fetch(\PDO::FETCH_OBJ)) {
                            $first_letter=mb_substr($office->office_name,0,1);?>
                            <li><a href="#"><a href="/obooking/calendar?office=<?=$office->office_id?>"><span class="icon office_name"><?=$first_letter?></span> <?=$office->office_name?></a> <a href="/obooking/calendar?office=<?=$office->office_id?>&create=1" class="btn btn-xs btn-outline"><span class="icon-plus"></span></a></li>
                        <?}?>
                    </ul>
                    <ul class="list-unstyled">
                        <li><a href="/uAuth/profile"><span class="icon icon-user"></span> Мой профиль</a></li>
                        <li><a href="/obooking/clients"><span class="icon icon-users"></span> Ученики</a> <a href="javascript:void(0);"  onclick="obooking_inline_create.new_client_init()"><span class="icon-plus"></span></a></li>
                        <li><a href="/obooking/managers"><span class="icon icon-user-secret"></span> Наставники</a> <a href="javascript:void(0);"  onclick="obooking_inline_create.new_manager_init()"><span class="icon-plus"></span></a></li>
                        <li><a href="/obooking/administrators"><span class="icon icon-user"></span> Админы</a> <a href="javascript:void(0);"  onclick="obooking_inline_create.new_administrator_init()"><span class="icon-plus"></span></a></li>
                        <li><a href="/obooking/orders"><span class="icon icon-chat"></span> Заявки</a> <a href="javascript:void(0);"  onclick="obooking_inline_create.new_order_init()"><span class="icon-plus"></span></a></li>
                        <li><a href="/obooking/offices"><span class="icon icon-commerical-building"></span> Филиалы</a> <a href="javascript:void(0);" onclick="obooking_inline_create.new_office_init()"><span class="icon-plus"></span></a></li>
                        <li><a href="/obooking/classes"><span class="icon icon-graduation-cap"></span> Классы</a> <a href="javascript:void(0);"  onclick="obooking_inline_create.new_class_init()"><span class="icon-plus"></span></a></li>
                        <li><a href="/obooking/rec_types"><span class="icon icon-cubes"></span> Типы занятий</a> <a href="javascript:void(0);"  onclick="obooking_inline_create.new_rec_type_init()"><span class="icon-plus"></span></a></li>
                    </ul>
                </div>
            </div>
        <?}
    }

    public function __construct (&$uCore) {
        $uFunc=new uFunc($uCore);
        $uSes=new \uSes($uCore);
        $this->obooking=new common($uCore);
        if($uSes->access(2)) {
            $user_id=(int)$uSes->get_val('user_id');
            $this->is_admin=$this->obooking->is_admin($user_id);
        }
        else {
            $this->is_admin=false;
        }

        $uFunc->incCss(staticcontent_url . 'css/obooking/sidebar.min.css',2);
    }
}
