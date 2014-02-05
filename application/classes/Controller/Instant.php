<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Instant controller.
 **/
class Controller_Instant extends Controller_Layout {
  protected $secure_actions = array(
    'index','create', 'edit', 'delete'
  );
  protected $controls = array(
    'subject' => 'input',
    'text' => 'textarea',
    'is_draft' => 'checkbox'
  );
  public function action_create()
  {
    $this->template = new View_Edit;
    $id = $this->request->param('id');
    if (!Model_Course::exists($id))
    {
      $this->redirect('error/500');
    }
    $this->template->model = ORM::factory('Instant');
    $this->template->model->subscription_id = $id;
    $this->template->title = __('New letter');
    $this->_edit($this->template->model);
  }
  public function action_index()
  {
    $this->template = new View_Instant_Index;
    $id = $this->request->param('id');
    $model = ORM::factory('Subscription', $id)->with('instants');
    if (!$model->loaded())
    {
      $this->redirect('error/404');
    }
    $this->template->title = __('Subscription').' '.$model->title;
    $this->template->subscription_id = $id;
    $this->template->items = $model->instants
      ->filter_by_page($this->request->param('page'))
      ->order_by('timestamp')
      ->find_all();
  }

  public function action_edit()
  {
    $this->template = new View_Edit;
    $this->template->title = __('Instant editing');
    $id = $this->request->param('id');
    $model = ORM::factory('Instant', $id);
    if (!$model->loaded())
    {
      $this->redirect('error/404');
    }
    $this->_edit($model);
  }
}
