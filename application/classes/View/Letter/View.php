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

  public function scripts()
  {
    $scripts = $this->scripts;
    $temp = "";
    foreach($scripts as $script):
      if (strstr($script, '://') === FALSE) //no protocol given, script is local
      {
        if ($script === 'jquery') // CDN shortcut
        {
          $temp .= HTML::script('https://yandex.st/jquery/2.0.3/jquery.min.js')."\n";
        }
        else
        {
          $temp .= HTML::script('application/assets/javascript/'.$script)."\n";
        }
      }
      else
      {
        $temp .= HTML::script($script)."\n";
      }
    endforeach;
    return $temp;
  }
}
