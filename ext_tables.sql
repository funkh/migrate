CREATE TABLE tx_migrate_migrations (
	uid int(11) unsigned NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	executed int(11) unsigned DEFAULT '0' NOT NULL,
	version varchar(10) NOT NULL default '',
	extension_key varchar(255) DEFAULT '' NOT NULL,
	script_path varchar(255) DEFAULT '' NOT NULL,
	query text,
);