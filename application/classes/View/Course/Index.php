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
      I18n::translate('Title'),
      I18n::translate('Description'),
      I18n::translate('Subscribers'),
      I18n::translate('Edit'),
      I18n::translate('Delete')
    );
  }

  public function content()
  {
    return '<p>'
      .I18n::translate('A course is a pre-defined regular mailing list.')
      .' '
      .I18n::translate("You <i>add</i> a message, forming a series of messages.")
      .' '
      .I18n::translate("Each new subscriber gets the first message in this series.")
      .' '
      .I18n::translate("You can customize the delay (1 day by default) between the messages.")
      .'</p>';
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
      'description' => $item->description,
      'view_link' => HTML::anchor(Route::url('default', array('controller' => Request::current()->controller(), 'action' => 'view','id' => $item->id)),$item->title, array('class' => 'link_view')),
      'edit_link' => HTML::anchor(Route::url('default', array('controller' => Request::current()->controller(), 'action' => 'edit','id' => $item->id)), I18n::translate('Edit'), array('class' => 'link_edit')),
      'delete_link' => HTML::anchor(Route::url('default', array('controller' => Request::current()->controller(), 'action' => 'delete','id' => $item->id)), I18n::translate('Delete'), array('class' => 'link_delete')),
      'client_count' => $item->count_clients(),
    );
    return $output;
  }
}
