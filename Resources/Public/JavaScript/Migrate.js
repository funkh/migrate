Migrate = {


	initializeDataTables: function() {
		console.log('initializeDataTables');
		jQuery('.tx_migrate').dataTable();

	}
};


jQuery(function() {
	Migrate.initializeDataTables();
});