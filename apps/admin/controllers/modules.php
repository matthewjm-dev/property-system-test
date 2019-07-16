<?php
ipsCore::requires_controller('controller');
ipsCore::requires_model(['model', 'module', 'module_field', 'module_item']);

class modules_controller extends admin_controller
{
    protected static $permission_level = 1;
    protected $protected_keys = ['mid', 'uid', 'mfid'];

    // Construct
    public function __construct($controller, $additional = false)
    {
        $this->set_page_title('Module Management - Admin');

        $this->load_model('admin_module', 'module');
        $this->load_model('admin_module_field', 'field');

        parent::__construct($controller, $additional);

        if (!$this->currentuser->is_developer()) {
            ipsCore::$functions->redirect('admin');
        }
    }

    // Methods
    public function index()
    {
        $this->get_layout();
        $this->set_breadcrumbs(['Dashboard' => '/admin', 'Modules' => false]);

        $module_items = $this->module->get_all();

        $modules = [];

        foreach($module_items as $module) {
            $modules[] = [
                'mid' => $module->mid,
                'key' => $module->get_pkey(),
                'title' => $module->title,
                'dbslug' => $module->dbslug,
            ];
        }

        $this->add_data([
            'title' => 'Manage Modules',
            'content' => 'Add, edit and manage modules used in the website.',
            'create_module_text' => 'Create Module',
            'create_module_href' => '/admin/modules/create',
            'manage_module_text' => 'Manage Module',
            'manage_module_href' => '/admin/modules/module/',
            'modules' => $modules,
        ]);

        $this->build_view();
    }

    // Module Management
    public function create($mode, $module_mid = false)
    {
        switch ($mode) {
            default:
            case 'module':
                $this->create_module();
                break;
            case 'field':
                $this->create_field($module_mid);
                break;
        }
    }

    private function module_form()
    {
        $form = new admin_form_builder('modify_module_form');
        $form->set_action('/admin/modules/module_process/' . ($this->module->mid ? $this->module->mid . '/' : ''));
        $form->set_classes(['ajax_form', 'main_form', 'edit']);
        $form->start_section('form_main');

        $module_options = $this->get_module_parent_list(true);

        $form->add_select('parent', 'Parent Module', ['value' => $this->module->parent, 'options' => $module_options, 'required' => true, 'default' => 0]);
        $form->add_text('title', 'Title', ['value' => $this->module->title, 'required' => true, 'placeholder' => 'Enter Title']);
        $form->add_text('title_single', 'Single Title', ['value' => $this->module->title_single, 'required' => true, 'placeholder' => 'Enter Singular Title']);
        $form->add_text('slug', 'Slug', ['value' => $this->module->slug, 'required' => true, 'placeholder' => 'Enter unique URL Slug']);
        $form->add_text('dbslug', 'Database Slug', ['value' => $this->module->dbslug, 'required' => true, 'placeholder' => 'Enter unique DB Slug']);
        $form->add_text('pkey', 'Primary Key', ['value' => ($this->module->mid ? $this->module->get_pkey() : ''), 'required' => true, 'placeholder' => 'Enter unique Module Primary Key']);
        $form->add_text('icon', 'FA Icon', ['value' => $this->module->icon, 'placeholder' => 'Enter Font Awesome Icon class', 'comment' => 'View available FA icons by <a target="_blank" href="https://fontawesome.com/icons?d=gallery">clicking here.</a>']);
        $form->add_textarea('description', 'Description', ['value' => $this->module->description, 'placeholder' => 'Enter A description of the post type']);
        $form->end_section('form_main');
        $form->start_section('submit');
        $form->add_submit('modify', '<i class="far fa-save"></i>' . ($this->module->mid ? 'Save' : 'Create') . ' Module');
        $form->end_section('submit');

        return $form;
    }

    public function module($module_mid = false)
    {
        if ($module_mid) {
            if ($this->module->retrieve($module_mid)) {
                $this->get_layout();
                $this->set_breadcrumbs(['Dashboard' => '/admin', 'Modules' => '/admin/modules', $this->module->title => false]);

                $module_fields = $this->field->where(['mid' => $this->module->mid])->get_all();
                $fields = [];
                if (!empty($module_fields)) {
                    foreach ($module_fields as $module_field) {
                        $fields[] =[
                            'mfid' => $module_field->get_id(),
                            'title' => $module_field->title,
                            'dbslug' => $module_field->dbslug,
                            'type' => $module_field->type,
                        ];
                    }
                }

                $this->add_data([
                    'slug' => $this->module->slug,
                    'title' => 'Managing Module "' . $this->module->title . '"',
                    'content' => 'Here you can manage your modules.',
                    'form' => $this->module_form()->render(),
                    'create_field_text' => 'Create Field',
                    'create_field_href' => '/admin/modules/create/field/' . $this->module->mid,
                    'manage_field_text' => 'Manage Field',
                    'manage_field_href' => '/admin/modules/field/',
                    'fields' => $fields,
                    'fields_sort_href' => '/admin/modules/sort/admin_module_field/',
                    'remove_form' => $this->remove_module_form($this->module->mid),
                    'no_fields_message' => 'No Fields to show',
                ]);

                $this->build_view();
            } else {
                ipsCore::add_error('Invalid Module');
            }
        } else {
            ipsCore::add_error('Module required manage a module');
        }
    }

    public function create_module()
    {
        $this->get_layout();
        $this->set_breadcrumbs(['Dashboard' => '/admin', 'Modules' => '/admin/modules', 'Create Module' => false]);

        $this->add_data([
            'title' => 'Create New Module',
            'content' => 'Here you can create a new module.',
            'form' => $this->module_form()->render(),
        ]);

        $this->set_view('modules/module');
        $this->build_view();
    }

    public function module_process($module_mid = false)
    {
        $errors = [];

        if (!$module_mid || ($module_mid && $this->module->retrieve($module_mid))) {

            $form = $this->module_form();
            $form->populate_form();

            $form->set_field_value('slug', ipsCore::$functions->generate_slug($form->get_field_value('slug')));
            $form->set_field_value('dbslug', ipsCore::$functions->generate_dbslug($form->get_field_value('dbslug')));
            $form->set_field_value('pkey', ipsCore::$functions->generate_dbslug($form->get_field_value('pkey')));

            $form->validate_form($errors);

            if (empty($errors)) {
                if ($form->get_field_value('slug') != $this->module->slug) {
                    $existing_module_slug = $this->module->get(['slug' => $form->get_field_value('slug')]);
                    if (!empty($existing_module_slug)) {
                        $errors['slug'] = 'A Module with that slug already exists.';
                    }
                }

                if ($form->get_field_value('dbslug') != $this->module->dbslug) {
                    $existing_module_dbslug = $this->module->get(['dbslug' => $form->get_field_value('dbslug')]);
                    if (!empty($existing_module_dbslug)) {
                        $errors['dbslug'] = 'A Module with that database slug already exists.';
                    }
                }

                if (in_array($form->get_field_value('pkey'), $this->protected_keys)) {
                    $errors['pkey'] = 'That is a protected Key, please try another';
                } else {
                    if ($form->get_field_value('pkey') != $this->module->get_pkey()) {
                        $existing_module_key = $this->module->get(['pkey' => $form->get_field_value('pkey')]);
                        if (!empty($existing_module_key)) {
                            $errors['pkey'] = 'A Module with that Primary Key already exists.';
                        }
                    }
                }

                if (empty($errors)) {
                    if ($module_mid) {
                        // Make DB changes
                        if ($this->module->get_pkey() != $form->get_field_value('pkey')) {
                            $current_table = $this->module->get_model_table();
                            $this->module->set_table($this->module->add_prefix($this->module->dbslug));
                            if (!$this->module->modify_pkey($form->get_field_value('pkey'))) {
                                $errors[] = 'Failed to Alter pkey.';
                            }
                            $this->module->set_table($current_table);
                        }
                        if ($this->module->dbslug != $form->get_field_value('dbslug')) {
                            if (!$this->module->modify_table($this->module->dbslug, $form->get_field_value('dbslug'))) {
                                $errors[] = 'Failed to Alter table name.';
                            }
                        }
                    } else {
                        // Create table
                        if (!$this->module->create_table($form->get_field_value('dbslug'), $form->get_field_value('pkey'))) {
                            $errors[] = 'Failed to create table into database for module';
                        }
                    }
                    if (empty($errors)) {
                        $this->module->modified = time();
                        if (!$module_mid) {
                            $this->module->created = $this->module->modified;
                        }
                        $this->module->parent = $form->get_field_value('parent');
                        $this->module->title = $form->get_field_value('title');
                        $this->module->title_single = $form->get_field_value('title_single');
                        $this->module->slug = $form->get_field_value('slug');
                        $this->module->dbslug = $form->get_field_value('dbslug');
                        $this->module->description = $form->get_field_value('description');
                        $this->module->set_pkey($form->get_field_value('pkey'));
                        $this->module->icon = $form->get_field_value('icon');

                        if ($this->module->save()) {
                            if ($module_mid) {
                                $this->log('Modified Module', 'Module ID: ' . $this->module->mid);
                                $json_data = ['success' => 'Module has been successfully modified!'];
                            } else {
                                $this->log('Created Module', 'Module ID: ' . $this->module->mid);
                                $json_data = ['redirect' => '/admin/modules/module/' . $this->module->mid];
                                $this->add_flash('Module Created Successfully!');
                            }
                        } else {
                            $errors[] = 'Failed to Save Module.';
                        }
                    }
                }
            }
        } else {
            $errors[] = 'Failed to find Module to Save.';
        }

        if (!empty($errors)) {
            $json_data = ['success' => false, 'errors' => $errors];
        }

        $this->add_data(['json' => $json_data]);

        $this->build_view([/*'layout' => false, */'json' => true]);
    }

    private function field_form()
    {
        $form = new admin_form_builder('modify_field_form');
        $form->set_action('/admin/modules/field_process/' . ($this->field->mfid ? $this->field->mfid : 'create/' . $this->module->mid) . '/');
        $form->set_classes(['ajax_form', 'main_form', 'edit']);

        // Main Fields
        $form->start_section('form_main');

        $form->add_text('title', 'Title', ['value' => $this->field->title, 'required' => true, 'placeholder' => 'Enter Title']);
        $form->add_text('dbslug', 'Database Slug', ['value' => $this->field->dbslug, 'required' => true, 'placeholder' => 'Enter Database Slug']);
        $form->add_select('type', 'Type', ['options' => $form->get_field_type_options($this->field->type), 'required' => true, 'placeholder' => 'Choose Field Type']);

        // Link / Option classes
        $show_options = 'hidden';
        $show_link_module = 'hidden';
        $show_link_module_field = 'hidden';
        $show_placeholder_selectable = 'hidden';
        if ($this->field->mfid) {
            $field_type = admin_form_builder::get_field_types($this->field->type);
            if (isset($field_type['link'])) {
                if ($field_type['link']) {
                    $show_link_module = '';
                    $show_link_module_field = '';
                } else {
                    $show_options = '';
                }
                $show_placeholder_selectable = '';
            }
        }

        // Manual Options
        $form->add_textarea('options', 'Options', ['value' => $form->show_field_options_output($this->field->options), 'placeholder' => 'Enter Options', 'comment' => 'Enter Options on new lines like: "value : Name"', 'fieldset_classes' => $show_options]);

        // Link Module
        $form->add_select('link', 'Link Module', ['options' => $this->get_field_link_options(), 'placeholder' => 'Choose Link Module..', 'fieldset_classes' => $show_link_module]);
        $form->add_select('link_field', 'Link Module Field', ['options' => $this->get_field_link_field_options(), 'placeholder' => 'Choose link module field to show..', 'fieldset_classes' => $show_link_module_field]);

        // Other fields
        $form->add_textarea('description', 'Description', ['value' => $this->field->description, 'placeholder' => 'Enter A description of the Field']);
        $form->add_textarea('default', 'Default Data', ['value' => $this->field->default, 'placeholder' => 'Enter any default data for the Field']);
        $form->add_text('placeholder', 'Placeholder', ['value' => $this->field->placeholder, 'placeholder' => 'Enter Placeholder text']);
        $form->add_radio('placeholder_selectable', 'Selectable Placeholder? (for drop downs)',
            ['value' => $this->field->placeholder_selectable, 'options' => [['text' => 'Yes', 'value' => 1], ['text' => 'No', 'value' => 0]], 'comment' => 'Choose if the placeholder option in a drop down should be selectable.', 'default' => 0, 'fieldset_classes' => $show_placeholder_selectable]);
        $form->end_section('form_main');

        // Field Options
        $form->start_section('form_options');

        // Module Item Link
        $form->add_select('item_link', 'Item Link', ['comment' => 'Choose a link item from this module to show this field only on that item.', 'options' => $this->get_module_item_link_options(), 'placeholder' => 'No Linked Item', 'placeholder_selectable' => true, 'default' => 0]);

        // Display Options
        $form->add_radio('show_list', 'Show On List', ['value' => $this->field->show_list, 'options' => [['text' => 'Yes', 'value' => 1], ['text' => 'No', 'value' => 0]], 'comment' => 'Choose if this field should show on the admin module list', 'default' => 0]);
        $form->add_radio('required', 'Required Field', ['value' => $this->field->required, 'options' => [['text' => 'Yes', 'value' => 1], ['text' => 'No', 'value' => 0]], 'comment' => 'Choose if this field is required', 'default' => 0]);
        $form->end_section('form_options');

        // Submit
        $form->start_section('submit');
        if ($this->field->mfid) {
            $form->add_html('add_another', '<a id="add-another" class="button" href="/admin/modules/create/field/' . $this->module->mid . '"><i class="fas fa-plus-square"></i>Add Another</a>');
        }
        $form->add_submit('modify', '<i class="far fa-save"></i>' . ($this->field->mfid ? 'Save' : 'Create') . ' Field');
        $form->end_section('submit');

        return $form;
    }

    public function field($field_mfid)
    {
        if ($field_mfid && $this->field->retrieve($field_mfid)) {
            if ($this->module->retrieve($this->field->mid)) {
                $this->get_layout();
                $this->set_breadcrumbs(['Dashboard' => '/admin', 'Modules' => '/admin/modules', $this->module->title => '/admin/modules/module/' . $this->module->mid, 'Manage Field "' . $this->field->title . '""' => false]);

                $this->add_data([
                    'slug' => $this->module->slug,
                    'title' => 'Managing Field "' . $this->field->title . '"',
                    'form' => $this->field_form()->render(),
                    'remove_form' => $this->remove_field_form($this->field->mfid),
                ]);

                $this->build_view();
            } else {
                ipsCore::add_error('Invalid Module');
            }
        } else {
            ipsCore::add_error('Invalid Field');
        }
    }

    public function create_field($module_mid)
    {
        if ($module_mid && $this->module->retrieve($module_mid)) {
            $this->get_layout();
            $this->set_breadcrumbs(['Dashboard' => '/admin', 'Modules' => '/admin/modules', $this->module->title => '/admin/modules/module/' . $this->module->mid, 'Create Field' => false]);

            $this->add_data([
                'title' => 'Create New Field',
                'content' => 'Here you can create a new field.',
                'form' => $this->field_form()->render(),
            ]);

            $this->set_view('modules/field');
            $this->build_view();
        } else {
            $this->log('Create Field Failed', 'Invalid Module: ' . $module_mid);
            ipsCore::add_error('Invalid Module');
        }
    }

    public function field_process($a, $b = false)
    {
        $errors = [];

        if ($b) {
            $field_mfid = false;
            $module_mid = $b;
        } else {
            $field_mfid = $a;
            $module_mid = false;
        }

        if (!$field_mfid || ($field_mfid && $this->field->retrieve($field_mfid))) {

            if (!$module_mid) {
                $module_mid = $this->field->mid;
            }

            $field_form = $this->field_form();
            $field_form->populate_form();

            $field_form->set_field_value('dbslug', ipsCore::$functions->generate_dbslug($field_form->get_field_value('dbslug')));

            $field_form->validate_form($errors);

            $field_options = null;
            $field_link_valid = null;
            $field_link_field_valid = null;

            if (empty($errors)) {
                if (!$field_type = ipsCore_form_builder::get_field_types($field_form->get_field_value('type'))) {
                    $errors['type'] = 'You must choose a valid Field type';
                } else {
                    if (isset($field_type['link'])) {
                        if ($field_type['link']) { // Link
                            if (!empty($field_form->get_field_value('link')) && $this->module->get(['mid' => $field_form->get_field_value('link')])) {
                                $field_link_valid = $field_form->get_field_value('link');
                                $field_link_field = $field_form->get_field_value('link_field');
                                if (!empty($field_link_valid) && $this->field->get(['mfid' => $field_link_field])) {
                                    $field_link_field_valid = $field_link_field;
                                } else {
                                    if ((int)$field_link_field === 0) {
                                        $field_link_field_valid = $field_link_field;
                                    } else {
                                        $errors['link_field'] = 'That field does not exist to link on';
                                    }
                                }
                            } else {
                                $errors['link'] = 'That module does not exist to link';
                            }
                        } else { // Options
                            if ($field_form->get_field_value('options')) {
                                $field_options = $field_form->process_field_options($field_form->get_field_value('options'));
                            } else {
                                $errors['options'] = 'Enter Options for the field';
                            }
                        }
                    }
                }

                if (empty($errors)) {
                    if ($module_mid && $this->module->retrieve($module_mid)) {
                        $this->load_model('admin_module', 'module_item', $this->module->dbslug);
                    } else {
                        $this->log('Field Process Failed', 'Failed to retrieve field Module - module_mid: ' . $module_mid);
                        $errors[] = 'Failed to retrieve field Module';
                    }

                    if ($field_mfid) {
                        if (!$this->module_item->modify_column($this->field->dbslug, $field_form->get_field_value('dbslug'), $field_type['type'], $field_type['length'], $field_form->get_field_value('default'), false)) {
                            $this->log('Field Process Failed', 'Failed to modify column in database - From: ' . $this->field->dbslug . ' To: ' . $field_form->get_field_value('dbslug'));
                            $errors[] = 'Failed to modify column in database';
                        }
                    } else {
                        if (!$this->module_item->create_column($field_form->get_field_value('dbslug'), $field_type['type'], $field_type['length'], $field_form->get_field_value('default'), false)) {
                            $this->log('Field Process Failed', 'Failed to create column in database - Column: ' . $field_form->get_field_value('dbslug'));
                            $errors[] = 'Failed to create column in database';
                        }
                    }

                    if (empty($errors)) {
                        $this->field->modified = time();
                        if (!$field_mfid) {
                            $this->field->created = $this->field->modified;
                        }
                        $this->field->title = $field_form->get_field_value('title');
                        $this->field->dbslug = $field_form->get_field_value('dbslug');
                        $this->field->description = $field_form->get_field_value('description');
                        $this->field->position = 0;
                        $this->field->default = $field_form->get_field_value('default');
                        $this->field->placeholder = $field_form->get_field_value('placeholder');
                        $this->field->placeholder_selectable = $field_form->get_field_value('placeholder_selectable');
                        $this->field->type = $field_type['key'];
                        $this->field->options = $field_options;
                        $this->field->link = $field_link_valid;
                        $this->field->link_field = $field_link_field_valid;
                        $this->field->item_link = $field_form->get_field_value('item_link');
                        $this->field->show_list = $field_form->get_field_value('show_list');
                        $this->field->required = $field_form->get_field_value('required');
                        $this->field->searchable = $field_form->get_field_value('searchable');
                        if (!$field_mfid) {
                            $this->field->mid = $module_mid;
                        }

                        if ($this->field->save()) {
                            if ($field_mfid) {
                                $this->log('Modified Field', 'Field ID: ' . $this->field->mfid);
                                $json_data = ['success' => 'Field has been successfully modified!'];
                            } else {
                                $this->log('Created Field', 'Field ID: ' . $this->field->mfid);
                                $json_data = ['redirect' => '/admin/modules/field/' . $this->field->mfid];
                                $this->add_flash('Field Created Successfully!');
                            }
                        } else {
                            if ($field_mfid) {
                                $this->log('Field Process Failed', 'Failed to Save Field.');
                                $errors[] = 'Failed to Save Field.';
                            } else {
                                $this->log('Field Process Failed', 'Failed to Create Field.');
                                $errors[] = 'Failed to Create Field.';
                            }
                        }
                    }
                }
            }
        } else {
            $this->log('Field Process Failed', 'Failed to find Field to Save - field_mfid: ' . $field_mfid . ' module_mid: ' . $module_mid);
            $errors[] = 'Failed to find Field to Save.';
        }

        if (!empty($errors)) {
            $json_data = ['success' => false, 'errors' => $errors];
        }

        $this->add_data(['json' => $json_data]);

        $this->build_view([/*'layout' => true, */'json' => true]);
    }

    private function remove_module_form($module_mid)
    {
        $form = new ipsCore_form_builder('remove_form');
        $form->set_action('/admin/modules/remove_module_popup/' . $module_mid . '/');
        $form->set_classes(['ajax_form', 'remove']);
        $form->add_submit('remove', '<i class="far fa-trash-alt"></i>Remove Module');

        return $form->render();
    }

    public function remove_module_popup($module_mid)
    {
        $confirm_form = new ipsCore_form_builder('confirm_remove_form');
        $confirm_form->set_action('/admin/modules/remove_module_process/' . $module_mid . '/');
        $confirm_form->add_submit('confirm', 'Remove Module');

        $this->add_data([
            'message' => 'Are you sure you want to remove the Module? All data and fields stored in module items for this field will be lost. This is a permanent action that cannot be reversed.',
            'confirm_form' => $confirm_form->render(),
        ]);

        $this->set_view('parts/confirm_popup');
        $this->build_view([/*'layout' => true, */'json' => true]);
    }

    public function remove_module_process($module_mid)
    {
        $errors = [];

        if ($module_mid && $this->module->retrieve($module_mid)) { // get module
            if ($this->module->remove(['mid' => $module_mid])) { // remove module
                if ($this->field->remove(['mid' => $module_mid])) { // remove module fields

                    $this->load_model('admin_module', 'module_item', $this->module->dbslug);
                    if ($this->module_item->remove_table()) { // remove module items
                        $json_data = ['redirect' => '/admin/modules/'];
                        $this->add_flash('Module Successfully Deleted');
                        $this->log('Deleted Field', 'Deleted Module - module_mid: ' . $module_mid);
                    } else {
                        $this->log('Failed to remove Module Items', 'Failed to remove module items - module_mid: ' . $module_mid);
                        $errors[] = 'Failed to Remove Module Items.';
                    }
                } else {
                    $this->log('Failed to remove Module Feilds', 'Failed to remove module fields - module_mid: ' . $module_mid);
                    $errors[] = 'Failed to Remove Module Fields.';
                }
            } else {
                $this->log('Failed to remove Module', 'Failed to remove module - module_mid: ' . $module_mid);
                $errors[] = 'Failed to Remove Module.';
            }
        } else {
            $this->log('Failed to remove Module', 'Failed to find module to remove - module_mid: ' . $module_mid);
            $errors[] = 'Failed to find Module to Remove.';
        }

        if (!empty($errors)) {
            $json_data = ['success' => false, 'errors' => $errors];
        }

        $this->add_data(['json' => $json_data]);

        $this->build_view([/*'layout' => false, */'json' => true]);
    }

    public function remove_field_form($field_mfid)
    {
        $form = new ipsCore_form_builder('remove_form');
        $form->set_action('/admin/modules/remove_field_popup/' . $field_mfid . '/');
        $form->set_classes(['ajax_form', 'remove']);
        $form->add_submit('remove', '<i class="far fa-trash-alt"></i>Remove Field');

        return $form->render();
    }

    public function remove_field_popup($field_mfid)
    {
        $confirm_form = new ipsCore_form_builder('confirm_remove_form');
        $confirm_form->set_action('/admin/modules/remove_field_process/' . $field_mfid . '/');
        $confirm_form->add_submit('confirm', 'Remove Field');

        $this->add_data([
            'message' => 'Are you sure you want to remove the Field? All data stored in module items for this field will be lost. This is a permanent action that cannot be reversed.',
            'confirm_form' => $confirm_form->render(),
        ]);

        $this->set_view('parts/confirm_popup');
        $this->build_view([/*'layout' => true, */'json' => true]);
    }

    public function remove_field_process($field_mfid)
    {
        $errors = [];

        if ($field_mfid && $this->field->retrieve($field_mfid)) { // is valid field
            if ($this->module->retrieve($this->field->mid)) { // get module from field

                $this->load_model('admin_module', 'module_item', $this->module->dbslug);
                if ($this->module_item->remove_column($this->field->dbslug)) {   // remove column first

                    if ($this->field->remove()) { // remove field item
                        $json_data = ['redirect' => '/admin/modules/module/' . $this->field->mid . '/'];
                        $this->add_flash('Field has been successfully removed!');
                        $this->log('Deleted Field', 'Deleted Field item - field_mfid: ' . $field_mfid . ' ( ' . $this->field->get_name() . ' ) mid: ' . $this->field->mid);
                    } else {
                        $this->log('Failed to remove field', 'Failed to remove item - field_mfid: ' . $field_mfid . ' mid: ' . $this->field->mid);
                        $errors[] = 'Failed to remove Field item.';
                    }
                } else {
                    $this->log('Failed to remove field', 'Failed to remove field column - field_mfid: ' . $field_mfid . ' mid: ' . $this->field->mid);
                    $errors[] = 'Failed to remove Field column.';
                }
            } else {
                $this->log('Failed to remove field', 'Failed to retrieve module from field - field_mfid: ' . $field_mfid . ' mid: ' . $this->field->mid);
                $errors[] = 'Failed to remove Field column.';
            }
        } else {
            $this->log('Failed to remove field', 'Failed to find field to remove - field_mfid: ' . $field_mfid . ' mid: ' . $this->field->mid);
            $errors[] = 'Failed to find Field to Remove.';
        }

        if (!empty($errors)) {
            $json_data = ['success' => false, 'errors' => $errors];
        }

        $this->add_data(['json' => $json_data]);

        $this->build_view([/*'layout' => false, */'json' => true]);
    }

    private function get_field_link_options()
    {
        $link_options = [];

        $modules = $this->module->get_all();
        if (!empty($modules)) {
            foreach ($modules as $module) {
                $selected = ($module->mid == $this->field->link ? true : false);
                $link_options[] = [
                    'text' => $module->title,
                    'value' => $module->mid,
                    'selected' => $selected,
                ];
            }
        }

        return $link_options;
    }

    private function get_field_link_field_options()
    {
        $link_field_options = [[
            'text' => 'Title',
            'value' => 0,
            'selected' => ($this->field->link_field == 0 ? true : false)
        ]];

        if ($this->field->link) {
            $this->load_model('admin_module_field', 'link_field');
            $link_fields = $this->link_field->where(['mid' => $this->field->link])->get_all();

            if (!empty($link_fields)) {
                foreach ($link_fields as $link_field) {

                    $link_field_options[] = [
                        'text' => $link_field->title,
                        'value' => $link_field->mfid,
                        'selected' => ($this->field->link_field == $link_field->mfid ? true : false),
                    ];
                }
            }
        }

        return $link_field_options;
    }

    private function get_module_item_link_options() {
        $module_item_link_options = [];

        $this->load_model('admin_module_item', 'item_link', $this->module->dbslug);
        $item_links = $this->item_link->get_all();

        if (!empty($item_links)) {
            foreach ($item_links as $item_link) {

                $module_item_link_options[] = [
                    'text' => $item_link->title,
                    'value' => $item_link->get_id(),
                    'selected' => ($this->field->item_link == $item_link->get_id() ? true : false),
                ];
            }
        }

        return $module_item_link_options;
    }

    public function do_field_type_change($field_type)
    {
        $errors = [];

        if ($field_type = admin_form_builder::get_field_types($field_type)) {

            if (isset($field_type['link'])) {
                if ($field_type['link']) {
                    if ($field_type['key'] == 'submodule') {
                        $json_data = ['hide' => ['#field-options', '#field-link_field', '#field-placeholder_selectable'], 'show' => ['#field-link']];
                    } else {
                        $json_data = ['hide' => ['#field-options'], 'show' => ['#field-link', '#field-link_field', '#field-placeholder_selectable']];
                    }
                } else {
                    $json_data = ['hide' => ['#field-link', '#field-link_field'], 'show' => ['#field-options', '#field-placeholder_selectable']];
                }
            } else {
                $json_data = ['hide' => ['#field-link', '#field-link_field', '#field-options', '#field-placeholder_selectable']];
            }

        } else {
            $errors[] = 'Invalid Field type';
        }

        if (!empty($errors)) {
            $json_data = ['success' => false, 'errors' => $errors];
        }

        $this->add_data(['json' => $json_data]);

        $this->build_view([/*'layout' => false, */'json' => true]);
    }

    public function do_field_link_module_change($link_module)
    {
        $this->field->link = $link_module;

        $link_fields = $this->get_field_link_field_options();

        if (!empty($link_fields)) {
            $json_data = ['link_module_fields' => $link_fields];
        } else {
            $errors[] = 'Invalid Field type';
        }

        if (!empty($errors)) {
            $json_data = ['success' => false, 'errors' => $errors];
        }

        $this->add_data(['json' => $json_data]);

        $this->build_view([/*'layout' => false, */'json' => true]);
    }

    public function get_module_parent_list($as_options = false)
    {
        $modules = $this->module->get_all();

        if ($as_options) {
            $list = [
                ['value' => 0, 'text' => 'No Parent'],
            ];
            $char = '-';
            $this->get_module_parent_list_recursive_options($modules, $list, $char);

            return $list;
        } else {
            return $this->get_module_parent_list_recursive($modules);
        }
    }

    private function get_module_parent_list_recursive($modules, $current_id = 0)
    {
        if (!empty($modules)) {
            $module_list = [];
            foreach ($modules as $module) {
                if ($module->parent == $current_id) {
                    $module_list[$module->mid] = [
                        'mid' => $module->mid,
                        'title' => $module->title,
                        'children' => $this->get_module_parent_list_recursive($modules, $module->mid),
                    ];
                }
            }

            return $module_list;
        }
    }

    private function get_module_parent_list_recursive_options($modules, &$list, $char, $new_char = '', $current_id = 0)
    {
        if (!empty($modules)) {
            if ($current_id != 0) {
                $new_char = $new_char . $char;
            }
            foreach ($modules as $module) {
                if ($module->parent == $current_id) {
                    $list[] = [
                        'value' => $module->mid,
                        'text' => $new_char . ' ' . $module->title,
                    ];
                    $this->get_module_parent_list_recursive_options($modules, $list, $char, $new_char, $module->mid);
                }
            }
        }
    }
}
