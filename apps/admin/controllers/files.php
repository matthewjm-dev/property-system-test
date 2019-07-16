<?php // Site admin files controller

ipsCore::requires_controller('controller');
ipsCore::requires_model('file');

class files_controller extends admin_controller
{

    // Construct
    public function __construct($controller, $additional = false)
    {
        parent::__construct($controller, $additional);

        $this->load_model('admin_file', 'file');

        $this->set_page_title('View Files & Media');
    }

    // Methods
    public function index($current_page)
    {
        $this->get_layout();

        $this->set_breadcrumbs(['Dashboard' => '/admin', 'Files' . ($current_page > 1 ? ' ( Page ' . $current_page . ' )' : '') => false]);

        $files_data = $this->get_filtered_list(['model' => $this->file, 'current_page' => $current_page, 'where' => false]);
        $files = [];

        if (!empty($files_data)) {
            foreach ($files_data as $file_data) {
                $files[] = [
                    'afid' => $file_data->afid,
                    'path' => $file_data->path,
                    'title' => $file_data->title,
                    'type' => $file_data->type,
                ];
            }
        }

        $this->add_data([
            'title' => 'Manage Files',
            'content' => 'Here you can see and upload all files / media uploaded to the CMS',
            'create_file_href' => '/admin/files/create/file',
            'create_file_text' => '<span>Create</span> File',
            'create_image_href' => '/admin/files/create/image',
            'create_image_text' => '<span>Create</span> Image',
            'manage_file_href' => '/admin/files/file/',
            'manage_file_text' => 'View File',
            'files' => $files,
        ]);

        $this->build_view();
    }

    private function form($form_name, $process_url, $type, $text)
    {
        $form = new ipsCore_form_builder($form_name);
        $form->set_action('/admin/files/' . $process_url . ($this->file->afid ? '/' . $this->file->afid : ''));
        $form->set_classes(['ajax_form', 'main_form', 'edit']);

        $form->start_section('form_main');

        $form->add_text('title', 'Title', ['value' => $this->file->title, 'required' => true, 'placeholder' => 'Enter Title']);
        $form->add_text('alt', 'Alt', ['value' => $this->file->alt, 'placeholder' => 'Enter Alt Text']);
        if ($type == 'image') {
            $form->add_image('file', ($this->file->path ? 'Replacement ' : '') . 'Image Upload', ['value' => $this->file->path, 'placeholder' => 'Choose Image', 'preview' => $this->file->path]);
            if ($this->file->path) {
                $form->add_html('image-preview', '<fieldset class="image-preview"><p>Current Image Preview:</p><a href="' . $this->file->path . '" target="_blank"><img src="' . $this->file->path . '" /></a></fieldset>');
            }
        } else {
            $form->add_file('file', ($this->file->path ? 'Replacement ' : '') . 'File Upload', ['value' => $this->file->path, 'placeholder' => 'Choose File', 'preview' => $this->file->path]);
            if ($this->file->path) {
                $form->add_html('file-preview', '<fieldset class="file-preview"><p>Current Uploaded File:</p><a href="' . $this->file->path . '" target="_blank"></a></fieldset>');
            }
        }

        if ( $this->file->afid ) {
        	$form->add_html('file_details', '<fieldset id="file-details">
				<p><span>Upload Path:</span> ' . $this->file->path . '</p>
				<p><span>File Basename:</span> ' . $this->file->basename . '</p>
				<p><span>Extension:</span> ' . $this->file->extension . '</p>
				<p><span>Filename:</span> ' . $this->file->filename . '</p>' .
				( $this->file->img_width ? '<p><span>Image Width:</span> ' . $this->file->img_width . '</p>' : '' ) .
				( $this->file->img_height ? '<p><span>Image Height:</span> ' . $this->file->img_height . '</p>' : '' ) .
				'<p><span>File Size:</span> ' . ipsCore::$functions->format_bytes($this->file->size) . '</p>
			</fieldset>');
		}

        $form->end_section('form_main');
        $form->start_section('submit');
        if ($this->file->afid) {
            $form->add_html('add_another', '<a id="add-another" class="button" href="/admin/files/create/' . $type . '/"><i class="fas fa-plus-square"></i>Add Another</a>');
        }
        $form->add_submit('modify', '<i class="far fa-save"></i>' . ($this->file->afid ? 'Save' : 'Create') . ' ' . $text);
        $form->end_section('submit');

        return $form;
    }

    private function file_form()
    {
        return $this->form('file_form', 'file_process', 'file', 'File');
    }

    private function image_form()
    {
        return $this->form('image_form', 'image_process', 'image', 'Image');
    }

    public function file($file_id)
    {
        $this->manage('file', 'File', $file_id);
    }

    public function image($file_id)
    {
        $this->manage('image', 'Image', $file_id);
    }

    public function file_process($file_id = false)
    {
        $this->process('file', 'File', $file_id);
    }

    public function image_process($file_id = false)
    {
        $this->process('image', 'Image', $file_id);
    }

    public function create($type)
    {
        if ($type == 'image') {
            $text = 'Image';
            $form = $this->image_form()->render();
        } else {
            $text = 'File';
            $form = $this->file_form()->render();
        }

        $this->get_layout();
        $this->set_breadcrumbs(['Dashboard' => '/admin', 'Files' => '/admin/files', 'Create ' . $text => false]);

        $this->add_data([
            'title' => 'Create New ' . $text,
            'content' => 'Here you can create and upload a new ' . $text . '.',
            'form' => $form,
        ]);

        $this->set_view('files/file');
        $this->build_view();
    }

    private function manage($type, $text, $file_id)
    {
        if ($this->file->retrieve($file_id)) {
            if ($type == 'image') {
                $form = $this->file_form()->render();
            } else {
                $form = $this->image_form()->render();
            }
        } else {
            $this->add_flash('Could not find that ' . $text, 'error');
            ipsCore::$functions->redirect('admin/files');

            return false;
        }

        $this->get_layout();
        $this->set_breadcrumbs(['Dashboard' => '/admin', 'Files' => '/admin/files', 'Manage ' . $text => false]);

        $this->add_data([
            'title' => 'Manage ' . $text,
            'remove_form' => $this->remove_file_form($file_id, $text),
            'form' => $form,
        ]);

        $this->set_view('files/file');
        $this->build_view();
    }

    private function process($type, $text, $file_id = false)
    {
        $errors = [];

        if (!$file_id || ($file_id && $this->file->retrieve($file_id))) {

            if ($type == 'image') {
                $form = $this->file_form();
            } else {
                $form = $this->image_form();
            }

            $form->populate_form();
            $form->validate_form($errors);

            if (empty($errors)) {
                $return = [];
                $reload = false;
                if (!$file_id || ($file_id && !empty($form->get_field_value('file')))) {
                    $return = ipsCore_file_manager::do_upload_file('file', $type);
                    $reload = true;
                }
                if (!isset($return['errors'])) {
                    $this->file->modified = time();
                    if (!$file_id) {
                        $this->file->created = $this->file->modified;
                    }
                    $this->file->type = $type;
                    $this->file->user = $this->currentuser->uid;
                    $this->file->title = $form->get_field_value('title');
                    $this->file->alt = $form->get_field_value('alt');

                    if (isset($return['basename'])) {
                        $this->file->basename = $return['basename'];
                    }
                    if (isset($return['extension'])) {
                        $this->file->extension = $return['extension'];
                    }
                    if (isset($return['name'])) {
                        $this->file->filename = $return['name'];
                    }
                    if (isset($return['uploadto'])) {
                        $this->file->path = '/' . $return['uploadto'];
                    }
                    if (isset($return['size'])) {
                        $this->file->size = $return['size'];
                    }
                    if (isset($return['img_width'])) {
                        $this->file->img_width = $return['img_width'];
                    }
                    if (isset($return['img_height'])) {
                        $this->file->img_height = $return['img_height'];
                    }

                    if ($this->file->save()) {
                        if ($file_id) {
                            if (!$reload) {
                                $this->log('Modified ' . $text, 'File ID: ' . $this->file->afid);
                                $json_data = ['success' => $text . ' has been successfully modified!'];
                            } else {
                                $this->log('Modified ' . $text, 'File ID: ' . $this->file->afid);
                                $json_data = ['redirect' => '/admin/files/file/' . $this->file->afid];
                                $this->add_flash($text . ' has been successfully modified!');
                            }
                        } else {
                            $this->log('Created ' . $text, 'Field ID: ' . $this->file->afid);
                            $json_data = ['redirect' => '/admin/files/file/' . $this->file->afid];
                            $this->add_flash($text . ' Created Successfully!');
                        }
                    } else {
                        if ($file_id) {
                            $this->log('File Process Failed', $text . ' to Save Field.');
                            $errors[] = 'Failed to Save ' . $text . '.';
                        } else {
                            $this->log('File Process Failed', 'Failed to Create ' . $text . '.');
                            $errors[] = 'Failed to Create ' . $text . '.';
                        }
                    }
                } else {
                    $errors = array_merge($errors, $return['errors']);
                }
            }
        }

        if (!empty($errors)) {
            $json_data = ['success' => false, 'errors' => $errors];
        }

        $this->add_data(['json' => $json_data]);

        $this->build_view([/*'layout' => false, */'json' => true]);
    }

    public function remove_file_form($file_id, $text)
    {
        $form = new ipsCore_form_builder('remove_form');
        $form->set_action('/admin/files/remove_file_popup/' . $file_id . '/' . $text);
        $form->set_classes(['ajax_form', 'remove']);
        $form->add_submit('remove', '<i class="far fa-trash-alt"></i>Remove ' . $text);

        return $form->render();
    }

    public function remove_file_popup($file_id, $text)
    {
        $confirm_form = new ipsCore_form_builder('confirm_remove_form');
        $confirm_form->set_action('/admin/files/remove_file_process/' . $file_id);
        $confirm_form->add_submit('confirm', 'Remove ' . $text);

        $this->add_data([
            'message' => 'Are you sure you want to remove the ' . $text . '? This is a permanent action that cannot be reversed.',
            'confirm_form' => $confirm_form->render(),
        ]);

        $this->set_view('parts/confirm_popup');
        $this->build_view(['json' => true]);
    }

    public function remove_file_process($file_id)
    {
        $errors = [];

        if ($file_id && $this->file->retrieve($file_id)) {

            if (ipsCore_file_manager::do_delete_file($this->file->filename)) {

                if ($this->file->remove()) {
                    $json_data = ['redirect' => '/admin/files/'];
                    $this->add_flash('File Successfully Deleted File');
                    $this->log('Deleted Item', 'Deleted file - file_id: ' . $file_id);
                } else {
                    $this->log('Failed to remove Item', 'Failed to remove file - file_id: ' . $file_id);
                    $errors[] = 'Failed to remove item.';
                }
            } else {
                $this->log('Failed to remove Item', 'Failed to remove file - file_id: ' . $file_id);
                $errors[] = 'Failed to remove item.';
            }
        } else {
            $this->log('Failed to remove File', 'Failed to find file to remove - file_id: ' . $file_id);
            $errors[] = 'Failed to remove item.';
        }

        if (!empty($errors)) {
            $json_data = ['success' => false, 'errors' => $errors];
        }

        $this->add_data(['json' => $json_data]);

        $this->build_view(['json' => true]);
    }

    public function file_selection_popup($type, $arg, $name)
    {
        $type = ($type == 'file' ? 'file' : 'image');
        $arg = ($arg == 'multi' ? 'multi' : 'single');

        $single = ($arg == 'multi' ? false : true);
        $image = ($type == 'file' ? false : true);
        $text = ($image ? 'Image' : 'File');
        $text_singular = $text;
        $text_multiple = $text . 's';
        $text = ($single ? $text_singular : $text_multiple);

        $form = new ipsCore_form_builder('file_picker_form');
        $form->set_action('/admin/files/file_selection_process/' . $type . '/' . $arg . '/' . $name);

        $current_items = [];
        if (isset($_POST[$name])) {
            $current_items = $_POST[$name];
            if (!is_array($current_items)) {
                $current_items = [$current_items];
            }
        }

        $files = $this->file->where(['type' => $type])->get_all();
        $message = 'Select ' . ($single ? ($image ? 'an' : 'a') : '') . ' ' . $text . ' from the items shown below to insert.';
        $items = [];

        if (!empty($files)) {
            foreach ($files as $file) {
                if ($image) {
                    $preview = '<div class="file-picker-preview image" style="background-image:url(' . $file->path . '");" title="' . $file->title . '" />';
                } else {
                    $preview = '<p class="file-picker-preview">' . $file->title . '(<a href="' . $file->path . '" target="_blank">' . $file->path . '</a>)</p>';
                }

                $checked = '<div class="file-picker-item-check"><i class="far fa-check-square"></i></div>';
                $preview = '<div class="file-picker-item">' . $preview . $checked . '</div>';

                $items[] = [
                    'text' => $preview,
                    'value' => $file->afid,
                    'selected' => (in_array($file->get_id(), $current_items) ? true : false),
                ];
            }

            if (!empty($items)) {
                if ($single) {
                    $form->add_radio('file-picker-items', false, ['options' => $items]);
                } else {
                    $form->add_check('file-picker-items', false, ['options' => $items]);
                }
            }
        } else {
            $message = '<p>There are currently no ' . $text_multiple . ', upload a new ' . $text_singular . ' by <a href="/admin/files/create/">Clicking here</a>.</p>';
        }

        $form->add_submit('confirm', '<i class="fas fa-plus-square"></i>Add ' . $text);

        $this->add_data([
            'message' => $message,
            'upload_button' => '<a class="button upload" href="/admin/files/create/" target="_blank"><i class="fas fa-upload"></i>Upload<span> ' . $text_singular . '</span></a>',
            'manage_button' => '<a class="button manage" href="/admin/files/" target="_blank"><i class="far fa-edit"></i>Manage<span> ' . $text_multiple . '</span></a>',
            'selector_form' => $form->render(),
        ]);

        $this->set_view('files/file_picker_popup');
        $this->build_view([/*'layout' => true, */'json' => true]);
    }

    public function file_selection_process($type, $arg, $name)
    {
        $type = ($type == 'file' ? 'file' : 'image');
        $arg = ($arg == 'multi' ? 'multi' : 'single');

        $single = ($arg == 'multi' ? false : true);
        $image = ($type == 'file' ? false : true);
        $text = ($image ? 'Image' : 'File');
        $text .= ($single ? '' : 's');

        if (isset($_POST['file-picker-items']) && !empty($_POST['file-picker-items'])) {
            if ($single) {
                $items = [$_POST['file-picker-items'][0]];
            } else {
                $items = $_POST['file-picker-items'];
            }

            $items_html = '';
            foreach ($items as $item) {
                $items_html .= $this->preview_file($item, $name, $single, $image);
            }

            $this->add_data([
                'json' => ['html' => $items_html],
            ]);

            $this->build_view([/*'layout' => true, */'json' => true]);
        }
    }

    public function preview_file($file_id, $name, $single = true, $image = false)
    {
        $file = $this->file->reset()->get($file_id);

        if ($file) {
            $link_start = '<a class="file-picker-preview-item-link" href="' . $file->path . '" target="_blank" title="Remove">';
            $link_end = '</a>';
            $input = '<input type="hidden" name="' . $name . ($single ? '' : '[]') . '" value="' . $file_id . '" />';
            $remove = '<div class="file-picker-preview-item-remove a"><i class="far fa-trash-alt"></i></div>';

            if ($image) {
                return '<div class="file-picker-preview-item col col-lg-2 col-md-3 col-sm-6 col-6">' . $link_start . '<div class="file-picker-preview-item-image" style="background-image:url(' . $file->path . ');" title="' . $file->title . '"></div>' . $remove . $link_end . $input . '</div>';
            } else {
                return '<div class="file-picker-preview-item col col-12">' . $link_start . $file->title . $remove . $link_end . $input . '</div>';
            }
        }

        return false;
    }
}
