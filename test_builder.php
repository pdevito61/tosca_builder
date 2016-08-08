<?php
header('Content-Type: application/json');
require('tosca_builder.php');

$st = tosca_builder::start()
	->service_template_add()
	->service_template_mod('tosca_definitions_version', 'tosca_simple_profile_for_nfv_1_0_0')
	->service_template_mod('description', 'test del tosca builder')
	->service_template_mod('metadata', ['ID' => 'ID0001', 'vendor' => 'Telecom Italia', 'version' => 'version 1.0'])
	->service_template_mod('imports', ['TOSCA_nfv_definition_1_0' => "C:\DATI_PERSONALI\TILAB\OSS Evolution\TOSCA_php/TOSCA_nfv_definition_1_0.yml"])
	->topology_template_add()
	->topology_template_mod('description', 'topology template di prova')
	->node_template_add('NODE01', 'tosca.nodes.nfv.VDU')
	->node_template_mod('NODE01', 'description', 'descrizione del nodo 01')
	->node_template_mod('NODE01', 'properties', ['component_version' => 'version 0.1', 'admin_credential' => 'my credential'])
	->node_template_mod('NODE01', 'attributes', ['tosca_id' => '0003', 'tosca_name' => 'vdu'] )
	->node_template_add('NODE02', 'tosca.nodes.SoftwareComponent');
	
$st->topology_template_mod('description', 'topology template di prova con aggiunta')
	->node_template_set('NODE01')
	->node_template_interface_add('Standard', 'tosca.interfaces.node.lifecycle.Standard')
	->node_template_interface_mod('Standard', 'inputs', array('input1' => '45', 'input2' => '65'))
	->node_template_if_operation_add('create');
	
echo $st->yaml();
// print_r($st->topology_template_get());

// $st->topology_template_del()
	// ->service_template_del('metadata', ['vendor']);

$pst = tosca_builder::start()
	->service_template_add($st->get());

echo "\n\nPARSED ENTITIES: \n\n";
echo $pst->yaml();
// print_r($pst);



?>