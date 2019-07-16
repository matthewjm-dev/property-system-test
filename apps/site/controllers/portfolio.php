<?php // Site pages controller

ipsCore::requires_controller( 'controller' );
ipsCore::requires_model( ['portfolio', 'page'] );

class portfolio_controller extends site_controller
{
    // Construct
    public function __construct($controller, $additional = false)
    {
        $this->load_model('portfolio');

        parent::__construct($controller, $additional);
    }

    // Methods
    public function index( $slug = false )
    {
        if ( $slug ) {
            $this->portfolio( $slug );
        } else {
            $this->load_model('page');
            $this->page->retrieve(['slug' => 'portfolio']);

            $this->set_page_title($this->page->title);

            $portfolios = $this->portfolio->get_all();
            $portfolios_html = '';

            foreach ($portfolios as $portfolio) {
                $portfolios_html .= ipsCore::get_part('portfolio/preview', [
                    'title' => $portfolio->title,
                    'href' => '/portfolio/' . $portfolio->slug,
                    'preview' => $portfolio->snippet,
                ] );
            }

            $this->add_data([
                'title' => $this->page->title,
                'content' => $this->page->content,
                'portfolios' => $portfolios_html,
            ]);

            $this->get_layout();
            $this->build_view();
        }
    }

    private function portfolio( $slug ) {
        if ( $this->portfolio->retrieve( ['slug' => $slug] ) ) {
            $this->set_page_title($this->portfolio->title . ' - Portfolio');

            $this->add_data([
                'title' => $this->portfolio->title,
                'featured_image' => $this->get_image($this->portfolio->featured_image),
                'content' => $this->portfolio->content,
            ]);

            $this->get_layout();
            $this->set_view('portfolio/portfolio');
            $this->build_view();
        } else {
            $this->error404();
        }
    }
}
