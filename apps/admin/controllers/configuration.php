<?php // Site admin config controller

ipsCore::requires_controller('controller');
ipsCore::requires_model(['config_group', 'config_field']);

class configuration_controller extends admin_controller
{
    protected static $permission_level = 2;

    // Construct
    public function __construct($controller, $additional = false)
    {
        parent::__construct($controller, $additional);

        $this->load_model('admin_config_group', 'config_group');
        $this->load_model('admin_config_field', 'config_field');

        $this->set_page_title('Admin Configuration Page');
    }

    // Methods
    private function configuration_form($config_field_items = false)
    {
        if ($this->config_group->get_id()) {
            $form = new admin_form_builder('modify_module_form');
            $form->set_action('/admin/configuration/process/' . $this->config_group->get_id() . '/');
            $form->set_classes(['ajax_form', 'main_form', 'edit']);
            $form->start_section('form_main');

            if ($config_field_items) {
                foreach ($config_field_items as $field) {

                    if ($field_type = admin_form_builder::get_field_types($field->type)) {
                        $options = [];
                        if (isset($field_type['link'])) {
                            /*if ($field_type['link']) {
                                $this->load_model('admin_module', 'link_module');
                                $this->link_module->retrieve($field->link);

                                $this->load_model('admin', 'link_items', $this->link_module->dbslug);
                                $items = $this->link_items->get_all();

                                // Module link field
                                $link_field_title = false;
                                $link_field = $this->field->get(['mfid' => $field->link_field]);
                                if ($link_field !== false && $link_field->dbslug !== null) {
                                    $link_field_title = $link_field->dbslug;
                                }

                                if (!empty($items)) {
                                    foreach ($items as $item) {
                                        $title = ($link_field_title && $item->{$link_field_title} ? $item->{$link_field_title} : $item->get_id());
                                        $options[] = [
                                            'text' => $title,
                                            'value' => $item->get_id(),
                                        ];
                                    }
                                }
                            } else {*/
                            $options = $form->show_field_options($field->options);
                            //}
                        }

                        $form->add_field($field->dbslug, $field->title . ' (' . $field->dbslug . ')', $field->type, [
                            // TODO: add default value?
                            'value' => ($field->value ?: ''),
                            'options' => $options,
                            'required' => $field->required,
                        ]);

                    }
                }
            }

            $form->end_section('form_main');
            $form->start_section('submit');
            $form->add_submit('modify', '<i class="far fa-save"></i>Save Changes');
            $form->end_section('submit');

            return $form;
        }
        ipsCore::add_error('Cant build config form without group');

        return false;
    }

    public function index($group_id = false)
    {
        $this->get_layout();

        $manage_link = '';
        if ($this->currentuser->is_developer()) {
            $manage_link = '<a id="manage-config" class="button" href="/admin/configuration/manage/"><i class="fas fa-plus-square"></i> <i class="far fa-edit"></i> Manage Groups & Fields</a>';
        }

        if ($group_id !== false) {
            $this->config_group->where_has_permission()->retrieve($group_id);
        } else {
            $this->config_group->where_has_permission()->retrieve(['position' => 0]);
            $group_id = $this->config_group->get_id();
        }

        $groups = [];
        $groups_items = $this->config_group->reset()->where_has_permission()->get_all();
        if (!empty($groups_items)) {
            foreach ($groups_items as $group_item) {
                $groups[] = [
                    'href' => '/admin/configuration/' . $group_item->get_id(),
                    'title' => $group_item->title,
                    'icon' => $group_item->icon,
                    'current' => ($group_item->get_id() == $group_id ? true : false),
                ];
            }
        }

        if ($this->config_group) {

            $this->set_breadcrumbs(['Dashboard' => '/admin', 'Configuration' => '/admin/configuration/', $this->config_group->title => false]);

            $title = $this->config_group->title;

            if ($config_field_items = $this->config_field->where([$this->config_group->get_pkey() => $this->config_group->get_id()])->get_all()) {
                $form = $this->configuration_form($config_field_items);

                $content = '';
                $return = $form->render();
            }

            $this->add_data([
                'title' => 'Configuration - ' . $title,
                'content' => (isset($content) ? $content : 'No configuration fields exist for this group yet.'),
                'manage_link' => $manage_link,
                'groups' => $groups,
                'form' => (isset($return) ? $return : ''),
            ]);

            $this->build_view();
        } else {
            $this->get_layout();
            $this->set_breadcrumbs(['Dashboard' => '/admin', 'Configuration' => false]);

            $this->add_data([
                'manage_link' => $manage_link,
                'title' => 'Configuration Group Not Found',
                'content' => 'Sorry, we could not locate that configuration group.',
                'groups' => $groups,
            ]);

            $this->build_view();
        }
    }

    public function process($group_id)
    {
        $errors = [];

        if ($this->config_group->where_has_permission()->retrieve($group_id)) {

            if ($config_field_items = $this->config_field->where([$this->config_group->get_pkey() => $this->config_group->get_id()])->get_all()) {
                $form = $this->configuration_form($config_field_items);
                $form->populate_form();
                $form->validate_form($errors);
            } else {
                $errors[] = 'Failed to retrieve configuration fields';
            }
        } else {
            $errors[] = 'Failed to retrieve configuration group';
        }

        if (empty($errors)) {

            foreach ($config_field_items as $field) {
                $field->value = $form->get_field_value($field->dbslug);
                if (!$field->save()) {
                    $error[$field['dbslug']] = 'Failed to save field.';
                }
            }

            $this->log('Updated Configuration', 'Configuration has been successfully updated!');
            $json_data = ['success' => 'Configuration has been successfully updated!'];
        } else {
            $json_data = ['success' => false, 'errors' => $errors];
        }

        $this->add_data(['json' => $json_data]);

        $this->build_view(['json' => true]);
    }

    public function manage($method = false, $id = false)
    {
        if ($this->currentuser->is_developer()) {
            $this->get_layout();

            if ($method == 'creategroup') {
                $this->creategroup();
            } elseif ($method == 'managegroup' && $id != false) {
                $this->managegroup($id);
            } elseif ($method == 'createfield' && $id != false) {
                $this->createfield($id);
            } elseif ($method == 'managefield' && $id != false) {
                $this->managefield($id);
            } else {
                $this->set_breadcrumbs(['Dashboard' => '/admin', 'Configuration' => '/admin/configuration/', 'Manage Groups & Fields' => false]);

                $groups = [];
                $groups_items = $this->config_group->get_all();
                if (!empty($groups_items)) {
                    foreach ($groups_items as $group_item) {
                        $groups[] = [
                            'manage_group_href' => '/admin/configuration/manage/managegroup/' . $group_item->get_id(),
                            'title' => $group_item->title,
                        ];
                    }
                }

                $this->add_data([
                    'title' => 'Manage Configuration Groups & Fields',
                    'content' => 'Drag and drop groups to set their order / position',
                    'create_group_link' => '/admin/configuration/manage/creategroup/',
                    'groups' => $groups,
                ]);
            }

            $this->build_view();
        } else {
            ipsCore::$functions->redirect('/');
        }
    }

    private function creategroup()
    {
        $this->set_breadcrumbs(['Dashboard' => '/admin', 'Configuration' => '/admin/configuration/', 'Manage' => '/admin/configuration/manage/', 'Create Group' => false]);

        $form = $this->group_form();

        $this->add_data([
            'title' => 'Create Configuration Group',
            'form' => $form->render(),
        ]);

        $this->set_view('/configuration/manage_group');
    }

    private function managegroup($group_id)
    {
        $this->set_breadcrumbs(['Dashboard' => '/admin', 'Configuration' => '/admin/configuration/', 'Manage' => '/admin/configuration/manage/', 'Manage Group' => false]);

        if ($this->config_group->retrieve($group_id)) {

            $form = $this->group_form();
            $return = $form->render();

            $fields = [];
            if ($config_fields = $this->config_field->where([$this->config_group->get_pkey() => $group_id])->get_all()) {
                foreach ($config_fields as $config_field) {
                    $fields[] = [
                        'title' => $config_field->title,
                        'dbslug' => $config_field->dbslug,
                        'type' => $config_field->type,
                        'manage_config_field_href' => '/admin/configuration/manage/managefield/' . $config_field->get_id(),
                    ];
                }
            }

            $create_link = '/admin/configuration/manage/createfield/' . $group_id;
        } else {
            $return = 'Group not found';
            $fields = false;
            $create_link = '';
        }

        $this->add_data([
            'title' => 'Manage Configuration Group',
            'form' => $return,
            'remove_form' => $this->remove_group_form($group_id),
            'create_config_field_href' => $create_link,
            'fields' => $fields,
        ]);

        $this->set_view('/configuration/manage_group');
    }

    private function group_form()
    {
        $form = new admin_form_builder('config_group_form');
        $form->set_action('/admin/configuration/group_process/' . ($this->config_group->get_id() ? $this->config_group->get_id() . '/' : ''));
        $form->set_classes(['ajax_form', 'main_form', 'edit']);
        $form->start_section('form_main');

        $form->add_text('title', 'Title', ['value' => ($this->config_group->title ?: ''), 'required' => true, 'placeholder' => 'Enter Group Title']);

        $permission_options = [];
        foreach(admin_user_model::get_user_levels() as $user_level_key => $user_level_name) {
            $permission_options[] = [
                'text' => $user_level_name,
                'value' => $user_level_key,
            ];
        }
        $form->add_select('level', 'Permissions Level', ['value' => ($this->config_group->level ?: ''), 'options' => $permission_options/*[['value' => 1, 'text' => 'Admin'], ['value' => 2, 'text' => 'Developer']]*/, 'required' => true, 'placeholder' => 'Choose Group permissions', 'default' => 1]);
        $form->add_text('icon', 'FA Icon', ['value' => ($this->config_group->icon ?: ''), 'placeholder' => 'Enter Font Awesome Icon class', 'comment' => 'View available FA icons by <a target="_blank" href="https://fontawesome.com/icons?d=gallery">clicking here.</a>']);

        $form->end_section('form_main');
        $form->start_section('submit');
        $form->add_submit('modify', '<i class="far fa-save"></i>' . ($this->config_group->get_id() ? 'Save' : 'Create') . ' Group');
        $form->end_section('submit');

        return $form;
    }

    public function group_process($group_id = false)
    {
        if ($this->currentuser->is_developer()) {

            $errors = [];

            $form = $this->group_form();
            $form->populate_form();
            $form->validate_form($errors);

            if (!$group_id || $group_id && $this->config_group->retrieve($group_id)) {

                if (empty($errors)) {
                    $this->config_group->title = $form->get_field_value('title');
                    $this->config_group->level = $form->get_field_value('level');
                    $this->config_group->icon = $form->get_field_value('icon');
                    $this->config_group->position = 0;

                    if ($this->config_group->save()) {
                        $this->log('Modified Configuration Group', 'Group ID: ' . $this->config_group->get_id());
                        if ($group_id) {
                            $json_data = ['success' => 'Configuration Group has been successfully modified!'];
                        } else {
                            $json_data = ['redirect' => '/admin/configuration/manage/managegroup/' . $this->config_group->get_id()];
                            $this->add_flash('Configuration Group has been successfully added!');
                        }
                    } else {
                        $this->log('Failed to Save Configuration Group', 'Group Title: ' . $this->config_group->title);
                        $errors[] = 'Failed to Save Configuration Group.';
                    }
                }
            } else {
                $this->log('Configuration Group Process Failed', 'Failed to find Configuration Group to Save - group_id: ' . $group_id);
                $errors[] = 'Failed to find Configuration Group to Save.';
            }

            if (!empty($errors)) {
                $json_data = ['success' => false, 'errors' => $errors];
            }

            $this->add_data(['json' => $json_data]);

            $this->build_view([/*'layout' => false, */ 'json' => true]);
        } else {
            ipsCore::$functions->redirect('/');
        }
    }

    private function createfield($group_id)
    {
        if ($this->config_group->retrieve($group_id)) {
            $this->set_breadcrumbs(['Dashboard' => '/admin', 'Configuration' => '/admin/configuration/', 'Manage' => '/admin/configuration/manage/', $this->config_group->title => '/admin/configuration/manage/group' . $group_id, 'Create Field' => false]);

            $form = $this->field_form();
            $return = $form->render();
        } else {
            $this->set_breadcrumbs(['Dashboard' => '/admin', 'Configuration' => '/admin/configuration/', 'Manage' => '/admin/configuration/manage/', 'Create Field' => false]);
            $return = 'Group not found';
        }

        $this->add_data([
            'title' => 'Create Configuration Field',
            'form' => $return,
        ]);

        $this->set_view('/configuration/manage_field');
    }

    private function managefield($field_id)
    {
        if ($this->config_field->retrieve($field_id)) {
            if ($this->config_group->retrieve($this->config_field->{$this->config_group->get_pkey()})) {
                $this->set_breadcrumbs(['Dashboard' => '/admin', 'Configuration' => '/admin/configuration/', 'Manage' => '/admin/configuration/manage/', $this->config_group->title => '/admin/configuration/manage/group' . $this->config_group->get_id(), 'Manage Field' => false]);

                $form = $this->field_form();
                $return = $form->render();
            } else {
                $this->set_breadcrumbs(['Dashboard' => '/admin', 'Configuration' => '/admin/configuration/', 'Manage' => '/admin/configuration/manage/', 'Manage Field' => false]);
                $return = 'Group not found';
            }
        } else {
            $this->set_breadcrumbs(['Dashboard' => '/admin', 'Configuration' => '/admin/configuration/', 'Manage' => '/admin/configuration/manage/', 'Manage Field' => false]);
            $return = 'Field not found';
        }

        $this->add_data([
            'title' => 'Manage Configuration Field',
            'remove_form' => $this->remove_field_form($field_id),
            'form' => $return,
        ]);

        $this->set_view('/configuration/manage_field');
    }

    private function field_form()
    {
        $form = new admin_form_builder('config_group_form');
        $form->set_action('/admin/configuration/field_process/' . ($this->config_field->get_id() ? $this->config_field->get_id() : 'create/' . $this->config_group->get_id()) . '/');
        $form->set_classes(['ajax_form', 'main_form', 'edit']);
        $form->start_section('form_main');

        $form->add_text('title', 'Title', ['value' => ($this->config_field->title ?: ''), 'required' => true, 'placeholder' => 'Enter Field Title']);
        $form->add_text('dbslug', 'Database Slug', ['value' => ($this->config_field->dbslug ?: ''), 'required' => true, 'placeholder' => 'Enter unique DB Slug']);
        $form->add_select('type', 'Type', ['options' => $form->get_field_type_options($this->config_field->type), 'required' => true, 'placeholder' => 'Choose Field Type']);

        $show_options = 'hidden';
        if ($this->config_field->get_id()) {
            $field_type = admin_form_builder::get_field_types($this->config_field->type);
            if (isset($field_type['link'])) {
                $show_options = '';
            }
        }
        $form->add_textarea('options', 'Options', ['value' => $form->show_field_options_output($this->config_field->options), 'placeholder' => 'Enter Options', 'comment' => 'Enter Options on new lines like: "value : Name"', 'fieldset_classes' => $show_options]);

        $form->add_radio('required', 'Required Field', ['value' => $this->config_field->required, 'options' => [['text' => 'Yes', 'value' => 1], ['text' => 'No', 'value' => 0]], 'comment' => 'Choose if this field is required', 'default' => 0]);

        $form->end_section('form_main');
        $form->start_section('submit');
        $form->add_submit('modify', '<i class="far fa-save"></i>Save Changes');
        $form->end_section('submit');

        return $form;
    }

    public function field_process($field_id, $group_id = false)
    {
        if ($this->currentuser->is_developer()) {

            if ($group_id !== false) {
                $field_id = false;
            } else {
                $group_id = false;
            }

            $errors = [];

            if (!$field_id || $field_id && $this->config_field->retrieve($field_id)) {

                if (!$group_id) {
                    $group_id = $this->config_field->{$this->config_group->get_pkey()};
                }

                if ($this->config_group->retrieve($group_id)) {

                    $form = $this->field_form();
                    $form->populate_form();

                    if ($form->get_field_value('dbslug') != $this->config_field->dbslug) {
                        $existing_config_field_dbslug = $this->config_field->get(['dbslug' => $form->get_field_value('dbslug')]);
                        if (!empty($existing_config_field_dbslug)) {
                            $errors['dbslug'] = 'A Configuration Field with that database slug already exists.';
                        }
                    }

                    $form->set_field_value('dbslug', ipsCore::$functions->generate_dbslug($form->get_field_value('dbslug')));

                    $form->validate_form($errors);

                    if ($field_type = admin_form_builder::get_field_types($form->get_field_value('type'))) {
                        if (isset($field_type['link'])) {
                            $form->process_field_options($form->get_field_value('options'));
                        }
                    } else {
                        $errors[] = 'Not a valid field type.';
                    }

                    if (empty($errors)) {
                        $this->config_field->{$this->config_group->get_pkey()} = $group_id;
                        $this->config_field->position = 0;
                        $this->config_field->title = $form->get_field_value('title');
                        $this->config_field->dbslug = $form->get_field_value('dbslug');
                        $this->config_field->type = $form->get_field_value('type');
                        $this->config_field->options = $form->get_field_value('options');
                        $this->config_field->required = $form->get_field_value('required');

                        if ($this->config_field->save()) {
                            $this->log('Modified Configuration Group', 'Group ID: ' . $this->config_group->get_id());
                            if ($field_id) {
                                $json_data = ['success' => 'Configuration Group Field has been successfully modified!'];
                            } else {
                                $json_data = ['redirect' => '/admin/configuration/manage/managefield/' . $this->config_field->get_id()];
                                $this->add_flash('Configuration Group Field has been successfully added!');
                            }
                        } else {
                            $this->log('Failed to Save Configuration Field', 'Field Title: ' . $this->config_field->title);
                            $errors[] = 'Failed to Save Configuration Field.';
                        }
                    }
                } else {
                    $this->log('Configuration Group Process Failed', 'Failed to find Configuration Group to Save - group_id: ' . $this->config_field->{$this->config_group->get_pkey()});
                    $errors[] = 'Failed to find Configuration Field to Save.';
                }
            } else {
                $this->log('Configuration Field Process Failed', 'Failed to find Configuration Field to Save - field_id: ' . $field_id);
                $errors[] = 'Failed to find Configuration Field to Save.';
            }

            if (!empty($errors)) {
                $json_data = ['success' => false, 'errors' => $errors];
            }

            $this->add_data(['json' => $json_data]);

            $this->build_view([/*'layout' => false, */ 'json' => true]);
        } else {
            ipsCore::$functions->redirect('/');
        }
    }

    private function remove_group_form($group_id)
    {
        $form = new ipsCore_form_builder('remove_form');
        $form->set_action('/admin/configuration/remove_group_popup/' . $group_id);
        $form->set_classes(['ajax_form', 'remove']);
        $form->add_submit('remove', '<i class="far fa-trash-alt"></i>Remove Config Group');

        return $form->render();
    }

    public function remove_group_popup($group_id)
    {
        $confirm_form = new ipsCore_form_builder('confirm_remove_form');
        $confirm_form->set_action('/admin/configuration/remove_group_process/' . $group_id);
        $confirm_form->add_submit('confirm', 'Remove Config Group');

        $this->add_data([
            'message' => 'Are you sure you want to remove the Config Group? All fields contained in this configuration group will also be removed! This is a permanent action that cannot be reversed.',
            'confirm_form' => $confirm_form->render(),
        ]);

        $this->set_view('parts/confirm_popup');
        $this->build_view(['json' => true]);
    }

    public function remove_group_process($group_id)
    {
        $errors = [];

        if ($group_id && $this->config_group->retrieve($group_id)) {

            $deleted_process = true;
            $failed_fields = [];
            $succeeded_fields = [];
            if ($config_fields = $this->config_field->where([$this->config_group->get_pkey() => $group_id])->get_all()) {
                foreach ($config_fields as $config_field) {
                    if ($config_field->remove()) {
                        $succeeded_fields[] = $config_field->get_id();
                    } else {
                        $failed_fields[] = $config_field->get_id();
                        $deleted_process = false;
                    }
                }
            }

            if ($deleted_process && $this->config_group->remove()) {
                $json_data = ['redirect' => '/admin/configuration/manage'];
                $this->add_flash('Configuration Group Successfully Removed');
                $this->log('Removed Configuration Group', 'Removed configuration group - group_id: ' . $group_id . '<br />Including fields: ' . implode(", ", $succeeded_fields));
            } else {
                $this->log('Failed to remove Configuration Group', 'Failed to remove configuration group - group_id: ' . $group_id . '<br/>Successfully deleted fields: ' . implode(", ", $succeeded_fields) . '<br />Failed to delete fields: ' . implode(", ", $failed_fields));
                $errors[] = 'Failed to remove configuration group.';
            }
        } else {
            $this->log('Failed to remove Configuration Group', 'Failed to find configuration group to remove - group_id: ' . $group_id);
            $errors[] = 'Failed to remove configuration group.';
        }

        if (!empty($errors)) {
            $json_data = ['success' => false, 'errors' => $errors];
        }

        $this->add_data(['json' => $json_data]);

        $this->build_view(['json' => true]);
    }

    private function remove_field_form($field_id)
    {
        $form = new ipsCore_form_builder('remove_form');
        $form->set_action('/admin/configuration/remove_field_popup/' . $field_id);
        $form->set_classes(['ajax_form', 'remove']);
        $form->add_submit('remove', '<i class="far fa-trash-alt"></i>Remove Config Field');

        return $form->render();
    }

    public function remove_field_popup($field_id)
    {
        $confirm_form = new ipsCore_form_builder('confirm_remove_form');
        $confirm_form->set_action('/admin/configuration/remove_field_process/' . $field_id);
        $confirm_form->add_submit('confirm', 'Remove Config Field');

        $this->add_data([
            'message' => 'Are you sure you want to remove the Config Field? The fields data will be removed so anywhere relying on it may be effected. This is a permanent action that cannot be reversed.',
            'confirm_form' => $confirm_form->render(),
        ]);

        $this->set_view('parts/confirm_popup');
        $this->build_view(['json' => true]);
    }

    public function remove_field_process($field_id)
    {
        $errors = [];

        if ($field_id && $this->config_field->retrieve($field_id)) {

            if ($this->config_field->remove()) {
                $json_data = ['redirect' => '/admin/configuration/manage'];
                $this->add_flash('Configuration Field Successfully Removed');
                $this->log('Removed Configuration Field', 'Removed configuration field - field_id: ' . $field_id);
            } else {
                $this->log('Failed to remove Configuration Field', 'Failed to remove configuration field - field_id: ' . $field_id);
                $errors[] = 'Failed to remove configuration field.';
            }
        } else {
            $this->log('Failed to remove Configuration Field', 'Failed to find configuration field to remove - field_id: ' . $field_id);
            $errors[] = 'Failed to remove configuration field.';
        }

        if (!empty($errors)) {
            $json_data = ['success' => false, 'errors' => $errors];
        }

        $this->add_data(['json' => $json_data]);

        $this->build_view(['json' => true]);
    }

}
