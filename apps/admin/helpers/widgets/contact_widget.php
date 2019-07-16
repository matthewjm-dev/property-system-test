<?php // Contact Widget

ipsCore::requires_model('module_item');

class contact_widget extends dashboard_widget
{
    protected $widget_title = 'Contact form Submissions';

    public function build()
    {
        $this->load_model('admin_module_item', 'module_item', 'contact_form_submissions');

        $contacts = $this->module_item->get_all();

        $count_all = count($contacts);
        $count_live = 0;
        $count_unlive = 0;
        $count_locked = 0;

		if (!empty($contacts)) {
			foreach ($contacts as $contact) {
				if ($contact->live) {
					$count_live++;
				} else {
					$count_unlive++;
				}

				if ($contact->locked) {
					$count_locked++;
				}
			}
        }

        $this->add_widget_data([
            'url' => '/admin/module/contact-form-submissions/',
            'icon' => 'fas fa-clone',
            'count_all' => $count_all . " Total",
            'count_live' => $count_live . " Live",
            'count_unlive' => $count_unlive . " Un-Live",
            'count_locked' => $count_locked . " Locked",
        ]);
    }

}
