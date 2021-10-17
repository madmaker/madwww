<?php
require_once 'uSlider/inc/common.php';
if(!isset($this->uSlider)) $this->uSlider=new \uSlider\common($this->uCore);
$this->uSlider->cache_flip_book($cols_els_id);
$dir='uSlider/cache/'.site_id.'/'.$cols_els_id;
echo file_get_contents($dir."/slider.html");?>
<script type="text/javascript">
    $(document).ready(function() {
        u235_common.addScript('/js/WebcamSwiper/demo.js');
        u235_common.addStyle('/js/WebcamSwiper/swiperDemo.css');

        if(typeof swiper_init!=="undefined") swiper_init();
        // Handler for .ready() called.
    });
</script>