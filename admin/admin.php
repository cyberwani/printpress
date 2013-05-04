<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * @file
 *
 * The Admin Panel and all administration related tasks are handled in this
 * file.
 */
if (!class_exists('wp_pp_admin')) {
    class wp_pp_admin
    {

        /**
         * Creates the admin panel
         */
        public function __construct()
        {
            /*
             * 1. Admin Menu
             */
            add_action('admin_menu', array(&$this, 'admin_menu'));

            /*
             * 2. Load Scripts and Styles
             */
            if (isset($_GET['page']) && ($_GET['page'] == 'printpress-edit' || $_GET['page'] == 'printpress-settings')) {
                add_action('admin_print_scripts', array(&$this, 'load_scripts'));
                add_action('admin_print_styles', array(&$this, 'load_styles'));
            }

            /*
             * 3. Generate Settings and template forms
             */
            add_action('admin_init', array(&$this, 'settings_form'));

            /*
             * 4. Execute any action
             */
            $this->action_hook();

            /*
             * 5. Contextual help
             */
            add_filter('contextual_help', array(&$this, 'showhelp'));
        }

        /**
         * This function inserts the plug-in menu to the WordPress menu
         */
        public function admin_menu()
        {
            global $printpress_settings;
            global $printpress_edit;
            // Create a top menu page
            add_menu_page('Print Press Settings', 'PrintPress', 'manage_options', 'printpress-settings', array(&$this, 'menu_hook'), PP_URLPATH . 'admin/files/menu_icon.png');

            // Create Submenus
            $printpress_settings = add_submenu_page('printpress-settings', 'Print Press || Settings', 'Settings', 'manage_options', 'printpress-settings', array(&$this, 'menu_hook'));
        }

        /**
         * This function routes the different admin pages
         */
        public function menu_hook()
        {
            switch ($_GET['page']) {
                case 'printpress-settings':
                default:
                    ?>
                    <div class="wrap" xmlns="http://www.w3.org/1999/html">
                        <div class="icon32" id="icon-options-general"><br></div>
                        <?php
                        if (isset($_GET['action']) && $_GET['action'] === 'flush') {
                            ?>
                            <div class="updated">
                                <p>
                                    <strong><?php _e('Rewrite Rules flushed', 'wp-pp'); ?></strong>
                                </p>
                            </div>
                            <?php
                        }
                        ?>
                        <h2>PrintPress Settings</h2>

                        <form action="options.php" method="post">
                            <?php settings_fields('printpress_settings'); ?>
                            <?php do_settings_sections('printpress_settings_form'); ?>
                            <input type="hidden" name="_wp_http_referer"
                                   value="<?php echo admin_url('admin.php?page=printpress-settings'); ?>"/>

                            <p class="submit">
                                <input name="Submit" type="submit" class="button-primary"
                                       value="<?php esc_attr_e('Save Changes'); ?>"/>
                                <a id="reset_default" class="button-secondary"
                                   href="?page=printpress-settings&action=reset"><?php esc_attr_e('Reset Default'); ?></a>
                            </p>
                        </form>
                    </div>
                    <?php
                    break;
            }
        }

        /**
         * This function load the scripts used by the Admin Panel
         */
        public function load_scripts()
        {
            wp_enqueue_script('textarea', PP_URLPATH . 'admin/files/jquery.textarea.js', array('jquery'));
            wp_enqueue_script('core', PP_URLPATH . 'admin/files/admin.js', array('jquery'));
        }

        /**
         * This function load the styles used by the Admin Panel
         */
        public function load_styles()
        {

        }

        /**
         * This function declares the different forms, sections and fields.
         */
        public function settings_form()
        {
            /*
             * Settings Page
             */
            register_setting('printpress_settings', 'printpress_settings', array(&$this, 'validate'));
            // General Settings
            add_settings_section('general_section', 'General settings', 'wp_pp_forms::section_description', 'printpress_settings_form');
            add_settings_field('inc_images', 'Include Images and Video', 'wp_pp_forms::checkbox', 'printpress_settings_form', 'general_section', array('id' => 'inc_images', 'option' => 'inc_images', 'tab' => 'settings', 'text' => ''));
            add_settings_field('print_comments', 'Include Comments', 'wp_pp_forms::checkbox', 'printpress_settings_form', 'general_section', array('id' => 'print_comments', 'option' => 'print_comments', 'tab' => 'settings', 'text' => ''));
            add_settings_field('print_button', 'Insert Print button in posts', 'wp_pp_forms::checkbox', 'printpress_settings_form', 'general_section', array('id' => 'print_button', 'option' => 'print_button', 'tab' => 'settings', 'text' => ''));
            add_settings_field('paragraph_wrap', 'Wrap paragraph in P element', 'wp_pp_forms::checkbox', 'printpress_settings_form', 'general_section', array('id' => 'paragraph_wrap', 'option' => 'paragraph_wrap', 'tab' => 'settings', 'text' => ''));
            add_settings_field('process_shortcodes', 'Process Shortcodes', 'wp_pp_forms::checkbox', 'printpress_settings_form', 'general_section', array('id' => 'process_shortcodes', 'option' => 'process_shortcodes', 'tab' => 'settings', 'text' => ''));
            add_settings_field('date_format', 'Date Format', 'wp_pp_forms::textbox', 'printpress_settings_form', 'general_section', array('id' => 'date_format', 'option' => 'date_format', 'tab' => 'settings', 'text' => ''));
            // Rewrite Settings
            add_settings_section('rewrite_section', 'Rewrite settings', 'wp_pp_forms::section_description', 'printpress_settings_form');
            add_settings_field('smart_permalink', 'Enable Smart Permalink', 'wp_pp_forms::checkbox', 'printpress_settings_form', 'rewrite_section', array('id' => 'smart_permalink', 'option' => 'smart_permalink', 'tab' => 'settings', 'text' => 'This will work only if you have Permalinks enabled in WordPress'));
            add_settings_field('refresh_button', 'Flush Rewrite rules', 'wp_pp_forms::button', 'printpress_settings_form', 'rewrite_section', array('name' => 'refresh_btn', 'action' => 'flush', 'value' => 'Refresh'));

        }

        /**
         * This functions validate the submitted user input.
         * @param Array $var
         * @return type
         */
        public function validate($var)
        {
            return $var;
        }

        public function action_hook()
        {
            if (!isset($_GET['action'])) {
                return;
            }
            switch ($_GET['action']) {
                case 'reset':
                    if (isset($_GET['page']) && $_GET['page'] === 'printpress-settings') {
                        update_option('printpress_settings', array('inc_images' => wp_pp::$inc_images, 'print_comments' => wp_pp::$print_comments, 'print_button' => wp_pp::$print_button, 'paragraph_wrap' => wp_pp::$paragraph_wrap, 'date_format' => wp_pp::$date_format));
                    }
                    break;
                case 'flush':
                    wp_pp::flush_rewrite_rules();
                    break;
            }
        }


        /**
         * This function displays the top bar scrollable help for each page
         */
        public function showhelp()
        {
            global $printpress_settings;
            global $printpress_edit;
            $screen = get_current_screen();
            switch ($screen->id) {
                case $printpress_settings:
                    $screen->add_help_tab(array(
                        'id' => 'my_help_tab',
                        'title' => __('Usage', 'wp-pp'),
                        'content' => __("
    <h2>Print Press Settings</h2>
    <h3>General Settings</h3>
    <ul>
    <li><strong>Include Images and Video</strong>: Check this to include images 
        videos in the print page
    </li>
    <li><strong>Include Comments</strong>: Include the post comments to be
        printed with the blog post.
    </li>
    <li><strong>Insert Print button in posts</strong>: Insert a Print Image
    button in the right position of every post.
    </li>
    <li><strong>Wrap paragraph in P element</strong>: Automatically detect 
        paragraphs and wrap them in a <p></p> element. Don't check this if you
        are manually formatting your post.
    </li>
    <li><strong>Process Shortcodes</strong>: Convert Shortcodes in the print page.</li>
    <li><strong>Date format</strong>: Customize the post date format.</li>
    </ul>
    <h3>Rewrite Settings</h3>
    <ul>
    <li><strong>Enable Smart Permalink</strong>: Enable the /print endpoint instead of query strings</li>
    <li><strong>Refresh Button</strong>: Refresh the rewrite rules.</li>
    </ul>
    ", 'wp-pp')));
                    break;
            }
        }
    }
}

$pp_print = new wp_pp_admin();