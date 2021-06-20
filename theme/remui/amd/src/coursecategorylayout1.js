"use strict";

define([
    'jquery',
    'core/ajax',
    'core/str',
    'core/templates',
    'theme_remui/jquery-toolbar',
    'core/notification'
], function ($, Ajax, str, templates, toolbar, Notification) {

    // Globals.
    var filterobj;
    var langstrings;

    var categoryfilter = $('#categoryfilter .categoryselector');

    var categorylink = '[category-filter-link]';

    var cardswrapperarea = $('.course-cards');
    var cardspagination = $('.cards-pagination');

    var pageheaderactions = '.page-header-actionss';
    // View templates.
    var gridtemplate = 'theme_remui/course_card_grid';

    var searchfilter = $('#categoryfilter .simplesearchform .simplesearchform');
    
    var mycoursescheckbox = $('.custom-switch');

    var coursecounter = $('.course-counter span.course-number');

    var tagswrapper = $('.course-tags .tag-wrapper');
    /**
     * Main category filters class.
     * @param  {Integer} defaultCategory Default category to select.
     * @return {Object}  Filter object
     */
    var categoryFilters = function(defaultCategory) {

        var _pageobj = {courses: 0, mycourses: 0};
        var _obj = {
            // Category id.
            category: defaultCategory,
            // Sorting.
            sort: 'ASC',
            // Searching string.
            search: "",
            // If true, means mycourses tab is active.
            tab: false,
            // This object consist of page number that is currently active, has mycourses and all courses tab page number.
            page: _pageobj,
            // If True, regenerate the pagination on any action performed.
            pagination: true,
            // Initially it is null to detect initial change in view, String grid - view in grid format, String list - list format.
            // view: null,
            // This filterModified true will tell that we need to fetch the courses otherwise show old fetched data.
            isFilterModified: true
        };

        _obj.initAttributes = function() {
            _obj.category = defaultCategory;
            _obj.sort = 'ASC';
            _obj.search = '';
            _obj.tab = false;
            _obj.page = _pageobj;
            _obj.pagination = true;
            // _obj.view = null;
            _obj.isFilterModified = true;
        };

        _obj.initPagination = function() {
            _obj.page = {courses: 0, mycourses: 0};
        };
        return _obj;
    }


    /**
     * Filters Generation
     * @param  {Object} filterdata Filter data
     */
    function generateFilters(filterdata) {
        // $(".selectpicker").each(function() {
        //     $(this).selectpicker();
        // });
        if (filterdata.category !== "") {
            $("#categoryfilter .categoryselector").val(filterdata.category);
        }

        if (filterdata.tab == true) {
            $('#switch-label1, #switch-label2').prop('checked', true);
        } else {
            $('#switch-label1, #switch-label2').prop('checked', false);
        }

        // if (filterdata.sort !== null) {
        //     $("#sortfilter.selectpicker").selectpicker('val', filterdata.sort);
        // }

        if (filterdata.search !== "") {
            $("#coursesearchbox").val(filterdata.search);
        }

        // Put animation over here.
        // $(".category-filters").removeClass('d-none');
    }

    /**
     * Update page content
     */
    function updatePage() {
        // Destroy the cards from page.
        destroyCourseCards();
        // Create courses cards again.
        generateCourseCards();
    }

    /*
     * Populate the tags section.
     */
    function populate_tags(){
        var serviceName = 'theme_remui_get_tags';
        var getcourses = Ajax.call([{
            methodname: serviceName,
            args: {
                data: JSON.stringify(filterobj)
            }
        }]);
        getcourses[0].done(function(response) {
            tagswrapper.empty().append(response);
        }).fail(Notification.exception);
    }

    /**
     * Course cards initialization function.
     */
    function generateCourseCards() {
        // Check if Filters are modified and need to fetch the courses.
        if (!filterobj.isFilterModified) {
            return;
        }
        // Fetch the courses.
        getCourses();
    }
    /**
     * Destroy courses cards
     */
    function destroyCourseCards() {
        // Find active tab to append the course cards.
        // var destroytab = (filterobj.tab) ? mycoursesregion : coursesregion;
        // Empty the courses region.
        $(cardswrapperarea).empty();

        // Destroy the pagination also.
        if (filterobj.pagination) {
            // var destroypagination = (filterobj.tab) ? mycoursespagination : coursespagination;
            // $(destroypagination).empty();
            cardspagination.empty();
        }
    }

    /**
     * Ajax to fetch the course and also append those courses to the page.
     * If pagination is enabled it will also generate new pagination.
     */
    function getCourses() {
        $('.courses-tabs .courses-loader-wrap').show();
        // Find active tab to append the course cards.
        // var appendtab = (filterobj.tab) ? mycoursesregion : coursesregion;
        // var appendpagination = (filterobj.tab) ? mycoursespagination : coursespagination;
        var serviceName = 'theme_remui_get_courses';
        var getcourses = Ajax.call([{
            methodname: serviceName,
            args: {
                data: JSON.stringify(filterobj)
            }
        }]);
        getcourses[0].done(function(response) {
            response = JSON.parse(response);

            // Empty the action button on top header, and add new ones.
            $(pageheaderactions).empty();
            if (response.hasmanagebutton == true) {
                $(pageheaderactions).append(response.managebuttons);
            }

            // Show category management dropdown button when user has 'moodle/category:manage' capability.
            if (response.dropdown != undefined) {
                $('#page-header .page-header-actionss').append(response.dropdown);
            } else {
                $('#page-header .page-header-actionss [data-enhance="moodle-core-actionmenu"]').remove();
            }

            // Get the view.
            // var viewobj = (filterobj.view === null) ? response.view : filterobj.view;
            var viewobj = 'grid'; // View is always in grid form.

            // Select the template to render according to view.
            // var rendertemplate = (viewobj == 'grid' || response.latest_card) ? gridtemplate : listtemplate;
            var rendertemplate = gridtemplate;

            // Always render grid teplate on mobile screen and when latest cards setting is on.
            // if (window.screen.width <= 480 || response.latest_card) {
            //     rendertemplate = gridtemplate;
            //     viewobj = 'grid';
            // }

            // Update the view.
            // updateView(viewobj);

            // updateCards(response.latest_card);

            var courses = response.courses;
            if (response.latest_card) {
                cardswrapperarea.addClass('latest-cards');
            }
            $(coursecounter).text(response.totalcoursescount);
            if (courses.length > 0) {
                for (var i = 0; i < courses.length; i++) {
                    // This will call the function to load and render our template.
                    templates.render(rendertemplate, courses[i])
                    // It returns a promise that needs to be resoved.
                    /* eslint no-loop-func: 0 */
                    .then(function(html, js) {
                        // Here eventually I have my compiled template, and any javascript that it generated.
                        // The templates object has append, prepend and replace functions.
                        // templates.appendNodeContents(appendtab, html, js);
                        templates.appendNodeContents(cardswrapperarea, html, js);

                        // Show options button on course card.
                        // check if not mycourse tab.
                        // This is very bad code, couldn't do it another way.
                        // it get called each time a single card is added to dom, try to improve it.
                        // if (!filterobj.tab && !response.latest_card) {
                        if (!response.latest_card) {
                            /* eslint promise/always-return: 0 */
                            $('.course-cards .showoptions').each(function() {
                                $(this).toolbar({
                                    content: $(this).data('toolbar'),
                                    style: 'primary'
                                });
                            });
                        }
                    }).fail(Notification.exception);
                }

            } else {
                var htmldata = '<div class="alert alert-warning alert-dismissible  w-full mx-10" role="alert">';
                htmldata += '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                '<span aria-hidden="true">×</span><span class="sr-only">Close</span></button>' + langstrings[0]+ '</div>';
                templates.appendNodeContents(cardswrapperarea, htmldata, '');

            }

            // // Pagination html.
            // // Check if pagination is enabled.
            if (filterobj.pagination) {
                cardspagination.empty();
                templates.appendNodeContents(cardspagination, response.pagination, '');
            }
            // $('.courses-tabs .courses-loader-wrap').hide();
        }).fail(Notification.exception);
    }

    // This is for, Toolbar redirection not working.
    $(document).delegate('.tool-item', 'click', function() {
        window.location = $(this).attr('href');
    });

    $(categoryfilter).on('change', function(e) {
        filterobj.category = e.target.value;
        filterobj.initPagination();
        window.history.replaceState('pagechange', document.title, M.cfg.wwwroot + '/course/index.php?category=' +
                    encodeURI(e.target.value));
        updatePage();
        populate_tags();
    });

    // Search Filter.
    $(searchfilter).on('submit', function(e) {
        e.preventDefault();
        filterobj.initPagination();
        filterobj.search = $('#categoryfilter .simplesearchform .input-group input[type="text"]').val();
        updatePage();
    });

    $('#switch-label1, #switch-label2').on('change.bootstrapSwitch', function(e, data){
        filterobj.tab = e.target.checked;
        updatePage();
        populate_tags();
    });

    // Pagination Click Event.
    $(document).delegate('.cards-pagination .pagination .page-item .page-link', 'click', function(e) {
        e.preventDefault();
        // Update the page number in object for mycourses as well as all courses tab.
        var linkdata = e.target.href;
        if (linkdata === undefined) {
            linkdata = e.target.parentElement.href;
            if (linkdata === undefined) {
                linkdata = e.target.parentElement.parentElement.href;
            }
        }

        var hashes = linkdata.slice(linkdata.indexOf('?') + 1).split('&');
        var vars = [],
        hash;
        for (var i = 0; i < hashes.length; i++) {
            hash = hashes[i].split('=');
            vars.push(hash[0]);
            vars[hash[0]] = hash[1];
        }

        if (filterobj.tab) {
            filterobj.page.mycourses = vars.page;
        } else {
            filterobj.page.courses = vars.page;
        }

        updatePage();
    });


    var init = function(defaultCategory) {

        $(categorylink).on('click', function(event) {
            event.preventDefault();
            $(categoryfilter).val($(this).data('id')).trigger('change');
            return false;
        });
        var strings = [
            {
                key: 'nocoursefound',
                component: 'theme_remui'
            }
        ];
        str.get_strings(strings).then(function (stringres) {
            langstrings = stringres;

            filterobj = categoryFilters(defaultCategory); // Global object for filters.
            // Initialize global filter object with default values.

            var vars = [],
            hash;
            var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
            for (var i = 0; i < hashes.length; i++) {
                hash = hashes[i].split('=');
                vars.push(hash[0]);
                vars[hash[0]] = hash[1];
            }
            
            if (vars.categoryid && vars.categoryid != 0) {
                filterobj.category = vars.categoryid;
            }

            if (vars.categorysort != undefined) {
                filterobj.sort = vars.categorysort;
            }

            if (vars.search != undefined) {
                filterobj.search = vars.search;
            }

            if (vars.mycourses && vars.mycourses != 0) {
                filterobj.tab = true;
                if ($("body").hasClass("notloggedin")) {
                    filterobj.tab = false;
                }
            }

            generateFilters(filterobj); // This will create filters.
            generateCourseCards(); // Course cards Generation.
            populate_tags();
        });
    }

    return {
     init: init
    };
});
