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
    if ( ! $item instanceof ORM)
    {
      return FALSE;
    }

    $output = array(
      'description' => $item->text,
      'view_link' => $item->subject,
      'is_sent' => I18n::translate('Sent'),
      'edit_link' => FALSE,
      'send_button' => HTML::anchor(Route::url('default', array('controller' => 'Instant', 'action' => 'send','id' => $item->id)), Form::btn('send', 'Send to subscribers')),
    );

    if ($item->sent == 0)
    {
      $output['edit_link'] = HTML::anchor(Route::url('default', array('controller' => 'Instant', 'action' => 'edit','id' => $item->id)), I18n::translate('Edit'), array('class' => 'link_edit'));
    }
    return $output;
  }

  public function link_new()
  {
    return HTML::anchor(Route::url('default', array('controller' => 'Instant', 'action' => 'create', 'id' => $this->subscription_id)), I18n::translate('Add'), array('class' => 'link_new'));
  }
}
