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
    if (
      Kohana::$environment === Kohana::PRODUCTION &&
      is_array($this->secure_actions) &&
      in_array($action_name, $this->secure_actions, TRUE)
    )
    {
      if ( Auth::instance()->logged_in() === FALSE)
      {
        $this->redirect('user/signin');
      }
      else
      {
        //user is clear to go but his pages are cache-sensitive
        $this->is_private = TRUE;
      }
    }
  }
  public function after()
  {
    if ($this->auto_render)
    {
      if (!empty($this->controls) && empty($this->template->controls))
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
    if (!($model instanceof ORM))
    {
      Log::instance()->add(Log::ERROR, __('Attempt to call _edit() on non-ORM model. Parameter class should be ORM, not  ').get_class($model).'.');
      $this->redirect('error/500');
    }
    $this->template->errors = array();
    if (is_null($controls))
    {
      $controls = $this->controls;
    }
    
    if ($this->request->method() === HTTP_Request::POST) {
      $model->values($this->request->post(), array_keys($controls));
      $model->customize();
      $validation = $model->validate_create($this->request->post());
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
