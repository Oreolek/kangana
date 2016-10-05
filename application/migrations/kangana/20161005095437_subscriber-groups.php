<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Subscriber groups
 */
class Migration_Kangana_20161005095437 extends Minion_Migration_Base {

	/**
	 * Run queries needed to apply this migration
	 *
	 * @param Kohana_Database $db Database connection
	 */
	public function up(Kohana_Database $db)
	{
		$db->query(NULL, "
CREATE TABLE IF NOT EXISTS `groups` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`name` varchar(255) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB;");
		$db->query(NULL, "
CREATE TABLE IF NOT EXISTS `clients_groups` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`client_id` int(11) NOT NULL,
	`group_id` int(11) NOT NULL,
	PRIMARY KEY (`id`),
  KEY `fk_clients_groups_1_idx` (`client_id`),
  KEY `fk_clients_groups_2_idx` (`group_id`)
) ENGINE=InnoDB;");
    $db->query(NULL, "
ALTER TABLE `clients_groups`
  ADD CONSTRAINT `fk_clients_groups_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	ADD CONSTRAINT `fk_clients_groups_2` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
		");
	}

	/**
	 * Run queries needed to remove this migration
	 *
	 * @param Kohana_Database $db Database connection
	 */
	public function down(Kohana_Database $db)
	{
		$db->query(NULL, "DROP TABLE `clients_groups`");
		$db->query(NULL, "DROP TABLE `groups`");
	}

}
