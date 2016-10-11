<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Letter index view controller.
 * @package Views
 * @author Oreolek
 **/
class View_Letter_Index extends View_Index {
  protected $is_admin = TRUE; // admin only view
  public $show_date = FALSE;
  public $course_id;
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
      'view_link' => HTML::anchor(Route::url('default', array('controller' => 'Letter', 'action' => 'view','id' => $item->id)), $item->subject, array('class' => 'link_view')),
      'edit_link' => HTML::anchor(Route::url('default', array('controller' => 'Letter', 'action' => 'edit','id' => $item->id)), I18n::translate('Edit'), array('class' => 'link_edit')),
      'delete_link' =>  HTML::anchor(Route::url('default', array('controller' => 'Letter', 'action' => 'delete','id' => $item->id)), I18n::translate('Delete'), array('class' => 'link_delete')),
    );
    return $output;
  }

  public function iframe_code()
  {
    return array(
      'text' => I18n::translate('Subscription code'),
      'code' => '<iframe src="'.Route::url('default', array('controller' => 'Course', 'action' => 'subscribe', 'id' => $this->course_id), TRUE).'" width="100%" height="400"></iframe>',
    );
  }

  public function link_new()
  {
    return HTML::anchor(Route::url('default', array('controller' => 'Letter', 'action' => 'create', 'id' => $this->course_id)), I18n::translate('Add'), array('class' => 'link_new'));
  }
}
