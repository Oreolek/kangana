<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * model combining
 */
class Migration_Kangana_20161016123906 extends Minion_Migration_Base {

	/**
	 * Run queries needed to apply this migration
	 *
	 * @param Kohana_Database $db Database connection
	 */
	public function up(Kohana_Database $db)
  {
    echo 'Opening the transaction.'.PHP_EOL;
    $db->begin();
    try
    {
      echo 'Altering tables.'.PHP_EOL;
      $db->query(NULL, 'ALTER TABLE `letters` ADD COLUMN `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP');
      $db->query(NULL, "alter table `letters` add column `sent` int(1) null default '0'");
      $db->query(NULL, "alter table `letters` add column `is_draft` int(1) not null default '0'");
      $db->query(NULL, "alter table `courses` add column `type` int(2) not null default '0'");
      echo "Copying Subscription model to Courses." . PHP_EOL;
      $subscriptions = DB::select()->from('subscriptions')->as_object()->execute();
      foreach ($subscriptions as $subscription)
      {
        $course = ORM::factory('Course');
        $course->type = Course::TYPE_IRREGULAR;
        $course->title = $subscription->title;
        $course->description = $subscription->description;
        $course->price = $subscription->price;
        $instants = $subscription->instants->find_all();
        foreach ($instants as $instant)
        {
          echo 'Migrating a letter:' . $instant->subject . PHP_EOL;
          $query = DB::query(
            Database::INSERT,
            'INSERT INTO letters (course_id, subject, text, timestamp, sent)
            VALUES(:course_id, :subject, :text, :timestamp, :sent)'
          );

          $query->parameters(array(
            ':course_id' => $course->id,
            ':subject' => $instant->subject,
            ':text' => $instant->text,
            ':timestamp' => $instant->timestamp,
            ':sent' => $instant->sent,
          ));
        }
      }
      $db->commit();
    }
    catch (Database_Exception $e)
    {
      $db->rollback();
    }
  }

  /**
   * Run queries needed to remove this migration
   *
   * @param Kohana_Database $db Database connection
   */
  public function down(Kohana_Database $db)
  {
    echo 'An automatic rollback for this migration back is not implemented.';
    return false;
  }

}
