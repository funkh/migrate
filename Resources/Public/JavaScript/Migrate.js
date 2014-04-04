var Migrate = {
	initializeDataTables: function() {
		jQuery('.tx_migrate').dataTable({
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
	}
};


jQuery(function() {
	Migrate.initializeDataTables();
});