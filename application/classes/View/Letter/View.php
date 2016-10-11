<?php defined('SYSPATH') or die('No direct script access.');

/**
 * E-mail letter view controller
 **/
class View_Letter_View extends View {
  public $_view = NULL;
  public $_layout = 'email';

  /**
   * Unsubscription token
   **/
  public $token = '';
  /**
   * Email receiver address
   **/
  public $address = '';
  /**
   * Email subject
   **/
  public $subject = '';
  /**
   * Letter ID
   **/
  public $id = NULL;
  public $scripts = array();

  public function view_link()
  {
    return HTML::anchor(Route::url('default', array('controller' => 'Letter', 'action' => 'view', 'id' => $this->id), TRUE), I18n::translate('Problems viewing this email? Click here.'));
  }

  public function get_content()
  {
    return Kostache::factory()->render($this->content);
  }

  public function unsubscribe()
  {
    return self::unsubscribe_link($this->id, $this->address, $this->token);
  }

  public static function unsubscribe_link($id, $address, $token)
  {
    return HTML::anchor(
      Route::url('default', array('controller' => 'Letter', 'action' => 'unsubscribe', 'id' => $id), TRUE).'?'.http_build_query(array(
        'email' => $address,
        'token' => $token
      )),
      I18n::translate('Tired of receiving these emails? Click this link to unsubscribe.')
    );
  }
}
