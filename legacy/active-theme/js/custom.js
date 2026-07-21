(function( $ ) {
// NAVIGATION CALLBACK
var ww = jQuery(window).width();
jQuery(document).ready(function() { 
	jQuery(".sitenav li a").each(function() {
		if (jQuery(this).next().length > 0) {
			jQuery(this).addClass("parent");
		};
	})
	jQuery(".toggleMenu").click(function(e) { 
		e.preventDefault();
		jQuery(this).toggleClass("active");
		jQuery(".sitenav").slideToggle('fast');
	});
	adjustMenu();
})

// navigation orientation resize callbak
jQuery(window).bind('resize orientationchange', function() {
	ww = jQuery(window).width();
	adjustMenu();
});

var adjustMenu = function() {
	if (ww < 1000) {
		jQuery(".toggleMenu").css("display", "block");
		if (!jQuery(".toggleMenu").hasClass("active")) {
			jQuery(".sitenav").hide();
		} else {
			jQuery(".sitenav").show();
		}
		jQuery(".sitenav li").unbind('mouseenter mouseleave');
	} else {
		jQuery(".toggleMenu").css("display", "none");
		jQuery(".sitenav").show();
		jQuery(".sitenav li").removeClass("hover");
		jQuery(".sitenav li a").unbind('click');
		jQuery(".sitenav li").unbind('mouseenter mouseleave').bind('mouseenter mouseleave', function() {
			jQuery(this).toggleClass('hover');
		});
	}
}

})( jQuery );

jQuery(document).ready(function($) {

    $(".section_cars .one_car a.btn_reserv ").each(function(){
        var hrefParts = $(this).attr('href').split('#');
        $(this).attr('href', hrefParts[0] + (hrefParts[0].indexOf('?') >= 0 ? '&' : '?') + 'type=iframe#' + hrefParts[1]);
    });

    $("a.btn_reserv").fancybox({
        maxWidth    : 1000,
        maxHeight   : '99%',
        fitToView   : true,
        width       : '100%',
        height      : 800,
        autoSize    : false,
        closeClick  : false,
        openEffect  : 'none',
        closeEffect : 'none',
        iframe: {
            scrolling : 'yes',
            preload   : true
        },
        beforeShow: function(){
            $("body").css({'overflow-y':'hidden'});
        },
        afterClose: function(){
            $("body").css({'overflow-y':'visible'});
        },
        wrapCSS : 'people' // add a class selector to the fancybox wrap
    });//.trigger('click');

    $("a.btn_reserv").click(function(e) {
    	e.preventDefault();

        window.location.hash = this.hash;
    });

    if(window.location.hash) {
        var hash = window.location.hash.substring(1); //Puts hash in variable, and removes the # character
        var new_class_name = $("#" + hash).data("class");
        $("#" + hash).fancybox({
            maxWidth    : 1000,
            maxHeight   : '99%',
            fitToView   : true,
            width       : '100%',
            height      : 800,
            autoSize    : false,
            closeClick  : false,
            openEffect  : 'none',
            closeEffect : 'none',
            beforeShow: function(){
                $("body").css({'overflow-y':'hidden'});
            },
            afterClose: function(){
                $("body").css({'overflow-y':'visible'});
            },
            wrapCSS : new_class_name // add a class selector to the fancybox wrap
        }).trigger('click');
        // hash found
    } else {
        // No hash found
    }

    $('.icon_photo, .size-homepage-thumb').click(function(){
        var btn = $(this).closest('.one_car').find('.btn_reserv');
        $(btn).trigger('click');
    })
    function validateForm2(form) {
        if($("#mini_drivers_age_input", form).val() == '' && $("#mini_drivers_age_input", form).val() < 18)
        {
            alert("Driver's age required");
            return false;
        }
        else return true;
    }
    //
    // $("#from_date_sidebar").datepicker({
    //     showOn: 'both',
    //     buttonImageOnly: true,
    //     buttonImage: '/wp-content/images/calendar.png',
    //     minDate:0,
    //     dateFormat:"dd/mm/yy",
    //     onSelect: function(dateText, inst)
    //     {
    //         // var minDate = $(this).datepicker('getDate', '+2d');
    //         // minDate.setDate(minDate.getDate() + 2);
    //         //
    //         // $("#to_datepicker_sidebar").datepicker('option', 'minDate', new Date(minDate));
    //         //
    //         // if( $(this).datepicker('getDate') >= $('#to_datepicker_sidebar').datepicker('getDate') )
    //         // {
    //         //     var nextDayDate = $(this).datepicker('getDate', '+1d');
    //         //     nextDayDate.setDate(nextDayDate.getDate() + 7);
    //         //     $('#to_datepicker_sidebar').datepicker("setDate",nextDayDate);
    //         // }
    //          var minDate = $(this).datepicker('getDate', '+2d');
    //          minDate.setDate(minDate.getDate() + 2);
    //         $("#to_datepicker_sidebar").datepicker('option', 'minDate', $(this).datepicker( "getDate" ));
    //
    //         if( $(this).datepicker('getDate') >= $('#to_datepicker_sidebar').datepicker('getDate') )
    //         {
    //             var nextDayDate = $(this).datepicker('getDate', '+1d');
    //             nextDayDate.setDate(nextDayDate.getDate() + 1);
    //             $('#to_datepicker_sidebar').datepicker("setDate",nextDayDate);
    //         }
    //     }
    // });
    //
    // $("#to_datepicker_sidebar").datepicker({
    //    // minDate:1,
    //     dateFormat:"dd/mm/yy",
    //     showOn: 'both',
    //     buttonImageOnly: true,
    //     buttonImage: '/wp-content/images/calendar.png',
    //     minDate: "+2d",
    //    //   minDate: "+1d",
    //     maxDate: '+2Y',
    // });

    $("#from_date_sidebar").datepicker({
        showOn: 'both',
        buttonImageOnly: true,
        buttonImage: '/wp-content/images/calendar.png',
        minDate:0,
        dateFormat:"dd/mm/yy",
        onSelect: function(dateText, inst)
        {
            var minDate = $(this).datepicker('getDate', '+2d');
            minDate.setDate(minDate.getDate() + 2);

            $("#to_datepicker_sidebar").datepicker('option', 'minDate', new Date(minDate));

            if( $(this).datepicker('getDate') >= $('#to_datepicker_sidebar').datepicker('getDate') )
            {
                var nextDayDate = $(this).datepicker('getDate', '+1d');
                nextDayDate.setDate(nextDayDate.getDate() + 7);
                $('#to_datepicker_sidebar').datepicker("setDate",nextDayDate);
            }
        }
    });

    $("#to_datepicker_sidebar").datepicker({
        // minDate:1,
        dateFormat:"dd/mm/yy",
        showOn: 'both',
        buttonImageOnly: true,
        buttonImage: '/wp-content/images/calendar.png',
        minDate: "+2d",
        maxDate: '+2Y',
    });





});