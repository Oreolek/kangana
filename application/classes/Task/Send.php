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
  protected $_options = array(
    'limit' => 100,
  );

  /**
   * Send all prepared letters.
   *
   * @return null
   */
  protected function _execute()
  {
    $params = $this->get_options();
    $limit = $params['limit'];
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
        ->limit($limit)
        ->execute();
      $ids = [];
      foreach ($letters as $letter)
      {
        echo 'Sending a letter '.$letter['id'].' to '.$letter['email']."\n";
        Model_Letter::_send($letter['email'], $letter['text'], $letter['subject'], $letter['id'], $letter['token']);
        $ids[] = $letter['id'];
      }

      DB::update('tasks')
        ->set(array('status' => Model_Task::STATUS_SENT))
        ->where('letter_id', 'IN', $ids)
        ->execute();
      $db->commit();
    }
    catch(Database_Exception $e)
    {
      $db->rollback();
    }
  }
}
