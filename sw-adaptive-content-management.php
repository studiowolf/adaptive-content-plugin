<?php
/*
Plugin Name: SW Adaptive Content Management
Plugin URI: http://www.studiowolf.nl/
Description: Extensions to add adaptive content management functions to adapt Wordpress to the adaptive content principle.
Version: 2.0.1 beta
Author: Studio Wolf
Author URI: http://www.studiowolf.nl/
License: For unlimited single website usage, do not copy
Copyright: Studio Wolf (hallo@studiowolf.com)
*/


class SWAdaptiveContentManagement {

    var $plugin_path;
    var $plugin_url;
    var $plugin_file;
    var $template_path;
    var $version;
    var $settings;


    /**
     * Constructor
     *
     * @since  1.0
     */

    function __construct()
    {
        $this->plugin_path = plugin_dir_path( __FILE__ );
        $this->plugin_url = plugins_url('',__FILE__);
        $this->plugin_file = __FILE__;
        $this->template_path = $this->plugin_path . "templates/";
        $this->version = "2.1.0";

        // Set default settings
        $this->settings = array(
            // Disable ACM per post type, empty value means not disabled
            'acm_post_types' => array(),
            // Global field settings
            'global_field_settings' => array(

                // Global ACM field settings, boolean: show or hide
                'acm_fields' => array(
                    'sub_headline' => true,
                    'teaser' => true,
                    'thumbnail' => true,
                    'images' => true,
                    'files' => true,
                    'videos' => true
                ),
            ),
            // Settings per post type, these overrule the global settings
            // The post type settings are the same as the global settings
            // False or empty means no specific post type settings
            'post_type_field_settings' => array()
        );

        // Load only if admin
        if(is_admin()) add_action('init', array($this, 'init'), 9999);
        add_action('admin_init', array($this, 'admin_init'));

        $this->load_modules();
    }


    /**
     * Load the plugin modules
     *
     * @since  1.0
     */

    function load_modules()
    {
        // Bootstrap admin modules
        if(is_admin()) {
            // Load controllers
            require_once('controllers/contentEditing.php');
            require_once('controllers/users.php');
            require_once('controllers/roles.php');

            // Load fields
            require_once('fields/metafields_acf4.php');

            // Bootstrap controllers
            new SWContentEditing($this);
            new SWUsers($this);
            new SWRoles($this);

            // // Bootstrap fields
            new SWAcf4MetaFields($this);
        }

        require_once('controllers/shortTags.php');
        new SWShortTags($this);
    }


    /**
     * Basic styles and scrips for admin
     *
     * @since  1.3
     */

    function admin_init()
    {
        // Register javascripts
        $scripts = array(
            'acm-submitbox' => $this->plugin_url . '/javascript/acm-submitbox.js',
        );

        foreach($scripts as $k => $v) {
            wp_register_script($k, $v, array('jquery'), $this->version);
        }

        // Register stylesheets
        $styles = array(
            'acm-submitbox' => $this->plugin_url . '/css/submitbox.css',
        );

        foreach($styles as $k => $v) {
            wp_register_style($k, $v, false, $this->version);
        }
    }


    /**
     * Basic settings, loaded as late as possible
     *
     * Hooks into the init function of Wordpress to create basic functionality for
     * the plugin to work.
     *
     * @since 1.2
     */

    function init()
    {
        // Enable all visisble post types for ACM
        $post_types = get_post_types(array('public' => true));
        foreach($post_types as $post_type) {
            $this->settings['acm_post_types'][$post_type] = true;
        }

        // Disable for attachments and acf
        unset($this->settings['acm_post_types']['attachment']);
        unset($this->settings['acm_post_types']['acf']);

        // hook filter to change the acm post types
        $this->settings['acm_post_types'] = apply_filters('sw_acm_post_types', $this->settings['acm_post_types']);

        // Filter global settings
        $this->settings['global_field_settings'] = apply_filters('sw_acm_global_field_settings', $this->settings['global_field_settings']);

        // Pass global settings to all post type specific fields
        foreach($this->settings['acm_post_types'] as $key => $value) {
            $this->settings['post_type_field_settings'][$key] = $this->settings['global_field_settings'];

            if($key == 'page') {
                // Remove thumbnail field from page post type
                unset($this->settings['post_type_field_settings']['page']['acm_fields']['thumbnail']);
            }

            // Filter for specific post type field
            $this->settings['post_type_field_settings'][$key] = apply_filters('sw_acm_field_settings-' . $key, $this->settings['post_type_field_settings'][$key]);
        }


    }
}

new SWAdaptiveContentManagement();

// Load API
require_once('api.php');