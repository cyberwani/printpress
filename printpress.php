<?php
/**
 * PrintPress - Generates a printable version of your WordPress posts and pages.
 *
 * @author Abid Omar
 * @version 1.1
 * @package Main
 */
/*
  Plugin Name: PrintPress
  Plugin URI: http://costartpress.com
  Description: Generates a printable version of your WordPress posts and pages. (Free Version)
  Author: Abid Omar
  Author URI: http://omarabid.com
  Version: 1.1
  Text Domain: wp-pp
  License: GPLv3
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('wp_pp')) {
    /**
     * The main class and start-up point of the plug-in
     */
    class wp_pp
    {

        /**
         * The WordPress Plug-in version.
         *
         * @var string
         */
        public $version = "1.1";

        /**
         * The minimal required WordPress version for this plug-in to function
         * correctly
         *
         * @var string
         */
        public $wp_version = "3.5";

        /**
         * @var string
         */
        static $inc_images = 'On';

        /**
         * @var string
         */
        static $print_comments = 'On';

        /**
         * @var string
         */
        static $print_button = 'On';

        /**
         * @var string
         */
        static $paragraph_wrap = '';

        /**
         * @var string
         */
        static $date_format = 'Y-m-d \a\t H:i:s';

        /**
         * @var
         */
        static $smart_permalink = 'On';


        /**
         * The print page template HTML Code
         * @var string
         */
        static $post_html = '
<!doctype html>
<html>
    <head>
        <link rel="stylesheet" type="text/css" href="{{@plugin_path}}css/reset.css" />
        <link rel="stylesheet" type="text/css" href="{{@plugin_path}}css/style.css" />
        <style type="text/css">
          {{@css}}
        </style>
    </head>
    
    <body>
        <div id="header_ad">
            <img src="ad.png"/>
        </div>
        <div id="container">
            <div id="header_article">
                <h1>{{@title}}</h1>
                <div id="info">
                    <ul>
                        <li><span class="bold">Author: </span> {{@author_firstname}} {{@author_lastname}}</li>
                        <li><span class="bold">Published: </span> {{@date}}</li>
                    </ul>
                </div>
            <div class="clear"></div>
            </div>
            <div id="content">
                {{@content}}
            </div>
        </div>
        <div id="comments">
        <span id="comments-count">Comments ({{@comment_count}})</span>
          {{@comments}}
        </div>
        <div id="footer">
            <!-- Custom Footer -->
            <p></p>
        </div>
    </body>
</html>';

        /**
         * @var string
         */
        static $post_css = '
/*
You can add here additional CSS rules, or overwrite the existing ones
*/';
        /**
         * @var string
         */
        static $comments_html = '
<div class="comment">
    <div class="comment-author">
        {{@gravatar}}
        <cite>{{@author}}</cite>
        <span>Says:</span>
        <div class="comment-meta">
            <span class="date">{{@date}}</span>
        </div>
    </div>
    <p>{{@comment}}</p>
</div>';

        /**
         * @var string
         */
        static $comments_gravatar = 'On';

        /**
         * @var string
         */
        static $comments_gravatar_size = '32';

        /**
         * @var string
         */
        static $comments_date_format = 'Y-m-d \a\t H:i:s';

        /**
         * @var
         */
        static $button_html = '<a href="{{@print_url}}">
    <img style="float:right; padding:10px;" src="{{@print_icon_url}}" />
</a>';

        /**
         * Construct and start the plug-in class
         */
        public function __construct()
        {
            //
            // 1. Plug-in requirements
            //
            if (!$this->check_requirements()) {
                return;
            }

            //
            // 2. Declare constants and load dependencies
            //
            $this->define_constants();
            $this->load_dependencies();

            //
            // 3. Hooks
            //
            register_activation_hook(__FILE__, array(&$this, 'activate'));
            register_deactivation_hook(__FILE__, array(&$this, 'deactivate'));
            register_uninstall_hook(__FILE__, 'wp_pp::uninstall');

            //
            // 4. Load Widget
            //
            add_action('widgets_init', create_function('', 'register_widget("pp_widget");'));

            //
            // 5. i18n
            //
            add_action('init', array(&$this, 'i18n'));

            //
            // 6. Actions
            //
            add_action('plugins_loaded', array(&$this, 'start'));

            //
            // 7. Tracking
            //
            add_action('admin_init', array(&$this, 'tracking'));
        }

        /**
         * Checks that the WordPress setup meets the plugin requirements
         * @global string $wp_version
         * @return boolean
         */
        private function check_requirements()
        {
            global $wp_version;
            if (!version_compare($wp_version, $this->wp_version, '>=')) {
                add_action('admin_notices', 'wp_pp::display_req_notice');
                return false;
            }
            return true;
        }

        /**
         * Display the requirement notice
         * @static
         */
        static function display_req_notice()
        {
            global $wp_pp;
            echo '<div id="message" class="error"><p><strong>';
            echo __('Sorry, BootstrapPress re requires WordPress ' . $wp_pp->wp_version . ' or higher.
            Please upgrade your WordPress setup', 'wp-pp');
            echo '</strong></p></div>';
        }

        /**
         * This function define constants that are needed across the plug-in.
         */
        private function define_constants()
        {
            /* [printpress_free/printpress.php] */
            define('PP_BASENAME', plugin_basename(__FILE__));
            /* [/devl/dev/wp-content/plugins/printpress_free] */
            define('PP_DIR', dirname(__FILE__));
            /* [printpress_free] */
            define('PP_FOLDER', plugin_basename(dirname(__FILE__)));
            /* [/devl/dev/wp-content/plugins/printpress_free/] */
            define('PP_ABSPATH', trailingslashit(str_replace("\\", "/", WP_PLUGIN_DIR . '/' . plugin_basename(dirname(__FILE__)))));
            /* [http://bootstrappress.com/dev/wp-content/plugins/printpress/] */
            define('PP_URLPATH', trailingslashit(WP_PLUGIN_URL . '/' . plugin_basename(dirname(__FILE__))));
            /* [http://bootstrappress.com/dev/wp-admin/] */
            define('PP_ADMINPATH', get_admin_url());
        }

        /**
         * This function load other PHP files that required by the plug-in
         */
        private function load_dependencies()
        {
            // Admin Panel
            if (is_admin()) {
                require_once('admin/forms.php');
                require_once('admin/admin.php');
            }
            // Front-End Site
            require_once('print_button.php');
            require_once('print_widget.php');
            require_once('rewrite_engine.php');
            require_once('template_render.php');
        }

        /**
         * This function is called everytime the plug-in is activated.
         */
        public function activate()
        {
            /* Check if the plug-in is running for the first time */
            if (!get_option('printpress_installed')) {
                /* Set Default settings */
                update_option('printpress_settings', array('inc_images' => self::$inc_images, 'print_comments' => self::$print_comments, 'print_button' => self::$print_button, 'paragraph_wrap' => self::$paragraph_wrap, 'date_format' => self::$date_format));
                update_option('printpress_post', array('post_html' => self::$post_html, 'post_css' => self::$post_css));
                update_option('printpress_comments', array('comments_html' => self::$comments_html, 'comments_gravatar' => self::$comments_gravatar, 'comments_gravatar_size' => self::$comments_gravatar_size, 'comments_date_format' => self::$comments_date_format));
                update_option('printpress_button', array('button_html' => self::$print_button));
                update_option('printpress_installed', true);
            }
            // Refresh rewrite rules
            self::flush_rewrite_rules();
        }

        /**
         * This function is called when the plug-in is deactivated.
         */
        public function deactivate()
        {
            flush_rewrite_rules();
        }

        /**
         * This function is called when the plug-in is uninstalled
         */
        static function uninstall()
        {
            //flush_rewrite_rules();
            delete_option('printpress_installed');
            delete_option('printpress_settings');
            delete_option('prinpress_post');
            delete_option('printpress_comments');
        }

        static function flush_rewrite_rules()
        {
            add_action('init', 'wp_pp::proxy_flush_rewrite_rules');
        }

        static function proxy_flush_rewrite_rules()
        {
            add_rewrite_endpoint('print', EP_ALL);
            flush_rewrite_rules();
        }

        /**
         * Internationalization
         */
        public function i18n()
        {
            load_plugin_textdomain('wp-pp', false, basename(dirname(__FILE__)) . '/lang/');
        }

        /**
         * This function is responsible for starting the plug-in
         */
        public function start()
        {

        }

        /**
         * Return the Print Button Content
         *
         * @param $content
         * @return string
         */
        public function print_button($content)
        {
            global $post;
            $permalink = post_permalink($post->ID);
            if ($permalink[strlen($permalink) - 1] === '/') {
                $link = post_permalink($post->ID) . 'print';
            } else {
                $link = post_permalink($post->ID) . '&print';
            }
            $print_button = '
<a href="' . $link . '">
    <img style="float:right; padding:10px;" src="' . PP_URLPATH . 'css/print_button.png" />
</a>
';
            return $print_button . $content;
        }

        /**
         * Usage Tracking code
         */
        public function tracking()
        {

            // PressTrends Account API Key
            $api_key = 'c1le7evp66kcn23g12rn9px0iuwchgysu13j';
            $auth = 'kmi2iyfxiyttsewiquzzn4c7s1loxrbyf';

            // Start of Metrics
            global $wpdb;
            $data = get_transient('presstrends_cache_data');
            if (!$data || $data == '') {
                $api_base = 'http://api.presstrends.io/index.php/api/pluginsites/update/auth/';
                $url = $api_base . $auth . '/api/' . $api_key . '/';

                $count_posts = wp_count_posts();
                $count_pages = wp_count_posts('page');
                $comments_count = wp_count_comments();

                // wp_get_theme was introduced in 3.4, for compatibility with older versions, let's do a workaround for now.
                if (function_exists('wp_get_theme')) {
                    $theme_data = wp_get_theme();
                    $theme_name = urlencode($theme_data->Name);
                } else {
                    $theme_data = get_theme_data(get_stylesheet_directory() . '/style.css');
                    $theme_name = $theme_data['Name'];
                }

                $plugin_name = '&';
                foreach (get_plugins() as $plugin_info) {
                    $plugin_name .= $plugin_info['Name'] . '&';
                }
                // CHANGE __FILE__ PATH IF LOCATED OUTSIDE MAIN PLUGIN FILE
                $plugin_data = get_plugin_data(__FILE__);
                $posts_with_comments = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_type='post' AND comment_count > 0");
                $data = array(
                    'url' => stripslashes(str_replace(array('http://', '/', ':'), '', site_url())),
                    'posts' => $count_posts->publish,
                    'pages' => $count_pages->publish,
                    'comments' => $comments_count->total_comments,
                    'approved' => $comments_count->approved,
                    'spam' => $comments_count->spam,
                    'pingbacks' => $wpdb->get_var("SELECT COUNT(comment_ID) FROM $wpdb->comments WHERE comment_type = 'pingback'"),
                    'post_conversion' => ($count_posts->publish > 0 && $posts_with_comments > 0) ? number_format(($posts_with_comments / $count_posts->publish) * 100, 0, '.', '') : 0,
                    'theme_version' => $plugin_data['Version'],
                    'theme_name' => $theme_name,
                    'site_name' => str_replace(' ', '', get_bloginfo('name')),
                    'plugins' => count(get_option('active_plugins')),
                    'plugin' => urlencode($plugin_name),
                    'wpversion' => get_bloginfo('version'),
                );

                foreach ($data as $k => $v) {
                    $url .= $k . '/' . $v . '/';
                }
                wp_remote_get($url);
                set_transient('presstrends_cache_data', $data, 60 * 60 * 24);
            }
        }

    }

}

/*
 * Creates a new instance of the PrintPress Class
 */
global $wp_pp;
$wp_pp = new wp_pp();
?>