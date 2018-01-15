<?php namespace wp_gdpr\view\admin; ?>
    <h2>List of users that requested for information</h2>
	<?php
use wp_gdpr\controller\Controller_Menu_Page;

$controller = new Controller_Menu_Page();
$controller->build_table_with_requests();
?>
    <h2>List of plugins that store data of users</h2>
<?php $controller->build_table_with_plugins();


