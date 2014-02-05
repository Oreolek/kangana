<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Instant index view controller.
 * @package Views
 * @author Oreolek
 **/
class View_Instant_Index extends View_Index {
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
      'description' => $item->text,
      'view_link' => $item->subject,//HTML::anchor(Route::url('default', array('controller' => 'Letter', 'action' => 'view','id' => $item->id)), $item->subject, array('class' => 'link_view')),
      'edit_link' => HTML::anchor(Route::url('default', array('controller' => 'Instant', 'action' => 'edit','id' => $item->id)), __('Edit'), array('class' => 'link_edit')),
    );
    return $output;
  }

  public function link_new()
  {
    return HTML::anchor(Route::url('default', array('controller' => 'Instant', 'action' => 'create', 'id' => $this->subscription_id)), __('Add'), array('class' => 'link_new'));
  }
}
