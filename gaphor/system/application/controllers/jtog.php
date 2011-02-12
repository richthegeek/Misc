<?php

class Jtog extends Controller
{
	function Jtog()
	{
		Controller::Controller();
		$this->load->model( 'ufile' );
	}

	function index()
	{
		// listing files
	
		$projects = $this->ufile->list_projects();
	
		$this->load->view( "list", array( "username"=>$this->ufile->username(), "projects"=>$projects ) );
	}
	
	function login( $do = false, $user = false )
	{
		if( $do )
		{
			$user = $user ? $user : $this->input->post( 'username', TRUE );
			
			preg_match( "/[A-Za-z0-9_\.\-]+/", $user, $data );
			
			$user = $data[0];
					
			$this->ufile->add_user( $user );
			$this->ufile->username( $user );
			redirect( "jtog" );
		}
		// show login
		$this->load->view( "login" );
	}
	
	function logout()
	{
		$this->session->unset_userdata( "username" );
		redirect( "jtog/login" );
	}
	
	function new_project()
	{
			$prj = $this->input->post( 'name', TRUE );		
			preg_match( "/[A-Za-z0-9_\.\-]+/", $prj, $data );
			$project = $data[0];
			
			$this->ufile->add_project( $project );
			redirect( "jtog/project/$project" );
	}
	
	function project( $name = false )
	{
		if( !$name ) return $this->index();
		$files = $this->ufile->list_files( $this->ufile->username(), $name );	
		$this->load->view( "project", array( "username"=>$this->ufile->username(), "project"=>$name, "files"=>$files ) );
	}
	
	function upload( $username, $project )
	{
		$config['upload_path'] = "./files/$username/$project";
		$config['allowed_types'] = 'text/x-java|.java|java|text/plain';
		$config['max_size']	= '100';
		
		$this->load->library('upload', $config);
		
		if( !$this->upload->do_upload() )
		{
			print $this->upload->display_errors();
		}
		else
		{
			redirect( "jtog/project/$project" );
		}
	}
	
	function rm( $user, $project = false, $file = false )
	{
		if( $file )
		{
			$this->ufile->rm_file( $user, $project, $file );
			redirect( "jtog/project/$project" );
		}
		else if( $project )
		{
			$this->ufile->rm_project( $user, $project );
			redirect( "jtog" );
		}
		else
		{
			$this->ufile->rm_user( $user );
			redirect( "jtog/login" );
		}
	}
	
	function view( $user, $project = false, $file = false )
	{
		if( $file )
		{
			print $this->ufile->get_file( $user, $project, $file );
		}
		else if( $project )
		{
			$this->project( $project );
		}
		else
		{
			$this->login( true, $user );
		}
	}
	
	function j2a( $project = false )
	{
		$this->load->model( "j2a" );
	
		$user = $this->ufile->username();
		
		$files = $this->ufile->list_files( $user, $project );
		
		$arr = $this->j2a->load_project( $user, $project, $files );		
		
		//print_r( $arr );	
			
		return $arr;
	}
	
	function a2g( $project )
	{
		$this->load->model( "j2g" );
		$arr = $this->j2a( $project );
		$gaph = $this->j2g->array_to_xml( $arr );
		
		$this->load->helper( "download" );
		
		force_download( $project.".gaphor", $gaph );
	}
}
