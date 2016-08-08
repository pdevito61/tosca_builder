<?php
header('Content-Type: application/json');
require('tosca_builder.php');

$st = tosca_builder::start()
	->service_template_add()
	->service_template_mod('tosca_definitions_version', 'tosca_simple_profile_for_nfv_1_0_0')
	->service_template_mod('description', 'test del tosca builder')
	->service_template_mod('metadata', array('ID' => 'ID0001', 'vendor' => 'Telecom Italia', 'version' => 'version 1.0'))
	->service_template_mod('imports', ['TOSCA_nfv_definition_1_0' => "C:\DATI_PERSONALI\TILAB\OSS Evolution\TOSCA_php/TOSCA_nfv_definition_1_0.yml"])
	->topology_template_add()
	->topology_template_mod('description', 'topology template di prova')
	->node_template('NODE02', 'tosca.nodes.SoftwareComponent')
	->node_template('NODE01', 'tosca.nodes.nfv.VDU')
	->node_description('NODE01', 'descrizione del nodo 01');
	
// $st->service_template_set();

$st->topology_template_mod('description', 'topology template di prova con aggiunta');
// $st->topology_template_set()
	// ->topology_template_del('description');
	
echo $st->yaml();
// print_r($st->topology_template_get());

$pst = tosca_builder::start()
	->service_template_add($st->get())
	->topology_template_set()
	->service_template_mod('description', 'test del tosca builder, descrizione modificata')
	->topology_template_mod('description', 'nuova descrizione modificata');

echo "\n\nPARSED ENTITIES: \n\n";
echo $pst->yaml();
// print_r($pst);



?>