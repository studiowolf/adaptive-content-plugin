<?php

/**
 * To be commented
 * @todo create filter to create a start offset for embed and image index
 * @todo apply filter to set tags where embed code needs to be encapsulated in
 * @todo make the image size fiterable
 * @todo kunnen we nog iets met verschillende groottes doen
 * @todo image en embed index onderdeel maken van het globale sw_adaptive cms object. Nu komt deze losse klasse op veel plekken terug.
 * @todo shorttag maken voor files!
 * @todo wellicth de mogelijkheid geven om afbeeldingen te verplaatsen met position=right oid
 * @todo optie inbouwen als, use thumbnail as first picture, de keuze geven of iemand specifiek een thumbnail wil uploaden, of dat de thumanil
 * ook het eerste plaatje mag zijn
 *
 * LET OP! nu wordt er ongeacht de post steeds 1 bij de index opgesteld. Als er dus meerdere posts zijn op 1 pagina dan wordt
 * de index groter en groter, en zijn de afbeeldingen niet goed meer voor volgende posts. Als het ID nummer veranderd van een post, zou de index moeten resetten
 * MOGELIJKHEID: API functie (genaamd get-embed-index) in sw-adaptive-content-management.php waaraan je het huidige ID mee moet geven. Deze functie
 * geeft je de index, en hoogt 'm 1tje op als het post_id hetzelfde is gebleven, anders zet hij 'm weer op num
 */


class SWShortTags
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

        add_action('init', array($this, 'init'));

    }


    /**
     * Load the shortcodes
     *
     * @since  1.0
     */

    function init()
    {
        add_shortcode('afbeelding', array($this, 'tag_insert_image'));
        add_shortcode('video', array($this, 'tag_insert_embed'));
        add_shortcode('bestand', array($this, 'tag_insert_file'));
    }


    var $images;
    static $image_index = 0;

    function tag_insert_image()
    {
        $code = false;

        // Check if images are loaded
        if(!$this->images) {
            // If not load images. Only load when needed
            $this->images = sw_get_images();
        }

        if(isset($this->images[self::$image_index])) {
            // Index found, try to embed, and add one to index

            // Make the size filterable
            $image = $this->images[self::$image_index];
            $code = sw_get_image_tag($image, 'large');
            self::$image_index++;
        }

        // Make html fiterable
        if($code) {
            // hook filter to change embed code, first the image-tag, second the image itself
            $code = apply_filters('sw_insert_image', $code, $image);

            return '<figure class="image">' . $code . '</figure>';
        } else {
            return false;
        }
    }


    var $embeds;
    static $embed_index = 0;

    function tag_insert_embed()
    {
        $code = false;

        // Check if embeds are loaded
        if(!$this->embeds) {
            // If not load embeds. Only load when needed
            $this->embeds = sw_get_videos();
        }

        if(isset($this->embeds[self::$embed_index])) {
            // Index found, try to embed, and add one to index
            $code = sw_get_embed_tag($this->embeds[self::$embed_index]);
            self::$embed_index++;
        }

        // Make html fiterable
        if($code) {
            return '<figure class="video">' . $code . '</figure>';
        } else {
            return false;
        }
    }

    var $files;
    static $file_index = 0;

    function tag_insert_file()
    {
        $code = false;

        // Check if files are loaded
        if(!$this->files) {
            // If not load files. Only load when needed
            $this->files = sw_get_files();
        }

        if(isset($this->files[self::$file_index])) {
            // Index found, try to include the file, and add one to index
            if(is_array($this->files[self::$file_index])){
                $file_id = $this->files[self::$file_index]['file'];
            } else {
                $file_id = $this->files[self::$file_index]->ID;
            }

            $code = sw_get_file_tag($file_id);
            self::$file_index++;
        }

        // Make html fiterable
        if($code) {
            $code = apply_filters('sw_insert_file', $code, $file_id);
            return '<span class="file">' . $code . '</span>';
        } else {
            return false;
        }
    }
}