<?php
require_once 'gallery/classes/common.php';
if(!isset($this->gallery)) $this->gallery=new \gallery\common($this->uCore);
$this->gallery->cache_gallery($cols_els_id);
$dir='gallery/cache/'.site_id.'/'.$el_id;
echo file_get_contents($dir."/gallery.html");?>
<script type="text/javascript">
    function init_justifiedGallery_<?=$cols_els_id?> () {
        if(typeof $.prototype.justifiedGallery==="undefined") {
            u235_common.addScript("/js/justifiedGallery/jquery.justifiedGallery.min.js");
            u235_common.addStyle("/js/justifiedGallery/justifiedGallery.min.css");

            u235_common.addScript("/js/colorbox/jquery.colorbox-min.js");
            u235_common.addStyle("/js/colorbox/example3/colorbox.css");

            setTimeout("init_justifiedGallery_<?=$cols_els_id?>()",50);
            return false;
        }
        if(typeof $.prototype.colorbox==="undefined") {
            u235_common.addScript("/js/colorbox/jquery.colorbox-min.js");
            u235_common.addStyle("/js/colorbox/example3/colorbox.css");
            setTimeout("init_justifiedGallery_<?=$cols_els_id?>()",50);
            return false;
        }
        $("#uPage_gallery_<?=$el_id?>").removeClass("hidden");
        <?=file_get_contents($dir."/gallery.js")?>
    }
    $(document).ready(function() {
        init_justifiedGallery_<?=$cols_els_id?> ();
    });
</script>