<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Electronic letter model.
 * Stores HTML to be placed in e-mail template.
 * @package Models
 * @author Oreolek
 **/
class Model_Letter extends ORM {
  protected $belongs_to = array(
    'subscription'
  );
  
  /**
   * @return array validation rules
   **/
  public function rules()
	{
		return array(
      'text' => array(
				array('not_empty'),
				array('min_length', array(':value', 20)),
      ),
      'order' => array(
        array('numeric')
      )
		);
	}

  /**
   * Array of field labels.
   * Used in forms.
   **/
  protected $_labels = array(
    'text' => 'Message text',
    'order' => 'Message order'
  );

  public function customize()
  {
    if(empty($this->order))
    {
      $this->order = Model_Subscription::count_letters($this->subscription_id) + 1;
    }
  }

}
