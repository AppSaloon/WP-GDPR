<?php
echo 'test';

$table = new \wp_gdpr\lib\Appsaloon_Table_Builder( array( 'head1', 'head2' ), array(
	array( 'row!', 'row12' ),
	array( 'row2', 'row22' )
), array( 'footer!,j', 'footer2' ) );
$table->print_table();