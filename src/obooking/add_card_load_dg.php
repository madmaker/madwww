<?php
namespace obooking;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "obooking/classes/common.php";

class add_card_load_dg {
    private $client_balance;
    /**
     * @var int
     */
    private $client_id;
    /**
     * @var uFunc
     */
    private $uFunc;

    private function check_data() {
        if(!isset($_POST["client_id"])) {
            $this->uFunc->error(10);
        }
        $this->client_id=(int)$_POST["client_id"];
    }

    private function get_card_types() {
        try {
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            card_type_id,
            card_type_name,
            validity,
            price
            FROM 
            card_types 
            WHERE 
            site_id=:site_id
            ");
            $site_id=site_id;
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}
        return 0;
    }
    public function __construct (&$uCore) {
        $uSes=new uSes($uCore);
        if(!$uSes->access(2)) {
            print 'forbidden';
            exit;
        }

        $user_id=(int)$uSes->get_val('user_id');
        $obooking=new common($uCore);
        $is_admin=$obooking->is_admin($user_id);

        if(!$is_admin) {
            print 'forbidden';
            exit;
        }

        $this->uFunc=new uFunc($uCore);

        $this->check_data();


        if(!$client_info=$obooking->get_client_info("client_balance",$this->client_id)) {
            $this->uFunc->error(30);
        }
        $client_balance=$client_info->client_balance;
        ?>

        <input type="hidden" id="obooking_calendar_new_card_client_id" value="<?=$this->client_id?>">

        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <label for="obooking_calendar_new_card_type">???????????????? ?????? ??????????</label>

                    <div class="input-group">
                        <select
                                class="form-control"
                                id="obooking_calendar_new_card_type"
                                onchange="obooking_inline_create.add_new_card_onchange()"
                        >
                            <?if($stm=$this->get_card_types()) {
                                while($qr=$stm->fetch(PDO::FETCH_OBJ)) {?>
                                    <option

                                            id="obooking_calendar_new_card_type_option_<?=$qr->card_type_id?>"
                                            data-price="<?=$qr->price?>"
                                            data-validity="<?=$qr->validity?>"
                                            value="<?=$qr->card_type_id?>"
                                    ><?=$qr->card_type_name?></option>
                                <?}
                            }?>
                        </select>
                        <span class="input-group-btn">
                            <button class="btn btn-default" type="button" title="?????????????????????????? ??????????" onclick="obooking_inline_create.edit_cards_types_init()"><span class="icon-pencil"></span></button>
                        </span>
                    </div><!-- /input-group -->
                </div>
                <div class="col-md-6">
                    <label for="obooking_calendar_new_card_number">?????????? ??????????</label>
                    <input type="text" class="form-control" id="obooking_calendar_new_card_number">
                </div>
            </div>
            <div class="row">&nbsp;</div>
            <div class="row">
                <div class="col-md-6">
                    <label for="obooking_calendar_new_card_start_datepicker_input">???????????? ???????????????? ??????????</label>
                    <div id="obooking_calendar_new_card_start_datepicker" data-date=""></div>
                    <input id="obooking_calendar_new_card_start_datepicker_input" type="hidden" value="">
                </div>
                <div class="col-md-6">
                    <label for="obooking_calendar_new_card_datepicker_input">?????????????????? ???????????????? ??????????</label>
                    <div id="obooking_calendar_new_card_datepicker" data-date=""></div>
                    <input id="obooking_calendar_new_card_datepicker_input" type="hidden" value="">
                </div>
            </div>


            <div>
                <h3>???????????? ??????????</h3>
                <div class="row">
                    <div class="col-md-12">
                        <label>?????????????? ???????????? ??????????????: </label>
                        <span class="form-control-static"><?=$client_balance?></span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <label for="obooking_calendar_new_card_payment_input">?????????? ?? ????????????</label>
                        <input type="text" class="form-control" id="obooking_calendar_new_card_payment_input">
                        <span class="help-block muted">?????????? ?????????? ?????????????? ?? ??????????????</span>
                        <div>&nbsp;</div>
                        <label for="obooking_calendar_new_card_office_id">????????????</label>
                        <select id="obooking_calendar_new_card_office_id" class="form-control"><?php
                            $q_offices=$obooking->get_offices("office_id,office_name");
                            while($office=$q_offices->fetch(PDO::FETCH_OBJ)) {?>
                                <option value="<?=$office->office_id?>"><?=$office->office_name?></option>
                            <?}?></select>
                    </div>
                    <div class="col-md-6">
                        <label for="obooking_calendar_new_card_payment_paid_input">???????????????? ???? ??????????</label>
                        <input type="text" class="form-control" id="obooking_calendar_new_card_payment_paid_input">
                        <span class="help-block muted">??????????, ?????????????? ???????????????? ????????????</span>
                        <div>&nbsp;</div>
                        <label for="obooking_calendar_new_card_payment_type_selectbox">???????????? ????????????</label>
                        <select id="obooking_calendar_new_card_payment_type_selectbox" class="form-control">
                            <option value="0">??????????????????</option>
                            <option value="1">????????????</option>
                            <option value="2">????????????</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

    <?}
}
new add_card_load_dg($this);
