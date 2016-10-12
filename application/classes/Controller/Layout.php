<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Layout extends Controller {
  protected $secure_actions = FALSE;
  protected $is_private = FALSE;
  public $auto_render = TRUE;
  public $template = '';
  /**
   * Array of CRUD controls (create & edit).
   * @see View_Edit
   **/
  protected $controls = array();

  public function before()
  {
    parent::before();
    $action_name = $this->request->action();
    if (is_array($this->secure_actions)
      AND in_array($action_name, $this->secure_actions, TRUE))
    {
      if (Auth::instance()->logged_in() === FALSE)
      {
        $this->redirect('user/signin');
      } else {
        // user is clear to go but his pages are cache-sensitive
        $this->is_private = TRUE;
      }
    }
  }
  public function after()
  {
    if ($this->auto_render)
    {
      if ( ! empty($this->controls) AND empty($this->template->controls))
      {
        $this->template->controls = $this->controls;
      }
      $renderer = Kostache_Layout::factory($this->template->_layout);
      $this->response->body($renderer->render($this->template, $this->template->_view));
    }
    if ($this->is_private)
    {
      $this->response->headers( 'cache-control', 'private' );
      $this->check_cache();
    }
  }

  /**
   * Edit or create model.
   **/
  protected function _edit($model, $controls = NULL)
  {
    if ( ! ($model instanceof ORM))
    {
      Log::instance()->add(Log::ERROR, I18n::translate('Attempt to call _edit() on non-ORM model. Parameter class should be ORM, not  ').get_class($model).'.');
      $this->redirect('error/500');
    }
    $this->template->errors = array();
    if (is_null($controls))
    {
      $controls = $this->controls;
    }

    if ($this->request->method() === Request::POST) {
      $model->values($this->request->post(), array_keys($controls));
      $model->customize();
      $validation = $model->validate_create($this->request->post());
      try
      {
        if ($validation->check())
        {
          $model->save();
          $this->afterSave($model);
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
        $this->redirect($this->_edit_redirect($model));
      }
    }
    $this->template->model = $model;
  }

  /**
   * Event after saving the model.
   * @param ORM $model
   * @return void
   **/
  protected function after_save($model)
  {
  }

  /**
   * Where to redirect after successful model editing.
   * @param ORM $model
   **/
  protected function _edit_redirect($model)
  {
    return Route::url('default', array('controller' => Request::current()->controller(),'action' => 'view','id' => $model->id));
  }
}
