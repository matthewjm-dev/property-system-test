<?php //output fields

ipsCore::requires_model(['model', 'module'], 'admin');

class fields_helper extends ipsCore_helper
{
    public function output_fields(&$form, $form_builder, $module_fields, $current_mid) {
        if (!empty($module_fields)) {
            foreach ($module_fields as $field) {
                if (($field_type = $form_builder::get_field_types($field->type)) && (!$field->item_link || $field->item_link == $current_mid)) {
                    $options = [];
                    $extra_args = [];

                    if (isset($field_type['link'])) {
                        if ($field_type['link']) {
                            $this->load_model('admin_module', 'link_module', 'admin_module');
                            $this->link_module->where($field->link)->retrieve();

                            $this->load_model('admin', 'link_items', $this->link_module->dbslug);
                            $items = $this->link_items->get_all();

                            // Module link field
                            $link_field_title = false;
                            if ((int)$field->link_field === 0) {
                                $link_field_title = 'title';
                            } else {
                                $link_field = $this->field->get(['mfid' => $field->link_field]);
                                if ($link_field !== false && $link_field->dbslug !== null) {
                                    $link_field_title = $link_field->dbslug;
                                }
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
                        } else {
                            $options = $form->show_field_options($field->options);
                        }
                    }

                    if (isset($field_type['file'])) {
                        if ($field_type['file']) {

                        } else {

                        }
                    }

                    $form->add_field($field->dbslug, $field->title, $field->type, array_merge([
                        'default' => $field->default,
                        'options' => $options,
                        'required' => $field->required,
                        'placeholder' => $field->placeholder,
                        'placeholder_selectable' => $field->placeholder_selectable,
                    ], $extra_args));
                }
            }
        }
    }
}
