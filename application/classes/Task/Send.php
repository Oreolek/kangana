<?php defined('SYSPATH') OR die('No direct script access.');
 /**
  * This is an automated task to send all pending letters.
  * It has no configurable options.
  * @category Helpers
  * @author Oreolek
  * @license AGPL
  **/
class Task_Send extends Minion_Task
{
  protected $_options = array();

  /**
   * Send all prepared letters.
   *
   * @return null
   * TODO: group by letters and send them using Bcc instead of one at a time
   */
  protected function _execute()
  {
    $params = $this->get_options();
    $db = Database::instance();
    $db->begin();
    try
    {
      $letters = DB::select(
          array('letters.id', 'id'),
          array('letters.text', 'text'),
          array('letters.subject', 'subject'),
          array('clients.email', 'email'),
          array('clients.token', 'token')
        )
        ->from('tasks')
        ->join('letters', 'LEFT')
        ->on('tasks.letter_id', '=', 'letters.id')
        ->join('clients', 'LEFT')
        ->on('tasks.client_id', '=', 'clients.id')
        ->where('tasks.status', '=', Model_Task::STATUS_PENDING)
        ->execute();
      foreach ($letters as $letter)
      {
        Model_Letter::_send($letter['email'], $letter['text'], $letter['subject'], $letter['id'], $letter['token']);
      }

      DB::update('tasks')
        ->set(array('status' => Model_Task::STATUS_SENT))
        ->where('status', '=', Model_Task::STATUS_PENDING)
        ->execute();
      $db->commit();
    }
    catch(Database_Exception $e)
    {
      $db->rollback();
    }
  }
}
