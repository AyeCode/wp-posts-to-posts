<?php

if (!defined('ABSPATH')) {
    exit;
}

class P2P_Connections_Page_Add {
    public function __construct() {
        add_action('acf/init', [$this, 'add_acf_options_page']);
        add_action('acf/init', [$this, 'register_acf_fields']);
        add_action('acf/save_post', [$this, 'save_connection_type'], 20);
    }

    public function add_acf_options_page() {
        if (function_exists('acf_add_options_page')) {
            acf_add_options_page([
                'page_title' => 'Posts 2 Posts Connections',
                'menu_title' => 'Add P2P Connection',
                'menu_slug' => 'p2p-add-connections',
                'capability' => 'manage_options',
                'parent_slug' => 'p2p-connections',
            ]);
        }
    }

    public function register_acf_fields() {
        if (function_exists('acf_add_local_field_group')) {
            acf_add_local_field_group([
                'key' => 'group_p2p_connections',
                'title' => 'P2P Post Connection Velden',
                'fields' => [
                    [
                        'key' => 'field_post_type_from',
                        'label' => 'Post type van',
                        'name' => 'post_type_from',
                        'type' => 'text',
                        'default_value' => 'post',
                        'required' => true,
                    ],
                    [
                        'key' => 'field_post_type_to',
                        'label' => 'Post type naar',
                        'name' => 'post_type_to',
                        'type' => 'text',
                        'default_value' => 'post',
                        'required' => true,
                    ]
                ],
                'location' => [
                    [
                        [
                            'param' => 'options_page',
                            'operator' => '==',
                            'value' => 'p2p-add-connections',
                        ],
                    ],
                ],
            ]);
        }
    }

    public function save_connection_type() {
        global $wpdb;

        // Only run on our options page
        if (!isset($_POST['_acf_post_id']) || $_POST['_acf_post_id'] !== 'options') {
            return;
        }

        $connection_name = get_field('post_type_from', 'option') . '_' . get_field('post_type_to', 'option');
        $post_type_from = get_field('post_type_from', 'option');
        $post_type_to = get_field('post_type_to', 'option');

        // Validate that we have all required fields
        if (empty($connection_name) || empty($post_type_from) || empty($post_type_to)) {
            return;
        }

        // Insert into wp_p2p_connection_types table
        $table_name = $wpdb->prefix . 'p2p_connection_types';
        
        $result = $wpdb->insert(
            $table_name,
            [
                'connection_name' => $connection_name,
                'post_type_from' => $post_type_from,
                'post_type_to' => $post_type_to
            ],
            ['%s', '%s', '%s']
        );

        if ($result) {
            // Clear the ACF fields after successful save
            update_field('connection_name', '', 'option');
            update_field('post_type_from', 'post', 'option');
            update_field('post_type_to', 'post', 'option');

            // Add admin notice
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible"><p>Connection type saved successfully!</p></div>';
            });
        }
    }
}

new P2P_Connections_Page_Add();