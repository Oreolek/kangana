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

  /**
    * @group Mail
    * Tests that letter shouldn't be prepared if it's too early to send it.
   **/
  function test_prepare_timeout()
  {
    DB::delete('tasks')
      ->where('letter_id', '=', 1)
      ->and_where('client_id','=',1)
      ->execute();
    DB::insert('tasks', array('letter_id', 'client_id', 'date', 'status'))
      ->values(array(1,1,date('Y-m-d'), Model_Task::STATUS_PENDING))
      ->execute();
    $check = Model_Task::check_period(1, 1, NULL);
    $this->assertEquals(TRUE, $check);
    $check = Model_Task::check_period(1, 1, 1);
    $this->assertEquals(FALSE, $check);
    $check = Model_Task::check_period(1, 1, 0);
    $this->assertEquals(TRUE, $check);

    DB::delete('tasks')
      ->where('letter_id', '=', 1)
      ->and_where('client_id','=',1)
      ->execute();
    DB::insert('tasks', array('letter_id', 'client_id', 'date', 'status'))
      ->values(array(1,1,date('Y-m-d', strtotime("-1 days")), Model_Task::STATUS_PENDING))
      ->execute();
    $check = Model_Task::check_period(1, 1, 1);
    $this->assertEquals(TRUE, $check);
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
