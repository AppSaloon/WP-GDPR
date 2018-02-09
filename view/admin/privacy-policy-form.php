<?php
/**
 * form to update privacy policy link in admin page settings
 */
?>
<form method="post" id="gdpr_admin_privacy_policy">
	<input type="url" class="" name="gdpr_priv_pov_link" value="<?php echo $privacy_policy_url; ?>">
	<input type="submit" class="button button-primary" name="gdpr_save_priv_pol_link" value="<?php _e('Update url', 'wp_gdpr'); ?>">
</form>

