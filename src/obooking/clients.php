<?php

use obooking\common;
use processors\uFunc;

require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";
require_once "obooking/classes/common.php";

class clients {
    /**
     * @var uSes
     */
    public $uSes;
    /**
     * @var bool
     */
    public $is_admin;
    /**
     * @var int
     */
    public $client_id;
    private $uCore;

    public function __construct (&$uCore) {
        if(!isset($uCore)) {
            $uCore = new uCore();
        }

        $this->uSes=new uSes($uCore);
        $uFunc=new uFunc($uCore);
        $obooking=new common($uCore);

        if($this->uSes->access(2)) {
            $user_id=$this->uSes->get_val('user_id');
            $this->is_admin=$obooking->is_admin($user_id);

            if($this->is_admin) {
                if(isset($uCore->url_prop[1])) {
                    $this->client_id=(int)$uCore->url_prop[1];
                }
                else {
                    $this->client_id=0;
                }
                $uFunc->incJs(staticcontent_url . 'js/translator/translator.min.js');
                $uFunc->incJs(staticcontent_url . 'js/obooking/inline_create.min.js');
                $uFunc->incJs(staticcontent_url . 'js/obooking/clients.min.js');
                $uFunc->incJs(staticcontent_url . 'js/obooking/inline_edit.min.js');
                $uFunc->incCss(staticcontent_url . 'css/obooking/common.min.css');
                $uFunc->incCss(staticcontent_url . 'css/obooking/clients.min.css');

                $this->uCore->page['page_width'] = 1;
            }
        }
    }
}
$obooking=new clients($this);
ob_start();

if($obooking->uSes->access(2)) {
    if($obooking->is_admin) {
        require_once 'dialogs/inline_edit_dialogs.php';
        require_once 'dialogs/inline_create_dialogs.php';
        ?>
            <div id="obooking">
                <div id="obooking_clients">
                    <div>
                        <button type="button" class="btn btn-primary" onclick="obooking_inline_create.new_client_init()"><span class="icon-plus"></span> Добавить ученика</button>
                    </div>
                    <div id="clients_list"></div>
                </div>
            </div>

        <script type="text/javascript">
            if(typeof obooking==='undefined') obooking={};
            if(typeof obooking.clients==='undefined') obooking.clients={};
            obooking.clients.open_client_id=<?=$obooking->client_id?>;
        </script>
        <?php
    }
    else {?>
        <div class="jumbotron">
            <h1 class="page-header">У вас нет доступа к этой странице</h1>
        </div>
    <?}
}
else {?>
    <div class="jumbotron">
        <h1 class="page-header">Школа Рока</h1>
        <p>Пожалуйста, авторизуйтесь</p>
        <p><a href="javascript:void(0)" class="btn btn-primary btn-lg"  onclick="uAuth_form.open()">Авторизоваться</a></p>
    </div>
<?}

$this->page_content=ob_get_clean();

include 'templates/template.php';
