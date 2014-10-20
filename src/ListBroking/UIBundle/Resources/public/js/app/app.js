/**
 * Created by scastro on 19/10/14.
 */
define(function (require) {
    require(['jquery', 'bootstrap'], function($){
        $(function() {
            //Enable sidebar toggle
            $("[data-toggle='offcanvas']").click(function(e) {
                e.preventDefault();

                //If window is small enough, enable sidebar push menu
                if ($(window).width() <= 992) {
                    $('.row-offcanvas').toggleClass('active');
                    $('.left-side').removeClass("collapse-left");
                    $(".right-side").removeClass("strech");
                    $('.row-offcanvas').toggleClass("relative");
                } else {
                    //Else, enable content streching
                    $('.left-side').toggleClass("collapse-left");
                    $(".right-side").toggleClass("strech");
                }
            });

            /*
             * Make sure that the sidebar is streched full height
             * ---------------------------------------------
             * We are gonna assign a min-height value every time the
             * wrapper gets resized and upon page load. We will use
             * Ben Alman's method for detecting the resize event.
             *
             **/
            function _fix() {
                //Get window height and the wrapper height
                var height = $(window).height() - $("body > .header").height() - ($("body > .footer").outerHeight() || 0);
                $(".wrapper").css("min-height", height + "px");
                var content = $(".wrapper").height();
                //If the wrapper height is greater than the window
                if (content > height)
                //then set sidebar height to the wrapper
                    $(".left-side, html, body").css("min-height", content + "px");
                else {
                    //Otherwise, set the sidebar to the height of the window
                    $(".left-side, html, body").css("min-height", height + "px");
                }
            }
            //Fire upon load
            _fix();
            //Fire when wrapper is resized
            $(".wrapper").resize(function() {
                _fix();
            });

            // Opens all the collapsed boxes
            $(".box.collapsed-box").each(function() {
                var box = $(this);
                var bf = box.find(".box-body, .box-footer");

                box.removeClass("collapsed-box");
                bf.slideDown();
            });

            // Loading Widget, stop when everything is loaded
            $('#loading_widget').fadeOut();

            // Starts the Loading Widget when clicked
            $('[data-load=start],[type=submit]').on('click', function(){
                $('#loading_widget').fadeIn();
            });
        });
    });
});