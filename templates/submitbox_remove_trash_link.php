<style>
    #delete-action{ display:none; }
    <?php if(!current_user_can('administrator')):?>
        .edit-post-status{ display:none; }
        .curtime { display: none; }
        .edit-visibility { display: none; }
    <?php endif;?>
</style>

<div class="misc-pub-section">
    <strong>Deze pagina kan niet verwijderd worden</strong>
</div>
