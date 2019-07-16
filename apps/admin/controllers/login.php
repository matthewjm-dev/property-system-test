<?php // Site admin home controller

ipsCore::requires_controller('controller');
ipsCore::requires_model(['user']);

class login_controller extends admin_controller
{
    // Construct
    public function __construct($controller)
    {
        parent::__construct($controller);

        $this->load_model('admin_user', 'currentuser');

        $this->set_page_title('Admin Login');

        $this->get_header_logo();
    }

    // Methods
    public function index()
    {

        if ($this->currentuser->is_logged_in()) {
            ipsCore::$functions->redirect('admin');
        } else {
            //$salt = '';
            //$password = '';
            //die( hash( 'sha512', hash( 'sha512', $password ) . $salt ) );

            $form = new ipsCore_form_builder('login_form');
            $form->set_action('/admin/login/process/');
            $form->add_text('username', false, ['required' => true, 'placeholder' => 'Username']);
            $form->add_password('password', false, ['required' => true, 'placeholder' => 'Password']);

            if (ipsCore::$uri_get) {
                $redirect = [];
                parse_str(ipsCore::$uri_get, $redirect);
                if (isset($redirect['redir'])) {
                    $form->add_hidden('redir', ['value' => $redirect['redir']]);
                }
            }

            $form->add_submit('login', 'Login');

            $this->add_data([
                'content' => 'Please login to continue to the admin control panel.',
                'login_form' => $form->render(),
                'forgotten_password' => 'Forgotten your password? <a href="/admin/login/forgotten/">Click Here</a>',
            ]);

            $this->build_view(['layout' => 'login']);
        }
    }

    public function process()
    {
        $errors = [];

        $submitted_username = $_REQUEST['username'];
        $submitted_password = $_REQUEST['password'];
        $submitted_redir = (isset($_REQUEST['redir']) ? $_REQUEST['redir'] : false);

        $this->currentuser->where(['username' => $submitted_username])->retrieve();

        if ($this->currentuser->{$this->currentuser->get_pkey()} !== false) {
            $password = $this->currentuser->hash_password($submitted_password);

            if ($password != $this->currentuser->password) {
                $this->log('Login: Incorrect password', 'User: ' . $submitted_username, $this->currentuser->uid);
                $errors['password'] = 'Password Incorrect';
            }
        } else {
            $errors['username'] = 'User "' . $submitted_username . '" does not exist';
        }

        if (empty($errors)) {
            $this->currentuser->do_login();

            $redir = '/admin';
            if ($submitted_redir) {
                $redir = $submitted_redir;
            }

            $this->log('Login: Successful', 'User: ' . $submitted_username);
            $json_data = ['redirect' => $redir];
            $this->add_flash('Logged in successfully!');
        } else {
            $json_data = ['success' => false, 'errors' => $errors];
        }

        $this->add_data(['json' => $json_data]);

        $this->build_view([/*'layout' => false, */'json' => true]);
    }

    public function logout()
    {
        $this->add_data([
            'header_logo_src' => '/img/admin/logo-alt-small.png',
            'header_logo_title' => 'In-Phase Solutions Content Management System',
            'header_logo_cmsver' => 'CMS v1.00',
            'content' => 'You have been successfully logged out.',
            'btn_login_text' => 'Return to Login',
            'btn_login_href' => '/admin/login/',
            'btn_home_text' => 'Return to homepage',
            'btn_home_href' => '/',
        ]);

        $this->currentuser->do_logout();

        $this->build_view(['layout' => 'login']);
    }

    private function forgotten_form()
    {
        $form = new ipsCore_form_builder('forgotten_form');
        $form->set_action('/admin/login/forgotten_process');
        $form->set_classes(['ajax_form']);

        $form->add_text('username', false, ['placeholder' => 'Username']);
        $form->add_html('or_message', '<p>or</p>');
        $form->add_email('email', false, ['placeholder' => 'Email']);
        $form->add_submit('submit', 'Reset Password');

        return $form;
    }

    public function forgotten()
    {
        $this->add_data([
            'content' => 'Reset your forgotten password by either entering your username or email address below.',
            'forgotten_form' => $this->forgotten_form()->render(),
            'forgotten_message' => 'If you are still having trouble accessing your account, please contact an administrator for help.',
        ]);

        $this->build_view(['layout' => 'login']);
    }

    public function forgotten_process()
    {
        $errors = [];
        $where = false;

        $form = $this->forgotten_form();
        $form->populate_form();

        if (!empty($form->get_field_value('email'))) {
            $where = ['email' => $form->get_field_value('email')];
        } else {
            $form->remove_field('email');
            if (!empty($form->get_field_value('username'))) {
                $where = ['username' => $form->get_field_value('username')];
            } else {
                $errors[] = 'You must enter either your email address or username to reset your password';
            }
        }

        if ($where) {
            $form->validate_form($errors);

            if (empty($errors)) {
                if ($this->currentuser->retrieve($where)) {
                    $reset_key = $this->currentuser->hash_password($this->currentuser->username . time());
                    $this->currentuser->reset = $reset_key;

                    if ($this->currentuser->save()) {
                        $email_template = ipsCore::get_part('login/forgotten_email', [
                            'username' => $this->currentuser->username,
                            'reset_url' => ipsCore::$site_base . 'admin/login/reset/' . $reset_key,
                        ]);

                        if (ipsCore::$mailer->send($this->currentuser->email, 'Reset your ipsCMS password', $email_template)) {
                            $json_data = ['redirect' => '/admin/login/forgotten_submitted'];
                        } else {
                            $errors[] = 'Failed to send reset email, please try again or contact an administrator for assistance.';
                        }
                    } else {
                        $errors[] = 'Failed to save reset key, please contact an administrator for assistance.';
                    }
                } else {
                    $errors[] = 'Sorry, no account was found matching those details.';
                }
            }
        }

        if (!empty($errors)) {
            $json_data = ['success' => false, 'errors' => $errors];
        }

        $this->add_data(['json' => $json_data]);

        $this->build_view([/*'layout' => false, */'json' => true]);
    }

    public function forgotten_submitted()
    {
        $this->add_data([
            'content' => 'If that email address or username exists on the system, an email will have been sent containing further details on resetting your password.',
            'btn_login_text' => 'Return to Login',
            'btn_login_href' => '/admin/login/',
            'btn_home_text' => 'Return to homepage',
            'btn_home_href' => '/',
        ]);

        $this->set_view('login/logout');
        $this->build_view(['layout' => 'login']);
    }

    private function reset_form($key = '')
    {
        $form = new ipsCore_form_builder('reset_form');
        $form->set_action('/admin/login/reset_process/');
        $form->set_classes(['ajax_form']);

        $form->add_password('password1', false, ['placeholder' => 'Enter Password']);
        $form->add_password('password2', false, ['placeholder' => 'Enter Password Again']);
        $form->add_hidden('reset_key', ['value' => $key]);
        $form->add_submit('submit', 'Reset Password');

        return $form;
    }

    public function reset($key = false)
    {
        $form = '';
        $buttons = [
            'btn_login_text' => 'Return to Login',
            'btn_login_href' => '/admin/login/',
            'btn_forgotten_text' => 'Reset Password Again',
            'btn_forgotten_href' => '/admin/login/forgotten/',
        ];

        if ($key) {
            if ($this->currentuser->retrieve(['reset' => $key])) {
                $content = 'Hello ' . $this->currentuser->username . ', enter your new password below.';
                $form = $this->reset_form($key)->render();
                $buttons = [];
            } else {
                $content = 'Sorry, that reset key is invalid or has expired. Please try submitting a new request to reset your password.';
            }
        } else {
            $content = 'Reset key not present, please try the link you received by email again.';
        }

        $this->add_data(array_merge([
            'content' => $content,
            'forgotten_form' => $form,
            'forgotten_message' => 'If you are still having trouble accessing your account, please contact an administrator for help.',
        ], $buttons));

        $this->build_view(['layout' => 'login']);
    }

    public function reset_process()
    {
        $errors = [];

        $form = $this->reset_form();
        $form->populate_form();
        $form->validate_form($errors);

        if (empty($errors)) {
            $password1 = $form->get_field_value('password1');
            $password2 = $form->get_field_value('password2');

            if ($password1 == $password2) {
                if ($this->currentuser->retrieve(['reset' => $form->get_field_value('reset_key')])) {
                    $this->currentuser->reset = '';
                    $this->currentuser->password = $this->currentuser->hash_password($password1);

                    if ($this->currentuser->save()) {
                        $json_data = ['redirect' => '/admin/login/'];
                        $this->log('Password Reset: Successful', 'User: ' . $this->currentuser->username);
                        $this->add_flash('Password Reset successfully!');
                    } else {
                        $errors[] = 'Unfortunately there was a problem resetting your password, please try again or contact an administrator for assistance.';
                    }
                } else {
                    $errors[] = 'Sorry, that reset key is invalid or has expired. Please try submitting a new request to reset your password.';
                }
            } else {
                $errors[] = 'Passwords do not match.';
            }
        }

        if (!empty($errors)) {
            $json_data = ['success' => false, 'errors' => $errors];
        }

        $this->add_data(['json' => $json_data]);

        $this->build_view([/*'layout' => true, */'json' => true]);
    }
}
