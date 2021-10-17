<?php
namespace uAuth;

class enter {
    private $uCore;

    public function text($str) {
        return $this->uCore->text(array('uAuth','enter'),$str);
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;

        $this->uCore->page['page_title']=$this->text("Page name"/*Вход*/);
    }
}
$uAuth=new enter($this);


ob_start();?>
<div class="jumbotron">
    <h1><?=$uAuth->text("Login - page header"/*Вход*/)?></h1>
    <p><?=$uAuth->text("Login - page text"/*Для работы Вам необходимо авторизоваться*/)?></p>
    <p><a href="javascript:void(0)" class="btn btn-primary btn-lg" onclick="uAuth_form.open()"><?=$uAuth->text("Login - btn text"/*Авторизоваться*/)?></a></p>

</div>
<?
$this->page_content=ob_get_contents();
ob_end_clean();

include "templates/template.php";
