<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Client model.
 * Client is just a subscriber at the moment.
 * @package Models
 * @author Oreolek
 **/
class Model_Client extends ORM {
  protected $_has_many = array(
    'subscription' => array(
      'model' => 'Subscription',
      'through' => 'clients_subscriptions'
    )
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

  public function customize()
  {
    $this->token = base64_encode(openssl_random_pseudo_bytes(32));
  }

}
