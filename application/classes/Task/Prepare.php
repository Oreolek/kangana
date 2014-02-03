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
   * @param int $subscription subscription ID
   **/
  protected function prepare_subscription($subscription)
  {
    $count = Model_Subscription::count_letters($subscription);
    if ($count == 0)
      return;
    $period = Model_Subscription::get_period($subscription);
    $clients = Model_Subscription::get_client_ids($subscription, $period);
    $letters = Model_Subscription::get_letter_ids($subscription);
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
   * If a client received less letters from subscription than there is in subscription,
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
      $subscriptions = Model_Subscription::get_ids();
      echo __('Total subscription count').': '.count($subscriptions)."\n";
      if (!is_array($subscriptions))
      {
        $this->prepare_subscription($subscriptions);
      }
      else
      {
        foreach ($subscriptions as $subscription)
        {
          $this->prepare_subscription($subscription);
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
