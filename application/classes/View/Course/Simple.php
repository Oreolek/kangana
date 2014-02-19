<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Simple course and first letter creation view controller
 **/
class View_Course_Simple extends View_Edit {
  public function controls_course()
  {
    return array(
      'heading' => __('New course'),
      'controls' => array(
        Form::textinput('title', '', array('label' => 'Title')),
        Form::textarea('description', '', array('label' => 'Description'))
      )
    );
  }

  public function controls_letter()
  {
    return array(
      'heading' => __('First letter'),
      'controls' => array(
        Form::textinput('letter_subject', '', array('label' => 'Subject')),
        Form::textarea('letter_body', '', array('label' => 'Message body'))
      )
    );
  }
}
