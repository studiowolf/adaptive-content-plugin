<?php

/**
 *  Api functions
 *
 *  @package     SW Adaptive Content Management
 *  @subpackage  Api functions
 *  @copyright   Studio Wolf
 *  @license     Studio Wolf
 *  @since       1.0
 *  @todo        all functional that accept an ID should also accept a slug
 */


/**
 * Get single field from meta data
 *
 * @param  $field the field to get
 * @param  int $post_id post_id
 * @param  boolean $repeater_field, is the field an repeater field?
 * @return return the field value or the repeater array
 * @since  1.3.1
 */

function sw_get_field($field, $post_id = null, $repeater_field = false)
{
    if(!$post_id) $post_id = get_the_id();

    /*
     *  Override for preview
     *
     *  If the $_GET['preview_id'] is set, then the user wants to see the preview data.
     *  There is also the case of previewing a page with post_id = 1, but using get_field
     *  to load data from another post_id.
     *  In this case, we need to make sure that the autosave revision is actually related
     *  to the $post_id variable. If they match, then the autosave data will be used, otherwise,
     *  the user wants to load data from a completely different post_id
     */

    if(isset($_GET['preview_id'])) {
        $autosave = wp_get_post_autosave($_GET['preview_id']);
        if($autosave->post_parent == $post_id) {
            $post_id = intval($autosave->ID);
        }
    }

    // If not a repeater field, fetch the data directly from get_post_meta
    if(!$repeater_field) {
        return get_post_meta($post_id, $field, true);
    } else {
        if($amount = get_post_meta($post_id, $field, true)) {
            $objects = array(); $i = 0;

            // Loop all meta data to check the pattern
            foreach(get_post_meta($post_id) as $key => $value) {
                preg_match('/^' . $field . '_(0|[1-9][0-9]*)_(.*)/', $key, $matches);
                // If there is a match build the array
                // object[item order][element name] = [value]
                if($matches) {
                    $objects[$matches[1]][$matches[2]] = $value[0];
                }
            }
            ksort($objects);
            return $objects;
        }
        return false;
    }
}


/**
 * Get the navigation title
 *
 * @param  int $sw_post_id post_id
 * @return string short title
 * @since  1.0
 */

function sw_get_title($reference = null)
{
    if($reference) {
        // Check if a locked reference exists
        $post_id = reference_lookup($reference);

        // If no result is fount and the $reference is an ID, then look further
        if(!$post_id && is_numeric($reference)) $post_id = $reference;

        return get_the_title($post_id);
    } else {
        return get_the_title();
    }
}


/**
 * Get the headline
 *
 * @param  int $sw_post_id post_id
 * @return string headline, or short title if headline does not exist
 * @since  1.0
 */

function sw_get_headline($sw_post_id = null)
{
    if(!$sw_headline = sw_get_field('headline', $sw_post_id)) {
        $sw_headline = sw_get_title($sw_post_id);
    }

    return $sw_headline;
}


/**
 * Get the sub headline
 *
 * @param  int $sw_post_id post_id
 * @return string sub headline or false if not existant
 * @since  1.0
 */

function sw_get_sub_headline($sw_post_id = null)
{
    if($sw_sub_headline = sw_get_field('sub_headline', $sw_post_id)) {
        return $sw_sub_headline;
    }
    return false;
}


/**
 * Get the window title
 *
 * @param  int $sw_post_id post_id
 * @return string the headline combined with the blog name
 * @since  1.0
 */

function sw_get_window_title($post_id = null)
{
    if(is_search()) {
        $title = ucfirst(get_search_query());
    } elseif(is_404()) {
        $title = '404';
    } elseif(is_author()) {
        global $wp_query;
        $user = $wp_query->get_queried_object();
        $title = sw_get_user_field('first_name', $user) . ' ' . sw_get_user_field('last_name', $user);
    } else {
        if(!$post_id) $post_id = get_the_id();
        $title = sw_get_headline($post_id);
    }

    return strip_tags($title) . " - " . sw_get_option_field('organisation');
}


/**
 * Get mini teaser
 *
 * @param  int $sw_post_id post_id
 * @return string mini teaser or false if not existant
 * @since  1.0
 */

function sw_get_mini_teaser($reference = null)
{
    if($reference){
        $post_id = reference_lookup($reference);
        if(!$post_id && is_numeric($reference)) $post_id = $reference;

        if($sw_mini_teaser = sw_get_field('mini_teaser', $post_id)) {
            return $sw_mini_teaser;
        }

    } else {

        if($sw_mini_teaser = sw_get_field('mini_teaser', $reference)) {
            return $sw_mini_teaser;
        }
        return false;
    }
}


/**
 * Get the teaser
 *
 * @param  int $sw_post_id post_id
 * @return string teaser or false if not existant
 * @since  1.0
 */

function sw_get_teaser($sw_post_id = null)
{
    if($sw_teaser = sw_get_field('teaser', $sw_post_id)) {
        return $sw_teaser;
    }
    return false;
}


/**
 * Get page or post link
 *
 * @param  int $sw_post_id post_id
 * @return string the page link
 * @since  1.0
 */

function sw_get_link($reference = null)
{
    // Check if a locked reference exists
    $post_id = reference_lookup($reference);

    // If no result is fount and the $reference is an ID, then look further
    if(!$post_id && is_int($reference)) $post_id = $reference;

    return get_page_link($post_id);
}


/**
 * Get the meta descriptions
 *
 * @param  int $sw_post_id post_id
 * @return string meta description, which is actually the mini teaser
 * @since  1.0
 */

function sw_get_metadescription()
{
    if(!is_author() && !is_404()) {
        return sw_get_mini_teaser();
    } else {
        return "";
    }

}


/**
 * Get images beloning to a post
 *
 * @param  int $sw_post_id post_id
 * @param  int $amount the number of images to fetch
 * @return array of images
 * @since  1.0
 */

function sw_get_images($post_id = null, $amount = false)
{
    if($image_ids = sw_get_field('images', $post_id)) {
        return $image_ids;
    }

    return false;
}


/**
 * Get the thumbnail
 *
 * @param  int $post_id post_id
 * @return array image object
 * @since  1.6
 */

function sw_get_thumbnail($post_id = null)
{
    if(!$thumbnail = sw_get_field('thumbnail', $post_id)) {
        $thumbnail = sw_get_first_image($post_id, false);
    }
    return $thumbnail;
}



/**
 * Get the first image
 *
 * @param  int $post_id post_id
 * @return array image object
 * @since  1.0
 */

function sw_get_first_image($post_id = null, $raiseIndex = false)
{
    if($image_ids = sw_get_images($post_id)) {
        if($raiseIndex) {
            SWShortTags::$image_index++;
        }
        return array_shift($image_ids);
    }
    return false;
}


/**
 * Get the file objects of the files belonging to a post
 *
 * @param  int $post_id the id of the post to get the files from
 * @param  int $amount the amount of files to fetch
 * @return array of file objects
 */

function sw_get_files($post_id = null, $amount = false)
{
    if($files = sw_get_field('files', $post_id, true)) {
        $objects = array();
        foreach($files as $file) {
            $objects[] = get_post( $file['file'] );
        }
        return $objects;
    }
}


function sw_get_videos($post_id = null, $amount = false)
{
    if($video_objects = sw_get_field('videos', $post_id, true)) {
        return $video_objects;
    }

    return false;
}


/**
 * Get the reference of a given page id
 *
 * @param  int $sw_post_id post_id
 * @return string reference or false if not found
 * @since  1.2
 */

function sw_get_reference($sw_post_id = null)
{
    $locked_pages = get_option('locked_pages', array());

    global $post;
    if(!$sw_post_id) $sw_post_id = $post->ID;

    // Find the reference by a page ID
    if(isset($locked_pages[$sw_post_id])) {
        return $locked_pages[$sw_post_id];
    } else {
        return false;
    }
}


/**
 * Get the reference of a given page id
 *
 * @param  int $term_id the term id
 * @return string reference or false if not found
 * @since  1.2
 */

function sw_get_term_reference($term_id)
{
    $locked_terms = get_option('locked_terms', array());

    // Find the reference by a page ID
    if(isset($locked_terms[$term_id])) {
        return $locked_terms[$term_id];
    } else {
        return false;
    }
}


/**
 * Get page object by reference code
 *
 * @param  string $reference the reference to match
 * @return page object
 * @since  1.2
 */

function sw_get_page_by_reference($reference)
{
    // Check if a locked reference exists
    $post_id = sw_reference_lookup($reference);

    // If no result is fount and the $reference is an ID, then look further
    if(!$post_id && is_int($reference)) $post_id = $reference;

    return get_page($post_id);
}



/**
 * Get term object by reference code
 *
 * @param  string $reference the reference to match
 * @return term id
 * @since  1.8
 */

function sw_get_term_id_by_reference($reference)
{
    // Check if a locked reference exists
    $term_id = sw_term_reference_lookup($reference);

    // If no result is fount and the $reference is an ID, then look further
    if(!$term_id && is_int($reference)) $term_id = $reference;

    return get_term($term_id);
}




/**
 * Conditional tag to check if the current page matches the reference
 *
 * @param  string $reference the reference to match or array with references
 * @return boolean
 * @since  1.3.2
 */

function sw_is_reference_page($references)
{
    // Check if a locked reference exists
    $locked_pages = get_option('locked_pages');

    // Check if $references is an array, otherwise convert to array
    if(!is_array($references)) {
        $references = array($references);
    }

    // Loop all references to find the matching ids
    $ids = array();
    foreach($references as $reference) {
        $ids[] = array_search($reference, $locked_pages);
    }

    if(in_array(get_the_ID(), $ids)) return true;
    return false;
}


/**
 * Get first video
 *
 * @param  int $sw_post_id post_id
 * @return array video object
 * @since  1.0
 */

function sw_get_first_video($post_id = null)
{
    if(!$post_id) $post_id = get_the_id();

    if($video_objects = sw_get_field('videos', $post_id, true)) {
        SWShortTags::$embed_index++;
        return array_shift($video_objects);
    }

    return false;
}


/**
 * Get option field value
 *
 * @param  string $field the field to fetch
 * @param  $repeater_field is the field an repeater field?
 * @return string the content of the option or array if repeater field
 * @since  1.0
 */

function sw_get_option_field($field, $repeater_field = false)
{
    if(!$repeater_field) {
        return get_option('options_' . $field);
    } else {
        $objects = array(); $i = 0;

        // Loop all meta data to check the pattern
        foreach(wp_load_alloptions() as $key => $value) {
            preg_match('/^options_' . $field . '_(0|[1-9][0-9]*)_(.*)/', $key, $matches);

            // If there is a match build the array
            // object[item order][element name] = [value]
            if($matches) {
                $objects[$matches[1]][$matches[2]] = $value;
            }
        }
        ksort($objects);
        return $objects;
    }
}


/**
 * Get a user field
 *
 * @param  string $field the field to fetch
 * @param  User Object or ID of the user
 * @return string the content of the field
 * @since  1.0
 */

function sw_get_user_field($field, $user = false)
{
    // If $user is false, then get current post user
    if(!$user) {
        global $post;
        $user = $post->post_author;

        // Return false if no user is found
        if(!$user) return false;
    }

    // Check the $user is an ID or object
    if(is_numeric($user)) {
        //return get_field($field, 'user_' . $user);
        return get_user_meta($user, $field, true);
    } else {
        //return get_field($field, 'user_' . $user->ID);
        return get_user_meta($user->ID, $field, true);
    }
}


/**
 * Get the e-mailadres of an user
 *
 * @param  User Object $user
 * @return Email address
 * @since  1.0
 */

function sw_get_user_email($user)
{
    return $user->user_email;
}


/**
 * Lookup post_id by reference
 *
 * @param  string $reference the reference to lookup
 * @return post_id if found or false, of not found
 * @since  1.5
 */

function sw_reference_lookup($reference)
{
    // Read the locked_pages option
    $locked_pages = get_option('locked_pages', array());
    // Check if $reference is inside the locked_pages array
    $post_id = array_search($reference, $locked_pages);
    // Apply filter to allow plugins to hook in
    $post_id = apply_filters('sw_acm_reference_lookup', $post_id, $reference);

    return $post_id;
}

function sw_term_reference_lookup($reference)
{
    // Read the locked_pages option
    $locked_terms = get_option('locked_terms', array());
    // Check if $reference is inside the locked_pages array
    $term_id = array_search($reference, $locked_terms);
    // Apply filter to allow plugins to hook in
    $term_id = apply_filters('sw_acm_term_reference_lookup', $term_id, $reference);

    return $term_id;
}

function reference_lookup($reference)
{
    return sw_reference_lookup($reference);
}


function sw_get_embed_index($post_id = null)
{
    // Get the current embed index and raise by one
}

function sw_get_image_index($post_id = null)
{
    // Get the current image index and  raise by one
}
