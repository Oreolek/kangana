<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Electronic letter model.
 * Stores HTML to be placed in e-mail template.
 * @package Models
 * @author Oreolek
 **/
class Model_Letter extends ORM {
  protected $belongs_to = array(
    'subscription'
  );
  
  /**
   * @return array validation rules
   **/
  public function rules()
	{
		return array(
      'subject' => array(
				array('not_empty'),
				array('max_length', array(':value', 128)),
      ),
      'text' => array(
				array('not_empty'),
				array('min_length', array(':value', 20)),
      ),
      'order' => array(
        array('numeric')
      )
		);
	}

  /**
   * Array of field labels.
   * Used in forms.
   **/
  protected $_labels = array(
    'text' => 'Message text',
    'subject' => 'Message subject',
    'order' => 'Message order'
  );

  public function customize()
  {
    if(empty($this->order))
    {
      $this->order = Model_Subscription::count_letters($this->subscription_id) + 1;
    }
  }

  /**
   * Function to send a email to a specified address.
   * Not suitable for a large-scale use.
   * @param email $address email address
   **/
  public function send($address, $token = '')
  {
    return self::_send($address, $this->text, $this->subject, $token);
  }
  
  /**
   * @param $address string or array of strings - email addresses
   * @param $text message body
   * @param $subject message subject
   * @param $token user subscription token
   **/
  public static function _send($address, $text, $subject, $token = '')
  {
    $sender = Kohana::$config->load('email')->get('sender');
    $email = Email::factory($subject, $text)->to($address)->from($sender);
    return $email->send();
  }

}
