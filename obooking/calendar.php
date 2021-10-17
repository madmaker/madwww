<?php
namespace obooking;
use DateTime;
use PDO;
use processors\uFunc;
use uSes;

require_once 'processors/uSes.php';
require_once 'processors/classes/uFunc.php';
require_once 'obooking/classes/common.php';

class calendar {
    public $obooking;
    public $office_name;
    public $office_id;
    public $offices_ar;
    /**
     * @var uSes
     */
    public $uSes;
    /**
     * @var int
     */
    public $user_id;
    /**
     * @var bool
     */
    public $is_admin;

    private function check_data() {
        if(isset($_GET['office'])) {
            $this->office_id = (int)$_GET['office'];
        }
        else {
            $this->office_id = $this->obooking->get_first_office_id();
        }
    }

    private function get_office_info($office_id) {
        if(!$office=$this->obooking->get_office_info('office_id,office_name',$office_id)) {
            $office_id=$this->office_id=$this->obooking->get_first_office_id();
            $this->get_office_info($office_id);
        }
        $this->office_id=$office->office_id;
        $this->office_name=$office->office_name;
    }
    private function get_offices_list() {
        $q_offices=$this->obooking->get_offices();
        $this->offices_ar=$q_offices->fetchAll(PDO::FETCH_OBJ);
    }

    public function __construct (&$uCore) {
        $this->uSes=new uSes($uCore);
        $uFunc=new uFunc($uCore);
        $this->obooking=new common($uCore);

        if($this->uSes->access(2)) {
            $user_id=(int)$this->uSes->get_val('user_id');
            $this->is_admin=$this->obooking->is_admin($user_id);

            if($this->is_admin) {
                $this->check_data();
                $this->get_office_info($this->office_id);
                $this->get_offices_list();

                $uFunc->incJs(staticcontent_url . 'js/translator/translator.min.js');
                $uFunc->incJs(staticcontent_url . 'js/obooking/inline_create.min.js');
                $uFunc->incCss(staticcontent_url . 'css/obooking/common.min.css');
                $uFunc->incCss(staticcontent_url . 'css/obooking/calendar.min.css');
                $uFunc->incJs(staticcontent_url . 'js/obooking/calendar.min.js');

                $uCore->page['page_width'] = 1;
            }
        }
    }
}
$obooking=new calendar($this);
ob_start();

if($obooking->uSes->access(2)) {
    if($obooking->is_admin) {
        require_once 'dialogs/calendar.php';
        require_once 'dialogs/inline_edit_dialogs.php';
        require_once 'dialogs/inline_create_dialogs.php';

        $today_midnight_timestamp = date('d.m.Y');
        $format = 'd.m.Y H:i';
        $dateobj = DateTime::createFromFormat($format, $today_midnight_timestamp . ' 00:00');
        $iso_datetime = $dateobj->format(Datetime::ATOM);
        $today_midnight_timestamp = strtotime($iso_datetime);
        ?>
        <script type="text/javascript">
            if (typeof obooking_calendar === "undefined") obooking_calendar = {};
            obooking_calendar.date =<?=$today_midnight_timestamp?>;

            obooking_calendar.office_id =<?=$obooking->office_id?>;

            <?php
            $offices_options = '';
            $offices_number = count($obooking->offices_ar);
            for ($i = 0; $i < $offices_number; $i++) {
                $offices_options .= '<option value="' . $obooking->offices_ar[$i]->office_id . '">' . $obooking->offices_ar[$i]->office_name . '</option>';
            }
            ?>

            obooking_calendar.offices_options = decodeURIComponent("<?=rawurlencode($offices_options)?>");
            <?if(isset($_GET['create'])){?>
            obooking_calendar.create_after_page_load = 1;
            <?}

            ?>
        </script>

        <div id="obooking">
            <div id="notification_bar" class="bg-info">
                <ul>
                    <li>Здесь будут новости школы</li>
                    <!--            <li class="bg-danger">Админ на Севере сильно порезал руку, поэтому за него придется работать Маше Лисичкиной. Подробности <a href="#tmp">туточки</a></li>-->
                </ul>
            </div>
            <div id="obooking_calendar_container"></div>
        </div>
    <?} else {?>
        <div class="jumbotron">
            <h1 class="page-header">Школа Рока</h1>
            <p>У вас нет прав доступа к этой странице</p>
            <p>Обратитесь к администратору</p>
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
