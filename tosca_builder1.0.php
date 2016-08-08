<?php
require('tosca_classes6.0.php');

class tosca_builder {
	private $_template = null;
	
	private function _check_st() {
		if ($this->_template === null) $this->service_template();
		return $this;
	}
	private function _check_tt() {
		if ($this->_template->get_topology_template() === null) $this->topology_template();
		return $this;
	}
	
	public static function start() {
		return new tosca_builder();
	}
	
	public function service_template($template = null) {
		$this->_template = new tosca_service_template($template);
		return $this;
	}
	public function service_description($ds) {
		$this->_check_st();
		$this->_template->description($ds);
		return $this;
	}
	public function service_imports($ip) {
		$this->_check_st();
		$this->_template->imports($ip);
		return $this;
	}
	public function topology_template() {
		$this->_check_st();
		$this->_template->topology_template(array(), true);
		return $this;
	}
	public function topology_description($ds) {
		$this->_check_st()
			 ->_check_tt();
		$tt = $this->_template->get_topology_template();
		$tt->description($ds);
		$this->_template->topology_template($tt->get());
		return $this;
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
	
	public function delete() { // TBD
	}
	public function service_template_get($entity) {
	}
	public function service_template_add($entity, $value) {
	}
	public function service_template_delete($entity) {
	}
	public function node_template_add($name, $entity, $value) {
	}
	public function node_template_get($name, $entity ) {
	}
	public function node_template_delete($name, $entity) {
	}
	
	public function yaml() {
		return $this->_template->yaml();
	}
	public function get() {
		return $this->_template->get();
	}
}
?>