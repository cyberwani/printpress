<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('wp_pp_button')) {
    class wp_pp_button
    {
        function __construct()
        {
            // Register Shortcode
            add_shortcode('printpress_button', array(&$this, 'register_shortcode'));
            // Insert Print Button
            $this->insert_button();
        }

        /**
         * @param $atts
         * @param null $content
         * @return string
         */
        public function register_shortcode($atts, $content = null)
        {
            return self::print_button();
        }

        /**
         *
         */
        public function insert_button()
        {
            $settings = get_option('printpress_settings');
            // Display a print button
            if (isset($settings['print_button'])) {
                add_filter('the_content', 'wp_pp_button::insert_button_filter');
            }
        }

        /**
         * @param $content
         * @return mixed
         */
        static function insert_button_filter($content)
        {
            $content = self::print_button() . $content;
            return $content;
        }

        /**
         * @return string
         */
        static function print_button()
        {
            $settings = get_option('printpress_button');
            if (isset($settings['button_html'])) {
                $btn = $settings['button_html'];
            } else {
                $btn = wp_pp::$button_html;
            }
            $btn = self::replace_mod('print_url', self::get_print_url(), $btn);
            $btn = self::replace_mod('print_icon_url', PP_URLPATH . 'css/print_button.png', $btn);
            return $btn;
        }

        /**
         * @return string
         */
        static function get_print_url()
        {
            global $post;
            $settings = get_option('printpress_settings');
            if (get_option('permalink_structure') != '' && isset($settings['smart_permalink'])) {
                $link = post_permalink($post->ID) . 'print';
            } else {
                $link = self::addQuery(post_permalink($post->ID), 'print');
            }
            return $link;
        }

        /**
         * @param $url
         * @param $query
         * @return string
         */
        static function addQuery($url, $query)
        {
            $cache = parse_url($url, PHP_URL_QUERY);
            if (empty($cache)) {
                return $url . "?" . $query;
            } else {
                return $url . "&" . $query;
            }
        }

        /**
         * This functions replace a variable in a string {{@variable}}
         *
         * @param string $var The variable to replace
         * @param string $content The replacement content
         * @param string $txt The text where the search will be made
         * @return string
         */
        static function replace_mod($var, $content, $txt)
        {
            $txt = str_replace('{{@' . $var . '}}', $content, $txt);
            return $txt;
        }
    }
}

new wp_pp_button();