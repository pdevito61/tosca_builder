<?php
header('Content-Type: application/json');
require('tosca_builder.php');

$st = tosca_builder::start()
	->service_template()
	->service_description('test del tosca builder')
	->service_imports(['TOSCA_nfv_definition_1_0' => "C:\DATI_PERSONALI\TILAB\OSS Evolution\TOSCA_php/TOSCA_nfv_definition_1_0.yml"])
	->topology_template()
	->topology_description('topology template di prova')
	->node_template('NODE02', 'tosca.nodes.SoftwareComponent')
	->node_template('NODE01', 'tosca.nodes.nfv.VDU')
	->node_description('NODE01', 'descrizione del nodo 01');

$st->topology_description('topology template di prova con aggiunta');

echo $st->yaml();
// print_r($st);

$pst = tosca_builder::start()
	->service_template($st->get())
	->service_description('test del tosca builder, descrizione modificata')
	->topology_description('nuova descrizione modificata');

echo "\n\nPARSED ENTITIES: \n\n";
echo $pst->yaml();
// print_r($pst);

?>