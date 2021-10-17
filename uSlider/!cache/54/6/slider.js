            //<script type="text/javascript">
           $(document).ready(function() {
                $("#uPage_owl_carousel_6").owlCarousel({
                    autoplayTimeout:5000,
                    slideSpeed:0,
                    autoplayHoverPause:true,
                    slideBy:1,
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
                            dots:true,
                            autoplay:true                        },
                        768:{
                            items:3,
                            nav:false,
                            dots:true,
                            autoplay:true                        },
                        992:{
                            items:4,
                            nav:false,
                            dots:true,
                            autoplay:true                        },
                        1200:{
                            items:5,
                            nav:false,
                            dots:true,
                            autoplay:true                        },
                        1600:{
                            items:6,
                            nav:false,
                            dots:true,
                            autoplay:false                        }
                    }
                });
           });
                        