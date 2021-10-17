            //<script type="text/javascript">
           $(document).ready(function() {
                $("#uPage_owl_carousel_154").owlCarousel({
                    autoplayTimeout:5000,
                    slideSpeed:3000,
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
                            items:1,
                            nav:false,
                            dots:false,
                            autoplay:true                        },
                        768:{
                            items:2,
                            nav:true,
                            dots:true,
                            autoplay:true                        },
                        992:{
                            items:3,
                            nav:true,
                            dots:true,
                            autoplay:true                        },
                        1200:{
                            items:5,
                            nav:true,
                            dots:true,
                            autoplay:true                        },
                        1600:{
                            items:8,
                            nav:true,
                            dots:true,
                            autoplay:true                        }
                    }
                });
           });
                        