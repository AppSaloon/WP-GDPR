<?php ?>
<h3>All comments added by email address: <?php echo $controller->email_request; ?></h3>
<?php
/**
 * create table with comments
 */
$controller->create_table_with_comments();
