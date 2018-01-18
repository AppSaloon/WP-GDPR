<?php
/**
 *  FORM to send request for access to data about user
 */
?>
<?php if ( 'GET' == $_SERVER['REQUEST_METHOD'] ): ?>
    <form action="" method="post">
        <?php _e('E-mail', 'wp_gdpr'); ?>:<br>
        <input type="email" name="email" value="" required>
        <br><br>
        <input type="submit" name="gdpr_req" value="<?php _e('Submit', 'wp_gdpr'); ?>">
    </form>
<?php  else: ?>
    <h3><?php _e('Thank You! We will send You e-mail in 48h.', 'wp_gdpr'); ?></h3>
<?php endif; ?>
