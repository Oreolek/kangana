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
  public $group;
  public function get_header()
  {
    return array(
      I18n::translate('Name'),
      I18n::translate('Email'),
      I18n::translate('Edit'),
      I18n::translate('Delete')
    );
  }

  /**
   * Group search field
   */
  public function groups()
  {
    return Form::open()
      .Form::select('group_id', ORM::factory('Group')->find_all()->as_array('id', 'name'))
      .Form::submit('s', I18n::translate('Submit'))
      .Form::close();
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
      'description' => $item->email,
      'view_link' => HTML::anchor(Route::url('default', array('controller' => 'Client', 'action' => 'view','id' => $item->id)), $item->name, array('class' => 'link_view')),
      'edit_link' => HTML::anchor(Route::url('default', array('controller' => 'Client', 'action' => 'edit','id' => $item->id)), I18n::translate('Edit'), array('class' => 'link_edit')),
      'delete_link' =>  HTML::anchor(Route::url('default', array('controller' => 'Client', 'action' => 'delete','id' => $item->id)), I18n::translate('Delete'), array('class' => 'link_delete')),
    );
    return $output;
  }
}
