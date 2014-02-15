<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Course model.
 * Course is an ordered collection of letters.
 * It has a period in days. Every <period> days a client receives a letter from the collection.
 * @package Models
 * @author Oreolek
 **/
class Model_Course extends ORM {
  protected $_has_many = array(
    'client' => array(
      'model' => 'Client',
      'through' => 'clients_courses'
    ),
    'letters' => array(
      'model' => 'Letter'
    )
  );

  /**
   * @return array validation rules
   **/
  public function rules()
	{
		return array(
      'title' => array(
				array('not_empty'),
				array('min_length', array(':value', 4)),
				array('max_length', array(':value', 100)),
      ),
      'description' => array(
				array('not_empty'),
				array('min_length', array(':value', 20)),
      ),
      'period' => array(
        array('numeric')
      ),
      'price' => array(
        array('numeric')
      )
		);
	}

  /**
   * Array of field labels.
   * Used in forms.
   **/
  protected $_labels = array(
    'title' => 'Title',
    'price' => 'Subscription price',
    'period' => 'Mailing period (in days)',
    'description' => 'Description (for the clients)'
  );

  public function customize()
  {
    if ($this->period < 1)
    {
      $this->period = 1;
    }
    if (empty($this->price))
    {
      $this->price = 0;
    }
  }

  public static function count_letters($id)
  {
    return DB::select(array(DB::expr('COUNT(*)'), 'cnt'))->from('letters')->where('course_id', '=', $id)->execute()->get('cnt');
  }

  /**
   * Return subscriber count
   **/
  public function count_clients()
  {
    return DB::select(array(DB::expr('COUNT(client_id)'), 'cnt'))->from('clients_courses')->where('course_id', '=', $this->id)->execute()->get('cnt');
  }

  public static function exists($id)
  {
    $count = DB::select(array(DB::expr('COUNT(*)'), 'cnt'))->from('courses')->where('id', '=', $id)->execute()->get('cnt');
    return ($count == 1);
  }

  public static function get_ids()
  {
    return DB::select('id')->from('courses')->execute()->get('id');
  }

  public static function get_period($course_id)
  {
    return DB::select('period')
      ->from('courses')
      ->where('course_id', '=', $course_id)
      ->execute()
      ->get('period');
  }

  public static function get_letter_ids($course_id)
  {
    return DB::select('id')
      ->from('letters')
      ->where('course_id', '=', $course_id)
      ->order_by('order')
      ->execute()
      ->get('id');
  }
  public static function get_client_ids($course_id)
  {
    return DB::select('client_id')
      ->from('clients_courses')
      ->where('course_id', '=', $course_id)
      ->execute()
      ->get('client_id');
  }

  /**
   * Get next letter in course
   * @param int $offset search offset (typically number of already sent letters)
   **/
  public function next_letter($offset = 0)
  {
    return ORM::factory('Letter')
      ->where('course_id', '=', $this->id)
      ->order_by('order', 'ASC')
      ->limit(1)
      ->offset($offset)
      ->find();
  }

  public function delete()
  {
    $letter_ids = $this->get_letter_ids($this->id);
    $query = DB::delete('tasks');
    if (is_array($letter_ids))
    {
      $query->where('letter_id', 'IN', $letter_ids);
    }
    else
    {
      $query->where('letter_id', '=', $letter_ids);
    }
    $query->execute();
    DB::delete('letters')
      ->where('course_id', '=', $this->id)
      ->execute();
    DB::delete('clients_courses')
      ->where('course_id', '=', $this->id)
      ->execute();
    return parent::delete();
  }
}
