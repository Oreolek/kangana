<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Client model.
 * Client is just a subscriber at the moment.
 * @package Models
 * @author Oreolek
 **/
class Model_Client extends ORM {
  protected $has_many = array(
    'subscription'
  );
  
  /**
   * @return array validation rules
   **/
  public function rules()
	{
		return array(
      'email' => array(
				array('not_empty'),
				array('email'),
				array('min_length', array(':value', 5)),
      ),
      'name' => array(
				array('not_empty'),
				array('min_length', array(':value', 5)),
      ),
      'token' => array(
				array('not_empty'),
        array('numeric')
      )
		);
	}

  /**
   * Array of field labels.
   * Used in forms.
   **/
  protected $_labels = array(
    'email' => 'Email',
    'name' => 'Name',
    'token' => 'Subscription token'
  );


}
