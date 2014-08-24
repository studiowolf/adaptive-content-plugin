<?php

/**
 *  SW Content Editing controller
 *
 *  @package     SW Adaptive Content Management
 *  @subpackage  SW Content editing controller
 *  @copyright   Studio Wolf
 *  @license     Studio Wolf
 *  @since       1.0
 *
 *  @todo Filter text before it is saved (ingore linebreaks etc.)
 *  @todo Give editors a message if something is changed
 *  @todo Remove HTML bar / Only for non-admins, see on_init function
 *  @todo Build option that review to Studio Wolf can be disabled, Zorg ervoor dat de contact persoon de reviewmail krijgt
 *  @todo Quality link ter review "Waarom?"
 *  @todo Review mail: send mail met afzender van de loggedin user
 */


class SWContentEditing
{
    var $parent;


    /**
     * Constructor
     *
     * Set hooks
     *
     * @since  1.0
     * @param  $parent SWAdapativeContentManagement object
     */

    function __construct($parent)
    {
        $this->parent = $parent;

        add_action('admin_init', array($this, 'admin_init'));

        add_filter('enter_title_here', array($this, 'enter_title_here'));
        add_action('post_submitbox_misc_actions', array($this,'post_submitbox_misc_actions'));
        add_filter('page_row_actions', array($this, 'page_row_actions'), 10, 2);
        add_action('admin_print_scripts', array($this, 'admin_print_scripts'));
        add_action('admin_print_styles', array($this, 'admin_print_styles'));
        add_action('before_delete_post', array($this, 'before_delete_post'));
        add_action('save_post', array($this, 'save_post'));
        add_action('_wp_post_revision_fields', array($this, '_wp_post_revision_fields'));
        add_action('edit_form_after_title', array($this, 'edit_form_after_title'));

        // ACF 5 Option page filter
        add_filter('acf/settings/options_page', array($this, 'acf5_options_page_settings'));

        // ACF 4 Option page filter
        add_filter('acf/options_page/settings', array($this, 'acf4_options_page_settings'));

        // ACF 5
        // Add the option pages
        if(is_admin()) {
            // acf_add_options_page(array(
            //     'page_title'    => 'Organisatie',
            //     'menu_title'    => 'Organisatie',
            //     'menu_slug'     => 'acm-organisation',
            //     'capability'    => 'edit_posts',
            //     'position'      => 100,
            //     'icon_url'      => false
            // ));

            // acf_add_options_page(array(
            //     'page_title'    => 'Social media',
            //     'menu_title'    => 'Social media',
            //     'menu_slug'     => 'acm-social-media',
            //     'capability'    => 'edit_posts',
            //     'position'      => 101,
            //     'icon_url'      => false
            // ));

            // acf_add_options_page(array(
            //     'page_title'    => 'Technisch',
            //     'menu_title'    => 'Technisch',
            //     'menu_slug'     => 'acm-technical',
            //     'capability'    => 'edit_posts',
            //     'position'      => 102,
            //     'icon_url'      => false
            // ));
        }
    }


    /**
     * Customize the post submitbox
     *
     * @since   1.2
     * @return  void
     */

    function post_submitbox_misc_actions()
    {
        global $typenow;
        global $post;

        if($typenow != 'page') return;

        // Display in navigation, get the meta variable, and set the template
        $display_in_navigation = get_post_meta($post->ID, 'show_in_navigation', true);

        // If the meta key isn't set, set display in navigation to true
        // wordpress returns an empty string if the meta value is non existant
        if($display_in_navigation === "") {
            $display_in_navigation = 1;
        }

        set_query_var('display_in_navigation', $display_in_navigation);
        load_template($this->parent->template_path . "/submitbox_show_in_navigation.php");

        // Get all the locked pages
        $locked_pages = get_option('locked_pages');

        // Lock pages only for admins
        if(current_user_can('administrator')) {

            if(isset($locked_pages[$post->ID])) {
                $locked_page = $locked_pages[$post->ID];
            } else {
                $locked_page = false;
            }

            // Load the template
            set_query_var('locked_page', $locked_page);
            load_template($this->parent->template_path . "/submitbox_lock_page.php");
        } else {
            // If a user isn't allowed to delte pages, remove the options and
            // show a message. If current page is a concept, it can be removed
            if(isset($locked_pages[$post->ID]) ||
                (!current_user_can('delete_published_pages') && !in_array($post->post_status, array('draft', 'auto-draft')))) {
                load_template($this->parent->template_path . "/submitbox_remove_trash_link.php");
            }
        }
    }


    /**
     * Remove delete action from page list when the page is locked
     *
     * @param  array  $actions  the actions one can perform
     * @param  object $post     $the post object
     * @return array  new list of actions
     * @since  1.2
     */

    function page_row_actions($actions, $post)
    {
        $locked_pages = get_option('locked_pages');
        if(isset($locked_pages[$post->ID]) && !current_user_can('administrator')) {
            unset($actions["trash"]);
        }
        return $actions;
    }


    /**
     * Change editing function when Wordpress admin is loaded
     *
     * @since  1.0
     */

    function admin_init()
    {
        if (is_plugin_active('advanced-custom-fields-pro/acf.php') || is_plugin_active('advanced-custom-fields/acf.php')) {

            // Remomve media buttons, we have special fields for that
            remove_all_actions('media_buttons');
            add_action('media_buttons', array($this, 'media_buttons'));
        } else {
            add_action('admin_notices', array($this, 'notice_acf_not_installed'));
        }

        // When a user is not allowed to publish posts, e-mail the contact
        // person with a review request
        add_action('save_post', array($this, 'save_post'));
        add_action('admin_notices', array($this, 'notice_save_post'));

        // Do this for all taxonomies
        $taxonomies = get_taxonomies(array(
            'show_ui' => true,
            'public' => true,
        ));

        foreach($taxonomies as $taxonomy) {
            add_action($taxonomy . '_edit_form_fields', array($this, 'taxonomy_edit_form_fields'));
            add_action($taxonomy . '_add_form_fields', array($this, 'taxonomy_add_form_fields'));
            add_action('edit_' . $taxonomy, array($this, 'edit_taxonomy'));
            add_action('create_' . $taxonomy, array($this, 'edit_taxonomy'));
            add_action('delete_' . $taxonomy, array($this, 'delete_taxonomy'));
            add_filter($taxonomy . '_row_actions', array($this, 'taxonomy_row_actions'), 10, 2);
            add_filter('bulk_actions-edit-' . $taxonomy, array($this, 'bulk_actions_edit_taxonomy'));
        }

    }


    /**
     * Do actions while saving post data
     *
     * @param  int $post_id the ID of the corresponding post
     * @since  1.0
     * @todo   candidate for rewrite and performance improvement
     * @todo   rule 170: build check to see if reference already exists
     * @todo   moving to sub private functions to keep the overview
     */

    function save_post($post_id)
    {
        global $typenow;

        // This function is only used for pages now
        // Do not continue if it is a post revision or autosave
        if($typenow != 'page' || wp_is_post_revision($post_id)) return $post_id;
        if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return $post_id;
        if(!isset($_POST['post_status'])) return $post_id;

        $original_post_status = $_POST['original_post_status'];
        $post_status = $_POST['post_status'];
        $post_type = $_POST['post_type'];

        // Create review taks if post type is page, status is pending,
        // and previous status was not pending, to prevent email bomb

        // DISABLED REVIEW OPTION
        // if($post_type == 'page' && $post_status == 'pending' && $original_post_status != 'pending') {
        //     $post_title = $_POST['post_title'];

        //     $headers = 'From: Wordpress <wordpress@studiowolf.nl>' . "\r\n";
        //     $to = 'tim@studiowolf.nl';
        //     $subject = 'WORDPRESS: Verzoek tot pagereview '. get_bloginfo('name');
        //     $message = '<strong>Paginatitel</strong>: ' . $post_title .
        //         '<br/><strong>Overzichtlink</strong>: ' . get_bloginfo('wpurl') . '/wp-admin/edit.php?post_type=page' .
        //         '<br/><strong>Postlink</strong>: ' . get_bloginfo('wpurl') . '/wp-admin/post.php?post=' . $_POST['post_ID'] . '&action=edit';

        //     wp_mail($to, $subject, $message, $headers);

        //     // Set the message to be viewed next page view
        //     $message = '<div class="updated"><p><strong>Pagina is ter review verstuurd naar Studio Wolf (Tim Sluis). Reactie volgt binnen een werkdag.</strong> <a href="" target="_blank">Waarom?</a></p></div>';
        //     set_transient('notice_save_post', $message, 60);
        // }

        // Store the locked, reference and navigation state of a page
        if($post_type == 'page') {

            // Store show in navigation preferences
            if(isset($_POST['post_navigation'])) {
                update_post_meta($post_id, 'show_in_navigation', $_POST['post_navigation']);
            }

            // Administrator functions only
            if(current_user_can('administrator')) {
                // Store locked page preferences
                // Get the locked_pages option
                $locked_pages = get_option('locked_pages', array());

                // Store the locked page in an associative array
                if(isset($_POST['post_locked'])) {
                    if($reference = $_POST['post_locked_reference']) {

                        // Sluggify the reference
                        $locked_pages[$post_id] = sanitize_title($reference);
                    } else {
                        $locked_pages[$post_id] = true;
                    }
                } else {
                    // Remove lock if existant
                    unset($locked_pages[$post_id]);
                }
                update_option('locked_pages', $locked_pages);
            }
        }
        return $post_id;
    }


    /**
     * Wordpress doesn't store custom meta fields when native WP fields are not
     * changed when doing autosafe
     * Force storing of revisions for custom meta fields
     *
     * @since  1.5
     */

    function _wp_post_revision_fields($fields) {
       $fields["debug_preview"] = "debug_preview";
       return $fields;
    }


    /**
     * Add an extra field to fix preview for custom meta fields
     *
     * @since  1.5
     */

    function edit_form_after_title() {
       echo '<input type="hidden" name="debug_preview" value="debug_preview">';
    }


    /**
     * If page is deleted, then remove it from the locked page array
     *
     * @param  int $post_id the id of the post
     * @return return the id of the post
     * @since  1.2
     */

    function before_delete_post($post_id)
    {
        // Check if the post type is page
        global $typenow;
        if($typenow != 'page') $post_id;

        $locked_pages = get_option('locked_pages');

        // If the page exists in the array, remove it from the locked_pages
        if(isset($locked_pages[$post_id])) {
            unset($locked_pages[$post_id]);
            update_option('locked_pages', $locked_pages);
        }
        return $post_id;
    }


    /**
     * Display admin notice when post is saved if needed
     *
     * @since  1.0
     * @todo   see if we can improve this message system
     */

    function notice_save_post()
    {
        // Check if there is a message waiting
        if($message = get_transient('notice_save_post')) {
            echo $message;
            delete_transient('notice_save_post');
        }
    }


    /**
     * Admin notice when ACF is not installed
     *
     * @since 1.0
     */

    function notice_acf_not_installed()
    {
        echo '<div class="error"><p>Advanced Custom Fields (Pro) is nodig om SW Content Management in te schakelen.</p></div>';
    }


    /**
     * Change placeholder text in post title bar
     *
     * @param  string $title the original title
     * @return string the renewed title
     * @since  1.1
     */

    function enter_title_here($title)
    {
        return $title = 'Voer hier de korte titel in';
    }


    /**
     * Hook for printing javascripts
     *
     * Scripts are registered before and can now be enqeueud
     *
     * @since   1.2
     * @return  void
     * @todo    only print on pages
     */

    function admin_print_scripts()
    {
        //if(!$this->validate_page()) return;

        wp_enqueue_script(array(
            'acm-submitbox',
        ));
    }


    /**
     * Hook for printing stylesheets
     *
     * Styles are registered before and can now be enqeueud
     *
     * @since   1.2
     * @return  void
     */

    function admin_print_styles()
    {
        wp_enqueue_style(array(
            'acm-submitbox',
        ));
    }


    /**
     * Rename the ACF options page
     *
     * @param  string $title the title of the page
     * @return string the title of the page
     * @since  1.4
     */
    function acf5_options_page_settings($settings)
    {
        return false;
        print_r($settings);
        $settings['menu_title'] = 'Alg. opties';

        return $settings;
        $settings['title'] = 'Algemeen';
        $settings['pages'] = array('Organisatie', 'Social media', 'Technisch');

        return $settings;
    }


    /**
     * Rename the ACF options page
     *
     * @param  string $title the title of the page
     * @return string the title of the page
     * @since  2.1
     */
    function acf4_options_page_settings($settings)
    {
        $settings['title'] = 'Algemeen';
        $settings['pages'] = array('Organisatie', 'Social media', 'Technisch');

        return $settings;
    }


    /**
     * Added ACM media buttons to the editor
     *
     * @since 1.4.1
     */

    function media_buttons()
    {
        global $post_type;

        // Only show the buttons that need to be shown
        $post_type_field_settings = $this->parent->settings['post_type_field_settings'];

        if(isset($post_type_field_settings[$post_type])) {
            $acm_fields = $post_type_field_settings[$post_type]['acm_fields'];

            $media_buttons['image'] = (isset($acm_fields['images'])) ? 1 : 0;
            $media_buttons['video'] = (isset($acm_fields['videos'])) ? 1 : 0;
            $media_buttons['file'] = (isset($acm_fields['files'])) ? 1 : 0;

            // Apply so the visibility of the buttons can be programatically altered
            $media_buttons = apply_filters('sw_acm_media_buttons', $media_buttons);

            if($media_buttons['image']) {
                echo '<a href="#" class="button" data-editor="content" onmousedown=\'window.send_to_editor("[afbeelding]");\'>Afbeelding-tag invoegen</a>';
            }
            if($media_buttons['video']) {
                echo '<a href="#" class="button" data-editor="content" onmousedown=\'window.send_to_editor("[video]");\'>Video-tag invoegen</a>';
            }
            if($media_buttons['file']) {
                echo '<a href="#" class="button" data-editor="content" onmousedown=\'window.send_to_editor("[bestand]");\'>Bestand-tag invoegen</a>';
            }
            //echo '<small>Hiermee geef je aan waar je de foto\'s en video\'s wilt plaatsen die je onderaan bij media hebt toegevoegd.</small>';
        }
    }


    /**
     * If a edit term window is opened, show lock fields for admins
     *
     * @param  term $term
     * @since  1.8
     */

    function taxonomy_edit_form_fields($term)
    {
        //Only available for the admin
        if(current_user_can('administrator')) {

            //Get all the locked pages
            $locked_terms = get_option('locked_terms');
            if(isset($locked_terms[$term->term_id])) {
                $locked_term = $locked_terms[$term->term_id];
            } else {
                $locked_term = false;
            }

            // Load the template
            set_query_var('locked_term', $locked_term);
            load_template($this->parent->template_path . "/edit_lock_term.php");
        }
    }


    /**
     * If a new term window is opened, show lock fields for admins
     *
     * @since  1.8
     */

    function taxonomy_add_form_fields()
    {
        if(current_user_can('administrator')) {
            load_template($this->parent->template_path . "/add_lock_term.php");
        }
    }


    /**
     * If term is edited, check if the lock status has changed
     *
     * @param  int $term_id the id of the term
     * @since  1.8
     */

    function edit_taxonomy($term_id)
    {
        // Administrator functions only
        if(current_user_can('administrator')) {
            // Store locked terms preferences
            // Get the locked_terms option
            $locked_terms = get_option('locked_terms', array());

            // Store the locked terms in an associative array
            if(isset($_POST['term_locked'])) {
                if($reference = $_POST['term_locked_reference']) {

                    // Sluggify the reference
                    $locked_terms[$term_id] = sanitize_title($reference);
                } else {
                    $locked_terms[$term_id] = true;
                }
            } else {
                // Remove lock if existant
                unset($locked_terms[$term_id]);
            }
            update_option('locked_terms', $locked_terms);
        }
    }


    /**
     * If term is deleted, then remove it from the locked terms array
     *
     * @param  int $term_id the id of the term
     * @since  1.8
     */

    function delete_taxonomy($term_id)
    {

        $locked_terms = get_option('locked_terms');

        // If the term exists in the array, remove it from the locked_terms
        if(isset($locked_terms[$term_id])) {
            unset($locked_terms[$term_id]);
            update_option('locked_terms', $locked_terms);
        }
    }


    /**
     * Remove delete action from term list when the term is locked
     *
     * @param  array  $actions  the actions one can perform
     * @param  object $term     $the term object
     * @return array  new list of actions
     * @since  1.8
     */

    function taxonomy_row_actions($actions, $term)
    {
        $locked_terms = get_option('locked_terms');
        if(isset($locked_terms[$term->term_id]) && !current_user_can('administrator')) {
            unset($actions["delete"]);
        }
        return $actions;
    }


    /**
     * Remove bulk delete action from term list when not admin
     *
     * @param  array  $actions  the actions one can perform
     * @return array  new list of actions
     * @since  1.8
     */

    function bulk_actions_edit_taxonomy($actions)
    {
        if(!current_user_can('administrator')) {
            unset($actions["delete"]);
        }
        return $actions;
    }

}