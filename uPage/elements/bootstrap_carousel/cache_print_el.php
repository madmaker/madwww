<?php
require_once 'uSlider/inc/common.php';
if(!isset($this->uSlider)) $this->uSlider=new \uSlider\common($this->uCore);
$this->uSlider->cache_bootstrap_slider($cols_els_id);
$dir='uSlider/cache/'.site_id.'/'.$cols_els_id;
echo file_get_contents($dir."/slider.html");?>
<script type="text/javascript">
    <?=file_get_contents($dir."/slider.js")?>
</script>