<?
echo '<?
                require_once "uForms/inc/form_builder.php";
                $uForms=new uForms_form($this->uCore);
                
                if(isset($this->uCore)) $this->uCore->uInt_js(\'uForms\',\'form\');
                else $this->uCore->uInt_js;
                
                $form_id='.$el_id.';
                $uForms->check_data($form_id);
                $dir="uForms/cache/'.site_id.'/'.$el_id.'";
                if(!file_exists($dir."/form.html")) $uForms->build_form_php($dir,'.$el_id.');

                echo file_get_contents($dir."/form.html");        
                ?>';