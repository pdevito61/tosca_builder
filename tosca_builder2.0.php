<?php
require('tosca_classes6.0.php');

class tosca_builder {
	private $_template = null;
	private $_current_entity_obj = null;
	private $_current_entity_name = null;
	
	function __construct() {
		$this->_current_entity_obj = (object) null;
	}
	
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
	
	public function current_reset() {
		$this->_current_entity_obj = (object) null;
		$this->_current_entity_name = null;
	}

	public function service_template_add($template = null) {
		$this->_template = new tosca_service_template($template);
		$this->_current_entity_obj = $this->_template;
		$this->_current_entity_name = 'NONAME';
		return $this;
	}
	public function service_template_mod($attr, $value) {
		$this->_check_st();
		if (is_a($this->_current_entity_obj, 'tosca_'.substr(__FUNCTION__,0,-4))) {  // TBD error handling
			switch ($attr) {
				case 'description':
				case 'imports':
				case 'metadata':
				case 'tosca_definitions_version':
					$this->_current_entity_obj->$attr($value);
				break;
				default:  // TBD error handling
				break;
			}
		}
		return $this;
	}
	public function service_template_del($attr, $todel = null) {
		if (is_a($this->_current_entity_obj, 'tosca_'.substr(__FUNCTION__,0,-4))) {  // TBD error handling
			$this->_current_entity_obj->delete($attr, $todel);
		}
		return $this;
	}
	public function service_template_set() {
		$this->_check_st();
		$this->_current_entity_obj = $this->_template;
		$this->_current_entity_name = 'NONAME';
		return $this;
	}
	public function service_template_get() {
		if (is_a($this->_current_entity_obj, 'tosca_'.substr(__FUNCTION__,0,-4))) {  // TBD error handling
			return $this->_current_entity_obj->get();
		}
	}


	public function topology_template_add() {
		$this->_check_st();
		$this->_template->topology_template(array(), true);
		$this->_current_entity_obj = $this->_template->get_topology_template();
		$this->_current_entity_name = 'NONAME';
		return $this;
	}
	public function topology_template_mod($attr, $value) {
		$this->_check_st()
			 ->_check_tt();
		if (is_a($this->_current_entity_obj, 'tosca_'.substr(__FUNCTION__,0,-4))) {  // TBD error handling
			switch ($attr) {
				case 'description':
					$this->_current_entity_obj->$attr($value);
				break;
				default:  // TBD error handling
				break;
			}
		}
		$this->_template->topology_template($this->_current_entity_obj->get());
		return $this;
	}
	public function topology_template_del($attr, $todel = null) {
		if (is_a($this->_current_entity_obj, 'tosca_'.substr(__FUNCTION__,0,-4))) {  // TBD error handling
			$this->_current_entity_obj->delete($attr, $todel);
		}
		$this->_template->topology_template($this->_current_entity_obj->get());
		return $this;
	}
	public function topology_template_set() {
		$this->_check_st()
			 ->_check_tt();
		$this->_current_entity_obj = $this->_template->get_topology_template();
		$this->_current_entity_name = 'NONAME';
		return $this;
	}
	public function topology_template_get() {
		if (is_a($this->_current_entity_obj, 'tosca_'.substr(__FUNCTION__,0,-4))) {  // TBD error handling
			return $this->_current_entity_obj->get();
		}
	}


	public function node_template_add($name, $type) {
		$this->_check_st()
			 ->_check_tt();
		if (is_a($this->_current_entity_obj, 'tosca_topology_template')) {  // TBD error handling
			$nt = new tosca_node_template($type);
			$this->_current_entity_obj->node_templates(array($name => $nt->get()), true);
			$this->_template->topology_template($this->_current_entity_obj->get());
			$this->_current_entity_obj = $this->_current_entity_obj->get_node_templates($name);
			$this->_current_entity_name = $name;
		}
		return $this;
	}
	public function node_template_mod($name, $attr, $value) {
		if (is_a($this->_current_entity_obj, 'tosca_'.substr(__FUNCTION__,0,-4)) &&
			$this->_current_entity_name == $name) {  // TBD error handling
			switch ($attr) {
				case 'description':
				case 'properties':
				case 'attributes':
					$this->_current_entity_obj->$attr($value);
				break;
				default:  // TBD error handling
				break;
			}
			$tt = $this->_template->get_topology_template();
			$tt->node_templates([$name => $this->_current_entity_obj->get()]);
			$this->_template->topology_template($tt->get());
		}
		return $this;
	}
	public function node_template_del($attr, $todel = null) {
		return $this;
	}
	public function node_template_set() {
		return $this;
	}
	public function node_template_get() {
	}

	public function _add() {
		return $this;
	}
	public function _mod($attr, $value) {
		return $this;
	}
	public function _del($attr, $todel = null) {
		return $this;
	}
	public function _set() {
		return $this;
	}
	public function _get() {
	}



	public function node_template($name, $type) {
		$this->_check_st()
			 ->_check_tt();
		$nt = new tosca_node_template($type);
		$tt = $this->_template->get_topology_template();
		$tt->node_templates(array($name => $nt->get()), true);
		$this->_template->topology_template($tt->get());
		return $this;
	}
	public function node_description($name, $ds) {
		if (($tt = $this->_template->get_topology_template()) !== null) {
			if (($nt = $tt->get_node_templates($name)) !== null) {
				$nt->description($ds);
				$tt->node_templates(array($name => $nt->get()));
				$this->_template->topology_template($tt->get());
			}
		}
		return $this;
	}
	
	
	public function yaml() {
		return $this->_template->yaml();
	}
	public function get() {
		return $this->_template->get();
	}
}
?>