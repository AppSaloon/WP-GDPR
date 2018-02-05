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
        <label for="checkbox_gdpr">
            I consent to having <?php echo get_bloginfo('name'); ?> collect my email so that they can send me my requested info.
            For more info check our privacy policy where you'll get more info on where, how and why we store your data.
        </label>
        <input type="checkbox" name="checkbox_gdpr" required>
        <br><br>
        <input type="submit" name="gdpr_req" value="<?php _e('Submit', 'wp_gdpr'); ?>">
    </form>
<?php  else:

    echo get_locale();?>
    <h3><?php _e('Thank You! We will send You e-mail in 48h.', 'wp_gdpr'); ?></h3>
<?php endif; ?>
