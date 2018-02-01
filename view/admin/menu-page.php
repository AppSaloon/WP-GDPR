<?php namespace wp_gdpr\view\admin;
/**
 * this template is to show manu page in admin-menu
 */
?>
<div class="wrap">
    <h2><?php _e('List of users that requested for information', 'wp_gdpr'); ?></h2>
	<?php
use wp_gdpr\lib\Gdpr_Container;

$controller = Gdpr_Container::make('wp_gdpr\controller\Controller_Menu_Page') ;
$controller->build_table_with_requests();
?>

    <h2><?php _e('List of plugins that store data of users', 'wp_gdpr'); ?></h2>
<?php $controller->build_table_with_plugins(); ?>
    <h2><?php _e('List of delete requests', 'wp_gdpr'); ?></h2>
	<?php $controller->build_table_with_delete_requests(); ?>

    <h2><?php _e('Request your own add-on', 'wp_gdpr'); ?></h2>
    <?php $controller->build_form_to_request_add_on(); ?>


</div>


