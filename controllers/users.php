<?php

/**
 *  SW Users controller
 *
 *  @package     SW Adaptive Content Management
 *  @subpackage  SW Users controller
 *  @copyright   Studio Wolf
 *  @license     Studio Wolf
 *  @since       1.0
 */

class SWUsers
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

        add_filter('user_contactmethods', array($this, 'user_contactmethods'));
        add_filter('init', array($this, 'init_acf4'));
    }


    /**
     * Change user contact methods
     *
     * @param  array $contact_methods current user contact methods
     * @return array customized user contact methods
     * @since  1.0
     */

    function user_contactmethods($contact_methods)
    {
        $contact_methods['twitter'] = 'Twitter';
        $contact_methods['facebook'] = 'Facebook';
        $contact_methods['linkedin'] = 'LinkedIn';
        $contact_methods['dribbble'] = 'Dribbble';
        $contact_methods['phone'] = 'Telefoon';

        unset($contact_methods['yim']);
        unset($contact_methods['aim']);
        unset($contact_methods['jabber']);

        return $contact_methods;
    }

    /**
     * Add team fields to the user profile page
     *
     * @since  2.1
     */

    function init_acf4()
    {
        if(function_exists("register_field_group")) {
            register_field_group(array (
                'id' => '508ff3c3f0442',
                'title' => 'Teamlid',
                'fields' =>
                array (
                    0 =>
                    array (
                        'key' => 'field_508fe0fcbe200',
                        'label' => 'Tonen op website',
                        'name' => 'show',
                        'type' => 'true_false',
                        'instructions' => '',
                        'required' => '0',
                        'message' => 'Teamlid tonen op de website?',
                        'order_no' => '0',
                    ),
                    1 =>
                    array (
                        'key' => 'field_50896a9d52da2',
                        'label' => 'Functietitel',
                        'name' => 'job_title',
                        'type' => 'text',
                        'instructions' => '',
                        'required' => '0',
                        'default_value' => '',
                        'formatting' => 'none',
                        'order_no' => '1',
                    ),
                    2 =>
                    array (
                        'key' => 'field_508fe506ddbc1',
                        'label' => 'Functie-omschrijving',
                        'name' => 'job_description',
                        'type' => 'text',
                        'instructions' => 'Functieomschrijving in maximaal 15 woorden.',
                        'required' => '0',
                        'default_value' => '',
                        'formatting' => 'html',
                        'order_no' => '2',
                    ),
                    3 =>
                    array (
                        'key' => 'field_508fe57a95746',
                        'label' => 'Foto',
                        'name' => 'photo',
                        'type' => 'image',
                        'instructions' => 'Foto geschikt voor vertoning op de website.',
                        'required' => '0',
                        'save_format' => 'object',
                        'preview_size' => 'thumbnail',
                        'order_no' => '3',
                    ),
                ),
                'location' =>
                array (
                    'rules' =>
                    array (
                        0 =>
                        array (
                            'param' => 'ef_user',
                            'operator' => '==',
                            'value' => 'editor',
                            'order_no' => '0',
                        ),
                        1 =>
                        array (
                            'param' => 'ef_user',
                            'operator' => '==',
                            'value' => 'author',
                            'order_no' => '1',
                        ),
                        2 =>
                        array (
                            'param' => 'ef_user',
                            'operator' => '==',
                            'value' => 'contributor',
                            'order_no' => '2',
                        ),
                        3 =>
                        array (
                            'param' => 'ef_user',
                            'operator' => '==',
                            'value' => 'subscriber',
                            'order_no' => '3',
                        ),
                        4 =>
                        array (
                            'param' => 'ef_user',
                            'operator' => '==',
                            'value' => 'managing_editor',
                            'order_no' => '4',
                        ),
                        5 =>
                        array (
                            'param' => 'ef_user',
                            'operator' => '==',
                            'value' => 'administrator',
                            'order_no' => '5',
                        ),
                    ),
                    'allorany' => 'any',
                ),
                'options' =>
                array (
                    'position' => 'normal',
                    'layout' => 'default',
                    'hide_on_screen' =>
                    array (
                    ),
                ),
                'menu_order' => 0,
            ));
        }
    }


    /**
     * Add team fields to the user profile page
     *
     * @since  1.0
     */

    function init_acf6()
    {
        if(function_exists("register_field_group")) {
            register_field_group(array (
                'key' => '508ff3c3f0442',
                'title' => 'Teamlid',
                'fields' => array (
                    array (
                        'key' => 'field_508fe0fcbe200',
                        'label' => 'Tonen op website',
                        'name' => 'show',
                        'type' => 'true_false',
                        'instructions' => '',
                        'required' => 0,
                        'message' => 'Teamlid tonen op de website?'
                    ),
                    array (
                        'key' => 'field_50896a9d52da2',
                        'label' => 'Functietitel',
                        'name' => 'job_title',
                        'type' => 'text',
                        'instructions' => '',
                        'required' => 0,
                        'default_value' => '',
                        'formatting' => 'none'
                    ),
                    array (
                        'key' => 'field_508fe506ddbc1',
                        'label' => 'Functie-omschrijving',
                        'name' => 'job_description',
                        'type' => 'text',
                        'instructions' => 'Functieomschrijving in maximaal 15 woorden.',
                        'required' => 0,
                        'default_value' => '',
                        'formatting' => 'html'
                    ),
                    array (
                        'key' => 'field_508fe57a95746',
                        'label' => 'Foto',
                        'name' => 'photo',
                        'type' => 'image',
                        'instructions' => 'Foto geschikt voor vertoning op de website.',
                        'required' => 0,
                        'return_format' => 'id',
                        'preview_size' => 'thumbnail',
                        'library' => 'all'
                    ),
                ),
                'location' => array (
                    array (
                        array (
                            'param' => 'user_role',
                            'operator' => '==',
                            'value' => 'editor'
                        ),
                    ),
                    array (
                        array (
                            'param' => 'user_role',
                            'operator' => '==',
                            'value' => 'author'
                        ),
                    ),
                    array (
                        array (
                            'param' => 'user_role',
                            'operator' => '==',
                            'value' => 'contributor'
                        ),
                    ),
                    array (
                        array (
                            'param' => 'user_role',
                            'operator' => '==',
                            'value' => 'subscriber'
                        ),
                    ),
                    array (
                        array (
                            'param' => 'user_role',
                            'operator' => '==',
                            'value' => 'managing_editor'
                        ),
                    ),
                    array (
                        array (
                            'param' => 'user_role',
                            'operator' => '==',
                            'value' => 'administrator'
                        ),
                    )
                ),
                'position' => 'normal',
                'style' => 'default',
                'label_placement' => 'top',
                'instruction_placement' => 'label',
                'hide_on_screen' => '',
                'menu_order' => 0,
            ));
        }
    }
}