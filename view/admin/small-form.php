<?php
/**
 * small form to use in table
 * allows to send email with unique urls to users
 */
?>
<form>
    <?php $controller->print_inputs_with_emails(); ?>
    <input type="submit" class="button button-primary" name="send_gdp_emails" value="Send email for all requests">
</form>
