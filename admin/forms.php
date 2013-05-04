<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * @file
 *
 * This class draws the form fields HTML Code. It's a collection of static
 * functions that can be used without initiation. They were wrapped in a class
 * for encapsulation.
 */
if (!class_exists('wp_pp_forms')) {
    class wp_pp_forms
    {
        /**
         * Outputs a checkbox.
         *
         * @param array $arr
         * Accepts an array that contains the ID, name of the checkbox to output.
         * An optional text can be also added.
         */
        static function checkbox($arr)
        {
            $option = get_option('printpress_' . $arr['tab']);
            if (isset($option[$arr['option']])) {
                $option = $option[$arr['option']];
            } else {
                $option = '';
            }
            $checked = '';
            if ($option) {
                $checked = ' checked="checked" ';
            }
            if (!isset($arr['text'])) {
                $arr['text'] = '';
            }
            echo '<input name="printpress_' . $arr['tab'] . '[' . $arr['option'] . ']" id="' . $arr['id'] . '"' . $checked . ' type="checkbox" > ' . $arr['text'];
        }

        /**
         * Outputs a text area
         * @param array $arr
         * Accepts an array that contains the ID, name, tab, and option.
         */
        static function textarea($arr)
        {
            $option = get_option('printpress_' . $arr['tab']);
            $option = $option[$arr['option']];
            echo '<textarea style="height:250px; width:100%; font: 12px/1.4em Courier, monospace;" id="' . $arr['id'] . '" name="printpress_' . $arr['tab'] . '[' . $arr['option'] . ']">' . $option . '</textarea>';
        }

        /**
         * Outputs a textbox
         * @param array $arr
         * Accepts an array with ID, tab, option and default value
         */
        static function textbox($arr)
        {
            $option = get_option('printpress_' . $arr['tab']);
            $option = $option[$arr['option']];
            if (!isset($arr['text'])) {
                $arr['text'] = '';
            }
            echo '<input name="printpress_' . $arr['tab'] . '[' . $arr['option'] . ']" id="' . $arr['id'] . '" type="text" value="' . $option . '"/>' . $arr['text'];
        }

        /**
         * Outputs a link with a button style
         * @param array $arr
         * Accepts an array that contains the name, action and value
         */
        static function button($arr)
        {
            $name = $arr['name'];
            $action = $arr['action'];
            $value = $arr['value'];
            echo '<a name="' . $name . '" class="button-primary" href="' . admin_url('admin.php?page=printpress-settings') . '&action=' . $action . '"/>' . $value . '</a>';
        }

        /**
         * Outputs a section description
         *
         * @param string $var The section description
         */
        static function section_description($var)
        {
            switch ($var['id']) {
                case 'general_section':
                    _e('Check the box to enable the feature', 'wp-pp');
                    break;
                case 'post_section':
                    _e('Here, you can edit the post print page template. Please refer to the documentation for more information.', 'wp-pp');
                    break;
                case 'comments_section':
                    _e('Here, you can edit the comments print page template. Please refer to the documentation for more information.', 'wp-pp');
                    break;
                default:
                    break;
            }
        }

        /**
         * This function returns the structure of the template page tabs.
         *
         * @return Array an array representation of the template page tabs
         */
        static function tabs()
        {
            $tabs = array(
                'post' => __('Post Template','wp-pp'),
                'comments' => __('Comments Template', 'wp-pp'),
                'button' => __('Button Template', 'wp-pp')
            );
            return $tabs;
        }

    }
}