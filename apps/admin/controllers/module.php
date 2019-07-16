<?php
ipsCore::requires_controller('controller');
ipsCore::requires_model(['model', 'module', 'module_field', 'module_item', 'file']);
ipsCore::requires_helper('fields_helper');

class module_controller extends admin_controller
{

    // Construct
    public function __construct($controller, $additional = false)
    {
        parent::__construct($controller, $additional);

        $this->load_model('admin_module', 'module');
    }

    // Methods
    public function index($module_slug = false, $current_page = false)
    {
        if (!$module_slug) {
            $this->add_flash('That module does not exist');
            ipsCore::$functions->redirect('/admin');
        }

        if (!$current_page) {
            $current_page = 1;
        }

        if ($this->module->where_has_permission()->where(['slug' => $module_slug])->retrieve()) {

            $this->set_page_title($this->module->title . ' Module - Admin');

            $this->get_layout();
            $this->set_breadcrumbs(['Dashboard' => '/admin', $this->module->title . ($current_page > 1 ? ' ( Page ' . $current_page . ' )' : '') => false]);

            $this->load_model('admin_module_item', 'items', $this->module->dbslug);

            if (!$this->currentuser->is_developer()) {
                $this->items->where(['removed' => 0]);
            }

            $items = $this->get_list_items([
                'module_slug' => $module_slug,
                'model' => $this->items,
                'current_page' => $current_page,
                'slug' => $module_slug,
                'submit_location' => '/admin/module/filter_bar_submit',
                'searchfield' => 'title', // TODO: this needs to be changeable
            ]);

            $this->load_model('admin_module_field', 'fields');
            $fields = $this->fields->where(['mid' => $this->module->mid])->get_all();

            $data_items = [];
            $items_headers = [];

            $first = true;
            if (!empty($items)) {
                foreach ($items as $item) {
                    $data_item = [
                        'title' => $item->title,
                        'classes' => ($item->live ? 'live' : 'unlive') . ($item->removed ? ' removed' : '') . ($item->locked ? ' locked' : ' unlocked'),
                        'fields' => [],
                        'toggle_live_item_form' => $this->toggle_live_form($this->module->mid, $item->get_id()),
                        'toggle_lock_item_form' => $this->toggle_lock_form($this->module->mid, $item->get_id()),
                    ];
                    if (!empty($fields)) {
                        foreach ($fields as $field) {
                            if ($field->show_list && (!$field->item_link || $field->item_link == $item->get_id())) {
                                $field_type = admin_form_builder::get_field_types($field->type);
                                if (isset($field_type['link']) && $item->{$field->dbslug}) {
                                    if ($field_type['link']) {
                                        $this->load_model('admin_module', 'link_module');
                                        $this->link_module->retrieve($field->link);

                                        $this->load_model('admin', 'link_item', $this->link_module->dbslug);
                                        $this->link_item->retrieve($item->{$field->dbslug});

                                        $this->load_model('admin_module_field', 'field');
                                        $link_field_title = false;
                                        if ((int)$field->link_field === 0) {
                                            $link_field_title = 'title';
                                        } else {
                                            $link_field = $this->field->get(['mfid' => $field->link_field]);
                                            if ($link_field !== false && $link_field->dbslug !== null) {
                                                $link_field_title = $link_field->dbslug;
                                            }
                                        }

                                        $data_item['fields'][$field->dbslug] = ($link_field_title && $this->link_item->{$link_field_title} ? $this->link_item->{$link_field_title} : $item->get_id());
                                    } else {

                                    }
                                } else {
                                    if (isset($item->{$field->dbslug})) {
                                        $data_item['fields'][$field->dbslug] = $item->{$field->dbslug};
                                    } else {
                                        $data_item['fields'][$field->dbslug] = '';
                                    }
                                }
                                if ($first) {
                                    $items_headers[] = $field->title;
                                }
                            }
                        }
                    }
                    $data_items[$item->get_id()] = $data_item;
                    $first = false;
                }
            }

            $this->add_data([
                'title' => 'Manage ' . $this->module->title,
                'empty' => 'No ' . $this->module->title . ' to display.',
                'content' => $this->module->description,
                'create_item_text' => 'Create ' . $this->module->title_single,
                'create_item_href' => '/admin/module/create/' . $this->module->slug,
                'manage_item_text' => 'Manage ' . $this->module->title_single,
                'manage_item_href' => '/admin/module/manage/' . $this->module->slug . '/',
                'items_headers' => $items_headers,
                'items' => $data_items,
            ]);

            $this->build_view();
        } else {
            $this->error_nopermission();
        }
    }

    private function module_item_form($manage, $module_item_id = false)
    {
        $form = new admin_form_builder('manage_module_item_form');
        $form->set_action('/admin/module/process/' . $this->module->slug . ($manage ? '/' . $this->module_item->get_id() : '') . '/');
        $form->set_classes(['ajax_form', 'main_form', 'edit']);

        // Default Title field
        $form->start_section('form_title');
        $form->add_text('title', 'Title', ['value' => $this->module->title, 'required' => true, 'placeholder' => 'Enter Title']);
        $form->end_section('form_title');

        // Module defined Fields
        $this->load_model('admin_module_field', 'field');
        $module_fields = $this->field
            ->where(['mid' => $this->module->mid])
            ->and_where(['item_link' => 0], ['item_link' => ['value' => $module_item_id, 'binding' => 'OR']])
            ->order('position', 'ASC')
            ->get_all();

        if (!empty($module_fields)) {
            $form->start_section('form_main');

            $fields_helper = new fields_helper();
            $fields_helper->output_fields($form, 'admin_form_builder', $module_fields, $module_item_id);

            $form->end_section('form_main');
        }

        $form->start_section('options');
        $form->add_radio('live', 'Live', ['options' => [['text' => 'Yes', 'value' => 1], ['text' => 'No', 'value' => 0]], 'comment' => 'Choose if this item is live on the website', 'default' => 0]);
        if ($this->currentuser->is_developer()) {
            $form->add_radio('locked', 'Lock Item', ['options' => [['text' => 'Yes', 'value' => 1], ['text' => 'No', 'value' => 0]], 'comment' => 'Developer Option: Lock this item so it cant be removed by admins', 'default' => 0]);
        }
        $form->end_section('options');
        $form->start_section('submit');
        if ($manage) {
            $form->add_html('add_another', '<a id="add-another" class="button" href="/admin/module/create/' . $this->module->slug . '"><i class="fas fa-plus-square"></i>Add Another</a>');
        }
        $form->add_submit('modify', '<i class="far fa-save"></i>' . ($manage ? 'Save ' : 'Create ') . $this->module->title_single);
        $form->end_section('submit');

        return $form;
    }

    public function create($module_slug)
    {
        if ($this->module->retrieve(['slug' => $module_slug])) {
            $this->get_layout();
            $this->set_breadcrumbs(['Dashboard' => '/admin', $this->module->title => '/admin/module/' . $this->module->slug, 'Create ' . $this->module->title_single => false]);

            $create_form = $this->module_item_form(false);
            $create_form->populate_form();

            $this->add_data([
                'title' => 'Create new ' . $this->module->title_single,
                'form' => $create_form->render(),
            ]);

            $this->set_view('module/item');
            $this->build_view();
        } else {
            ipsCore::add_error('Invalid Module');
        }
    }

    public function manage($module_slug, $module_item_id = false)
    {
    	if ($module_item_id) {
			if ($this->module->retrieve(['slug' => $module_slug])) {
				$this->load_model('admin_module_item', 'module_item', $this->module->dbslug);

				if ($this->module_item->retrieve($module_item_id)) {

					$this->get_layout();
					$this->set_breadcrumbs(['Dashboard' => '/admin', $this->module->title => '/admin/module/' . $this->module->slug, 'Manage ' . $this->module->title_single => false]);

					$modify_form = $this->module_item_form(true, $module_item_id);
					$modify_form->populate_form($this->module_item);

					$this->add_data([
						'title' => 'Manage ' . $this->module->title_single,
						'remove_form' => (!$this->module_item->removed ? (!$this->module_item->locked ? $this->remove_form($this->module->mid, $module_item_id, $this->module->title_single) : '<p id="locked-message">This item is locked</p>') : false),
						'restore_form' => ($this->module_item->removed ? $this->restore_form($this->module->mid, $module_item_id, $this->module->title_single) : false),
						'form' => $modify_form->render(),
					]);

					$this->set_view('module/item');
					$this->build_view();
				} else {
					$this->log('Invalid Module Item', 'Tried to load an invalid module item: ' . $module_item_id);
					ipsCore::add_error('Invalid Module Item');
				}
			} else {
				$this->log('Invalid Module', 'Tried to load an invalid module: ' . $module_slug);
				ipsCore::add_error('Invalid Module');
			}
        } else {
			$this->error404();
		}
    }

    public function process($module_slug, $module_item_id = false)
    {
        $errors = [];

        // Load Module
        if ($module_slug && $this->module->retrieve(['slug' => $module_slug])) {

            // Create Module Item
            $this->load_model('admin_module_item', 'module_item', $this->module->dbslug);

            // Bypass or Load Module Item
            if (!$module_item_id || ($module_item_id && $this->module_item->retrieve($module_item_id))) {

                // Load form & validate
                $form = $this->module_item_form(false);
                $form->populate_form();
                $form->validate_form($errors);

                if (empty($errors)) {
                    // Load Module Fields
                    $this->load_model('admin_module_field', 'field');
                    $module_fields = $this->field->where(['mid' => $this->module->mid])->get_all();

                    // If we have Module Fields
                    if (!empty($module_fields)) {

                        // Loop through Module Fields
                        foreach ($module_fields as $field) {

                            // Load Field type
                            if ($field_type = admin_form_builder::get_field_types($field->type)) {

                                $value = $form->get_field_value($field->dbslug);

                                if (is_array($value)) {
                                    $value = implode(',', $value);
                                }

                                if (isset($field_type['file'])) {
                                    $raw_file = ipsCore_file_manager::get_sent_file($field->dbslug);

                                    if ($field_type['file']) { // file
                                        // TODO: validate file
                                    } else { // image
                                        // TODO: validate image
                                    }
                                }

                                // Add field to Module Item
                                $this->module_item->{$field->dbslug} = $value;
                            }
                        }
                    }

                    if (!$module_item_id) {
                        $this->module_item->created = time();
                        $this->module_item->removed = 0;
                    }
                    $this->module_item->live = $form->get_field_value('live');
                    if ($this->currentuser->is_developer()) {
                        $this->module_item->locked = $form->get_field_value('locked');
                    }
                    $this->module_item->modified = time();

                    $this->module_item->position = 0;
                    $this->module_item->title = $form->get_field_value('title');

                    // Save Module Item
                    if (empty($errors) && $this->module_item->save()) {
                        if ($module_item_id) {
                            $this->log('Updated Module item', $this->module->title_single . ' has been successfully updated!');
                            $json_data = ['success' => $this->module->title_single . ' has been successfully updated!'];
                        } else {
                            $this->log('Created Module item', $this->module->title_single . ' has been successfully added!');
                            $json_data = ['redirect' => '/admin/module/manage/' . $this->module->slug . '/' . $this->module_item->get_id()];
                            $this->add_flash('Item Successfully Created');
                        }
                    } else {
                        if ($module_item_id) {
                            $this->log('Module Process Failed', $this->module->title_single . ' failed to update in database');
                            $errors[] = $this->module->title_single . ' failed to update in database';
                        } else {
                            $this->log('Module Process Failed', $this->module->title_single . ' failed to insert into database');
                            $errors[] = $this->module->title_single . ' failed to insert into database';
                        }
                    }
                }
            } else {
                $this->log('Module Process Failed', 'Failed to load existing ' . $this->module->title_single);
                $errors[] = 'Failed to load existing ' . $this->module->title_single;
            }
        } else {
            $this->log('Module Process Failed', 'Tried to process an invalid module: ' . $module_slug);
            $errors[] = 'Invalid module';
        }

        if (!empty($errors)) {
            $json_data = ['success' => false, 'errors' => $errors];
        }

        $this->add_data(['json' => $json_data]);

        $this->build_view([/*'layout' => false, */'json' => true]);
    }

    private function remove_form($module_mid, $item_id, $title)
    {
        $form = new admin_form_builder('remove_form');
        $form->set_action('/admin/module/remove_popup/' . $module_mid . '/' . $item_id . '/');
        $form->set_classes(['ajax_form', 'remove']);
        $form->add_submit('remove', '<i class="far fa-trash-alt"></i>Remove ' . $title);

        return $form->render();
    }

    public function remove_popup($module_mid, $item_id)
    {
        if ($item_id && $module_mid && $this->module->retrieve($module_mid)) {
            $confirm_form = new admin_form_builder('confirm_remove_form');
            $confirm_form->set_action('/admin/module/remove_process/' . $module_mid . '/' . $item_id . '/');
            $confirm_form->add_submit('confirm', 'Remove ' . $this->module->title_single);
            $form = $confirm_form->render();
        } else {
            $form = 'Error.';
        }

        $this->add_data([
            'message' => 'Are you sure you want to remove the item? Note: You can set an items "Live" status to "No" if you do not want it to appear on the website but still want to keep the data here.',
            'confirm_form' => $form,
        ]);

        $this->set_view('parts/confirm_popup');
        $this->build_view([/*'layout' => true, */'json' => true]);
    }

    public function remove_process($module_mid, $item_id)
    {
        $errors = [];

        if ($item_id && $module_mid && $this->module->retrieve($module_mid)) { // get module

            $this->load_model('admin_module_item', 'module_item', $this->module->dbslug);

            if ($this->module_item->retrieve($item_id)) { // get item

                $this->module_item->live = 0;
                $this->module_item->removed = 1;
                if ($this->module_item->save()) { // soft remove item
                    $json_data = ['redirect' => '/admin/module/' . $this->module->slug];
                    $this->add_flash($this->module->title_single . ' Successfully Deleted');
                    $this->log('Deleted Item', 'Deleted item - module_id: ' . $module_mid . ' item_id: ' . $item_id);
                } else {
                    $this->log('Failed to remove Item', 'Failed to remove item - module_id: ' . $module_mid . ' item_id: ' . $item_id);
                    $errors[] = 'Failed to remove item.';
                }
            } else {
                $this->log('Failed to remove Item', 'Failed to find item to remove - module_id: ' . $module_mid . ' item_id: ' . $item_id);
                $errors[] = 'Failed to remove item.';
            }
        } else {
            $this->log('Failed to remove Item', 'Failed to find module to remove item from - module_id: ' . $module_mid . ' item_id: ' . $item_id);
            $errors[] = 'Failed to remove item.';
        }

        if (!empty($errors)) {
            $json_data = ['success' => false, 'errors' => $errors];
        }

        $this->add_data(['json' => $json_data]);

        $this->build_view([/*'layout' => false, */'json' => true]);
    }

    private function restore_form($module_mid, $item_id, $title)
    {
        $form = new admin_form_builder('restore_form');
        $form->set_action('/admin/module/restore_popup/' . $module_mid . '/' . $item_id . '/');
        $form->set_classes(['ajax_form', 'restore']);
        $form->add_submit('restore', '<i class="fas fa-trash-restore"></i>Restore ' . $title);

        return $form->render();
    }


    public function restore_popup($module_mid, $item_id)
    {
        if ($item_id && $module_mid && $this->module->retrieve($module_mid)) {
            $confirm_form = new admin_form_builder('confirm_restore_form');
            $confirm_form->set_action('/admin/module/restore_process/' . $module_mid . '/' . $item_id . '/');
            $confirm_form->add_submit('confirm', 'Restore ' . $this->module->title_single);
            $form = $confirm_form->render();
        } else {
            $form = 'Error.';
        }

        $this->add_data([
            'message' => 'Are you sure you want to restore the item? The Item will be restored as "un-live" so it must be set to live to appear on the website.',
            'confirm_form' => $form,
        ]);

        $this->set_view('parts/confirm_popup');
        $this->build_view([/*'layout' => true, */'json' => true]);
    }

    public function restore_process($module_mid, $item_id)
    {
        $errors = [];

        if ($item_id && $module_mid && $this->module->retrieve($module_mid)) { // get module

            $this->load_model('admin_module_item', 'module_item', $this->module->dbslug);

            if ($this->module_item->retrieve($item_id)) { // get item

                $this->module_item->live = 0;
                $this->module_item->removed = 0;
                if ($this->module_item->save()) {
                    $json_data = ['redirect' => '/admin/module/manage/' . $this->module->slug . '/' . $item_id];
                    $this->add_flash($this->module->title_single . ' Successfully Restored');
                    $this->log('Restored Item', 'Restored item - module_id: ' . $module_mid . ' item_id: ' . $item_id);
                } else {
                    $this->log('Failed to restore Item', 'Failed to restore item - module_id: ' . $module_mid . ' item_id: ' . $item_id);
                    $errors[] = 'Failed to restore item.';
                }
            } else {
                $this->log('Failed to restore Item', 'Failed to find item to restore - module_id: ' . $module_mid . ' item_id: ' . $item_id);
                $errors[] = 'Failed to restore item.';
            }
        } else {
            $this->log('Failed to restore Item', 'Failed to find module to restore item from - module_id: ' . $module_mid . ' item_id: ' . $item_id);
            $errors[] = 'Failed to restore item.';
        }

        if (!empty($errors)) {
            $json_data = ['success' => false, 'errors' => $errors];
        }

        $this->add_data(['json' => $json_data]);

        $this->build_view([/*'layout' => false, */'json' => true]);
    }

    private function toggle_live_form($module_mid, $item_id)
    {
        $label = '<i class="live far fa-check-circle"></i><i class="unlive far fa-times-circle"></i>';

        if ($item_id && $module_mid) { // get module
            $this->load_model('admin_module_item', 'module_item', $this->module->dbslug);

            if ($this->module_item->retrieve($item_id)) { // get item

                if (!$this->module_item->locked || $this->currentuser->is_developer()) {
                    $form = new admin_form_builder('toggle_live_item_form');
                    $form->set_action('/admin/module/toggle_status/live/' . $module_mid . '/' . $item_id . '/');
                    $form->set_classes(['ajax_form', 'toggle_form', 'toggle_live_form']);
                    $form->add_submit('toggle', $label);

                    return $form->render();
                }
            }
        }

        return '<div class="toggle_form toggle_live_form">' . $label . '</div>';
    }

    private function toggle_lock_form($module_mid, $item_id)
    {
        $label = '<i class="locked fas fa-lock"></i><i class="unlocked fas fa-lock-open"></i>';

        if ($this->currentuser->is_developer()) {
            $form = new admin_form_builder('toggle_lock_item_form');
            $form->set_action('/admin/module/toggle_status/lock/' . $module_mid . '/' . $item_id . '/');
            $form->set_classes(['ajax_form', 'toggle_form', 'toggle_lock_form']);
            $form->add_submit('toggle', $label);

            return $form->render();
        }

        return '<div class="toggle_form toggle_lock_form">' . $label . '</div>';
    }

    public function toggle_status($type, $module_mid, $item_id)
    {
        if ($type == 'live' || $type == 'lock' && $this->currentuser->is_developer()) {

            if ($item_id && $module_mid && $this->module->retrieve($module_mid)) { // get module
                $this->load_model('admin_module_item', 'module_item', $this->module->dbslug);

                if ($this->module_item->retrieve($item_id)) { // get item
                    if ($type == 'live') {
                        if ($this->module_item->live) {
                            $this->module_item->live = 0;
                            $classes = ['add' => 'unlive', 'remove' => 'live'];
                        } else {
                            $this->module_item->live = 1;
                            $classes = ['add' => 'live', 'remove' => 'unlive'];
                        }
                    } else {
                        if ($this->module_item->locked) {
                            $this->module_item->locked = 0;
                            $classes = ['add' => 'unlocked', 'remove' => 'locked'];
                        } else {
                            $this->module_item->locked = 1;
                            $classes = ['add' => 'locked', 'remove' => 'unlocked'];
                        }
                    }
                    if ($this->module_item->save()) {
                        $json_data = ['success' => $classes];
                    } else {
                        $this->log('Failed to update ' . $type . ' status', 'Failed to update ' . $type . ' status - module_id: ' . $module_mid . ' item_id: ' . $item_id);
                        $errors[] = 'Failed to update item ' . $type . ' status.';
                    }
                } else {
                    $this->log('Failed to update ' . $type . ' status', 'Failed to update ' . $type . ' status, could not find item to update - module_id: ' . $module_mid . ' item_id: ' . $item_id);
                    $errors[] = 'Failed to update item ' . $type . ' status.';
                }
            } else {
                $this->log('Failed to update ' . $type . ' status', 'Failed to update ' . $type . ' status, could not find module  - module_id: ' . $module_mid . ' item_id: ' . $item_id);
                $errors[] = 'Failed to update item ' . $type . ' status.';
            }
        } else {
            $this->log('Failed to update item status', 'Failed to update "' . $type . '" status, invalid type (or user was not a developer) - module_id: ' . $module_mid . ' item_id: ' . $item_id);
            $errors[] = 'Failed to update item status.';
        }

        if (!empty($errors)) {
            $json_data = ['success' => false, 'errors' => $errors];
        }

        $this->add_data(['json' => $json_data]);

        $this->build_view([/*'layout' => false, */'json' => true]);
    }
}
