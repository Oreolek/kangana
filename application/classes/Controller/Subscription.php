<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Subscription controller.
 **/
class Controller_Subscription extends Controller_Layout {
  protected $secure_actions = array(
    'index',
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
      $post->delete();
      $this->redirect('/');
    }
  }

  /**
   * Edit or create model.
   **/
  protected function _edit($model)
  {
    if (!($model instanceof ORM))
    {
      Log::instance()->add(Log::ERROR, __('Attempt to call _edit() on non-ORM model. Parameter class should be ORM, not  ').get_class($model).'.');
      $this->redirect('error/500');
    }
    $this->template->errors = array();
    
    if ($this->request->method() === HTTP_Request::POST) {
      $model->values($this->request->post(), array_keys($this->controls));
      $validation = $model->validate_create($this->request->post());
      $model->customize();
      try
      {
        if ($validation->check())
        {
          $model->save();
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
        $this->redirect(Route::url('default', array('controller' => Request::current()->controller(), 'action' => 'view','id' => $model->id)));
      }
    }
    $this->template->model = $model;
  }

}
