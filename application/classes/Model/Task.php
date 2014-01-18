<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Task model.
 * Task is a job to send the next letter in a subscription.
 * @package Models
 * @author Oreolek
 **/
class Model_Task extends ORM {
  const STATUS_UNKNOWN = 0;
  const STATUS_PENDING = 1;
  const STATUS_SENDING = 2;
  const STATUS_SENT = 3;

  protected $has_many = array(
    'letter',
    'client'
  );
  
  /**
   * @return array validation rules
   **/
  public function rules()
	{
		return array(
      'date' => array(
				array('not_empty'),
				array('date'),
      ),
      'status' => array(
        array('numeric')
      )
		);
	}

  /**
   * Array of field labels.
   * Used in forms.
   **/
  protected $_labels = array(
    'date' => 'Mailing date',
    'status' => 'Status'
  );
}
