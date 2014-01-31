<?php defined('SYSPATH') OR die('No direct script access.');
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
  );
  
  public function build_validation(Validation $validation)
  {
    return parent::build_validation($validation)
      ->rule('csv', 'not_empty'); // Require this param
  }

  /**
   * This is a migration task
   *
   * @return null
   */
  protected function _execute(array $params)
  {
    $path = $params['csv'];
    if (file_exists($path))
    {
      $db = Database::instance();
      $db->begin();
      try
      {
        $csv = file($path);
        foreach ($csv as $line_num => $line)
        {
          $arr = explode(',',$line);
          DB::insert('clients', array('email', 'name'))->values(array(trim($arr[0], '"'), trim($arr[1], '"')))->execute();
        }
        /*
        $csv = fopen($path, 'r');
        while (!feof($csv)) {
          $string = fgets($csv, 1024);
          $arr = explode(',',$string);
          echo $string;
          DB::insert('clients', array('name', 'email'))->values(array(trim($arr[0], '"'), trim($arr[1], '"')))->execute();
        }
        fclose($csv);
        */
        $db->commit();
      }
      catch(Database_Exception $e)
      {
        $db->rollback();
      }
    }
  }
}
