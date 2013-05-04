<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('pp_widget')) {
    /**
     * AdPress Widget
     */
    class pp_widget extends WP_Widget
    {

        /**
         * Constructor
         *
         * Registers the widget details with the parent class
         */
        function __construct()
        {
            // widget actual processes
            parent::__construct($id = 'pp_widget', $name = 'PrintPress', $options = array('description' => __('Displays the PrintPress button', 'wp-pp')));
        }

        /**
         * Creates a form in the theme widgets page
         * @param $instance
         */
        function form($instance)
        {
            // outputs the options form on admin
            if ($instance) {
                $title = esc_attr($instance['title']);
            } else {
                $title = __('Print Page', 'wp-pp');
            }
            ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">Title</label><br/>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
                   name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>"/>
        </p>
        <?php
        }

        /**
         * Update the form on submit
         * @param $new_instance
         * @param $old_instance
         * @return array
         */
        function update($new_instance, $old_instance)
        {
            $instance = $old_instance;
            $instance['title'] = strip_tags($new_instance['title']);
            return $instance;
        }

        /**
         * Displays the widget
         * @param $args
         * @param $instance
         */
        function widget($args, $instance)
        {
            // Extract the content of the widget
            extract($args);
            $title = apply_filters('widget_title', $instance['title']);

            // Before Widget
            echo $before_widget;

            // Displays the title
            if ($title) {
                echo $before_title . $title . $after_title;
            }

            // Displays the print button
            //TODO: Print Button
            // After Widget
            echo $after_widget;
        }

    }
}