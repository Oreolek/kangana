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

  protected $_has_many = array(
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

  public function execute()
  {
    $letter = ORM::factory('Letter', $this->letter_id);
    $client = ORM::factory('Client', $this->client_id);
    $letter->send($client->email);
    $this->status = STATUS_SENT;
    $this->save();
  }

  /**
   * Get next unsent letter by client. Returns FALSE if there're no letters left to send.
   * @param array $letters array of letter IDs (integers) to send
   * @param int $client_id client ID
   * @return int/bool first unsent letter ID or FALSE
   **/
  public static function next_unsent($client_id, $letters)
  {
    $query = DB::select(array(DB::expr('COUNT(*)'), 'cnt'))
      ->from('tasks');
    if (is_array($letters))
    {
      $query = $query->where('letter_id', 'IN', $letters);
    }
    else
    {
      $query = $query->where('letter_id', '=', $letters);
    }
    $cnt = $query
      ->and_where('status', '=', self::STATUS_SENT)
      ->and_where('client_id', '=', $client_id)
      ->execute()
      ->get('cnt');
    if ($cnt < count($letters))
    {
      $query = DB::select('letter_id')
        ->from('tasks');
      if (is_array($letters))
      {
        $query = $query->where('letter_id', 'IN', $letters);
      }
      else
      {
        $query = $query->where('letter_id', '=', $letters);
      }
      $sent_letters = $query
        ->and_where('status', '=', self::STATUS_SENT)
        ->and_where('client_id', '=', $client_id)
        ->execute()
        ->get('letter_id');
      if (is_array($letters))
      {
        $diff = array_diff($letters, $sent_letters);
        return $diff[0];
      }
      else
      {
        // count($letters) is > count($sent_letters), $letters is not an array
        // so there are no sent letters
        return $letters;
      }
    }
    return FALSE;
  }

  public static function prepare($client_id, $letter_id)
  {
    return DB::insert('tasks', array('client_id', 'letter_id', 'date', 'status'))
      ->values(array($client_id, $letter_id, date('Y-m-d'), self::STATUS_PENDING))
      ->execute();
  }

  /**
   * Get last sent or prepared letter and check if it's time to send another one.
   **/
  public static function check_period($client_id, $letters, $period)
  {
    $query = DB::select('date')
      ->from('tasks');
    if (is_array($letters))
    {
      $query = $query->where('letter_id', 'IN', $letters);
    }
    else
    {
      $query = $query->where('letter_id', '=', $letters);
    }
    $check = NULL;
    $check = $query
      ->and_where('status', '=', self::STATUS_SENT)
      ->or_where('status', '=', self::STATUS_PENDING)
      ->and_where('client_id', '=', $client_id)
      ->and_where(DB::expr('DATEDIFF(CURDATE(), `date`)'), '=', $period)
      ->execute()
      ->get('date');
    if (!empty($check))
      return TRUE;
    return FALSE;
  }
}
