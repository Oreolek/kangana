<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * new client fields
 */
class Migration_Kangana_20161023121040 extends Minion_Migration_Base {

  public function up(Kohana_Database $db)
  {
		$db->query(NULL, 'ALTER TABLE clients ADD COLUMN sex CHAR(1) NULL ');
		$db->query(NULL, 'ALTER TABLE clients ADD COLUMN referrer VARCHAR(255) NULL ');
		$db->query(NULL, 'ALTER TABLE clients ADD COLUMN city VARCHAR(255) NULL ');
		$db->query(NULL, 'ALTER TABLE clients ADD COLUMN country VARCHAR(255) NULL ');
	}

	/**
	 * Run queries needed to remove this migration
	 *
	 * @param Kohana_Database $db Database connection
	 */
	public function down(Kohana_Database $db)
	{
		$db->query(NULL, 'ALTER TABLE clients DROP COLUMN sex');
		$db->query(NULL, 'ALTER TABLE clients DROP COLUMN referrer');
		$db->query(NULL, 'ALTER TABLE clients DROP COLUMN city');
		$db->query(NULL, 'ALTER TABLE clients DROP COLUMN country');
	}

}
