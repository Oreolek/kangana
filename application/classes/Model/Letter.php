<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Electronic letter model.
 * Stores HTML to be placed in e-mail template.
 * @package Models
 * @author Oreolek
 **/
class Model_Letter extends ORM {
  protected $belongs_to = array(
    'course'
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
    if ($this->order == 0)
    {
      $this->order = Model_Course::count_letters($this->course_id) + 1;
    }
  }

  /**
   * Function to send a email to a specified address.
   * Not suitable for a large-scale use.
   * @param ORM $client Recipient
   **/
  public function send($client)
  {
    return Model_Task::prepare($client->id, $this);
  }

  /**
   * @param $address string or array of strings - email addresses
   * @param $text message body
   * @param $subject message subject
   * @param $id message ID
   * @param $token user course token
   **/
  public static function _send($address, $text, $subject, $id, $token = '')
  {
    Log::instance()->add(Log::NOTICE, I18n::translate('Sending letter with subject "%subject" to address %address', array('%subject' => $subject, '%address' => $address)));
    $sender = Kohana::$config->load('email')->get('sender');
    $template = new View_Letter_View;
    $template->content = $text;
    $template->id = $id;
    $template->subject = $subject;
    $template->token = $token;
    $template->address = $address;
    $renderer = Kostache_Layout::factory($template->_layout);
    $html = $renderer->render($template, $template->_view);
    $email = Email::factory($subject, $html, 'text/html')->from($sender[0], $sender[1]);
    if (is_array($address))
    {
      $email->bcc($address);
    }
    else
    {
      $email->to($address);
    }
    if ( ! $email->send())
    {
      echo "Error sending message $id to $address\n";
    }
  }

}
