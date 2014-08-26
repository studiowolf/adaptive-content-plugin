<div class="misc-pub-section">
    Vastzetten:
    <span id="post-locked-display"><?php if($locked_page):?>Ja <?php if($locked_page && $locked_page != 1) echo '(' . $locked_page . ')'; ?><?php else: ?>Nee<?php endif;?></span>
    <a href="#post_locked" class="edit-post-locked hide-if-no-js" style="display: inline;">Bewerken</a>

    <div id="post-locked-input" class="hide-if-js" style="display: none;">
        <input type="hidden" name="hidden_post_locked" id="hidden-post-locked" value="<?php echo ($locked_page) ? '1' : '0' ?>" />
        <input type="hidden" name="hidden_post_locked-reference" id="hidden-post-locked-reference" value="<?php if($locked_page && $locked_page != 1) echo $locked_page; ?>" />
        <label><input id="post-locked" type="checkbox" value="1" name="post_locked" <?php checked((bool) $locked_page)?>/> Deze pagina vastzetten</label>
        <br/><label>Referentie</label>
        <input id="post-locked-reference" type="text" name="post_locked_reference" value="<?php if($locked_page && $locked_page != 1) echo $locked_page; ?>" /><br/>

        <p>
            <a href="#post_locked" class="save-post-locked hide-if-no-js button">OK</a>
            <a href="#post_locked" class="cancel-post-locked hide-if-no-js">Annuleren</a>
        </p>
    </div>
</div>
