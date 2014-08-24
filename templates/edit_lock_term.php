<tr class="form-field">
    <th scope="row" valign="top">
        <label for="term_locked">Vastzetten</label>
    </th>
    <td>
        <label for="term_locked">
            <input name="term_locked" type="checkbox" id="term_locked" value="1" <?php checked((bool) $locked_term)?> /> 
            Deze term vastzetten.
        </label><br/><br/>
        <input name="term_locked_reference" id="term_locked_reference" type="text" value="<?php if($locked_term && $locked_term != 1) echo $locked_term; ?>" size="40" />
        <p class="description">Deze tag vastzetten met een referentie.</p>
    </td>
</tr>