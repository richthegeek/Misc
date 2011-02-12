<?php

class J2a extends Model
{
	var $base;

	function J2a()
	{
		Model::Model();
		$this->base = $_SERVER['DOCUMENT_ROOT']."gaphor/files/";
	}

	function load_project( $username, $project, $files )
	{
		$fileA = array();
		foreach( $files as $k=>$file )
		{
			$files[ $file ] = file_get_contents( $this->base.$username."/".$project."/".$file );
			unset( $files[ $k ] );
			
			$fileA[ $file ] = $this->parse_java( $files[$file] );
		}
		
		return $fileA;
	}
	
	function parse_java( $str )
	{
		$has_class  = preg_match( "/class ([A-Za-z][A-Za-z0-9_]*)/", $str, $class );
		$has_extend = preg_match( "/extends ([A-Za-z][A-Za-z0-9_]*)/", $str, $extend );
		$has_funcs  = preg_match_all( "/(public|private)( static)?( protected)?( void| double| boolean| String| string| float| real| int| [A-Za-z0-9_]+)?( [A-Za-z][A-Za-z0-9_]*)\((.*)\)/", $str, $funcs );
		$has_attrs	= preg_match_all( "/(public|private)( static)?( protected)?( void| double| boolean| String| string| float| real| int| [A-Za-z0-9_]+)?( [A-Za-z][A-Za-z0-9_= -]*)\;/", $str, $attrs );
	
		$array_fields = array( 	"full_string", 
								"public", 
								"static",
								"protected",
								"type", 
								"name",
								"arguments"
							);
		$array_evals  = array( 	"return %1;", 
								"return (trim(%1) == 'public');",
								"return (trim(%1) == 'static');",
								"return (trim(%1) == 'protected');",
								"return trim(%1);",
								"return trim(%1);",
								"return explode(',',%1);"
							);
		$fl = array();
	
		foreach( $funcs as $i=>$field )
		{
			$key = $array_fields[$i];
			foreach( $field as $j=>$function )
			{
				$eval = str_replace( "%1", "'$function'", $array_evals[$i] );
				$fl[ $j ][ $key ] = eval( $eval );
			}
		}
		
		$array_fields = array( 	"full_string", 
								"public", 
								"static",
								"protected",
								"type", 
								"name"
							);
		$array_evals  = array( 	"return %1;", 
								"return (trim(%1) == 'public');",
								"return (trim(%1) == 'static');",
								"return (trim(%1) == 'protected');",
								"return trim(%1);",
								"return preg_replace('/(=(.*))/', '', %1);"
							);
		$al = array();
	
		foreach( $attrs as $i=>$field )
		{
			$key = $array_fields[$i];
			foreach( $field as $j=>$attr )
			{
				$eval = str_replace( "%1", "'$attr'", $array_evals[$i] );
				$al[ $j ][ $key ] = eval( $eval );
			}
		}
		
		return array( "class"=>$class[1], "extends"=>($has_extend?$extend[1]:false), "funcs"=>$fl, "attrs"=>$al );
	
	}
	
}

?>
