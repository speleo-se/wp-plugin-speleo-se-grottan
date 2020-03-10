<?php

require_once 'PageInterface.php';

class PageGrottan implements PageInterface {

    private $wp_post = null;

	private $numbers = [];
	private $year;
	private $number;

    public function __construct( $year = null, $number = null) {
		$this->year = $year;
		$this->number = $number;

		require 'testdata/data.php';
    }

    public function getUrl() {
        return '/grottan'.( $this->year ? '/'.$this->year.( $this->number ? '/'.$this->number : '') : '');
    }

    public function getTemplate() {
        return 'page.php';
    }

    public function getTitle() {
		return 'Tidskriften Grottan'.( $this->year ? ' '.$this->year.( $this->number ? ' nr '.$this->number : '') : '');
    }

	private function getContent() {
		$re = '';
		if (!$this->year) {
			foreach($this->numbers as $key => $value) {
				$re .= '<h2><a href="/grottan/'.$key.'/">Grottan '.$key.'</a></h2>';

			}
		} elseif (!$this->number) {
			foreach($this->numbers[$this->year] as $key => $value) {
				$re .= '<h2><a href="/grottan/'.$this->year.'/'.$key.'/">Grottan '.$key.'</a></h2>';
			}
		} else {
			foreach($this->numbers[$this->year][$this->number]['content'] as $value) {
				$re .= '<h2>'.$value['title'].'</h2>';
				$re .= '<p>';
				$re .= '<strong>Författare:</strong> '.$value['author'].'<br>';
				$re .= '<strong>Sidhänvisning:</strong> '.$value['pages'].'<br>';
			}
		}
		return $re;

	}
//    function setTitle( $title ) {
//        $this->title = filter_var( $title, FILTER_SANITIZE_STRING );
//        return $this;
//    }

//    function setContent( $content ) {
//        $this->content = $content;
//        return $this;
//    }

//    function setTemplate( $template ) {
//        $this->template = $template;
//        return $this;
//    }

	public function asWpPost() {
		if ( is_null( $this->wp_post ) ) {
			$post = array(
					'ID'             => 1, // Groups-pluginen döljer innehållet om == false. Använder 1 istället för 0
					'post_title'     => $this->getTitle(),
					'post_name'      => sanitize_title( $this->getTitle() ),
					'post_content'   => $this->getContent() ? : '',
					'post_excerpt'   => '',
					'post_parent'    => 0,
					'menu_order'     => 0,
					'post_type'      => 'page',
					'post_status'    => 'publish',
					'comment_status' => 'closed',
					'ping_status'    => 'closed',
					'comment_count'  => 0,
					'post_password'  => '',
					'to_ping'        => '',
					'pinged'         => '',
					'guid'           => home_url( $this->getUrl() ),
					'post_date'      => current_time( 'mysql' ),
					'post_date_gmt'  => current_time( 'mysql', 1 ),
					'post_author'    => is_user_logged_in() ? get_current_user_id() : 0,
					'is_virtual'     => true,
					'filter'         => 'raw'
					);
			$this->wp_post = new \WP_Post( (object) $post );
		}
		return $this->wp_post;
	}
}