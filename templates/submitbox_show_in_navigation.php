<div class="misc-pub-section">
    Tonen in navigatie:
    <span id="post-navigation-display"><?php if($display_in_navigation):?>Ja <?php if($display_in_navigation && $display_in_navigation != 1) echo '(' . $display_in_navigation . ')'; ?><?php else: ?>Nee<?php endif;?></span>
    <a href="#post_navigation" class="edit-post-navigation hide-if-no-js" style="display: inline;">Bewerken</a>

    <div id="post-navigation-input" class="hide-if-js" style="display: none;">
        <input type="hidden" name="hidden_post_navigation" id="hidden-post-navigation" value="<?php echo ($display_in_navigation) ? '1' : '0' ?>" />

        <input type="radio" name="post_navigation" id="post_navigation_show" value="1" <?php checked((bool) $display_in_navigation)?> /> <label for="post_navigation_show" class="selectit">Ja</label><br/>
        <input type="radio" name="post_navigation" id="post_navigation_hide" value="0" <?php checked((bool) $display_in_navigation, false)?> /> <label for="post_navigation_hide" class="selectit">Nee</label><br/>

        <p>
            <a href="#post_navigation" class="save-post-navigation hide-if-no-js button">OK</a>
            <a href="#post_navigation" class="cancel-post-navigation hide-if-no-js">Annuleren</a>
        </p>
    </div>
</div>
