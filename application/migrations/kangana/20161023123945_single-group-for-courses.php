<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * single group for courses
 */
class Migration_Kangana_20161023123945 extends Minion_Migration_Base {

	/**
	 * Run queries needed to apply this migration
	 *
	 * @param Kohana_Database $db Database connection
	 */
	public function up(Kohana_Database $db)
	{
		$db->query(NULL, 'DROP TABLE courses_groups');
		$db->query(NULL, 'ALTER TABLE courses ADD COLUMN group_id INT(11) NULL');
    $db->query(NULL, "ALTER TABLE `courses`
  ADD CONSTRAINT `fk_courses_groups` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
    ");
	}

	/**
	 * Run queries needed to remove this migration
	 *
	 * @param Kohana_Database $db Database connection
	 */
	public function down(Kohana_Database $db)
	{
		$db->query(NULL, 'ALTER TABLE courses DROP COLUMN group_id');
    $db->query(NULL, "
CREATE TABLE IF NOT EXISTS `courses_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_courses_groups_course_index` (`course_id`),
  KEY `fk_courses_groups_group_index` (`group_id`)
) ENGINE=InnoDB;");

    $db->query(NULL, "
ALTER TABLE `courses_groups`
  ADD CONSTRAINT `fk_courses_groups_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_courses_groups_group` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
    ");
	}

}
