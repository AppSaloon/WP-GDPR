<?php
/**
 * small form to use in table
 * allows to send e-mail with unique urls to users
 */
?>
<form method="post" id="gdpr_admin_del_comments_form">
    <?php //$controller->print_inputs_with_emails(); ?>
    <input type="submit" class="button button-primary" name="send_gdp_emails" value="<?php _e('Delete comments', 'wp_gdpr'); ?>">
</form>
