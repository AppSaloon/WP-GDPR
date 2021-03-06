<?php
/**
 *  FORM to send request for access to data about user
 */
?>
<?php if ( 'GET' == $_SERVER['REQUEST_METHOD'] ): ?>
    <form action="" method="post">
        <?php _e('Email', 'wp_gdpr'); ?>:<br>
        <input type="email" name="email" value="" required>
        <br><br>
        <?php
        $string = __('I consent to having %s collect my email so that they can send me my requested info.
            For more info check our privacy policy where you\'ll get more info on where, how and why we store your data.', 'wp_gdpr');
        $blog_name = get_bloginfo('name');
        ?>
            <input type="checkbox" name="checkbox_gdpr" required>
            <label for="checkbox_gdpr">
                <?php echo sprintf($string, $blog_name); ?>
            </label>
        <br><br>
        <input type="submit" name="gdpr_req" value="<?php _e('Submit', 'wp_gdpr'); ?>">
    </form>
<?php  else: ?>
    <h3><?php _e('Thank You! We will send you email in 48h.', 'wp_gdpr'); ?></h3>
<?php endif; ?>
