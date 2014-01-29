<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Letter controller.
 **/
class Controller_Letter extends Controller_Layout {
  protected $secure_actions = array(
    'index','create', 'edit', 'delete'
  );
  protected $controls = array(
    'text' => 'textarea',
    'order' => 'input',
  );
  public function action_create()
  {
    $this->template = new View_Edit;
    $id = $this->request->param('id');
    if (!Model_Subscription::exists($id))
    {
      $this->redirect('error/500');
    }
    $this->template->model = ORM::factory('Letter');
    $this->template->model->subscription_id = $id;
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
      $post->delete();
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
    $this->template->items = $model->letters
      ->filter_by_page($this->request->param('page'))
      ->find_all();
  }
}
