<?php // Site pages controller

ipsCore::requires_controller( 'controller' );
ipsCore::requires_model( ['blog', 'page'] );

class blog_controller extends site_controller
{
	// Construct
	public function __construct($controller, $additional = false)
	{
		$this->load_model('blog');

		parent::__construct($controller, $additional);
	}

	// Methods
	public function index( $slug = false, $page = false )
	{
		if ( $slug && $slug != 'page' ) {
			$this->post( $slug );
		} else {
			if ( !$page ) {
				$page = 1;
			}
			$this->page($page);
		}
	}

	private function post( $slug ) {
		if ( $this->blog->retrieve( ['slug' => $slug] ) ) {
			$this->set_page_title($this->blog->title . ' - Blog');

			$this->add_data([
				'title' => $this->blog->title,
				'content' => $this->blog->content,
			]);

			$this->get_layout();
            $this->set_view('blog/post');
			$this->build_view();
		} else {
			$this->error404();
		}
	}

	public function page( $page ) {
		$this->load_model('page');
		$this->page->retrieve(['slug' => 'blog']);

		$this->set_page_title($this->page->title);

		$posts = $this->get_filtered_list(['model' => $this->blog, 'current_page' => $page, 'slug' => 'page']);
		$posts_html = '';

		foreach ($posts as $post) {
		    $featured_image = $this->get_image($post->featured_image, true);
		    if ( $featured_image ) {
                $featured_image = $featured_image->path;
            } else {
                $featured_image = '';
            }
			$posts_html .= ipsCore::get_part('blog/preview', [
			    'featured_image_href' => $featured_image,
			    'title' => $post->title,
                'href' => '/blog/' . $post->slug,
                'preview' => $post->preview,
            ] );
		}

		$this->add_data([
			'title' => 'Blog Page',
			'content' => $this->page->content,
			'posts' => $posts_html,
		]);

		$this->get_layout();
		$this->build_view();
	}
}
