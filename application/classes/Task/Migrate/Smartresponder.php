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
    'group_id' => NULL,
  );

  public function build_validation(Validation $validation)
  {
    return parent::build_validation($validation)
      ->rule('csv', 'not_empty') // Require this param
      ->rule('group_id', 'not_empty'); // Require this param
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

    if ( ! file_exists($path))
    {
      echo "ERROR: File not found.";
      return;
    }

    $db = Database::instance();
    $db->begin();
    $transcoder = Transcoder::create();

    $group = ORM::factory('Group')->where('id', '=', $params['group_id'])->find();
    if ( ! $group->loaded()) {
      echo "No group with id ".$params['group_id'  " found.\n";
      return;
    }

    try
    {
      if (($handle = fopen($path, "r")) !== FALSE) {
        echo "File opened.\n";
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
          $client = ORM::factory('Client')->where('email', '=', $data[0]);
          if ( ! $client->loaded()) {
            $client = ORM::factory('Client');
            $client->email = $data[0];
          }
          $name = trim($transcoder->transcode($data[1].' '.$data[2], 'cp1251'));
          $client->name = $name;
          $client->sex = $data[5];
          if ( ! empty($data[13]))
          {
            $client->referrer = $data[13];
          }
          if ( ! empty($data[7]))
          {
            $client->city = $transcoder->transcode($data[7], 'cp1251');
          }
          if ( ! empty($data[6]))
          {
            $client->country = $data[6];
          }
          echo "Importing client ".$name.".\n";
          try
          {
            $client->customize();
            $client->save();
          } catch (ORM_Validation_Exception $e) {
            continue;
            var_dump($client->object());
          }
          $client->add('group', $group->id);
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
