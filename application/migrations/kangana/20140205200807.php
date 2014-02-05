<?php defined('SYSPATH') OR die('No direct script access.');

class Migration_Kangana_20140205200807 extends Minion_Migration_Base {

	/**
	 * Run queries needed to apply this migration
	 *
	 * @param Kohana_Database $db Database connection
	 */
	public function up(Kohana_Database $db)
	{
		$db->query(NULL, "
CREATE TABLE IF NOT EXISTS `clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;");
		
    $db->query(NULL, "
CREATE TABLE IF NOT EXISTS `clients_courses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_clients_courses_1_idx` (`client_id`),
  KEY `fk_clients_courses_2_idx` (`course_id`)
) ENGINE=InnoDB;");
    
    $db->query(NULL, "
CREATE TABLE IF NOT EXISTS `letters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `text` longtext,
  `course_id` int(11) DEFAULT NULL,
  `order` int(11) DEFAULT NULL,
  `subject` varchar(128) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_letters_1_idx` (`course_id`)
) ENGINE=InnoDB;");

    $db->query(NULL, "
CREATE TABLE IF NOT EXISTS `pages` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `content` longtext NOT NULL,
  `is_draft` int(1) NOT NULL DEFAULT '0',
  `posted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;");

    $db->query(NULL, "
CREATE TABLE IF NOT EXISTS `courses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `period` int(11) DEFAULT NULL COMMENT 'period in days (1 = daily, 7 = weekly etc.)',
  `description` tinytext,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;");

    $db->query(NULL, "
CREATE TABLE IF NOT EXISTS `tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `letter_id` int(11) DEFAULT NULL,
  `client_id` int(11) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `status` int(11) DEFAULT NULL COMMENT 'status flag (see php model)',
  PRIMARY KEY (`id`),
  KEY `fk_mail_queue_1_idx` (`client_id`),
  KEY `fk_mail_queue_2_idx` (`letter_id`)
) ENGINE=InnoDB;");

    $db->query(NULL, "
CREATE TABLE IF NOT EXISTS `subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `description` tinytext,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;");

    $db->query(NULL, "
CREATE TABLE IF NOT EXISTS `clients_subscriptions` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `subscription_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_table1_2_idx` (`client_id`),
  KEY `fk_table1_1_idx` (`subscription_id`),
  CONSTRAINT `fk_table1_1` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_table1_2` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB;");

    $db->query(NULL, "
CREATE TABLE IF NOT EXISTS `instants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject` varchar(128) DEFAULT NULL,
  `text` longtext,
  `subscription_id` int(11) DEFAULT NULL,
  `is_draft` int(11) DEFAULT '0',
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_instants_1_idx` (`subscription_id`),
  CONSTRAINT `fk_instants_1` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB;");

    $db->query(NULL, "
ALTER TABLE `clients_courses`
  ADD CONSTRAINT `fk_clients_courses_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_clients_courses_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;");

    $db->query(NULL, "
ALTER TABLE `letters`
  ADD CONSTRAINT `fk_letters_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;");

    $db->query(NULL, "
ALTER TABLE `tasks`
  ADD CONSTRAINT `fk_mail_queue_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_mail_queue_2` FOREIGN KEY (`letter_id`) REFERENCES `letters` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;");
	}

	/**
	 * Run queries needed to remove this migration
	 *
	 * @param Kohana_Database $db Database connection
	 */
	public function down(Kohana_Database $db)
	{
    $db->query(NULL, 'DROP TABLE IF EXISTS `instants`;');
    $db->query(NULL, 'DROP TABLE IF EXISTS `clients_courses`;');
    $db->query(NULL, 'DROP TABLE IF EXISTS `clients_subscriptions`;');
    $db->query(NULL, 'DROP TABLE IF EXISTS `tasks`;');
    $db->query(NULL, 'DROP TABLE IF EXISTS `letters`;');
    $db->query(NULL, 'DROP TABLE IF EXISTS `pages`;');
    $db->query(NULL, 'DROP TABLE IF EXISTS `courses`;');
    $db->query(NULL, 'DROP TABLE IF EXISTS `subscriptions`;');
    $db->query(NULL, 'DROP TABLE IF EXISTS `clients`;');
  }

}
