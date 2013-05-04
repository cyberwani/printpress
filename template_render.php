<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * @file
 *
 * Template rendering class
 */
if (!class_exists('wp_pp_render')) {
    /**
     * This class render the print page template
     */
    class wp_pp_render
    {

        /**
         * @var
         */
        private $template;

        /**
         *
         */
        function __construct()
        {
            $this->render_post();
            $this->render_comments();
        }

        /**
         *
         */
        public function print_page()
        {
            echo $this->template;
        }

        /**
         *
         */
        private function render_post()
        {
            global $post;
            $post_template = get_option('printpress_post');
            $settings = get_option('printpress_settings');
            $author_info = get_userdata($post->post_author);
            /* Load the template from the settings */
            $this->template = $post_template['post_html'];
            /* Replace the variables */
            // Post Variables
            $this->replace_mod('title', $post->post_title);
            $this->replace_mod('content', $this->filter_media($post->post_content));
            $this->replace_mod('comment_count', $post->comment_count);
            $this->replace_mod('date', date_format(date_create($post->post_date), $settings['date_format']));
            $this->replace_mod('permalink', $post->guid);
            // Author Variables
            $this->replace_mod('author', $author_info->user_login);
            $this->replace_mod('author_firstname', $author_info->first_name);
            $this->replace_mod('author_lastname', $author_info->last_name);
            // Plug-in Variables
            $this->replace_mod('plugin_path', PP_URLPATH);

            /* Put the additional CSS code */
            $this->replace_mod('css', $post_template['post_css']);
            /* Check if comments are included */
            if (isset($settings['print_comments']) && $settings['print_comments']) {
                $this->replace_mod('comments', $this->render_comments());
            } else {
                $this->replace_mod('comments', '');
            }
        }

        /**
         * @return string
         */
        private function render_comments()
        {
            global $post;
            /* Get the post comments in an array */
            $comments = get_comments(array('post_id' => $post->ID));
            $comments_html = '';
            /* Create the comments HTML code from the Template */
            foreach ($comments as $comment) {
                $comments_html .= $this->create_comment($comment);
            }
            return $comments_html;
        }

        /**
         * @param $comment
         * @return mixed
         */
        private function create_comment($comment)
        {
            $comments_template = get_option('printpress_comments');
            $comment_html = $comments_template['comments_html'];
            /* Replace the variables */
            $this->replace_mod('author', $comment->comment_author, $comment_html);
            $this->replace_mod('date', date_format(date_create($comment->comment_date), $comments_template['comments_date_format']), $comment_html);
            $this->replace_mod('comment', $comment->comment_content, $comment_html);
            if (isset($comments_template['comments_gravatar']) && $comments_template['comments_gravatar']) {
                $this->replace_mod('gravatar', get_avatar($comment->comment_author_email, $comments_template['comments_gravatar_size']), $comment_html);
            } else {
                $this->replace_mod('gravatar', '', $comment_html);
            }
            return $comment_html;
        }

        /**
         * This functions replace a variable in a string {{@variable}}
         *
         * @param string $txt The text where the search will be made
         * @param string $var The variable to replace
         * @param string $content The replacement content
         */
        private function replace_mod($var, $content, &$txt = 'template')
        {
            if ($txt === 'template') {
                $this->template = str_replace('{{@' . $var . '}}', $content, $this->template);
            } else {
                $txt = str_replace('{{@' . $var . '}}', $content, $txt);
            }
        }

        /**
         * @param $content
         * @return mixed|string
         */
        private function filter_media($content)
        {
            $settings = get_option('printpress_settings');
            // Process Shortcodes if processing is enabled
            if (isset($settings['process_shortcodes']) && $settings['process_shortcodes']) {
                $content = do_shortcode($content);
            }
            /* Remove Images and Videos */
            if (!$settings['inc_images']) {
                /* Remove images */
                $content = preg_replace('/<img[^>]+\>/i', '', $content);
                /* Remove Videos */
                $content = preg_replace('/<video[^>]+\>/i', '', $content);
            }
            /* Wrap Paragraph in P element */
            if (isset($settings['paragraph_wrap']) && $settings['paragraph_wrap']) {
                $content = '<p>' . implode("</p>\n\n<p>", preg_split('/\n(?:\s*\n)+/', $content)) . '</p>';
            }
            return $content;
        }
    }
}