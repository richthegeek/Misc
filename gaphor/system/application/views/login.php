<?php $this->load->view( "header" ); ?>
<h1>Login!</h1>
<?php

echo form_open( 'jtog/login/go' );

echo form_label( "Username:" )."<br/>";
echo form_input( "username" );

echo form_submit( "login", "Login" );
echo form_close();

?>
<?php $this->load->view( "footer" ); ?>
