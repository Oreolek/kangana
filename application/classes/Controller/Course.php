<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Course controller.
 **/
class Controller_Course extends Controller_Layout {
  protected $secure_actions = array(
    'index',
    'sindex',
    'create',
    'edit',
    'delete',
    'view',
  );
  protected $controls = NULL;

  public function action_index()
  {
    $this->template = new View_Course_Index;
    $this->template->title = I18n::translate('Course index');

    $this->template->content = '<p>'
      .I18n::translate('A course is a pre-defined regular mailing list.')
      .' '
      .I18n::translate("You <i>add</i> a message, forming a series of messages.")
      .' '
      .I18n::translate("Each new subscriber gets the first message in this series.")
      .' '
      .I18n::translate("You can customize the delay (1 day by default) between the messages.")
      .'</p>';

    $this->template->items = ORM::factory('Course')
      ->where('type', '=', Model_Course::TYPE_SCHEDULED)
      ->filter_by_page($this->request->param('page'))
      ->find_all();
  }

  public function action_sindex()
  {
    $this->template = new View_Course_Index;
    $this->template->title = I18n::translate('Subscription index');

    $this->template->content =  '<p>'
      .I18n::translate('A subscription is a non-regular mailing list.')
      .' '
      .I18n::translate("You <i>add</i> a message, and then you send it to subscribers.")
      .'</p>';

    $this->template->items = ORM::factory('Course')
      ->where('type', '=', Model_Course::TYPE_IRREGULAR)
      ->filter_by_page($this->request->param('page'))
      ->find_all();
  }

  public function action_create()
  {
    $this->template = new View_Course_Edit;
    $this->controls = [
      'title' => 'input',
      'description' => 'textarea',
    ];
    $this->template->model = ORM::factory('Course');
    $this->template->title = I18n::translate('New course');
    $this->_edit($this->template->model);
  }

  /**
   * One-page course creation
   **/
  public function action_simple()
  {
    $this->template = new View_Course_Simple;
    $this->template->controls = array();
    $this->template->title = I18n::translate('New course');
    $course = ORM::factory('Course');
    $course->type = Model_Course::TYPE_SCHEDULED;
    $letter = ORM::factory('Letter');
    if ($this->request->method() === Request::POST) {
      $course->values($this->request->post(), array('title', 'description', 'group_id'));
      $letter->values($this->request->post(), array('subject', 'text'));
      $course->price = 0;
      $course->period = 1;
      $letter->order = 1;
      $validation_course = $course->validate_create($this->request->post());
      $validation_letter = $letter->validate_create($this->request->post());
      try
      {
        if ($validation_course->check() AND $validation_letter->check())
        {
          $course->create();
          $letter->course_id = $course->id;
          $letter->create();
        }
        else
        {
          $this->template->errors = array_merge(
            $validation_course->errors('default'),
            $validation_letter->errors('default')
          );
        }
      }
      catch (ORM_Validation_Exception $e)
      {
        $this->template->errors = $e->errors('default');
      }
      if (empty($this->template->errors))
      {
        $this->redirect($this->_edit_redirect($course));
      }
    }
    $this->template->model_letter = $letter;
    $this->template->model_course = $course;
  }

  public function action_edit()
  {
    $this->template = new View_Course_Edit;
    $this->template->title = I18n::translate('Edit course');
    $id = $this->request->param('id');
    $model = ORM::factory('Course', $id);
    $this->controls = [
      'title' => 'input',
      'description' => 'textarea',
    ];
    if ($model->type === Model_Course::TYPE_SCHEDULED)
    {
      $this->controls['period'] = 'input';
      $this->controls['price'] = 'input';
    };
    if ( ! $model->loaded())
    {
      $this->redirect('error/404');
    }
    $this->_edit($model);
  }

  public function action_delete()
  {
    $this->template = new View_Delete;
    $id = $this->request->param('id');
    $model = ORM::factory('Course', $id);
    if ( ! $model->loaded())
    {
      $this->redirect('error/404');
    }
    $this->template->title = I18n::translate('Delete course');
    $this->template->content_title = $model->title;
    $this->template->content = $model->description;

    $confirmation = $this->request->post('confirmation');
    if ($confirmation === 'yes') {
      $model->delete();
      $this->redirect('/');
    }
  }
  public function action_view()
  {
    $this->template = new View_Letter_Index;
    $id = $this->request->param('id');
    $model = ORM::factory('Course', $id)->with('letters');
    if ( ! $model->loaded())
    {
      $this->redirect('error/404');
    }
    $this->template->title = I18n::translate('Course').' '.$model->title;
    $this->template->course_id = $id;
    $this->template->items = $model->letters
      ->filter_by_page($this->request->param('page'))
      ->order_by('order')
      ->find_all();
  }

  public function action_subscribe()
  {
    $this->template = new View_Course_Subscribe;
    $id = $this->request->param('id');
    $course = ORM::factory('Course', $id);
    if ( ! $course->loaded())
    {
      $this->redirect('error/404');
    }
    $this->template->title = I18n::translate('Subscribe to ') . $course->title;
    $controls = array(
      'name' => 'input',
      'email' => 'input'
    );
    $this->template->controls = $controls;
    $this->template->errors = array();
    $model = ORM::factory('Client');

    if ($this->request->method() === Request::POST) {
      $model = ORM::factory('Client')->where('email', '=', $this->request->post('email'))->find();
      $model->values($this->request->post(), array_keys($controls));
      $model->customize();
      $validation = $model->validate_create($this->request->post());
      try
      {
        if ($validation->check())
        {
          $model->save();
          if ( ! $model->has('course', $course))
          {
            $model->add('course', $course);
          }
          if ($course->group)
          {
            $model->add('group', $course->group);
          }
          if ($course->type = Model_Course::TYPE_SCHEDULED)
          {
            $task = ORM::factory('Task');
            $letter = $course->next_letter();
            $task->letter_id = $letter->id;
            $task->client_id = $model->id;
            // now we break the abstraction to speed things up
            $task->status = Model_Task::STATUS_SENT;
            $task->date = date('Y-m-d');
            $task->create();
            $letter->send($model->email);
          } else {
            $instant = ORM::factory('Letter');
            $instant->course_id = $id;
            $instant->subject = I18n::translate('You were subscribed to ').$course->title;
            $instant->text = I18n::translate('From now on you will receive letters from this subscription.');
            $instant->send($model->email, $model->token);
            // instant is not saved because it's just a welcome email
          }
        }
        else
        {
          $this->template->errors = $validation->errors('default');
        }
      }
      catch (ORM_Validation_Exception $e)
      {
        $this->template->errors = $e->errors('default');
      }
      if (empty($this->template->errors))
      {
        Session::instance()->set('flash_success', I18n::translate('You were subscribed. A welcome email has been sent to you. Please check your inbox.'));
      }
    }
    $this->template->model = $model;
  }

}
