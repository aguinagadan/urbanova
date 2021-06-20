<?php
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
 * Theme customizer left_sidebar process trait
 *
 * @package   theme_remui
 * @copyright (c) 2021 WisdmLabs (https://wisdmlabs.com/) <support@wisdmlabs.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Yogesh Shirsath
 */

namespace theme_remui\customizer\process;

defined('MOODLE_INTERNAL') || die;

trait left_sidebar {

    /**
     * Process left sidebar
     *
     * @param string $css css content
     * @return string processed css conent
     */
    private function process_left_sidebar($css) {
        // Main sidebar color.
        $mainbackgroundcolor = $this->get_config('left-sidebar-main-background-color');
        $css = str_replace("'[[setting:left-sidebar-main-background-color]]'", $mainbackgroundcolor, $css);
        $mainlinktext = $this->get_config('left-sidebar-main-link-text');
        $css = str_replace("'[[setting:left-sidebar-main-link-text]]'", $mainlinktext, $css);
        $mainlinkhovertext = $this->get_config('left-sidebar-main-link-hover-text');
        $css = str_replace("'[[setting:left-sidebar-main-link-hover-text]]'", $mainlinkhovertext, $css);
        $mainlinkhoverbackground = $this->get_config('left-sidebar-main-link-hover-background');
        $css = str_replace("'[[setting:left-sidebar-main-link-hover-background]]'", $mainlinkhoverbackground, $css);
        $mainactivelinkcolor = $this->get_config('left-sidebar-main-active-link-color');
        $css = str_replace("'[[setting:left-sidebar-main-active-link-color]]'", $mainactivelinkcolor, $css);
        $mainactivelinkbackground = $this->get_config('left-sidebar-main-active-link-background');
        $css = str_replace("'[[setting:left-sidebar-main-active-link-background]]'", $mainactivelinkbackground, $css);

        // Settings list.
        $settings = [
            'hide-dashboard' => 'myhome',
            'hide-home' => 'home',
            'hide-calendar' => 'calendar',
            'hide-private-files' => 'privatefiles',
            'hide-my-courses' => 'mycourses',
            'hide-content-bank' => 'contentbank'
        ];

        // Process each setting.
        foreach ($settings as $key => $value) {
            $checked = get_config('theme_remui', $key) == 'hide';
            $css .= "#nav-drawer [data-key='{$value}'] {
                display: " . ($checked == true ? 'none' : 'block') . ";
            }";
        }

        // Footer colors.
        $footerbackgroundcolor = $this->get_config('left-sidebar-secondary-background-color');
        $css = str_replace("'[[setting:left-sidebar-secondary-background-color]]'", $footerbackgroundcolor, $css);
        $footerlinkicon = $this->get_config('left-sidebar-secondary-link-icon');
        $css = str_replace("'[[setting:left-sidebar-secondary-link-icon]]'", $footerlinkicon, $css);
        $footerlinkhoverbackground = $this->get_config('left-sidebar-secondary-link-hover-background');
        $css = str_replace("'[[setting:left-sidebar-secondary-link-hover-background]]'", $footerlinkhoverbackground, $css);

        // Footer font size.
        $footerfontsize = $this->get_config('left-sidebar-secondary-font-size', true);
        $css = str_replace("'[[setting:left-sidebar-secondary-font-size]]'", $footerfontsize['default'], $css);
        // Font size tablet.
        if (isset($footerfontsize['tablet']) && $footerfontsize['tablet'] != '') {
            $css .= $this->wrap_responsive(
                "tablet",
                "[data-region=drawer] .site-menubar-footer a .fa {
                    font-size: " . $footerfontsize['tablet'] . "rem;
                }"
            );
        }

        // Font size mobile.
        if (isset($footerfontsize['mobile']) && $footerfontsize['mobile'] != '') {
            $css .= $this->wrap_responsive(
                "mobile",
                "[data-region=drawer] .site-menubar-footer a .fa {
                    font-size: " . $footerfontsize['mobile'] . "rem;
                }"
            );
        }
        return $css;
    }
}
