<?php // Add admin app only fields to form builder

class admin_form_builder extends ipsCore_form_builder
{

    function validate_image_picker( $field ) {

    }

	function render_image_picker( $field, $args )
	{
        $current_item = 'No Image selected.';
	    $files_controller = ipsCore::get_additional_controller( 'files' );
        if ( $field['value'] ) {
            $current_item = $files_controller->preview_file($field['value'], $field['name'], true, true);
        }

		$this->form_html( '<fieldset id="field-' . $field['name'] . '" class="file-picker image">
            <label>' . $field['label'] . '</label>
			<div class="file-picker-selection row">' . $current_item . '</div>
			<button class="file-picker-button" data-name="' . $field['name'] . '" data-action="/admin/files/file_selection_popup/image/single/">Choose Image</button>
		</fieldset>' );
	}

    function validate_image_picker_multi( $field ) {

    }

	function render_image_picker_multi( $field, $args )
	{
        $current_items = 'No Images selected.';
        $files_controller = ipsCore::get_additional_controller( 'files' );
        if ( $field['value'] ) {
            $files = explode(',', $field['value']);
            if (!empty($files)) {
                $current_items = '';
                foreach( $files as $file ) {
                    $current_items .= $files_controller->preview_file($file, $field['name'], false, true);
                }
            }
        }

		$this->form_html( '<fieldset id="field-' . $field['name'] . '" class="file-picker image multi">
            <label>' . $field['label'] . '</label>
			<div class="file-picker-selection row">' . $current_items . '</div>
			<button class="file-picker-button" data-name="' . $field['name'] . '" data-action="/admin/files/file_selection_popup/image/multi/">Choose Images</button>
		</fieldset>' );
	}

    function validate_file_picker( $field ) {

    }

	function render_file_picker( $field, $args )
	{
        $current_item = 'No File selected.';
        $files_controller = ipsCore::get_additional_controller( 'files' );
        if ( $field['value'] ) {
            $current_item = $files_controller->preview_file($field['value'], $field['name']);
        }

		$this->form_html( '<fieldset id="field-' . $field['name'] . '" class="file-picker">
            <label>' . $field['label'] . '</label>
			<div class="container"><div class="file-picker-selection row">' . $current_item . '</div></div>
			<button class="file-picker-button" data-name="' . $field['name'] . '" data-action="/admin/files/file_selection_popup/file/single/">Choose File</button>
		</fieldset>' );
	}

    function validate_file_picker_multi( $field ) {

    }

	function render_file_picker_multi( $field, $args )
	{
        $current_items = 'No Files selected.';
        $files_controller = ipsCore::get_additional_controller( 'files' );
        if ( $field['value'] ) {
            $files = explode(',', $field['value']);
            if (!empty($files)) {
                $current_items = '';
                foreach( $files as $file ) {
                    $current_items .= $files_controller->preview_file($file, $field['name'], false);
                }
            }
        }

		$this->form_html( '<fieldset id="field-' . $field['name'] . '" class="file-picker multi">
            <label>' . $field['label'] . '</label>
			<div class="file-picker-selection row">' . $current_items . '</div>
			<button class="file-picker-button" data-name="' . $field['name'] . '" data-action="/admin/files/file_selection_popup/file/multi/">Choose Files</button>
		</fieldset>' );
	}
}
