<?php
namespace obooking;
use PDO;
use processors\uFunc;
use uSes;

require_once "processors/uSes.php";
require_once 'processors/classes/uFunc.php';
require_once 'obooking/classes/common.php';

class new_record_dg_loader_bg {
    /**
     * @var float
     */
    private $price_without_card;
    private $class_name;
    private $office_name;
    private $rec_type_name;
    private $price;
    private $notes;
    private $duration;
    private $manager_id;
    private $client_id;
    private $class_id;
    private $rec_type_id;
    private $rec_id;
    private $edit;
    private $timestamp;
    private $office_id;
    private $obooking;
    private $uFunc;
    private function check_data() {
        if(isset($_POST['edit'])) {
            $this->edit = 1;
        }
        else {
            $this->edit = 0;
        }

        if($this->edit) {
            if(!isset($_POST['rec_id'])) {
                $this->uFunc->error(10);
            }
            $this->rec_id=(int)$_POST['rec_id'];
            $rec_info=$this->obooking->get_record_info('rec_type,office_id,class_id,manager_id,timestamp,duration,notes,price,price_without_card',$this->rec_id);
            $this->office_id=(int)$rec_info->office_id;
            $this->rec_type_id=(int)$rec_info->rec_type;
            $this->class_id=(int)$rec_info->class_id;
            $this->manager_id=(int)$rec_info->manager_id;
            $this->timestamp=(int)$rec_info->timestamp;
            $this->duration=(int)$rec_info->duration;
            $this->notes=$rec_info->notes;
            $this->price=(float)$rec_info->price;
            $this->price_without_card=(float)$rec_info->price_without_card;
            if(!$rec_type_info=$this->obooking->get_rec_type_info('rec_type_name',$this->rec_type_id)) {
                $this->uFunc->error(20, 1);
            }
            $this->rec_type_name=$rec_type_info->rec_type_name;
            if(!$office_info=$this->obooking->get_office_info('office_name',$this->office_id)) {
                $this->uFunc->error(30, 1);
            }
            $this->office_name=$office_info->office_name;
            if(!$class_info=$this->obooking->get_class_info('class_name',$this->class_id)) {
                $this->uFunc->error(40, 1);
            }
            $this->class_name=$class_info->class_name;
        }
        else {
            $this->rec_id=0;
            if (isset($_POST['office_id'])) {
                $this->office_id = (int)$_POST['office_id'];
                if (!$this->obooking->get_office_info('office_id', $this->office_id)) {
                    $this->office_id = $this->obooking->get_first_office_id();
                }
            }
            $this->office_id = $this->obooking->get_first_office_id();

            if (isset($_POST['timestamp'])) {
                $this->timestamp = (int)$_POST['timestamp'];
            }
            else {
                $this->timestamp = time();
            }
        }
    }
    private function draw_dialog() {?>
        <input type="hidden" id="obooking_calendar_new_record_rec_id_input" value="<?=$this->rec_id?>">
        <div class="container-fluid">
            <div style="display:table; width: 100%">
                <div class="col-md-12">
                    <div class="form-group col-md-12"  id="obooking_calendar_new_record_client_id_form_group">
                        <label><a href="javascript:void(0)" onclick="obooking_inline_edit.get_clients_list(1)">Ученик <span class="icon-plus"></span></a></label>
                        <input type="hidden" id="obooking_calendar_new_record_client_id" value="<?php
                        if($this->edit) {
                            $clients_stm=$this->obooking->get_rec_clients_info('clients.client_id AS client_id, client_balance, client_name,client_lastname,client_phone,client_email, records_clients.status AS status, records_clients.trial AS trial',$this->rec_id);
                            $clients_ar=$clients_stm->fetchAll(PDO::FETCH_OBJ);
                            foreach ($clients_ar as $iValue) {
                                $client= $iValue;
                                print '#';
                                print $client->client_id;
                                print '#';
                            }
                        }
                        ?>">
                        <div id="obooking_calendar_new_record_client_input"><?php
                        if($this->edit) {
                            /** @noinspection PhpUndefinedVariableInspection */
                            foreach ($clients_ar as $iValue) {
                                $client= $iValue;
                                $classes_left=$this->obooking->get_client_subscription_classes_left($client->client_id);
                                $client->status=(int)$client->status;
                                $client->trial=(int)$client->trial;?>
                                <div id="record_editor_client_<?=$client->client_id?>"><span class="icon-cancel" onclick="obooking_calendar.record_editor_remove_client(<?=$client->client_id?>)"></span>
                                <span onclick="obooking_inline_edit.edit_client_init(<?=$client->client_id?>)">
                                <?=htmlspecialchars($client->client_name)?> <?=htmlspecialchars($client->client_lastname)?>
                                </span>
                                <?if($client->client_phone!== '') {?>
                                    &nbsp;<a href="tel:<?=$client->client_phone?>"><span class='icon-phone-1'></span></a>&nbsp;
                                    <?=$client->client_phone?>
                                <?}
                                if($client->client_email!== '') {?>
                                    &nbsp;&nbsp;<a href="mailto:<?=$client->client_email?>"><span class='icon-mail-alt'></span></a>&nbsp;
                                    <?=$client->client_email?>
                                <?}

                                $has_card=0;
                                    if($card_data=$this->obooking->get_client_longest_card($client->client_id)) {
                                        $has_card=1;
                                        print ' &nbsp;';
                                        print '<span class="icon-credit-card"></span> ';
//                                        print $this->obooking->card_type_id2name($card_data->card_type_id);
//                                        print " <b>№</b> ";
//                                        print $card_data->card_number;
                                        if($card_data->valid_thru<time()) {
                                            $has_card=0;
                                            print ' <span class="bg-danger"> ';
                                        }
//                                        print ' <span class="icon-clock"></span>';
//                                        print date("d.m.Y", $card_data->start_date);
//                                        print "-";
                                        print date('d.m.Y', $card_data->valid_thru);
                                        if($card_data->valid_thru<time()) {
                                            print ' - Карта просрочена!</span>';
                                        }
                                    }
                                    ?>
                                &nbsp;&nbsp;
                                <?if($client->status===-1) {//-1 Не отмечено
                                    //1 - Пришел
                                    //0 - Прогулял
                                    ?>
                                    <button
                                    id="rec_is_trial_btn_<?=$this->rec_id?>'_'<?=$client->client_id?>"
                                    class="btn btn-<?=($client->trial? 'primary' : 'default')?> btn-xs"
                                    data-is_trial="<?=$client->trial?>"
                                    data-rec_id="<?=$this->rec_id?>"
                                    data-client_id="<?=$client->client_id?>"
                                    onclick="obooking_calendar.rec_is_trial_init(this)">Это пробное занятие?</button>&nbsp;
                                    <div class="btn-group"  id="rec_payment_btns_<?=$this->rec_id?>_<?=$client->client_id?>">
                                    <button class="btn btn-danger btn-xs" onclick="obooking_calendar.rec_payment_init(<?=$this->rec_id?>,<?=$client->client_id?>,<?=$client->client_balance?>,<?=$classes_left?>,<?=$this->price?>,<?=$this->price_without_card?>,<?=$has_card?>,0)">Прогулял</button>
                                    <button class="btn btn-success btn-xs" onclick="obooking_calendar.rec_payment_init(<?=$this->rec_id?>,<?=$client->client_id?>,<?=$client->client_balance?>,<?=$classes_left?>,<?=$this->price?>,<?=$this->price_without_card?>,<?=$has_card?>,1)">Посетил</button>
                                    </div>
                                <?}
                                elseif($client->status===1) {?>
                                    <span class="icon-ok"></span> Посетил
                                <?}
                                elseif($client->status===0) {?>
                                    <span class="icon-cancel"></span> Прогулял
                                <?}?>
                                <p></p>
                                </div>
                            <?}
                        }?>
                        </div>
                        <p class="help-block hidden" id="obooking_calendar_new_record_client_id_hint"></p>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-group col-md-12" id="obooking_calendar_new_record_manager_id_form_group">
                        <label><a href="javascript:void(0)" onclick="obooking_inline_edit.get_managers_list(1)" >Наставник</a></label>
                        <input type="hidden" id="obooking_calendar_new_record_manager_id"  value="<?=$this->edit?$this->manager_id: '' ?>">
                        <?if($this->edit) {
                            $manager = $this->obooking->get_manager_info('manager_name,manager_lastname,manager_phone,manager_email', $this->manager_id);
                        } ?>
                        <div id="obooking_calendar_new_record_manager_input" onclick="obooking_inline_edit.edit_manager_init($('#obooking_calendar_new_record_manager_id').val())"><?php
                        if($this->edit) {
                            /** @noinspection PhpUndefinedVariableInspection */
                            print htmlspecialchars($manager->manager_name);
                            print ' ';
                            print htmlspecialchars($manager->manager_lastname);
                            if($manager->manager_phone!== '') {
                                print " <a href='tel:$manager->manager_phone'><span class='icon-phone-1'></a></span> ";
                                print $manager->manager_phone;
                            }
                            if($manager->manager_email!== '') {
                                print "  <a href='mailto:$manager->manager_email'><span class='icon-mail-alt'></span></a> ";
                                print $manager->manager_email;
                            }
                        }?></div>
                        <p class="help-block hidden" id="obooking_calendar_new_record_manager_id_hint"></p>
                    </div>
                </div>

                <div style="display:table; width:100%">
                    <div class="col-md-6">
                        <div class="col-md-12" id="obooking_calendar_new_record_date_form_group">
                            <?php
                            if($this->edit) {
                                $record_date = date("d.m.Y", $this->timestamp);
                            }
                            else {
                                $record_date = date("d.m.Y");
                            }
                            ?>
                            <div id="obooking_calendar_new_record_date_datepicker" data-date="<?=$record_date?>"></div>
                            <input id="obooking_calendar_new_record_date_input" type="hidden" value="<?=$record_date?>">
                            <p class="help-block hidden" id="obooking_calendar_new_record_date_input_hint"></p>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group col-md-12" id="obooking_calendar_new_record_class_id_form_group">
                            <label><a href="javascript:void(0);" onclick="obooking_inline_edit.get_classes_list(1)">Место</a></label>
                            <input type="hidden" id="obooking_calendar_new_record_class_id"  value="<?=$this->edit?$this->class_id: '' ?>">
                            <input type="hidden" id="obooking_calendar_new_record_office_id"  value="<?=$this->edit?$this->office_id: '' ?>">
                            <input type="hidden" id="obooking_calendar_new_record_class_name"  value="<?=$this->edit?htmlspecialchars($this->class_name): '' ?>">

                            <div id="obooking_calendar_new_record_class_input" onclick="obooking_inline_edit.edit_class_init($('#obooking_calendar_new_record_class_id').val())"><?php
                                if($this->edit) {?>
                                    Класс: <?php
                                    print htmlspecialchars($this->class_name);
                                    print '<br>Филиал: ';
                                    print htmlspecialchars($this->office_name);
                                }?></div>
                            <p class="help-block hidden" id="obooking_calendar_new_record_class_input_hint"></p>
                        </div>

                        <div class="form-group col-md-12" id="obooking_calendar_new_record_rec_type_id_form_group">
                            <label><a href="javascript:void(0);" onclick="obooking_inline_edit.get_rec_types_list(1)">Тип занятия</a></label>
                            <input type="hidden" id="obooking_calendar_new_record_rec_type_id"  value="<?=$this->edit?$this->rec_type_id: '' ?>">
                            <div id="obooking_calendar_new_record_rec_type_input" onclick="obooking_inline_edit.edit_rec_type_init($('#obooking_calendar_new_record_rec_type_id').val())"><?php
                                if($this->edit) {
                                    print htmlspecialchars($this->rec_type_name);
                                }?></div>
                            <p class="help-block hidden" id="obooking_calendar_new_record_rec_type_input_hint"></p>
                        </div>


                        <div class="form-group col-md-12" id="obooking_calendar_new_record_price_form_group">
                            <label for="obooking_calendar_new_record_price_input" class="control-label">Стоимость занятия по клубной карте</label>
                            <input type="text" class="form-control" id="obooking_calendar_new_record_price_input" value="<?=$this->edit?$this->price: '' ?>">
                            <p class="help-block hidden" id="obooking_calendar_new_record_price_hint"></p>
                        </div>
                        <div class="form-group col-md-12" id="obooking_calendar_new_record_price_without_card_form_group">
                            <label for="obooking_calendar_new_record_price_without_card_input" class="control-label">Стоимость занятия без карты</label>
                            <input type="text" class="form-control" id="obooking_calendar_new_record_price_without_card_input" value="<?=$this->edit?$this->price_without_card: '' ?>">
                            <p class="help-block hidden" id="obooking_calendar_new_record_price_without_card_hint"></p>
                        </div>

                        <div class="form-group col-md-6" id="obooking_calendar_new_record_time_form_group">
                            <label for="obooking_calendar_new_record_time_input" class="control-label">Начало</label>
                            <div id="obooking_calendar_new_record_time_container" class="input-group clockpicker" data-placement="left" data-align="top" data-autoclose="true">
                                <input id="obooking_calendar_new_record_time_input" type="text" class="form-control" value="<?=($this->edit?date('H:i',$this->timestamp): '')?>">
                                <span class="input-group-addon">
                                  <span class="icon-clock"></span>
                              </span>
                            </div>
                            <p class="help-block hidden" id="obooking_calendar_new_record_time_input_hint"></p>
                        </div>

                        <div class="form-group col-md-6" id="obooking_calendar_new_record_duration_form_group">
                            <label for="obooking_calendar_new_record_duration_input" class="control-label">Длительность</label>
                            <div id="obooking_calendar_new_record_duration_container" class="input-group clockpicker" data-placement="left" data-align="top" data-autoclose="true">
                                <input id="obooking_calendar_new_record_duration_input" type="text" class="form-control" value="<?php
                                $duration_minutes=$this->duration/60;
                                for($hours=0;$duration_minutes>60;$hours++) {
                                    $duration_minutes-=60;
                                }
                                print ($this->edit?($hours. ':' .(int)$duration_minutes): '')?>">
                                <span class="input-group-addon">
                                  <span class="icon-clock"></span>
                              </span>
                            </div>
                            <p class="help-block hidden" id="obooking_calendar_new_record_duration_input_hint"></p>
                        </div>

                    </div>
                </div>


            <div style="display: table; width: 100%">
              <div class="col-md-12">
                  <div class="form-group col-md-12">
                      <label for="obooking_calendar_new_record_notes">Заметки</label>
                      <textarea class="form-control" id="obooking_calendar_new_record_notes"><?=$this->notes?></textarea>
                  </div>
              </div>
            </div>

        </div>
    <?}

    public function __construct (&$uCore) {
        $uSes=new uSes($uCore);
        if(!$uSes->access(2)) {
            print 'forbidden';
            exit;
        }
        $this->obooking=new common($uCore);
        $user_id=(int)$uSes->get_val('user_id');
        $is_admin=$this->obooking->is_admin($user_id);

        if(!$is_admin) {
            print 'forbidden';
            exit;
        }

        $this->uFunc=new uFunc($uCore);

        $this->check_data();
        $this->draw_dialog();
    }
}
new new_record_dg_loader_bg($this);
