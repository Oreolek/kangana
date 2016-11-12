<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Clear duplicate clients and force UNIQUE on email field.
 * Constraint is not removable because it's a bugfix.
 **/
class Migration_Kangana_20161112132556 extends Minion_Migration_Base {

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
      echo "Finding duplicate clients.".PHP_EOL;
      $emails = DB::select('email')
        ->from('clients')
        ->group_by('email')
        ->having(DB::expr("COUNT(email)"), ">", 1)
        ->execute($db)
        ->as_array(NULL, 'email');

      if ( ! empty($emails))
      {
        foreach ($emails as $email)
        {
          $clients = DB::select()
            ->from('clients')
            ->where('email', '=', $email)
            ->as_object()
            ->execute($db);
          $final = [];
          $final['email'] = $email;
          $final['city'] = NULL;
          $final['country'] = NULL;
          $final['name'] = NULL;
          $final['referrer'] = NULL;
          $final['sex'] = NULL;
          $final['token'] = NULL;
          $final['courses'] = [];
          $final['groups'] = [];
          foreach ($clients as $client)
          {
            $this->set_field($final, 'city', $client->city);
            $this->set_field($final, 'country', $client->country);
            $this->set_field($final, 'name', $client->name);
            $this->set_field($final, 'referrer', $client->referrer);
            $this->set_field($final, 'sex', $client->sex);
            $this->set_field($final, 'token', $client->token);
            $groups = DB::select('group_id')
              ->from('clients_groups')
              ->where('client_id', '=', $client->id)
              ->execute($db)
              ->as_array(NULL, 'group_id');
            if (isset($groups))
            {
              foreach ($groups as $group)
              {
                $final['groups'][] = $group;
              }
            }
            $courses = DB::select('course_id')
              ->from('clients_courses')
              ->where('client_id', '=', $client->id)
              ->execute($db)
              ->as_array(NULL, 'course_id');
            if (isset($courses))
            {
              foreach ($courses as $course)
              {
                $final['courses'][] = $course;
              }
            }
          }
          $final['courses'] = array_unique($final['courses']);
          $final['groups'] = array_unique($final['groups']);

          $ids = DB::select('id')
            ->from('clients')
            ->where('email', '=', $email)
            ->execute($db)
            ->as_array(NULL, 'id');
          $query = DB::query(
            DATABASE::DELETE,
            "DELETE FROM clients_courses WHERE client_id IN (".implode(',', $ids).")"
          )->execute($db);
          $query = DB::query(
            DATABASE::DELETE,
            "DELETE FROM clients_groups WHERE client_id IN (".implode(',', $ids).")"
          )->execute($db);
          $query = DB::query(
            DATABASE::DELETE,
            "DELETE FROM tasks WHERE client_id IN (".implode(',', $ids).")"
          )->execute($db);
          $query = DB::query(
            DATABASE::DELETE,
            "DELETE FROM clients WHERE id IN (".implode(',', $ids).")"
          )->execute($db);
          $client = ORM::factory('Client');
          $client->city = $final['city'];
          $client->country = $final['country'];
          $client->name = $final['name'];
          $client->sex = $final['sex'];
          $client->referrer = $final['referrer'];
          $client->token = $final['token'];
          $client->email = $final['email'];
          if (empty($client->name))
          {
            // name is empty, we don't need a client like that
            continue;
          }
          try
          {
            $client->save();
          }
          catch (ORM_Validation_Exception $e)
          {
            var_dump($e->errors());
            throw $e;
          }
          if (!empty($final['groups']))
          {
            foreach ($final['groups'] as $group_id)
            {
              $client->add('group', $group_id);
            }
          }
          if (!empty($final['courses']))
          {
            foreach ($final['courses'] as $course_id)
            {
              $client->add('course', $course_id);
            }
          }
        }
      }

      echo 'Altering tables.'.PHP_EOL;
      $db->query(NULL, "alter table `clients` add UNIQUE (`email`)");
      $db->commit();
      echo 'All done.'.PHP_EOL;
    }
    catch (Exception $e)
    {
      $db->rollback();
      echo $e->getMessage();
      return false;
    }
  }

  protected function set_field($arr, $attribute, $value)
  {
    if ($value) {
      $arr[$attribute] = $value;
    }
    return $arr;
  }
	/**
	 * Run queries needed to remove this migration
	 *
	 * @param Kohana_Database $db Database connection
	 */
	public function down(Kohana_Database $db)
  {
    echo "Constraint is not removable.";
    return true;
	}

}
