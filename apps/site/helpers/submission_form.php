<?php // Contact form Helper

ipsCore::requires_core_helper('form_builder');
ipsCore::requires_helper('fields_helper', 'admin');

class submission_form extends ipsCore_helper
{
    protected $form_name;
    protected $form_module;
    protected $form_action;
    protected $module_fields;

    protected $send_to;
    protected $success_message = 'Your submission has been made successfully';

    // Construct
    public function __construct($form_module, $form_action = false)
    {
        $this->form_name = $form_module;
        $this->form_action = ($form_action ?: '/contact/process/');

        if ($this->set_form_module()) {
            $this->set_module_fields();
        } else {
            $this->form_module = false;
        }

        parent::__construct($this->form_name, true);
    }

    // Setters
    public function set_form_module() {
        $this->load_model('site', 'form_module', 'admin_module');
        if ($this->form_module->retrieve(['dbslug' => $this->form_name])) {
            return true;
        }
        return false;
    }

    public function set_module_fields() {
        $this->load_model('site', 'module_fields', 'admin_module_field');
        $this->module_fields = $this->module_fields->where(['mid' => $this->form_module->mid])->order('position', 'ASC')->get_all();
    }

    public function set_send_to($addresses) {
        $this->send_to = $addresses;
    }

    public function set_success_message($message) {
        $this->success_message = $message;
    }

    // Getters
    public function get_form_module() {
        return $this->form_module;
    }

    public function get_module_fields() {
        return $this->module_fields;
    }

    // Methods
    private function form() {
        if ($this->get_form_module()) {
            $form = new ipsCore_form_builder($this->form_name);
            $form->set_action($this->form_action);
            $form->set_classes(['ajax_form']);

            $form->start_section('form_main');

            $output_fields_helper = new fields_helper();
            $output_fields_helper->output_fields($form, 'ipsCore_form_builder', $this->get_module_fields(), $this->get_form_module()->mid);

            $form->end_section('form_main');
            $form->start_section('submit');
            $form->add_submit('send', 'Submit');
            $form->end_section('submit');

            return $form;
        }
    }

    public function render() {
        return $this->form()->render();
    }

    public function process_form($success_message = false, $send_to = false) {
        $errors = [];

        $form = $this->form();

        $form->populate_form();
        $form->validate_form($errors);

        if (empty($errors)) {
            $submission = $this->get_model('site', 'submission', $this->get_form_module()->dbslug);
            $content = $this->get_form_module()->title_single . ' Contents:<br />';
            $submission->created = time();
            $submission->modified = $submission->created;

            foreach($this->get_module_fields() as $field) {
                $form_value = $form->get_field_value($field->dbslug);
                $content .= '<br />' . $field->title . ': ' . $form_value;
                $submission->{$field->dbslug} = $form_value;
            }

            $submission->title = $submission->name . ' (' . $submission->email_address . ')';

            if ($submission->save()) {
                if (!$success_message) {
                    $success_message = $this->success_message;
                }
                if (!$send_to) {
                    $send_to = $this->send_to;
                }

                if ($this->send_to) {
                    if (ipsCore::$mailer->send($send_to, 'New Contact Form Submission', $content)) {
                        $json_data = ['success' => $success_message];
                    } else {
                        $errors[] = 'Failed to send your submission, please try again or contact an administrator for assistance.';
                    }
                } else {
                    $json_data = ['success' => $success_message];
                }
            } else {
                $errors[] = 'Failed to save your submission, please try again or contact an administrator for assistance.';
            }
        }

        if (!empty($errors)) {
            $json_data = ['success' => false, 'errors' => $errors];
        }

        $this->add_data(['json' => $json_data]);

        $this->build_view([/*'layout' => false, */'json' => true]);
    }
}
