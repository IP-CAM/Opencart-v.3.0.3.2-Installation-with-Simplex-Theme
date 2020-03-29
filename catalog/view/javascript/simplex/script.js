$('.nav-tabs .nav-link').click(function () {
    if ($(this).attr('href') === '#relative') {
        setTimeout(function () {
            $('#relative .related-slider').slick('setPosition');
        }, 155);
    }
});
$("input.quantity").inputSpinner({
    decrementButton: "-",
    incrementButton: "+",
    groupClass: "input-group-spinner",
    buttonsWidth: "0",
    textAlign: "center",
    autoDelay: 500,
    autoInterval: 100,
    boostThreshold: 15,
    boostMultiplier: 2,
    locale: null
});
$('.input-range').each(function () {
    let slider = $('.range', this);
    let max_input = $('.input-max input', this);
    let min_input = $('.input-min input', this);
    slider.slider({
        range: true,
        min: parseInt(min_input.attr('min')),
        max: parseInt(max_input.attr('max')),
        values: [min_input.val(), max_input.val()],
        step: 10,
        slide: function (event, ui) {
            min_input.val(ui.values[0]);
            max_input.val(ui.values[1]);
        },
    });
    min_input.on("change", function () {
        slider.slider("values", 0, $(this).val());
    });
    max_input.on("change", function () {
        slider.slider("values", 1, $(this).val());
    });
});
$('.cards .close').click(function () {
    $(this).parents('.cards').animate({
        opacity: 0,
    }, 500, function () {
        $(this).remove();
    });
});
$('.control .grid').click(function () {
    $(this).addClass('active');
    $('.products').removeClass('inline');
    $('.control .inline').removeClass('active');
});
$('.control .inline').click(function () {
    $(this).addClass('active');
    $('.products').addClass('inline');
    $('.control .grid').removeClass('active');
});
$('input[type=radio]:checked, input[type=checkbox]:checked').parent().addClass('semi-bold');
$('input[type=radio]').click(function () {
    $(this).parent("form").find('.form-element').css('display', 'none');
    $('input[type=radio]').parent().removeClass('semi-bold');
    $(this).parent().addClass('semi-bold');
    $('#' + $(this).attr('value')).css('display', 'block');
});
$('input[type=checkbox]').click(function () {
    $(this).parent().toggleClass('semi-bold');
});
$('.tab-content > button').click(function () {
    if (!$($(this).attr('data-target')).hasClass('collapsing')) {
        let attr = $(this).attr('data-target');
        $('.tab-content .tab-pane:not(' + $(this).attr('data-target') + ')').removeClass('active');
        $(attr).toggleClass('active');
        $('.nav-tabs .nav-link:not([href="' + attr + '"])').removeClass('active');
        $('.nav-tabs .nav-link[href="' + attr + '"]').toggleClass('active');
    }
});
let current_slide = 1;
let total_slides = $('.slideshow .slick-slide').length;
$('#slides-counter').prepend('<p>' + current_slide + '/' + total_slides + '</p>');
$('.slick-arrow, .slick-slide').click(function () {
    current_slide = parseInt($('.slideshow .slick-active').attr('data-slick-index')) + 1;
    $('#slides-counter p').remove();
    $('#slides-counter').prepend('<p>' + current_slide + '/' + total_slides + '</p>');
});
$('#form-link').click(function () {
    if ($(window).width() >= 768) {
        $([document.documentElement, document.body]).animate({
            scrollTop: $($(this).attr('target')).offset().top - 95
        }, 500);
    } else {
        $('body').addClass('noscroll');
        $($(this).attr('target')).after('<div class="overlay" />').addClass('active').animate({
            opacity: 1,
        }, 500)
    }
});
$('.anchor-link').click(function () {
    $([document.documentElement, document.body]).animate({
        scrollTop: $($(this).attr('target')).offset().top - 95
    }, 500);
});
$('#contact-form .close').click(function () {
    $(this).parents('#contact-form').animate({
        opacity: 0,
    }, 500, function () {
        $(this).removeClass('active');
        $('.overlay').remove();
        $('.noscroll').removeClass('noscroll');
    });
});
$('.delete').click(function () {
    $(this).parents('.product-column').animate({
        opacity: 0,
    }, 500, function () {
        $(this).remove();
    });
});
$('.delete-all').click(function () {
    $('.wish.products').animate({
        opacity: 0,
    }, 500, function () {
        $(this).remove();
    });
    $(this).remove();
});
$('.popup').click(function () {
    if ($(window).width() < 768) {
        $('body').addClass('noscroll');
    }
    $($(this).attr('target')).after('<div class="overlay" />').addClass('active').animate({
        opacity: 1,
    }, 500);
});
$(document).on("click", function(event){
    if($(event.target).attr('class') === 'overlay'){
        if ($(window).width() < 768) {
            $('#contact-form .close').click();
        }
    }
});
$('#contact-form, .popup').click(function (event) {
    event.stopPropagation();
});
$('input[type="radio"]').after('<span class="radio"></span>');
$('input[type="checkbox"]').after('<span class="checkbox"></span>');
if ($(window).width() < 768) {
    if ($('.navbar-nav #buttons').length === 0) {
        $('.navbar-nav').append('<div class="nav-item" id="buttons"></div>');
    }
    $('#compare, #favorite').prependTo('#buttons');
    $('#profile').appendTo('.navbar-nav').addClass('nav-item');
}
$(window).resize(function () {
    let screen = $(window);
    if (screen.width() < 768) {
        if ($('.navbar-nav #buttons').length === 0) {
            $('.navbar-nav').append('<div class="nav-item" id="buttons"></div>');
        }
        $('#compare, #favorite').appendTo('#buttons');
        $('#profile').appendTo('.navbar-nav').addClass('nav-item');
    } else {
        $('#compare, #favorite').insertAfter('#search').removeClass('nav-item');
        $('#profile').insertAfter('#search');
        $('.navbar-nav #buttons').remove();
    }
});

function show_succes_popup(product_id, message, type = 'cart') {
    let button_product_id_selector = "button[data-product_id='" + product_id + "']";
    let $button = $(button_product_id_selector);
    if ($button.length) {
        $button.parent().parent().width();
        $("#success-popup").css('display', 'block');
        document.getElementById('success-popup').offsetWidth;
        let div = $button.parent();
        let divOffset = $(div).offset();
        let divHeight = $(div).height();
        $("#success-popup .message").html(message);
        $('#success-popup').css('left', divOffset.left).css('top', divOffset.top + divHeight + 24).css('display', 'block');
    } else {
        if (type === 'cart') {
            $button = $("#button-cart");
        }
        if (type === 'compare') {
            $button = $("#button-compare");
        }
        if (type === 'wishlist') {
            $button = $("#button-wishlist");
        }
        let divOffset = $button.offset();
        let divHeight = $button.height();

        $("#success-popup .message").html(message);
        $('#success-popup').css('left', divOffset.left).css('top', divOffset.top + divHeight + 24).css('display', 'block');
    }
    setTimeout(function () {
        $('#success-popup').css('display', 'none');
    }, 2000);
}

$('.phone a:not(.tel)').click(function (e) {
    e.preventDefault();
    let div = $(this);
    let divOffset = $(div).offset();
    $('.phone-popup').css('left', divOffset.left - 130).css('top', divOffset.top - 282).css('display', 'block');
});

function getCookie(name) {
    let matches = document.cookie.match(new RegExp(
        "(?:^|; )" + name.replace(/([.$?*|{}()\[\]\\\/+^])/g, '\\$1') + "=([^;]*)"
    ));
    return matches ? decodeURIComponent(matches[1]) : undefined;
}

let accept_cookies = getCookie("accept-cookies");
if (accept_cookies === "1") {
    $(".cookies-politic-block").hide();
} else {
    $(".cookies-politic-block").show().css("display", "flex");
}
$(".btn-accept-cookies-policy").click(function () {
    $(".cookies-politic-block").hide(50);
    document.cookie = "accept-cookies=1;  path=/;";
    return false;
});
$('.done .delete a').click(function () {
    $(this).parent().parent().remove();
});
$('#search .search-button').click(function () {
    $(this).parent().toggleClass('active');
});
$('.phone-popup .delete').click(function (e) {
    e.preventDefault();
    $(this).parent().css('display', 'none');
});
$('#catalog').append($('#categories'));
$('.upload').change(function (e) {
    let fileName = e.target.files[0].name;
    $('+ p', this).remove();
    $('<p style="color: #868686;font-size: .8125rem;font-weight: 500;align-self: center; margin-left: 1rem">' + fileName + '</p>').insertAfter(this);
});
$('.search-button').click(function () {
    $(this).parent().find('#formSearch input').focus();
});
$('button[data-target="#formSearch"]').click(function () {
    setTimeout(function () {
        $('#formSearch input').focus();
    }, 500);
});

$(document).ready(function () {
    /* Slick */
    $('#slider .main-slider').slick({
        dots: true,
        infinite: false,
        slidesToShow: 1,
        slidesToScroll: 1,
        vertical: true,
        verticalSwiping: true,
        arrows: true,
        autoplay: true,
        autoplaySpeed: $('#slider .main-slider').data('interval'),
        prevArrow:
            '<button class="slick-prev" aria-label="Previous" type="button"><svg\n' +
            '        xmlns="http://www.w3.org/2000/svg"\n' +
            '        width="14px" height="22px">\n' +
            '    <path fill-rule="evenodd"  stroke="rgb(0, 0, 0)" stroke-width="2px" stroke-linecap="butt" stroke-linejoin="miter" fill="none"\n' +
            '          d="M2.468,1.000 L12.000,11.000 L2.468,21.000 "></path>\n' +
            '</svg></button>',
        nextArrow:
            '<button class="slick-next" aria-label="Next" type="button"><svg\n' +
            '        xmlns="http://www.w3.org/2000/svg"\n' +
            '        width="14px" height="22px">\n' +
            '    <path fill-rule="evenodd"  stroke="rgb(0, 0, 0)" stroke-width="2px" stroke-linecap="butt" stroke-linejoin="miter" fill="none"\n' +
            '          d="M2.468,1.000 L12.000,11.000 L2.468,21.000 "></path>\n' +
            '</svg></button>',
    });
    $('.product-slider').slick({
        dots: true,
        infinite: false,
        speed: 300,
        slidesToShow: 5,
        slidesToScroll: 5,
        adaptiveHeight: true,
        variableWidth: false,
        prevArrow: '<button class="slick-prev" aria-label="Previous" type="button"><svg\n' +
            '        xmlns="http://www.w3.org/2000/svg"\n' +
            '        width="14px" height="22px">\n' +
            '    <path fill-rule="evenodd"  stroke="rgb(0, 0, 0)" stroke-width="2px" stroke-linecap="butt" stroke-linejoin="miter" fill="none"\n' +
            '          d="M2.468,1.000 L12.000,11.000 L2.468,21.000 "></path>\n' +
            '</svg></button>',
        nextArrow: '<button class="slick-next" aria-label="Next" type="button"><svg\n' +
            '        xmlns="http://www.w3.org/2000/svg"\n' +
            '        width="14px" height="22px">\n' +
            '    <path fill-rule="evenodd"  stroke="rgb(0, 0, 0)" stroke-width="2px" stroke-linecap="butt" stroke-linejoin="miter" fill="none"\n' +
            '          d="M2.468,1.000 L12.000,11.000 L2.468,21.000 "></path>\n' +
            '</svg></button>',
        responsive: [
            {
                breakpoint: 1260,
                settings: {
                    slidesToShow: 4,
                    slidesToScroll: 4,
                }
            },
            {
                breakpoint: 992,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 3,
                }
            },
            {
                breakpoint: 768,
                settings: {
                    variableWidth: true,
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    centerMode: true,
                    infinite: true
                }
            }
        ]
    });
    $('.interesting .projects-list').slick({
        slidesToShow: 3,
        dots: false,
        arrows: true,
        prevArrow: '<button class="slick-prev" aria-label="Previous" type="button"><svg\n' +
            '        xmlns="http://www.w3.org/2000/svg"\n' +
            '        width="14px" height="22px">\n' +
            '    <path fill-rule="evenodd"  stroke="rgb(0, 0, 0)" stroke-width="2px" stroke-linecap="butt" stroke-linejoin="miter" fill="none"\n' +
            '          d="M2.468,1.000 L12.000,11.000 L2.468,21.000 "></path>\n' +
            '</svg></button>',
        nextArrow: '<button class="slick-next" aria-label="Next" type="button"><svg\n' +
            '        xmlns="http://www.w3.org/2000/svg"\n' +
            '        width="14px" height="22px">\n' +
            '    <path fill-rule="evenodd"  stroke="rgb(0, 0, 0)" stroke-width="2px" stroke-linecap="butt" stroke-linejoin="miter" fill="none"\n' +
            '          d="M2.468,1.000 L12.000,11.000 L2.468,21.000 "></path>\n' +
            '</svg></button>',
        infinite: false,
        adaptiveHeight: true,
        responsive: [
            {
                breakpoint: 1260,
                settings: {
                    slidesToShow: 3,
                }
            },
            {
                breakpoint: 768,
                settings: {
                    variableWidth: true,
                    slidesToShow: 1,
                }
            }
        ]
    });
    $('.related-slider').slick({
        dots: true,
        infinite: false,
        speed: 300,
        slidesToShow: 5,
        slidesToScroll: 5,
        prevArrow: '<button class="slick-prev" aria-label="Previous" type="button"><svg\n' +
            '        xmlns="http://www.w3.org/2000/svg"\n' +
            '        width="14px" height="22px">\n' +
            '    <path fill-rule="evenodd"  stroke="rgb(0, 0, 0)" stroke-width="2px" stroke-linecap="butt" stroke-linejoin="miter" fill="none"\n' +
            '          d="M2.468,1.000 L12.000,11.000 L2.468,21.000 "></path>\n' +
            '</svg></button>',
        nextArrow: '<button class="slick-next" aria-label="Next" type="button"><svg\n' +
            '        xmlns="http://www.w3.org/2000/svg"\n' +
            '        width="14px" height="22px">\n' +
            '    <path fill-rule="evenodd"  stroke="rgb(0, 0, 0)" stroke-width="2px" stroke-linecap="butt" stroke-linejoin="miter" fill="none"\n' +
            '          d="M2.468,1.000 L12.000,11.000 L2.468,21.000 "></path>\n' +
            '</svg></button>',
        responsive: [
            {
                breakpoint: 1260,
                settings: {
                    slidesToShow: 4,
                    slidesToScroll: 4,
                }
            },
            {
                breakpoint: 992,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 3,
                }
            },
            {
                breakpoint: 768,
                settings: {
                    variableWidth: true,
                    slidesToShow: 1,
                    slidesToScroll: 1,
                }
            }
        ]
    });
    $('aside .product-slider').slick('unslick').slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        infinite: false,
        dots: true,
        arrows: false,
    });
    $('.brand-slider').slick({
        dots: false,
        infinite: false,
        speed: 300,
        slidesToShow: 7,
        slidesToScroll: 7,
        lazyLoad: 'ondemand',
        adaptiveHeight: false,
        prevArrow: '<button class="slick-prev" aria-label="Previous" type="button"><svg\n' +
            '        xmlns="http://www.w3.org/2000/svg"\n' +
            '        width="14px" height="22px">\n' +
            '    <path fill-rule="evenodd"  stroke="rgb(0, 0, 0)" stroke-width="2px" stroke-linecap="butt" stroke-linejoin="miter" fill="none"\n' +
            '          d="M2.468,1.000 L12.000,11.000 L2.468,21.000 "></path>\n' +
            '</svg></button>',
        nextArrow: '<button class="slick-next" aria-label="Next" type="button"><svg\n' +
            '        xmlns="http://www.w3.org/2000/svg"\n' +
            '        width="14px" height="22px">\n' +
            '    <path fill-rule="evenodd"  stroke="rgb(0, 0, 0)" stroke-width="2px" stroke-linecap="butt" stroke-linejoin="miter" fill="none"\n' +
            '          d="M2.468,1.000 L12.000,11.000 L2.468,21.000 "></path>\n' +
            '</svg></button>',
        responsive: [
            {
                breakpoint: 1260,
                settings: {
                    slidesToShow: 6,
                    slidesToScroll: 6,
                }
            },
            {
                breakpoint: 768,
                settings: {
                    slidesToShow: 5,
                    slidesToScroll: 5,
                }
            },
            {
                breakpoint: 576,
                settings: {
                    slidesToShow: 4,
                    slidesToScroll: 4,
                }
            },
            {
                breakpoint: 480,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 3,
                }
            }
        ]
    });
    $('#about-us .cards').slick({
        dots: false,
        infinite: false,
        speed: 300,
        slidesToShow: 5,
        slidesToScroll: 1,
        adaptiveHeight: false,
        prevArrow: '<button class="slick-prev" aria-label="Previous" type="button"><svg\n' +
            '        xmlns="http://www.w3.org/2000/svg"\n' +
            '        width="14px" height="22px">\n' +
            '    <path fill-rule="evenodd"  stroke="rgb(0, 0, 0)" stroke-width="2px" stroke-linecap="butt" stroke-linejoin="miter" fill="none"\n' +
            '          d="M2.468,1.000 L12.000,11.000 L2.468,21.000 "></path>\n' +
            '</svg></button>',
        nextArrow: '<button class="slick-next" aria-label="Next" type="button"><svg\n' +
            '        xmlns="http://www.w3.org/2000/svg"\n' +
            '        width="14px" height="22px">\n' +
            '    <path fill-rule="evenodd"  stroke="rgb(0, 0, 0)" stroke-width="2px" stroke-linecap="butt" stroke-linejoin="miter" fill="none"\n' +
            '          d="M2.468,1.000 L12.000,11.000 L2.468,21.000 "></path>\n' +
            '</svg></button>',
        responsive: [
            {
                breakpoint: 768,
                settings: {
                    slidesToShow: 3
                }
            },
            {
                breakpoint: 576,
                settings: {
                    slidesToShow: 2
                }
            },
        ]
    });
    $('#news .news').slick({
        slidesToShow: 3,
        dots: false,
        arrows: true,
        prevArrow: '<button class="slick-prev" aria-label="Previous" type="button"><svg\n' +
            '        xmlns="http://www.w3.org/2000/svg"\n' +
            '        width="14px" height="22px">\n' +
            '    <path fill-rule="evenodd"  stroke="rgb(0, 0, 0)" stroke-width="2px" stroke-linecap="butt" stroke-linejoin="miter" fill="none"\n' +
            '          d="M2.468,1.000 L12.000,11.000 L2.468,21.000 "></path>\n' +
            '</svg></button>',
        nextArrow: '<button class="slick-next" aria-label="Next" type="button"><svg\n' +
            '        xmlns="http://www.w3.org/2000/svg"\n' +
            '        width="14px" height="22px">\n' +
            '    <path fill-rule="evenodd"  stroke="rgb(0, 0, 0)" stroke-width="2px" stroke-linecap="butt" stroke-linejoin="miter" fill="none"\n' +
            '          d="M2.468,1.000 L12.000,11.000 L2.468,21.000 "></path>\n' +
            '</svg></button>',
        infinite: false,
        responsive: [
            {
                breakpoint: 768,
                settings: {
                    variableWidth: true,
                    slidesToShow: 1,
                }
            }
        ]
    });
    $('.gallery-list').slick({
        dots: false,
        infinite: false,
        slidesToShow: 1,
        slidesToScroll: 1,
        vertical: true,
        verticalSwiping: true,
        adaptiveHeight: true,
        asNavFor: '.gallery-images',
        arrows: false,
        waitForAnimate: true,
        responsive: [
            {
                breakpoint: 768,
                settings: {
                    verticalSwiping: false,
                    vertical: false,
                }
            }
        ]
    });
    $('.gallery-images').slick({
        asNavFor: '.gallery-list',
        slidesToShow: $('.gallery-images .item-image').length > 5 ? 5 : $('.gallery-images .item-image').length,
        dots: false,
        focusOnSelect: true,
        vertical: true,
        waitForAnimate: true,
        responsive: [
            {
                breakpoint: 1260,
                settings: {
                    vertical: false,
                    slidesToShow: 4,
                    variableWidth: true,
                    infinite: true,
                }
            },
            {
                breakpoint: 992,
                settings: {
                    vertical: false,
                    slidesToShow: 3,
                    variableWidth: true,
                    infinite: true,
                }
            }
        ]
    });
    $('.gallery-item-container .slideshow').slick({
        infinite: false,
        slidesToShow: 1,
        slidesToScroll: 1,
        adaptiveHeight: true,
        arrows: true,
        asNavFor: '.slideshow-nav',
        prevArrow: '<button class="slick-prev" aria-label="Previous" type="button"><svg\n' +
            '        xmlns="http://www.w3.org/2000/svg"\n' +
            '        width="14px" height="22px">\n' +
            '    <path fill-rule="evenodd"  stroke="rgb(0, 0, 0)" stroke-width="2px" stroke-linecap="butt" stroke-linejoin="miter" fill="none"\n' +
            '          d="M2.468,1.000 L12.000,11.000 L2.468,21.000 "></path>\n' +
            '</svg></button>',
        nextArrow: '<button class="slick-next" aria-label="Next" type="button"><svg\n' +
            '        xmlns="http://www.w3.org/2000/svg"\n' +
            '        width="14px" height="22px">\n' +
            '    <path fill-rule="evenodd"  stroke="rgb(0, 0, 0)" stroke-width="2px" stroke-linecap="butt" stroke-linejoin="miter" fill="none"\n' +
            '          d="M2.468,1.000 L12.000,11.000 L2.468,21.000 "></path>\n' +
            '</svg></button>',
    });
    $('.project-wrapper .slideshow').slick({
        infinite: false,
        slidesToShow: 1,
        slidesToScroll: 1,
        adaptiveHeight: true,
        arrows: true,
        prevArrow: '<button class="slick-prev" aria-label="Previous" type="button"><svg\n' +
            '        xmlns="http://www.w3.org/2000/svg"\n' +
            '        width="14px" height="22px">\n' +
            '    <path fill-rule="evenodd"  stroke="rgb(0, 0, 0)" stroke-width="2px" stroke-linecap="butt" stroke-linejoin="miter" fill="none"\n' +
            '          d="M2.468,1.000 L12.000,11.000 L2.468,21.000 "></path>\n' +
            '</svg></button>',
        nextArrow: '<button class="slick-next" aria-label="Next" type="button"><svg\n' +
            '        xmlns="http://www.w3.org/2000/svg"\n' +
            '        width="14px" height="22px">\n' +
            '    <path fill-rule="evenodd"  stroke="rgb(0, 0, 0)" stroke-width="2px" stroke-linecap="butt" stroke-linejoin="miter" fill="none"\n' +
            '          d="M2.468,1.000 L12.000,11.000 L2.468,21.000 "></path>\n' +
            '</svg></button>',
    });
    $('.news-wrapper .slideshow').slick({
        infinite: false,
        slidesToShow: 1,
        slidesToScroll: 1,
        adaptiveHeight: true,
        arrows: true,
        prevArrow: '<button class="slick-prev" aria-label="Previous" type="button"><svg\n' +
            '        xmlns="http://www.w3.org/2000/svg"\n' +
            '        width="14px" height="22px">\n' +
            '    <path fill-rule="evenodd"  stroke="rgb(0, 0, 0)" stroke-width="2px" stroke-linecap="butt" stroke-linejoin="miter" fill="none"\n' +
            '          d="M2.468,1.000 L12.000,11.000 L2.468,21.000 "></path>\n' +
            '</svg></button>',
        nextArrow: '<button class="slick-next" aria-label="Next" type="button"><svg\n' +
            '        xmlns="http://www.w3.org/2000/svg"\n' +
            '        width="14px" height="22px">\n' +
            '    <path fill-rule="evenodd"  stroke="rgb(0, 0, 0)" stroke-width="2px" stroke-linecap="butt" stroke-linejoin="miter" fill="none"\n' +
            '          d="M2.468,1.000 L12.000,11.000 L2.468,21.000 "></path>\n' +
            '</svg></button>',
    });
    $('.slideshow-nav').slick({
        infinite: false,
        slidesToShow: $('.slideshow-nav .image').length,
        slidesToScroll: 1,
        arrows: false,
        focusOnSelect: true,
        asNavFor: '.gallery-item-container .slideshow',
        adaptiveHeight: true,
        variableWidth: true,
    });
    /* End Slick */
});