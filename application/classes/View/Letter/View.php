<?php defined('SYSPATH') or die('No direct script access.');

/**
 * E-mail letter view controller
 **/
class View_Letter_View extends View {
  public $_view = NULL;
  public $_layout = 'email';
  public $token = '';
  public $scripts = array();
  
  public function stylesheet()
  {
    $url = Less::compile(APPPATH.'assets/stylesheets/main', 'screen', FALSE);
    return file_get_contents(DOCROOT.$url[0]);
  }

  public function get_content()
  {
    return Kostache::factory()->render($this->content);
  }
}
