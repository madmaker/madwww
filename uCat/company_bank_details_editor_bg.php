<?php
namespace uCat\admin;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class company_bank_details_editor_bg {
    private $uFunc;
    private $uSes;
    private $uCore;

    private function print_editor() {
        if(!$company_bank_details=$this->uFunc->get_company_bank_details()) {
            $company_bank_details=new \stdClass();

            $company_bank_details->bank_id="";
            $company_bank_details->bill_prefix="";
            $company_bank_details->bank_name="";
            $company_bank_details->bank_account_number="";
            $company_bank_details->company_vat_number="";
            $company_bank_details->company_account_number="";
            $company_bank_details->company_name="";
            $company_bank_details->company_tax_info_1="";
            $company_bank_details->company_address="";
            $company_bank_details->company_stamp_url="";
            $company_bank_details->company_signature_url="";
            $company_bank_details->vat_percent="";
            $company_bank_details->company_signature_post="";
            $company_bank_details->company_signature_name="";
        }?>

        <div class="container-fluid">
            <div class="row">
                <h3>Банк</h3>

                <div class="form-group col-md-6">
                    <label for="company_bank_details_bank_id">БИК</label>
                    <input class="form-control" type="text" id="company_bank_details_bank_id" value="<?=htmlspecialchars($company_bank_details->bank_id)?>">
                </div>

                <div class="form-group col-md-6">
                    <label for="company_bank_details_bank_account_number">Кор. счет банка</label>
                    <input class="form-control" type="text" id="company_bank_details_bank_account_number" value="<?=htmlspecialchars($company_bank_details->bank_account_number)?>">
                </div>


                <div class="form-group col-md-12">
                    <label for="company_bank_details_bank_name">Название банка</label>
                    <input class="form-control" type="text" id="company_bank_details_bank_name" value="<?=htmlspecialchars($company_bank_details->bank_name)?>">
                </div>

                <h3>Компания</h3>

                <div class="form-group col-md-12">
                    <label for="company_bank_details_company_account_number">Номер расчетного счета</label>
                    <input class="form-control" type="text" id="company_bank_details_company_account_number" value="<?=htmlspecialchars($company_bank_details->company_account_number)?>">
                </div>

                <div class="form-group col-md-4">
                    <label for="company_bank_details_company_vat_number">ИНН</label>
                    <input class="form-control" type="text" id="company_bank_details_company_vat_number" value="<?=htmlspecialchars($company_bank_details->company_vat_number)?>">
                </div>

                <div class="form-group col-md-4">
                    <label for="company_bank_details_company_tax_info_1">КПП</label>
                    <input class="form-control" type="text" id="company_bank_details_company_tax_info_1" value="<?=htmlspecialchars($company_bank_details->company_tax_info_1)?>">
                </div>

                <div class="form-group col-md-4">
                    <label for="company_bank_details_vat_percent">Ставка НДС</label>
                    <select class="form-control" id="company_bank_details_vat_percent">
                        <option value="0" <?=(int)$company_bank_details->vat_percent===0?'selected':''?>>Без НДС</option>
                        <option value="10" <?=(int)$company_bank_details->vat_percent===10?'selected':''?>>10%</option>
                        <option value="20" <?=(int)$company_bank_details->vat_percent===20?'selected':''?>>20%</option>
                    </select>
                </div>

                <div class="form-group col-md-12">
                    <label for="company_bank_details_company_name">Название компании</label>
                    <input class="form-control" type="text" id="company_bank_details_company_name" value="<?=htmlspecialchars($company_bank_details->company_name)?>">
                </div>

                <div class="form-group col-md-12">
                    <label for="company_bank_details_company_address">Юридический адрес</label>
                    <input class="form-control" type="text" id="company_bank_details_company_address" value="<?=htmlspecialchars($company_bank_details->company_address)?>">
                </div>

                <div class="form-group col-md-6">
                    <label for="company_signature_post">Должность директора</label>
                    <input class="form-control" type="text" id="company_signature_post" value="<?=htmlspecialchars($company_bank_details->company_signature_post)?>">
                </div>

                <div class="form-group col-md-6">
                    <label for="company_signature_name">ФИО директора</label>
                    <input class="form-control" type="text" id="company_signature_name" value="<?=htmlspecialchars($company_bank_details->company_signature_name)?>">
                </div>

                <div class="form-group col-md-12">
                    <label for="bill_prefix">Префикс к номеру счета</label>
                    <input class="form-control" type="text" id="bill_prefix" value="<?=htmlspecialchars($company_bank_details->bill_prefix)?>">
                </div>


                <div class="form-group col-md-12">
                    <label for="company_bank_details_company_stamp_url">URL скана печати организации</label>
                    <input class="form-control" type="url" id="company_bank_details_company_stamp_url" value="<?=htmlspecialchars($company_bank_details->company_stamp_url)?>">
                </div>

                <div class="form-group col-md-12">
                    <label for="company_bank_details_company_signature_url">URL скана подписи и расшифровки подписи руководителя</label>
                    <input class="form-control" type="url" id="company_bank_details_company_signature_url" value="<?=htmlspecialchars($company_bank_details->company_signature_url)?>">
                </div>
            </div>
        </div>
    <?}
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(25)&&!$this->uSes->access(7)) die("{'status' : 'forbidden'}");
        $this->uFunc=new uFunc($this->uCore);

        $this->print_editor();
    }
}
new company_bank_details_editor_bg($this);