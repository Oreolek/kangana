<?php defined('SYSPATH') or die('No direct script access.');

/**
 * E-mail letter view controller
 **/
class View_Letter_View extends View {
  public $_view = NULL;
  public $_layout = 'email';
  public $token = '';
  public $subject = '';
  public $id = NULL;
  public $scripts = array();
  
  public function stylesheet()
  {
    $url = Less::compile(APPPATH.'assets/stylesheets/email', 'all', FALSE);
    return file_get_contents(DOCROOT.$url[0]);
  }

  public function view_link()
  {
    return HTML::anchor(Route::url('default', array('controller' => 'Letter', 'action' => 'view', 'id' => $this->id), TRUE), __('Problems viewing this email? Click here.'));
  }

  public function get_content()
  {
    return Kostache::factory()->render($this->content);
  }
}
