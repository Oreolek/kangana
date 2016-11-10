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
   * Items to show — filtered list
   **/
  public $items = NULL;
  /**
   * How many items there are
   */
  public $item_count = 0;

  /**
   * Pagination controls
   **/
  public function get_paging()
  {
    $current_page = $this->get_current_page();
    $item_count = $this->item_count;
    $page_size = Kohana::$config->load('common.page_size');
    $page_count = ceil($item_count / $page_size);
    if ($page_count === 1.0)
      return '';
    $output = '';
    $i = $current_page - 10;
    if ($i < 1)
    {
      $i = 1;
    }
    $max = $page_count;
    if ($max > $current_page + 10)
    {
      $max = $current_page + 10;
    }
    if ($i > 1)
    {
      $output .= '<span>…</span>&nbsp;';
    }
    while ($i <= $max)
    {
      $output .= '<a href="'.Route::url('default', array('controller' => Request::current()->controller(), 'action' => Request::current()->action(), 'page' => $i)).'"';
      if ($i == $current_page)
      {
        $output .= ' class="active"';
      }
      $output .= '>'.$i.'</a>';
      $i++;
    }
    if ($max < $page_count)
    {
      $output .= '&nbsp;<span>…</span>';
    }
    return $output;
  }

  /**
   * Pagination: calculate current page
   **/
  protected function get_current_page()
  {
    $current_page = Request::current()->param('page');
    if ( ! $current_page)
      return 1;
    return $current_page;
  }

  public function has_errors()
  {
    return ! empty($this->errors);
  }

  public function site_title()
  {
    return Kohana::$config->load('common.title');
  }

  public function stylesheet()
  {
    return Html::style("/style.css")."\n".Html::style("https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css");
  }

  public function get_content()
  {
    return Kostache::factory()->render($this->content);
  }

  public function scripts()
  {
    $scripts = array_merge ($this->base_scripts, $this->scripts);
    $temp = "";
    foreach($scripts as $script)
    {
      if (strstr($script, '://') === FALSE) // no protocol given, script is local
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
    };
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
    if ( ! Auth::instance()->logged_in())
    {
      $navigation = array_merge($navigation, array('Вход' => 'user/signin'));
    }
    else
    {
      $navigation = array_merge($navigation, array(
        I18n::translate('New course') => 'course/simple',
        I18n::translate('Courses') => 'course/index',
        I18n::translate('Subscriptions') => 'course/sindex',
        I18n::translate('Clients') => 'client/index',
        I18n::translate('Groups') => 'group/index',
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
    if (Auth::instance()->logged_in())
    {
      return array(
        'button_text' => I18n::translate('Submit'),
        'input_text' => I18n::translate('Search'),
        'action' => Route::url('default', array('controller' => 'Client', 'action' => 'search'))
      );
    }
  }
}
