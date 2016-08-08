<?php

require('tosca_classes6.0.php');
// require('mem_usage.php');

header('Content-Type: application/json');

$st = new tosca_service_template();
	$st->tosca_definitions_version('tosca_simple_profile_for_nfv_1_0_0');
	$st->description('Example of service template for tosca classes 5.0');
	$st->metadata(array('ID' => 'ID0001', 'vendor' => 'Telecom Italia', 'version' => 'version 1.0'));
	$st->imports(array(	'TOSCA_nfv_definition_1_0' => "C:\DATI_PERSONALI\TILAB\OSS Evolution\TOSCA_php/TOSCA_nfv_definition_1_0.yml"));

	$tt = new tosca_topology_template();

		$tt->description('Example of topology template for tosca classes');

		$sm = new tosca_substitution_mapping('tosca.nodes.nfv.VNF');
			$sm->requirements(array('virtualLink1' => operator::map_of('CP21', 'virtualLink')));
			$sm->capabilities(array('forwarder1' => operator::map_of('CP21', 'forwarder')));
		$tt->substitution_mappings($sm->get());

		$ip = new tosca_parameter('integer');
			$ip->description('Example of input parameter');
			$ip->keys(array('value' => 4, 'required' => false, 'default' => 2, 'status' => 'my status', 'constraints' => [operator::in_range(1,4)]));
		$tt->inputs(array('number_of_cpu' => $ip->get()));
		
		$VDU1 = new tosca_node_template('tosca.nodes.nfv.VDU');
			$VDU1->description('Example of node template');
			$VDU1->properties(array('component_version' => 'version 0.1', 'admin_credential' => 'my credential'));
			$VDU1->attributes(array('tosca_id' => '0003', 'tosca_name' => 'vdu'));
			$VDU1->capabilities(array('high_availability' => 'YES', 'virtualbinding' => 'YES', 'monitoring_parameter' => 'YES'));
			// $cap = new tosca_capability('tosca.capabilities.nfv.HA');
				// $cap->properties(array('component_version' => 'version 0.1', 'admin_credential' => 'my credential'));
				// $cap->attributes(array('tosca_id' => '0003', 'tosca_name' => 'vdu'));
			
			$VDU1->requirements(array('high_availability' => 'YES' ));   // short notation for requirements
			$rq = new tosca_requirement();    // extended notation for requirements
				$rq->keys(array('capability' => 'tosca.capabilities.Node', 'node' => 'tosca.nodes.Compute', 'relationship' => 'tosca.relationships.HostedOn'));
				$nf = new tosca_node_filter();
					$nf->properties(array('num_cpus' => operator::in_range(1,4),'mem_size' => operator::equal('2 GB')));
					$nf->capabilities(array('hosts' => array('num_cpus' => operator::in_range(1,4),'mem_size' => operator::equal('2 GB')),
											'os' => array('architecture' => operator::equal('x86_64'),'type' => 'linux', 'distribution' => 'ubuntu')));
				$rq->keys(array('node_filter' => $nf->get()));
			$VDU1->requirements(array('host' => $rq->get())); 

			$if = new tosca_interface('tosca.interfaces.node.lifecycle.Standard');  
				$if->inputs(array('input1' => '45', 'input2' => '65'));
				$operation = new tosca_operation();			// extended notation for operation
					$operation->description('Example of operation');
					$operation->implementation('implemen.sh', array('setup.sh','library.rpm'));
					$operation->inputs(array('input1' => '45', 'input2' => '65'));
				$if->operations(array('create' => $operation->get(), 
									  'configure' => 'vdu1_configure.sh'));     // short notation for operation
			$VDU1->interfaces(array('Standard' => $if->get()));
	
			$VDU1->artifacts(array('VM_image' => 'vdu1.image'));   // short notation for artifacts
			
			$ar = new tosca_artifact('tosca.artifacts.File');		// extended notation for artifacts
				$ar->description('Example of artifact');
				$ar->keys(array('file' => "C:\DATI_PERSONALI\TILAB\OSS Evolution\TOSCA_php/class_template_generated.yml", 'repository' => "MY_REPOSITORY_NAME", 'deploy_path' => "C:\DATI_PERSONALI\TILAB\OSS Evolution\TOSCA_php/"));
			$VDU1->artifacts(array('my_yaml_descriptor' => $ar->get()));

		$tt->node_templates(array('VDU1' => $VDU1->get()));

		$gr1 = new tosca_group('tosca.groups.nfv.vnffg');
			$gr1->description('Example of group');
			$gr1->properties(array('vendor' => 'Pinco pallino SPA', 'number_of_endpoints' => 2, 'dependent_virtual_link' => array('VL1','VL2','VL4')));
			$gr1->targets(array('VDU1','VDU1','VDU1'));
			$if2 = new tosca_interface('tosca.interfaces.node.lifecycle.Standard');  
				$if2->inputs(array('input1' => '45', 'input2' => '65'));
				$operation1 = new tosca_operation();			// extended notation for operation
					$operation1->description('Example of operation 1');
					$operation1->implementation('implemen.sh', array('setup.sh','library.rpm'));
					$operation1->inputs(array('input1' => '45', 'input2' => '65'));
				$operation2 = new tosca_operation();			// extended notation for operation
					$operation2->description('Example of operation 2');
					$operation2->implementation('activate.sh', array('setup.sh','library.rpm'));
					$operation2->inputs(array('input3' => '45', 'input4' => '65'));
				$if2->operations(array('create' => $operation1->get(), 'start' => $operation2->get(), 'stop' => 'stop.sh')); 
			$gr1->interfaces(array('Standard' => $if2->get()));
		$tt->groups(array('VNFFG1' => $gr1->get()));

		$op = new tosca_parameter('scalar-unit.size');
			$op->description('Example of output parameter');
			$op->keys(array('value' => '10 GB', 'required' => false, 'default' => '5 GB', 'status' => 'my status', 'constraints' => [operator::equal(4)]));
		$tt->outputs(array('RAM allocated' => $op->get()));

	$st->topology_template($tt->get());

	// mem_usage("completato service template template");

// print_r($if);

echo $st->yaml();


// print_r($VDU1->type_info());

$stp = new tosca_service_template($st->get());

// mem_usage("completato parsing del service template template");

$ttp = $stp->get_topology_template();
$smp = $ttp->get_substitution_mappings();
$ipp = $ttp->get_inputs('number_of_cpu');
$ntp = $ttp->get_node_templates('VDU1');
$cbp = $ntp->get_capabilities();
$rqp = $ntp->get_requirements('host');
$nfp = $rqp->get_node_filter();
$ifp = $ntp->get_interfaces('Standard');
$opp = $ifp->get_operations('configure');
$arp = $ntp->get_artifacts('my_yaml_descriptor');
$grp = $ttp->get_groups('VNFFG1');

// mem_usage("completata get del service template template");

// $ifp->delete('operations', ['create']);
// $arp->delete('repository');

// $ntp->artifacts(array('my_yaml_descriptor' => $arp->get()));
// $ifp->operations(array('configure' => $opp->get()));
// $ntp->interfaces(array('Standard' => $ifp->get()));
// $rqp->keys(array('node_filter' => $nfp->get()));
// $ntp->requirements(array('host' => $rqp->get()));
// $ttp->groups(array('VNFFG1' => $grp->get()));
// $ttp->node_templates(array('VDU1' => $ntp->get()));
// $ttp->inputs(array('number_of_cpu' => $ipp->get()));
// $ttp->substitution_mappings($smp->get());
// $stp->topology_template($ttp->get());

// echo $stp->yaml();


echo "\n\nPARSED ENTITIES: \n\n";
// print_r($ifp);

?>