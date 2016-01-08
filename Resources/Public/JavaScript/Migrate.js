define([
	'jquery',
	'bootstrap',
	'datatables.net',
	'datatables.bootstrap'
], function($) {

	var Migrate = {};

	Migrate.initializeDataTables = function() {
		$('.tx_migrate').dataTable({
			"order": [[ 3, "desc" ]],
			"columnDefs": [
//				{
//					"targets": 1,
//					"searchable": false
//				},
				{
					"targets": -1,
					"orderable": false
				}
			]
		});
	};

	$(function() {
		Migrate.initializeDataTables();
	});

	return Migrate;

});