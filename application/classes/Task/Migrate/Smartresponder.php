<?php defined('SYSPATH') OR die('No direct script access.');

use Ddeboer\Transcoder\Transcoder;

 /**
  * This is a task to migrate client database from SmartResponder csv dump
  * It can accept -csv option.
  * csv should have format: "Email","Name"
  * @category Helpers
  * @author Oreolek
  * @license AGPL
  **/
class Task_Migrate_Smartresponder extends Minion_Task
{
  protected $_options = array(
    'csv' => NULL,
    'group' => NULL,
  );

  public function build_validation(Validation $validation)
  {
    return parent::build_validation($validation)
      ->rule('csv', 'not_empty') // Require this param
      ->rule('group', 'not_empty'); // Require this param
  }

  /**
   * This is a migration task
   *
   * @return null
   */
  protected function _execute()
  {
    $params = $this->get_options();
    $path = $params['csv'];

    if (!file_exists($path))
    {
      echo "ERROR: File not found.";
      return;
    }

    $db = Database::instance();
    $db->begin();
    $transcoder = Transcoder::create();

    $group = ORM::factory('Group')->where('name', '=', $params['group'])->find();
    if ( ! $group->loaded()) {
      echo "No group with name " . $params['group'] . " found.\n";
      return;
    }

    try
    {
      if (($handle = fopen($path, "r")) !== FALSE) {
        echo "File opened.\n";
        $query = DB::query(
          Database::INSERT,
          'INSERT INTO clients (email, name, group_id)
          VALUES (:email, :name, :group_id)'
        )
          ->bind(':email', $email)
          ->bind(':name', $name)
          ->bind(':group_id', $group->id);

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
          $email = $data[0];
          $name = $transcoder->transcode($data[1] . ' ' . $data[2], 'cp1251');
          $sex = $data[5];
          $referrer = $data[13];
          $city = $transcoder->transcode($data[7], 'cp1251');
          $country = $data[6];
          echo "Importing client " . $name . ".\n";
          $query->execute();
        }
        $db->commit();
      } else {
        echo "Could not open the file.";
      }
    }
    catch(Database_Exception $e)
    {
      $db->rollback();
    }
  }
}
