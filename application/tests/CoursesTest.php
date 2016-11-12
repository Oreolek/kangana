<?php

/**
  * Test for course workflow.
  * @category Tests
  * @author Oreolek
  * @license AGPL
  **/
class CoursesTest extends Unittest_Database_TestCase
{
  protected $_database_connection = 'test';

  protected function getdataset()
  {
    return $this->createXMLDataSet(Kohana::find_file('tests', 'test_data/courses', 'xml'));
  }

  /**
   * @group Mail
   **/
  function test_prepare_course()
  {
    DB::delete('tasks')
      ->where('letter_id', '=', 1)
      ->and_where('client_id','=',1)
      ->execute();
    Minion_Task::factory(['prepare'])
      ->execute();
    $status = DB::select('status')
      ->from('tasks')
      ->where('letter_id', '=', 1)
      ->and_where('client_id','=',1)
      ->execute()
      ->get('status');
    $this->assertEquals(Model_Task::STATUS_PENDING, $status);
  }

  function test_send_course()
  {
    $status = DB::select('status')
      ->from('tasks')
      ->where('letter_id', '=', 1)
      ->and_where('client_id','=',1)
      ->execute()
      ->get('status');
    if (is_null($status) or $status !== Model_Task::STATUS_PENDING)
    {
      DB::insert('tasks', array('letter_id', 'client_id', 'date', 'status'))
        ->values(array(1,1,date('Y-m-d'), Model_Task::STATUS_PENDING))
        ->execute();
    }
    Minion_Task::factory(['send'])
      ->execute();
    $status = DB::select('status')
      ->from('tasks')
      ->where('letter_id', '=', 1)
      ->and_where('client_id','=',1)
      ->execute()
      ->get('status');
    $this->assertEquals(Model_Task::STATUS_SENT, $status);
  }
}
