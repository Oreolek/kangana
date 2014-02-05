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

  public function getSetUpOperation() {
    // whether you want cascading truncates
    // set false if unsure
    $cascadeTruncates = false;

    return new PHPUnit_Extensions_Database_Operation_Composite(array(
      new PHPUnit_Extensions_Database_Operation_MySQL55Truncate($cascadeTruncates),
      PHPUnit_Extensions_Database_Operation_Factory::INSERT()
    ));
  }

  /**
   * @group Mail
   **/
  function testPrepareCourse()
  {
    Minion_Task::factory(array('task' => 'prepare'))->execute();
    $status = DB::select('status')->from('tasks')->where('letter_id', '=', '1')->and_where('client_id','=','1')->execute()->get('status');
    $this->assertEquals(Model_Task::STATUS_PENDING, $status);
  }

  function testSendCourse()
  {
    $status = DB::select('status')->from('tasks')->where('letter_id', '=', '1')->and_where('client_id','=','1')->execute()->get('status');
    if (is_null($status))
    {
      DB::insert('tasks', array('letter_id', 'client_id', 'date', 'status'))->values(array('1','1',date('Y-m-d'), Model_Task::STATUS_PENDING))->execute();
    }
    Minion_Task::factory(array('task' => 'send'))->execute();
    $status = DB::select('status')->from('tasks')->where('letter_id', '=', '1')->and_where('client_id','=','1')->execute()->get('status');
    $this->assertEquals(Model_Task::STATUS_SENT, $status);
  }
}
