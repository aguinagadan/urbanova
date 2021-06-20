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
 * Theme customizer left_sidebar trait
 *
 * @package   theme_remui
 * @copyright (c) 2021 WisdmLabs (https://wisdmlabs.com/) <support@wisdmlabs.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Yogesh Shirsath
 */

namespace theme_remui\customizer\add;

defined('MOODLE_INTERNAL') || die;

trait left_sidebar {
    /**
     * Add left sidebar settings
     */
    private function left_sidebar_settings() {
        $this->add_panel('left-sidebar', get_string('left-sidebar', 'theme_remui'), 'root');
        $this->add_panel('left-sidebar-main', get_string('main-sidebar', 'theme_remui'), 'left-sidebar');

        // Sidebar colors.
        if (get_config('theme_remui', 'sidebarcolor') != 'site-menubar-light') {
            // Default dark color scheme.
            $colors = [
                'link-text' => '#97a3aa',
                'background-color' => '#263238',
                'link-hover-text' => 'rgb(212 215 208)',
                'link-hover-background' => '#2a363b',
                'active-link-color' => '#fff',
                'active-link-background' => '#212a2e',
            ];
        } else {
            // Default light color scheme.
            $colors = [
                'link-text' => '#37474f',
                'background-color' => '#fff',
                'link-hover-text' => $this->get_common_default_color('primary'),
                'link-hover-background' => '#fff',
                'active-link-color' => $this->get_common_default_color('primary'),
                'active-link-background' => 'rgb(238 244 253)',
            ];
        }

        foreach ($colors as $name => $color) {
            $label = get_string($name, 'theme_remui');
            $this->add_setting(
                'color',
                'left-sidebar-main-' . $name,
                $label,
                'left-sidebar-main',
                [
                    'help' => get_string($name . '_help', 'theme_remui', get_string('main-sidebar', 'theme_remui')),
                    'default' => $color,
                    'options' => [
                        ['key' => 'preferredFormat', 'value' => '\'rgb\''],
                    ]
                ]
            );
        }

        $this->add_panel('left-sidebar-links', get_string('sidebar-links', 'theme_remui'), 'left-sidebar');

        // Hide settings list.
        $settings = [
            'hide-dashboard' => 'myhome',
            'hide-home' => 'home',
            'hide-calendar' => 'calendar',
            'hide-private-files' => 'privatefiles',
            'hide-my-courses' => 'mycourses',
            'hide-content-bank' => 'contentbank'
        ];

        foreach ($settings as $name => $target) {
            $label = get_string($name, 'theme_remui');
            $this->add_setting(
                'checkbox',
                $name,
                $label,
                'left-sidebar-links',
                [
                    'help' => get_string($name . '_help', 'theme_remui'),
                    'default' => '',
                    'value' => 'hide',
                    'options' => [
                        'data-target' => $target
                    ]
                ]
            );
        }

        $this->add_panel('left-sidebar-secondary', get_string('secondary-sidebar', 'theme_remui'), 'left-sidebar');

        // Sidebar footer colors.
        if (get_config('theme_remui', 'sidebarcolor') != 'site-menubar-light') {
            // Default dark color scheme.
            $colors = [
                'background-color' => '#21292e',
                'link-icon' => '#cbcbcb',
                'link-hover-background' => '#1e2427'
            ];
        } else {
            // Default light color scheme.
            $colors = [
                'background-color' => '#e4eaec',
                'link-icon' => '#000',
                'link-hover-background' => '#d5dee1'
            ];
        }

        foreach ($colors as $name => $color) {
            $label = get_string($name, 'theme_remui');
            $this->add_setting(
                'color',
                'left-sidebar-secondary-' . $name,
                $label,
                'left-sidebar-secondary',
                [
                    'help' => get_string($name . '_help', 'theme_remui', get_string('secondary-sidebar', 'theme_remui')),
                    'default' => $color,
                    'options' => [
                        ['key' => 'preferredFormat', 'value' => '\'rgb\''],
                        ['key' => 'showAlpha', 'value' => 'true']
                    ]
                ]
            );
        }

        $label = get_string('font-size', 'theme_remui');
        $this->add_setting(
            'number',
            'left-sidebar-secondary-font-size',
            $label . ' (rem)',
            'left-sidebar-secondary',
            [
                'help' => get_string('font-size_help', 'theme_remui', get_string('secondary-sidebar', 'theme_remui')),
                'default' => 1,
                'options' => [
                    'min' => 0,
                    'step' => 0.01
                ],
                'responsive' => true
            ]
        );
    }
}
