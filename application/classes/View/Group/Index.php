<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Group index view controller.
 * @package Views
 * @author Oreolek
 **/
class View_Group_Index extends View_Index {
  protected $is_admin = TRUE; // admin only view
  public $show_date = FALSE;
  public $content;
  public function get_header()
  {
    return array(
      I18n::translate('Group name'),
      I18n::translate('Subscribers'),
      I18n::translate('Edit'),
      I18n::translate('Delete'),
    );
  }

  /**
   * An internal function to prepare item data.
   **/
  protected function show_item($item)
  {
    if ( ! $item instanceof ORM)
    {
      return FALSE;
    }

    $output = array(
      'view_link' => HTML::anchor(Route::url('default', array('controller' => Request::current()->controller(), 'action' => 'view','id' => $item->id)), $item->name, array('class' => 'link_view')),
      'edit_link' => HTML::anchor(Route::url('default', array('controller' => Request::current()->controller(), 'action' => 'edit','id' => $item->id)), I18n::translate('Edit'), array('class' => 'link_edit')),
      'delete_link' => HTML::anchor(Route::url('default', array('controller' => Request::current()->controller(), 'action' => 'delete','id' => $item->id)), I18n::translate('Delete'), array('class' => 'link_delete')),
      'client_count' => $item->count_clients(),
    );
    return $output;
  }
}
