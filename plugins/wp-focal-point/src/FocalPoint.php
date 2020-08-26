<?php

namespace Bonnier\WP\FocalPoint;

use WP_Post;

class FocalPoint
{
    const FIELD = 'focal_point';
    private static $instance;

    private $pluginUrl;

    private function __construct()
    {
        $this->pluginUrl = plugin_dir_url(__DIR__);
        add_action('admin_enqueue_scripts', [$this, 'registerScripts']);
        add_filter('attachment_fields_to_edit', [$this, 'addFields'], 11, 2);
        add_filter('attachment_fields_to_save', [$this, 'saveFields'], 11, 2);
        add_filter('pll_copy_post_metas', [$this, 'copy_post_metas'], 10, 5);
    }

    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public function addFields($formFields, WP_Post $post = null)
    {
        if (!$post) {
            return $formFields;
        }
        if (preg_match("/image/", $post->post_mime_type)) {
            $meta = get_post_meta($post->ID, '_' . static::FIELD, true);

            $formFields[static::FIELD] = [
                'label' => 'Focal point',
                'input' => 'text',
                'value' => $meta
            ];
        }

        return $formFields;
    }

    public function saveFields($post, $attachment)
    {
        if (isset($attachment[static::FIELD])) {
            if (!preg_match('/[0,1]\.[0-9]{1,2}\,[0,1]\.[0-9]{1,2}/', $attachment[static::FIELD])) {
                $post['errors'][static::FIELD]['errors'][] = 'Invalid focal point format';
            } else {
                update_post_meta($post['ID'], '_' . static::FIELD, $attachment[static::FIELD]);
            }
        }

        return $post;
    }

    public function registerScripts($hook)
    {
        if ('post.php' !== $hook || !wp_attachment_is_image()) {
            return;
        }

        wp_register_script('focal_point_script', $this->pluginUrl . 'assets/js/focal_point.js');
        wp_localize_script('focal_point_script', 'assets', [
            'crosshair' => $this->pluginUrl . 'assets/img/crosshair.png',
        ]);
        wp_enqueue_script('focal_point_script');
    }

    public function copy_post_metas($metas) {
        return array_merge($metas, array( '_focal_point'));
    }
}
