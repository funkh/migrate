# CLI Context

`./typo3/cli_dispatch.phpsh migrate`


# Usage

Migration definitions to be used with EXT:migrate need to be within a folder called **Migrations** in any installed TYPO3 extension.
For examples please have a look at typo3-cms-extensions/migrate_samples project!

Migrations of specific extensions can be blacklisted with the packageBlacklist array under the EXTCONF of migrate.
###Example:
$conf = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['migrate'];
if (isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['migrate']['packageBlacklist'])) {
	$packageBlacklist = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['migrate']['packageBlacklist'];
} else {
	$packageBlacklist = array();
}
// Disable migrations from these packages:
$packageBlacklist = array_merge(
	$packageBlacklist,
	array(
		'extkey1' => '100,101',
		'extkey2' => '', // blacklist all versions
	)
);
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['migrate']['packageBlacklist'] = $packageBlacklist;
###