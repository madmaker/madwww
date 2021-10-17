<?php
namespace uCat\admin;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class save_company_bank_details_bg {
    private $bill_prefix;
    private $company_signature_name;
    private $company_signature_post;
    private $vat_percent;
    private $company_signature_url;
    private $company_stamp_url;
    private $company_address;
    private $company_tax_info_1;
    private $company_name;
    private $company_account_number;
    private $company_vat_number;
    private $bank_account_number;
    private $bank_id;
    private $bank_name;
    private $uSes;
    private $uFunc;
    private $uCore;
    private function check_data() {
        if(!isset(
        $_POST["bank_id"],
        $_POST["bank_name"],
        $_POST["bank_account_number"],
        $_POST["company_vat_number"],
        $_POST["company_account_number"],
        $_POST["company_name"],
        $_POST["company_tax_info_1"],
        $_POST["company_address"],
        $_POST["company_stamp_url"],
        $_POST["company_signature_url"],
        $_POST["vat_percent"],
        $_POST["company_signature_post"],
        $_POST["company_signature_name"],
        $_POST["bill_prefix"]
        )) $this->uFunc->error(0,1);

        $this->bank_id=trim($_POST["bank_id"]);
        $this->bank_name=trim($_POST["bank_name"]);
        $this->bank_account_number=trim($_POST["bank_account_number"]);
        $this->company_vat_number=trim($_POST["company_vat_number"]);
        $this->company_account_number=trim($_POST["company_account_number"]);
        $this->company_name=trim($_POST["company_name"]);
        $this->company_tax_info_1=trim($_POST["company_tax_info_1"]);
        $this->company_address=trim($_POST["company_address"]);
        $this->company_stamp_url=trim($_POST["company_stamp_url"]);
        $this->company_signature_url=trim($_POST["company_signature_url"]);
        $this->vat_percent=trim($_POST["vat_percent"]);
        $this->company_signature_post=trim($_POST["company_signature_post"]);
        $this->company_signature_name=trim($_POST["company_signature_name"]);
        $this->bill_prefix=$_POST["bill_prefix"];
    }

    private function save_company_bank_details($site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("common")->prepare("REPLACE INTO  
            company_bank_details (
            bank_id, 
            bank_name, 
            bank_account_number, 
            company_vat_number, 
            company_account_number, 
            company_name, 
            company_tax_info_1, 
            company_address, 
            company_stamp_url, 
            company_signature_url, 
            site_id, 
            vat_percent,
            company_signature_post,
            company_signature_name,
            bill_prefix
            ) VALUES (
            :bank_id,
            :bank_name,
            :bank_account_number,
            :company_vat_number,
            :company_account_number,
            :company_name,
            :company_tax_info_1,
            :company_address,
            :company_stamp_url,
            :company_signature_url,
            :site_id,
            :vat_percent,
            :company_signature_post,
            :company_signature_name,
            :bill_prefix
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':bank_id',$this->bank_id,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':bank_name',$this->bank_name,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':bank_account_number',$this->bank_account_number,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':company_vat_number',$this->company_vat_number,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':company_account_number',$this->company_account_number,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':company_name',$this->company_name,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':company_tax_info_1',$this->company_tax_info_1,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':company_address',$this->company_address,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':company_stamp_url',$this->company_stamp_url,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':company_signature_url',$this->company_signature_url,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id',$site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':vat_percent',$this->vat_percent,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':company_signature_post',$this->company_signature_post,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':company_signature_name',$this->company_signature_name,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':bill_prefix',$this->bill_prefix,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('0'/*.$e->getMessage()*/);}
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(25)&&!$this->uSes->access(7)) die("{'status' : 'forbidden'}");
        $this->uFunc=new uFunc($this->uCore);

        $this->check_data();

        $this->save_company_bank_details();

        echo json_encode(array("status"=>"done"));
    }
}
new save_company_bank_details_bg($this);