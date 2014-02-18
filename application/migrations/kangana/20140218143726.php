<?php defined('SYSPATH') OR die('No direct script access.');

class Migration_Kangana_20140218143726 extends Minion_Migration_Base {

	/**
	 * Run queries needed to apply this migration
	 *
	 * @param Kohana_Database $db Database connection
	 */
	public function up(Kohana_Database $db)
	{
		$db->query(NULL, 'alter table `instants` drop column `is_draft`');
		$db->query(NULL, "alter table `instants` add column `sent` int(1) not null default '0'");
	}

	/**
	 * Run queries needed to remove this migration
	 *
	 * @param Kohana_Database $db Database connection
	 */
	public function down(Kohana_Database $db)
	{
		$db->query(NULL, 'alter table `instants` drop column `sent`');
		$db->query(NULL, "alter table `instants` add column `is_draft` int(1) not null default '1'");
	}

}
