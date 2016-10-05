<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Client model.
 * Client is just a subscriber at the moment.
 * @package Models
 * @author Oreolek
 **/
class Model_Client extends ORM {
  protected $_has_many = array(
    'course' => array(
      'model' => 'Course',
      'through' => 'clients_courses'
    ),
    'subscription' => array(
      'model' => 'Subscription',
      'through' => 'clients_subscriptions'
    ),
    'group' => array(
      'model' => 'Group',
      'through' => 'clients_groups'
    ),
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
    'email' => 'Your e-mail',
    'name' => 'What is your name?',
    'token' => 'Subscription token'
  );

  public function customize()
  {
    $this->token = base64_encode(openssl_random_pseudo_bytes(32));
  }

  /**
   * Search by name or email
   **/
  public function search($query)
  {
    $query = '%'.trim($query, '%').'%';
    return $this
      ->where(DB::expr('LOWER(name)'), 'LIKE', strtolower($query))
      ->or_where(DB::expr('LOWER(email)'), 'LIKE', strtolower($query));
  }

}
