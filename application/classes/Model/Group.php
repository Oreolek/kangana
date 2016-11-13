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
      'name' => array(
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
    'name' => 'Name',
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

  /**
   * Get ID of all groups which have subscribers
   **/
  public static function get_ids()
  {
    return DB::select('group_id')->distinct(TRUE)->from('clients_groups')->execute()->as_array(NULL, 'group_id');
  }
}
