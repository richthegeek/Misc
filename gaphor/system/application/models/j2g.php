<?php

class J2g extends Model
{
	var $xml = array();
	var $doc = "";
	var $pkg = "";
	var $pointer;

	var $id_list = array();
	var $rand;
	
	var $doc_id = 0;
	var $package_id = 0;

	function J2g()
	{
		Model::Model();
		$this->rand = rand()/rand();
		$this->doc_id = substr( md5( $this->rand ), 0, 28 );	
		$this->pointer =& $this->xml;	
	}
	
	function array_to_xml( $arr )
	{
		$this->create_document();
		
		foreach( $arr as $file )
		{
			$class_sxe = $this->create_class( $file['class'] );
			
			if( count( $file['attrs'] ) > 0 )
			{
				$oa = $class_sxe->addChild( "ownedAttribute" );
				$oa->addChild("reflist");
				foreach( $file['attrs'] as $attr )
				{
					$this->add_attribute( $attr, $class_sxe );
				}
			}
			if( count( $file['funcs'] ) > 0 )
			{
				$oa = $class_sxe->addChild( "ownedOperation" );
				$oa->addChild("reflist");
				foreach( $file['funcs'] as $func )
				{
					$this->add_operation( $func, $class_sxe );
				}
			}			
		}
		
		return $this->xml->asXML();
	}
	
	function gen_id( $type )
	{
		$this->doc_id++;
		$ids = (string)$this->doc_id;
		$id = 	strtoupper( "DCE:"
				.substr( $ids, -8, 8 )."-"
				.substr( $ids, -12, 4 )."-"
				.substr( $ids, -16, 4 )."-"
				.substr( $ids, -20, 4 )."-"
				.substr( $ids, -28, 8 ) );	
		$this->id_list[ $type ][] = $id;
		return $id;
	}
	
	function create_document()
	{
		// generate random document ID
		$xmlbase = $this->load->view( "xml/gaphor_base", array(), TRUE );
		
		$this->xml = simplexml_load_string( $xmlbase );

		$this->package_id = $this->gen_id( "null" );

		$this->xml->Package[0]->addAttribute( "id", $this->package_id );
		$this->xml->Diagram[0]->addAttribute( "id", $this->gen_id( "null" ) );
		$this->xml->Diagram[0]->package[0]->ref[0]->addAttribute( "refid", $this->package_id );
	}
	
	function create_class( $name )
	{
		$xmldiag = simplexml_load_string($this->load->view( "xml/class_canvas", array(), TRUE ));
		$xmlclass= simplexml_load_string($this->load->view( "xml/class_class", array(), TRUE ));
		
		$class_id = $this->gen_id( "class" );
		$item_id = $this->gen_id( "item" );
		
		$xmldiag->addAttribute( "id", $item_id );
		$xmldiag->subject[0]->ref[0]->addAttribute( "refid", $class_id );
		$xmldiag->matrix->val = $this->random_matrix();
		
		$xmlclass->name[0]->val[0] = $name;
		$xmlclass->addAttribute( "id", $class_id );
		$xmlclass->package[0]->ref[0]->addAttribute( "refid", $this->package_id );
		$xmlclass->presentation[0]->reflist[0]->ref[0]->addAttribute( "refid", $item_id );
		
		simplexml_append( $this->xml->Diagram[0]->canvas, $xmldiag );
		simplexml_append( $this->xml, $xmlclass );
		
		$classlist =& $this->xml->xpath("//Class");
				
		$class =& $classlist[ count( $classlist ) - 1 ];
				
		return $class;
	}
	
	function add_operation( $function, &$class )
	{
		// 	add operation tag
		// 	add return
		//		add parameter (return) tag
		// 		add literal spec (return type) tag
		// 		add misc other literal specs (min, upper, lower)
		//	add arguments
		//		add parameter (argname) tag
		//		add literal spec (arg type) tag
		//		add misc other litspecs
		
		$class_attrs  = $class->attributes();
		$class_id = $class_attrs['id'];
	
	// operation tag
		$op_id = $this->gen_id( "operation" );
		$ret_id= $this->gen_id( "retval" );

		// add ref to class reflist	
		
		$ref = $class->ownedOperation->reflist->addChild("ref");
		$ref->addAttribute( "refid", $op_id );
				
		// create Operation tag
		$op = $this->xml->addChild( "Operation" );
		$op->addAttribute( "id", $op_id );
		
		// add class_ ref
		$this->add_refid( $op, "class_", $class_id );
		
		// add name string
		$name = $op->addChild( "name" );
		$name->addChild( "val", $function['name'] );
		
		// add presentation reflist (empty)
		$pres = $op->addChild( "presentation" );
		$pres->addChild("reflist");
		
		// add returnResult reflist
		$ret = $op->addChild( "returnResult" );
		$refl = $ret->addChild( "reflist" );
		$ref = $refl->addChild( "ref" );
		$ref->addAttribute( "refid", $ret_id );

		if( $function["public"] == false || $function["protected"] == true )
		{
			$t1 = $op->addChild("visibility");
			$t1->addChild("val", ($function["protected"]?"protected":"private"));
		}

	// parameter (return) tag
		$param = $this->xml->addChild( "Parameter" );
		$param->addAttribute( "id", $ret_id );
		
		// add direction (return)
		$dir = $param->addChild( "direction" );
		$val = $dir->addChild( "val", "return" );
		
		// generate three ids
		$lower_id = $this->gen_id( "null" );
		$upper_id = $this->gen_id( "null" );
		$type_id = $this->gen_id( "type" );
	
		$this->add_refid( $param, "ownerReturnParam", $op_id );		
		$this->add_refid( $param, "lowerValue", $lower_id );
		$this->add_refid( $param, "upperValue", $upper_id );
		$this->add_refid( $param, "typeValue", $type_id );	
		
		$litspec = array();
		if( strlen( $function['type'] ) > 0 ) {
			$litspec[ $type_id ] = $function['type'];
		} else {
			$litspec[ $type_id ] = false;
		}
		
		$litspec[ $lower_id ] = false;
		$litspec[ $upper_id ] = false;
		
		$this->add_literal_specs( $litspec );	
	
	// ARGUMENTS
	//	add each argument id to formalParameter tag (create it in op)
		$args = $function['arguments'];
		if( count( $args ) > 0 && strlen( trim( $args[0] ) ) > 1  )
		{
			// add formalParameter reflist
			$fa = $op->addChild( "formalParameter" );
			$farl = $fa->addChild( "reflist" );

			foreach( $args as $arg )
			{
				$arg = trim( $arg );

				list( $type, $name ) = explode( " ", $arg );
				
				$param = $this->xml->addChild( "Parameter" );
				$pid = $this->gen_id( "param" );
				$param->addAttribute( "id", $pid );
			
				$ref = $farl->addChild( "ref" );
				$ref->addAttribute( "refid", $pid );
			
				$nm = $param->addChild( "name" );
				$nm->addChild( "val", $name );
				
				$tid = $this->gen_id( "type" );
				$dv = $this->gen_id( "null" );
				$lv = $this->gen_id( "null" );
				$uv = $this->gen_id( "null" );
			
				$this->add_refid( $param, "defaultValue", $dv );
				$this->add_refid( $param, "lowerValue", $lv );
				$this->add_refid( $param, "upperValue", $uv );
				
				$this->add_refid( $param, "ownerFormalParam", $op_id );
				$this->add_refid( $param, "typeValue", $tid );
				
				$ls = array();
				
				$ls[ $tid ] = $type;
				$ls[ $dv ] = false;
				$ls[ $lv ] = false;
				$ls[ $uv ] = false;
				$this->add_literal_specs( $ls );
			}	
		}
	}
	
	function add_refid( &$target, $name, $id )
	{
		$tag = $target->addChild( $name );
		$ref = $tag->addChild("ref");
		$ref->addAttribute( "refid", $id );
	}
	
	function add_literal_specs( $array )
	{
		//	Array = nx [refid] = (false|val)
		foreach( $array as $id=>$val )
		{
			$t1 = $this->xml->addChild( "LiteralSpecification" );
			$t1->addAttribute( "id", $id );
			if( $val )
			{
				$t2 = $t1->addChild( "value" );
				$t2->addChild( "val", $val );
			}
		}	
	}
		
	function add_attribute( $attr, &$class )
	{
		$class_attrs  = $class->attributes();
		$class_id = $class_attrs['id'];
	
		$prop_id = $this->gen_id( "attr" );
		
		$ref = $class->ownedAttribute[0]->reflist[0]->addChild("ref");
		$ref->addAttribute("refid", $prop_id);
		
		$prop = $this->xml->addChild("Property");
		$prop->addAttribute("id",$prop_id);
		
		$tmp = $prop->addChild("class_");
		$t2  = $tmp->addChild( "ref" );
		$t2->addAttribute("refid",$class_id);
		
		$dv_id = $this->gen_id( "null" );
		$tv_id = $this->gen_id( "null" );
		$uv_id = $this->gen_id( "null" );
		$lv_id = $this->gen_id( "null" );
		
		$dv = $prop->addChild("defaultValue");
		$dr = $dv->addChild("ref");
		$dr->addAttribute("refid",$dv_id);

		$dv = $prop->addChild("lowerValue");
		$dr = $dv->addChild("ref");
		$dr->addAttribute("refid",$dv_id);

		$dv = $prop->addChild("upperValue");
		$dr = $dv->addChild("ref");
		$dr->addAttribute("refid",$uv_id);										

		$dv = $prop->addChild("typeValue");
		$dr = $dv->addChild("ref");
		$dr->addAttribute("refid",$tv_id);
		
		$t1 = $prop->addChild("presentation");
		$t1->addChild("reflist");
		
		$t1 = $prop->addChild("name");
		$t1->addChild("val", trim($attr["name"]));
		
		if( $attr["public"] == false || $attr["protected"] == true )
		{
			$t1 = $prop->addChild("visibility");
			$t1->addChild("val", ($attr["protected"]?"protected":"private"));
		}
		
		$litspec = array();
		
		$litspec[ $tv_id ] = $attr["type"];
		$litspec[ $dv_id ] = false;
		$litspec[ $lv_id ] = false;
		$litspec[ $uv_id ] = false;
		
		$this->add_literal_specs( $litspec );
		

	}
	
	function random_matrix()
	{
		$p1 = (int)rand(0, 800);
		$p2 = (int)rand(0, 800);
		return "(1.0, 0.0, 0.0, 1.0, $p1.0, $p2.0)";
	}
}

function simplexml_append(SimpleXMLElement $parent, SimpleXMLElement $new_child){
   $node1 = dom_import_simplexml($parent);
   $dom_sxe = dom_import_simplexml($new_child);
   $node2 = $node1->ownerDocument->importNode($dom_sxe, true);
   $node1->appendChild($node2);
}
