<?php

/**
 *  SW Roles controller
 *
 *  @package     SW Adaptive Content Management
 *  @subpackage  SW Roles controller
 *  @copyright   Studio Wolf
 *  @license     Studio Wolf
 *  @since       1.0
 */

class SWRoles
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

        // Activation/deactivation hooks
        register_activation_hook($this->parent->plugin_file, array(&$this, 'activation'));
        register_deactivation_hook($this->parent->plugin_file, array(&$this, 'deactivation'));

        add_filter('editable_roles', array(&$this, 'editable_roles'));
        add_filter('map_meta_cap', array(&$this, 'map_meta_cap'), 10, 4);
        add_action('admin_init', array(&$this, 'admin_init'));
    }


    /**
     * Rename admin roles to more understandable roles
     *
     * @since  1.0
     */

    function admin_init()
    {
        global $wp_roles;

        if(isset($wp_roles->roles['contributor'])) {
            $wp_roles->roles['contributor']['name'] = 'Medewerker';    
            $wp_roles->role_names['contributor'] = 'Medewerker';
        }

        if(isset($wp_roles->roles['subscriber'])) {
            $wp_roles->roles['subscriber']['name'] = 'Lid';
            $wp_roles->role_names['subscriber'] = 'Lid';
        }

        if(isset($wp_roles->roles['administrator'])) {
            $wp_roles->roles['administrator']['name'] = 'Studio Wolf';
            $wp_roles->role_names['administrator'] = 'Studio Wolf';    
        }
        
        if(isset($wp_roles->roles['managing_editor'])) {
            $wp_roles->roles['managing_editor']['name'] = 'Beheerder';
            $wp_roles->role_names['managing_editor'] = 'Beheerder';
        }
        
    }


    /**
     * Other users than administrators are not able to edit/create administrators
     *
     * @param  array $roles the roles available
     * @return array of roles that are editable
     * @since  1.0
     */

    function editable_roles( $roles )
    {
        if(isset($roles['administrator']) && !current_user_can('administrator')) {
            unset($roles['administrator']);
        }
        return $roles;
    }


    /**
     * Don't allow users other than admin to delete or edit an admin
     *
     * @since  1.0
     */

    function map_meta_cap($caps, $cap, $user_id, $args)
    {
        switch($cap) {
            case 'edit_user':
            case 'remove_user':
            case 'promote_user':
                if(isset($args[0]) && $args[0] == $user_id) {
                    break;
                }
                elseif(!isset($args[0])) {
                    $caps[] = 'do_not_allow';
                }
                $other = new WP_User(absint($args[0]));
                if($other->has_cap('administrator')) {
                    if(!current_user_can('administrator')) {
                        $caps[] = 'do_not_allow';
                    }
                }
                break;
            case 'delete_user':
            case 'delete_users':

                if(!isset($args[0])) {
                    break;
                }
                $other = new WP_User( absint($args[0]) );
                if($other->has_cap('administrator')) {
                    if(!current_user_can('administrator')) {
                        $caps[] = 'do_not_allow';
                    }
                }
                break;
            default:

                break;
        }
        return $caps;
    }


    /**
     * Edit roles and capabilities on plugin activation
     *
     * @since  1.0
     */

    function activation()
    {
        global $wp_roles;

        // Get the editor role
        $editor = $wp_roles->get_role('editor');

        // Remove page publishing/deleting capabilities from an editor
        // DISABLED REVIEW OPTION
        // $editor->remove_cap('delete_published_pages');
        // $editor->remove_cap('publish_pages');
        // $editor->remove_cap('delete_others_pages');

        // Add the managing editor role with default editor capabilities
        add_role('managing_editor', 'Hoofdredacteur', $editor->capabilities);

        // Get the managing editor role
        $managing_editor = get_role('managing_editor');

        // Add the user create/edit/delete capabilities if managing editor
        if(!empty($managing_editor)) {
            $managing_editor->add_cap('delete_published_pages');
            $managing_editor->add_cap('delete_others_pages');
            $managing_editor->add_cap('create_users');
            $managing_editor->add_cap('list_users');
            $managing_editor->add_cap('edit_users');
            $managing_editor->add_cap('delete_users');
            $managing_editor->add_cap('promote_users');
        }
    }


    /**
     * Restore Wordpress in original situation when plugin is deactivated
     *
     * @since  1.0
     */

    function deactivation()
    {
        global $wp_roles;

        // Restore editor role to normal capabilities
        $editor = $wp_roles->get_role('editor');
        // DISABLED REVIEW OPTION
        $editor->add_cap('delete_published_pages');
        $editor->add_cap('publish_pages');
        $editor->add_cap('delete_others_pages');

        // Remove the managing editor role
        remove_role('managing_editor');
    }
}