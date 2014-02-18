<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Subscription model.
 * Subscription is a list of emails. An administrator can mail a letter to everyone on this list.
 * @package Models
 * @author Oreolek
 **/
class Model_Subscription extends ORM {
  protected $_has_many = array(
    'clients' => array(
      'model' => 'Client',
      'through' => 'clients_subscriptions'
    ),
    'instants' => array(
      'model' => 'Instant'
    )
  );

  /**
   * @return array validation rules
   **/
  public function rules()
	{
		return array(
      'title' => array(
				array('not_empty'),
				array('min_length', array(':value', 4)),
				array('max_length', array(':value', 100)),
      ),
      'description' => array(
				array('not_empty'),
				array('min_length', array(':value', 20)),
      ),
      'welcome' => array(
				array('min_length', array(':value', 20)),
      ),
      'price' => array(
        array('numeric')
      )
		);
	}

  /**
   * Array of field labels.
   * Used in forms.
   **/
  protected $_labels = array(
    'title' => 'Title',
    'price' => 'Subscription price',
    'welcome' => 'Welcome message',
    'description' => 'Description (for the clients)'
  );

  /**
   * Return subscriber count
   **/
  public function count_clients()
  {
    return DB::select(array(DB::expr('COUNT(client_id)'), 'cnt'))->from('clients_courses')->where('course_id', '=', $this->id)->execute()->get('cnt');
  }
}
