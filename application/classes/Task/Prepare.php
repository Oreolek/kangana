<?php defined('SYSPATH') OR die('No direct script access.');
 /**
  * This is an automated task to create mailing tasks.
  * It has no configurable options.
  * @category Helpers
  * @author Oreolek
  * @license AGPL
  **/
class Task_Prepare extends Minion_Task
{
  protected $_options = array();
  /**
   * @param int $course course ID
   **/
  protected function prepare_course($course)
  {
    if (is_null($course))
    {
      Log::instance()->add(Log::ERROR, 'Course ID is NULL when preparing');
      return;
    }
    $count = Model_Course::count_letters($course);
    if ($count == 0)
      return;
    $period = Model_Course::get_period($course);
    $clients = Model_Course::get_client_ids($course, $period);
    $letters = Model_Course::get_letter_ids($course);
    if (!is_array($clients))
    {
      $this->prepare_letters($clients, $letters, $period);
    }
    else
    {
      foreach ($clients as $client)
      {
        $this->prepare_letters($client, $letters, $period);
      }
    }
  }

  protected function prepare_letters($client_id, $letter_ids, $period)
  {
    if (Model_Task::check_period($client_id, $letter_ids, $period))
    {
      $letter = Model_Task::next_unsent($client_id, $letter_ids);
      if ($letter !== FALSE)
      {
        Model_Task::prepare($client_id, $letter);
      }
    }
  }

  /**
   * Prepare letters to be sent out.
   * If a client received less letters from course than there is in course,
   * a task is formed.
   *
   * @return null
   */
  protected function _execute(array $params)
  {
    $db = Database::instance();
    $db->begin();
    try
    {
      // get courses which have subscribers
      $courses = Model_Course::get_ids();
      if (!is_array($courses))
      {
        $this->prepare_course($courses);
      }
      else
      {
        foreach ($courses as $course)
        {
          $this->prepare_course($course);
        } 
      }
      $db->commit();
    }
    catch(Database_Exception $e)
    {
      $db->rollback();
    }
  }
}
