<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Client index view controller.
 * @package Views
 * @author Oreolek
 **/
class View_Client_Index extends View_Index {
  protected $is_admin = TRUE; // admin only view
  public $show_date = FALSE;
  public $subscription_id;
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
      'description' => $item->email,
      'view_link' => HTML::anchor(Route::url('default', array('controller' => 'Client', 'action' => 'view','id' => $item->id)), $item->name, array('class' => 'link_view')),
      'edit_link' => HTML::anchor(Route::url('default', array('controller' => 'Client', 'action' => 'edit','id' => $item->id)), __('Edit'), array('class' => 'link_edit')),
      'delete_link' =>  HTML::anchor(Route::url('default', array('controller' => 'Client', 'action' => 'delete','id' => $item->id)), __('Delete'), array('class' => 'link_delete')),
    );
    return $output;
  }
}
