<?php $this->load->view( "header" ); ?>
<a id="back" href="/gaphor/index.php/jtog/view/<?php print $username;?>">&laquo; Back</a>
<h1>Project "<?php print $project;?>" management page</h1>
<p>If you want to remove the whole project, <a href="/gaphor/index.php/jtog/rm/<?php print $username.'/'.$project;?>">click here</a>
<p>The following is a list of your <b>.java</b> files within the <?php print $project;?> project:</p>

<ul class='file-list'>
	<?php if( count( $files ) < 1 ) { ?>
		(no files)
	<?php } else { ?>
	<?php foreach( $files as $file ) { ?>
	<li>
		<a href="/gaphor/index.php/jtog/rm/<?php print $username.'/'.$project.'/'.$file;?>"><img src="/gaphor/images/cross_small.png" /></a>	
		<a href="/gaphor/index.php/jtog/view/<?php print $username.'/'.$project.'/'.$file;?>"><?php print $file;?></a>
	</li>
	<?php } } ?>
</ul>

<h1>New File</h1>
<?php
echo form_open_multipart( "jtog/upload/$username/$project" );

echo form_upload( "userfile" );

echo form_submit( "create", "Upload file" );

echo form_close ();
?>

<h1>Gaphor File</h1>
Download the Gaphor file!<br/>
<a href="/gaphor/index.php/jtog/a2g/<?php print $project;?>"><img src="/gaphor/images/download.png" /></a>
<?php $this->load->view( "footer" ); ?>
