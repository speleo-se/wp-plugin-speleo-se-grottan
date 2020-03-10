<?php

/**
 * Plugin Name: Speleo.se Grottan
 * Description: Ansluter till Grottan index ifrån Google Drive och gör det tillgänligt via short codes
 *
 *
 * Läs: https://developer.wordpress.org/plugins/plugin-basics/header-requirements/
 *
 *
 * Hur man kopplar ihop Google Drive:
 * https://www.youtube.com/watch?v=iTZyuszEkxI
 *
 * https://wordpress.stackexchange.com/questions/162240/custom-pages-with-plugin
 *
 * https://www.twilio.com/blog/create-google-sheets-database-php-app-sms-notifications
 
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


require_once 'TemplateLoader.php';
require_once 'PageGrottan.php';
require_once 'RepositorySpeleoSeGrottan.php';

class SpeleoSeGrottan {
	private $templateLoader;

	private $currentPage = null;

	public function __construct() {
		$this->templateLoader = new TemplateLoader();
		add_filter( 'do_parse_request', [ $this, 'dispatch' ], PHP_INT_MAX, 2 );

		add_action( 'loop_end', function( \WP_Query $query ) {
			if ( isset( $query->virtual_page ) && ! empty( $query->virtual_page ) ) {
				$query->virtual_page = NULL;
			}
		} );

		add_filter( 'the_permalink', function( $plink ) {
			global $post, $wp_query;
			if (
					$wp_query->is_page
					&& isset( $wp_query->virtual_page )
					&& $wp_query->virtual_page instanceof PageInterface
					&& isset( $post->is_virtual )
					&& $post->is_virtual
					) {
				$plink = home_url( $wp_query->virtual_page->getUrl() );
			}
			return $plink;
		} );
		// https://developer.wordpress.org/plugins/shortcodes/basic-shortcodes/
		// https://developer.wordpress.org/plugins/shortcodes/shortcodes-with-parameters/#complete-example
		$this->shortcodeGrottanInit();
	}

	/**
	 * Run on 'do_parse_request' and if the request is for one of the registered pages
	 * setup global variables, fire core hooks, requires page template and exit.
	 *
	 * @param boolean $bool The boolean flag value passed by 'do_parse_request'
	 * @param \WP $wp       The global wp object passed by 'do_parse_request'
	 */
	public function dispatch( $bool, \WP $wp ) {
		if ( $this->checkRequest() && $this->currentPage instanceof PageInterface ) {
			$this->templateLoader->init( $this->currentPage );
			$wp->virtual_page = $this->currentPage;
			do_action( 'parse_request', $wp );
			$this->setupQuery();
			do_action( 'wp', $wp );
			$this->templateLoader->load();
			exit();
		}
		return $bool;
	}

	private function checkRequest() {
		$path = trim( $this->getPathInfo(), '/' );
		if(preg_match('/^grottan\/(?<year>\d{4})($|\/(?<number>(\d-\d|\d)))/', $path, $matches) === 1) {
			$this->currentPage = new PageGrottan($matches['year'] ?? null, $matches['number'] ?? null);
			return true;
		}
		return false;
	}

	private function getPathInfo() {
		$home_path = parse_url( home_url(), PHP_URL_PATH );
		return preg_replace( "#^/?{$home_path}/#", '/', esc_url( add_query_arg(array()) ) );
	}

private function setupQuery() {
        global $wp_query;
        $wp_query->init();
        $wp_query->is_page       = TRUE;
        $wp_query->is_singular   = TRUE;
        $wp_query->is_home       = FALSE;
        $wp_query->found_posts   = 1;
        $wp_query->post_count    = 1;
        $wp_query->max_num_pages = 1;
        $posts = (array) apply_filters(
            'the_posts', array( $this->currentPage->asWpPost() ), $wp_query
        );
        $post = $posts[0];
        $wp_query->posts          = $posts;
        $wp_query->post           = $post;
        $wp_query->queried_object = $post;
        $GLOBALS['post']          = $post;
        $wp_query->virtual_page   = $post instanceof \WP_Post && isset( $post->is_virtual )
            ? $this->currentPage
            : NULL;
    }
	private $isLoggedInMember = null;
	/**
	 * http://docs.itthinx.com/document/groups/api/examples/
	 */
	private function isLoggedInMember() {
		if ($this->isLoggedInMember === null) {
			$this->isLoggedInMember = false;
			require_once( ABSPATH . 'wp-includes/pluggable.php' );
			if ( $group = Groups_Group::read_by_name( 'Foobar' ) ) {
				$this->isLoggedInMember = Groups_User_Group::read( get_current_user_id() , $group->group_id );
			}
		}
	return $is_a_member;
	}
	public function shortcodeGrottanInit() {
		add_shortcode('grottan', [$this, 'shortcodeGrottan']);
	}
	public function shortcodeGrottan($atts = [], $content = null) {
		// normalize attribute keys, lowercase
		$atts = array_change_key_case((array)$atts, CASE_LOWER);
				// override default attributes with user attributes
				$wporg_atts = shortcode_atts([
					'year' => null,
				]
				, $atts);
		
		$repo = new RepositorySpeleoSeGrottan();
		// start output
		$o = 'Här kommer alla Grottan på en och samma gång.<br>';
		$o .= '
				<form>
					<input type="text" placeholder="Sök fungerar inte än...">
					<select name="author">
						<option selected>Författarsök fungerar inte än</option>';
		foreach ($repo->getAllAuthors() as $id => $author) {
			$o .= '<option value="'.$id.'">'.$author['name'].' ('.$author['earliest']['year'].($author['earliest']['year'] <> $author['latest']['year'] ? '-'.$author['latest']['year'] : '' ).')</option>';
		}
		$o .= '
					</select>
				</form>
				';
// pdf to jpg.
// https://gist.github.com/umidjons/11037635
// Kolla även
// pdftoppm -l 1 -scale-to 150 -jpeg example.pdf > thumb.jpg


		foreach ($repo->getAllNumbers() as $year => $numbersYear) {
			$o .= '<h3>'.$year.'</h3>';
			foreach ($numbersYear as $number => $numbersYearNumber) {
				$o .= '<a href="/grottan/'.$year.'/'.$number.'">Grottan '.$year.' nr '.$number.'</a>';
				$o .= '<button';
				if ($this->isLoggedInMember()) {
				} else {
					 $o .= ' disabled title="Inloggade medlemar kan ladda ner en PDF av detta nummer."';
				}
				$o .= '>&#x1f5ce;</button>&nbsp;&nbsp;&nbsp;';
				//&#x21D3;&#x2B73;
				/*
				foreach ($numbersYearNumber['content'] as $article) {
					foreach(explode(';', $article['author']) as $author) {
						$allAuthors[trim($author)] = trim($author);
					}
				}
				*/
			}
		}

		//foreach($repo->getAllNumbers() as $year) {

		$o .= '<pre>'.print_r($repo->getAllNumbers(), true).'</pre>';
		// do something to $content
		// always return
		return $o;
	}

}

new SpeleoSeGrottan();

// plugin activation
//register_activation_hook( __FILE__, [ new CiviCRMSpeleoSeConfig(), 'configureCiviCRM' ] );
