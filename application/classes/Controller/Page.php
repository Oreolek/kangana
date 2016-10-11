<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Page extends Controller_Layout {
  protected $secure_actions = array(
    'drafts', 'create', 'edit', 'delete'
  );
  protected $controls = array(
    'name' => 'input',
    'content' => 'textarea',
  );
  /**
   * View a page.
   **/
  public function action_view()
  {
    $this->auto_render = FALSE;
    $id = $this->request->param('id');
    $page = ORM::factory('Page', $id);
    if ( ! $page->loaded())
    {
      $this->redirect('error/404');
    }
    $is_admin = Auth::instance()->logged_in('admin');
    if ($page->is_draft AND ! $is_admin)
    {
      $this->redirect('error/403');
    }
    $cache = Cache::instance('apcu');
    $latest_change = $page->posted_at;
    if ( ! $is_admin)
    {
      $body = $cache->get('page_'.$id);
      if ( ! empty($body))
      {
        if ($cache->get('page_'.$id.'_changed') === $latest_change)
        {
          $this->response->body($body);
          return;
        }
        else
        {
          $cache->delete('page_'.$id);
        }
      }
    }
    $this->template = new View_Page_View;
    $this->template->title = $page->name;
    $this->template->content = $page->content;
    if ($page->is_draft)
    {
      $this->template->title .= ' ('.I18n::translate('draft').')';
    }
    $renderer = Kostache_Layout::factory('layout');
    $body = $renderer->render($this->template, $this->template->_view);
    if ( ! $is_admin)
    {
      $cache->set('page_'.$id, $body, 60*60*24); // cache page for 1 day
    }
    $cache->set('page_'.$id.'_changed', $latest_change);
    $this->response->body($body);
  }
  /**
   * Page index
   **/
  public function action_index()
  {
    $this->template = new View_Index;
    $this->template->title = I18n::translate('Page index');
    $this->template->show_date = FALSE;
    $this->template->items = ORM::factory('Page')
      ->where('is_draft', '=', '0')
      ->order_by('name', 'ASC')
      ->filter_by_page($this->request->param('page'))
      ->find_all();
  }
  public function action_delete()
  {
    $this->template = new View_Delete;
    $id = $this->request->param('id');
    $page = ORM::factory('Page', $id);
    if ( ! $page->loaded())
    {
      $this->redirect('error/404');
    }
    $this->template->title = I18n::translate('Delete page');
    $this->template->content_title = $page->name;
    $this->template->content = $page->content;

    $confirmation = $this->request->post('confirmation');
    if ($confirmation === 'yes') {
      $page->delete();
      $this->redirect('page/index');
    }
  }
  /**
   * Create a page (for admin)
   **/
  public function action_create()
  {
    $this->template = new View_Edit;
    $this->template->title = I18n::translate('New page');
    $this->template->errors = array();
    $this->template->model = ORM::factory('Page');
    $this->_edit($this->template->model);
  }
  /**
   * Edit a page (for admin)
   **/
  public function action_edit()
  {
    $this->template = new View_Page_Edit;
    $this->template->title = I18n::translate('Edit page');
    $id = $this->request->param('id');
    $this->template->model = ORM::factory('Page', $id);
    if ( ! $this->template->model->loaded())
    {
      $this->redirect('error/404');
    }
    $this->_edit($this->template->model);
  }

  /**
   * Draft index
   **/
  public function action_drafts()
  {
    $this->template = new View_Index;
    $this->template->title = I18n::translate('Page drafts');
    $this->template->show_date = FALSE;
    $this->template->items = ORM::factory('Page')
      ->where('is_draft', '=', '1')
      ->order_by('name', 'DESC')
      ->find_all();
  }
}
