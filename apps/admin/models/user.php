<?php // Site admin users model

ipsCore::requires_model('model');

class admin_user_model extends admin_model
{

    protected static $user_levels = [1 => 'Developer', 2 => 'Admin', 3 => 'Editor', 4 => 'User'];

    // Construct
    public function __construct($name, $table)
    {
        parent::__construct($name, $table);
    }

    // Methods
    public static function get_user_levels()
    {
        return self::$user_levels;
    }

    public function do_login()
    {
        ipsCore::$session->write('logged_in', true);

        $user_details = [];
        foreach ($this->fields as $field_key => $field) {
            $user_details[$field_key] = $this->{$field_key};
        }
        ipsCore::$session->write('user_details', $user_details);
    }

    public function is_logged_in()
    {
        if (ipsCore::$session->read('logged_in') === true) {
            return true;
        }

        return false;
    }

    public function do_logout()
    {
        ipsCore::$session->write('logged_in', false);
        ipsCore::$session->write('user_details', false);
    }

    public function get_user_level()
    {
        if (isset(ipsCore::$session->read('user_details')['level'])) {
            return ipsCore::$session->read('user_details')['level'];
        }

        return 0;
    }

    public function is_user_level($level)
    {
        if ($this->get_user_level() <= $level) {
            return true;
        }

        return false;
    }

    public function get_level_name($level)
    {
        if (isset(self::get_user_levels()[$level])) {
            return self::get_user_levels()[$level];
        }

        return 'Invalid User Level';
    }

    public function is_developer()
    {
        return $this->is_user_level(1);
    }

    public function hash_password($password)
    {
        return hash('sha512', hash('sha512', $password) . $this->salt);
    }

    public function generate_salt()
    {
        return md5(time());
    }

}
