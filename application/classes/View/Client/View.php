<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Client details view controller
 **/
class View_Client_View extends View_Layout {
  protected $details;

  /**
   * Prepare and return a details table
   * @return array
   */
  public function get_details()
  {
    $retval = [];
    $retval[] = $this->detail('Name', 'name');
    $retval[] = $this->detail('Email', 'email');
    $retval[] = $this->detail('Sex', 'sex', $this->sex($this->model->sex));
    $retval[] = $this->detail('Referrer', 'referrer');
    $retval[] = $this->detail('City', 'city');
    $retval[] = [
      'caption' => I18n::translate('Groups'),
      'value' => implode(',', $this->model->group->find_all()->as_array(NULL, 'name'))
    ];
    $retval[] = [
      'caption' => I18n::translate('Courses'),
      'value' => implode(',', $this->model->course->find_all()->as_array(NULL, 'title'))
    ];
    return $retval;
  }

  protected function detail($caption, $attribute, $value = NULL)
  {
    if (is_null($value))
    {
      $value = $this->model->$attribute;
      if (empty($value))
      {
        $value = I18n::translate('unknown');
      }
    }
    return [
      'caption' => I18n::translate($caption),
      'value' => $value
    ];
  }

  protected function sex($value)
  {
    if ($value === 'm')
    {
      return I18n::translate('Male');
    }
    if ($value === 'f')
    {
      return I18n::translate('Female');
    }
    return I18n::translate('unknown');
  }
}
