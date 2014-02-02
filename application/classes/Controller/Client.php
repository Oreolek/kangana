<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Client controller.
 **/
class Controller_Client extends Controller_Layout {
  protected $secure_actions = array(
    'index','create', 'edit', 'delete', 'view'
  );
  public function action_index()
  {
    $this->template = new View_Client_Index;
    $this->template->title = __('Clients');
    $this->template->items = ORM::factory('Client')
      ->filter_by_page($this->request->param('page'))
      ->find_all();
  }

  /**
   * Manually add a client.
   **/
  public function action_create()
  {
  }

  /**
   * Search a client.
   **/
  public function action_search()
  {
    $this->template = new View_Client_Search;
  }

  public function action_subscribe()
  {
  }
  
  public function action_unsubscribe()
  {
  }
}
