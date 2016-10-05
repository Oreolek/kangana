<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Group model.
 * Group is a collection of clients.
 * @package Models
 * @author Oreolek
 **/
class Model_Group extends ORM {
  protected $_has_many = array(
    'clients' => array(
      'model' => 'Client',
      'through' => 'clients_groups'
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
	array('max_length', array(':value', 255)),
      ),
    );
  }

  /**
   * Array of field labels.
   * Used in forms.
   **/
  protected $_labels = array(
    'title' => 'Title',
  );

  public static function count($id)
  {
    $query = DB::select(array(DB::expr('COUNT(*)'), 'cnt'))->from('clients_groups')->where('group_id', '=', $id);
    return $query->execute()->get('cnt');
  }

  /**
   * Return client count
   **/
  public function count_clients()
  {
    return self::count($this->id);
  }
}
