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
 * Classes for rendering HTML output for Moodle.
 *
 * Please see {@link http://docs.moodle.org/en/Developement:How_Moodle_outputs_HTML}
 * for an overview.
 *
 * Included in this file are the primary renderer classes:
 *     - renderer_base:         The renderer outline class that all renderers
 *                              should inherit from.
 *     - core_renderer:         The standard HTML renderer.
 *     - core_renderer_cli:     An adaption of the standard renderer for CLI scripts.
 *     - core_renderer_ajax:    An adaption of the standard renderer for AJAX scripts.
 *     - plugin_renderer_base:  A renderer class that should be extended by all
 *                              plugin renderers.
 *
 * @package core
 * @category output
 * @copyright  2009 Tim Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Simple base class for Moodle renderers.
 *
 * Tracks the xhtml_container_stack to use, which is passed in in the constructor.
 *
 * Also has methods to facilitate generating HTML output.
 *
 * @copyright 2009 Tim Hunt
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 2.0
 * @package core
 * @category output
 */
class renderer_base {
    /**
     * @var xhtml_container_stack The xhtml_container_stack to use.
     */
    protected $opencontainers;

    /**
     * @var moodle_page The Moodle page the renderer has been created to assist with.
     */
    protected $page;

    /**
     * @var string The requested rendering target.
     */
    protected $target;

    /**
     * @var Mustache_Engine $mustache The mustache template compiler
     */
    private $mustache;

    /**
     * Return an instance of the mustache class.
     *
     * @since 2.9
     * @return Mustache_Engine
     */
    protected function get_mustache() {
        global $CFG;

        if ($this->mustache === null) {
            require_once("{$CFG->libdir}/filelib.php");

            $themename = $this->page->theme->name;
            $themerev = theme_get_revision();

            // Create new localcache directory.
            $cachedir = make_localcache_directory("mustache/$themerev/$themename");

            // Remove old localcache directories.
            $mustachecachedirs = glob("{$CFG->localcachedir}/mustache/*", GLOB_ONLYDIR);
            foreach ($mustachecachedirs as $localcachedir) {
                $cachedrev = [];
                preg_match("/\/mustache\/([0-9]+)$/", $localcachedir, $cachedrev);
                $cachedrev = isset($cachedrev[1]) ? intval($cachedrev[1]) : 0;
                if ($cachedrev > 0 && $cachedrev < $themerev) {
                    fulldelete($localcachedir);
                }
            }

            $loader = new \core\output\mustache_filesystem_loader();
            $stringhelper = new \core\output\mustache_string_helper();
            $quotehelper = new \core\output\mustache_quote_helper();
            $jshelper = new \core\output\mustache_javascript_helper($this->page);
            $pixhelper = new \core\output\mustache_pix_helper($this);
            $shortentexthelper = new \core\output\mustache_shorten_text_helper();
            $userdatehelper = new \core\output\mustache_user_date_helper();

            // We only expose the variables that are exposed to JS templates.
            $safeconfig = $this->page->requires->get_config_for_javascript($this->page, $this);

            $helpers = array('config' => $safeconfig,
                             'str' => array($stringhelper, 'str'),
                             'quote' => array($quotehelper, 'quote'),
                             'js' => array($jshelper, 'help'),
                             'pix' => array($pixhelper, 'pix'),
                             'shortentext' => array($shortentexthelper, 'shorten'),
                             'userdate' => array($userdatehelper, 'transform'),
                         );

            $this->mustache = new \core\output\mustache_engine(array(
                'cache' => $cachedir,
                'escape' => 's',
                'loader' => $loader,
                'helpers' => $helpers,
                'pragmas' => [Mustache_Engine::PRAGMA_BLOCKS],
                // Don't allow the JavaScript helper to be executed from within another
                // helper. If it's allowed it can be used by users to inject malicious
                // JS into the page.
                'disallowednestedhelpers' => ['js']));

        }

        return $this->mustache;
    }


    /**
     * Constructor
     *
     * The constructor takes two arguments. The first is the page that the renderer
     * has been created to assist with, and the second is the target.
     * The target is an additional identifier that can be used to load different
     * renderers for different options.
     *
     * @param moodle_page $page the page we are doing output for.
     * @param string $target one of rendering target constants
     */
    public function __construct(moodle_page $page, $target) {
        $this->opencontainers = $page->opencontainers;
        $this->page = $page;
        $this->target = $target;
    }

    /**
     * Renders a template by name with the given context.
     *
     * The provided data needs to be array/stdClass made up of only simple types.
     * Simple types are array,stdClass,bool,int,float,string
     *
     * @since 2.9
     * @param array|stdClass $context Context containing data for the template.
     * @return string|boolean
     */
    public function render_from_template($templatename, $context) {
        static $templatecache = array();
        $mustache = $this->get_mustache();

        try {
            // Grab a copy of the existing helper to be restored later.
            $uniqidhelper = $mustache->getHelper('uniqid');
        } catch (Mustache_Exception_UnknownHelperException $e) {
            // Helper doesn't exist.
            $uniqidhelper = null;
        }

        // Provide 1 random value that will not change within a template
        // but will be different from template to template. This is useful for
        // e.g. aria attributes that only work with id attributes and must be
        // unique in a page.
        $mustache->addHelper('uniqid', new \core\output\mustache_uniqid_helper());
        if (isset($templatecache[$templatename])) {
            $template = $templatecache[$templatename];
        } else {
            try {
                $template = $mustache->loadTemplate($templatename);
                $templatecache[$templatename] = $template;
            } catch (Mustache_Exception_UnknownTemplateException $e) {
                throw new moodle_exception('Unknown template: ' . $templatename);
            }
        }

        $renderedtemplate = trim($template->render($context));

        // If we had an existing uniqid helper then we need to restore it to allow
        // handle nested calls of render_from_template.
        if ($uniqidhelper) {
            $mustache->addHelper('uniqid', $uniqidhelper);
        }

        return $renderedtemplate;
    }


    /**
     * Returns rendered widget.
     *
     * The provided widget needs to be an object that extends the renderable
     * interface.
     * If will then be rendered by a method based upon the classname for the widget.
     * For instance a widget of class `crazywidget` will be rendered by a protected
     * render_crazywidget method of this renderer.
     * If no render_crazywidget method exists and crazywidget implements templatable,
     * look for the 'crazywidget' template in the same component and render that.
     *
     * @param renderable $widget instance with renderable interface
     * @return string
     */
    public function render(renderable $widget) {
        $classparts = explode('\\', get_class($widget));
        // Strip namespaces.
        $classname = array_pop($classparts);
        // Remove _renderable suffixes
        $classname = preg_replace('/_renderable$/', '', $classname);

        $rendermethod = 'render_'.$classname;
        if (method_exists($this, $rendermethod)) {
            return $this->$rendermethod($widget);
        }
        if ($widget instanceof templatable) {
            $component = array_shift($classparts);
            if (!$component) {
                $component = 'core';
            }
            $template = $component . '/' . $classname;
            $context = $widget->export_for_template($this);
            return $this->render_from_template($template, $context);
        }
        throw new coding_exception('Can not render widget, renderer method ('.$rendermethod.') not found.');
    }

    /**
     * Adds a JS action for the element with the provided id.
     *
     * This method adds a JS event for the provided component action to the page
     * and then returns the id that the event has been attached to.
     * If no id has been provided then a new ID is generated by {@link html_writer::random_id()}
     *
     * @param component_action $action
     * @param string $id
     * @return string id of element, either original submitted or random new if not supplied
     */
    public function add_action_handler(component_action $action, $id = null) {
        if (!$id) {
            $id = html_writer::random_id($action->event);
        }
        $this->page->requires->event_handler("#$id", $action->event, $action->jsfunction, $action->jsfunctionargs);
        return $id;
    }

    /**
     * Returns true is output has already started, and false if not.
     *
     * @return boolean true if the header has been printed.
     */
    public function has_started() {
        return $this->page->state >= moodle_page::STATE_IN_BODY;
    }

    /**
     * Given an array or space-separated list of classes, prepares and returns the HTML class attribute value
     *
     * @param mixed $classes Space-separated string or array of classes
     * @return string HTML class attribute value
     */
    public static function prepare_classes($classes) {
        if (is_array($classes)) {
            return implode(' ', array_unique($classes));
        }
        return $classes;
    }

    /**
     * Return the direct URL for an image from the pix folder.
     *
     * Use this function sparingly and never for icons. For icons use pix_icon or the pix helper in a mustache template.
     *
     * @deprecated since Moodle 3.3
     * @param string $imagename the name of the icon.
     * @param string $component specification of one plugin like in get_string()
     * @return moodle_url
     */
    public function pix_url($imagename, $component = 'moodle') {
        debugging('pix_url is deprecated. Use image_url for images and pix_icon for icons.', DEBUG_DEVELOPER);
        return $this->page->theme->image_url($imagename, $component);
    }

    /**
     * Return the moodle_url for an image.
     *
     * The exact image location and extension is determined
     * automatically by searching for gif|png|jpg|jpeg, please
     * note there can not be diferent images with the different
     * extension. The imagename is for historical reasons
     * a relative path name, it may be changed later for core
     * images. It is recommended to not use subdirectories
     * in plugin and theme pix directories.
     *
     * There are three types of images:
     * 1/ theme images  - stored in theme/mytheme/pix/,
     *                    use component 'theme'
     * 2/ core images   - stored in /pix/,
     *                    overridden via theme/mytheme/pix_core/
     * 3/ plugin images - stored in mod/mymodule/pix,
     *                    overridden via theme/mytheme/pix_plugins/mod/mymodule/,
     *                    example: image_url('comment', 'mod_glossary')
     *
     * @param string $imagename the pathname of the image
     * @param string $component full plugin name (aka component) or 'theme'
     * @return moodle_url
     */
    public function image_url($imagename, $component = 'moodle') {
        return $this->page->theme->image_url($imagename, $component);
    }

    /**
     * Return the site's logo URL, if any.
     *
     * @param int $maxwidth The maximum width, or null when the maximum width does not matter.
     * @param int $maxheight The maximum height, or null when the maximum height does not matter.
     * @return moodle_url|false
     */
    public function get_logo_url($maxwidth = null, $maxheight = 200) {
        global $CFG;
        $logo = get_config('core_admin', 'logo');
        if (empty($logo)) {
            return false;
        }

        // 200px high is the default image size which should be displayed at 100px in the page to account for retina displays.
        // It's not worth the overhead of detecting and serving 2 different images based on the device.

        // Hide the requested size in the file path.
        $filepath = ((int) $maxwidth . 'x' . (int) $maxheight) . '/';

        // Use $CFG->themerev to prevent browser caching when the file changes.
        return moodle_url::make_pluginfile_url(context_system::instance()->id, 'core_admin', 'logo', $filepath,
            theme_get_revision(), $logo);
    }

    /**
     * Return the site's compact logo URL, if any.
     *
     * @param int $maxwidth The maximum width, or null when the maximum width does not matter.
     * @param int $maxheight The maximum height, or null when the maximum height does not matter.
     * @return moodle_url|false
     */
    public function get_compact_logo_url($maxwidth = 300, $maxheight = 300) {
        global $CFG;
        $logo = get_config('core_admin', 'logocompact');
        if (empty($logo)) {
            return false;
        }

        // Hide the requested size in the file path.
        $filepath = ((int) $maxwidth . 'x' . (int) $maxheight) . '/';

        // Use $CFG->themerev to prevent browser caching when the file changes.
        return moodle_url::make_pluginfile_url(context_system::instance()->id, 'core_admin', 'logocompact', $filepath,
            theme_get_revision(), $logo);
    }

    /**
     * Whether we should display the logo in the navbar.
     *
     * We will when there are no main logos, and we have compact logo.
     *
     * @return bool
     */
    public function should_display_navbar_logo() {
        $logo = $this->get_compact_logo_url();
        return !empty($logo) && !$this->should_display_main_logo();
    }

    /**
     * Whether we should display the main logo.
     *
     * @param int $headinglevel The heading level we want to check against.
     * @return bool
     */
    public function should_display_main_logo($headinglevel = 1) {

        // Only render the logo if we're on the front page or login page and the we have a logo.
        $logo = $this->get_logo_url();
        if ($headinglevel == 1 && !empty($logo)) {
            if ($this->page->pagelayout == 'frontpage' || $this->page->pagelayout == 'login') {
                return true;
            }
        }

        return false;
    }

}


/**
 * Basis for all plugin renderers.
 *
 * @copyright Petr Skoda (skodak)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 2.0
 * @package core
 * @category output
 */
class plugin_renderer_base extends renderer_base {

    /**
     * @var renderer_base|core_renderer A reference to the current renderer.
     * The renderer provided here will be determined by the page but will in 90%
     * of cases by the {@link core_renderer}
     */
    protected $output;

    /**
     * Constructor method, calls the parent constructor
     *
     * @param moodle_page $page
     * @param string $target one of rendering target constants
     */
    public function __construct(moodle_page $page, $target) {
        if (empty($target) && $page->pagelayout === 'maintenance') {
            // If the page is using the maintenance layout then we're going to force the target to maintenance.
            // This way we'll get a special maintenance renderer that is designed to block access to API's that are likely
            // unavailable for this page layout.
            $target = RENDERER_TARGET_MAINTENANCE;
        }
        $this->output = $page->get_renderer('core', null, $target);
        parent::__construct($page, $target);
    }

    /**
     * Renders the provided widget and returns the HTML to display it.
     *
     * @param renderable $widget instance with renderable interface
     * @return string
     */
    public function render(renderable $widget) {
        $classname = get_class($widget);
        // Strip namespaces.
        $classname = preg_replace('/^.*\\\/', '', $classname);
        // Keep a copy at this point, we may need to look for a deprecated method.
        $deprecatedmethod = 'render_'.$classname;
        // Remove _renderable suffixes
        $classname = preg_replace('/_renderable$/', '', $classname);

        $rendermethod = 'render_'.$classname;
        if (method_exists($this, $rendermethod)) {
            return $this->$rendermethod($widget);
        }
        if ($rendermethod !== $deprecatedmethod && method_exists($this, $deprecatedmethod)) {
            // This is exactly where we don't want to be.
            // If you have arrived here you have a renderable component within your plugin that has the name
            // blah_renderable, and you have a render method render_blah_renderable on your plugin.
            // In 2.8 we revamped output, as part of this change we changed slightly how renderables got rendered
            // and the _renderable suffix now gets removed when looking for a render method.
            // You need to change your renderers render_blah_renderable to render_blah.
            // Until you do this it will not be possible for a theme to override the renderer to override your method.
            // Please do it ASAP.
            static $debugged = array();
            if (!isset($debugged[$deprecatedmethod])) {
                debugging(sprintf('Deprecated call. Please rename your renderables render method from %s to %s.',
                    $deprecatedmethod, $rendermethod), DEBUG_DEVELOPER);
                $debugged[$deprecatedmethod] = true;
            }
            return $this->$deprecatedmethod($widget);
        }
        // pass to core renderer if method not found here
        return $this->output->render($widget);
    }

    /**
     * Magic method used to pass calls otherwise meant for the standard renderer
     * to it to ensure we don't go causing unnecessary grief.
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call($method, $arguments) {
        if (method_exists('renderer_base', $method)) {
            throw new coding_exception('Protected method called against '.get_class($this).' :: '.$method);
        }
        if (method_exists($this->output, $method)) {
            return call_user_func_array(array($this->output, $method), $arguments);
        } else {
            throw new coding_exception('Unknown method called against '.get_class($this).' :: '.$method);
        }
    }
}


/**
 * The standard implementation of the core_renderer interface.
 *
 * @copyright 2009 Tim Hunt
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 2.0
 * @package core
 * @category output
 */
class core_renderer extends renderer_base {
    /**
     * Do NOT use, please use <?php echo $OUTPUT->main_content() ?>
     * in layout files instead.
     * @deprecated
     * @var string used in {@link core_renderer::header()}.
     */
    const MAIN_CONTENT_TOKEN = '[MAIN CONTENT GOES HERE]';

    /**
     * @var string Used to pass information from {@link core_renderer::doctype()} to
     * {@link core_renderer::standard_head_html()}.
     */
    protected $contenttype;

    /**
     * @var string Used by {@link core_renderer::redirect_message()} method to communicate
     * with {@link core_renderer::header()}.
     */
    protected $metarefreshtag = '';

    /**
     * @var string Unique token for the closing HTML
     */
    protected $unique_end_html_token;

    /**
     * @var string Unique token for performance information
     */
    protected $unique_performance_info_token;

    /**
     * @var string Unique token for the main content.
     */
    protected $unique_main_content_token;

    /** @var custom_menu_item language The language menu if created */
    protected $language = null;

    /**
     * Constructor
     *
     * @param moodle_page $page the page we are doing output for.
     * @param string $target one of rendering target constants
     */
    public function __construct(moodle_page $page, $target) {
        $this->opencontainers = $page->opencontainers;
        $this->page = $page;
        $this->target = $target;

        $this->unique_end_html_token = '%%ENDHTML-'.sesskey().'%%';
        $this->unique_performance_info_token = '%%PERFORMANCEINFO-'.sesskey().'%%';
        $this->unique_main_content_token = '[MAIN CONTENT GOES HERE - '.sesskey().']';
    }

    /**
     * Get the DOCTYPE declaration that should be used with this page. Designed to
     * be called in theme layout.php files.
     *
     * @return string the DOCTYPE declaration that should be used.
     */
    public function doctype() {
        if ($this->page->theme->doctype === 'html5') {
            $this->contenttype = 'text/html; charset=utf-8';
            return "<!DOCTYPE html>\n";

        } else if ($this->page->theme->doctype === 'xhtml5') {
            $this->contenttype = 'application/xhtml+xml; charset=utf-8';
            return "<!DOCTYPE html>\n";

        } else {
            // legacy xhtml 1.0
            $this->contenttype = 'text/html; charset=utf-8';
            return ('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . "\n");
        }
    }

    /**
     * The attributes that should be added to the <html> tag. Designed to
     * be called in theme layout.php files.
     *
     * @return string HTML fragment.
     */
    public function htmlattributes() {
        $return = get_html_lang(true);
        $attributes = array();
        if ($this->page->theme->doctype !== 'html5') {
            $attributes['xmlns'] = 'http://www.w3.org/1999/xhtml';
        }

        // Give plugins an opportunity to add things like xml namespaces to the html element.
        // This function should return an array of html attribute names => values.
        $pluginswithfunction = get_plugins_with_function('add_htmlattributes', 'lib.php');
        foreach ($pluginswithfunction as $plugins) {
            foreach ($plugins as $function) {
                $newattrs = $function();
                unset($newattrs['dir']);
                unset($newattrs['lang']);
                unset($newattrs['xmlns']);
                unset($newattrs['xml:lang']);
                $attributes += $newattrs;
            }
        }

        foreach ($attributes as $key => $val) {
            $val = s($val);
            $return .= " $key=\"$val\"";
        }

        return $return;
    }

    /**
     * The standard tags (meta tags, links to stylesheets and JavaScript, etc.)
     * that should be included in the <head> tag. Designed to be called in theme
     * layout.php files.
     *
     * @return string HTML fragment.
     */
    public function standard_head_html() {
        global $CFG, $SESSION, $SITE;

        // Before we output any content, we need to ensure that certain
        // page components are set up.

        // Blocks must be set up early as they may require javascript which
        // has to be included in the page header before output is created.
        foreach ($this->page->blocks->get_regions() as $region) {
            $this->page->blocks->ensure_content_created($region, $this);
        }

        $output = '';

        // Give plugins an opportunity to add any head elements. The callback
        // must always return a string containing valid html head content.
        $pluginswithfunction = get_plugins_with_function('before_standard_html_head', 'lib.php');
        foreach ($pluginswithfunction as $plugins) {
            foreach ($plugins as $function) {
                $output .= $function();
            }
        }

        // Allow a url_rewrite plugin to setup any dynamic head content.
        if (isset($CFG->urlrewriteclass) && !isset($CFG->upgraderunning)) {
            $class = $CFG->urlrewriteclass;
            $output .= $class::html_head_setup();
        }

        $output .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' . "\n";
        $output .= '<meta name="keywords" content="moodle, ' . $this->page->title . '" />' . "\n";
        // This is only set by the {@link redirect()} method
        $output .= $this->metarefreshtag;

        // Check if a periodic refresh delay has been set and make sure we arn't
        // already meta refreshing
        if ($this->metarefreshtag=='' && $this->page->periodicrefreshdelay!==null) {
            $output .= '<meta http-equiv="refresh" content="'.$this->page->periodicrefreshdelay.';url='.$this->page->url->out().'" />';
        }

        // Set up help link popups for all links with the helptooltip class
        $this->page->requires->js_init_call('M.util.help_popups.setup');

        $focus = $this->page->focuscontrol;
        if (!empty($focus)) {
            if (preg_match("#forms\['([a-zA-Z0-9]+)'\].elements\['([a-zA-Z0-9]+)'\]#", $focus, $matches)) {
                // This is a horrifically bad way to handle focus but it is passed in
                // through messy formslib::moodleform
                $this->page->requires->js_function_call('old_onload_focus', array($matches[1], $matches[2]));
            } else if (strpos($focus, '.')!==false) {
                // Old style of focus, bad way to do it
                debugging('This code is using the old style focus event, Please update this code to focus on an element id or the moodleform focus method.', DEBUG_DEVELOPER);
                $this->page->requires->js_function_call('old_onload_focus', explode('.', $focus, 2));
            } else {
                // Focus element with given id
                $this->page->requires->js_function_call('focuscontrol', array($focus));
            }
        }

        // Get the theme stylesheet - this has to be always first CSS, this loads also styles.css from all plugins;
        // any other custom CSS can not be overridden via themes and is highly discouraged
        $urls = $this->page->theme->css_urls($this->page);
        foreach ($urls as $url) {
            $this->page->requires->css_theme($url);
        }

        // Get the theme javascript head and footer
        if ($jsurl = $this->page->theme->javascript_url(true)) {
            $this->page->requires->js($jsurl, true);
        }
        if ($jsurl = $this->page->theme->javascript_url(false)) {
            $this->page->requires->js($jsurl);
        }

        // Get any HTML from the page_requirements_manager.
        $output .= $this->page->requires->get_head_code($this->page, $this);

        // List alternate versions.
        foreach ($this->page->alternateversions as $type => $alt) {
            $output .= html_writer::empty_tag('link', array('rel' => 'alternate',
                    'type' => $type, 'title' => $alt->title, 'href' => $alt->url));
        }

        // Add noindex tag if relevant page and setting applied.
        $allowindexing = isset($CFG->allowindexing) ? $CFG->allowindexing : 0;
        $loginpages = array('login-index', 'login-signup');
        if ($allowindexing == 2 || ($allowindexing == 0 && in_array($this->page->pagetype, $loginpages))) {
            if (!isset($CFG->additionalhtmlhead)) {
                $CFG->additionalhtmlhead = '';
            }
            $CFG->additionalhtmlhead .= '<meta name="robots" content="noindex" />';
        }

        if (!empty($CFG->additionalhtmlhead)) {
            $output .= "\n".$CFG->additionalhtmlhead;
        }

        if ($this->page->pagelayout == 'frontpage') {
            $summary = s(strip_tags(format_text($SITE->summary, FORMAT_HTML)));
            if (!empty($summary)) {
                $output .= "<meta name=\"description\" content=\"$summary\" />\n";
            }
        }

        return $output;
    }

    /**
     * The standard tags (typically skip links) that should be output just inside
     * the start of the <body> tag. Designed to be called in theme layout.php files.
     *
     * @return string HTML fragment.
     */
    public function standard_top_of_body_html() {
        global $CFG;
        $output = $this->page->requires->get_top_of_body_code($this);
        if ($this->page->pagelayout !== 'embedded' && !empty($CFG->additionalhtmltopofbody)) {
            $output .= "\n".$CFG->additionalhtmltopofbody;
        }

        // Give subsystems an opportunity to inject extra html content. The callback
        // must always return a string containing valid html.
        foreach (\core_component::get_core_subsystems() as $name => $path) {
            if ($path) {
                $output .= component_callback($name, 'before_standard_top_of_body_html', [], '');
            }
        }

        // Give plugins an opportunity to inject extra html content. The callback
        // must always return a string containing valid html.
        $pluginswithfunction = get_plugins_with_function('before_standard_top_of_body_html', 'lib.php');
        foreach ($pluginswithfunction as $plugins) {
            foreach ($plugins as $function) {
                $output .= $function();
            }
        }

        $output .= $this->maintenance_warning();

        return $output;
    }

    /**
     * Scheduled maintenance warning message.
     *
     * Note: This is a nasty hack to display maintenance notice, this should be moved
     *       to some general notification area once we have it.
     *
     * @return string
     */
    public function maintenance_warning() {
        global $CFG;

        $output = '';
        if (isset($CFG->maintenance_later) and $CFG->maintenance_later > time()) {
            $timeleft = $CFG->maintenance_later - time();
            // If timeleft less than 30 sec, set the class on block to error to highlight.
            $errorclass = ($timeleft < 30) ? 'alert-error alert-danger' : 'alert-warning';
            $output .= $this->box_start($errorclass . ' moodle-has-zindex maintenancewarning m-3 alert');
            $a = new stdClass();
            $a->hour = (int)($timeleft / 3600);
            $a->min = (int)(($timeleft / 60) % 60);
            $a->sec = (int)($timeleft % 60);
            if ($a->hour > 0) {
                $output .= get_string('maintenancemodeisscheduledlong', 'admin', $a);
            } else {
                $output .= get_string('maintenancemodeisscheduled', 'admin', $a);
            }

            $output .= $this->box_end();
            $this->page->requires->yui_module('moodle-core-maintenancemodetimer', 'M.core.maintenancemodetimer',
                    array(array('timeleftinsec' => $timeleft)));
            $this->page->requires->strings_for_js(
                    array('maintenancemodeisscheduled', 'maintenancemodeisscheduledlong', 'sitemaintenance'),
                    'admin');
        }
        return $output;
    }

    /**
     * The standard tags (typically performance information and validation links,
     * if we are in developer debug mode) that should be output in the footer area
     * of the page. Designed to be called in theme layout.php files.
     *
     * @return string HTML fragment.
     */
    public function standard_footer_html() {
        global $CFG, $SCRIPT;

        $output = '';
        if (during_initial_install()) {
            // Debugging info can not work before install is finished,
            // in any case we do not want any links during installation!
            return $output;
        }

        // Give plugins an opportunity to add any footer elements.
        // The callback must always return a string containing valid html footer content.
        $pluginswithfunction = get_plugins_with_function('standard_footer_html', 'lib.php');
        foreach ($pluginswithfunction as $plugins) {
            foreach ($plugins as $function) {
                $output .= $function();
            }
        }

        if (core_userfeedback::can_give_feedback()) {
            $output .= html_writer::div(
                $this->render_from_template('core/userfeedback_footer_link', ['url' => core_userfeedback::make_link()->out(false)])
            );
        }

        // This function is normally called from a layout.php file in {@link core_renderer::header()}
        // but some of the content won't be known until later, so we return a placeholder
        // for now. This will be replaced with the real content in {@link core_renderer::footer()}.
        $output .= $this->unique_performance_info_token;
        if ($this->page->devicetypeinuse == 'legacy') {
            // The legacy theme is in use print the notification
            $output .= html_writer::tag('div', get_string('legacythemeinuse'), array('class'=>'legacythemeinuse'));
        }

        // Get links to switch device types (only shown for users not on a default device)
        $output .= $this->theme_switch_links();

        if (!empty($CFG->debugpageinfo)) {
            $output .= '<div class="performanceinfo pageinfo">' . get_string('pageinfodebugsummary', 'core_admin',
                $this->page->debug_summary()) . '</div>';
        }
        if (debugging(null, DEBUG_DEVELOPER) and has_capability('moodle/site:config', context_system::instance())) {  // Only in developer mode
            // Add link to profiling report if necessary
            if (function_exists('profiling_is_running') && profiling_is_running()) {
                $txt = get_string('profiledscript', 'admin');
                $title = get_string('profiledscriptview', 'admin');
                $url = $CFG->wwwroot . '/admin/tool/profiling/index.php?script=' . urlencode($SCRIPT);
                $link= '<a title="' . $title . '" href="' . $url . '">' . $txt . '</a>';
                $output .= '<div class="profilingfooter">' . $link . '</div>';
            }
            $purgeurl = new moodle_url('/admin/purgecaches.php', array('confirm' => 1,
                'sesskey' => sesskey(), 'returnurl' => $this->page->url->out_as_local_url(false)));
            $output .= '<div class="purgecaches">' .
                    html_writer::link($purgeurl, get_string('purgecaches', 'admin')) . '</div>';
        }
        if (!empty($CFG->debugvalidators)) {
            // NOTE: this is not a nice hack, $this->page->url is not always accurate and
            // $FULLME neither, it is not a bug if it fails. --skodak.
            $output .= '<div class="validators"><ul class="list-unstyled ml-1">
              <li><a href="http://validator.w3.org/check?verbose=1&amp;ss=1&amp;uri=' . urlencode(qualified_me()) . '">Validate HTML</a></li>
              <li><a href="http://www.contentquality.com/mynewtester/cynthia.exe?rptmode=-1&amp;url1=' . urlencode(qualified_me()) . '">Section 508 Check</a></li>
              <li><a href="http://www.contentquality.com/mynewtester/cynthia.exe?rptmode=0&amp;warnp2n3e=1&amp;url1=' . urlencode(qualified_me()) . '">WCAG 1 (2,3) Check</a></li>
            </ul></div>';
        }
        return $output;
    }

    /**
     * Returns standard main content placeholder.
     * Designed to be called in theme layout.php files.
     *
     * @return string HTML fragment.
     */
    public function main_content() {
        // This is here because it is the only place we can inject the "main" role over the entire main content area
        // without requiring all theme's to manually do it, and without creating yet another thing people need to
        // remember in the theme.
        // This is an unfortunate hack. DO NO EVER add anything more here.
        // DO NOT add classes.
        // DO NOT add an id.
        return '<div role="main">'.$this->unique_main_content_token.'</div>';
    }

    /**
     * Returns standard navigation between activities in a course.
     *
     * @return string the navigation HTML.
     */
    public function activity_navigation() {
        // First we should check if we want to add navigation.
        $context = $this->page->context;
        if (($this->page->pagelayout !== 'incourse' && $this->page->pagelayout !== 'frametop')
            || $context->contextlevel != CONTEXT_MODULE) {
            return '';
        }

        // If the activity is in stealth mode, show no links.
        if ($this->page->cm->is_stealth()) {
            return '';
        }

        // Get a list of all the activities in the course.
        $course = $this->page->cm->get_course();
        $modules = get_fast_modinfo($course->id)->get_cms();

        // Put the modules into an array in order by the position they are shown in the course.
        $mods = [];
        $activitylist = [];
        foreach ($modules as $module) {
            // Only add activities the user can access, aren't in stealth mode and have a url (eg. mod_label does not).
            if (!$module->uservisible || $module->is_stealth() || empty($module->url)) {
                continue;
            }
            $mods[$module->id] = $module;

            // No need to add the current module to the list for the activity dropdown menu.
            if ($module->id == $this->page->cm->id) {
                continue;
            }
            // Module name.
            $modname = $module->get_formatted_name();
            // Display the hidden text if necessary.
            if (!$module->visible) {
                $modname .= ' ' . get_string('hiddenwithbrackets');
            }
            // Module URL.
            $linkurl = new moodle_url($module->url, array('forceview' => 1));
            // Add module URL (as key) and name (as value) to the activity list array.
            $activitylist[$linkurl->out(false)] = $modname;
        }

        $nummods = count($mods);

        // If there is only one mod then do nothing.
        if ($nummods == 1) {
            return '';
        }

        // Get an array of just the course module ids used to get the cmid value based on their position in the course.
        $modids = array_keys($mods);

        // Get the position in the array of the course module we are viewing.
        $position = array_search($this->page->cm->id, $modids);

        $prevmod = null;
        $nextmod = null;

        // Check if we have a previous mod to show.
        if ($position > 0) {
            $prevmod = $mods[$modids[$position - 1]];
        }

        // Check if we have a next mod to show.
        if ($position < ($nummods - 1)) {
            $nextmod = $mods[$modids[$position + 1]];
        }

        $activitynav = new \core_course\output\activity_navigation($prevmod, $nextmod, $activitylist);
        $renderer = $this->page->get_renderer('core', 'course');
        return $renderer->render($activitynav);
    }

    /**
     * The standard tags (typically script tags that are not needed earlier) that
     * should be output after everything else. Designed to be called in theme layout.php files.
     *
     * @return string HTML fragment.
     */
    public function standard_end_of_body_html() {
        global $CFG;

        // This function is normally called from a layout.php file in {@link core_renderer::header()}
        // but some of the content won't be known until later, so we return a placeholder
        // for now. This will be replaced with the real content in {@link core_renderer::footer()}.
        $output = '';
        if ($this->page->pagelayout !== 'embedded' && !empty($CFG->additionalhtmlfooter)) {
            $output .= "\n".$CFG->additionalhtmlfooter;
        }
        $output .= $this->unique_end_html_token;
        return $output;
    }

    /**
     * The standard HTML that should be output just before the <footer> tag.
     * Designed to be called in theme layout.php files.
     *
     * @return string HTML fragment.
     */
    public function standard_after_main_region_html() {
        global $CFG;
        $output = '';
        if ($this->page->pagelayout !== 'embedded' && !empty($CFG->additionalhtmlbottomofbody)) {
            $output .= "\n".$CFG->additionalhtmlbottomofbody;
        }

        // Give subsystems an opportunity to inject extra html content. The callback
        // must always return a string containing valid html.
        foreach (\core_component::get_core_subsystems() as $name => $path) {
            if ($path) {
                $output .= component_callback($name, 'standard_after_main_region_html', [], '');
            }
        }

        // Give plugins an opportunity to inject extra html content. The callback
        // must always return a string containing valid html.
        $pluginswithfunction = get_plugins_with_function('standard_after_main_region_html', 'lib.php');
        foreach ($pluginswithfunction as $plugins) {
            foreach ($plugins as $function) {
                $output .= $function();
            }
        }

        return $output;
    }

    /**
     * Return the standard string that says whether you are logged in (and switched
     * roles/logged in as another user).
     * @param bool $withlinks if false, then don't include any links in the HTML produced.
     * If not set, the default is the nologinlinks option from the theme config.php file,
     * and if that is not set, then links are included.
     * @return string HTML fragment.
     */
    public function login_info($withlinks = null) {
        global $USER, $CFG, $DB, $SESSION;

        if (during_initial_install()) {
            return '';
        }

        if (is_null($withlinks)) {
            $withlinks = empty($this->page->layout_options['nologinlinks']);
        }

        $course = $this->page->course;
        if (\core\session\manager::is_loggedinas()) {
            $realuser = \core\session\manager::get_realuser();
            $fullname = fullname($realuser);
            if ($withlinks) {
                $loginastitle = get_string('loginas');
                $realuserinfo = " [<a href=\"$CFG->wwwroot/course/loginas.php?id=$course->id&amp;sesskey=".sesskey()."\"";
                $realuserinfo .= "title =\"".$loginastitle."\">$fullname</a>] ";
            } else {
                $realuserinfo = " [$fullname] ";
            }
        } else {
            $realuserinfo = '';
        }

        $loginpage = $this->is_login_page();
        $loginurl = get_login_url();

        if (empty($course->id)) {
            // $course->id is not defined during installation
            return '';
        } else if (isloggedin()) {
            $context = context_course::instance($course->id);

            $fullname = fullname($USER);
            // Since Moodle 2.0 this link always goes to the public profile page (not the course profile page)
            if ($withlinks) {
                $linktitle = get_string('viewprofile');
                $username = "<a href=\"$CFG->wwwroot/user/profile.php?id=$USER->id\" title=\"$linktitle\">$fullname</a>";
            } else {
                $username = $fullname;
            }
            if (is_mnet_remote_user($USER) and $idprovider = $DB->get_record('mnet_host', array('id'=>$USER->mnethostid))) {
                if ($withlinks) {
                    $username .= " from <a href=\"{$idprovider->wwwroot}\">{$idprovider->name}</a>";
                } else {
                    $username .= " from {$idprovider->name}";
                }
            }
            if (isguestuser()) {
                $loggedinas = $realuserinfo.get_string('loggedinasguest');
                if (!$loginpage && $withlinks) {
                    $loggedinas .= " (<a href=\"$loginurl\">".get_string('login').'</a>)';
                }
            } else if (is_role_switched($course->id)) { // Has switched roles
                $rolename = '';
                if ($role = $DB->get_record('role', array('id'=>$USER->access['rsw'][$context->path]))) {
                    $rolename = ': '.role_get_name($role, $context);
                }
                $loggedinas = get_string('loggedinas', 'moodle', $username).$rolename;
                if ($withlinks) {
                    $url = new moodle_url('/course/switchrole.php', array('id'=>$course->id,'sesskey'=>sesskey(), 'switchrole'=>0, 'returnurl'=>$this->page->url->out_as_local_url(false)));
                    $loggedinas .= ' ('.html_writer::tag('a', get_string('switchrolereturn'), array('href' => $url)).')';
                }
            } else {
                $loggedinas = $realuserinfo.get_string('loggedinas', 'moodle', $username);
                if ($withlinks) {
                    $loggedinas .= " (<a href=\"$CFG->wwwroot/login/logout.php?sesskey=".sesskey()."\">".get_string('logout').'</a>)';
                }
            }
        } else {
            $loggedinas = get_string('loggedinnot', 'moodle');
            if (!$loginpage && $withlinks) {
                $loggedinas .= " (<a href=\"$loginurl\">".get_string('login').'</a>)';
            }
        }

        $loggedinas = '<div class="logininfo">'.$loggedinas.'</div>';

        if (isset($SESSION->justloggedin)) {
            unset($SESSION->justloggedin);
            if (!empty($CFG->displayloginfailures)) {
                if (!isguestuser()) {
                    // Include this file only when required.
                    require_once($CFG->dirroot . '/user/lib.php');
                    if ($count = user_count_login_failures($USER)) {
                        $loggedinas .= '<div class="loginfailures">';
                        $a = new stdClass();
                        $a->attempts = $count;
                        $loggedinas .= get_string('failedloginattempts', '', $a);
                        if (file_exists("$CFG->dirroot/report/log/index.php") and has_capability('report/log:view', context_system::instance())) {
                            $loggedinas .= ' ('.html_writer::link(new moodle_url('/report/log/index.php', array('chooselog' => 1,
                                    'id' => 0 , 'modid' => 'site_errors')), get_string('logs')).')';
                        }
                        $loggedinas .= '</div>';
                    }
                }
            }
        }

        return $loggedinas;
    }

    /**
     * Check whether the current page is a login page.
     *
     * @since Moodle 2.9
     * @return bool
     */
    protected function is_login_page() {
        // This is a real bit of a hack, but its a rarety that we need to do something like this.
        // In fact the login pages should be only these two pages and as exposing this as an option for all pages
        // could lead to abuse (or at least unneedingly complex code) the hack is the way to go.
        return in_array(
            $this->page->url->out_as_local_url(false, array()),
            array(
                '/login/index.php',
                '/login/forgot_password.php',
            )
        );
    }

    /**
     * Return the 'back' link that normally appears in the footer.
     *
     * @return string HTML fragment.
     */
    public function home_link() {
        global $CFG, $SITE;

        if ($this->page->pagetype == 'site-index') {
            // Special case for site home page - please do not remove
            return '<div class="sitelink">' .
                   '<a title="Moodle" class="d-inline-block aalink" href="http://moodle.org/">' .
                   '<img src="' . $this->image_url('moodlelogo_grayhat') . '" alt="'.get_string('moodlelogo').'" /></a></div>';

        } else if (!empty($CFG->target_release) && $CFG->target_release != $CFG->release) {
            // Special case for during install/upgrade.
            return '<div class="sitelink">'.
                   '<a title="Moodle" href="http://docs.moodle.org/en/Administrator_documentation" onclick="this.target=\'_blank\'">' .
                   '<img src="' . $this->image_url('moodlelogo_grayhat') . '" alt="'.get_string('moodlelogo').'" /></a></div>';

        } else if ($this->page->course->id == $SITE->id || strpos($this->page->pagetype, 'course-view') === 0) {
            return '<div class="homelink"><a href="' . $CFG->wwwroot . '/">' .
                    get_string('home') . '</a></div>';

        } else {
            return '<div class="homelink"><a href="' . $CFG->wwwroot . '/course/view.php?id=' . $this->page->course->id . '">' .
                    format_string($this->page->course->shortname, true, array('context' => $this->page->context)) . '</a></div>';
        }
    }

    /**
     * Redirects the user by any means possible given the current state
     *
     * This function should not be called directly, it should always be called using
     * the redirect function in lib/weblib.php
     *
     * The redirect function should really only be called before page output has started
     * however it will allow itself to be called during the state STATE_IN_BODY
     *
     * @param string $encodedurl The URL to send to encoded if required
     * @param string $message The message to display to the user if any
     * @param int $delay The delay before redirecting a user, if $message has been
     *         set this is a requirement and defaults to 3, set to 0 no delay
     * @param boolean $debugdisableredirect this redirect has been disabled for
     *         debugging purposes. Display a message that explains, and don't
     *         trigger the redirect.
     * @param string $messagetype The type of notification to show the message in.
     *         See constants on \core\output\notification.
     * @return string The HTML to display to the user before dying, may contain
     *         meta refresh, javascript refresh, and may have set header redirects
     */
    public function redirect_message($encodedurl, $message, $delay, $debugdisableredirect,
                                     $messagetype = \core\output\notification::NOTIFY_INFO) {
        global $CFG;
        $url = str_replace('&amp;', '&', $encodedurl);

        switch ($this->page->state) {
            case moodle_page::STATE_BEFORE_HEADER :
                // No output yet it is safe to delivery the full arsenal of redirect methods
                if (!$debugdisableredirect) {
                    // Don't use exactly the same time here, it can cause problems when both redirects fire at the same time.
                    $this->metarefreshtag = '<meta http-equiv="refresh" content="'. $delay .'; url='. $encodedurl .'" />'."\n";
                    $this->page->requires->js_function_call('document.location.replace', array($url), false, ($delay + 3));
                }
                $output = $this->header();
                break;
            case moodle_page::STATE_PRINTING_HEADER :
                // We should hopefully never get here
                throw new coding_exception('You cannot redirect while printing the page header');
                break;
            case moodle_page::STATE_IN_BODY :
                // We really shouldn't be here but we can deal with this
                debugging("You should really redirect before you start page output");
                if (!$debugdisableredirect) {
                    $this->page->requires->js_function_call('document.location.replace', array($url), false, $delay);
                }
                $output = $this->opencontainers->pop_all_but_last();
                break;
            case moodle_page::STATE_DONE :
                // Too late to be calling redirect now
                throw new coding_exception('You cannot redirect after the entire page has been generated');
                break;
        }
        $output .= $this->notification($message, $messagetype);
        $output .= '<div class="continuebutton">(<a href="'. $encodedurl .'">'. get_string('continue') .'</a>)</div>';
        if ($debugdisableredirect) {
            $output .= '<p><strong>'.get_string('erroroutput', 'error').'</strong></p>';
        }
        $output .= $this->footer();
        return $output;
    }

    /**
     * Start output by sending the HTTP headers, and printing the HTML <head>
     * and the start of the <body>.
     *
     * To control what is printed, you should set properties on $PAGE.
     *
     * @return string HTML that you must output this, preferably immediately.
     */
    public function header() {
        global $USER, $CFG, $SESSION;

        // Give plugins an opportunity touch things before the http headers are sent
        // such as adding additional headers. The return value is ignored.
        $pluginswithfunction = get_plugins_with_function('before_http_headers', 'lib.php');
        foreach ($pluginswithfunction as $plugins) {
            foreach ($plugins as $function) {
                $function();
            }
        }

        if (\core\session\manager::is_loggedinas()) {
            $this->page->add_body_class('userloggedinas');
        }

        if (isset($SESSION->justloggedin) && !empty($CFG->displayloginfailures)) {
            require_once($CFG->dirroot . '/user/lib.php');
            // Set second parameter to false as we do not want reset the counter, the same message appears on footer.
            if ($count = user_count_login_failures($USER, false)) {
                $this->page->add_body_class('loginfailures');
            }
        }

        // If the user is logged in, and we're not in initial install,
        // check to see if the user is role-switched and add the appropriate
        // CSS class to the body element.
        if (!during_initial_install() && isloggedin() && is_role_switched($this->page->course->id)) {
            $this->page->add_body_class('userswitchedrole');
        }

        // Give themes a chance to init/alter the page object.
        $this->page->theme->init_page($this->page);

        $this->page->set_state(moodle_page::STATE_PRINTING_HEADER);

        // Find the appropriate page layout file, based on $this->page->pagelayout.
        $layoutfile = $this->page->theme->layout_file($this->page->pagelayout);
        // Render the layout using the layout file.
        $rendered = $this->render_page_layout($layoutfile);

        // Slice the rendered output into header and footer.
        $cutpos = strpos($rendered, $this->unique_main_content_token);
        if ($cutpos === false) {
            $cutpos = strpos($rendered, self::MAIN_CONTENT_TOKEN);
            $token = self::MAIN_CONTENT_TOKEN;
        } else {
            $token = $this->unique_main_content_token;
        }

        if ($cutpos === false) {
            throw new coding_exception('page layout file ' . $layoutfile . ' does not contain the main content placeholder, please include "<?php echo $OUTPUT->main_content() ?>" in theme layout file.');
        }
        $header = substr($rendered, 0, $cutpos);
        $footer = substr($rendered, $cutpos + strlen($token));

        if (empty($this->contenttype)) {
            debugging('The page layout file did not call $OUTPUT->doctype()');
            $header = $this->doctype() . $header;
        }

        // If this theme version is below 2.4 release and this is a course view page
        if ((!isset($this->page->theme->settings->version) || $this->page->theme->settings->version < 2012101500) &&
                $this->page->pagelayout === 'course' && $this->page->url->compare(new moodle_url('/course/view.php'), URL_MATCH_BASE)) {
            // check if course content header/footer have not been output during render of theme layout
            $coursecontentheader = $this->course_content_header(true);
            $coursecontentfooter = $this->course_content_footer(true);
            if (!empty($coursecontentheader)) {
                // display debug message and add header and footer right above and below main content
                // Please note that course header and footer (to be displayed above and below the whole page)
                // are not displayed in this case at all.
                // Besides the content header and footer are not displayed on any other course page
                debugging('The current theme is not optimised for 2.4, the course-specific header and footer defined in course format will not be output', DEBUG_DEVELOPER);
                $header .= $coursecontentheader;
                $footer = $coursecontentfooter. $footer;
            }
        }

        send_headers($this->contenttype, $this->page->cacheable);

        $this->opencontainers->push('header/footer', $footer);
        $this->page->set_state(moodle_page::STATE_IN_BODY);

        return $header . $this->skip_link_target('maincontent');
    }

    /**
     * Renders and outputs the page layout file.
     *
     * This is done by preparing the normal globals available to a script, and
     * then including the layout file provided by the current theme for the
     * requested layout.
     *
     * @param string $layoutfile The name of the layout file
     * @return string HTML code
     */
    protected function render_page_layout($layoutfile) {
        global $CFG, $SITE, $USER;
        // The next lines are a bit tricky. The point is, here we are in a method
        // of a renderer class, and this object may, or may not, be the same as
        // the global $OUTPUT object. When rendering the page layout file, we want to use
        // this object. However, people writing Moodle code expect the current
        // renderer to be called $OUTPUT, not $this, so define a variable called
        // $OUTPUT pointing at $this. The same comment applies to $PAGE and $COURSE.
        $OUTPUT = $this;
        $PAGE = $this->page;
        $COURSE = $this->page->course;

        ob_start();
        include($layoutfile);
        $rendered = ob_get_contents();
        ob_end_clean();
        return $rendered;
    }

    /**
     * Outputs the page's footer
     *
     * @return string HTML fragment
     */
    public function footer() {
        global $CFG, $DB;

        $output = '';

        // Give plugins an opportunity to touch the page before JS is finalized.
        $pluginswithfunction = get_plugins_with_function('before_footer', 'lib.php');
        foreach ($pluginswithfunction as $plugins) {
            foreach ($plugins as $function) {
                $extrafooter = $function();
                if (is_string($extrafooter)) {
                    $output .= $extrafooter;
                }
            }
        }

        $output .= $this->container_end_all(true);

        $footer = $this->opencontainers->pop('header/footer');

        if (debugging() and $DB and $DB->is_transaction_started()) {
            // TODO: MDL-20625 print warning - transaction will be rolled back
        }

        // Provide some performance info if required
        $performanceinfo = '';
        if (defined('MDL_PERF') || (!empty($CFG->perfdebug) and $CFG->perfdebug > 7)) {
            $perf = get_performance_info();
            if (defined('MDL_PERFTOFOOT') || debugging() || $CFG->perfdebug > 7) {
                $performanceinfo = $perf['html'];
            }
        }

        // We always want performance data when running a performance test, even if the user is redirected to another page.
        if (MDL_PERF_TEST && strpos($footer, $this->unique_performance_info_token) === false) {
            $footer = $this->unique_performance_info_token . $footer;
        }
        $footer = str_replace($this->unique_performance_info_token, $performanceinfo, $footer);

        // Only show notifications when the current page has a context id.
        if (!empty($this->page->context->id)) {
            $this->page->requires->js_call_amd('core/notification', 'init', array(
                $this->page->context->id,
                \core\notification::fetch_as_array($this)
            ));
        }
        $footer = str_replace($this->unique_end_html_token, $this->page->requires->get_end_code(), $footer);

        $this->page->set_state(moodle_page::STATE_DONE);

        return $output . $footer;
    }

    /**
     * Close all but the last open container. This is useful in places like error
     * handling, where you want to close all the open containers (apart from <body>)
     * before outputting the error message.
     *
     * @param bool $shouldbenone assert that the stack should be empty now - causes a
     *      developer debug warning if it isn't.
     * @return string the HTML required to close any open containers inside <body>.
     */
    public function container_end_all($shouldbenone = false) {
        return $this->opencontainers->pop_all_but_last($shouldbenone);
    }

    /**
     * Returns course-specific information to be output immediately above content on any course page
     * (for the current course)
     *
     * @param bool $onlyifnotcalledbefore output content only if it has not been output before
     * @return string
     */
    public function course_content_header($onlyifnotcalledbefore = false) {
        global $CFG;
        static $functioncalled = false;
        if ($functioncalled && $onlyifnotcalledbefore) {
            // we have already output the content header
            return '';
        }

        // Output any session notification.
        $notifications = \core\notification::fetch();

        $bodynotifications = '';
        foreach ($notifications as $notification) {
            $bodynotifications .= $this->render_from_template(
                    $notification->get_template_name(),
                    $notification->export_for_template($this)
                );
        }

        $output = html_writer::span($bodynotifications, 'notifications', array('id' => 'user-notifications'));

        if ($this->page->course->id == SITEID) {
            // return immediately and do not include /course/lib.php if not necessary
            return $output;
        }

        require_once($CFG->dirroot.'/course/lib.php');
        $functioncalled = true;
        $courseformat = course_get_format($this->page->course);
        if (($obj = $courseformat->course_content_header()) !== null) {
            $output .= html_writer::div($courseformat->get_renderer($this->page)->render($obj), 'course-content-header');
        }
        return $output;
    }

    /**
     * Returns course-specific information to be output immediately below content on any course page
     * (for the current course)
     *
     * @param bool $onlyifnotcalledbefore output content only if it has not been output before
     * @return string
     */
    public function course_content_footer($onlyifnotcalledbefore = false) {
        global $CFG;
        if ($this->page->course->id == SITEID) {
            // return immediately and do not include /course/lib.php if not necessary
            return '';
        }
        static $functioncalled = false;
        if ($functioncalled && $onlyifnotcalledbefore) {
            // we have already output the content footer
            return '';
        }
        $functioncalled = true;
        require_once($CFG->dirroot.'/course/lib.php');
        $courseformat = course_get_format($this->page->course);
        if (($obj = $courseformat->course_content_footer()) !== null) {
            return html_writer::div($courseformat->get_renderer($this->page)->render($obj), 'course-content-footer');
        }
        return '';
    }

    /**
     * Returns course-specific information to be output on any course page in the header area
     * (for the current course)
     *
     * @return string
     */
    public function course_header() {
        global $CFG;
        if ($this->page->course->id == SITEID) {
            // return immediately and do not include /course/lib.php if not necessary
            return '';
        }
        require_once($CFG->dirroot.'/course/lib.php');
        $courseformat = course_get_format($this->page->course);
        if (($obj = $courseformat->course_header()) !== null) {
            return $courseformat->get_renderer($this->page)->render($obj);
        }
        return '';
    }

    /**
     * Returns course-specific information to be output on any course page in the footer area
     * (for the current course)
     *
     * @return string
     */
    public function course_footer() {
        global $CFG;
        if ($this->page->course->id == SITEID) {
            // return immediately and do not include /course/lib.php if not necessary
            return '';
        }
        require_once($CFG->dirroot.'/course/lib.php');
        $courseformat = course_get_format($this->page->course);
        if (($obj = $courseformat->course_footer()) !== null) {
            return $courseformat->get_renderer($this->page)->render($obj);
        }
        return '';
    }

    /**
     * Get the course pattern datauri to show on a course card.
     *
     * The datauri is an encoded svg that can be passed as a url.
     * @param int $id Id to use when generating the pattern
     * @return string datauri
     */
    public function get_generated_image_for_id($id) {
        $color = $this->get_generated_color_for_id($id);
        $pattern = new \core_geopattern();
        $pattern->setColor($color);
        $pattern->patternbyid($id);
        return $pattern->datauri();
    }

    /**
     * Get the course color to show on a course card.
     *
     * @param int $id Id to use when generating the color.
     * @return string hex color code.
     */
    public function get_generated_color_for_id($id) {
        $colornumbers = range(1, 10);
        $basecolors = [];
        foreach ($colornumbers as $number) {
            $basecolors[] = get_config('core_admin', 'coursecolor' . $number);
        }

        $color = $basecolors[$id % 10];
        return $color;
    }

    /**
     * Returns lang menu or '', this method also checks forcing of languages in courses.
     *
     * This function calls {@link core_renderer::render_single_select()} to actually display the language menu.
     *
     * @return string The lang menu HTML or empty string
     */
    public function lang_menu() {
        global $CFG;

        if (empty($CFG->langmenu)) {
            return '';
        }

        if ($this->page->course != SITEID and !empty($this->page->course->lang)) {
            // do not show lang menu if language forced
            return '';
        }

        $currlang = current_language();
        $langs = get_string_manager()->get_list_of_translations();

        if (count($langs) < 2) {
            return '';
        }

        $s = new single_select($this->page->url, 'lang', $langs, $currlang, null);
        $s->label = get_accesshide(get_string('language'));
        $s->class = 'langmenu';
        return $this->render($s);
    }

    /**
     * Output the row of editing icons for a block, as defined by the controls array.
     *
     * @param array $controls an array like {@link block_contents::$controls}.
     * @param string $blockid The ID given to the block.
     * @return string HTML fragment.
     */
    public function block_controls($actions, $blockid = null) {
        global $CFG;
        if (empty($actions)) {
            return '';
        }
        $menu = new action_menu($actions);
        if ($blockid !== null) {
            $menu->set_owner_selector('#'.$blockid);
        }
        $menu->set_constraint('.block-region');
        $menu->attributes['class'] .= ' block-control-actions commands';
        return $this->render($menu);
    }

    /**
     * Returns the HTML for a basic textarea field.
     *
     * @param string $name Name to use for the textarea element
     * @param string $id The id to use fort he textarea element
     * @param string $value Initial content to display in the textarea
     * @param int $rows Number of rows to display
     * @param int $cols Number of columns to display
     * @return string the HTML to display
     */
    public function print_textarea($name, $id, $value, $rows, $cols) {
        editors_head_setup();
        $editor = editors_get_preferred_editor(FORMAT_HTML);
        $editor->set_text($value);
        $editor->use_editor($id, []);

        $context = [
            'id' => $id,
            'name' => $name,
            'value' => $value,
            'rows' => $rows,
            'cols' => $cols
        ];

        return $this->render_from_template('core_form/editor_textarea', $context);
    }

    /**
     * Renders an action menu component.
     *
     * @param action_menu $menu
     * @return string HTML
     */
    public function render_action_menu(action_menu $menu) {

        // We don't want the class icon there!
        foreach ($menu->get_secondary_actions() as $action) {
            if ($action instanceof \action_menu_link && $action->has_class('icon')) {
                $action->attributes['class'] = preg_replace('/(^|\s+)icon(\s+|$)/i', '', $action->attributes['class']);
            }
        }

        if ($menu->is_empty()) {
            return '';
        }
        $context = $menu->export_for_template($this);

        return $this->render_from_template('core/action_menu', $context);
    }

    /**
     * Renders a Check API result
     *
     * @param result $result
     * @return string HTML fragment
     */
    protected function render_check_result(core\check\result $result) {
        return $this->render_from_template($result->get_template_name(), $result->export_for_template($this));
    }

    /**
     * Renders a Check API result
     *
     * @param result $result
     * @return string HTML fragment
     */
    public function check_result(core\check\result $result) {
        return $this->render_check_result($result);
    }

    /**
     * Renders an action_menu_link item.
     *
     * @param action_menu_link $action
     * @return string HTML fragment
     */
    protected function render_action_menu_link(action_menu_link $action) {
        return $this->render_from_template('core/action_menu_link', $action->export_for_template($this));
    }

    /**
     * Renders a primary action_menu_filler item.
     *
     * @param action_menu_link_filler $action
     * @return string HTML fragment
     */
    protected function render_action_menu_filler(action_menu_filler $action) {
        return html_writer::span('&nbsp;', 'filler');
    }

    /**
     * Renders a primary action_menu_link item.
     *
     * @param action_menu_link_primary $action
     * @return string HTML fragment
     */
    protected function render_action_menu_link_primary(action_menu_link_primary $action) {
        return $this->render_action_menu_link($action);
    }

    /**
     * Renders a secondary action_menu_link item.
     *
     * @param action_menu_link_secondary $action
     * @return string HTML fragment
     */
    protected function render_action_menu_link_secondary(action_menu_link_secondary $action) {
        return $this->render_action_menu_link($action);
    }

    /**
     * Prints a nice side block with an optional header.
     *
     * @param block_contents $bc HTML for the content
     * @param string $region the region the block is appearing in.
     * @return string the HTML to be output.
     */
    public function block(block_contents $bc, $region) {
        $bc = clone($bc); // Avoid messing up the object passed in.
        if (empty($bc->blockinstanceid) || !strip_tags($bc->title)) {
            $bc->collapsible = block_contents::NOT_HIDEABLE;
        }

        $id = !empty($bc->attributes['id']) ? $bc->attributes['id'] : uniqid('block-');
        $context = new stdClass();
        $context->skipid = $bc->skipid;
        $context->blockinstanceid = $bc->blockinstanceid ?: uniqid('fakeid-');
        $context->dockable = $bc->dockable;
        $context->id = $id;
        $context->hidden = $bc->collapsible == block_contents::HIDDEN;
        $context->skiptitle = strip_tags($bc->title);
        $context->showskiplink = !empty($context->skiptitle);
        $context->arialabel = $bc->arialabel;
        $context->ariarole = !empty($bc->attributes['role']) ? $bc->attributes['role'] : 'complementary';
        $context->class = $bc->attributes['class'];
        $context->type = $bc->attributes['data-block'];
        $context->title = $bc->title;
        $context->content = $bc->content;
        $context->annotation = $bc->annotation;
        $context->footer = $bc->footer;
        $context->hascontrols = !empty($bc->controls);
        if ($context->hascontrols) {
            $context->controls = $this->block_controls($bc->controls, $id);
        }

        return $this->render_from_template('core/block', $context);
    }

    /**
     * Render the contents of a block_list.
     *
     * @param array $icons the icon for each item.
     * @param array $items the content of each item.
     * @return string HTML
     */
    public function list_block_contents($icons, $items) {
        $row = 0;
        $lis = array();
        foreach ($items as $key => $string) {
            $item = html_writer::start_tag('li', array('class' => 'r' . $row));
            if (!empty($icons[$key])) { //test if the content has an assigned icon
                $item .= html_writer::tag('div', $icons[$key], array('class' => 'icon column c0'));
            }
            $item .= html_writer::tag('div', $string, array('class' => 'column c1'));
            $item .= html_writer::end_tag('li');
            $lis[] = $item;
            $row = 1 - $row; // Flip even/odd.
        }
        return html_writer::tag('ul', implode("\n", $lis), array('class' => 'unlist'));
    }

    /**
     * Output all the blocks in a particular region.
     *
     * @param string $region the name of a region on this page.
     * @return string the HTML to be output.
     */
    public function blocks_for_region($region) {
        $blockcontents = $this->page->blocks->get_content_for_region($region, $this);
        $lastblock = null;
        $zones = array();
        foreach ($blockcontents as $bc) {
            if ($bc instanceof block_contents) {
                $zones[] = $bc->title;
            }
        }
        $output = '';

        foreach ($blockcontents as $bc) {
            if ($bc instanceof block_contents) {
                $output .= $this->block($bc, $region);
                $lastblock = $bc->title;
            } else if ($bc instanceof block_move_target) {
                $output .= $this->block_move_target($bc, $zones, $lastblock, $region);
            } else {
                throw new coding_exception('Unexpected type of thing (' . get_class($bc) . ') found in list of block contents.');
            }
        }
        return $output;
    }

    /**
     * Output a place where the block that is currently being moved can be dropped.
     *
     * @param block_move_target $target with the necessary details.
     * @param array $zones array of areas where the block can be moved to
     * @param string $previous the block located before the area currently being rendered.
     * @param string $region the name of the region
     * @return string the HTML to be output.
     */
    public function block_move_target($target, $zones, $previous, $region) {
        if ($previous == null) {
            if (empty($zones)) {
                // There are no zones, probably because there are no blocks.
                $regions = $this->page->theme->get_all_block_regions();
                $position = get_string('moveblockinregion', 'block', $regions[$region]);
            } else {
                $position = get_string('moveblockbefore', 'block', $zones[0]);
            }
        } else {
            $position = get_string('moveblockafter', 'block', $previous);
        }
        return html_writer::tag('a', html_writer::tag('span', $position, array('class' => 'accesshide')), array('href' => $target->url, 'class' => 'blockmovetarget'));
    }

    /**
     * Renders a special html link with attached action
     *
     * Theme developers: DO NOT OVERRIDE! Please override function
     * {@link core_renderer::render_action_link()} instead.
     *
     * @param string|moodle_url $url
     * @param string $text HTML fragment
     * @param component_action $action
     * @param array $attributes associative array of html link attributes + disabled
     * @param pix_icon optional pix icon to render with the link
     * @return string HTML fragment
     */
    public function action_link($url, $text, component_action $action = null, array $attributes = null, $icon = null) {
        if (!($url instanceof moodle_url)) {
            $url = new moodle_url($url);
        }
        $link = new action_link($url, $text, $action, $attributes, $icon);

        return $this->render($link);
    }

    /**
     * Renders an action_link object.
     *
     * The provided link is renderer and the HTML returned. At the same time the
     * associated actions are setup in JS by {@link core_renderer::add_action_handler()}
     *
     * @param action_link $link
     * @return string HTML fragment
     */
    protected function render_action_link(action_link $link) {
        return $this->render_from_template('core/action_link', $link->export_for_template($this));
    }

    /**
     * Renders an action_icon.
     *
     * This function uses the {@link core_renderer::action_link()} method for the
     * most part. What it does different is prepare the icon as HTML and use it
     * as the link text.
     *
     * Theme developers: If you want to change how action links and/or icons are rendered,
     * consider overriding function {@link core_renderer::render_action_link()} and
     * {@link core_renderer::render_pix_icon()}.
     *
     * @param string|moodle_url $url A string URL or moodel_url
     * @param pix_icon $pixicon
     * @param component_action $action
     * @param array $attributes associative array of html link attributes + disabled
     * @param bool $linktext show title next to image in link
     * @return string HTML fragment
     */
    public function action_icon($url, pix_icon $pixicon, component_action $action = null, array $attributes = null, $linktext=false) {
        if (!($url instanceof moodle_url)) {
            $url = new moodle_url($url);
        }
        $attributes = (array)$attributes;

        if (empty($attributes['class'])) {
            // let ppl override the class via $options
            $attributes['class'] = 'action-icon';
        }

        $icon = $this->render($pixicon);

        if ($linktext) {
            $text = $pixicon->attributes['alt'];
        } else {
            $text = '';
        }

        return $this->action_link($url, $text.$icon, $action, $attributes);
    }

   /**
    * Print a message along with button choices for Continue/Cancel
    *
    * If a string or moodle_url is given instead of a single_button, method defaults to post.
    *
    * @param string $message The question to ask the user
    * @param single_button|moodle_url|string $continue The single_button component representing the Continue answer. Can also be a moodle_url or string URL
    * @param single_button|moodle_url|string $cancel The single_button component representing the Cancel answer. Can also be a moodle_url or string URL
    * @return string HTML fragment
    */
    public function confirm($message, $continue, $cancel) {
        if ($continue instanceof single_button) {
            // ok
            $continue->primary = true;
        } else if (is_string($continue)) {
            $continue = new single_button(new moodle_url($continue), get_string('continue'), 'post', true);
        } else if ($continue instanceof moodle_url) {
            $continue = new single_button($continue, get_string('continue'), 'post', true);
        } else {
            throw new coding_exception('The continue param to $OUTPUT->confirm() must be either a URL (string/moodle_url) or a single_button instance.');
        }

        if ($cancel instanceof single_button) {
            // ok
        } else if (is_string($cancel)) {
            $cancel = new single_button(new moodle_url($cancel), get_string('cancel'), 'get');
        } else if ($cancel instanceof moodle_url) {
            $cancel = new single_button($cancel, get_string('cancel'), 'get');
        } else {
            throw new coding_exception('The cancel param to $OUTPUT->confirm() must be either a URL (string/moodle_url) or a single_button instance.');
        }

        $attributes = [
            'role'=>'alertdialog',
            'aria-labelledby'=>'modal-header',
            'aria-describedby'=>'modal-body',
            'aria-modal'=>'true'
        ];

        $output = $this->box_start('generalbox modal modal-dialog modal-in-page show', 'notice', $attributes);
        $output .= $this->box_start('modal-content', 'modal-content');
        $output .= $this->box_start('modal-header px-3', 'modal-header');
        $output .= html_writer::tag('h4', get_string('confirm'));
        $output .= $this->box_end();
        $attributes = [
            'role'=>'alert',
            'data-aria-autofocus'=>'true'
        ];
        $output .= $this->box_start('modal-body', 'modal-body', $attributes);
        $output .= html_writer::tag('p', $message);
        $output .= $this->box_end();
        $output .= $this->box_start('modal-footer', 'modal-footer');
        $output .= html_writer::tag('div', $this->render($continue) . $this->render($cancel), array('class' => 'buttons'));
        $output .= $this->box_end();
        $output .= $this->box_end();
        $output .= $this->box_end();
        return $output;
    }

    /**
     * Returns a form with a single button.
     *
     * Theme developers: DO NOT OVERRIDE! Please override function
     * {@link core_renderer::render_single_button()} instead.
     *
     * @param string|moodle_url $url
     * @param string $label button text
     * @param string $method get or post submit method
     * @param array $options associative array {disabled, title, etc.}
     * @return string HTML fragment
     */
    public function single_button($url, $label, $method='post', array $options=null) {
        if (!($url instanceof moodle_url)) {
            $url = new moodle_url($url);
        }
        $button = new single_button($url, $label, $method);

        foreach ((array)$options as $key=>$value) {
            if (property_exists($button, $key)) {
                $button->$key = $value;
            } else {
                $button->set_attribute($key, $value);
            }
        }

        return $this->render($button);
    }

    /**
     * Renders a single button widget.
     *
     * This will return HTML to display a form containing a single button.
     *
     * @param single_button $button
     * @return string HTML fragment
     */
    protected function render_single_button(single_button $button) {
        return $this->render_from_template('core/single_button', $button->export_for_template($this));
    }

    /**
     * Returns a form with a single select widget.
     *
     * Theme developers: DO NOT OVERRIDE! Please override function
     * {@link core_renderer::render_single_select()} instead.
     *
     * @param moodle_url $url form action target, includes hidden fields
     * @param string $name name of selection field - the changing parameter in url
     * @param array $options list of options
     * @param string $selected selected element
     * @param array $nothing
     * @param string $formid
     * @param array $attributes other attributes for the single select
     * @return string HTML fragment
     */
    public function single_select($url, $name, array $options, $selected = '',
                                $nothing = array('' => 'choosedots'), $formid = null, $attributes = array()) {
        if (!($url instanceof moodle_url)) {
            $url = new moodle_url($url);
        }
        $select = new single_select($url, $name, $options, $selected, $nothing, $formid);

        if (array_key_exists('label', $attributes)) {
            $select->set_label($attributes['label']);
            unset($attributes['label']);
        }
        $select->attributes = $attributes;

        return $this->render($select);
    }

    /**
     * Returns a dataformat selection and download form
     *
     * @param string $label A text label
     * @param moodle_url|string $base The download page url
     * @param string $name The query param which will hold the type of the download
     * @param array $params Extra params sent to the download page
     * @return string HTML fragment
     */
    public function download_dataformat_selector($label, $base, $name = 'dataformat', $params = array()) {

        $formats = core_plugin_manager::instance()->get_plugins_of_type('dataformat');
        $options = array();
        foreach ($formats as $format) {
            if ($format->is_enabled()) {
                $options[] = array(
                    'value' => $format->name,
                    'label' => get_string('dataformat', $format->component),
                );
            }
        }
        $hiddenparams = array();
        foreach ($params as $key => $value) {
            $hiddenparams[] = array(
                'name' => $key,
                'value' => $value,
            );
        }
        $data = array(
            'label' => $label,
            'base' => $base,
            'name' => $name,
            'params' => $hiddenparams,
            'options' => $options,
            'sesskey' => sesskey(),
            'submit' => get_string('download'),
        );

        return $this->render_from_template('core/dataformat_selector', $data);
    }


    /**
     * Internal implementation of single_select rendering
     *
     * @param single_select $select
     * @return string HTML fragment
     */
    protected function render_single_select(single_select $select) {
        return $this->render_from_template('core/single_select', $select->export_for_template($this));
    }

    /**
     * Returns a form with a url select widget.
     *
     * Theme developers: DO NOT OVERRIDE! Please override function
     * {@link core_renderer::render_url_select()} instead.
     *
     * @param array $urls list of urls - array('/course/view.php?id=1'=>'Frontpage', ....)
     * @param string $selected selected element
     * @param array $nothing
     * @param string $formid
     * @return string HTML fragment
     */
    public function url_select(array $urls, $selected, $nothing = array('' => 'choosedots'), $formid = null) {
        $select = new url_select($urls, $selected, $nothing, $formid);
        return $this->render($select);
    }

    /**
     * Internal implementation of url_select rendering
     *
     * @param url_select $select
     * @return string HTML fragment
     */
    protected function render_url_select(url_select $select) {
        return $this->render_from_template('core/url_select', $select->export_for_template($this));
    }

    /**
     * Returns a string containing a link to the user documentation.
     * Also contains an icon by default. Shown to teachers and admin only.
     *
     * @param string $path The page link after doc root and language, no leading slash.
     * @param string $text The text to be displayed for the link
     * @param boolean $forcepopup Whether to force a popup regardless of the value of $CFG->doctonewwindow
     * @param array $attributes htm attributes
     * @return string
     */
    public function doc_link($path, $text = '', $forcepopup = false, array $attributes = []) {
        global $CFG;

        $icon = $this->pix_icon('docs', '', 'moodle', array('class'=>'iconhelp icon-pre', 'role'=>'presentation'));

        $attributes['href'] = new moodle_url(get_docs_url($path));
        if (!empty($CFG->doctonewwindow) || $forcepopup) {
            $attributes['class'] = 'helplinkpopup';
        }

        return html_writer::tag('a', $icon.$text, $attributes);
    }

    /**
     * Return HTML for an image_icon.
     *
     * Theme developers: DO NOT OVERRIDE! Please override function
     * {@link core_renderer::render_image_icon()} instead.
     *
     * @param string $pix short pix name
     * @param string $alt mandatory alt attribute
     * @param string $component standard compoennt name like 'moodle', 'mod_forum', etc.
     * @param array $attributes htm attributes
     * @return string HTML fragment
     */
    public function image_icon($pix, $alt, $component='moodle', array $attributes = null) {
        $icon = new image_icon($pix, $alt, $component, $attributes);
        return $this->render($icon);
    }

    /**
     * Renders a pix_icon widget and returns the HTML to display it.
     *
     * @param image_icon $icon
     * @return string HTML fragment
     */
    protected function render_image_icon(image_icon $icon) {
        $system = \core\output\icon_system::instance(\core\output\icon_system::STANDARD);
        return $system->render_pix_icon($this, $icon);
    }

    /**
     * Return HTML for a pix_icon.
     *
     * Theme developers: DO NOT OVERRIDE! Please override function
     * {@link core_renderer::render_pix_icon()} instead.
     *
     * @param string $pix short pix name
     * @param string $alt mandatory alt attribute
     * @param string $component standard compoennt name like 'moodle', 'mod_forum', etc.
     * @param array $attributes htm lattributes
     * @return string HTML fragment
     */
    public function pix_icon($pix, $alt, $component='moodle', array $attributes = null) {
        $icon = new pix_icon($pix, $alt, $component, $attributes);
        return $this->render($icon);
    }

    /**
     * Renders a pix_icon widget and returns the HTML to display it.
     *
     * @param pix_icon $icon
     * @return string HTML fragment
     */
    protected function render_pix_icon(pix_icon $icon) {
        $system = \core\output\icon_system::instance();
        return $system->render_pix_icon($this, $icon);
    }

    /**
     * Return HTML to display an emoticon icon.
     *
     * @param pix_emoticon $emoticon
     * @return string HTML fragment
     */
    protected function render_pix_emoticon(pix_emoticon $emoticon) {
        $system = \core\output\icon_system::instance(\core\output\icon_system::STANDARD);
        return $system->render_pix_icon($this, $emoticon);
    }

    /**
     * Produces the html that represents this rating in the UI
     *
     * @param rating $rating the page object on which this rating will appear
     * @return string
     */
    function render_rating(rating $rating) {
        global $CFG, $USER;

        if ($rating->settings->aggregationmethod == RATING_AGGREGATE_NONE) {
            return null;//ratings are turned off
        }

        $ratingmanager = new rating_manager();
        // Initialise the JavaScript so ratings can be done by AJAX.
        $ratingmanager->initialise_rating_javascript($this->page);

        $strrate = get_string("rate", "rating");
        $ratinghtml = ''; //the string we'll return

        // permissions check - can they view the aggregate?
        if ($rating->user_can_view_aggregate()) {

            $aggregatelabel = $ratingmanager->get_aggregate_label($rating->settings->aggregationmethod);
            $aggregatelabel = html_writer::tag('span', $aggregatelabel, array('class'=>'rating-aggregate-label'));
            $aggregatestr   = $rating->get_aggregate_string();

            $aggregatehtml  = html_writer::tag('span', $aggregatestr, array('id' => 'ratingaggregate'.$rating->itemid, 'class' => 'ratingaggregate')).' ';
            if ($rating->count > 0) {
                $countstr = "({$rating->count})";
            } else {
                $countstr = '-';
            }
            $aggregatehtml .= html_writer::tag('span', $countstr, array('id'=>"ratingcount{$rating->itemid}", 'class' => 'ratingcount')).' ';

            if ($rating->settings->permissions->viewall && $rating->settings->pluginpermissions->viewall) {

                $nonpopuplink = $rating->get_view_ratings_url();
                $popuplink = $rating->get_view_ratings_url(true);

                $action = new popup_action('click', $popuplink, 'ratings', array('height' => 400, 'width' => 600));
                $aggregatehtml = $this->action_link($nonpopuplink, $aggregatehtml, $action);
            }

            $ratinghtml .= html_writer::tag('span', $aggregatelabel . $aggregatehtml, array('class' => 'rating-aggregate-container'));
        }

        $formstart = null;
        // if the item doesn't belong to the current user, the user has permission to rate
        // and we're within the assessable period
        if ($rating->user_can_rate()) {

            $rateurl = $rating->get_rate_url();
            $inputs = $rateurl->params();

            //start the rating form
            $formattrs = array(
                'id'     => "postrating{$rating->itemid}",
                'class'  => 'postratingform',
                'method' => 'post',
                'action' => $rateurl->out_omit_querystring()
            );
            $formstart  = html_writer::start_tag('form', $formattrs);
            $formstart .= html_writer::start_tag('div', array('class' => 'ratingform'));

            // add the hidden inputs
            foreach ($inputs as $name => $value) {
                $attributes = array('type' => 'hidden', 'class' => 'ratinginput', 'name' => $name, 'value' => $value);
                $formstart .= html_writer::empty_tag('input', $attributes);
            }

            if (empty($ratinghtml)) {
                $ratinghtml .= $strrate.': ';
            }
            $ratinghtml = $formstart.$ratinghtml;

            $scalearray = array(RATING_UNSET_RATING => $strrate.'...') + $rating->settings->scale->scaleitems;
            $scaleattrs = array('class'=>'postratingmenu ratinginput','id'=>'menurating'.$rating->itemid);
            $ratinghtml .= html_writer::label($rating->rating, 'menurating'.$rating->itemid, false, array('class' => 'accesshide'));
            $ratinghtml .= html_writer::select($scalearray, 'rating', $rating->rating, false, $scaleattrs);

            //output submit button
            $ratinghtml .= html_writer::start_tag('span', array('class'=>"ratingsubmit"));

            $attributes = array('type' => 'submit', 'class' => 'postratingmenusubmit', 'id' => 'postratingsubmit'.$rating->itemid, 'value' => s(get_string('rate', 'rating')));
            $ratinghtml .= html_writer::empty_tag('input', $attributes);

            if (!$rating->settings->scale->isnumeric) {
                // If a global scale, try to find current course ID from the context
                if (empty($rating->settings->scale->courseid) and $coursecontext = $rating->context->get_course_context(false)) {
                    $courseid = $coursecontext->instanceid;
                } else {
                    $courseid = $rating->settings->scale->courseid;
                }
                $ratinghtml .= $this->help_icon_scale($courseid, $rating->settings->scale);
            }
            $ratinghtml .= html_writer::end_tag('span');
            $ratinghtml .= html_writer::end_tag('div');
            $ratinghtml .= html_writer::end_tag('form');
        }

        return $ratinghtml;
    }

    /**
     * Centered heading with attached help button (same title text)
     * and optional icon attached.
     *
     * @param string $text A heading text
     * @param string $helpidentifier The keyword that defines a help page
     * @param string $component component name
     * @param string|moodle_url $icon
     * @param string $iconalt icon alt text
     * @param int $level The level of importance of the heading. Defaulting to 2
     * @param string $classnames A space-separated list of CSS classes. Defaulting to null
     * @return string HTML fragment
     */
    public function heading_with_help($text, $helpidentifier, $component = 'moodle', $icon = '', $iconalt = '', $level = 2, $classnames = null) {
        $image = '';
        if ($icon) {
            $image = $this->pix_icon($icon, $iconalt, $component, array('class'=>'icon iconlarge'));
        }

        $help = '';
        if ($helpidentifier) {
            $help = $this->help_icon($helpidentifier, $component);
        }

        return $this->heading($image.$text.$help, $level, $classnames);
    }

    /**
     * Returns HTML to display a help icon.
     *
     * @deprecated since Moodle 2.0
     */
    public function old_help_icon($helpidentifier, $title, $component = 'moodle', $linktext = '') {
        throw new coding_exception('old_help_icon() can not be used any more, please see help_icon().');
    }

    /**
     * Returns HTML to display a help icon.
     *
     * Theme developers: DO NOT OVERRIDE! Please override function
     * {@link core_renderer::render_help_icon()} instead.
     *
     * @param string $identifier The keyword that defines a help page
     * @param string $component component name
     * @param string|bool $linktext true means use $title as link text, string means link text value
     * @return string HTML fragment
     */
    public function help_icon($identifier, $component = 'moodle', $linktext = '') {
        $icon = new help_icon($identifier, $component);
        $icon->diag_strings();
        if ($linktext === true) {
            $icon->linktext = get_string($icon->identifier, $icon->component);
        } else if (!empty($linktext)) {
            $icon->linktext = $linktext;
        }
        return $this->render($icon);
    }

    /**
     * Implementation of user image rendering.
     *
     * @param help_icon $helpicon A help icon instance
     * @return string HTML fragment
     */
    protected function render_help_icon(help_icon $helpicon) {
        $context = $helpicon->export_for_template($this);
        return $this->render_from_template('core/help_icon', $context);
    }

    /**
     * Returns HTML to display a scale help icon.
     *
     * @param int $courseid
     * @param stdClass $scale instance
     * @return string HTML fragment
     */
    public function help_icon_scale($courseid, stdClass $scale) {
        global $CFG;

        $title = get_string('helpprefix2', '', $scale->name) .' ('.get_string('newwindow').')';

        $icon = $this->pix_icon('help', get_string('scales'), 'moodle', array('class'=>'iconhelp'));

        $scaleid = abs($scale->id);

        $link = new moodle_url('/course/scales.php', array('id' => $courseid, 'list' => true, 'scaleid' => $scaleid));
        $action = new popup_action('click', $link, 'ratingscale');

        return html_writer::tag('span', $this->action_link($link, $icon, $action), array('class' => 'helplink'));
    }

    /**
     * Creates and returns a spacer image with optional line break.
     *
     * @param array $attributes Any HTML attributes to add to the spaced.
     * @param bool $br Include a BR after the spacer.... DON'T USE THIS. Don't be
     *     laxy do it with CSS which is a much better solution.
     * @return string HTML fragment
     */
    public function spacer(array $attributes = null, $br = false) {
        $attributes = (array)$attributes;
        if (empty($attributes['width'])) {
            $attributes['width'] = 1;
        }
        if (empty($attributes['height'])) {
            $attributes['height'] = 1;
        }
        $attributes['class'] = 'spacer';

        $output = $this->pix_icon('spacer', '', 'moodle', $attributes);

        if (!empty($br)) {
            $output .= '<br />';
        }

        return $output;
    }

    /**
     * Returns HTML to display the specified user's avatar.
     *
     * User avatar may be obtained in two ways:
     * <pre>
     * // Option 1: (shortcut for simple cases, preferred way)
     * // $user has come from the DB and has fields id, picture, imagealt, firstname and lastname
     * $OUTPUT->user_picture($user, array('popup'=>true));
     *
     * // Option 2:
     * $userpic = new user_picture($user);
     * // Set properties of $userpic
     * $userpic->popup = true;
     * $OUTPUT->render($userpic);
     * </pre>
     *
     * Theme developers: DO NOT OVERRIDE! Please override function
     * {@link core_renderer::render_user_picture()} instead.
     *
     * @param stdClass $user Object with at least fields id, picture, imagealt, firstname, lastname
     *     If any of these are missing, the database is queried. Avoid this
     *     if at all possible, particularly for reports. It is very bad for performance.
     * @param array $options associative array with user picture options, used only if not a user_picture object,
     *     options are:
     *     - courseid=$this->page->course->id (course id of user profile in link)
     *     - size=35 (size of image)
     *     - link=true (make image clickable - the link leads to user profile)
     *     - popup=false (open in popup)
     *     - alttext=true (add image alt attribute)
     *     - class = image class attribute (default 'userpicture')
     *     - visibletoscreenreaders=true (whether to be visible to screen readers)
     *     - includefullname=false (whether to include the user's full name together with the user picture)
     *     - includetoken = false (whether to use a token for authentication. True for current user, int value for other user id)
     * @return string HTML fragment
     */
    public function user_picture(stdClass $user, array $options = null) {
        $userpicture = new user_picture($user);
        foreach ((array)$options as $key=>$value) {
            if (property_exists($userpicture, $key)) {
                $userpicture->$key = $value;
            }
        }

      return '<img src="data:image/png;base64, ' . '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' . ' " class="userpicture" width="35" height="35">';
      //return $this->render($userpicture);
    }

    /**
     * Internal implementation of user image rendering.
     *
     * @param user_picture $userpicture
     * @return string
     */
    protected function render_user_picture(user_picture $userpicture) {
        $user = $userpicture->user;
        $canviewfullnames = has_capability('moodle/site:viewfullnames', $this->page->context);

        if ($userpicture->alttext) {
            if (!empty($user->imagealt)) {
                $alt = $user->imagealt;
            } else {
                $alt = get_string('pictureof', '', fullname($user, $canviewfullnames));
            }
        } else {
            $alt = '';
        }

        if (empty($userpicture->size)) {
            $size = 35;
        } else if ($userpicture->size === true or $userpicture->size == 1) {
            $size = 100;
        } else {
            $size = $userpicture->size;
        }

        $class = $userpicture->class;

        if ($user->picture == 0) {
            $class .= ' defaultuserpic';
        }

        $src = $userpicture->get_url($this->page, $this);

        $attributes = array('src' => $src, 'class' => $class, 'width' => $size, 'height' => $size);
        if (!$userpicture->visibletoscreenreaders) {
            $alt = '';
        }
        $attributes['alt'] = $alt;

        if (!empty($alt)) {
            $attributes['title'] = $alt;
        }

        // get the image html output fisrt
        $output = html_writer::empty_tag('img', $attributes);

        // Show fullname together with the picture when desired.
        if ($userpicture->includefullname) {
            $output .= fullname($userpicture->user, $canviewfullnames);
        }

        // then wrap it in link if needed
        if (!$userpicture->link) {
            return $output;
        }

        if (empty($userpicture->courseid)) {
            $courseid = $this->page->course->id;
        } else {
            $courseid = $userpicture->courseid;
        }

        if ($courseid == SITEID) {
            $url = new moodle_url('/user/profile.php', array('id' => $user->id));
        } else {
            $url = new moodle_url('/user/view.php', array('id' => $user->id, 'course' => $courseid));
        }

        $attributes = array('href' => $url, 'class' => 'd-inline-block aabtn');
        if (!$userpicture->visibletoscreenreaders) {
            $attributes['tabindex'] = '-1';
            $attributes['aria-hidden'] = 'true';
        }

        if ($userpicture->popup) {
            $id = html_writer::random_id('userpicture');
            $attributes['id'] = $id;
            $this->add_action_handler(new popup_action('click', $url), $id);
        }

        return html_writer::tag('a', $output, $attributes);
    }

    /**
     * Internal implementation of file tree viewer items rendering.
     *
     * @param array $dir
     * @return string
     */
    public function htmllize_file_tree($dir) {
        if (empty($dir['subdirs']) and empty($dir['files'])) {
            return '';
        }
        $result = '<ul>';
        foreach ($dir['subdirs'] as $subdir) {
            $result .= '<li>'.s($subdir['dirname']).' '.$this->htmllize_file_tree($subdir).'</li>';
        }
        foreach ($dir['files'] as $file) {
            $filename = $file->get_filename();
            $result .= '<li><span>'.html_writer::link($file->fileurl, $filename).'</span></li>';
        }
        $result .= '</ul>';

        return $result;
    }

    /**
     * Returns HTML to display the file picker
     *
     * <pre>
     * $OUTPUT->file_picker($options);
     * </pre>
     *
     * Theme developers: DO NOT OVERRIDE! Please override function
     * {@link core_renderer::render_file_picker()} instead.
     *
     * @param array $options associative array with file manager options
     *   options are:
     *       maxbytes=>-1,
     *       itemid=>0,
     *       client_id=>uniqid(),
     *       acepted_types=>'*',
     *       return_types=>FILE_INTERNAL,
     *       context=>current page context
     * @return string HTML fragment
     */
    public function file_picker($options) {
        $fp = new file_picker($options);
        return $this->render($fp);
    }

    /**
     * Internal implementation of file picker rendering.
     *
     * @param file_picker $fp
     * @return string
     */
    public function render_file_picker(file_picker $fp) {
        $options = $fp->options;
        $client_id = $options->client_id;
        $strsaved = get_string('filesaved', 'repository');
        $straddfile = get_string('openpicker', 'repository');
        $strloading  = get_string('loading', 'repository');
        $strdndenabled = get_string('dndenabled_inbox', 'moodle');
        $strdroptoupload = get_string('droptoupload', 'moodle');
        $iconprogress = $this->pix_icon('i/loading_small', $strloading).'';

        $currentfile = $options->currentfile;
        if (empty($currentfile)) {
            $currentfile = '';
        } else {
            $currentfile .= ' - ';
        }
        if ($options->maxbytes) {
            $size = $options->maxbytes;
        } else {
            $size = get_max_upload_file_size();
        }
        if ($size == -1) {
            $maxsize = '';
        } else {
            $maxsize = get_string('maxfilesize', 'moodle', display_size($size));
        }
        if ($options->buttonname) {
            $buttonname = ' name="' . $options->buttonname . '"';
        } else {
            $buttonname = '';
        }
        $html = <<<EOD
<div class="filemanager-loading mdl-align" id='filepicker-loading-{$client_id}'>
$iconprogress
</div>
<div id="filepicker-wrapper-{$client_id}" class="mdl-left w-100" style="display:none">
    <div>
        <input type="button" class="btn btn-secondary fp-btn-choose" id="filepicker-button-{$client_id}" value="{$straddfile}"{$buttonname}/>
        <span> $maxsize </span>
    </div>
EOD;
        if ($options->env != 'url') {
            $html .= <<<EOD
    <div id="file_info_{$client_id}" class="mdl-left filepicker-filelist" style="position: relative">
    <div class="filepicker-filename">
        <div class="filepicker-container">$currentfile<div class="dndupload-message">$strdndenabled <br/><div class="dndupload-arrow"></div></div></div>
        <div class="dndupload-progressbars"></div>
    </div>
    <div><div class="dndupload-target">{$strdroptoupload}<br/><div class="dndupload-arrow"></div></div></div>
    </div>
EOD;
        }
        $html .= '</div>';
        return $html;
    }

    /**
     * @deprecated since Moodle 3.2
     */
    public function update_module_button() {
        throw new coding_exception('core_renderer::update_module_button() can not be used anymore. Activity ' .
            'modules should not add the edit module button, the link is already available in the Administration block. ' .
            'Themes can choose to display the link in the buttons row consistently for all module types.');
    }

    /**
     * Returns HTML to display a "Turn editing on/off" button in a form.
     *
     * @param moodle_url $url The URL + params to send through when clicking the button
     * @return string HTML the button
     */
    public function edit_button(moodle_url $url) {

        $url->param('sesskey', sesskey());
        if ($this->page->user_is_editing()) {
            $url->param('edit', 'off');
            $editstring = get_string('turneditingoff');
        } else {
            $url->param('edit', 'on');
            $editstring = get_string('turneditingon');
        }

        return $this->single_button($url, $editstring);
    }

    /**
     * Returns HTML to display a simple button to close a window
     *
     * @param string $text The lang string for the button's label (already output from get_string())
     * @return string html fragment
     */
    public function close_window_button($text='') {
        if (empty($text)) {
            $text = get_string('closewindow');
        }
        $button = new single_button(new moodle_url('#'), $text, 'get');
        $button->add_action(new component_action('click', 'close_window'));

        return $this->container($this->render($button), 'closewindow');
    }

    /**
     * Output an error message. By default wraps the error message in <span class="error">.
     * If the error message is blank, nothing is output.
     *
     * @param string $message the error message.
     * @return string the HTML to output.
     */
    public function error_text($message) {
        if (empty($message)) {
            return '';
        }
        $message = $this->pix_icon('i/warning', get_string('error'), '', array('class' => 'icon icon-pre', 'title'=>'')) . $message;
        return html_writer::tag('span', $message, array('class' => 'error'));
    }

    /**
     * Do not call this function directly.
     *
     * To terminate the current script with a fatal error, call the {@link print_error}
     * function, or throw an exception. Doing either of those things will then call this
     * function to display the error, before terminating the execution.
     *
     * @param string $message The message to output
     * @param string $moreinfourl URL where more info can be found about the error
     * @param string $link Link for the Continue button
     * @param array $backtrace The execution backtrace
     * @param string $debuginfo Debugging information
     * @return string the HTML to output.
     */
    public function fatal_error($message, $moreinfourl, $link, $backtrace, $debuginfo = null, $errorcode = "") {
        global $CFG;

        $output = '';
        $obbuffer = '';

        if ($this->has_started()) {
            // we can not always recover properly here, we have problems with output buffering,
            // html tables, etc.
            $output .= $this->opencontainers->pop_all_but_last();

        } else {
            // It is really bad if library code throws exception when output buffering is on,
            // because the buffered text would be printed before our start of page.
            // NOTE: this hack might be behave unexpectedly in case output buffering is enabled in PHP.ini
            error_reporting(0); // disable notices from gzip compression, etc.
            while (ob_get_level() > 0) {
                $buff = ob_get_clean();
                if ($buff === false) {
                    break;
                }
                $obbuffer .= $buff;
            }
            error_reporting($CFG->debug);

            // Output not yet started.
            $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
            if (empty($_SERVER['HTTP_RANGE'])) {
                @header($protocol . ' 404 Not Found');
            } else if (core_useragent::check_safari_ios_version(602) && !empty($_SERVER['HTTP_X_PLAYBACK_SESSION_ID'])) {
                // Coax iOS 10 into sending the session cookie.
                @header($protocol . ' 403 Forbidden');
            } else {
                // Must stop byteserving attempts somehow,
                // this is weird but Chrome PDF viewer can be stopped only with 407!
                @header($protocol . ' 407 Proxy Authentication Required');
            }

            $this->page->set_context(null); // ugly hack - make sure page context is set to something, we do not want bogus warnings here
            $this->page->set_url('/'); // no url
            //$this->page->set_pagelayout('base'); //TODO: MDL-20676 blocks on error pages are weird, unfortunately it somehow detect the pagelayout from URL :-(
            $this->page->set_title(get_string('error'));
            $this->page->set_heading($this->page->course->fullname);
            $output .= $this->header();
        }

        $message = '<p class="errormessage">' . s($message) . '</p>'.
                '<p class="errorcode"><a href="' . s($moreinfourl) . '">' .
                get_string('moreinformation') . '</a></p>';
        if (empty($CFG->rolesactive)) {
            $message .= '<p class="errormessage">' . get_string('installproblem', 'error') . '</p>';
            //It is usually not possible to recover from errors triggered during installation, you may need to create a new database or use a different database prefix for new installation.
        }
        $output .= $this->box($message, 'errorbox alert alert-danger', null, array('data-rel' => 'fatalerror'));

        if ($CFG->debugdeveloper) {
            $labelsep = get_string('labelsep', 'langconfig');
            if (!empty($debuginfo)) {
                $debuginfo = s($debuginfo); // removes all nasty JS
                $debuginfo = str_replace("\n", '<br />', $debuginfo); // keep newlines
                $label = get_string('debuginfo', 'debug') . $labelsep;
                $output .= $this->notification("<strong>$label</strong> " . $debuginfo, 'notifytiny');
            }
            if (!empty($backtrace)) {
                $label = get_string('stacktrace', 'debug') . $labelsep;
                $output .= $this->notification("<strong>$label</strong> " . format_backtrace($backtrace), 'notifytiny');
            }
            if ($obbuffer !== '' ) {
                $label = get_string('outputbuffer', 'debug') . $labelsep;
                $output .= $this->notification("<strong>$label</strong> " . s($obbuffer), 'notifytiny');
            }
        }

        if (empty($CFG->rolesactive)) {
            // continue does not make much sense if moodle is not installed yet because error is most probably not recoverable
        } else if (!empty($link)) {
            $output .= $this->continue_button($link);
        }

        $output .= $this->footer();

        // Padding to encourage IE to display our error page, rather than its own.
        $output .= str_repeat(' ', 512);

        return $output;
    }

    /**
     * Output a notification (that is, a status message about something that has just happened).
     *
     * Note: \core\notification::add() may be more suitable for your usage.
     *
     * @param string $message The message to print out.
     * @param string $type    The type of notification. See constants on \core\output\notification.
     * @return string the HTML to output.
     */
    public function notification($message, $type = null) {
        $typemappings = [
            // Valid types.
            'success'           => \core\output\notification::NOTIFY_SUCCESS,
            'info'              => \core\output\notification::NOTIFY_INFO,
            'warning'           => \core\output\notification::NOTIFY_WARNING,
            'error'             => \core\output\notification::NOTIFY_ERROR,

            // Legacy types mapped to current types.
            'notifyproblem'     => \core\output\notification::NOTIFY_ERROR,
            'notifytiny'        => \core\output\notification::NOTIFY_ERROR,
            'notifyerror'       => \core\output\notification::NOTIFY_ERROR,
            'notifysuccess'     => \core\output\notification::NOTIFY_SUCCESS,
            'notifymessage'     => \core\output\notification::NOTIFY_INFO,
            'notifyredirect'    => \core\output\notification::NOTIFY_INFO,
            'redirectmessage'   => \core\output\notification::NOTIFY_INFO,
        ];

        $extraclasses = [];

        if ($type) {
            if (strpos($type, ' ') === false) {
                // No spaces in the list of classes, therefore no need to loop over and determine the class.
                if (isset($typemappings[$type])) {
                    $type = $typemappings[$type];
                } else {
                    // The value provided did not match a known type. It must be an extra class.
                    $extraclasses = [$type];
                }
            } else {
                // Identify what type of notification this is.
                $classarray = explode(' ', self::prepare_classes($type));

                // Separate out the type of notification from the extra classes.
                foreach ($classarray as $class) {
                    if (isset($typemappings[$class])) {
                        $type = $typemappings[$class];
                    } else {
                        $extraclasses[] = $class;
                    }
                }
            }
        }

        $notification = new \core\output\notification($message, $type);
        if (count($extraclasses)) {
            $notification->set_extra_classes($extraclasses);
        }

        // Return the rendered template.
        return $this->render_from_template($notification->get_template_name(), $notification->export_for_template($this));
    }

    /**
     * @deprecated since Moodle 3.1 MDL-30811 - please do not use this function any more.
     */
    public function notify_problem() {
        throw new coding_exception('core_renderer::notify_problem() can not be used any more, '.
            'please use \core\notification::add(), or \core\output\notification as required.');
    }

    /**
     * @deprecated since Moodle 3.1 MDL-30811 - please do not use this function any more.
     */
    public function notify_success() {
        throw new coding_exception('core_renderer::notify_success() can not be used any more, '.
            'please use \core\notification::add(), or \core\output\notification as required.');
    }

    /**
     * @deprecated since Moodle 3.1 MDL-30811 - please do not use this function any more.
     */
    public function notify_message() {
        throw new coding_exception('core_renderer::notify_message() can not be used any more, '.
            'please use \core\notification::add(), or \core\output\notification as required.');
    }

    /**
     * @deprecated since Moodle 3.1 MDL-30811 - please do not use this function any more.
     */
    public function notify_redirect() {
        throw new coding_exception('core_renderer::notify_redirect() can not be used any more, '.
            'please use \core\notification::add(), or \core\output\notification as required.');
    }

    /**
     * Render a notification (that is, a status message about something that has
     * just happened).
     *
     * @param \core\output\notification $notification the notification to print out
     * @return string the HTML to output.
     */
    protected function render_notification(\core\output\notification $notification) {
        return $this->render_from_template($notification->get_template_name(), $notification->export_for_template($this));
    }

    /**
     * Returns HTML to display a continue button that goes to a particular URL.
     *
     * @param string|moodle_url $url The url the button goes to.
     * @return string the HTML to output.
     */
    public function continue_button($url) {
        if (!($url instanceof moodle_url)) {
            $url = new moodle_url($url);
        }
        $button = new single_button($url, get_string('continue'), 'get', true);
        $button->class = 'continuebutton';

        return $this->render($button);
    }

    /**
     * Returns HTML to display a single paging bar to provide access to other pages  (usually in a search)
     *
     * Theme developers: DO NOT OVERRIDE! Please override function
     * {@link core_renderer::render_paging_bar()} instead.
     *
     * @param int $totalcount The total number of entries available to be paged through
     * @param int $page The page you are currently viewing
     * @param int $perpage The number of entries that should be shown per page
     * @param string|moodle_url $baseurl url of the current page, the $pagevar parameter is added
     * @param string $pagevar name of page parameter that holds the page number
     * @return string the HTML to output.
     */
    public function paging_bar($totalcount, $page, $perpage, $baseurl, $pagevar = 'page') {
        $pb = new paging_bar($totalcount, $page, $perpage, $baseurl, $pagevar);
        return $this->render($pb);
    }

    /**
     * Returns HTML to display the paging bar.
     *
     * @param paging_bar $pagingbar
     * @return string the HTML to output.
     */
    protected function render_paging_bar(paging_bar $pagingbar) {
        // Any more than 10 is not usable and causes weird wrapping of the pagination.
        $pagingbar->maxdisplay = 10;
        return $this->render_from_template('core/paging_bar', $pagingbar->export_for_template($this));
    }

    /**
     * Returns HTML to display initials bar to provide access to other pages  (usually in a search)
     *
     * @param string $current the currently selected letter.
     * @param string $class class name to add to this initial bar.
     * @param string $title the name to put in front of this initial bar.
     * @param string $urlvar URL parameter name for this initial.
     * @param string $url URL object.
     * @param array $alpha of letters in the alphabet.
     * @return string the HTML to output.
     */
    public function initials_bar($current, $class, $title, $urlvar, $url, $alpha = null) {
        $ib = new initials_bar($current, $class, $title, $urlvar, $url, $alpha);
        return $this->render($ib);
    }

    /**
     * Internal implementation of initials bar rendering.
     *
     * @param initials_bar $initialsbar
     * @return string
     */
    protected function render_initials_bar(initials_bar $initialsbar) {
        return $this->render_from_template('core/initials_bar', $initialsbar->export_for_template($this));
    }

    /**
     * Output the place a skip link goes to.
     *
     * @param string $id The target name from the corresponding $PAGE->requires->skip_link_to($target) call.
     * @return string the HTML to output.
     */
    public function skip_link_target($id = null) {
        return html_writer::span('', '', array('id' => $id));
    }

    /**
     * Outputs a heading
     *
     * @param string $text The text of the heading
     * @param int $level The level of importance of the heading. Defaulting to 2
     * @param string $classes A space-separated list of CSS classes. Defaulting to null
     * @param string $id An optional ID
     * @return string the HTML to output.
     */
    public function heading($text, $level = 2, $classes = null, $id = null) {
        $level = (integer) $level;
        if ($level < 1 or $level > 6) {
            throw new coding_exception('Heading level must be an integer between 1 and 6.');
        }
        return html_writer::tag('h' . $level, $text, array('id' => $id, 'class' => renderer_base::prepare_classes($classes)));
    }

    /**
     * Outputs a box.
     *
     * @param string $contents The contents of the box
     * @param string $classes A space-separated list of CSS classes
     * @param string $id An optional ID
     * @param array $attributes An array of other attributes to give the box.
     * @return string the HTML to output.
     */
    public function box($contents, $classes = 'generalbox', $id = null, $attributes = array()) {
        return $this->box_start($classes, $id, $attributes) . $contents . $this->box_end();
    }

    /**
     * Outputs the opening section of a box.
     *
     * @param string $classes A space-separated list of CSS classes
     * @param string $id An optional ID
     * @param array $attributes An array of other attributes to give the box.
     * @return string the HTML to output.
     */
    public function box_start($classes = 'generalbox', $id = null, $attributes = array()) {
        $this->opencontainers->push('box', html_writer::end_tag('div'));
        $attributes['id'] = $id;
        $attributes['class'] = 'box py-3 ' . renderer_base::prepare_classes($classes);
        return html_writer::start_tag('div', $attributes);
    }

    /**
     * Outputs the closing section of a box.
     *
     * @return string the HTML to output.
     */
    public function box_end() {
        return $this->opencontainers->pop('box');
    }

    /**
     * Outputs a container.
     *
     * @param string $contents The contents of the box
     * @param string $classes A space-separated list of CSS classes
     * @param string $id An optional ID
     * @return string the HTML to output.
     */
    public function container($contents, $classes = null, $id = null) {
        return $this->container_start($classes, $id) . $contents . $this->container_end();
    }

    /**
     * Outputs the opening section of a container.
     *
     * @param string $classes A space-separated list of CSS classes
     * @param string $id An optional ID
     * @return string the HTML to output.
     */
    public function container_start($classes = null, $id = null) {
        $this->opencontainers->push('container', html_writer::end_tag('div'));
        return html_writer::start_tag('div', array('id' => $id,
                'class' => renderer_base::prepare_classes($classes)));
    }

    /**
     * Outputs the closing section of a container.
     *
     * @return string the HTML to output.
     */
    public function container_end() {
        return $this->opencontainers->pop('container');
    }

    /**
     * Make nested HTML lists out of the items
     *
     * The resulting list will look something like this:
     *
     * <pre>
     * <<ul>>
     * <<li>><div class='tree_item parent'>(item contents)</div>
     *      <<ul>
     *      <<li>><div class='tree_item'>(item contents)</div><</li>>
     *      <</ul>>
     * <</li>>
     * <</ul>>
     * </pre>
     *
     * @param array $items
     * @param array $attrs html attributes passed to the top ofs the list
     * @return string HTML
     */
    public function tree_block_contents($items, $attrs = array()) {
        // exit if empty, we don't want an empty ul element
        if (empty($items)) {
            return '';
        }
        // array of nested li elements
        $lis = array();
        foreach ($items as $item) {
            // this applies to the li item which contains all child lists too
            $content = $item->content($this);
            $liclasses = array($item->get_css_type());
            if (!$item->forceopen || (!$item->forceopen && $item->collapse) || ($item->children->count()==0  && $item->nodetype==navigation_node::NODETYPE_BRANCH)) {
                $liclasses[] = 'collapsed';
            }
            if ($item->isactive === true) {
                $liclasses[] = 'current_branch';
            }
            $liattr = array('class'=>join(' ',$liclasses));
            // class attribute on the div item which only contains the item content
            $divclasses = array('tree_item');
            if ($item->children->count()>0  || $item->nodetype==navigation_node::NODETYPE_BRANCH) {
                $divclasses[] = 'branch';
            } else {
                $divclasses[] = 'leaf';
            }
            if (!empty($item->classes) && count($item->classes)>0) {
                $divclasses[] = join(' ', $item->classes);
            }
            $divattr = array('class'=>join(' ', $divclasses));
            if (!empty($item->id)) {
                $divattr['id'] = $item->id;
            }
            $content = html_writer::tag('p', $content, $divattr) . $this->tree_block_contents($item->children);
            if (!empty($item->preceedwithhr) && $item->preceedwithhr===true) {
                $content = html_writer::empty_tag('hr') . $content;
            }
            $content = html_writer::tag('li', $content, $liattr);
            $lis[] = $content;
        }
        return html_writer::tag('ul', implode("\n", $lis), $attrs);
    }

    /**
     * Returns a search box.
     *
     * @param  string $id     The search box wrapper div id, defaults to an autogenerated one.
     * @return string         HTML with the search form hidden by default.
     */
    public function search_box($id = false) {
        global $CFG;

        // Accessing $CFG directly as using \core_search::is_global_search_enabled would
        // result in an extra included file for each site, even the ones where global search
        // is disabled.
        if (empty($CFG->enableglobalsearch) || !has_capability('moodle/search:query', context_system::instance())) {
            return '';
        }

        $data = [
            'action' => new moodle_url('/search/index.php'),
            'hiddenfields' => (object) ['name' => 'context', 'value' => $this->page->context->id],
            'inputname' => 'q',
            'searchstring' => get_string('search'),
            ];
        return $this->render_from_template('core/search_input_navbar', $data);
    }

    /**
     * Allow plugins to provide some content to be rendered in the navbar.
     * The plugin must define a PLUGIN_render_navbar_output function that returns
     * the HTML they wish to add to the navbar.
     *
     * @return string HTML for the navbar
     */
    public function navbar_plugin_output() {
        $output = '';

        // Give subsystems an opportunity to inject extra html content. The callback
        // must always return a string containing valid html.
        foreach (\core_component::get_core_subsystems() as $name => $path) {
            if ($path) {
                $output .= component_callback($name, 'render_navbar_output', [$this], '');
            }
        }

        if ($pluginsfunction = get_plugins_with_function('render_navbar_output')) {
            foreach ($pluginsfunction as $plugintype => $plugins) {
                foreach ($plugins as $pluginfunction) {
                    $output .= $pluginfunction($this);
                }
            }
        }

        return $output;
    }

    /**
     * Construct a user menu, returning HTML that can be echoed out by a
     * layout file.
     *
     * @param stdClass $user A user object, usually $USER.
     * @param bool $withlinks true if a dropdown should be built.
     * @return string HTML fragment.
     */
    public function user_menu($user = null, $withlinks = null) {
        global $USER, $CFG;
        require_once($CFG->dirroot . '/user/lib.php');

        if (is_null($user)) {
            $user = $USER;
        }

        // Note: this behaviour is intended to match that of core_renderer::login_info,
        // but should not be considered to be good practice; layout options are
        // intended to be theme-specific. Please don't copy this snippet anywhere else.
        if (is_null($withlinks)) {
            $withlinks = empty($this->page->layout_options['nologinlinks']);
        }

        // Add a class for when $withlinks is false.
        $usermenuclasses = 'usermenu';
        if (!$withlinks) {
            $usermenuclasses .= ' withoutlinks';
        }

        $returnstr = "";

        // If during initial install, return the empty return string.
        if (during_initial_install()) {
            return $returnstr;
        }

        $loginpage = $this->is_login_page();
        $loginurl = get_login_url();
        // If not logged in, show the typical not-logged-in string.
        if (!isloggedin()) {
            $returnstr = get_string('loggedinnot', 'moodle');
            if (!$loginpage) {
                $returnstr .= " (<a href=\"$loginurl\">" . get_string('login') . '</a>)';
            }
            return html_writer::div(
                html_writer::span(
                    $returnstr,
                    'login'
                ),
                $usermenuclasses
            );

        }

        // If logged in as a guest user, show a string to that effect.
        if (isguestuser()) {
            $returnstr = get_string('loggedinasguest');
            if (!$loginpage && $withlinks) {
                $returnstr .= " (<a href=\"$loginurl\">".get_string('login').'</a>)';
            }

            return html_writer::div(
                html_writer::span(
                    $returnstr,
                    'login'
                ),
                $usermenuclasses
            );
        }

        // Get some navigation opts.
        $opts = user_get_user_navigation_info($user, $this->page);

        $avatarclasses = "avatars";
        $avatarcontents = html_writer::span($opts->metadata['useravatar'], 'avatar current');
        $usertextcontents = $opts->metadata['userfullname'];

        // Other user.
        if (!empty($opts->metadata['asotheruser'])) {
            $avatarcontents .= html_writer::span(
                $opts->metadata['realuseravatar'],
                'avatar realuser'
            );
            $usertextcontents = $opts->metadata['realuserfullname'];
            $usertextcontents .= html_writer::tag(
                'span',
                get_string(
                    'loggedinas',
                    'moodle',
                    html_writer::span(
                        $opts->metadata['userfullname'],
                        'value'
                    )
                ),
                array('class' => 'meta viewingas')
            );
        }

        // Role.
        if (!empty($opts->metadata['asotherrole'])) {
            $role = core_text::strtolower(preg_replace('#[ ]+#', '-', trim($opts->metadata['rolename'])));
            $usertextcontents .= html_writer::span(
                $opts->metadata['rolename'],
                'meta role role-' . $role
            );
        }

        // User login failures.
        if (!empty($opts->metadata['userloginfail'])) {
            $usertextcontents .= html_writer::span(
                $opts->metadata['userloginfail'],
                'meta loginfailures'
            );
        }

        // MNet.
        if (!empty($opts->metadata['asmnetuser'])) {
            $mnet = strtolower(preg_replace('#[ ]+#', '-', trim($opts->metadata['mnetidprovidername'])));
            $usertextcontents .= html_writer::span(
                $opts->metadata['mnetidprovidername'],
                'meta mnet mnet-' . $mnet
            );
        }

        $returnstr .= html_writer::span(
            html_writer::span($usertextcontents, 'usertext mr-1') .
            html_writer::span($avatarcontents, $avatarclasses),
            'userbutton'
        );

        // Create a divider (well, a filler).
        $divider = new action_menu_filler();
        $divider->primary = false;

        $am = new action_menu();
        $am->set_menu_trigger(
            $returnstr
        );
        $am->set_action_label(get_string('usermenu'));
        $am->set_alignment(action_menu::TR, action_menu::BR);
        $am->set_nowrap_on_items();
        if ($withlinks) {
            $navitemcount = count($opts->navitems);
            $idx = 0;
            foreach ($opts->navitems as $key => $value) {

                switch ($value->itemtype) {
                    case 'divider':
                        // If the nav item is a divider, add one and skip link processing.
                        $am->add($divider);
                        break;

                    case 'invalid':
                        // Silently skip invalid entries (should we post a notification?).
                        break;

                    case 'link':
                        // Process this as a link item.
                        $pix = null;
                        if (isset($value->pix) && !empty($value->pix)) {
                            $pix = new pix_icon($value->pix, '', null, array('class' => 'iconsmall'));
                        } else if (isset($value->imgsrc) && !empty($value->imgsrc)) {
                            $value->title = html_writer::img(
                                $value->imgsrc,
                                $value->title,
                                array('class' => 'iconsmall')
                            ) . $value->title;
                        }

                        $al = new action_menu_link_secondary(
                            $value->url,
                            $pix,
                            $value->title,
                            array('class' => 'icon')
                        );
                        if (!empty($value->titleidentifier)) {
                            $al->attributes['data-title'] = $value->titleidentifier;
                        }
                        $am->add($al);
                        break;
                }

                $idx++;

                // Add dividers after the first item and before the last item.
                if ($idx == 1 || $idx == $navitemcount - 1) {
                    $am->add($divider);
                }
            }
        }

        return html_writer::div(
            $this->render($am),
            $usermenuclasses
        );
    }

    /**
     * Secure layout login info.
     *
     * @return string
     */
    public function secure_layout_login_info() {
        if (get_config('core', 'logininfoinsecurelayout')) {
            return $this->login_info(false);
        } else {
            return '';
        }
    }

    /**
     * Returns the language menu in the secure layout.
     *
     * No custom menu items are passed though, such that it will render only the language selection.
     *
     * @return string
     */
    public function secure_layout_language_menu() {
        if (get_config('core', 'langmenuinsecurelayout')) {
            $custommenu = new custom_menu('', current_language());
            return $this->render_custom_menu($custommenu);
        } else {
            return '';
        }
    }

    /**
     * This renders the navbar.
     * Uses bootstrap compatible html.
     */
    public function navbar() {
        return $this->render_from_template('core/navbar', $this->page->navbar);
    }

    /**
     * Renders a breadcrumb navigation node object.
     *
     * @param breadcrumb_navigation_node $item The navigation node to render.
     * @return string HTML fragment
     */
    protected function render_breadcrumb_navigation_node(breadcrumb_navigation_node $item) {

        if ($item->action instanceof moodle_url) {
            $content = $item->get_content();
            $title = $item->get_title();
            $attributes = array();
            $attributes['itemprop'] = 'url';
            if ($title !== '') {
                $attributes['title'] = $title;
            }
            if ($item->hidden) {
                $attributes['class'] = 'dimmed_text';
            }
            if ($item->is_last()) {
                $attributes['aria-current'] = 'page';
            }
            $content = html_writer::tag('span', $content, array('itemprop' => 'title'));
            $content = html_writer::link($item->action, $content, $attributes);

            $attributes = array();
            $attributes['itemscope'] = '';
            $attributes['itemtype'] = 'http://data-vocabulary.org/Breadcrumb';
            $content = html_writer::tag('span', $content, $attributes);

        } else {
            $content = $this->render_navigation_node($item);
        }
        return $content;
    }

    /**
     * Renders a navigation node object.
     *
     * @param navigation_node $item The navigation node to render.
     * @return string HTML fragment
     */
    protected function render_navigation_node(navigation_node $item) {
        $content = $item->get_content();
        $title = $item->get_title();
        if ($item->icon instanceof renderable && !$item->hideicon) {
            $icon = $this->render($item->icon);
            $content = $icon.$content; // use CSS for spacing of icons
        }
        if ($item->helpbutton !== null) {
            $content = trim($item->helpbutton).html_writer::tag('span', $content, array('class'=>'clearhelpbutton', 'tabindex'=>'0'));
        }
        if ($content === '') {
            return '';
        }
        if ($item->action instanceof action_link) {
            $link = $item->action;
            if ($item->hidden) {
                $link->add_class('dimmed');
            }
            if (!empty($content)) {
                // Providing there is content we will use that for the link content.
                $link->text = $content;
            }
            $content = $this->render($link);
        } else if ($item->action instanceof moodle_url) {
            $attributes = array();
            if ($title !== '') {
                $attributes['title'] = $title;
            }
            if ($item->hidden) {
                $attributes['class'] = 'dimmed_text';
            }
            $content = html_writer::link($item->action, $content, $attributes);

        } else if (is_string($item->action) || empty($item->action)) {
            $attributes = array('tabindex'=>'0'); //add tab support to span but still maintain character stream sequence.
            if ($title !== '') {
                $attributes['title'] = $title;
            }
            if ($item->hidden) {
                $attributes['class'] = 'dimmed_text';
            }
            $content = html_writer::tag('span', $content, $attributes);
        }
        return $content;
    }

    /**
     * Accessibility: Right arrow-like character is
     * used in the breadcrumb trail, course navigation menu
     * (previous/next activity), calendar, and search forum block.
     * If the theme does not set characters, appropriate defaults
     * are set automatically. Please DO NOT
     * use &lt; &gt; &raquo; - these are confusing for blind users.
     *
     * @return string
     */
    public function rarrow() {
        return $this->page->theme->rarrow;
    }

    /**
     * Accessibility: Left arrow-like character is
     * used in the breadcrumb trail, course navigation menu
     * (previous/next activity), calendar, and search forum block.
     * If the theme does not set characters, appropriate defaults
     * are set automatically. Please DO NOT
     * use &lt; &gt; &raquo; - these are confusing for blind users.
     *
     * @return string
     */
    public function larrow() {
        return $this->page->theme->larrow;
    }

    /**
     * Accessibility: Up arrow-like character is used in
     * the book heirarchical navigation.
     * If the theme does not set characters, appropriate defaults
     * are set automatically. Please DO NOT
     * use ^ - this is confusing for blind users.
     *
     * @return string
     */
    public function uarrow() {
        return $this->page->theme->uarrow;
    }

    /**
     * Accessibility: Down arrow-like character.
     * If the theme does not set characters, appropriate defaults
     * are set automatically.
     *
     * @return string
     */
    public function darrow() {
        return $this->page->theme->darrow;
    }

    /**
     * Returns the custom menu if one has been set
     *
     * A custom menu can be configured by browsing to
     *    Settings: Administration > Appearance > Themes > Theme settings
     * and then configuring the custommenu config setting as described.
     *
     * Theme developers: DO NOT OVERRIDE! Please override function
     * {@link core_renderer::render_custom_menu()} instead.
     *
     * @param string $custommenuitems - custom menuitems set by theme instead of global theme settings
     * @return string
     */
    public function custom_menu($custommenuitems = '') {
        global $CFG;

        if (empty($custommenuitems) && !empty($CFG->custommenuitems)) {
            $custommenuitems = $CFG->custommenuitems;
        }
        $custommenu = new custom_menu($custommenuitems, current_language());
        return $this->render_custom_menu($custommenu);
    }

    /**
     * We want to show the custom menus as a list of links in the footer on small screens.
     * Just return the menu object exported so we can render it differently.
     */
    public function custom_menu_flat() {
        global $CFG;
        $custommenuitems = '';

        if (empty($custommenuitems) && !empty($CFG->custommenuitems)) {
            $custommenuitems = $CFG->custommenuitems;
        }
        $custommenu = new custom_menu($custommenuitems, current_language());
        $langs = get_string_manager()->get_list_of_translations();
        $haslangmenu = $this->lang_menu() != '';

        if ($haslangmenu) {
            $strlang = get_string('language');
            $currentlang = current_language();
            if (isset($langs[$currentlang])) {
                $currentlang = $langs[$currentlang];
            } else {
                $currentlang = $strlang;
            }
            $this->language = $custommenu->add($currentlang, new moodle_url('#'), $strlang, 10000);
            foreach ($langs as $langtype => $langname) {
                $this->language->add($langname, new moodle_url($this->page->url, array('lang' => $langtype)), $langname);
            }
        }

        return $custommenu->export_for_template($this);
    }

    /**
     * Renders a custom menu object (located in outputcomponents.php)
     *
     * The custom menu this method produces makes use of the YUI3 menunav widget
     * and requires very specific html elements and classes.
     *
     * @staticvar int $menucount
     * @param custom_menu $menu
     * @return string
     */
    protected function render_custom_menu(custom_menu $menu) {
        global $CFG;

        $langs = get_string_manager()->get_list_of_translations();
        $haslangmenu = $this->lang_menu() != '';

        if (!$menu->has_children() && !$haslangmenu) {
            return '';
        }

        if ($haslangmenu) {
            $strlang = get_string('language');
            $currentlang = current_language();
            if (isset($langs[$currentlang])) {
                $currentlang = $langs[$currentlang];
            } else {
                $currentlang = $strlang;
            }
            $this->language = $menu->add($currentlang, new moodle_url('#'), $strlang, 10000);
            foreach ($langs as $langtype => $langname) {
                $this->language->add($langname, new moodle_url($this->page->url, array('lang' => $langtype)), $langname);
            }
        }

        $content = '';
        foreach ($menu->get_children() as $item) {
            $context = $item->export_for_template($this);
            $content .= $this->render_from_template('core/custom_menu_item', $context);
        }

        return $content;
    }

    /**
     * Renders a custom menu node as part of a submenu
     *
     * The custom menu this method produces makes use of the YUI3 menunav widget
     * and requires very specific html elements and classes.
     *
     * @see core:renderer::render_custom_menu()
     *
     * @staticvar int $submenucount
     * @param custom_menu_item $menunode
     * @return string
     */
    protected function render_custom_menu_item(custom_menu_item $menunode) {
        // Required to ensure we get unique trackable id's
        static $submenucount = 0;
        if ($menunode->has_children()) {
            // If the child has menus render it as a sub menu
            $submenucount++;
            $content = html_writer::start_tag('li');
            if ($menunode->get_url() !== null) {
                $url = $menunode->get_url();
            } else {
                $url = '#cm_submenu_'.$submenucount;
            }
            $content .= html_writer::link($url, $menunode->get_text(), array('class'=>'yui3-menu-label', 'title'=>$menunode->get_title()));
            $content .= html_writer::start_tag('div', array('id'=>'cm_submenu_'.$submenucount, 'class'=>'yui3-menu custom_menu_submenu'));
            $content .= html_writer::start_tag('div', array('class'=>'yui3-menu-content'));
            $content .= html_writer::start_tag('ul');
            foreach ($menunode->get_children() as $menunode) {
                $content .= $this->render_custom_menu_item($menunode);
            }
            $content .= html_writer::end_tag('ul');
            $content .= html_writer::end_tag('div');
            $content .= html_writer::end_tag('div');
            $content .= html_writer::end_tag('li');
        } else {
            // The node doesn't have children so produce a final menuitem.
            // Also, if the node's text matches '####', add a class so we can treat it as a divider.
            $content = '';
            if (preg_match("/^#+$/", $menunode->get_text())) {

                // This is a divider.
                $content = html_writer::start_tag('li', array('class' => 'yui3-menuitem divider'));
            } else {
                $content = html_writer::start_tag(
                    'li',
                    array(
                        'class' => 'yui3-menuitem'
                    )
                );
                if ($menunode->get_url() !== null) {
                    $url = $menunode->get_url();
                } else {
                    $url = '#';
                }
                $content .= html_writer::link(
                    $url,
                    $menunode->get_text(),
                    array('class' => 'yui3-menuitem-content', 'title' => $menunode->get_title())
                );
            }
            $content .= html_writer::end_tag('li');
        }
        // Return the sub menu
        return $content;
    }

    /**
     * Renders theme links for switching between default and other themes.
     *
     * @return string
     */
    protected function theme_switch_links() {

        $actualdevice = core_useragent::get_device_type();
        $currentdevice = $this->page->devicetypeinuse;
        $switched = ($actualdevice != $currentdevice);

        if (!$switched && $currentdevice == 'default' && $actualdevice == 'default') {
            // The user is using the a default device and hasn't switched so don't shown the switch
            // device links.
            return '';
        }

        if ($switched) {
            $linktext = get_string('switchdevicerecommended');
            $devicetype = $actualdevice;
        } else {
            $linktext = get_string('switchdevicedefault');
            $devicetype = 'default';
        }
        $linkurl = new moodle_url('/theme/switchdevice.php', array('url' => $this->page->url, 'device' => $devicetype, 'sesskey' => sesskey()));

        $content  = html_writer::start_tag('div', array('id' => 'theme_switch_link'));
        $content .= html_writer::link($linkurl, $linktext, array('rel' => 'nofollow'));
        $content .= html_writer::end_tag('div');

        return $content;
    }

    /**
     * Renders tabs
     *
     * This function replaces print_tabs() used before Moodle 2.5 but with slightly different arguments
     *
     * Theme developers: In order to change how tabs are displayed please override functions
     * {@link core_renderer::render_tabtree()} and/or {@link core_renderer::render_tabobject()}
     *
     * @param array $tabs array of tabs, each of them may have it's own ->subtree
     * @param string|null $selected which tab to mark as selected, all parent tabs will
     *     automatically be marked as activated
     * @param array|string|null $inactive list of ids of inactive tabs, regardless of
     *     their level. Note that you can as weel specify tabobject::$inactive for separate instances
     * @return string
     */
    public final function tabtree($tabs, $selected = null, $inactive = null) {
        return $this->render(new tabtree($tabs, $selected, $inactive));
    }

    /**
     * Renders tabtree
     *
     * @param tabtree $tabtree
     * @return string
     */
    protected function render_tabtree(tabtree $tabtree) {
        if (empty($tabtree->subtree)) {
            return '';
        }
        $data = $tabtree->export_for_template($this);
        return $this->render_from_template('core/tabtree', $data);
    }

    /**
     * Renders tabobject (part of tabtree)
     *
     * This function is called from {@link core_renderer::render_tabtree()}
     * and also it calls itself when printing the $tabobject subtree recursively.
     *
     * Property $tabobject->level indicates the number of row of tabs.
     *
     * @param tabobject $tabobject
     * @return string HTML fragment
     */
    protected function render_tabobject(tabobject $tabobject) {
        $str = '';

        // Print name of the current tab.
        if ($tabobject instanceof tabtree) {
            // No name for tabtree root.
        } else if ($tabobject->inactive || $tabobject->activated || ($tabobject->selected && !$tabobject->linkedwhenselected)) {
            // Tab name without a link. The <a> tag is used for styling.
            $str .= html_writer::tag('a', html_writer::span($tabobject->text), array('class' => 'nolink moodle-has-zindex'));
        } else {
            // Tab name with a link.
            if (!($tabobject->link instanceof moodle_url)) {
                // backward compartibility when link was passed as quoted string
                $str .= "<a href=\"$tabobject->link\" title=\"$tabobject->title\"><span>$tabobject->text</span></a>";
            } else {
                $str .= html_writer::link($tabobject->link, html_writer::span($tabobject->text), array('title' => $tabobject->title));
            }
        }

        if (empty($tabobject->subtree)) {
            if ($tabobject->selected) {
                $str .= html_writer::tag('div', '&nbsp;', array('class' => 'tabrow'. ($tabobject->level + 1). ' empty'));
            }
            return $str;
        }

        // Print subtree.
        if ($tabobject->level == 0 || $tabobject->selected || $tabobject->activated) {
            $str .= html_writer::start_tag('ul', array('class' => 'tabrow'. $tabobject->level));
            $cnt = 0;
            foreach ($tabobject->subtree as $tab) {
                $liclass = '';
                if (!$cnt) {
                    $liclass .= ' first';
                }
                if ($cnt == count($tabobject->subtree) - 1) {
                    $liclass .= ' last';
                }
                if ((empty($tab->subtree)) && (!empty($tab->selected))) {
                    $liclass .= ' onerow';
                }

                if ($tab->selected) {
                    $liclass .= ' here selected';
                } else if ($tab->activated) {
                    $liclass .= ' here active';
                }

                // This will recursively call function render_tabobject() for each item in subtree.
                $str .= html_writer::tag('li', $this->render($tab), array('class' => trim($liclass)));
                $cnt++;
            }
            $str .= html_writer::end_tag('ul');
        }

        return $str;
    }

    /**
     * Get the HTML for blocks in the given region.
     *
     * @since Moodle 2.5.1 2.6
     * @param string $region The region to get HTML for.
     * @return string HTML.
     */
    public function blocks($region, $classes = array(), $tag = 'aside') {
        $displayregion = $this->page->apply_theme_region_manipulations($region);
        $classes = (array)$classes;
        $classes[] = 'block-region';
        $attributes = array(
            'id' => 'block-region-'.preg_replace('#[^a-zA-Z0-9_\-]+#', '-', $displayregion),
            'class' => join(' ', $classes),
            'data-blockregion' => $displayregion,
            'data-droptarget' => '1'
        );
        if ($this->page->blocks->region_has_content($displayregion, $this)) {
            $content = $this->blocks_for_region($displayregion);
        } else {
            $content = '';
        }
        return html_writer::tag($tag, $content, $attributes);
    }

    /**
     * Renders a custom block region.
     *
     * Use this method if you want to add an additional block region to the content of the page.
     * Please note this should only be used in special situations.
     * We want to leave the theme is control where ever possible!
     *
     * This method must use the same method that the theme uses within its layout file.
     * As such it asks the theme what method it is using.
     * It can be one of two values, blocks or blocks_for_region (deprecated).
     *
     * @param string $regionname The name of the custom region to add.
     * @return string HTML for the block region.
     */
    public function custom_block_region($regionname) {
        if ($this->page->theme->get_block_render_method() === 'blocks') {
            return $this->blocks($regionname);
        } else {
            return $this->blocks_for_region($regionname);
        }
    }

    /**
     * Returns the CSS classes to apply to the body tag.
     *
     * @since Moodle 2.5.1 2.6
     * @param array $additionalclasses Any additional classes to apply.
     * @return string
     */
    public function body_css_classes(array $additionalclasses = array()) {
        return $this->page->bodyclasses . ' ' . implode(' ', $additionalclasses);
    }

    /**
     * The ID attribute to apply to the body tag.
     *
     * @since Moodle 2.5.1 2.6
     * @return string
     */
    public function body_id() {
        return $this->page->bodyid;
    }

    /**
     * Returns HTML attributes to use within the body tag. This includes an ID and classes.
     *
     * @since Moodle 2.5.1 2.6
     * @param string|array $additionalclasses Any additional classes to give the body tag,
     * @return string
     */
    public function body_attributes($additionalclasses = array()) {
        if (!is_array($additionalclasses)) {
            $additionalclasses = explode(' ', $additionalclasses);
        }
        return ' id="'. $this->body_id().'" class="'.$this->body_css_classes($additionalclasses).'"';
    }

    /**
     * Gets HTML for the page heading.
     *
     * @since Moodle 2.5.1 2.6
     * @param string $tag The tag to encase the heading in. h1 by default.
     * @return string HTML.
     */
    public function page_heading($tag = 'h1') {
        return html_writer::tag($tag, $this->page->heading);
    }

    /**
     * Gets the HTML for the page heading button.
     *
     * @since Moodle 2.5.1 2.6
     * @return string HTML.
     */
    public function page_heading_button() {
        return $this->page->button;
    }

    /**
     * Returns the Moodle docs link to use for this page.
     *
     * @since Moodle 2.5.1 2.6
     * @param string $text
     * @return string
     */
    public function page_doc_link($text = null) {
        if ($text === null) {
            $text = get_string('moodledocslink');
        }
        $path = page_get_doc_link_path($this->page);
        if (!$path) {
            return '';
        }
        return $this->doc_link($path, $text);
    }

    /**
     * Returns the page heading menu.
     *
     * @since Moodle 2.5.1 2.6
     * @return string HTML.
     */
    public function page_heading_menu() {
        return $this->page->headingmenu;
    }

    /**
     * Returns the title to use on the page.
     *
     * @since Moodle 2.5.1 2.6
     * @return string
     */
    public function page_title() {
        return $this->page->title;
    }

    /**
     * Returns the moodle_url for the favicon.
     *
     * @since Moodle 2.5.1 2.6
     * @return moodle_url The moodle_url for the favicon
     */
    public function favicon() {
        return $this->image_url('favicon', 'theme');
    }

    /**
     * Renders preferences groups.
     *
     * @param  preferences_groups $renderable The renderable
     * @return string The output.
     */
    public function render_preferences_groups(preferences_groups $renderable) {
        return $this->render_from_template('core/preferences_groups', $renderable);
    }

    /**
     * Renders preferences group.
     *
     * @param  preferences_group $renderable The renderable
     * @return string The output.
     */
    public function render_preferences_group(preferences_group $renderable) {
        $html = '';
        $html .= html_writer::start_tag('div', array('class' => 'col-sm-4 preferences-group'));
        $html .= $this->heading($renderable->title, 3);
        $html .= html_writer::start_tag('ul');
        foreach ($renderable->nodes as $node) {
            if ($node->has_children()) {
                debugging('Preferences nodes do not support children', DEBUG_DEVELOPER);
            }
            $html .= html_writer::tag('li', $this->render($node));
        }
        $html .= html_writer::end_tag('ul');
        $html .= html_writer::end_tag('div');
        return $html;
    }

    public function context_header($headerinfo = null, $headinglevel = 1) {
        global $DB, $USER, $CFG, $SITE;
        require_once($CFG->dirroot . '/user/lib.php');
        $context = $this->page->context;
        $heading = null;
        $imagedata = null;
        $subheader = null;
        $userbuttons = null;

        // Make sure to use the heading if it has been set.
        if (isset($headerinfo['heading'])) {
            $heading = $headerinfo['heading'];
        } else {
            $heading = $this->page->heading;
        }

        // The user context currently has images and buttons. Other contexts may follow.
        if (isset($headerinfo['user']) || $context->contextlevel == CONTEXT_USER) {
            if (isset($headerinfo['user'])) {
                $user = $headerinfo['user'];
            } else {
                // Look up the user information if it is not supplied.
                $user = $DB->get_record('user', array('id' => $context->instanceid));
            }

            // If the user context is set, then use that for capability checks.
            if (isset($headerinfo['usercontext'])) {
                $context = $headerinfo['usercontext'];
            }

            // Only provide user information if the user is the current user, or a user which the current user can view.
            // When checking user_can_view_profile(), either:
            // If the page context is course, check the course context (from the page object) or;
            // If page context is NOT course, then check across all courses.
            $course = ($this->page->context->contextlevel == CONTEXT_COURSE) ? $this->page->course : null;

            if (user_can_view_profile($user, $course)) {
                // Use the user's full name if the heading isn't set.
                if (empty($heading)) {
                    $heading = fullname($user);
                }

                $imagedata = $this->user_picture($user, array('size' => 100));

                // Check to see if we should be displaying a message button.
                if (!empty($CFG->messaging) && has_capability('moodle/site:sendmessage', $context)) {
                    $userbuttons = array(
                        'messages' => array(
                            'buttontype' => 'message',
                            'title' => get_string('message', 'message'),
                            'url' => new moodle_url('/message/index.php', array('id' => $user->id)),
                            'image' => 'message',
                            'linkattributes' => \core_message\helper::messageuser_link_params($user->id),
                            'page' => $this->page
                        )
                    );

                    if ($USER->id != $user->id) {
                        $iscontact = \core_message\api::is_contact($USER->id, $user->id);
                        $contacttitle = $iscontact ? 'removefromyourcontacts' : 'addtoyourcontacts';
                        $contacturlaction = $iscontact ? 'removecontact' : 'addcontact';
                        $contactimage = $iscontact ? 'removecontact' : 'addcontact';
                        $userbuttons['togglecontact'] = array(
                                'buttontype' => 'togglecontact',
                                'title' => get_string($contacttitle, 'message'),
                                'url' => new moodle_url('/message/index.php', array(
                                        'user1' => $USER->id,
                                        'user2' => $user->id,
                                        $contacturlaction => $user->id,
                                        'sesskey' => sesskey())
                                ),
                                'image' => $contactimage,
                                'linkattributes' => \core_message\helper::togglecontact_link_params($user, $iscontact),
                                'page' => $this->page
                            );
                    }

                    $this->page->requires->string_for_js('changesmadereallygoaway', 'moodle');
                }
            } else {
                $heading = null;
            }
        }

        if ($this->should_display_main_logo($headinglevel)) {
            $sitename = format_string($SITE->fullname, true, ['context' => context_course::instance(SITEID)]);
            // Logo.
            $html = html_writer::div(
                html_writer::empty_tag('img', [
                    'src' => $this->get_logo_url(null, 150),
                    'alt' => get_string('logoof', '', $sitename),
                    'class' => 'img-fluid'
                ]),
                'logo'
            );
            // Heading.
            if (!isset($heading)) {
                $html .= $this->heading($this->page->heading, $headinglevel, 'sr-only');
            } else {
                $html .= $this->heading($heading, $headinglevel, 'sr-only');
            }
            return $html;
        }

        $contextheader = new context_header($heading, $headinglevel, $imagedata, $userbuttons);
        return $this->render_context_header($contextheader);
    }

    /**
     * Renders the skip links for the page.
     *
     * @param array $links List of skip links.
     * @return string HTML for the skip links.
     */
    public function render_skip_links($links) {
        $context = [ 'links' => []];

        foreach ($links as $url => $text) {
            $context['links'][] = [ 'url' => $url, 'text' => $text];
        }

        return $this->render_from_template('core/skip_links', $context);
    }

     /**
      * Renders the header bar.
      *
      * @param context_header $contextheader Header bar object.
      * @return string HTML for the header bar.
      */
    protected function render_context_header(context_header $contextheader) {

        // Generate the heading first and before everything else as we might have to do an early return.
        if (!isset($contextheader->heading)) {
            $heading = $this->heading($this->page->heading, $contextheader->headinglevel);
        } else {
            $heading = $this->heading($contextheader->heading, $contextheader->headinglevel);
        }

        $showheader = empty($this->page->layout_options['nocontextheader']);
        if (!$showheader) {
            // Return the heading wrapped in an sr-only element so it is only visible to screen-readers.
            return html_writer::div($heading, 'sr-only');
        }

        // All the html stuff goes here.
        $html = html_writer::start_div('page-context-header');

        // Image data.
        if (isset($contextheader->imagedata)) {
            // Header specific image.
            $html .= html_writer::div($contextheader->imagedata, 'page-header-image');
        }

        // Headings.
        $html .= html_writer::tag('div', $heading, array('class' => 'page-header-headings'));

        // Buttons.
        if (isset($contextheader->additionalbuttons)) {
            $html .= html_writer::start_div('btn-group header-button-group');
            foreach ($contextheader->additionalbuttons as $button) {
                if (!isset($button->page)) {
                    // Include js for messaging.
                    if ($button['buttontype'] === 'togglecontact') {
                        \core_message\helper::togglecontact_requirejs();
                    }
                    if ($button['buttontype'] === 'message') {
                        \core_message\helper::messageuser_requirejs();
                    }
                    $image = $this->pix_icon($button['formattedimage'], $button['title'], 'moodle', array(
                        'class' => 'iconsmall',
                        'role' => 'presentation'
                    ));
                    $image .= html_writer::span($button['title'], 'header-button-title');
                } else {
                    $image = html_writer::empty_tag('img', array(
                        'src' => $button['formattedimage'],
                        'role' => 'presentation'
                    ));
                }
                $html .= html_writer::link($button['url'], html_writer::tag('span', $image), $button['linkattributes']);
            }
            $html .= html_writer::end_div();
        }
        $html .= html_writer::end_div();

        return $html;
    }

    /**
     * Wrapper for header elements.
     *
     * @return string HTML to display the main header.
     */
    public function full_header() {

        if ($this->page->include_region_main_settings_in_header_actions() &&
                !$this->page->blocks->is_block_present('settings')) {
            // Only include the region main settings if the page has requested it and it doesn't already have
            // the settings block on it. The region main settings are included in the settings block and
            // duplicating the content causes behat failures.
            $this->page->add_header_action(html_writer::div(
                $this->region_main_settings_menu(),
                'd-print-none',
                ['id' => 'region-main-settings-menu']
            ));
        }

        $header = new stdClass();
        $header->settingsmenu = $this->context_header_settings_menu();
        $header->contextheader = $this->context_header();
        $header->hasnavbar = empty($this->page->layout_options['nonavbar']);
        $header->navbar = $this->navbar();
        $header->pageheadingbutton = $this->page_heading_button();
        $header->courseheader = $this->course_header();
        $header->headeractions = $this->page->get_header_actions();
        return $this->render_from_template('core/full_header', $header);
    }

    /**
     * This is an optional menu that can be added to a layout by a theme. It contains the
     * menu for the course administration, only on the course main page.
     *
     * @return string
     */
    public function context_header_settings_menu() {
        $context = $this->page->context;
        $menu = new action_menu();

        $items = $this->page->navbar->get_items();
        $currentnode = end($items);

        $showcoursemenu = false;
        $showfrontpagemenu = false;
        $showusermenu = false;

        // We are on the course home page.
        if (($context->contextlevel == CONTEXT_COURSE) &&
                !empty($currentnode) &&
                ($currentnode->type == navigation_node::TYPE_COURSE || $currentnode->type == navigation_node::TYPE_SECTION)) {
            $showcoursemenu = true;
        }

        $courseformat = course_get_format($this->page->course);
        // This is a single activity course format, always show the course menu on the activity main page.
        if ($context->contextlevel == CONTEXT_MODULE &&
                !$courseformat->has_view_page()) {

            $this->page->navigation->initialise();
            $activenode = $this->page->navigation->find_active_node();
            // If the settings menu has been forced then show the menu.
            if ($this->page->is_settings_menu_forced()) {
                $showcoursemenu = true;
            } else if (!empty($activenode) && ($activenode->type == navigation_node::TYPE_ACTIVITY ||
                            $activenode->type == navigation_node::TYPE_RESOURCE)) {

                // We only want to show the menu on the first page of the activity. This means
                // the breadcrumb has no additional nodes.
                if ($currentnode && ($currentnode->key == $activenode->key && $currentnode->type == $activenode->type)) {
                    $showcoursemenu = true;
                }
            }
        }

        // This is the site front page.
        if ($context->contextlevel == CONTEXT_COURSE &&
                !empty($currentnode) &&
                $currentnode->key === 'home') {
            $showfrontpagemenu = true;
        }

        // This is the user profile page.
        if ($context->contextlevel == CONTEXT_USER &&
                !empty($currentnode) &&
                ($currentnode->key === 'myprofile')) {
            $showusermenu = true;
        }

        if ($showfrontpagemenu) {
            $settingsnode = $this->page->settingsnav->find('frontpage', navigation_node::TYPE_SETTING);
            if ($settingsnode) {
                // Build an action menu based on the visible nodes from this navigation tree.
                $skipped = $this->build_action_menu_from_navigation($menu, $settingsnode, false, true);

                // We only add a list to the full settings menu if we didn't include every node in the short menu.
                if ($skipped) {
                    $text = get_string('morenavigationlinks');
                    $url = new moodle_url('/course/admin.php', array('courseid' => $this->page->course->id));
                    $link = new action_link($url, $text, null, null, new pix_icon('t/edit', $text));
                    $menu->add_secondary_action($link);
                }
            }
        } else if ($showcoursemenu) {
            $settingsnode = $this->page->settingsnav->find('courseadmin', navigation_node::TYPE_COURSE);
            if ($settingsnode) {
                // Build an action menu based on the visible nodes from this navigation tree.
                $skipped = $this->build_action_menu_from_navigation($menu, $settingsnode, false, true);

                // We only add a list to the full settings menu if we didn't include every node in the short menu.
                if ($skipped) {
                    $text = get_string('morenavigationlinks');
                    $url = new moodle_url('/course/admin.php', array('courseid' => $this->page->course->id));
                    $link = new action_link($url, $text, null, null, new pix_icon('t/edit', $text));
                    $menu->add_secondary_action($link);
                }
            }
        } else if ($showusermenu) {
            // Get the course admin node from the settings navigation.
            $settingsnode = $this->page->settingsnav->find('useraccount', navigation_node::TYPE_CONTAINER);
            if ($settingsnode) {
                // Build an action menu based on the visible nodes from this navigation tree.
                $this->build_action_menu_from_navigation($menu, $settingsnode);
            }
        }

        return $this->render($menu);
    }

    /**
     * Take a node in the nav tree and make an action menu out of it.
     * The links are injected in the action menu.
     *
     * @param action_menu $menu
     * @param navigation_node $node
     * @param boolean $indent
     * @param boolean $onlytopleafnodes
     * @return boolean nodesskipped - True if nodes were skipped in building the menu
     */
    protected function build_action_menu_from_navigation(action_menu $menu,
            navigation_node $node,
            $indent = false,
            $onlytopleafnodes = false) {
        $skipped = false;
        // Build an action menu based on the visible nodes from this navigation tree.
        foreach ($node->children as $menuitem) {
            if ($menuitem->display) {
                if ($onlytopleafnodes && $menuitem->children->count()) {
                    $skipped = true;
                    continue;
                }
                if ($menuitem->action) {
                    if ($menuitem->action instanceof action_link) {
                        $link = $menuitem->action;
                        // Give preference to setting icon over action icon.
                        if (!empty($menuitem->icon)) {
                            $link->icon = $menuitem->icon;
                        }
                    } else {
                        $link = new action_link($menuitem->action, $menuitem->text, null, null, $menuitem->icon);
                    }
                } else {
                    if ($onlytopleafnodes) {
                        $skipped = true;
                        continue;
                    }
                    $link = new action_link(new moodle_url('#'), $menuitem->text, null, ['disabled' => true], $menuitem->icon);
                }
                if ($indent) {
                    $link->add_class('ml-4');
                }
                if (!empty($menuitem->classes)) {
                    $link->add_class(implode(" ", $menuitem->classes));
                }

                $menu->add_secondary_action($link);
                $skipped = $skipped || $this->build_action_menu_from_navigation($menu, $menuitem, true);
            }
        }
        return $skipped;
    }

    /**
     * This is an optional menu that can be added to a layout by a theme. It contains the
     * menu for the most specific thing from the settings block. E.g. Module administration.
     *
     * @return string
     */
    public function region_main_settings_menu() {
        $context = $this->page->context;
        $menu = new action_menu();

        if ($context->contextlevel == CONTEXT_MODULE) {

            $this->page->navigation->initialise();
            $node = $this->page->navigation->find_active_node();
            $buildmenu = false;
            // If the settings menu has been forced then show the menu.
            if ($this->page->is_settings_menu_forced()) {
                $buildmenu = true;
            } else if (!empty($node) && ($node->type == navigation_node::TYPE_ACTIVITY ||
                            $node->type == navigation_node::TYPE_RESOURCE)) {

                $items = $this->page->navbar->get_items();
                $navbarnode = end($items);
                // We only want to show the menu on the first page of the activity. This means
                // the breadcrumb has no additional nodes.
                if ($navbarnode && ($navbarnode->key === $node->key && $navbarnode->type == $node->type)) {
                    $buildmenu = true;
                }
            }
            if ($buildmenu) {
                // Get the course admin node from the settings navigation.
                $node = $this->page->settingsnav->find('modulesettings', navigation_node::TYPE_SETTING);
                if ($node) {
                    // Build an action menu based on the visible nodes from this navigation tree.
                    $this->build_action_menu_from_navigation($menu, $node);
                }
            }

        } else if ($context->contextlevel == CONTEXT_COURSECAT) {
            // For course category context, show category settings menu, if we're on the course category page.
            if ($this->page->pagetype === 'course-index-category') {
                $node = $this->page->settingsnav->find('categorysettings', navigation_node::TYPE_CONTAINER);
                if ($node) {
                    // Build an action menu based on the visible nodes from this navigation tree.
                    $this->build_action_menu_from_navigation($menu, $node);
                }
            }

        } else {
            $items = $this->page->navbar->get_items();
            $navbarnode = end($items);

            if ($navbarnode && ($navbarnode->key === 'participants')) {
                $node = $this->page->settingsnav->find('users', navigation_node::TYPE_CONTAINER);
                if ($node) {
                    // Build an action menu based on the visible nodes from this navigation tree.
                    $this->build_action_menu_from_navigation($menu, $node);
                }

            }
        }
        return $this->render($menu);
    }

    /**
     * Displays the list of tags associated with an entry
     *
     * @param array $tags list of instances of core_tag or stdClass
     * @param string $label label to display in front, by default 'Tags' (get_string('tags')), set to null
     *               to use default, set to '' (empty string) to omit the label completely
     * @param string $classes additional classes for the enclosing div element
     * @param int $limit limit the number of tags to display, if size of $tags is more than this limit the "more" link
     *               will be appended to the end, JS will toggle the rest of the tags
     * @param context $pagecontext specify if needed to overwrite the current page context for the view tag link
     * @param bool $accesshidelabel if true, the label should have class="accesshide" added.
     * @return string
     */
    public function tag_list($tags, $label = null, $classes = '', $limit = 10,
            $pagecontext = null, $accesshidelabel = false) {
        $list = new \core_tag\output\taglist($tags, $label, $classes, $limit, $pagecontext, $accesshidelabel);
        return $this->render_from_template('core_tag/taglist', $list->export_for_template($this));
    }

    /**
     * Renders element for inline editing of any value
     *
     * @param \core\output\inplace_editable $element
     * @return string
     */
    public function render_inplace_editable(\core\output\inplace_editable $element) {
        return $this->render_from_template('core/inplace_editable', $element->export_for_template($this));
    }

    /**
     * Renders a bar chart.
     *
     * @param \core\chart_bar $chart The chart.
     * @return string.
     */
    public function render_chart_bar(\core\chart_bar $chart) {
        return $this->render_chart($chart);
    }

    /**
     * Renders a line chart.
     *
     * @param \core\chart_line $chart The chart.
     * @return string.
     */
    public function render_chart_line(\core\chart_line $chart) {
        return $this->render_chart($chart);
    }

    /**
     * Renders a pie chart.
     *
     * @param \core\chart_pie $chart The chart.
     * @return string.
     */
    public function render_chart_pie(\core\chart_pie $chart) {
        return $this->render_chart($chart);
    }

    /**
     * Renders a chart.
     *
     * @param \core\chart_base $chart The chart.
     * @param bool $withtable Whether to include a data table with the chart.
     * @return string.
     */
    public function render_chart(\core\chart_base $chart, $withtable = true) {
        $chartdata = json_encode($chart);
        return $this->render_from_template('core/chart', (object) [
            'chartdata' => $chartdata,
            'withtable' => $withtable
        ]);
    }

    /**
     * Renders the login form.
     *
     * @param \core_auth\output\login $form The renderable.
     * @return string
     */
    public function render_login(\core_auth\output\login $form) {
        global $CFG, $SITE;

        $context = $form->export_for_template($this);

        // Override because rendering is not supported in template yet.
        if ($CFG->rememberusername == 0) {
            $context->cookieshelpiconformatted = $this->help_icon('cookiesenabledonlysession');
        } else {
            $context->cookieshelpiconformatted = $this->help_icon('cookiesenabled');
        }
        $context->errorformatted = $this->error_text($context->error);
        $url = $this->get_logo_url();
        if ($url) {
            $url = $url->out(false);
        }
        $context->logourl = $url;
        $context->sitename = format_string($SITE->fullname, true,
                ['context' => context_course::instance(SITEID), "escape" => false]);

        return $this->render_from_template('core/loginform', $context);
    }

    /**
     * Renders an mform element from a template.
     *
     * @param HTML_QuickForm_element $element element
     * @param bool $required if input is required field
     * @param bool $advanced if input is an advanced field
     * @param string $error error message to display
     * @param bool $ingroup True if this element is rendered as part of a group
     * @return mixed string|bool
     */
    public function mform_element($element, $required, $advanced, $error, $ingroup) {
        $templatename = 'core_form/element-' . $element->getType();
        if ($ingroup) {
            $templatename .= "-inline";
        }
        try {
            // We call this to generate a file not found exception if there is no template.
            // We don't want to call export_for_template if there is no template.
            core\output\mustache_template_finder::get_template_filepath($templatename);

            if ($element instanceof templatable) {
                $elementcontext = $element->export_for_template($this);

                $helpbutton = '';
                if (method_exists($element, 'getHelpButton')) {
                    $helpbutton = $element->getHelpButton();
                }
                $label = $element->getLabel();
                $text = '';
                if (method_exists($element, 'getText')) {
                    // There currently exists code that adds a form element with an empty label.
                    // If this is the case then set the label to the description.
                    if (empty($label)) {
                        $label = $element->getText();
                    } else {
                        $text = $element->getText();
                    }
                }

                // Generate the form element wrapper ids and names to pass to the template.
                // This differs between group and non-group elements.
                if ($element->getType() === 'group') {
                    // Group element.
                    // The id will be something like 'fgroup_id_NAME'. E.g. fgroup_id_mygroup.
                    $elementcontext['wrapperid'] = $elementcontext['id'];

                    // Ensure group elements pass through the group name as the element name.
                    $elementcontext['name'] = $elementcontext['groupname'];
                } else {
                    // Non grouped element.
                    // Creates an id like 'fitem_id_NAME'. E.g. fitem_id_mytextelement.
                    $elementcontext['wrapperid'] = 'fitem_' . $elementcontext['id'];
                }

                $context = array(
                    'element' => $elementcontext,
                    'label' => $label,
                    'text' => $text,
                    'required' => $required,
                    'advanced' => $advanced,
                    'helpbutton' => $helpbutton,
                    'error' => $error
                );
                return $this->render_from_template($templatename, $context);
            }
        } catch (Exception $e) {
            // No template for this element.
            return false;
        }
    }

    /**
     * Render the login signup form into a nice template for the theme.
     *
     * @param mform $form
     * @return string
     */
    public function render_login_signup_form($form) {
        global $SITE;

        $context = $form->export_for_template($this);
        $url = $this->get_logo_url();
        if ($url) {
            $url = $url->out(false);
        }
        $context['logourl'] = $url;
        $context['sitename'] = format_string($SITE->fullname, true,
                ['context' => context_course::instance(SITEID), "escape" => false]);

        return $this->render_from_template('core/signup_form_layout', $context);
    }

    /**
     * Render the verify age and location page into a nice template for the theme.
     *
     * @param \core_auth\output\verify_age_location_page $page The renderable
     * @return string
     */
    protected function render_verify_age_location_page($page) {
        $context = $page->export_for_template($this);

        return $this->render_from_template('core/auth_verify_age_location_page', $context);
    }

    /**
     * Render the digital minor contact information page into a nice template for the theme.
     *
     * @param \core_auth\output\digital_minor_page $page The renderable
     * @return string
     */
    protected function render_digital_minor_page($page) {
        $context = $page->export_for_template($this);

        return $this->render_from_template('core/auth_digital_minor_page', $context);
    }

    /**
     * Renders a progress bar.
     *
     * Do not use $OUTPUT->render($bar), instead use progress_bar::create().
     *
     * @param  progress_bar $bar The bar.
     * @return string HTML fragment
     */
    public function render_progress_bar(progress_bar $bar) {
        $data = $bar->export_for_template($this);
        return $this->render_from_template('core/progress_bar', $data);
    }

    /**
     * Renders element for a toggle-all checkbox.
     *
     * @param \core\output\checkbox_toggleall $element
     * @return string
     */
    public function render_checkbox_toggleall(\core\output\checkbox_toggleall $element) {
        return $this->render_from_template($element->get_template(), $element->export_for_template($this));
    }
}

/**
 * A renderer that generates output for command-line scripts.
 *
 * The implementation of this renderer is probably incomplete.
 *
 * @copyright 2009 Tim Hunt
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 2.0
 * @package core
 * @category output
 */
class core_renderer_cli extends core_renderer {

    /**
     * Returns the page header.
     *
     * @return string HTML fragment
     */
    public function header() {
        return $this->page->heading . "\n";
    }

    /**
     * Renders a Check API result
     *
     * To aid in CLI consistency this status is NOT translated and the visual
     * width is always exactly 10 chars.
     *
     * @param result $result
     * @return string HTML fragment
     */
    protected function render_check_result(core\check\result $result) {
        $status = $result->get_status();

        $labels = [
            core\check\result::NA        => '      ' . cli_ansi_format('<colour:gray>' ) . ' NA ',
            core\check\result::OK        => '      ' . cli_ansi_format('<colour:green>') . ' OK ',
            core\check\result::INFO      => '    '   . cli_ansi_format('<colour:blue>' ) . ' INFO ',
            core\check\result::UNKNOWN   => ' '      . cli_ansi_format('<colour:grey>' ) . ' UNKNOWN ',
            core\check\result::WARNING   => ' '      . cli_ansi_format('<colour:black><bgcolour:yellow>') . ' WARNING ',
            core\check\result::ERROR     => '   '    . cli_ansi_format('<bgcolour:red>') . ' ERROR ',
            core\check\result::CRITICAL  => ''       . cli_ansi_format('<bgcolour:red>') . ' CRITICAL ',
        ];
        $string = $labels[$status] . cli_ansi_format('<colour:normal>');
        return $string;
    }

    /**
     * Renders a Check API result
     *
     * @param result $result
     * @return string fragment
     */
    public function check_result(core\check\result $result) {
        return $this->render_check_result($result);
    }

    /**
     * Returns a template fragment representing a Heading.
     *
     * @param string $text The text of the heading
     * @param int $level The level of importance of the heading
     * @param string $classes A space-separated list of CSS classes
     * @param string $id An optional ID
     * @return string A template fragment for a heading
     */
    public function heading($text, $level = 2, $classes = 'main', $id = null) {
        $text .= "\n";
        switch ($level) {
            case 1:
                return '=>' . $text;
            case 2:
                return '-->' . $text;
            default:
                return $text;
        }
    }

    /**
     * Returns a template fragment representing a fatal error.
     *
     * @param string $message The message to output
     * @param string $moreinfourl URL where more info can be found about the error
     * @param string $link Link for the Continue button
     * @param array $backtrace The execution backtrace
     * @param string $debuginfo Debugging information
     * @return string A template fragment for a fatal error
     */
    public function fatal_error($message, $moreinfourl, $link, $backtrace, $debuginfo = null, $errorcode = "") {
        global $CFG;

        $output = "!!! $message !!!\n";

        if ($CFG->debugdeveloper) {
            if (!empty($debuginfo)) {
                $output .= $this->notification($debuginfo, 'notifytiny');
            }
            if (!empty($backtrace)) {
                $output .= $this->notification('Stack trace: ' . format_backtrace($backtrace, true), 'notifytiny');
            }
        }

        return $output;
    }

    /**
     * Returns a template fragment representing a notification.
     *
     * @param string $message The message to print out.
     * @param string $type    The type of notification. See constants on \core\output\notification.
     * @return string A template fragment for a notification
     */
    public function notification($message, $type = null) {
        $message = clean_text($message);
        if ($type === 'notifysuccess' || $type === 'success') {
            return "++ $message ++\n";
        }
        return "!! $message !!\n";
    }

    /**
     * There is no footer for a cli request, however we must override the
     * footer method to prevent the default footer.
     */
    public function footer() {}

    /**
     * Render a notification (that is, a status message about something that has
     * just happened).
     *
     * @param \core\output\notification $notification the notification to print out
     * @return string plain text output
     */
    public function render_notification(\core\output\notification $notification) {
        return $this->notification($notification->get_message(), $notification->get_message_type());
    }
}


/**
 * A renderer that generates output for ajax scripts.
 *
 * This renderer prevents accidental sends back only json
 * encoded error messages, all other output is ignored.
 *
 * @copyright 2010 Petr Skoda
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 2.0
 * @package core
 * @category output
 */
class core_renderer_ajax extends core_renderer {

    /**
     * Returns a template fragment representing a fatal error.
     *
     * @param string $message The message to output
     * @param string $moreinfourl URL where more info can be found about the error
     * @param string $link Link for the Continue button
     * @param array $backtrace The execution backtrace
     * @param string $debuginfo Debugging information
     * @return string A template fragment for a fatal error
     */
    public function fatal_error($message, $moreinfourl, $link, $backtrace, $debuginfo = null, $errorcode = "") {
        global $CFG;

        $this->page->set_context(null); // ugly hack - make sure page context is set to something, we do not want bogus warnings here

        $e = new stdClass();
        $e->error      = $message;
        $e->errorcode  = $errorcode;
        $e->stacktrace = NULL;
        $e->debuginfo  = NULL;
        $e->reproductionlink = NULL;
        if (!empty($CFG->debug) and $CFG->debug >= DEBUG_DEVELOPER) {
            $link = (string) $link;
            if ($link) {
                $e->reproductionlink = $link;
            }
            if (!empty($debuginfo)) {
                $e->debuginfo = $debuginfo;
            }
            if (!empty($backtrace)) {
                $e->stacktrace = format_backtrace($backtrace, true);
            }
        }
        $this->header();
        return json_encode($e);
    }

    /**
     * Used to display a notification.
     * For the AJAX notifications are discarded.
     *
     * @param string $message The message to print out.
     * @param string $type    The type of notification. See constants on \core\output\notification.
     */
    public function notification($message, $type = null) {}

    /**
     * Used to display a redirection message.
     * AJAX redirections should not occur and as such redirection messages
     * are discarded.
     *
     * @param moodle_url|string $encodedurl
     * @param string $message
     * @param int $delay
     * @param bool $debugdisableredirect
     * @param string $messagetype The type of notification to show the message in.
     *         See constants on \core\output\notification.
     */
    public function redirect_message($encodedurl, $message, $delay, $debugdisableredirect,
                                     $messagetype = \core\output\notification::NOTIFY_INFO) {}

    /**
     * Prepares the start of an AJAX output.
     */
    public function header() {
        // unfortunately YUI iframe upload does not support application/json
        if (!empty($_FILES)) {
            @header('Content-type: text/plain; charset=utf-8');
            if (!core_useragent::supports_json_contenttype()) {
                @header('X-Content-Type-Options: nosniff');
            }
        } else if (!core_useragent::supports_json_contenttype()) {
            @header('Content-type: text/plain; charset=utf-8');
            @header('X-Content-Type-Options: nosniff');
        } else {
            @header('Content-type: application/json; charset=utf-8');
        }

        // Headers to make it not cacheable and json
        @header('Cache-Control: no-store, no-cache, must-revalidate');
        @header('Cache-Control: post-check=0, pre-check=0', false);
        @header('Pragma: no-cache');
        @header('Expires: Mon, 20 Aug 1969 09:23:00 GMT');
        @header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        @header('Accept-Ranges: none');
    }

    /**
     * There is no footer for an AJAX request, however we must override the
     * footer method to prevent the default footer.
     */
    public function footer() {}

    /**
     * No need for headers in an AJAX request... this should never happen.
     * @param string $text
     * @param int $level
     * @param string $classes
     * @param string $id
     */
    public function heading($text, $level = 2, $classes = 'main', $id = null) {}
}



/**
 * The maintenance renderer.
 *
 * The purpose of this renderer is to block out the core renderer methods that are not usable when the site
 * is running a maintenance related task.
 * It must always extend the core_renderer as we switch from the core_renderer to this renderer in a couple of places.
 *
 * @since Moodle 2.6
 * @package core
 * @category output
 * @copyright 2013 Sam Hemelryk
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class core_renderer_maintenance extends core_renderer {

    /**
     * Initialises the renderer instance.
     *
     * @param moodle_page $page
     * @param string $target
     * @throws coding_exception
     */
    public function __construct(moodle_page $page, $target) {
        if ($target !== RENDERER_TARGET_MAINTENANCE || $page->pagelayout !== 'maintenance') {
            throw new coding_exception('Invalid request for the maintenance renderer.');
        }
        parent::__construct($page, $target);
    }

    /**
     * Does nothing. The maintenance renderer cannot produce blocks.
     *
     * @param block_contents $bc
     * @param string $region
     * @return string
     */
    public function block(block_contents $bc, $region) {
        return '';
    }

    /**
     * Does nothing. The maintenance renderer cannot produce blocks.
     *
     * @param string $region
     * @param array $classes
     * @param string $tag
     * @return string
     */
    public function blocks($region, $classes = array(), $tag = 'aside') {
        return '';
    }

    /**
     * Does nothing. The maintenance renderer cannot produce blocks.
     *
     * @param string $region
     * @return string
     */
    public function blocks_for_region($region) {
        return '';
    }

    /**
     * Does nothing. The maintenance renderer cannot produce a course content header.
     *
     * @param bool $onlyifnotcalledbefore
     * @return string
     */
    public function course_content_header($onlyifnotcalledbefore = false) {
        return '';
    }

    /**
     * Does nothing. The maintenance renderer cannot produce a course content footer.
     *
     * @param bool $onlyifnotcalledbefore
     * @return string
     */
    public function course_content_footer($onlyifnotcalledbefore = false) {
        return '';
    }

    /**
     * Does nothing. The maintenance renderer cannot produce a course header.
     *
     * @return string
     */
    public function course_header() {
        return '';
    }

    /**
     * Does nothing. The maintenance renderer cannot produce a course footer.
     *
     * @return string
     */
    public function course_footer() {
        return '';
    }

    /**
     * Does nothing. The maintenance renderer cannot produce a custom menu.
     *
     * @param string $custommenuitems
     * @return string
     */
    public function custom_menu($custommenuitems = '') {
        return '';
    }

    /**
     * Does nothing. The maintenance renderer cannot produce a file picker.
     *
     * @param array $options
     * @return string
     */
    public function file_picker($options) {
        return '';
    }

    /**
     * Does nothing. The maintenance renderer cannot produce and HTML file tree.
     *
     * @param array $dir
     * @return string
     */
    public function htmllize_file_tree($dir) {
        return '';

    }

    /**
     * Overridden confirm message for upgrades.
     *
     * @param string $message The question to ask the user
     * @param single_button|moodle_url|string $continue The single_button component representing the Continue answer.
     * @param single_button|moodle_url|string $cancel The single_button component representing the Cancel answer.
     * @return string HTML fragment
     */
    public function confirm($message, $continue, $cancel) {
        // We need plain styling of confirm boxes on upgrade because we don't know which stylesheet we have (it could be
        // from any previous version of Moodle).
        if ($continue instanceof single_button) {
            $continue->primary = true;
        } else if (is_string($continue)) {
            $continue = new single_button(new moodle_url($continue), get_string('continue'), 'post', true);
        } else if ($continue instanceof moodle_url) {
            $continue = new single_button($continue, get_string('continue'), 'post', true);
        } else {
            throw new coding_exception('The continue param to $OUTPUT->confirm() must be either a URL' .
                                       ' (string/moodle_url) or a single_button instance.');
        }

        if ($cancel instanceof single_button) {
            $output = '';
        } else if (is_string($cancel)) {
            $cancel = new single_button(new moodle_url($cancel), get_string('cancel'), 'get');
        } else if ($cancel instanceof moodle_url) {
            $cancel = new single_button($cancel, get_string('cancel'), 'get');
        } else {
            throw new coding_exception('The cancel param to $OUTPUT->confirm() must be either a URL' .
                                       ' (string/moodle_url) or a single_button instance.');
        }

        $output = $this->box_start('generalbox', 'notice');
        $output .= html_writer::tag('h4', get_string('confirm'));
        $output .= html_writer::tag('p', $message);
        $output .= html_writer::tag('div', $this->render($continue) . $this->render($cancel), array('class' => 'buttons'));
        $output .= $this->box_end();
        return $output;
    }

    /**
     * Does nothing. The maintenance renderer does not support JS.
     *
     * @param block_contents $bc
     */
    public function init_block_hider_js(block_contents $bc) {
        // Does nothing.
    }

    /**
     * Does nothing. The maintenance renderer cannot produce language menus.
     *
     * @return string
     */
    public function lang_menu() {
        return '';
    }

    /**
     * Does nothing. The maintenance renderer has no need for login information.
     *
     * @param null $withlinks
     * @return string
     */
    public function login_info($withlinks = null) {
        return '';
    }

    /**
     * Secure login info.
     *
     * @return string
     */
    public function secure_login_info() {
        return $this->login_info(false);
    }

    /**
     * Does nothing. The maintenance renderer cannot produce user pictures.
     *
     * @param stdClass $user
     * @param array $options
     * @return string
     */
    public function user_picture(stdClass $user, array $options = null) {
        return '';
    }
}
