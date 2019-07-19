<?php // Site admin home controller

ipsCore::requires_controller('controller');
ipsCore::requires_model('user');

class users_controller extends admin_controller
{
    protected static $permission_level = 3;

	// Construct
	public function __construct($controller, $additional = false)
	{
		parent::__construct($controller, $additional);

		$this->load_model('admin_user', 'user');

		$this->set_page_title('Manage Users');
	}

	// Methods
	public function index()
	{
		$this->get_layout();
		$this->set_breadcrumbs(['Dashboard' => '/admin', 'Users' => false]);

		$user_items = $this->user->get_all();
		$users = [];

		foreach ($user_items as $user) {
		    $users[] =[
                'uid' => $user->get_id(),
                'username' => $user->username,
                'email' => $user->email,
                'level' => $this->user->get_level_name($user->level),
                'edit' => ($user->level >= $this->currentuser->get_user_level() ? true : false),
            ];
        }

		$this->add_data([
			'title' => 'Manage Users',
			'content' => 'Here you can create and manage users of your CMS.',
			'create_user_href' => '/admin/users/create',
			'create_user_text' => 'Create new user',
			'manage_user_href' => '/admin/users/manage/',
			'manage_user_text' => 'Manage User',
			'users' => $users,
		]);

		$this->build_view();
	}

	private function user_form()
	{
		$form = new admin_form_builder('user_form');
		$form->set_action('/admin/users/process/' . ($this->user->get_id() ? $this->user->get_id() . '/' : ''));
		$form->set_classes(['ajax_form', 'main_form', 'edit']);
		$form->start_section('form_main');

		$form->add_text('username', 'Username', ['value' => $this->user->username, 'required' => true, 'placeholder' => 'Enter Username']);
		$user_level_options = [];
		foreach ($this->user->get_user_levels() as $user_level => $user_level_title) {
			$user_level_options[] = ['value' => $user_level, 'text' => $user_level_title];
		}
		$form->add_select('level', 'Level', ['options' => $user_level_options, 'value' => $this->user->level, 'required' => true, 'placeholder' => 'Choose User Level']);
		$form->add_email('email', 'Email Address', ['value' => $this->user->email, 'required' => true, 'placeholder' => 'Enter Email Address']);
		$form->add_password('password', ($this->user->get_id() ? 'New ' : '') . 'Password', ['placeholder' => 'Enter Password', 'required' => ($this->user->get_id() ? false : true)]);
		$form->end_section('form_main');
		$form->start_section('submit');
		if ( $this->user->get_id() ) {
			$form->add_html('add_another', '<a id="add-another" class="button" href="/admin/users/create/"><i class="fas fa-plus-square"></i>Add Another</a>');
		}
		$form->add_submit('modify', '<i class="far fa-save"></i>' . ($this->user->get_id() ? 'Save' : 'Create') . ' User');
		$form->end_section('submit');
		return $form;
	}

	public function create()
	{
		$this->get_layout();

		$this->set_breadcrumbs(['Dashboard' => '/admin', 'Users' => '/admin/users', 'Create User' => false]);

		$this->add_data([
			'title' => 'Create New User',
			'form' => $this->user_form()->render()
		]);

        $this->set_view('users/user');
		$this->build_view();
	}

	public function manage($user_id)
	{
		$this->get_layout();
		$this->set_breadcrumbs(['Dashboard' => '/admin', 'Users' => '/admin/users', 'Manage User' => false]);

		if ($this->user->retrieve($user_id)) {
            if ($this->user->level >= $this->currentuser->get_user_level()) {
                $return = $this->user_form()->render();
            } else {
                $this->add_flash('You do not have permission to edit a user of higher level than yourself.', 'error');
                $this->log('User Permission issue', 'User tried edit a user of higher level than themselves. - user_id: ' . $user_id);
                ipsCore::$functions->redirect('/admin/users');
                $return = false;
            }
		} else {
			$return = 'Could not find specified user';
		}

		$this->add_data([
			'title' => 'Manage User',
			'remove_form' => $this->remove_user_form($user_id),
			'form' => $return
		]);

        $this->set_view('users/user');
		$this->build_view();
	}

	public function process($user_id = false)
	{
		$errors = [];

		if (!$user_id || ($user_id && $this->user->retrieve($user_id))) {

            $form = $this->user_form();
            $form->populate_form();

		    if ($user_id) {
                $user_level = $this->user->level;
            } else {
                $user_level = $form->get_field_value('level');
            }

            if ($user_level >= $this->currentuser->get_user_level()) {

                if (empty($form->get_field_value('password'))) {
                    $form->remove_field('password');
                }

                $form->validate_form($errors);

                if (empty($errors)) {
                    $this->user->username = $form->get_field_value('username');
                    $this->user->level = $form->get_field_value('level');
                    $this->user->email = $form->get_field_value('email');
                    if (!$user_id) {
                        $this->user->salt = $this->user->generate_salt();
                    }
                    if (!empty($form->get_field_value('password'))) {
                        $this->user->password = $this->user->hash_password($form->get_field_value('password'));
                    }

                    if ($this->user->save()) {
                        if ($user_id) {
                            $this->log('Modified User', 'User ID: ' . $this->user->get_id());
                            $json_data = ['success' => 'User has been successfully modified!'];
                        } else {
                            $this->log('Created User', 'User ID: ' . $this->user->get_id());
                            $json_data = ['redirect' => '/admin/users/user/' . $this->user->get_id()];
                            $this->add_flash('User Created Successfully!');
                        }
                    } else {
                        $errors[] = 'Failed to Save User.';
                    }
                }
            } else {
                $this->log('User Process Failed (Permission issue)', 'User now allowed to edit a user of higher level than themselves. - user_id: ' . $user_id);
                $errors[] = 'You do not have permission to edit a user of higher level than yourself.';
            }
		} else {
			$this->log('User Process Failed', 'Failed to find User to Save - user_id: ' . $user_id);
			$errors[] = 'Failed to find User to Save.';
		}

		if (!empty($errors)) {
			$json_data = ['success' => false, 'errors' => $errors];
		}

		$this->add_data(['json' => $json_data]);

		$this->build_view([/*'layout' => false, */'json' => true]);
	}

	public function remove_user_form($user_id)
	{
        $form = new ipsCore_form_builder('remove_form');
        $form->set_action('/admin/users/remove_user_popup/' . $user_id);
        $form->set_classes(['ajax_form', 'remove']);
        $form->add_submit('remove', '<i class="far fa-trash-alt"></i>Remove User');

        return $form->render();
	}

	public function remove_user_popup($user_id)
	{
        $confirm_form = new ipsCore_form_builder('confirm_remove_form');
        $confirm_form->set_action('/admin/users/remove_user_process/' . $user_id);
        $confirm_form->add_submit('confirm', 'Remove User');

        $this->add_data([
            'message' => 'Are you sure you want to remove the User? This is a permanent action that cannot be reversed.',
            'confirm_form' => $confirm_form->render(),
        ]);

        $this->set_view('parts/confirm_popup');
        $this->build_view(['json' => true]);
	}

	public function remove_user_process($user_id)
	{
        $errors = [];

        if ($user_id && $this->user->retrieve($user_id)) {

            if ($this->user->remove()) {
                $json_data = ['redirect' => '/admin/configuration/manage'];
                $this->add_flash('User Successfully Removed');
                $this->log('Removed User', 'Removed user - user_id: ' . $user_id);
            } else {
                $this->log('Failed to remove User', 'Failed to remove user - user_id: ' . $user_id);
                $errors[] = 'Failed to remove user.';
            }
        } else {
            $this->log('Failed to remove User', 'Failed to find user to remove - user_id: ' . $user_id);
            $errors[] = 'Failed to remove user.';
        }

        if (!empty($errors)) {
            $json_data = ['success' => false, 'errors' => $errors];
        }

        $this->add_data(['json' => $json_data]);

        $this->build_view(['json' => true]);
	}

}
