<?php
/**
 * small form to use in table
 * to use in gdpr-template.php
 */
?>
<form method="post" id="wgdpr_delete_comments_form">
    <input type="hidden"  name="gdpr_email" value="<?php echo $email; ?>">
    <input type="submit" class="button button-primary" name="send_gdp_del_request" value="Send delete request">
</form>
