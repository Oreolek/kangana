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
    $this->redirect('instant/index/'.$this->request->param('id'));
  }

  public function action_subscribe()
  {
    $this->template = new View_Course_Subscribe;
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
          if (!$model->has('subscription', $subscription))
          {
            $model->add('subscription', $subscription);
          }
          $instant = ORM::factory('Instant');
          $instant->subscription_id = $id;
          $instant->subject = __('You were subscribed to ').$subscription->title;
          $instant->text = __('From now on you will receive letters from this subscription.');
          $instant->send($model->email, $model->token);
          // instant is not saved because it's just a welcome email
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
