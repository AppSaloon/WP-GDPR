<!doctype html>
<html>
<head>
    <meta name="viewport" content="width=device-width"/>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <style>
        .container {
            display: block;
            Margin: 0 auto !important;
            max-width: 580px;
            padding: 10px;
            width: 580px;
        }

        h2 {
            font-size: 25px;
            font-weight: 300;
            text-align: center;
            color: #000000;
            font-family: sans-serif;
            line-height: 1.4;
            margin: 0 0 30pt;
        }

        h3 {
            color: #31708f;
            background-color: #d9edf7;
            border-color: #bce8f1;
            padding: 15px;
            font-family: sans-serif;
            font-weight: 400;
            box-shadow: 0px 2px 10px #31708f;
        }

        p, th, td {
            font-family: sans-serif;
            font-size: 14px;
            font-weight: normal;
            margin: 0;
            Margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-spacing: 0;
        }

        th {
            text-transform: capitalize;
            font-weight: 900;
        }

        th, td {
            text-align: left;
            padding: 15px 5px;
        }

        tbody tr:nth-child(odd) {
            background-color: #f9f9f9;
        }

        #wgdpr_delete_comments_form {
            margin-top: 30px;
        }

        #wgdpr_delete_comments_form input {
            border: solid 1px #3498db;
            border-radius: 5px;
            box-sizing: border-box;
            cursor: pointer;
            display: inline-block;
            font-size: 14px;
            font-weight: bold;
            margin: 0;
            padding: 12px 25px;
            text-decoration: none;
            text-transform: capitalize;
            background-color: #3498db;
            color: #ffffff;
            height: initial;
        }
    </style>
    <title><?php _e( 'View Comments', 'wp_gdpr' ); ?></title>
	<?php wp_head(); ?>
</head>
<body>
<div class="container">
	<?php echo $controller->message; ?>
    <h2><?php _e( 'All comments added by email address', 'wp_gdpr' ); ?>: <?php echo $controller->email_request; ?></h2>
    <div class="js-update-message"></div>
	<?php
	/**
	 * create table with comments
	 */
	$controller->create_table_with_comments();
	/**
	 * do action for addon
	 */
	do_action( 'gdpr_show_entries', $controller->email_request );
	?>
</div>
</body>
<?php wp_footer(); ?>
</html>





