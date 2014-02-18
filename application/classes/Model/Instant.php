<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Electronic instant letter model. This is a strict one-time letter.
 * Stores HTML to be placed in e-mail template.
 * @package Models
 * @author Oreolek
 **/
class Model_Instant extends ORM {
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
      'is_draft' => array(
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
    'is_draft' => 'Is a draft?'
  );

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
   * @param $token user course token
   **/
  public static function _send($address, $text, $subject, $token = '')
  {
    Log::instance()->add(Log::NOTICE, __('Sending letter with subject "%subject" to address %address', array('%subject' => $subject, '%address' => $address)));
    $sender = Kohana::$config->load('email')->get('sender');
    $email = Email::factory($subject, $text)->from($sender[0], $sender[1]);
    if (is_array($address))
    {
      $email->bcc($address);
    }
    else
    {
      $email->to($address);
    }
    return $email->send();
  }
}
