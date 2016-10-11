<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Simple course and first letter creation view controller
 **/
class View_Course_Simple extends View_Edit {
  public $model_course;
  public $model_letter;
  public function controls_course()
  {
    return array(
      'heading' => I18n::translate('New course'),
      'controls' => array(
        Form::orm_input($this->model_course, 'title'),
        Form::orm_textarea($this->model_course, 'description')
      )
    );
  }

  public function controls_letter()
  {
    return array(
      'heading' => I18n::translate('First letter'),
      'controls' => array(
        Form::orm_input($this->model_letter, 'subject'),
        Form::orm_textarea($this->model_letter, 'text')
      )
    );
  }
}
