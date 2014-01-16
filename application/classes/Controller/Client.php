<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Client controller.
 **/
class Controller_Client extends Controller_Layout {
  /**
   * Manually add a client.
   **/
  public function action_add()
  {
  }

  /**
   * Search a client.
   **/
  public function action_search()
  {
    $this->template = new View_Client_Search;
  }
}
