            //<script type="text/javascript">
           $(document).ready(function() {
                $("#uPage_owl_carousel_5").owlCarousel({
                    autoplayTimeout:5000,
                    slideSpeed:0,
                    autoplayHoverPause:true,
                    slideBy:"page",
                    navText:['<span class="icon-left-open"></span>','<span class="icon-right-open"></span>'],
                    loop:true,
                    merge:false,
                    mergeFit:false,
                    autoWidth:false,
                    margin:10,
                    responsiveClass:true,
                    responsive:{
                        0:{
                            items:2,
                            nav:false,
                            dots:false,
                            autoplay:true                        },
                        768:{
                            items:4,
                            nav:false,
                            dots:false,
                            autoplay:true                        },
                        992:{
                            items:5,
                            nav:false,
                            dots:false,
                            autoplay:true                        },
                        1200:{
                            items:6,
                            nav:false,
                            dots:false,
                            autoplay:true                        },
                        1600:{
                            items:7,
                            nav:false,
                            dots:false,
                            autoplay:true                        }
                    }
                });
           });
                        