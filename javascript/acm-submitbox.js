jQuery(document).ready(function() {

    // Locked pages
    // When edit is pressed
    jQuery('.edit-post-locked').click(function(event) {
        jQuery('#post-locked-input').slideDown(200);
        jQuery('.edit-post-locked').hide();
        event.preventDefault();
    });

    // When save is pressed
    jQuery('.save-post-locked').click(function(event) {
        jQuery('#post-locked-input').slideUp(200);
        jQuery('.edit-post-locked').show();

        if(jQuery('#post-locked').is(':checked')) {
            jQuery('#post-locked-display').html('Ja');

        } else {
            jQuery('#post-locked-display').html('Nee');
            jQuery('#post-locked-reference').val('');
        }

        event.preventDefault();
    });

    // When cancel is pressed
    jQuery('.cancel-post-locked').click(function(event){
        jQuery('#post-locked-input').slideUp(200);
        jQuery('.edit-post-locked').show();

        var postLocked = jQuery('#hidden-post-locked').val();
        var postLockedReference = jQuery('#hidden-post-locked-reference').val();

        // Do we need to check or uncheck the box
        if(postLocked == 1) {
            jQuery('#post-locked').prop('checked', true);
            jQuery('#post-locked-display').html('Ja');
        } else {
            jQuery('#post-locked').prop('checked', false);
            jQuery('#post-locked-display').html('Nee');
        }
        jQuery('#post-locked-reference').val(postLockedReference);

        event.preventDefault();
    });


    // Display in navigation
    // When edit is pressed
    jQuery('.edit-post-navigation').click(function(event) {
        jQuery('#post-navigation-input').slideDown(200);
        jQuery('.edit-post-navigation').hide();
        event.preventDefault();
    });

    // When save is pressed
    jQuery('.save-post-navigation').click(function(event) {
        jQuery('#post-navigation-input').slideUp(200);
        jQuery('.edit-post-navigation').show();

        if(jQuery('#post_navigation_show').is(':checked')) {
            jQuery('#post-navigation-display').html('Ja');
        } else {
            jQuery('#post-navigation-display').html('Nee');
        }

        event.preventDefault();
    });

    // When cancel is pressed
    jQuery('.cancel-post-navigation').click(function(event){
        jQuery('#post-navigation-input').slideUp(200);
        jQuery('.edit-post-navigation').show();

         var postNavigation = jQuery('#hidden-post-navigation').val();

        // Do we need to check or uncheck the box
        if(postNavigation == 1) {
            jQuery('#post_navigation_show').prop('checked', true);
            jQuery('#post_navigation_hide').prop('checked', false);
            jQuery('#post-navigation-display').html('Ja');
        } else {
            jQuery('#post_navigation_show').prop('checked', false);
            jQuery('#post_navigation_hide').prop('checked', true);
            jQuery('#post-navigation-display').html('Nee');
        }

        event.preventDefault();
    });

});