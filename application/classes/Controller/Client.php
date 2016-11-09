<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Client controller.
 **/
class Controller_Client extends Controller_Layout {
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
    'email' => 'input',
  );
  public function action_index()
  {
    $this->template = new View_Client_Index;
    $this->template->title = I18n::translate('Clients');
    if ($this->request->method() === Request::POST)
    {
      $group_id = $this->request->post('group_id');
    } else {
      $group_id = $this->request->query('group_id');
    }
    if ($group_id)
    {
      $group = ORM::factory('Group', $group_id);
      if ( ! $group->loaded()) {
        $this->redirect('error/404');
      }
      $this->template->title .= ' - '.$group->name;
      $this->template->items = $group
        ->clients
        ->filter_by_page($this->request->param('page'))
        ->find_all();
    } else {
      $this->template->items = ORM::factory('Client')
        ->filter_by_page($this->request->param('page'))
        ->find_all();
    }
  }

  /**
   * Manually add a client.
   **/
  public function action_create()
  {
    $this->template = new View_Edit;
    $this->template->model = ORM::factory('Client');
    $this->template->title = I18n::translate('New client');
    $this->_edit($this->template->model);
  }

  /**
   * Manually add a client.
   **/
  public function action_view()
  {
    $this->template = new View_Client_View();
    $model = ORM::factory('Client', $this->request->param('id'))
      ->with('courses')
      ->with('groups');
    if ( ! $model->loaded())
    {
      $this->redirect('error/404');
    }
    $this->template->title = I18n::translate('Client details for').' '.$model->email;
    $this->template->model = $model;
  }

  public function action_edit()
  {
    $this->template = new View_Edit;
    $this->template->title = I18n::translate('Edit client info');
    $id = $this->request->param('id');
    $model = ORM::factory('Client', $id);
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
    $model = ORM::factory('Client', $id);
    if ( ! $model->loaded())
    {
      $this->redirect('error/404');
    }
    $this->template->title = I18n::translate('Delete client');
    $this->template->content_title = $model->name;
    $this->template->content = $model->email;

    $confirmation = $this->request->post('confirmation');
    if ($confirmation === 'yes') {
      $model->delete();
      $this->redirect('/');
    }
  }

  /**
   * Search a client.
   **/
  public function action_search()
  {
    $this->template = new View_Client_Index;
    $this->template->show_create = FALSE;
    $this->template->title = I18n::translate('Clients');
    $query = $this->request->post('query');
    $this->template->items = ORM::factory('Client')
      ->search($query)
      ->filter_by_page($this->request->param('page'))
      ->find_all();
  }
}
