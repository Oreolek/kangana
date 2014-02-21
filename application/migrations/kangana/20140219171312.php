<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Auto increment migration
 **/
class Migration_Kangana_20140219171312 extends Minion_Migration_Base {

	/**
	 * Run queries needed to apply this migration
	 *
	 * @param Kohana_Database $db Database connection
	 */
	public function up(Kohana_Database $db)
	{
		$db->query(NULL, 'alter table `clients_subscriptions` modify column `id` int(11) not null auto_increment FIRST');
	}

	/**
	 * Run queries needed to remove this migration
	 *
	 * @param Kohana_Database $db Database connection
	 */
	public function down(Kohana_Database $db)
	{
		$db->query(NULL, 'alter table `clients_subscriptions` modify column `id` int(11) not null FIRST');
	}

}
