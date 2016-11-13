<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Course model.
 * Course is an ordered collection of letters.
 * It has a period in days. Every <period> days a client receives a letter from the collection.
 * @package Models
 * @author Oreolek
 **/
class Model_Course extends ORM {
  /**
   * A pre-scheduled course - the subscriber gets a new letter once per N days in the series.
   */
  const TYPE_SCHEDULED = 0;

  /**
   * A irregular subscription - the letters are sent when the author sends them
   */
  const TYPE_IRREGULAR = 1;

  protected $_has_many = array(
    'clients' => array(
      'model' => 'Client',
      'through' => 'clients_courses'
    ),
    'letters' => array(
      'model' => 'Letter'
    ),
  );

  protected $_belongs_to = array(
    'group' => array(
      'model' => 'Group',
    ),
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
      ),
      'group_id' => array(
        array('numeric')
      ),
      'type' => array(
        array('numeric')
      ),
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

  public static function type_labels()
  {
    return [
      self::TYPE_IRREGULAR => I18n::translate('Subscription'),
      self::TYPE_SCHEDULED => I18n::translate('Course'),
    ];
  }

  public static function count_letters($id)
  {
    $query = DB::select(array(DB::expr('COUNT(*)'), 'cnt'))->from('letters')->where('course_id', '=', $id);
    return $query->execute()->get('cnt');
  }

  /**
   * Return subscriber count
   **/
  public function count_clients()
  {
    $use_groups = Kohana::$config->load('common.groupmode');
    if( ! $use_groups)
      return DB::select(array(DB::expr('COUNT(client_id)'), 'cnt'))->from('clients_courses')->where('course_id', '=', $this->id)->execute()->get('cnt');
    else
      return $this->group->count_clients();
  }

  public static function exists($id)
  {
    $count = DB::select(array(DB::expr('COUNT(*)'), 'cnt'))->from('courses')->where('id', '=', $id)->execute()->get('cnt');
    return ($count == 1);
  }

  /**
   * Get ID of all courses which have subscribers
   **/
  public static function get_ids()
  {
    $use_groups = Kohana::$config->load('common.groupmode');
    if( ! $use_groups)
    {
      return DB::select('course_id')
        ->distinct(TRUE)
        ->from('clients_courses')
        ->execute()
        ->as_array(NULL, 'course_id');
    }
    else
    {
      $groups = Model_Group::get_ids();
      return DB::select('id')
        ->from('courses')
        ->where('group_id', 'IN', $groups)
        ->execute()
        ->as_array(NULL, 'id');
    }
  }

  public static function get_period($course_id)
  {
    $query = DB::select('period')
      ->from('courses')
      ->where('id', '=', $course_id);
    return $query->execute()->get('period');
  }

  public static function get_letter_ids($course_id)
  {
    $query = DB::select('id')
      ->from('letters')
      ->where('course_id', '=', $course_id)
      ->order_by('order');
    return $query->execute()->as_array(NULL, 'id');
  }

  public static function get_client_ids($course_id)
  {
    $use_groups = Kohana::$config->load('common.groupmode');
    if( ! $use_groups)
    {
      return DB::select('client_id')
        ->from('clients_courses')
        ->where('course_id', '=', $course_id)
        ->execute()
        ->get('client_id');
    }
    else
    {
      $group_id = DB::select('group_id')
        ->from('courses')
        ->where('id', '=', $course_id)
        ->execute()
        ->get('group_id');
      return DB::select('client_id')
        ->from('clients_groups')
        ->where('group_id', '=', $group_id)
        ->execute()
        ->get('client_id');
    }
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
    if ( ! empty($letter_ids))
    {
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
    }
    DB::delete('letters')
      ->where('course_id', '=', $this->id)
      ->execute();
    DB::delete('clients_courses')
      ->where('course_id', '=', $this->id)
      ->execute();
    return parent::delete();
  }
}
