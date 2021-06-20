// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Template renderer for Moodle. Load and render Moodle templates with Mustache.
 *
 * @module     core/templates
 * @package    core
 * @class      templates
 * @copyright  2015 Damyon Wiese <damyon@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      2.9
 */
"use strict";
define([
    'jquery',
    'core/ajax',
    'theme_remui/tether',
    'core/event',
    'theme_remui/aria',
    'theme_remui/breakpoints',
    'theme_remui/drawer',
    'theme_remui/notice',
    'core/str',
    'core/pubsub',
    'core/modal_factory',
    'theme_remui/feedback',
    'theme_remui/pending',
    'theme_remui/util',
    'theme_remui/alert',
    'theme_remui/button',
    'theme_remui/carousel',
    'theme_remui/collapse',
    'theme_remui/dropdown',
    'theme_remui/modal',
    'theme_remui/scrollspy',
    'theme_remui/tab',
    'theme_remui/tooltip',
    'theme_remui/popover',
    'theme_remui/skintool',
], function(
    $,
    Ajax,
    Tether,
    Event,
    Aria,
    breakpoints,
    Drawer,
    Notice,
    str,
    PubSub,
    ModalFactory,
    Feedback
) {

    window.jQuery = $;
    window.Tether = Tether;
    Drawer.init();

    /**
     * Handle scrolling event on grade page.
     */
     function gradeTableScrollHandler() {
        let left = $('#region-main').offset().left -
        $('#user-grades').offset().left + parseInt($('#region-main').css('padding-left'));
        $('#grade-scrolling-fix').html(`
            .sideonly.floating {
                left: ${left}px !important;
            }
        `);
    }

    // We do twice because: https://github.com/twbs/bootstrap/issues/10547 end.
    $('body').popover({
        trigger: 'focus',
        selector: "[data-toggle=popover][data-trigger!=hover]"
    });

    $("html").popover({
        container: "body",
        selector: "[data-toggle=popover][data-trigger=hover]",
        trigger: "hover",
        delay: {
            hide: 500
        }
    });

    // We need to call popover automatically if nodes are added to the page later.
    Event.getLegacyEvents().done(function(events) {
        $(document).on(events.FILTER_CONTENT_UPDATED, function() {
            $('body').popover({
                selector: '[data-toggle="popover"]',
                trigger: 'focus'
            });

        });
    });

    // Settings update on change.
    $(`#id_s_theme_remui_frontpagechooser`).change(function() {
        window.onbeforeunload = null;
        this.form.submit();
    });
    Aria.init();
    // Open Right Sidebar.
    $(".page-aside-switch .fa-angle-left").on('click', function() {
        $("body").toggleClass('sidebar-open');
        if (!$("body").hasClass("hasblocks")) {
            $(".page-aside-switch").removeClass('d-flex');
            $(".page-aside-switch").hide();
        }
    });

    // Created custom event to adjust left sidebar scrollable height.
    $(document).on('remui-adjust-left-side', function() {
        if ($('.site-menubar-footer').length == 0) {
            return;
        }
        let sidebarFooterHeight = $('.site-menubar-footer').height();
        if ($('#nav-drawer').length == 0) {
            return;
        }
        $('#nav-drawer').css('bottom', sidebarFooterHeight + 'px');
    });

    $(document).ready(function() {

        // Dirty hack to handler grade page scrolling issue.
        if ($('body').is('#page-grade-report-grader-index')) {
            $('body').append('<style id="grade-scrolling-fix"></style>');
            $('#region-main > .card').on('scroll', gradeTableScrollHandler);
            $(window).on('scroll', gradeTableScrollHandler);
        }
        $('[data-toggle="tooltip"]').tooltip();
        if ($("body").hasClass("editing") && $("body").hasClass("hasblocks")) {
            $(".page-aside-switch .fa-angle-left").trigger('click');
        }

        $(document).trigger('remui-adjust-left-side');

        $('[data-region="drawer-toggle"]').on('click', function() {
            $(document).trigger('remui-adjust-left-side');
        });

        resetSidebar();

        // Collapsible menu in header implementation.
        // Moves excess menu items to 3 dot menu on resize and based on available screen space.

        // Greedy menu js implementation.
        var $btn = $('nav.greedy .menu-toggle');
        var $vlinks = $('nav.greedy .links');
        var $hlinks = $('nav.greedy .hidden-links');
        var numOfItems = 0;
        var totalSpace = 0;
        var breakWidths = [];
        var availableSpace, numOfVisibleItems, requiredSpace;

        // Get initial state.
        $vlinks.children().outerWidth(function(i, w) {
            totalSpace += w;
            numOfItems += 1;
            breakWidths.push(totalSpace);
        });

        /**
         * Custom collapsible navigation menu
         */
        function wdmCollapsibleNavMenu() {
            // Get instant state.
            availableSpace = $vlinks.width() - 10;
            numOfVisibleItems = $vlinks.children().length;
            requiredSpace = breakWidths[numOfVisibleItems - 1];

            // There is not enought space.
            if (requiredSpace > availableSpace) {
                $vlinks.children().last().prependTo($hlinks);
                numOfVisibleItems -= 1;
                wdmCollapsibleNavMenu();
                // There is more than enough space.
            } else if (availableSpace > breakWidths[numOfVisibleItems]) {
                $hlinks.children().first().appendTo($vlinks);
                numOfVisibleItems += 1;
            }
            // Update the button accordingly.
            $btn.attr("count", numOfItems - numOfVisibleItems);
            if (numOfVisibleItems === numOfItems) {
                $btn.addClass('hidden');
            } else {
                $btn.removeClass('hidden');
            }
        }

        // Init collapsible nav menu.
        wdmCollapsibleNavMenu();

        // Hide / show hidden-links ul on click button.
        $btn.on('click', function() {
            var currLeft = $(this).offset().left - 25;
            $hlinks.css({
                left: currLeft + "px"
            });
            $hlinks.toggleClass('hidden');
        });

        // Close when clicking somewhere else.
        $(document.body).on('click', function(evt) {
            let IGNORED_ELS = 'ul.hidden-links, button.menu-toggle, .modal, .alertify, .-handled-lick';
            if (evt.button === 0 && !$('ul.hidden-links').hasClass('hidden')) {
                var target = evt.target;
                if (target === evt.currentTarget || !$(target).closest(IGNORED_ELS).length) {
                    $('ul.hidden-links').addClass('hidden');
                }
            }
        });

        // Resize menu when drawer opens closes.
        PubSub.subscribe('nav-drawer-toggle-end', function() {
            wdmCollapsibleNavMenu();
        });

        // Window listeners.
        $(window).resize(function() {
            wdmCollapsibleNavMenu();
        });

        // Collapsible menu JS ends.

        // Auto hide messaging bar when not merged in sidebar.
        if ($('[data-region="right-hand-drawer"]').parents('#sidebar-message').length == 0) {
            $(document.body).on('click', function(evt) {
                if (evt.button === 0 && !$('[data-region="right-hand-drawer"]').hasClass('hidden')) {
                    // Hide Message Drawer if click outsite.
                    var IGNORE_DRAWER_BTN = '[data-region="right-hand-drawer"], [href="#sidebar-message"]';
                    var target = evt.target;
                    if (target === evt.currentTarget || !$(target).closest(IGNORE_DRAWER_BTN).length) {
                        $('[data-region="right-hand-drawer"]').addClass("hidden");
                        $(IGNORE_DRAWER_BTN).removeClass('active');
                    }
                }
            });
        } else {
            // Prevent messaging bar toggle when merged in sidebar.
            $('.page-aside .nav-item .nav-link[href="#sidebar-message"]').click(function() {
                $('[data-region="right-hand-drawer"]').removeClass('hidden');
            });
        }
    });

    // Pin & Unpin Right Sidebar.
    $(".page-aside-switch .fa-thumb-tack").on('click', function() {
        $("body").removeClass('sidebar-open');
        $("body").toggleClass('sidebar-pinned');
        if ($('body').hasClass('sidebar-pinned')) {
            M.util.set_user_preference('pin_aside', 'true');
            Notice.info(M.util.get_string('sidebarpinned', 'theme_remui'));
            $(this).prop('title', M.util.get_string('unpinsidebar', 'theme_remui'));
        } else {
            M.util.set_user_preference('pin_aside', '');
            Notice.info(M.util.get_string('sidebarunpinned', 'theme_remui'));
            $(this).prop('title', M.util.get_string('pinsidebar', 'theme_remui'));
        }
    });
    // This function ...  handles the aria-selected attribute for tabs in right sidebar.
    $('.page-aside .site-sidebar-nav .nav-link').on('click', function(){
        $('.page-aside .site-sidebar-nav .nav-link').attr('aria-selected', false);
        $(this).attr('aria-selected', true);
    });

    // Close Right Sidebar on click outside.
    $(document.body).on('click', function(evt) {
        let IGNORED_ELS = '.page-aside, .modal, .alertify, .-handled-lick';
        if (evt.button === 0 && $('body').hasClass('sidebar-open')) {
            var target = evt.target;
            if (target === evt.currentTarget || !$(target).closest(IGNORED_ELS).length) {
                $('body').removeClass('sidebar-open');
                $(".page-aside-switch").addClass('d-flex');
                $(".page-aside-switch").show();
            }
        }
    });

    // Scroll to top.
    $("#gotop").click(function() {
        $('html, body').animate({scrollTop: 0}, $(window).scrollTop() / 6);
        return false;
    });

    // Hide and Show Go to top button.
    $(window).scroll(function() {
        if ($(this).scrollTop() > 300) {
            $('#gotop').removeClass("d-none").addClass("d-flex");
        } else {
            $('#gotop').removeClass("d-flex").addClass("d-none");
        }
    });

    // Display Submenu on Hover on closed sidebar.
    $('#nav-drawer .list-group-item:not(.activity):not([data-indent="1"])').hover(function() {
        if (!$('#nav-drawer').hasClass('closed')) {
            return;
        }
        let distanceFromTop = $(this).position().top + $('nav.fixed-top:not(.fm-navbar)').height();
        let screenHeight = $(window).height();
        $(this).addClass('hovered');
        $('.media-body').css('top', distanceFromTop + 'px');
        // Sub Menu.
        let topdistance;
        let submenuid = $(this).attr('data-target');
        if (submenuid != undefined && submenuid != "") {
            let submenu = $(`${submenuid}`);
            if (submenu.length > 0) {
                if (distanceFromTop < (screenHeight / 2)) {
                    topdistance = distanceFromTop + 52;
                } else {
                    topdistance = distanceFromTop - $(submenu).height();
                }
                $(submenu).css('top', topdistance + 'px');
                $(submenu).addClass('pop-over');
            }
        }
        // My Courses data-indent = 1.
        let subcourseskey = $(this).attr('data-key');
        if (subcourseskey != undefined ) {
            var subcourses = $('#nav-drawer .mycoursesubmenu.'+subcourseskey);
            if (distanceFromTop < (screenHeight / 2)) {
                topdistance = distanceFromTop + 52;
                $(subcourses).css('top', topdistance + 'px');
                $(subcourses).addClass('pop-over');
            } else {
                $(subcourses).addClass('pop-over');
                topdistance = distanceFromTop - $(subcourses).height();
                $(subcourses).css('top', topdistance + 'px');
            }
        }
    }, function() {
        $(this).removeClass('hovered');
        $('.sub-menu').removeClass('pop-over');
        $('#nav-drawer .mycoursesubmenu').removeClass('pop-over');
    });

    $('.sub-menu').hover(function() {
        let elid = $(this).attr('id');
        let parentel = $('#nav-drawer .list-group-item[data-target="#' + elid + '"]');
        $(parentel).trigger('mouseenter');
        $(parentel).trigger('hover');
        $(parentel).trigger('mouseover');
    }, function() {
        let elid = $(this).attr('id');
        let parentel = $('#nav-drawer .list-group-item[data-target="#' + elid + '"]');
        $(parentel).trigger('mouseout');
    });

    // My Courses.
    $('#nav-drawer .mycoursesubmenu').hover(function() {
        let elkey = $(this).attr('data-parent-key');
        let parentel = $('#nav-drawer .list-group-item[data-key="' + elkey + '"]');
        $(parentel).trigger('mouseenter');
        $(parentel).trigger('hover');
        $(parentel).trigger('mouseover');
    }, function() {
        let elkey = $(this).attr('data-parent-key');
        let parentel = $('#nav-drawer .list-group-item[data-key="' + elkey + '"]');
        $(parentel).trigger('mouseout');
    });
    // Flat Navigation Improvements.
    $('#nav-drawer .toggle-submenu').on('click', function(e) {
        e.preventDefault();
        let _activeNodes = "#nav-drawer .toggle-submenu.active, #nav-drawer .mycoursesubmenu.active";
        let _this = this;
        if (!$(_this).is($(_activeNodes))) {
            $( _activeNodes ).each(function( index ) {
                $( this ).toggleClass('active');
                toggleSubmenuView(this);
            });
        }

        toggleSubmenuView(_this);
        $(_this).toggleClass("active");
        var panel = $(_this).next("." + $(_this).attr('data-key'));
        $(panel).toggleClass("active");
    });

    function toggleSubmenuView(_element){
        let _this = $(_element).find('.toggle-menu');
        let key = $(_this).attr('data-key');
        $(_this).toggleClass('rotate90');
        $('#nav-drawer a.list-group-item[data-parent-key="' + key + '"]').toggleClass('show');
    }

    $('#nav-drawer .toggle-menu').keypress(function(e){
        if(e.keyCode == 32 || e.keyCode == 13){
            //spacebar or enter clicks focused element
            try {
                e.preventDefault();
                let setfocus = 0;
                let key = $(this).attr('data-key');
                $('#nav-drawer .fa-angle-right').toggleClass('rotate90');
                $('#nav-drawer a.list-group-item[data-parent-key="' + key + '"]').toggleClass('show');
                // Fetch all sub menu items and change their tabindex property 
                $('#nav-drawer a.list-group-item[data-parent-key="' + key + '"]').each(function( index ) {
                    if ($('#nav-drawer a.list-group-item[data-parent-key="' + key + '"]')[index].tabIndex == -1){
                        setfocus = 1;
                        $('#nav-drawer a.list-group-item[data-parent-key="' + key + '"]')[index].tabIndex = 0;
                    } else {
                        $('#nav-drawer a.list-group-item[data-parent-key="' + key + '"]')[index].tabIndex = -1;
                    }
                });
                // Set the focus to first element of List
                if (setfocus) {
                    $('#nav-drawer a.list-group-item[data-parent-key="' + key + '"]')[0].focus();
                }
            }
            catch (e) {
                console.log(e);
            }            
        }
    });

    // Toggle section show or hide in default course formats.
    $('.sectionname .toggle-section').click(function() {
        let parentEl = $(this).parent().parent().parent();
        let sectionEl = parentEl.find('ul.section');
        if (sectionEl.length) {
            $(this).toggleClass('down');
            $(sectionEl).toggleClass('hidden');
        }

        if ($(this).attr('aria-expanded') == 'false') { // region is collapsed
            // update the aria-expanded attribute of the region
            $(this).attr('aria-expanded', 'true');
        }
        else { // region is expanded
            // update the aria-expanded attribute of the region
            $(this).attr('aria-expanded', 'false');
        }
    });

    $('.sectionname .toggle-section').keypress(function(e){
        if(e.keyCode == 32 || e.keyCode == 13){
            //spacebar or enter clicks focused element
            try {
                let parentEl = $(this).parent().parent().parent();
                let sectionEl = parentEl.find('ul.section');
                if (sectionEl.length) {
                    $(this).toggleClass('down');
                    $(sectionEl).toggleClass('hidden');
                }
                if ($(this).attr('aria-expanded') == 'false') { // region is collapsed
                    // update the aria-expanded attribute of the region
                    $(this).attr('aria-expanded', 'true');
                    $(sectionEl).focus();
                }
                else { // region is expanded
                    // update the aria-expanded attribute of the region
                    $(this).attr('aria-expanded', 'false');
                }
            }
            catch (e) {
                console.log(e);
            }            
        }
    });

    // Add signup form fields placeholders.
    $(".signupform .fcontainer .form-group").each(function() {
        var label = $.trim($(".col-form-label", this).text());
        $(".felement input", this).attr('placeholder', label);
    });

    // Function for fullscreen.
    $('#toggleFullscreen').click(function() {
        $(this).toggleClass('collapse');
        toggleFullScreen();
    });

    /**
     * Toggle fullscreen
     */
    function toggleFullScreen() {
        if (document.fullscreenElement ||
            document.webkitFullscreenElement ||
            document.mozFullScreenElement ||
            document.msFullscreenElement) {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.mozCancelFullScreen) {
                document.mozCancelFullScreen();
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            } else if (document.msExitFullscreen) {
                document.msExitFullscreen();
            }
        } else {
            var element = $('html')[0];
            if (element.requestFullscreen) {
                element.requestFullscreen();
            } else if (element.mozRequestFullScreen) {
                element.mozRequestFullScreen();
            } else if (element.webkitRequestFullscreen) {
                element.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
            } else if (element.msRequestFullscreen) {
                element.msRequestFullscreen();
            }
        }
    }


    // Fix to get message details when no blocks are present.
    $('.page-aside .page-aside-switch').click(function() {
        $(this).next().find('.nav-item .nav-link.active').trigger('click');
    });

    /**
     * Close drawer and sidebar automatically on smaller window size.
     */
    function resetSidebar() {
        var width = $(window).width();
        if (width < 992) {
            if ($('body').hasClass('drawer-open-left')) {
                $('button[data-action="toggle-drawer"]').trigger('click');
            }
            if ($('body').hasClass('sidebar-open')) {
                $('.page-aside-switch .fa-angle-left').trigger('click');
            }
            $('body').removeClass('sidebar-pinned');
        }
        $(document).trigger('remui-adjust-left-side');
    }

    // Resize listner for reset sidebar function.
    $(window).resize(function() {
        resetSidebar();
    });

    $('.navbar-toggler').click(function() {
        $('.navbar-nav.right-menu').toggleClass('show');
    });

    $('body').on('click', '.showchangelog', function(event) {
        event.preventDefault();
        var trigger = $('#create-modal');
        ModalFactory.create({
            title: M.util.get_string('changelog', 'theme_remui'),
            body: $(this).data('log')
        }, trigger).done(function(modal) {
            modal.show();
        });
        return;
    });

    // Hide update-nag ribbon.
    $('.update-nag [data-dismiss="alert"]').click(function() {
        Ajax.call([{
            'methodname': 'theme_remui_hide_update',
            'args': {}
        }]);
    });

    // Save the preference, after dismiss the announcement
    $('.site-announcement #dismiss_announcement').click(function(){
        M.util.set_user_preference('remui_dismised_announcement', true);
    });

    $('.customizer-turn-editing-on').on('click', function() {
        window.location.href = M.cfg.wwwroot + '/theme/remui/customizer.php?url=' + encodeURI(window.location.href);
    });

    // Showing closed sidebar's all footer icon on mouse over.
    $('.site-menubar-footer.sidebar-closed').mouseover(function() {
        $('.site-menubar-footer.sidebar-closed').css('height', `${55 * $(this).find('a:not(:hidden)').length}px`);
    }).mouseout(function() {
        $('.site-menubar-footer.sidebar-closed').css('height', '55px');
    });

    
});
