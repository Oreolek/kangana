<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Letter controller.
 **/
class Controller_Letter extends Controller_Layout {
  protected $secure_actions = array(
    'index','create', 'edit', 'delete'
  );
  protected $controls = array(
    'subject' => 'input',
    'text' => 'textarea',
    'order' => 'input',
  );
  public function action_create()
  {
    $this->template = new View_Edit;
    $id = $this->request->param('id');
    if (!Model_Course::exists($id))
    {
      $this->redirect('error/500');
    }
    $this->template->model = ORM::factory('Letter');
    $this->template->model->course_id = $id;
    $this->template->title = __('New letter');
    $this->_edit($this->template->model);
  }

  public function action_edit()
  {
    $this->template = new View_Edit;
    $this->template->title = __('Letter editing');
    $id = $this->request->param('id');
    $model = ORM::factory('Letter', $id);
    if (!$model->loaded())
    {
      $this->redirect('error/404');
    }
    $this->_edit($model);
  }
  
  protected function _edit_redirect($model)
  {
    return Route::url('default', array('controller' => 'Course','action' => 'view','id' => $model->course_id));
  }

  public function action_delete()
  {
    $this->template = new View_Delete;
    $id = $this->request->param('id');
    $model = ORM::factory('Letter', $id);
    if (!$model->loaded())
    {
      $this->redirect('error/404');
    }
    $this->template->title = __('Delete letter');
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
    $id = $this->request->param('id');
    $model = ORM::factory('Letter', $id);
    if (!$model->loaded())
    {
      $this->redirect('error/404');
    }
    Debugtoolbar::disable();
    $this->template = new View_Letter_View;
    $this->template->content = $model->text;
    $this->template->subject = $model->subject;
    $this->template->id = $model->id;
  }
}
