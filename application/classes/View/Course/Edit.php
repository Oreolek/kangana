<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Course details editing view controller
 **/
class View_Course_Edit extends View_Edit {
  public function group_select()
  {
    return Form::select('group_id', ORM::factory('Group')->find_all()->as_array('id', 'name'), NULL, [
      'label' => I18n::translate('Group')
    ]);
  }
}
