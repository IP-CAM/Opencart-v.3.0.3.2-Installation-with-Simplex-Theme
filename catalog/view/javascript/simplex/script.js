let mainSlider=$("#slider .main-slider"),current_slide=1,catalog=$("#catalog"),$dropdown=$("#slider .categories .dropright"),galeryImagesItems=$(".gallery-images .item-image");function show_succes_popup(t,e,o="cart"){let i=$("button[data-product_id='"+t+"']");if(i.length){i.parent().parent().width(),$("#success-popup").css("display","block"),document.getElementById("success-popup").offsetWidth;let t=i.parent(),o=$(t).offset(),s=$(t).height();$("#success-popup .message").html(e),$("#success-popup").css("left",o.left).css("top",o.top+s+24).css("display","block")}else{"cart"===o&&(i=$("#button-cart")),"compare"===o&&(i=$("#button-compare")),"wishlist"===o&&(i=$("#button-wishlist"));let t=i.offset(),s=i.height();$("#success-popup .message").html(e),$("#success-popup").css("left",t.left).css("top",t.top+s+24).css("display","block")}setTimeout(function(){$("#success-popup").css("display","none")},2e3)}function getCookie(t){let e=document.cookie.match(new RegExp("(?:^|; )"+t.replace(/([.$?*|{}()\[\]\\\/+^])/g,"\\$1")+"=([^;]*)"));return e?decodeURIComponent(e[1]):void 0}function categoryToggleActive(t){$(".category-toggle",t).click(function(){let e=$("+ .dropdown-menu",this).find("ul").length,o=$(this).hasClass("category-toggle-active");$(".category-toggle-active").removeClass("category-toggle-active"),o||$(this).addClass("category-toggle-active"),e?t.height($(this).parent().parent().height()):t.height("auto")})}if($(".nav-tabs .nav-link").click(function(){"#relative"===$(this).attr("href")&&setTimeout(function(){$("#relative .related-slider").slick("setPosition")},350)}),$("input.quantity").inputSpinner({decrementButton:"-",incrementButton:"+",groupClass:"input-group-spinner",buttonsWidth:"0",textAlign:"center",autoDelay:500,autoInterval:100,boostThreshold:15,boostMultiplier:2,locale:null}),$(".input-range").each(function(){let t=$(".range",this),e=$(".input-max input",this),o=$(".input-min input",this);t.slider({range:!0,min:parseInt(o.attr("min")),max:parseInt(e.attr("max")),values:[o.val(),e.val()],step:10,slide:function(t,i){o.val(i.values[0]),e.val(i.values[1])}}),o.on("change",function(){t.slider("values",0,$(this).val())}),e.on("change",function(){t.slider("values",1,$(this).val())})}),$(".cards .close").click(function(){$(this).parents(".cards").animate({opacity:0},500,function(){$(this).remove()})}),$(".control .grid").click(function(){$(this).addClass("active"),$(".products").removeClass("inline"),$(".control .inline").removeClass("active")}),$(".control .inline").click(function(){$(this).addClass("active"),$(".products").addClass("inline"),$(".control .grid").removeClass("active")}),$("input[type=radio]:checked, input[type=checkbox]:checked").parent().addClass("semi-bold"),$("input[type=radio]").click(function(){$(this).parent("form").find(".form-element").css("display","none"),$("input[type=radio]").parent().removeClass("semi-bold"),$(this).parent().addClass("semi-bold"),$("#"+$(this).attr("value")).css("display","block")}),$("input[type=checkbox]").click(function(){$(this).parent().toggleClass("semi-bold")}),$(".tab-content > button").click(function(){if(!$($(this).attr("data-target")).hasClass("collapsing")){let t=$(this).attr("data-target");$(".tab-content .tab-pane:not("+$(this).attr("data-target")+")").removeClass("active"),$(t).toggleClass("active"),$('.nav-tabs .nav-link:not([href="'+t+'"])').removeClass("active"),$('.nav-tabs .nav-link[href="'+t+'"]').toggleClass("active")}}),$("#form-link").click(function(){$(window).width()>=768?$([document.documentElement,document.body]).animate({scrollTop:$($(this).attr("target")).offset().top-95},500):($("body").addClass("noscroll"),$($(this).attr("target")).after('<div class="overlay"></div>').addClass("active").animate({opacity:1},500))}),$(".anchor-link").click(function(){$([document.documentElement,document.body]).animate({scrollTop:$($(this).attr("target")).offset().top-95},500)}),$("#contact-form .close").click(function(){$(this).parents("#contact-form").animate({opacity:0},500,function(){$(this).removeClass("active"),$(".overlay").remove(),$(".noscroll").removeClass("noscroll")})}),$(".delete").click(function(){$(this).parents(".product-column").animate({opacity:0},500,function(){$(this).remove()})}),$(".delete-all").click(function(){$(".wish.products").animate({opacity:0},500,function(){$(this).remove()}),$(this).remove()}),$(".popup").click(function(){$(window).width()<768&&$("body").addClass("noscroll"),$($(this).attr("target")).after('<div class="overlay"></div>').addClass("active").animate({opacity:1},500)}),$(document).click(function(t){"overlay"===$(t.target).attr("class")&&$(window).width()<768&&$("#contact-form .close").click()}),$("#contact-form, .popup").click(function(t){t.stopPropagation()}),$('input[type="radio"]').after('<span class="radio"></span>'),$('input[type="checkbox"]').after('<span class="checkbox"></span>'),window.matchMedia("(max-width: 992px)").matches&&(0===$(".navbar-nav #buttons").length&&$(".navbar-nav").append('<div class="nav-item" id="buttons"></div>'),$("#compare, #favorite").prependTo("#buttons"),$("#profile").appendTo(".navbar-nav").addClass("nav-item")),$(window).resize(function(){window.matchMedia("(max-width: 992px)").matches?(0===$(".navbar-nav #buttons").length&&$(".navbar-nav").append('<div class="nav-item" id="buttons"></div>'),$("#compare, #favorite").appendTo("#buttons"),$("#profile").appendTo(".navbar-nav").addClass("nav-item")):($("#compare, #favorite").insertAfter("#search").removeClass("nav-item"),$("#profile").insertAfter("#search"),$(".navbar-nav #buttons").remove())}),catalog.append($("#categories")),"home"===$("main").attr("id")){let t=$(window).scrollTop(),e=$("#slider .categories .menu"),o=e.offset().top+e.height()/2;t>o&&window.matchMedia("(min-width: 992px)").matches?catalog.show("fast"):catalog.hide("fast"),$(window).resize(function(){$(window).scrollTop()>o&&window.matchMedia("(min-width: 992px)").matches?catalog.show("fast"):catalog.hide("fast")}).scroll(function(){$(window).scrollTop()>o&&window.matchMedia("(min-width: 992px)").matches?catalog.show("fast"):catalog.hide("fast")})}$(".phone a:not(.tel)").click(function(t){t.preventDefault(),$(window).width()<1e3&&($(".phone-popup .input-telephone.top").show(),$(".phone-popup .timetable").show());let e=$(this).offset();$(document).width()>700?$(".phone-popup").css("left",e.left-130).css("top",e.top-282).css("display","block"):$(".phone-popup").css("top",e.top-282).css("display","block").css("width","100%")}),$(".phone a.tel").click(function(t){if(t.preventDefault(),$(window).width()<1e3&&($("header .phone .input-telephone.top").show(),$("header .phone .timetable").show()),$(".phone-popup-top .customer_phone").length>0){let t=$(this);if($(t).offset(),$(document).width()>700){const e=$(t)[0].offsetLeft-230;$(".phone-popup-top").css("left",e+"px").css("top",t.offsetHeight)}else $(".phone-popup-top").css("left",0).css("top",t.offsetHeight).css("width","100%")}}),$(window).width()<1e3&&($("header").on("focus",".phone .customer_phone",function(){$("header .phone .input-telephone.top").slideUp({duration:400}),$("header .phone .timetable").slideUp({duration:400})}),$(".phone-popup").on("focus",".input-telephone .customer_phone",function(){$(".phone-popup .input-telephone.top").slideUp({duration:400}),$(".phone-popup .timetable").slideUp({duration:400})})),"1"===getCookie("accept-cookies")?$(".cookies-politic-block").hide():$(".cookies-politic-block").show().css("display","flex"),$(".btn-accept-cookies-policy").click(function(){return $(".cookies-politic-block").hide(50),document.cookie="accept-cookies=1;  path=/;",!1}),$(".done .delete a").click(function(){$(this).parent().parent().remove()}),$("#search .search-button").click(function(){$(this).parent().toggleClass("active")}),$(".phone-popup .delete").click(function(t){t.preventDefault(),$(this).parent().hasClass("phone-popup-top")?$(this).parent().removeClass("show"):$(this).parent().css("display","none")}),$(".upload").change(function(t){let e=t.target.files[0].name;$("+ p",this).remove(),$('<p style="color: #868686;font-size: .8125rem;font-weight: 500;align-self: center; margin-left: 1rem">'+e+"</p>").insertAfter(this)}),$(".search-button").click(function(){$(this).parent().find("#formSearch input").focus()}),$('button[data-target="#formSearch"]').click(function(){setTimeout(function(){$("#formSearch input").focus()},500)}),$(document).ready(function(){mainSlider.slick({dots:!0,infinite:!1,slidesToShow:1,slidesToScroll:1,arrows:!0,autoplay:!0,autoplaySpeed:mainSlider.data("interval"),prevArrow:'<button class="slick-prev" aria-label="Previous" type="button"><svg\n        xmlns="http://www.w3.org/2000/svg"\n        width="14px" height="22px">\n    <path fill-rule="evenodd"  stroke="rgb(0, 0, 0)" stroke-width="2px" stroke-linecap="butt" stroke-linejoin="miter" fill="none"\n          d="M2.468,1.000 L12.000,11.000 L2.468,21.000 "></path>\n</svg></button>',nextArrow:'<button class="slick-next" aria-label="Next" type="button"><svg\n        xmlns="http://www.w3.org/2000/svg"\n        width="14px" height="22px">\n    <path fill-rule="evenodd"  stroke="rgb(0, 0, 0)" stroke-width="2px" stroke-linecap="butt" stroke-linejoin="miter" fill="none"\n          d="M2.468,1.000 L12.000,11.000 L2.468,21.000 "></path>\n</svg></button>'}),$(".product-slider").slick({dots:!0,infinite:!1,speed:300,slidesToShow:5,slidesToScroll:5,adaptiveHeight:!0,variableWidth:!1,prevArrow:'<button class="slick-prev" aria-label="Previous" type="button"><svg\n        xmlns="http://www.w3.org/2000/svg"\n        width="14px" height="22px">\n    <path fill-rule="evenodd"  stroke="rgb(0, 0, 0)" stroke-width="2px" stroke-linecap="butt" stroke-linejoin="miter" fill="none"\n          d="M2.468,1.000 L12.000,11.000 L2.468,21.000 "></path>\n</svg></button>',nextArrow:'<button class="slick-next" aria-label="Next" type="button"><svg\n        xmlns="http://www.w3.org/2000/svg"\n        width="14px" height="22px">\n    <path fill-rule="evenodd"  stroke="rgb(0, 0, 0)" stroke-width="2px" stroke-linecap="butt" stroke-linejoin="miter" fill="none"\n          d="M2.468,1.000 L12.000,11.000 L2.468,21.000 "></path>\n</svg></button>',responsive:[{breakpoint:1260,settings:{slidesToShow:4,slidesToScroll:4}},{breakpoint:992,settings:{slidesToShow:3,slidesToScroll:3}},{breakpoint:768,settings:{variableWidth:!0,slidesToShow:1,slidesToScroll:1,centerMode:!0,infinite:!0}}]}),$(".interesting .projects-list").slick({slidesToShow:3,dots:!1,arrows:!0,prevArrow:'<button class="slick-prev" aria-label="Previous" type="button"><svg\n        xmlns="http://www.w3.org/2000/svg"\n        width="14px" height="22px">\n    <path fill-rule="evenodd"  stroke="rgb(0, 0, 0)" stroke-width="2px" stroke-linecap="butt" stroke-linejoin="miter" fill="none"\n          d="M2.468,1.000 L12.000,11.000 L2.468,21.000 "></path>\n</svg></button>',nextArrow:'<button class="slick-next" aria-label="Next" type="button"><svg\n        xmlns="http://www.w3.org/2000/svg"\n        width="14px" height="22px">\n    <path fill-rule="evenodd"  stroke="rgb(0, 0, 0)" stroke-width="2px" stroke-linecap="butt" stroke-linejoin="miter" fill="none"\n          d="M2.468,1.000 L12.000,11.000 L2.468,21.000 "></path>\n</svg></button>',infinite:!1,adaptiveHeight:!0,responsive:[{breakpoint:1260,settings:{slidesToShow:3}},{breakpoint:768,settings:{variableWidth:!0,slidesToShow:1}}]}),$(".related-slider").slick({dots:!0,infinite:!1,speed:300,slidesToShow:5,slidesToScroll:5,prevArrow:'<button class="slick-prev" aria-label="Previous" type="button"><svg\n        xmlns="http://www.w3.org/2000/svg"\n        width="14px" height="22px">\n    <path fill-rule="evenodd"  stroke="rgb(0, 0, 0)" stroke-width="2px" stroke-linecap="butt" stroke-linejoin="miter" fill="none"\n          d="M2.468,1.000 L12.000,11.000 L2.468,21.000 "></path>\n</svg></button>',nextArrow:'<button class="slick-next" aria-label="Next" type="button"><svg\n        xmlns="http://www.w3.org/2000/svg"\n        width="14px" height="22px">\n    <path fill-rule="evenodd"  stroke="rgb(0, 0, 0)" stroke-width="2px" stroke-linecap="butt" stroke-linejoin="miter" fill="none"\n          d="M2.468,1.000 L12.000,11.000 L2.468,21.000 "></path>\n</svg></button>',responsive:[{breakpoint:1260,settings:{slidesToShow:4,slidesToScroll:4}},{breakpoint:992,settings:{slidesToShow:3,slidesToScroll:3}},{breakpoint:768,settings:{variableWidth:!0,slidesToShow:1,slidesToScroll:1}}]}),$("aside .product-slider").slick("unslick").slick({slidesToShow:1,slidesToScroll:1,infinite:!1,dots:!0,arrows:!1}),$(".brand-slider").slick({dots:!1,infinite:!1,speed:300,slidesToShow:7,slidesToScroll:7,lazyLoad:"ondemand",adaptiveHeight:!1,prevArrow:'<button class="slick-prev" aria-label="Previous" type="button"><svg\n        xmlns="http://www.w3.org/2000/svg"\n        width="14px" height="22px">\n    <path fill-rule="evenodd"  stroke="rgb(0, 0, 0)" stroke-width="2px" stroke-linecap="butt" stroke-linejoin="miter" fill="none"\n          d="M2.468,1.000 L12.000,11.000 L2.468,21.000 "></path>\n</svg></button>',nextArrow:'<button class="slick-next" aria-label="Next" type="button"><svg\n        xmlns="http://www.w3.org/2000/svg"\n        width="14px" height="22px">\n    <path fill-rule="evenodd"  stroke="rgb(0, 0, 0)" stroke-width="2px" stroke-linecap="butt" stroke-linejoin="miter" fill="none"\n          d="M2.468,1.000 L12.000,11.000 L2.468,21.000 "></path>\n</svg></button>',responsive:[{breakpoint:1260,settings:{slidesToShow:6,slidesToScroll:6}},{breakpoint:768,settings:{slidesToShow:5,slidesToScroll:5}},{breakpoint:576,settings:{slidesToShow:4,slidesToScroll:4}},{breakpoint:480,settings:{slidesToShow:3,slidesToScroll:3}}]}),$("#about-us #slider-test").slick({centerPadding:"60px",dots:!1,infinite:!1,speed:300,slidesToShow:5,slidesToScroll:1,adaptiveHeight:!1,prevArrow:'<button class="slick-prev" aria-label="Previous" type="button"><svg\n        xmlns="http://www.w3.org/2000/svg"\n        width="14px" height="22px">\n    <path fill-rule="evenodd"  stroke="rgb(0, 0, 0)" stroke-width="2px" stroke-linecap="butt" stroke-linejoin="miter" fill="none"\n          d="M2.468,1.000 L12.000,11.000 L2.468,21.000 "></path>\n</svg></button>',nextArrow:'<button class="slick-next" aria-label="Next" type="button"><svg\n        xmlns="http://www.w3.org/2000/svg"\n        width="14px" height="22px">\n    <path fill-rule="evenodd"  stroke="rgb(0, 0, 0)" stroke-width="2px" stroke-linecap="butt" stroke-linejoin="miter" fill="none"\n          d="M2.468,1.000 L12.000,11.000 L2.468,21.000 "></path>\n</svg></button>',responsive:[{breakpoint:768,settings:{slidesToShow:3}},{breakpoint:380,settings:{slidesToShow:2}}]}),$("#news .news").slick({slidesToShow:3,dots:!1,arrows:!0,prevArrow:'<button class="slick-prev" aria-label="Previous" type="button"><svg\n        xmlns="http://www.w3.org/2000/svg"\n        width="14px" height="22px">\n    <path fill-rule="evenodd"  stroke="rgb(0, 0, 0)" stroke-width="2px" stroke-linecap="butt" stroke-linejoin="miter" fill="none"\n          d="M2.468,1.000 L12.000,11.000 L2.468,21.000 "></path>\n</svg></button>',nextArrow:'<button class="slick-next" aria-label="Next" type="button"><svg\n        xmlns="http://www.w3.org/2000/svg"\n        width="14px" height="22px">\n    <path fill-rule="evenodd"  stroke="rgb(0, 0, 0)" stroke-width="2px" stroke-linecap="butt" stroke-linejoin="miter" fill="none"\n          d="M2.468,1.000 L12.000,11.000 L2.468,21.000 "></path>\n</svg></button>',infinite:!1,responsive:[{breakpoint:768,settings:{variableWidth:!0,slidesToShow:1}}]}),$(".gallery-list").slick({dots:!1,infinite:!1,slidesToShow:1,slidesToScroll:1,vertical:!0,verticalSwiping:!0,adaptiveHeight:!0,asNavFor:".gallery-images",arrows:!1,waitForAnimate:!0,responsive:[{breakpoint:768,settings:{verticalSwiping:!1,vertical:!1}}]}),$(".gallery-images").slick({asNavFor:".gallery-list",slidesToShow:galeryImagesItems.length>5?5:galeryImagesItems.length,dots:!1,focusOnSelect:!0,vertical:!0,waitForAnimate:!0,responsive:[{breakpoint:1260,settings:{vertical:!1,slidesToShow:4,variableWidth:!0,infinite:!0}},{breakpoint:992,settings:{vertical:!1,slidesToShow:3,variableWidth:!0,infinite:!0}}]}),$(".gallery-item-container .slideshow").slick({infinite:!1,slidesToShow:1,slidesToScroll:1,adaptiveHeight:!0,arrows:!0,asNavFor:".slideshow-nav",prevArrow:'<button class="slick-prev" aria-label="Previous" type="button"><svg\n        xmlns="http://www.w3.org/2000/svg"\n        width="14px" height="22px">\n    <path fill-rule="evenodd"  stroke="rgb(0, 0, 0)" stroke-width="2px" stroke-linecap="butt" stroke-linejoin="miter" fill="none"\n          d="M2.468,1.000 L12.000,11.000 L2.468,21.000 "></path>\n</svg></button>',nextArrow:'<button class="slick-next" aria-label="Next" type="button"><svg\n        xmlns="http://www.w3.org/2000/svg"\n        width="14px" height="22px">\n    <path fill-rule="evenodd"  stroke="rgb(0, 0, 0)" stroke-width="2px" stroke-linecap="butt" stroke-linejoin="miter" fill="none"\n          d="M2.468,1.000 L12.000,11.000 L2.468,21.000 "></path>\n</svg></button>'}),$(".project-wrapper .slideshow").slick({infinite:!1,slidesToShow:1,slidesToScroll:1,adaptiveHeight:!0,arrows:!0,prevArrow:'<button class="slick-prev" aria-label="Previous" type="button"><svg\n        xmlns="http://www.w3.org/2000/svg"\n        width="14px" height="22px">\n    <path fill-rule="evenodd"  stroke="rgb(0, 0, 0)" stroke-width="2px" stroke-linecap="butt" stroke-linejoin="miter" fill="none"\n          d="M2.468,1.000 L12.000,11.000 L2.468,21.000 "></path>\n</svg></button>',nextArrow:'<button class="slick-next" aria-label="Next" type="button"><svg\n        xmlns="http://www.w3.org/2000/svg"\n        width="14px" height="22px">\n    <path fill-rule="evenodd"  stroke="rgb(0, 0, 0)" stroke-width="2px" stroke-linecap="butt" stroke-linejoin="miter" fill="none"\n          d="M2.468,1.000 L12.000,11.000 L2.468,21.000 "></path>\n</svg></button>'}),$(".news-wrapper .slideshow").slick({infinite:!1,slidesToShow:1,slidesToScroll:1,adaptiveHeight:!0,arrows:!0,prevArrow:'<button class="slick-prev" aria-label="Previous" type="button"><svg\n        xmlns="http://www.w3.org/2000/svg"\n        width="14px" height="22px">\n    <path fill-rule="evenodd"  stroke="rgb(0, 0, 0)" stroke-width="2px" stroke-linecap="butt" stroke-linejoin="miter" fill="none"\n          d="M2.468,1.000 L12.000,11.000 L2.468,21.000 "></path>\n</svg></button>',nextArrow:'<button class="slick-next" aria-label="Next" type="button"><svg\n        xmlns="http://www.w3.org/2000/svg"\n        width="14px" height="22px">\n    <path fill-rule="evenodd"  stroke="rgb(0, 0, 0)" stroke-width="2px" stroke-linecap="butt" stroke-linejoin="miter" fill="none"\n          d="M2.468,1.000 L12.000,11.000 L2.468,21.000 "></path>\n</svg></button>'}),$(".slideshow-nav").slick({infinite:!1,slidesToShow:$(".slideshow-nav .image").length,slidesToScroll:1,arrows:!1,focusOnSelect:!0,asNavFor:".gallery-item-container .slideshow",adaptiveHeight:!0,variableWidth:!0}),$(document).click(function(t){let e=$(t.target);!0!==$(".navbar-collapse").hasClass("show")||e.hasClass("navbar-toggler")||$(".navbar-toggler").click(),e.hasClass("categories-main")||$(".categories-main").height("auto")});let t=$(".slideshow .slick-slide").length;$("#slides-counter").prepend("<p>"+current_slide+"/"+t+"</p>"),$(".slick-arrow, .slick-slide").click(function(){current_slide=parseInt($(".slideshow .slick-active").attr("data-slick-index"))+1,$("#slides-counter p").remove(),$("#slides-counter").prepend("<p>"+current_slide+"/"+t+"</p>")})}),$(".dropdown-menu a.dropdown-toggle").click(function(){return $(this).next().hasClass("show")||$(this).parents(".dropdown-menu").first().find(".show").removeClass("show"),$(this).next(".dropdown-menu").toggleClass("show"),$(this).parents("li.nav-item.dropdown.show").on("hidden.bs.dropdown",function(){$(".dropdown-submenu .show").removeClass("show")}),!1}),$(".dropdown-toggle.back").click(function(){for(let t of $(this).parent().parents())if($(t).hasClass("dropdown")||$(t).hasClass("dropright")){$(t).find("> .dropdown-toggle").click();break}$(".category-toggle").removeClass("category-toggle-active")}),$(window).on("load resize",function(){this.matchMedia("(min-width: 992px)").matches?$dropdown.hover(function(){$(".dropdown-toggle",this).dropdown("show")},function(){$(".dropdown-toggle",this).dropdown("hide")}):$dropdown.off("mouseenter mouseleave")}),$(".categories-header > .dropdown-toggle").click(function(){let t=$(this).parents();for(let e of t)if($(e).hasClass("navbar-collapse")){$(e).addClass("invisible");break}let e=$(this).children();for(let t of e)$(parent).hasClass("invisible")&&$(parent).removeClass("invisible")}),$(".categories-header .dropdown-toggle.back").click(function(){$("#navbarCollapse").height("auto");let t=$(this).parents();for(let e of t)if($(e).hasClass("invisible")){$(e).removeClass("invisible"),$($(e)).height("auto").height($(e).prop("scrollHeight"));break}}),$(".categories-header .dropdown-toggle.catalog-item:not(.back)").click(function(){let t=$(this).parents();for(let e of t)if($(e).hasClass("show")){$(e).addClass("invisible");break}for(let e of t)if($(e).hasClass("dropdown-menu")){$(e).height("auto");break}$("+ .dropdown-menu",this).height("auto").height($("+ .dropdown-menu",this).prop("scrollHeight"))}),$(".categories-header .dropdown-toggle").click(function(){for(let t of $(this).parents())($(t).hasClass("navbar-nav")||$(t).hasClass("dropdown-menu"))&&$(t).animate({scrollTop:0},250)}),$("#navbarCollapse").on("show.bs.collapse",function(){$(".main-overlay").show()}).on("hide.bs.collapse",function(){$(".main-overlay").hide()}),$(".navbar-toggler").click(function(){let t=$(this).parent().find("#navbarCollapse");$(t).hasClass("invisible")&&$(t).removeClass("invisible"),$("#navbarCollapse *").each(function(){$(this).hasClass("invisible")&&$(this).removeClass("invisible")}),$(".category-toggle").removeClass("category-toggle-active")}),$(".categories-main .dropdown, .categories-main .dropright").on("shown.bs.dropdown",function(){$(".categories-main").animate({height:$("> .dropdown-menu",this).height()},25)}),$("#navbarCollapse .dropdown, #navbarCollapse .dropright").on("shown.bs.dropdown",function(){$("> .dropdown-menu",this).height("auto").height($("> .dropdown-menu",this).prop("scrollHeight"))}),categoryToggleActive($(".menu-main")),categoryToggleActive($(".categories-main")),$("#navbarCollapse .category-toggle").click(function(){for(let t of $(this).parents())if($(t).hasClass("dropdown-menu")){$(t).height("auto"),$(t).height($(t).prop("scrollHeight"));break}});
