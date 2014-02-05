<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Course index view controller.
 * @package Views
 * @author Oreolek
 **/
class View_Course_Index extends View_Index {
  protected $is_admin = TRUE; // admin only view
  public $show_date = FALSE;
  public function get_header()
  {
    return array(
      __('Title'),
      __('Description'),
      __('Subscribers'),
      __('Edit'),
      __('Delete')
    );
  }
  /**
   * An internal function to prepare item data.
   **/
  protected function show_item($item)
  {
    if (!$item instanceof ORM)
    {
      return FALSE;
    }

    $output = array(
      'description' => $item->description,
      'view_link' => HTML::anchor(Route::url('default', array('controller' => Request::current()->controller(), 'action' => 'view','id' => $item->id)),$item->title, array('class' => 'link_view')),
      'edit_link' => HTML::anchor(Route::url('default', array('controller' => Request::current()->controller(), 'action' => 'edit','id' => $item->id)), __('Edit'), array('class' => 'link_edit')),
      'delete_link' => HTML::anchor(Route::url('default', array('controller' => Request::current()->controller(), 'action' => 'delete','id' => $item->id)), __('Delete'), array('class' => 'link_delete')),
      'client_count' => $item->count_clients(),
    );
    return $output;
  }
}
