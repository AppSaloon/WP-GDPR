<?php ?>
<h3><?php _e('All comments added by e-mail address', 'wp_gdpr'); ?>: <?php echo $controller->email_request; ?></h3>
<?php
/**
 * create table with comments
 */
$controller->create_table_with_comments();
