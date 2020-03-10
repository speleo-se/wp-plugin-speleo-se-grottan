<?php

require_once 'TemplateLoaderInterface.php';

/**
 * https://wordpress.stackexchange.com/questions/162240/custom-pages-with-plugin?newreg=3513dfe745d44936b0426fc37d4e56dd
 */
class TemplateLoader implements TemplateLoaderInterface {
	private $templates;

	public function init( PageInterface $page ) {
		$this->templates = wp_parse_args(
				array( 'page.php', 'index.php' ), (array) $page->getTemplate()
		);
	}

	public function load() {
		do_action( 'template_redirect' );
		$template = locate_template( array_filter( $this->templates ) );
		$filtered = apply_filters( 'template_include',
				apply_filters( 'virtual_page_template', $template )
		);
		if ( empty( $filtered ) || file_exists( $filtered ) ) {
			$template = $filtered;
		}
		if ( ! empty( $template ) && file_exists( $template ) ) {
//			die($template);
			require_once $template;
		}
	}
}
