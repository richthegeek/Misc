<?php $this->load->view( "header" ); ?>
<a href="/gaphor/index.php/jtog/logout" id="back">Logout</a>
<h1>Welcome to the Java > Gaphor generator, <?php print $username;?>!</h1>

<p>The following is a list of your <b>.java</b> projects:</p>

<ul class='project-list'>
<?php if( count( $projects ) == 0 ) { ?>
	(you have no projects)
<?php } else { ?>
<?php foreach( $projects as $project=>$files ) { ?>
	<li><a href="/gaphor/index.php/jtog/project/<?php print $project;?>"><?php print $project;?></a></li>
	<ul class='file-list'>
	<?php if( count( $files ) == 0 ) { ?>
		(no files)
	<?php } else { ?>
	<?php foreach( $files as $file ) { ?>
	<li><a href="/gaphor/index.php/jtog/view/<?php print $username.'/'.$project.'/'.$file;?>"><?php print $file;?></a></li>
	<?php } } ?>
</ul>
<?php } } ?>
</ul>

<h1>New Project</h1>
<?php
echo form_open( "jtog/new_project" );

echo form_input( "name", "Project name" );

echo form_submit( "create", "Create Project" );

echo form_close ();
?>

<?php $this->load->view( "footer" ); ?>
