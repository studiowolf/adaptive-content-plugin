<?php

/**
 *  SW Meta Fields
 *
 *  @package     SW Adaptive Content Management
 *  @subpackage  SW Meta Fields
 *  @copyright   Studio Wolf
 *  @license     Studio Wolf
 *  @since       1.0
 */

class SWAcf5MetaFields
{
    // Fields;
    var $headline;
    var $sub_headline;
    var $mini_teaser;
    var $teaser;
    var $thumbnail;
    var $images;
    var $files;
    var $videos;

    var $parent;


    /**
     * Constructor
     *
     * Set hooks
     *
     * @param  $parent SWAdapativeContentManagement object
     */

    function __construct($parent)
    {
        $this->parent = $parent;

        // Needs to be loaded as late as possible to reach all custom post types
        add_action('init', array($this, 'init'), 99999);
    }


    /**
     * Initialize all the meta post fields
     * Loop all post types if we are inside the admin, we create field
     * groups for every post type, acm fields can change. Every group
     * as a unique ID based on the post type
     *
     * @since  1.0
     */

    function init()
    {
        if(function_exists('register_field_group'))
        {
            if(!is_admin()) {

                // Simply register the fields without location rules if we're not in the admin
                $this->register_acm_fields('all', $rules = false);

            } else {

                $post_type_field_settings = $this->parent->settings['post_type_field_settings'];

                // Loop through post types
                foreach($post_type_field_settings as $post_type => $value) {

                    // Create the rules array
                    $rules = array(
                        array(
                            'param' => 'post_type',
                            'operator' => '==',
                            'value' => $post_type
                        )
                    );
                    $this->register_acm_fields($post_type, $rules, $value['acm_fields']);
                }
            }

            $this->register_options();
        }
    }


    /**
     * Register acm fields based on variables
     *
     * @since  1.3
     *
     * @param  int  $post_type  the post type to show the field in
     * @param  int  $rules      positioning rules
     * @param  boolean $acm_fields [description]
     */

    function register_acm_fields($post_type, $rules, $acm_fields = false)
    {
        // @TODO: in future this should be loose functions, instead of filling properties
        $this->populate_fields($post_type);

        // Headline fields, always load when not in admin
        $fields = array();
        $fields[] = $this->headline;
        if(!$rules || isset($acm_fields['sub_headline']))
            $fields[] = $this->sub_headline;

        // Register the headline group for post type
        register_field_group(array (
            'key' => 'group_508961bcabbb2_' . $post_type,
            'title' => 'Headlines',
            'fields' => $fields,
            'location' => array (
                $rules
            ),
            'position' => 'acf_after_title',
            'style' => 'default',
            'hide_on_screen' =>
            array (
                0 => 'excerpt',
                1 => 'custom_fields',
                2 => 'slug',
                3 => 'featured_image',
            ),
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'menu_order' => 10,
        ));

        // Teaser fields, always load when not in admin
        $fields = array();
        $fields[] = $this->mini_teaser;
        if(!$rules || isset($acm_fields['teaser']))
            $fields[] = $this->teaser;

        // Register the teaser group for post type
        register_field_group(array (
            'key' => 'group_508961bcac4bc_' . $post_type,
            'title' => 'Samenvattingen',
            'fields' => $fields,
            'location' =>
            array (
                $rules
            ),
            'position' => 'normal',
            'style' => 'default',
            'hide_on_screen' => '',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'menu_order' => 20,
        ));

        // Media fields, always load when not in admin
        $fields = array();
        if(!$rules || isset($acm_fields['thumbnail']))
            $fields[] = $this->thumbnail;
        if(!$rules || isset($acm_fields['images']))
            $fields[] = $this->images;
        if(!$rules || isset($acm_fields['files']))
            $fields[] = $this->files;
        if(!$rules || isset($acm_fields['videos']))
            $fields[] = $this->videos;

        // Register the media group for post type
        if(!empty($fields)) {
            register_field_group(array (
                'key' => 'group_508961bcad11a_' . $post_type,
                'title' => 'Media',
                'fields' => $fields,
                'location' =>
                array ($rules),
                'position' => 'normal',
                'style' => 'default',
                'hide_on_screen' => '',
                'label_placement' => 'top',
                'instruction_placement' => 'label',
                'menu_order' => 30
            ));
        }

    }


    /**
     * Populate all acm fields
     *
     * @since  1.3
     */

    private function populate_fields($post_type)
    {
        $this->headline = array (
            'key' => 'field_508922a48ec67_' . $post_type,
            'label' => 'Headline',
            'name' => 'headline',
            'type' => 'text',
            'instructions' => 'Dit is de hoofdkop. Deze beschrijft kort waar de content over gaat en komt bovenaan de pagina te staan.',
            'required' => 0,
            'default_value' => '',
            'formatting' => 'none'
        );

        $this->sub_headline = array (
            'key' => 'field_508922a48ee95_' . $post_type,
            'label' => 'Sub headline',
            'name' => 'sub_headline',
            'type' => 'text',
            'instructions' => 'Dit is de ondertitel bij de content. Ook wel verklarende titel of bijtitel genoemd en geeft nadere toelichting op de headline. Is niet in alle gevallen aanwezig.',
            'required' => 0,
            'default_value' => '',
            'formatting' => 'none'
        );

        $this->mini_teaser = array (
            'key' => 'field_508926b69b8a0_' . $post_type,
            'label' => 'Korte samenvatting',
            'name' => 'mini_teaser',
            'type' => 'text',
            'instructions' => 'Korte samenvatting van de content in maximaal 15 woorden. Deze moet verplicht ingevuld worden omdat dit onder andere wordt gebruikt voor zoekmachine-optimalisatie.',
            'required' => 1,
            'default_value' => '',
            'formatting' => 'none'
        );

        $this->teaser = array (
            'key' => 'field_508926b69bae6_' . $post_type,
            'label' => 'Samenvatting',
            'name' => 'teaser',
            'type' => 'textarea',
            'instructions' => 'Beschrijf de content in maximaal 1 alinea (moraal van het verhaal). Typisch 4 regels tekst. Deze content kan op verschillende plekken gebruikt worden, onder andere als eerste, iets vetter gedrukte, alinea op de webpagina. Dit veld is niet verplicht, maar wel aan te raden.',
            'required' => 0,
            'default_value' => '',
            'formatting' => 'br',
        );

        $this->thumbnail = array (
            'key' => 'field_52331eb100747_' . $post_type,
            'label' => 'Thumbnail',
            'name' => 'thumbnail',
            'type' => 'image',
            'instructions' => 'Een doorgaans klein plaatje dat de content representeert (samen met bijvoorbeeld een samenvatting). Als er geen thumbnail is ingevoerd, maar wel een nodig is, dan is de thumbnail gelijk aan de eerste afbeelding in de afbeeldingen hieronder. Dit veld kan dus worden leeggelaten als de eerste afbeelding en de thumbnail gelijk aan elkaar zijn.',
            'return_format' => 'id',
            'preview_size' => 'thumbnail',
            'library' => 'all',
        );

        $this->images = array (
            'key' => 'field_508931de9c472_' . $post_type,
            'label' => 'Afbeeldingen',
            'name' => 'images',
            'type' => 'gallery',
            'instructions' => 'Voeg afbeeldingen toe die bij de content horen. Via de [afbeelding]-tag plaats je de afbeeldingen in de content. Afbeeldingen die hoger staan worden eerder getoond. Afbeeldingen kunnen in volgorde versleept worden.',
            'required' => 0,
            'min' => '',
            'max' => '',
            'library' => 'all',
            'preview_size' => 'thumbnail'
        );

        $this->files = array (
            'key' => 'field_508948593ee00_' . $post_type,
            'label' => 'Bestanden',
            'name' => 'files',
            'type' => 'repeater',
            'instructions' => 'Voeg hier bestanden en/of downloads toe die bij de content horen. Belangrijk is dat de juiste data als titel, onderschrift en omschrijving zijn ingevuld bij de behorende file om een net overzicht te genereren.',
            'required' => 0,
            'sub_fields' => array (
                array (
                    'key' => 'field_508948593ee1d_' . $post_type,
                    'label' => 'Bestand',
                    'name' => 'file',
                    'type' => 'file',
                    'required' => 1,
                    'instructions' => '',
                    'column_width' => '',
                    'return_format' => 'id',
                    'library' => 'all'
                ),
            ),
            'min' => '',
            'max' => '',
            'layout' => 'table',
            'button_label' => 'Nieuw bestand toevoegen'
        );

        $this->videos = array (
            'key' => 'field_50894eaab9144_' . $post_type,
            'label' => 'Video\'s',
            'name' => 'videos',
            'type' => 'repeater',
            'instructions' => 'Voer URL\'s van video\'s in die bij de content horen. Plaats de volledige URL. Met de [video]-tag kun je video\'s in de content plaatsen.',
            'required' => 0,
            'sub_fields' =>
            array (
                0 =>
                array (
                    'key' => 'field_50894eaab9156_' . $post_type,
                    'label' => 'Video-url',
                    'name' => 'url',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 1,
                    'column_width' => '',
                    'default_value' => 'http://',
                    'formatting' => 'none'
                ),
            ),
            'min' => '',
            'max' => '',
            'layout' => 'table',
            'button_label' => 'Nieuwe video toevoegen'
        );

    }


    /**
     * Populate and register option fields
     *
     * @since  1.3
     */

    function register_options()
    {
        // Option page fields
        register_field_group(array (
            'key' => 'group_5089650b68bda',
            'title' => 'Contactgegevens',
            'fields' =>
            array (
                array (
                    'key' => 'field_50895d61370f8',
                    'label' => 'Organisatie',
                    'name' => 'organisation',
                    'type' => 'text',
                    'instructions' => 'Voer de naam van het bedrijf, instantie, organisatie etc. hier in.',
                    'required' => 0,
                    'default_value' => '',
                    'formatting' => 'none'
                ),
                array (
                    'key' => 'field_50895d6137575',
                    'label' => 'Adres',
                    'name' => 'address',
                    'type' => 'text',
                    'instructions' => 'Straatnaam en huisnummer.',
                    'required' => 0,
                    'default_value' => '',
                    'formatting' => 'none'
                ),
                array (
                    'key' => 'field_50895d6137431',
                    'label' => 'Postcode',
                    'name' => 'postal_code',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                    'default_value' => '',
                    'formatting' => 'none'
                ),
                array (
                    'key' => 'field_50895d6137801',
                    'label' => 'Postbus',
                    'name' => 'postbox',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                    'default_value' => '',
                    'formatting' => 'none'
                ),
                array (
                    'key' => 'field_50895d61379ac',
                    'label' => 'Plaats',
                    'name' => 'city',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                    'default_value' => '',
                    'formatting' => 'none'
                ),
                array (
                    'key' => 'field_50895ea600cfd',
                    'label' => 'Telefoonnummer',
                    'name' => 'phone',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                    'default_value' => '',
                    'formatting' => 'none'
                ),
                array (
                    'label' => 'Fax',
                    'name' => 'fax',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                    'default_value' => '',
                    'formatting' => 'none',
                    'key' => 'field_50895ec167195'
                ),
                array (
                    'key' => 'field_50895d6137b55',
                    'label' => 'E-mailadres',
                    'name' => 'email',
                    'type' => 'text',
                    'instructions' => 'Het algemene e-mailadres.',
                    'required' => 0,
                    'default_value' => '',
                    'formatting' => 'none'
                ),
                array (
                    'label' => 'Foto',
                    'name' => 'photo',
                    'type' => 'image',
                    'instructions' => 'Een algemene foto van de organisatie. Bijvoorbeeld een kantoor, of een groep mensen.',
                    'required' => 0,
                    'save_format' => 'object',
                    'preview_size' => 'thumbnail',
                    'key' => 'field_50901279d38a2'
                ),
                array (
                    'label' => 'Google Maps',
                    'name' => 'google_maps',
                    'type' => 'text',
                    'instructions' => 'Een link naar Google Maps waar men eventueel een routebeschrijving kan invullen.',
                    'required' => 0,
                    'default_value' => '',
                    'formatting' => 'none',
                    'key' => 'field_70234ec167195'
                ),
            ),
            'location' => array (
                array (
                    array (
                        'param' => 'options_page',
                        'operator' => '==',
                        'value' => 'acm-organisation'
                    )
                )
            ),
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'left',
            'instruction_placement' => 'field',
            'hide_on_screen' => '',
            'menu_order' => 10
        ));

        register_field_group(array (
            'key' => 'group_5089650b6a582',
            'title' => 'Social media-accounts',
            'fields' =>
            array (
                array (
                    'key' => 'field_5089601dbb0b9',
                    'label' => 'Twitter',
                    'name' => 'twitter_url',
                    'type' => 'text',
                    'instructions' => 'Voer de URL van het twitter-account in.',
                    'required' => 0,
                    'default_value' => '',
                    'placeholder' => 'http://',
                    'formatting' => 'none'
                ),
                array (
                    'key' => 'field_5089601dbb315',
                    'label' => 'Facebook',
                    'name' => 'facebook_url',
                    'type' => 'text',
                    'instructions' => 'Voer de URL van de facebookpagina in.',
                    'required' => 0,
                    'default_value' => '',
                    'placeholder' => 'http://',
                    'formatting' => 'none'
                ),
                array (
                    'key' => 'field_5089601dbb4c7',
                    'label' => 'LinkedIn',
                    'name' => 'linkedin_url',
                    'type' => 'text',
                    'instructions' => 'Voer de URL van de LinkedIn-pagina in.',
                    'required' => 0,
                    'default_value' => '',
                    'placeholder' => 'http://',
                    'formatting' => 'none'
                ),
                array (
                    'key' => 'field_5069201dbb4c7',
                    'label' => 'Flickr',
                    'name' => 'flickr_url',
                    'type' => 'text',
                    'instructions' => 'Voer de URL van de Flickr-pagina in.',
                    'required' => 0,
                    'default_value' => '',
                    'placeholder' => 'http://',
                    'formatting' => 'none'
                ),
            ),
            'location' => array (
                array (
                    array (
                        'param' => 'options_page',
                        'operator' => '==',
                        'value' => 'acm-social-media'
                    )
                )
            ),
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'left',
            'instruction_placement' => 'field',
            'hide_on_screen' => '',
            'menu_order' => 20,
        ));

        register_field_group(array (
            'key' => 'group_5089650b6b9c8',
            'title' => 'Administratief',
            'fields' => array (
                array (
                    'key' => 'field_50895e1df05dc',
                    'label' => 'KvK-nummer',
                    'name' => 'kvk',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                    'default_value' => '',
                    'formatting' => 'none'
                ),
                array (
                    'key' => 'field_50895e1df07ef',
                    'label' => 'BTW-nummer',
                    'name' => 'btw',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                    'default_value' => '',
                    'formatting' => 'none'
                ),
                array (
                    'key' => 'field_50895e1df099b',
                    'label' => 'Bankgegevens',
                    'name' => 'bank',
                    'type' => 'text',
                    'instructions' => 'Inclusief banknaam en nummer.',
                    'required' => 0,
                    'default_value' => '',
                    'formatting' => 'none'
                ),
                array (
                    'key' => 'field_50895ef29a6c2',
                    'label' => 'Algemene voorwaarden',
                    'name' => 'terms',
                    'type' => 'file',
                    'instructions' => 'Voeg hier het digitale formaat van de algemene voorwaarden toe indien beschikbaar.',
                    'required' => 0,
                    'return_format' => 'id'
                )
            ),
            'location' => array (
                array (
                    array (
                        'param' => 'options_page',
                        'operator' => '==',
                        'value' => 'acm-organisation'
                    )
                )
            ),
            'position' => 'normal',
            'label_placement' => 'left',
            'instruction_placement' => 'field',
            'style' => 'default',
            'hide_on_screen' => '',
            'menu_order' => 30,
        ));

        register_field_group(array (
            'key' => 'group_5080490b6b9c8',
            'title' => 'Bezoekers-tracking',
            'fields' => array (
                array (
                    'key' => 'field_50921e1df05dc',
                    'label' => 'Trackingcode',
                    'name' => 'analytics',
                    'type' => 'textarea',
                    'instructions' => 'De trackcode van een website-statistieken-applicatie. Bijvoorbeeld Google Analytics.',
                    'required' => 0,
                    'default_value' => '',
                    'formatting' => 'none'
                ),
            ),
            'location' =>
            array (
                array (
                    array (
                        'param' => 'options_page',
                        'operator' => '==',
                        'value' => 'acm-technical'
                    )
                )
            ),
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'left',
            'instruction_placement' => 'field',
            'hide_on_screen' => '',
            'menu_order' => 40
        ));
    }
}