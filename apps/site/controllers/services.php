<?php // Site pages controller

ipsCore::requires_controller( 'controller' );
ipsCore::requires_model( ['service', 'page'] );

class services_controller extends site_controller
{
	// Construct
	public function __construct($controller, $additional = false)
	{
		$this->load_model('service');

		parent::__construct($controller, $additional);
	}

	// Methods
	public function index( $slug = false )
	{
		if ( $slug ) {
			$this->service( $slug );
		} else {
            $this->load_model('page');
            $this->page->retrieve(['slug' => 'services']);

            $this->set_page_title($this->page->title);

            $services = $this->service->get_all();
            $services_html = '';

            foreach ($services as $service) {
                $services_html .= ipsCore::get_part('services/preview', [
                    'title' => $service->title,
                    'icon' => $service->icon,
                    'href' => '/services/' . $service->slug,
                    'preview' => $service->snippet,
                ] );
            }

            $this->add_data([
                'title' => $this->page->title,
                'content' => $this->page->content,
                'services' => $services_html,
            ]);

            $this->get_layout();
            $this->build_view();
		}
	}

	private function service( $slug ) {
		if ( $this->service->retrieve( ['slug' => $slug] ) ) {
			$this->set_page_title($this->service->title . ' - Service');

			$this->add_data([
				'title' => $this->service->title,
				'icon' => $this->service->icon,
				'content' => $this->service->content,
			]);

			$this->get_layout();
            $this->set_view('services/service');
			$this->build_view();
		} else {
			$this->error404();
		}
	}
}
