<?php defined('SYSPATH') OR die('No direct script access.');
 /**
  * This is a task to change admin's password.
  * It can accept -password option.
  * @category Helpers
  * @author Oreolek
  * @license AGPL
  **/
class Task_Password extends Minion_Task
{
  protected $_options = array(
    'user' => 'admin',
    'password' => NULL,
  );
  
  public function build_validation(Validation $validation)
  {
    return parent::build_validation($validation)
      ->rule('password', 'not_empty'); // Require this param
  }

  /**
   * This is an admin password task
   *
   * @return null
   */
  protected function _execute(array $params)
  {
    $writer = new Config_File_Writer;
    Kohana::$config->attach($writer);
    $config = Kohana::$config->load('auth');
    $hash = hash_hmac($config->get('hash_method'), $params['password'], $config->get('hash_key'));
    $config->set('users', array($params['user'] => $hash));
    Kohana::$config->detach($writer);
    echo __('The password was successfully changed.');
  }
}
