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
    if ( ! Model_Course::exists($id))
    {
      $this->redirect('error/500');
    }
    $this->template->model = ORM::factory('Letter');
    $this->template->model->course_id = $id;
    $this->template->title = I18n::translate('New letter');
    $this->_edit($this->template->model);
  }

  public function action_edit()
  {
    $this->template = new View_Edit;
    $this->template->title = I18n::translate('Letter editing');
    $id = $this->request->param('id');
    $model = ORM::factory('Letter', $id);
    if ( ! $model->loaded())
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
    if ( ! $model->loaded())
    {
      $this->redirect('error/404');
    }
    $this->template->title = I18n::translate('Delete letter');
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
    if ( ! $model->loaded())
    {
      $this->redirect('error/404');
    }
    Debugtoolbar::disable();
    $this->template = new View_Letter_View;
    $this->template->content = $model->text;
    $this->template->subject = $model->subject;
    $this->template->id = $model->id;
  }

  public function action_unsubscribe()
  {
    $id = $this->request->param('id');
    $letter = ORM::factory('Letter', $id);
    $token = $this->request->query('token');
    $email = $this->request->query('email');
    if ( ! $letter->loaded() OR empty($token) OR empty($email))
    {
      $this->redirect('error/500');
    }
    $client = ORM::factory('Client')->where('email', '=', $email)->and_where('token', '=', $token)->find();
    $course = ORM::factory('Course', $letter->course_id);
    if ( ! $client->loaded() OR ! $course->loaded())
    {
      Session::instance()->set_once('flash_error', I18n::translate('Client not found. Possible subscription token problem.'));
      $this->redirect('error/403');
    }
    // token is valid, client is found, letter is found - we can unsubscribe him now
    $course->remove('clients', $client);
    Log::instance()->add(Log::NOTICE, 'Client '.$client->name.' has been unsubscribed from course '.$course->title);
    Model_Task::cancel($client->id, $course->id);
    $this->template = new View_Message;
    $this->template->message = I18n::translate('You have been successfully unsubscribed from course %s', array('%s' => $course->title));
  }
  public function action_send()
  {
    $this->template = new View_Message;
    $this->template->title = I18n::translate('Send instant to all subscribers');
    $id = $this->request->param('id');
    $model = ORM::factory('Letter', $id);
    if ( ! $model->loaded())
    {
      $this->redirect('error/404');
    }
    $subscription = ORM::factory('Course', $model->course_id)->with('clients');
    $clients = $subscription->get_clients();
    if (empty($clients))
    {
      $this->template->message = I18n::translate('No clients to send to!');
    }
    else
    {
      foreach ($clients as $client)
      {
        $model->send($client);
      }
      $model->save();
      $this->template->message = I18n::translate('The instant has been sent.');
    }
  }
}
