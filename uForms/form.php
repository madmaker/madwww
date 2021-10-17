<?php
include_once 'uForms/inc/form_builder.php';
$uForms_form=new uForms_form($this);

if(isset($_POST['no_template'])) {
    if(!isset($_POST['form_id'])) $this->error(1);
    $form_id=$_POST['form_id'];
    $uForms_form->check_data($form_id);

    $dir='uForms/cache/'.site_id.'/'.$uForms_form->form_id;

    if(!file_exists($dir.'/form.html')) $uForms_form->build_form_php($dir,$uForms_form->form_id);
    echo file_get_contents($dir."/form.html");
}
else{
    if(!isset($this->url_prop[1])) header('Location: '.u_sroot);
    $form_id=$this->url_prop[1];
    $uForms_form->check_data($form_id);

    $dir='uForms/cache/'.site_id.'/'.$uForms_form->form_id;
    if(!file_exists($dir.'/form.php'||!file_exists($dir.'/form.html'))) $uForms_form->build_form_php($dir,$uForms_form->form_id);
    include $dir.'/form.php';
    include "templates/template.php";
}