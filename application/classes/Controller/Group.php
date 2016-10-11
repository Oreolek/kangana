<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Client controller.
 **/
class Controller_Group extends Controller_Layout {
  protected $secure_actions = array(
    'index',
    'create',
    'edit',
    'delete',
    'view',
    'search',
  );
  protected $controls = array(
    'name' => 'input',
  );
  public function action_index()
  {
    $this->template = new View_Index;
    $this->template->title = __('Groups');
    $this->template->header = [
      __('Group name'),
      __('Edit'),
      __('Delete'),
    ];
    $this->template->items = ORM::factory('Group')
      ->filter_by_page($this->request->param('page'))
      ->find_all();
  }

  /**
   * Manually add a group.
   **/
  public function action_create()
  {
    $this->template = new View_Edit;
    $this->template->model = ORM::factory('Group');
    $this->template->title = __('New group');
    $this->_edit($this->template->model);
  }

  public function action_edit()
  {
    $this->template = new View_Edit;
    $this->template->title = __('Edit group info');
    $id = $this->request->param('id');
    $model = ORM::factory('Group', $id);
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
    $model = ORM::factory('Group', $id);
    if (!$model->loaded())
    {
      $this->redirect('error/404');
    }
    $this->template->title = __('Delete group');
    $this->template->content_title = $model->name;
    // $this->template->content = $model->email;
    // TODO - display a list of subscribers in group

    $confirmation = $this->request->post('confirmation');
    if ($confirmation === 'yes') {
      $model->delete();
      $this->redirect('/');
    }
  }

  /**
   * Group view.
   **/
  public function action_view()
  {
    $this->redirect('client/index?group_id='.$this->request->param('id'));
  }

  /**
   * Search groups.
   **/
  public function action_search()
  {
    $this->template = new View_Client_Index;
    $this->template->show_create = FALSE;
    $this->template->title = __('Groups');
    $query = $this->request->post('query');
    $this->template->items = ORM::factory('Group')
      ->search($query)
      ->filter_by_page($this->request->param('page'))
      ->find_all();
  }
}
