<?php
/**
 *  FORM to send request for access to data about user
 */
?>
<?php if ( 'GET' == $_SERVER['REQUEST_METHOD'] ): ?>
    <form action="" method="post">
        Email:<br>
        <input type="email" name="email" value="" required>
        <br><br>
        <input type="submit" name="gdpr_req" value="Submit">
    </form>
<?php  else: ?>
    <h3>Thank You! We will send You email in 48h.</h3>
<?php endif; ?>
