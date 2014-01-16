<?php defined('SYSPATH') or die('No direct script access.');

/**
 * User controller.
 * Sign in and edit password.
 **/
class Controller_User extends Controller_Layout {
  public function action_view()
  {
    $this->redirect('');
  }
  public function action_signin()
  {
    if (Auth::instance()->logged_in())
    {
      $this->redirect('post/index');
    }
    $this->template = new View_Edit;
    $this->template->title = __('User login');
    $this->template->errors = array();
    $this->template->custom_controls = array(
      'username' => array(
        'type' => 'input',
        'label' => __('Username'),
        'value' => ''
      ),
      'password' => array(
        'type' => 'password',
        'label' => __('Password'),
        'value' => ''
      ),
    );
    if (HTTP_Request::POST == $this->request->method()) {
      $validation = Validation::factory($this->request->post())
        ->rules('username', array(
          array('not_empty'),
          array('max_length', array(':value', 32))
        ))
        ->rule('password', 'not_empty');
      if ($validation->check()) {
        if (Auth::instance()->login( $this->request->post('username'), $this->request->post('password'), true))
        {
          $this->redirect('page/index');
        }
        else
        {
          array_push($this->template->errors, __('Authorization error. Please check user login and password.'));
        }
      }
      else
      {
        $this->template->errors = $validation->errors('default');
      }
    }
  }
}
