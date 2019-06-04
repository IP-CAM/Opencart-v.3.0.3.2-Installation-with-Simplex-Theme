// $("input#quantity").inputSpinner({
//     decrementButton: "<strong>-</strong>",
//     incrementButton: "<strong>+</strong>",
//     groupClass: "input-group-spinner",
//     buttonsWidth: "0",
//     textAlign: "center",
//     autoDelay: 500,
//     autoInterval: 100,
//     boostThreshold: 15,
//     boostMultiplier: 2,
//     locale: null
// });
$('.product-slider').slick({
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
$('#slider .main-slider').slick({
    dots: false,
    infinite: false,
    slidesToShow: 1,
    slidesToScroll: 1,
    vertical: true,
    verticalSwiping: true,
    adaptiveHeight: true,
    asNavFor: '.slider-nav',
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
$('#slider .slider-nav').slick({
    asNavFor: '.main-slider',
    slidesToShow: $('.slider-nav .item').length,
    slidesToScroll: 1,
    dots: false,
    focusOnSelect: true,
    vertical: true,
    infinite: false,
    responsive: [
        {
            breakpoint: 992,
            settings: {
                vertical: false,
                variableWidth: true,
            }
        }
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
$('.input-range').each(function () {
    var slider = $('.range', this);
    var max_input = $('.input-max input', this);
    var min_input = $('.input-min input', this);
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
$('.cards a').click(function () {
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
$('label').each(function () {
    $('input[type=checkbox]', this).click(function () {
        $(this).parent().toggleClass('semi-bold');
    });
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
});
$('.gallery-images').slick({
    asNavFor: '.gallery-list',
    slidesToScroll: 1,
    slidesToShow: $('.gallery-images .item-image').length,
    dots: false,
    focusOnSelect: true,
    vertical: true,
    infinite: false,
});
$('.tab-content > button').click(function () {
    //console.log($(this).attr('data-target'));
    let attr = $(this).attr('data-target');
    $(attr).toggleClass('active');
    $($('[aria-expanded="false"]', this).attr('data-target')).removeClass('active');
    $('.nav-tabs .nav-link:not([href="' + attr + '"])').removeClass('active');
    $('.nav-tabs .nav-link[href="' + attr + '"]').toggleClass('active');
});