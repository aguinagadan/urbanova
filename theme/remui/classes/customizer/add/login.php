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
 * Theme customizer login trait
 *
 * @package   theme_remui
 * @copyright (c) 2021 WisdmLabs (https://wisdmlabs.com/) <support@wisdmlabs.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Yogesh Shirsath
 */

namespace theme_remui\customizer\add;

defined('MOODLE_INTERNAL') || die;

trait login {

    /**
     * Add login page settings
     */
    public function add_login_settings() {
        $this->add_panel('login', get_string('login', 'theme_remui'), 'root');

        $this->add_setting(
            'info',
            'login-panel',
            get_string('login-page-info', 'theme_remui'),
            'login'
        );

        $this->add_login_panel_settings();

        $this->add_login_page_settings();
    }

    /**
     * Add login panel settings
     *
     * @return void
     */
    private function add_login_panel_settings() {
        // Login panel heading.
        $this->add_setting(
            'heading_start',
            'login-panel',
            get_string('panel', 'theme_remui'),
            'login'
        );

        // Logo for login page.
        $label = get_string('logo', 'theme_remui');
        $name = 'login-panel-logo';
        $this->add_setting(
            'file',
            $name,
            $label,
            'login',
            [
                'help' => '<div>Default:' . get_string('login-panel-logo-default', 'theme_remui'). '</div>',
                'get_url' => true,
                'options' => [
                    'subdirs' => 0,
                    'maxfiles' => 1,
                    'accepted_types' => array('web_image')
                ]
            ]
        );

        // Background color.
        $label = get_string('page-background', 'theme_remui');
        $this->add_setting(
            'color',
            'loginpanelbackgroundcolor',
            $label,
            'login',
            [
                'help' => get_string('loginpanelbackgroundcolor_help', 'theme_remui'),
                'default' => $this->get_common_default_color('background')
            ]
        );

        // Text color.
        $label = get_string('text-color', 'theme_remui');
        $this->add_setting(
            'color',
            'loginpaneltextcolor',
            $label,
            'login',
            [
                'help' => get_string('loginpaneltextcolor_help', 'theme_remui'),
                'default' => $this->get_common_default_color('text')
            ]
        );

        // Link color.
        $label = get_string('link-color', 'theme_remui');
        $this->add_setting(
            'color',
            'loginpanellinkcolor',
            $label,
            'login',
            [
                'help' => get_string('loginpanellinkcolor_help', 'theme_remui'),
                'default' => $this->get_common_default_color('link')
            ]
        );

        // Link color.
        $label = get_string('link-hover-color', 'theme_remui');
        $this->add_setting(
            'color',
            'loginpanellinkhovercolor',
            $label,
            'login',
            [
                'help' => get_string('loginpanellinkhovercolor_help', 'theme_remui'),
                'default' => $this->get_common_default_color('link')
            ]
        );

        // Login panel position.
        $label = get_string('login-panel-position', 'theme_remui');
        $this->add_setting(
            'select',
            'login-panel-position',
            $label,
            'login',
            [
                'help' => get_string('login-panel-position_help', 'theme_remui'),
                'default' => 'right',
                'options' => [
                    'right' => get_string('right', 'core_editor'),
                    'left' => get_string('left', 'core_editor')
                ]
            ]
        );

        $this->add_setting(
            'heading_end',
            'login-panel',
            '',
            'login'
        );
    }

    /**
     * Add login page settings
     *
     * @return void
     */
    private function add_login_page_settings() {

        // Login page heading.
        $this->add_setting(
            'heading_start',
            'login-page',
            get_string('page', 'theme_remui'),
            'login'
        );

        // Left side content.
        $name = 'brandlogotext';
        $label = get_string($name, 'theme_remui');
        $this->add_setting(
            'htmleditor',
            $name,
            $label,
            'login',
            [
                'options' => [
                    'rows' => 10
                ]
            ]
        );

        // Login page image.
        $name = 'loginsettingpic';
        $label = get_string($name, 'theme_remui');
        $this->add_setting(
            'file',
            $name,
            $label,
            'login',
            [
                'help' => get_string('loginsettingpicdesc', 'theme_remui'),
                'description' => get_string('loginsettingpicdesc', 'theme_remui'),
                'options' => [
                    'subdirs' => 0,
                    'maxfiles' => 1,
                    'accepted_types' => array('web_image')
                ]
            ]
        );

        // Backround opacity.
        $name = 'loginbackgroundopacity';
        $label = get_string($name, 'theme_remui');
        $this->add_setting(
            'number',
            $name,
            $label,
            'login',
            [
                'help' => get_string('loginbackgroundopacity_help', 'theme_remui'),
                'default' => 0,
                'options' => [
                    'min' => 0,
                    'max' => 1,
                    'step' => 0.01
                ]
            ]
        );

        $this->add_setting(
            'heading_end',
            'login-page',
            '',
            'login'
        );
    }
}
