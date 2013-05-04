<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * @file
 *
 * A rewrite URL script for Print Press
 */
if (!class_exists('wp_pp_rewrite')) {
    /*
     * Rewrite URL Class
     */
    class wp_pp_rewrite
    {

        function __construct()
        {
            add_action('init', array(&$this, 'add_query_var'));
            // Set the print variable to become matchable
            add_filter('request', array(&$this, 'set_variable'));
            // Replace page output when print is matched
            add_filter('template_redirect', array(&$this, 'print_page'));
        }

        /**
         * Loads the print page template if the print variable is matched
         */
        public function print_page($q)
        {
            if (get_query_var('print') === 'true') {
                $render = new wp_pp_render();
                $render->print_page();
                // Stop WordPress from loading more functions
                exit;
            }
        }

        /**
         * Add 'print' to the query variable array.
         * @param array $q
         * @return array
         */
        public function add_query_var()
        {
            add_rewrite_tag('%print%','true');
        }

        /**
         * This function detects if the print variable was called, and set it to
         * true. This is required because the "get_query_variable" doesn't
         * accept empty variables
         *
         * @param array $vars An array of query variables
         * @return array return the array after setting the print variable
         */
        public function set_variable($vars)
        {
            if (isset($vars['print'])) {
                $vars['print'] = 'true';
            }
            return $vars;
        }

    }
}

$rewrite = new wp_pp_rewrite();