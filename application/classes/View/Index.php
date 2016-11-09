<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Index view controller.
 **/
class View_Index extends View_Layout {
  public $show_date = TRUE;
  /**
   * Show a link to add new entry
   **/
  public $show_create = TRUE;
  /**
   * Show edit and delete links for admin
   **/
  public $show_edit = TRUE;

  /**
   * Index description
   **/
  public $content = '';
  /**
   * Table header
   */
  public $header = NULL;

  protected $is_admin;

  /**
   * Table header
   */
  public function get_header()
  {
    return $this->header;
  }

  public function get_items()
  {
    $result = array();
    if (is_null($this->items) OR $this->items === FALSE OR count($this->items) === 0)
    {
      return I18n::translate('No objects found to show');
    };
    $items = $this->filter_items();
    foreach ($this->items as $item)
    {
      array_push($result, $this->show_item($item));
    }
    return $result;
  }

  /**
   * An internal function to prepare item data.
   * btw, it can be redefined.
   **/
  protected function show_item($item)
  {
    if ( ! $item instanceof ORM)
    {
      return FALSE;
    }

    if (is_null($this->is_admin))
    {
      $this->is_admin = Auth::instance()->logged_in('admin');
    }
    $output = array(
      'view_link' => HTML::anchor(Route::url('default', array('controller' => Request::current()->controller(), 'action' => 'view','id' => $item->id)), $item->name, array('class' => 'link_view')),
    );
    if ($this->is_admin and $this->show_edit)
    {
      $output['edit_link'] = HTML::anchor(Route::url('default', array('controller' => Request::current()->controller(), 'action' => 'edit','id' => $item->id)), I18n::translate('Edit'), array('class' => 'link_edit'));
      $output['delete_link'] = HTML::anchor(Route::url('default', array('controller' => Request::current()->controller(), 'action' => 'delete','id' => $item->id)), I18n::translate('Delete'), array('class' => 'link_delete'));
    }
    return $output;
  }

  protected function view_link_colwidth()
  {
    $columns = 3;
    if ( ! $this->show_date)
    {
      $columns++;
    }
    if ( ! Auth::instance()->logged_in('admin'))
    {
      $columns = $columns + 2;
    }
    return $columns;
  }

  public function link_new()
  {
    if (Auth::instance()->logged_in())
    {
      return '<a href="'.Route::url('default', array('controller' => Request::current()->controller(), 'action' => 'create')).'" class="link_new">'.I18n::translate('Add').'</a>';
    }
    return '';
  }
}
