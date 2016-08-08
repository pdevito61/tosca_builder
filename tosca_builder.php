<?php
require('lib/tosca_classes6.0.php');

class tosca_builder {
	private $_template = null;
	private $_current_node_name = null;
	private $_current_node_if_name = null;
	private $_current_node_if_operation_name = null;

/*	TOSCA template structure
	
	service_template:
		attributes: 
			description:   string
			tosca_definitions_version:   string
			metadata:   array
			imports:  array
		entities:
			topology_template:
				attributes: 
					description:   string
				entities:
					substitution_mappings:   
						attributes:
							node_type:   string
							capabilities:   array
							requirements:    array
					input: (list)
						attributes:
							description:   string
							value:  constant
							required:   boolean
							default:   constant
							status:   string
							constraints:   operator
					node_template: (list)
						attributes:
							description:   string
							properties:   array 
							attributes:   array 
						entities:
							node_template_requirement: (list)
								attributes:
									capability:  string
									node:   string
									relationship:   string
								entities:
									node_template_req_node_filter:
										attributes:
											properties: array
											capabilities: array
							node_template_capability: (list)
								attributes:
									properties:   array
									attributes:   array
							node_template_interface: (list)
								attributes:
									inputs:   array
								entities:
									node_template_if_operation: (list)
										attributes:
											description:   string
											implementation:   string | string (primary) + array (dependencies)
											inputs:   array 
							node_template_artifact: (list)
								attributes:
									description:   string
									file:   string
									repository:    string
									deploy_path:    string
					group: (list)
						attributes:
							description:   string
							properties:   array
							targets:    array
						entities:
							group_interface: (list)
								attributes:
									inputs:   array
								entities:
									group_if_operation(list)
										attributes:
											description:   string
											implementation:   string | string (primary) + array (dependencies)
											inputs:   array 
					output: (list)
						attributes:
							description:   string
							value:  constant
							required:   boolean
							default:   constant
							status:   string
							constraints:   operator
*/
/*	methods:
	<entity_name>_add() --> create the entity
							parameters:
								$name: (string) name of the entity (only for entity in list)
								$type: (string) typename of entity (if any)
								$en_val: (array) the array representation of the entity (optional)
							returns:
								tosca_builder object
	<entity_name>_mod() --> sets the value of an attribute of the entity
							parameters:
								$name: (string) name of the entity (only for entity in list)
								$attr: (string) attribute name
								$value: (depending on attribute type, see structure above) attibute value
							returns:
								tosca_builder object
	<entity_name>_del() --> delete an attribute or the entire entity 
							parameters:
								$name: (string) name of the entity (only for entity in list)
								$attr: (string) attribute name
								$todel: (depending on attribute type) attribute value to delete (if attribute type is array)
							returns:
								tosca_builder object
	<entity_name>_set() --> sets the current entity name (only for entity in list)
							parameters:
								$name: (string) name of the entity (only for entity in list)
							returns:
								tosca_builder object
	<entity_name>_get() --> returns the array representation of the entity
							parameters:
								$name: (string) name of the entity (only for entity in list)
							returns:
								array representation of the entity
*/
	
	private function _check_st() {
		if ($this->_template === null) $this->service_template_add();
		return $this;
	}
	private function _check_tt() {
		if ($this->_template->get_topology_template() === null) $this->topology_template_add();
		return $this;
	}
	
	public static function start() {
		return new tosca_builder();
	}
	
	public function service_template_add($en_val = null) {
		$this->_template = new tosca_service_template($en_val);
		return $this;
	}
	public function service_template_mod($attr, $value) {
		$this->_check_st();
		switch ($attr) {
			case 'description':
			case 'imports':
			case 'metadata':
			case 'tosca_definitions_version':
				$this->_template->$attr($value);
			break;
			default:  // TBD error handling
			break;
		}
		return $this;
	}
	public function service_template_del($attr, $todel = null) {
		$this->_template->delete($attr, $todel);
		return $this;
	}
	public function service_template_get() {
		return $this->_template->get();
	}

	public function topology_template_add($en_val = array()) {
		$this->_check_st();
		$this->_template->topology_template($en_val, true);
		return $this;
	}
	public function topology_template_mod($attr, $value) {
		$this->_check_st()
			 ->_check_tt();
		$tt = $this->_template->get_topology_template();
		switch ($attr) {
			case 'description':
				$tt->$attr($value);
			break;
			default:  // TBD error handling
			break;
		}
		$this->_template->topology_template($tt->get());
		return $this;
	}
	public function topology_template_del($attr = null, $todel = null) {
		if (($tt = $this->_template->get_topology_template()) !== null) {
			if (!isset($attr)) {
				$this->_template->delete('topology_template');
			}
			else {
				$tt->delete($attr, $todel);
				$this->_template->topology_template($tt->get());
			}
		}
		return $this;
	}
	public function topology_template_get() {
		if (($tt = $this->_template->get_topology_template()) !== null) {
			return $tt->get();
		}
	}

	public function node_template_add($name, $type, $en_val = null) {
		$this->_check_st()
			 ->_check_tt();
		$nt = new tosca_node_template($type, $en_val);
		$tt = $this->_template->get_topology_template();
		$tt->node_templates(array($name => $nt->get()), true);
		$this->_template->topology_template($tt->get());
		$this->_current_node_name = $name;
		return $this;
	}
	public function node_template_mod($name, $attr, $value) {
		if ( is_a($this->_template, 'tosca_service_template') &&
			 (($tt = $this->_template->get_topology_template()) !== null) &&
			 (($nt = $tt->get_node_templates($name)) !== null) ) {  // TBD error handling
			switch ($attr) {
				case 'description':
				case 'properties':
				case 'attributes':
					$nt->$attr($value);
				break;
				default:  // TBD error handling
				break;
			}
			$tt->node_templates([$name => $nt->get()]);
			$this->_template->topology_template($tt->get());
			$this->_current_node_name = $name;
		}
		return $this;
	}
	public function node_template_del($name, $attr = null, $todel = null) {
		if ( is_a($this->_template, 'tosca_service_template') &&
			 (($tt = $this->_template->get_topology_template()) !== null) &&
			 (($nt = $tt->get_node_templates($name)) !== null) ) {  // TBD error handling
			 if (!isset($attr)) {
				$tt->delete('node_templates', [$name]);
				$this->_current_node_name = null;
			 }
			 else {
				$nt->delete($attr, $todel);
				$tt->node_templates([$name => $nt->get()]);
				$this->_current_node_name = $name;
			}
			$this->_template->topology_template($tt->get());
		}
		return $this;
	}
	public function node_template_set($name) {
		if ( is_a($this->_template, 'tosca_service_template') &&
			 (($tt = $this->_template->get_topology_template()) !== null) &&
			 (($nt = $tt->get_node_templates($name)) !== null) ) {  // TBD error handling
			$this->_current_node_name = $name;
		}
		return $this;
	}
	public function node_template_get($name) {
		if ( is_a($this->_template, 'tosca_service_template') &&
			 (($tt = $this->_template->get_topology_template()) !== null) &&
			 (($nt = $tt->get_node_templates($name)) !== null) ) {  // TBD error handling
			$this->_current_node_name = $name;
			return $nt->get();
		}
	}

	public function node_template_interface_add($name, $type, $en_val = null) {
		if ( is_a($this->_template, 'tosca_service_template') &&
			 (($tt = $this->_template->get_topology_template()) !== null) &&
			 (($nt = $tt->get_node_templates($this->_current_node_name)) !== null) ) {  // TBD error handling
			$if = new tosca_interface($type, $en_val);
			$nt->interfaces([$name => $if->get()], true);
			$tt->node_templates([$this->_current_node_name => $nt->get()]);
			$this->_template->topology_template($tt->get());
			$this->_current_node_if_name = $name;
		}
		return $this;
	}
	public function node_template_interface_mod($name, $attr, $value) {
		if ( is_a($this->_template, 'tosca_service_template') &&
			 (($tt = $this->_template->get_topology_template()) !== null) &&
			 (($nt = $tt->get_node_templates($this->_current_node_name)) !== null) &&
			 (($if = $nt->get_interfaces($name)) !== null) ) {  // TBD error handling
			switch ($attr) {
				case 'inputs':
					$if->$attr($value);
				break;
				default:  // TBD error handling
				break;
			}
			$nt->interfaces([$name => $if->get()]);
			$tt->node_templates([$this->_current_node_name => $nt->get()]);
			$this->_template->topology_template($tt->get());
			$this->_current_node_if_name = $name;
		}
		return $this;
	}
	public function node_template_interface_del($name, $attr = null, $todel = null) {
		if ( is_a($this->_template, 'tosca_service_template') &&
			 (($tt = $this->_template->get_topology_template()) !== null) &&
			 (($nt = $tt->get_node_templates($this->_current_node_name)) !== null) &&
			 (($if = $nt->get_interfaces($name)) !== null) ) {  // TBD error handling
			 if (!isset($attr)) {
				$nt->delete('interfaces', [$name]);
				$this->_current_node_if_name = null;
			 }
			 else {
				$if->delete($attr, $todel);
				$nt->interfaces([$name => $if->get()]);
				$this->_current_node_if_name = $name;
			}
			$tt->node_templates([$this->_current_node_name => $nt->get()]);
			$this->_template->topology_template($tt->get());
		}
		return $this;
	}
	public function node_template_interface_set($name) {
		if ( is_a($this->_template, 'tosca_service_template') &&
			 (($tt = $this->_template->get_topology_template()) !== null) &&
			 (($nt = $tt->get_node_templates($this->_current_node_name)) !== null) &&
			 (($if = $nt->get_interfaces($name)) !== null) ) {  // TBD error handling
			$this->_current_node_if_name = $name;
		}
		return $this;
	}
	public function node_template_interface_get($name) {
		if ( is_a($this->_template, 'tosca_service_template') &&
			 (($tt = $this->_template->get_topology_template()) !== null) &&
			 (($nt = $tt->get_node_templates($this->_current_node_name)) !== null) &&
			 (($if = $nt->get_interfaces($name)) !== null) ) {  // TBD error handling
			$this->_current_node_if_name = $name;
			return $if->get();
		}
	}

	public function node_template_if_operation_add($name, $en_val = null) {
		if ( is_a($this->_template, 'tosca_service_template') &&
			 (($tt = $this->_template->get_topology_template()) !== null) &&
			 (($nt = $tt->get_node_templates($this->_current_node_name)) !== null) &&
			 (($if = $nt->get_interfaces($this->_current_node_if_name)) !== null) ) {  // TBD error handling
			$op = new tosca_operation($en_val);
			$if->operations([$name => $op->get()], true);
			$nt->interfaces([$this->_current_node_if_name => $if->get()]);
			$tt->node_templates([$this->_current_node_name => $nt->get()]);
			$this->_template->topology_template($tt->get());
			$this->_current_node_if_operation_name = $name;
		}
		return $this;
	}
	public function node_template_if_operation_mod($name, $attr, $value, $dep = null) {
		if ( is_a($this->_template, 'tosca_service_template') &&
			 (($tt = $this->_template->get_topology_template()) !== null) &&
			 (($nt = $tt->get_node_templates($this->_current_node_name)) !== null) &&
			 (($if = $nt->get_interfaces($this->_current_node_if_name)) !== null) && 
			 (($op = $if->get_operations($name)) !== null) ) {  // TBD error handling
			switch ($attr) {
				case 'description':
				case 'inputs':
					$op->$attr($value);
				break;
				case 'implementation':
					$op->$attr($value, $dep);
				break;
				default:  // TBD error handling
				break;
			}
			$if->operations([$name => $op->get()], true);
			$nt->interfaces([$this->_current_node_if_name => $if->get()]);
			$tt->node_templates([$this->_current_node_name => $nt->get()]);
			$this->_template->topology_template($tt->get());
			$this->_current_node_if_operation_name = $name;
		}
		return $this;
	}
	public function node_template_if_operation_del($name, $attr = null, $todel = null) {
		if ( is_a($this->_template, 'tosca_service_template') &&
			 (($tt = $this->_template->get_topology_template()) !== null) &&
			 (($nt = $tt->get_node_templates($this->_current_node_name)) !== null) &&
			 (($if = $nt->get_interfaces($this->_current_node_if_name)) !== null) && 
			 (($op = $if->get_operations($name)) !== null) ) {  // TBD error handling
			 if (!isset($attr)) {
				$if->delete('operations', [$name]);
				$this->_current_node_if_operation_name = null;
			 }
			 else {
				$op->delete($attr, $todel);
				$if->operations([$name => $op->get()]);
				$this->_current_node_if_operation_name = $name;
			}
			$nt->interfaces([$this->_current_node_if_name => $if->get()]);
			$tt->node_templates([$this->_current_node_name => $nt->get()]);
			$this->_template->topology_template($tt->get());
		}
		return $this;
	}
	public function node_template_if_operation_set($name) {
		if ( is_a($this->_template, 'tosca_service_template') &&
			 (($tt = $this->_template->get_topology_template()) !== null) &&
			 (($nt = $tt->get_node_templates($this->_current_node_name)) !== null) &&
			 (($if = $nt->get_interfaces($this->_current_node_if_name)) !== null) && 
			 (($op = $if->get_operations($name)) !== null) ) {  // TBD error handling
			$this->_current_node_if_operation_name = $name;
		}
		return $this;
	}
	public function node_template_if_operation_get($name) {
		if ( is_a($this->_template, 'tosca_service_template') &&
			 (($tt = $this->_template->get_topology_template()) !== null) &&
			 (($nt = $tt->get_node_templates($this->_current_node_name)) !== null) &&
			 (($if = $nt->get_interfaces($this->_current_node_if_name)) !== null) && 
			 (($op = $if->get_operations($name)) !== null) ) {  // TBD error handling
			$this->_current_node_if_operation_name = $name;
			return $op->get();
		}
	}

/*
	public function _add($en_val = null) {
		return $this;
	}
	public function _mod($attr, $value) {
		return $this;
	}
	public function _del($attr = null, $todel = null) {
		return $this;
	}
	public function _set($name) {
		return $this;
	}
	public function _get() {
	}
*/
	public function yaml() {
		return $this->_template->yaml();
	}
	public function get() {
		return $this->_template->get();
	}
}
?>