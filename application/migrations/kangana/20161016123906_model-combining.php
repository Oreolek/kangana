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
        $query = DB::query(
          Database::INSERT,
          'INSERT INTO courses (type, title, description, price)
          VALUES(:type, :title, :description, :price)'
        );
        $query->parameters(array(
          ':type' => Model_Course::TYPE_IRREGULAR,
          ':title' => $subscription->title,
          ':description' => $subscription->description,
          ':price' => $subscription->price
        ));
        $query->execute();
        echo 'Migrating subscription "' . $subscription->title . '"' . PHP_EOL;

        $course_id = DB::select('id')
          ->from('courses')
          ->where('title', '=', $subscription->title)
          ->and_where('description', '=', $subscription->description)
          ->and_where('type', '=', Model_Course::TYPE_IRREGULAR)
          ->execute()
          ->get('id');

        echo 'Migrating links between subscriptions and clients.';
        $clients = DB::select('client_id')
          ->from('clients_subscriptions')
          ->where('subscription_id', '=', $subscription->id)
          ->execute()
          ->as_array(NULL, 'client_id');

        foreach ($clients as $client)
        {
          $db->query(NULL, 'INSERT INTO clients_courses (client_id, course_id)
            VALUES (' . $client . ', ' . $course_id . ')');
        }

        echo 'Migrating links between subscriptions and groups.';
        $groups = DB::select('group_id')
          ->from('subscriptions_groups')
          ->where('subscription_id', '=', $subscription->id)
          ->execute()
          ->as_array(NULL, 'group_id');

        foreach ($groups as $group)
        {
          $db->query(NULL, 'INSERT INTO courses_groups (group_id, course_id)
            VALUES (' . $group . ', ' . $course_id . ')');
        }

        echo 'Migrating Instants.';

        $instants = DB::select()
          ->from('instants')
          ->where('subscription_id', '=', $subscription->id)
          ->as_object()
          ->execute();
        foreach ($instants as $instant)
        {
          echo 'Migrating letter "' . $instant->subject . '"' . PHP_EOL;
          $query = DB::query(
            Database::INSERT,
            'INSERT INTO letters (course_id, subject, text, timestamp, sent)
            VALUES(:course_id, :subject, :text, :timestamp, :sent)'
          );

          $query->parameters(array(
            ':course_id' => $course_id,
            ':subject' => $instant->subject,
            ':text' => $instant->text,
            ':timestamp' => $instant->timestamp,
            ':sent' => $instant->sent,
          ));
          $query->execute();
        }
      }
      $db->query(NULL, "DROP TABLE instants");
      $db->query(NULL, "DROP TABLE subscriptions");
      $db->query(NULL, "DROP TABLE subscriptions_groups");
      $db->query(NULL, "DROP TABLE clients_subscriptions");
      $db->commit();
      echo 'All done.' . PHP_EOL;
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
    echo 'Opening the transaction.'.PHP_EOL;
    $db->begin();
    try
    {
      echo 'Altering tables.'.PHP_EOL;
      $db->query(NULL, 'ALTER TABLE `letters` DROP COLUMN `timestamp`');
      $db->query(NULL, "alter table `letters` DROP column `sent`");
      $db->query(NULL, "alter table `letters` DROP column `is_draft`");
      $db->query(NULL, "alter table `courses` DROP column `type`");
      $db->commit();
      echo 'All done.' . PHP_EOL;
    }
    catch (Database_Exception $e)
    {
      $db->rollback();
    }
  }
}
