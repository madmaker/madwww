<?php
use processors\uFunc;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "translator/translator.php";

class uBc {
    public $uFunc;
    /**
     * @var \translator\translator
     */
    private $translator;
    private $uSes;
    private $uCore;
    public $add_info;
    public $page_title;

    private function get($parent) {//Эта функция возвращает родительскую страницу страницы и ссылку на нее. Принимает единственный аргумент - id родительской страницы. Если в качестве родительской - материал, то должен быть префикс "s-", если главная ссылается сама на себя, то аргумент будет равен "mainpage" - ключевое слово*/
        if(!isset($this->translator)) $this->translator=new translator\translator(site_lang,"processors/uBc.php");

         if($parent==='mainpage') return '';//Если в поле родительская страница указано ключевое слово "mainpage", значит мы достигли самого верхнего уровня
         if(strpos($parent,"s")===0) {//Если в поле "родительская страница" указано s-, значит в качестве родительской здесь статья
             try {
                 /** @noinspection PhpUndefinedMethodInspection */
                 $stm=$this->uFunc->pdo("pages")->prepare("SELECT
                navi_parent_page_id,
                page_title,
                page_name,
                page_alias,
                page_access
                FROM
                u235_pages_html
                WHERE
                site_id=:site_id AND
                page_id=:page_id
                ");
                $site_id=site_id;
                $page_id=str_replace("s","",$parent);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
                 /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
             }
             catch(PDOException $e) {$this->uFunc->error('bc1'/*.$e->getMessage()*/);}

             /** @noinspection PhpUndefinedMethodInspection */
             if($data=$stm->fetch(PDO::FETCH_ASSOC)) {//Записываем полученные из БД данные в массив
                $data['page_mod']='page';//Коль мы достаем материалы, то имя модуля - static, и нужно его записать, т.к. в таблице staticPages не указывается название модуля, т.к. он там для всех = "static"
             }
             else return '';//Если ничего не достали, то просто возвращаем пустую строку
         }
         elseif(strpos($parent,"p")===0) {//Если в поле "родительская страница" указано p-, значит в качестве родительской здесь uPage
             try {
                 /** @noinspection PhpUndefinedMethodInspection */
                 $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
                page_id,
                navi_parent_page_id,
                page_title,
                page_url
                FROM
                u235_pages
                WHERE
                site_id=:site_id AND
                page_id=:page_id
                ");
                $site_id=site_id;
                $page_id=str_replace("p","",$parent);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
                 /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
             }
             catch(PDOException $e) {$this->uFunc->error('bc2'/*.$e->getMessage()*/);}

             /** @noinspection PhpUndefinedMethodInspection */
             if($data=$stm->fetch(PDO::FETCH_ASSOC)) {//Записываем полученные из БД данные в массив
                 $data['page_mod'] = 'uPage';//Коль мы достаем материалы, то имя модуля - static, и нужно его записать, т.к. в таблице staticPages не указывается название модуля, т.к. он там для всех = "static"
             }
             else return '';//Если ничего не достали, то просто возвращаем пустую строку
         }
         else {//Если в качестве родительской страницы не статья, а скрипт:
             if(!uString::isDigits($parent)) return "";//Должны быть цифры
             try {
                 /** @noinspection PhpUndefinedMethodInspection */
                 $stm=$this->uFunc->pdo("pages")->prepare("SELECT
                navi_parent_page_id,
                page_title,
                page_name,
                page_mod,
                page_access
                FROM
                u235_pages_list
                WHERE
                page_id='".$parent."'");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                 /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
             }
             catch(PDOException $e) {$this->uFunc->error('bc3'/*.$e->getMessage()*/);}

             /** @noinspection PhpUndefinedMethodInspection */
             if($data=$stm->fetch(PDO::FETCH_ASSOC)) {//Записываем данные в массив
                 $data['page_alias'] = '';//Записываем данные в массив
             }
             else return '';//Если ничего не найдено, возвращаем пустоту
         }

	    $result=$this->get($data['navi_parent_page_id']); //В переменную Result записывается возврат функции mod_menu_getNavigation($parent). Т.е. рекурсивный вызов.
	    if(empty($result)) {
            if($data['page_mod']=='uPage') {?>
                <li><a href="<?=u_sroot.'uPage/'.(trim($data['page_url'])!=""?$data['page_url']:$data['page_id'])?>" ><?=uString::sql2text($data['page_title'],true)?></a></li>
                <?//Если родительская страница не найдена, то возвращаем имеющиеся построенные хлебные крошки
                return '';
            }
            else {
                if($this->uSes->access($data['page_access'])) {
                    if($data['page_mod']=="uCat"&&$data['page_name']=="article") $data['page_title']=$this->uFunc->getConf("arts_label","uCat",1);

                    if($data['page_mod']=="uPage"||$data['page_mod']=="page") {?>
                        <li><a href="<?=u_sroot.$data['page_mod'].'/'.$data['page_name']?>"><?=uString::sql2text($data['page_title'],true)?></a></li>
                    <?} else {?>
                        <li><a href="<?=u_sroot.$data['page_mod'].'/'.$data['page_name']?>"><?=$this->translator->txt($data['page_mod'].' '.$data['page_name'])?></a></li>
                    <?}?>
                    <?//Если родительская страница не найдена, то возвращаем имеющиеся построенные хлебные крошки
                    return '';
                }
            }
        }
         if($data['page_mod']=='uPage') {?>
             <li><a href="<?=u_sroot.'uPage/'.$data['page_url']?>" ><?=uString::sql2text($data['page_title'],true)?></a></li>
             <?//Если родительская страница найдена, то добавляем в строку хлебных крошек еще одно значение.
         }
         else {
            if($this->uSes->access($data['page_access'])) {?>
                 <li><a href="<?=u_sroot.$data['page_mod'].'/'.$data['page_name']?>" ><?=uString::sql2text($data['page_title'],true)?></a></li>
                 <?//Если родительская страница найдена, то добавляем в строку хлебных крошек еще одно значение.
             }
         }
	 }
	public function insert($eip=false) {//Эта функция должна вызываться из шаблона. Возвращает готовую строку с хлебными крошками
        if(!isset($this->translator)) $this->translator=new translator\translator(site_lang,"processors/uBc.php");

         if($this->uCore->mod=='mainpage'&&$this->uCore->page_name=='index') return '';?>
         <ol class="breadcrumb">
             <?$this->get($this->uCore->page['navi_parent_page_id']);
             if($this->uCore->mod=='uEvents'&&$this->uCore->page_name=='event') {?>
                 <li id="uBc_uEvents_type_li"><a href="<?=u_sroot?>uEvents/events/<?=$this->add_info->type_url?>"><?=$this->add_info->type_title?></a></li>
             <?}
             elseif($this->uCore->mod=='uCat'&&(
                     $this->uCore->page_name=='cats'||
                     $this->uCore->page_name=='items'||
                     $this->uCore->page_name=='item'||
                     $this->uCore->page_name=='article'
                 )||
                 $this->uCore->mod==="uRubrics"&&$this->uCore->page_name==="show"
             ) {
                 echo $this->add_info->html;
             }
             elseif($this->uCore->mod=='uDrive'&&$this->uCore->page_name=='my_drive') {?>
                 <li <?=!isset($this->add_info->type_url)?'class="active"':''?>><a href="<?=u_sroot?>uDrive/my_drive" class="uBc_uDrive_my_drive" onclick="uDrive_manager.<?=(isset($_POST['in_dialog'])?'move_':'')/*in_dialog передается на страницу uDrive/my_drive*/?>open_folder(0)"><?=uString::sql2text($this->uCore->page['page_title'],true)?></a></li>
                 <?if(isset($this->add_info->type_url)){
                     $subfolder_count=count($this->add_info->type_url)-1;
                     for($i=$subfolder_count;$i>=0;$i--) {?>
                         <li id="uBc_uEvents_type_li_<?=$this->add_info->type_url[$i]?>" <?=$i==0?'class="active"':''?>><a href="<?=u_sroot?>uDrive/my_drive/<?=$this->add_info->type_url[$i]?>" onclick="<?=$this->add_info->type_onclick[$i]?>" class="uBc_uDrive_my_drive"><?=$this->add_info->type_title[$i]?></a></li>
                     <?}
                 }
             }
             else {
                 if($this->uCore->page['page_mod']=="uPage"||$this->uCore->page['page_mod']=="page") {?>
                     <li class="active"><a href="<?=$_SERVER["REQUEST_URI"]?>"><?=uString::sql2text($this->uCore->page['page_title'],true)?></a></li>
                 <?} else {?>
                     <li class="active"><a href="<?=$_SERVER["REQUEST_URI"]?>"><?
                     if(isset($this->page_title)) echo $this->page_title;
                     else echo $this->translator->txt($this->uCore->page['page_mod'].' '.$this->uCore->page['page_name'])
                             ?></a></li>
                 <?}?>
             <?}

             if($this->uSes->access(7)&&($this->uCore->mod=='page'||($this->uCore->mod=='uPage'&&$this->uCore->page_name!='admin_pages'))){
                 ?><li><button class="u235_eip btn btn-xs btn-primary uTooltip" title="<?=$this->translator->txt("Edit sitemap - btn text"/*Редактировать карту сайта*/)?>" onclick="uNavi_eip.load_site_tree()" <?=$eip?'style="display:block"':''?>><span class="glyphicon glyphicon-pencil"></span></button> </li><?}
             ?>
         </ol>
         <?//Возвращает результат выполнения функции создания строки хлебных крошек, а также слева дописывает Имя главной страницы
    }
    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new uCore();
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);
        $this->add_info=new stdClass();
    }
}
