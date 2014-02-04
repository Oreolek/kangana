<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Subscription controller.
 **/
class Controller_Subscription extends Controller_Layout {
  protected $secure_actions = array(
    'index','create', 'edit', 'delete', 'view'
  );
  protected $controls = array(
    'title' => 'input',
    'description' => 'textarea',
    'period' => 'input',
//    'price' => 'input'
  );
  
  public function action_index()
  {
    $this->template = new View_Subscription_Index;
    $this->template->title = __('Subscription index');
    $this->template->items = ORM::factory('Subscription')
      ->filter_by_page($this->request->param('page'))
      ->find_all();
  }

  public function action_create()
  {
    $this->template = new View_Edit;
    $this->template->model = ORM::factory('Subscription');
    $this->template->title = __('New subscription');
    $this->_edit($this->template->model);
  }

  public function action_edit()
  {
    $this->template = new View_Edit;
    $this->template->title = __('Edit subscription');
    $id = $this->request->param('id');
    $model = ORM::factory('Subscription', $id);
    if (!$model->loaded())
    {
      $this->redirect('error/404');
    }
    $this->_edit($model);
  }

  public function action_delete()
  {
    $this->template = new View_Delete;
    $id = $this->request->param('id');
    $model = ORM::factory('Subscription', $id);
    if (!$model->loaded())
    {
      $this->redirect('error/404');
    }
    $this->template->title = __('Delete subscription');
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
    $model = ORM::factory('Subscription', $id)->with('letters');
    if (!$model->loaded())
    {
      $this->redirect('error/404');
    }
    $this->template->title = __('Subscription').' '.$model->title;
    $this->template->subscription_id = $id;
    $this->template->items = $model->letters
      ->filter_by_page($this->request->param('page'))
      ->order_by('order')
      ->find_all();
  }

  public function action_subscribe()
  {
    $this->template = new View_Subscription_Subscribe;
    $id = $this->request->param('id');
    $subscription = ORM::factory('Subscription', $id);
    if (!$subscription->loaded())
    {
      $this->redirect('error/404');
    }
    $this->template->title = __('Subscribe to ').$subscription->title;
    $controls = array(
      'name' => 'input',
      'email' => 'input'
    );
    $this->template->controls = $controls;
    $this->template->errors = array();
    $model = ORM::factory('Client');
    
    if ($this->request->method() === HTTP_Request::POST) {
      $model = ORM::factory('Client')->where('email', '=', $this->request->post('email'))->find();
      $model->values($this->request->post(), array_keys($controls));
      $model->customize();
      $validation = $model->validate_create($this->request->post());
      try
      {
        if ($validation->check())
        {
          $model->save();
          $model->add('subscription', $subscription);
          $task = ORM::factory('Task');
          $letter = $subscription->next_letter();
          $task->letter_id = $letter->id;
          $task->client_id = $model->id;
          // now we break the abstraction to speed things up
          $task->status = Model_Task::STATUS_SENT;
          $task->date = date('Y-m-d');
          $task->create();
          $letter->send($model->email);
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
        Session::instance()->set('flash_success', __('You were subscribed. A welcome email has been sent to you. Please check your inbox.'));
      }
    }
    $this->template->model = $model;
  }

}
