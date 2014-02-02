<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Layout view controller
 **/
class View_Layout {
  public $_view = NULL;
  public $_layout = 'layout';
  public $title = '';
  public $scripts = array();
  public $base_scripts = array(
  );
  public $errors;
 
  /**
   * Inherited paging function
   **/
  public function get_paging() {}
  
  public function has_errors()
  {
    return !empty($this->errors);
  }

  public function site_title()
  {
    return Kohana::$config->load('common.title');
  }
  public function stylesheet()
  {
    return Less::compile(APPPATH.'assets/stylesheets/main');
  }

  public function get_content()
  {
    return Kostache::factory()->render($this->content);
  }

  public function scripts()
  {
    $scripts = array_merge ($this->base_scripts, $this->scripts);
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

  public function favicon()
  {
    return URL::site('favicon.ico');
  }

  public function navigation()
  {
    $result = array();
    $navigation = array(
    );
    if (!Auth::instance()->logged_in())
    {
      $navigation = array_merge($navigation, array('Вход' => 'user/signin'));
    }
    else
    {
      $navigation = array_merge($navigation, array(
        __('Subscriptions') => 'subscription/index',
        'Клиенты' => 'client/index',
        'Поиск клиентов' => 'client/search',
      ));
    }

    foreach ($navigation as $key => $value)
    {
      array_push($result, array(
        'url' => URL::site('/'.$value),
        'title' => $key
      ));
    }
    return $result;
  }

  public function get_errors()
  {
    $result = array();
    foreach ($this->errors as $key => $string)
    {
      array_push($result, $string);
    }
    return $result;
  }

  public function flashes()
  {
    $session = Session::instance();
    return array(
      'info' => $session->get_once('flash_info'),
      'success' => $session->get_once('flash_success'),
      'error' => $session->get_once('flash_error'),
      'warning' => $session->get_once('flash_warning'),
    );
  }

  public function search_form()
  {
    return array(
      'button_text' => __('Submit'),
      'input_text' => __('Search'),
      'action' => Route::url('default', array('controller' => 'Client', 'action' => 'search')) 
    );
  }
}
