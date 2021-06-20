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
 * Theme customizer left-sidebar js
 *
 * @package   theme_remui/customizer
 * @copyright (c) 2021 WisdmLabs (https://wisdmlabs.com/) <support@wisdmlabs.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Yogesh Shirsath
 */

define('theme_remui/customizer/left-sidebar', ['jquery', './utils'], function($, Utils) {

    /**
     * Selectors
     */
    var SELECTOR = {
        BASEMAIN: 'left-sidebar-main',
        MAINBACKGROUNDCOLOR: 'left-sidebar-main-background-color',
        MAINLINKTEXT: 'left-sidebar-main-link-text',
        MAINLINKHOVERTEXT: 'left-sidebar-main-link-hover-text',
        MAINLINKHOVERBACKGROUND: 'left-sidebar-main-link-hover-background',
        MAINACTIVELINKCOLOR: 'left-sidebar-main-active-link-color',
        MAINACTIVELINKBACKGROUND: 'left-sidebar-main-active-link-background',
        BASENODES: 'left-sidebar-nodes',
        CHECKS: [
            'hide-dashboard',
            'hide-home',
            'hide-calendar',
            'hide-private-files',
            'hide-my-courses',
            'hide-content-bank'
        ],
        BASEFOOTER: 'left-sidebar-secondary',
        FOOTERBACKGROUNDCOLOR: 'left-sidebar-secondary-background-color',
        FOOTERLINKICON: 'left-sidebar-secondary-link-icon',
        FOOTERLINKHOVERBACKGROUND: 'left-sidebar-secondary-link-hover-background',
        FOOTERFONTSIZE: 'left-sidebar-secondary-font-size'
    };

    /**
     * Handler main sidebar settings.
     */
    function handleMain() {
        let backgroundColor = $(`[name="${SELECTOR.MAINBACKGROUNDCOLOR}"]`).spectrum('get').toString();
        let linkText = $(`[name="${SELECTOR.MAINLINKTEXT}"]`).spectrum('get').toString();
        let linkHoverText = $(`[name="${SELECTOR.MAINLINKHOVERTEXT}"]`).spectrum('get').toString();
        let linkHoverBackground = $(`[name="${SELECTOR.MAINLINKHOVERBACKGROUND}"]`).spectrum('get').toString();
        let activeLinkColor = $(`[name="${SELECTOR.MAINACTIVELINKCOLOR}"]`).spectrum('get').toString();
        let activeLinkBackground = $(`[name="${SELECTOR.MAINACTIVELINKBACKGROUND}"]`).spectrum('get').toString();

        let content = `
            [data-region=drawer] {
                background-color: ${backgroundColor};
            }
            [data-region=drawer] .list-group {
                background-color: ${backgroundColor};
                border: none !important;
            }
            [data-region=drawer] .list-group .list-group-item {
                background-color: ${backgroundColor};
                color: ${linkText};
            }
            [data-region=drawer] .section-heading {
                color: ${linkText} !important;
            }
            [data-region=drawer] .list-group .list-group-item:hover,
            [data-region=drawer] .list-group .list-group-item.hovered,
            [data-region=drawer] .list-group .list-group-item.hovered .media-body,
            [data-region=drawer] .list-group .list-group-item:hover .media-body,
            [data-region=drawer] .list-group .list-group-item:hover .icon {
                background-color: ${linkHoverBackground} !important;
                color: ${linkHoverText} !important;
            }
            [data-region=drawer] .list-group .list-group-item.hovered .icon,
            [data-region=drawer] .list-group .list-group-item.hover a {
                color: ${linkHoverText} !important;
            }
            [data-region=drawer] .list-group .list-group-item.active {
                background-color: ${activeLinkBackground};
                color: ${activeLinkColor};
            }
            [data-region=drawer] .list-group .list-group-item.active .icon {
                color: ${activeLinkColor};
            }
        `;
        Utils.putStyle(SELECTOR.BASEMAIN, content);
    }

    /**
     * Handler nodes settings.
     */
    function handleNodes() {
        let content = `
        `;
        SELECTOR.CHECKS.forEach(function(checkbox) {
            let checked = $(`[name="${checkbox}"]`).is(':checked');
            content += `
                #nav-drawer [data-key="${$(`[name="${checkbox}"]`).data('target')}"] {
                    display: ${checked ? 'none' : 'block'};
                }
            `;
        });

        let checked = $(`[name="hide-my-courses"]`).is(':checked');
        content += `
            #nav-drawer [data-parent-key="${$(`[name="hide-my-courses"]`).data('target')}"] {
                display: ${checked ? 'none' : 'block'};
            }
        `;
        Utils.putStyle(SELECTOR.BASENODES, content);
    }

    /**
     * Handler sidebar footer settings.
     */
    function handleFooter() {
        let backgroundColor = $(`[name="${SELECTOR.FOOTERBACKGROUNDCOLOR}"]`).spectrum('get').toString();
        let linkIcon = $(`[name="${SELECTOR.FOOTERLINKICON}"]`).spectrum('get').toString();
        let linkHoverBackground = $(`[name="${SELECTOR.FOOTERLINKHOVERBACKGROUND}"]`).spectrum('get').toString();
        let fontSize = $(`[name="${SELECTOR.FOOTERFONTSIZE}"]`).val();

        let content = `
            [data-region=drawer] .site-menubar-footer a {
                background-color: ${backgroundColor} !important;
            }

            [data-region=drawer] .site-menubar-footer a .fa {
        `;

        if (fontSize != '') {
            content += `
                font-size: ${fontSize}rem;
            `;
        }

        content += `color: ${linkIcon} !important;
            }

            [data-region=drawer] .site-menubar-footer a:hover {
                background-color: ${linkHoverBackground} !important;
            }
        `;

        // Tablet.
        fontSize = $(`[name='${SELECTOR.FOOTERFONTSIZE}-tablet']`).val();
        if (fontSize != '') {
            content += `\n
                @media screen and (min-width: ${Utils.deviceWidth.mobile + 1}px) and (max-width: ${Utils.deviceWidth.tablet}px) {
                    [data-region=drawer] .site-menubar-footer a .fa {
                        font-size: ${fontSize}rem;
                    }
                }
            `;
        }

        // Mobile.
        fontSize = $(`[name='${SELECTOR.FOOTERFONTSIZE}-mobile']`).val();
        if (fontSize != '') {
            content += `\n
                @media screen and (max-width: ${Utils.deviceWidth.mobile}px) {
                    [data-region=drawer] .site-menubar-footer a .fa {
                        font-size: ${fontSize}rem;
                    }
                }
            `;
        }
        Utils.putStyle(SELECTOR.BASEFOOTER, content);
    }

    /**
     * Apply settings.
     */
    function apply() {
        handleMain();
        handleNodes();
        handleFooter();
    }

    /**
     * Initialize events.
     */
    function init() {
        apply();
        let id = [];
        SELECTOR.CHECKS.forEach(function(checkbox) {
            id.push(`[name="${checkbox}"]`);
        });
        id.push(`[name="${SELECTOR.FOOTERFONTSIZE}"]`);
        $(id.join()).on('change', function() {
            handleNodes();
        });

        $(`
            [name="${SELECTOR.MAINBACKGROUNDCOLOR}"],
            [name="${SELECTOR.MAINLINKTEXT}"],
            [name="${SELECTOR.MAINLINKHOVERTEXT}"],
            [name="${SELECTOR.MAINLINKHOVERBACKGROUND}"],
            [name="${SELECTOR.MAINACTIVELINKCOLOR}"],
            [name="${SELECTOR.MAINACTIVELINKBACKGROUND}"]
        `).on('color.changed', handleMain);

        $(`
            [name="${SELECTOR.FOOTERBACKGROUNDCOLOR}"],
            [name="${SELECTOR.FOOTERLINKICON}"],
            [name="${SELECTOR.FOOTERLINKHOVERBACKGROUND}"]
        `).on('color.changed', handleFooter);

        $(`
            [name="${SELECTOR.FOOTERFONTSIZE}"],
            [name="${SELECTOR.FOOTERFONTSIZE}-tablet"],
            [name="${SELECTOR.FOOTERFONTSIZE}-mobile"]
        `).on('input', handleFooter);
    }

    return {
        init: init,
        apply: apply
    };
});
