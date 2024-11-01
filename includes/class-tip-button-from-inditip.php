<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Tip_button_from_IndiTip {

	/**
	 * The single instance of Tip_button_from_IndiTip.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * Settings class object
	 * @var     object
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = null;

	/**
	 * The version number.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_version;

	/**
	 * The token.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_token;

	/**
	 * The main plugin file.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $file;

	/**
	 * The main plugin directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $dir;

	/**
	 * The plugin assets directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_dir;

	/**
	 * The plugin assets URL.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_url;

	/**
	 * Suffix for Javascripts.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $script_suffix;

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct ( $file = '', $version = '1.0.0' ) {
		$this->_version = $version;
		$this->_token = 'tip_button_from_inditip';

		// Load plugin environment variables
		$this->file = $file;
		$this->dir = dirname( $this->file );
		$this->assets_dir = trailingslashit( $this->dir ) . 'assets';
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );

		$this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		register_activation_hook( $this->file, array( $this, 'install' ) );

		// Load frontend JS & CSS
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 10 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );

		// Load admin JS & CSS
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1 );

		// Load API for generic admin functions
		if ( is_admin() ) {
			$this->admin = new Tip_button_from_IndiTip_Admin_API();
		}

		// Handle localisation
		$this->load_plugin_textdomain();
		add_action( 'init', array( $this, 'load_localisation' ), 0 );
		add_action( 'init', array( $this, 'setup_iframe' ), 0 );
		add_shortcode( 'inditip', array( $this, 'inditip_button_shortcode' ) );		
	} // End __construct ()

	/**
	 * Wrapper function to register a new post type
	 * @param  string $post_type   Post type name
	 * @param  string $plural      Post type item plural name
	 * @param  string $single      Post type item single name
	 * @param  string $description Description of post type
	 * @return object              Post type class object
	 */
	public function register_post_type ( $post_type = '', $plural = '', $single = '', $description = '', $options = array() ) {

		if ( ! $post_type || ! $plural || ! $single ) return;

		$post_type = new Tip_button_from_IndiTip_Post_Type( $post_type, $plural, $single, $description, $options );

		return $post_type;
	}

	/**
	 * Wrapper function to register a new taxonomy
	 * @param  string $taxonomy   Taxonomy name
	 * @param  string $plural     Taxonomy single name
	 * @param  string $single     Taxonomy plural name
	 * @param  array  $post_types Post types to which this taxonomy applies
	 * @return object             Taxonomy class object
	 */
	public function register_taxonomy ( $taxonomy = '', $plural = '', $single = '', $post_types = array(), $taxonomy_args = array() ) {

		if ( ! $taxonomy || ! $plural || ! $single ) return;

		$taxonomy = new Tip_button_from_IndiTip_Taxonomy( $taxonomy, $plural, $single, $post_types, $taxonomy_args );

		return $taxonomy;
	}

	/**
	 * Load frontend CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return void
	 */
	public function enqueue_styles () {
		wp_register_style( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'css/frontend.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-frontend' );
	} // End enqueue_styles ()

	/**
	 * Load frontend Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function enqueue_scripts () {
		wp_register_script( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'js/frontend' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
		wp_enqueue_script( $this->_token . '-frontend' );
	} // End enqueue_scripts ()

	/**
	 * Load admin CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_styles ( $hook = '' ) {
		wp_register_style( $this->_token . '-admin', esc_url( $this->assets_url ) . 'css/admin.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-admin' );
	} // End admin_enqueue_styles ()

	/**
	 * Load admin Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_scripts ( $hook = '' ) {
		wp_register_script( $this->_token . '-admin', esc_url( $this->assets_url ) . 'js/admin' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
		wp_enqueue_script( $this->_token . '-admin' );
	} // End admin_enqueue_scripts ()

	/**
	 * Load plugin localisation
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_localisation () {
		load_plugin_textdomain( 'tip-button-from-inditip', false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_localisation ()

	/**
	 * Load plugin textdomain
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain () {
	    $domain = 'tip-button-from-inditip';

	    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

	    load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
	    load_plugin_textdomain( $domain, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_plugin_textdomain ()

	/**
	 * Main Tip_button_from_IndiTip Instance
	 *
	 * Ensures only one instance of Tip_button_from_IndiTip is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see Tip_button_from_IndiTip()
	 * @return Main Tip_button_from_IndiTip instance
	 */
	public static function instance ( $file = '', $version = '1.0.0' ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $file, $version );
		}
		return self::$_instance;
	} // End instance ()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __wakeup ()

	/**
	 * Installation. Runs on activation.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function install () {
		$this->_log_version_number();
	} // End install ()

	/**
	 * Log the plugin version number.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number () {
		update_option( $this->_token . '_version', $this->_version );
	} // End _log_version_number ()

	/**
	 * Register the_content hook
	 * @access  public
	 * @since   1.0.0
	 * @since   1.1.0 Conditional on value of "inditip_placement" setting
	 * @return  void
	 */
	public function setup_iframe () {
		if( get_option( 'inditip_placement' ) == 'end' ) {
			add_action( 'the_content', array( $this, 'append_iframe' ) );		
		}
	} // End setup_iframe ()

	/**
	 * Append tip button to post
	 * @access  public
	 * @since   1.1.0
	 * @since   1.1.1 removed is_single requirement
	 * @return  void
	 */
	public function append_iframe ( $content ) {
		$button = ( in_the_loop( ) ? $this->generate_button_html( ) : '');
	    ob_start();
	    eval('?>' . $content . $button);
    	ob_end_flush();
	}

	/**
	 * Enable shortcode for IndiTip button.
	 * @access  public
	 * @since   1.1.0
	 * @since   1.1.1 removed is_single requirement
	 * @return  String
	 */
	public function inditip_button_shortcode ( $atts, $content = null ) {
		return ( in_the_loop( ) ? $this->generate_button_html( ) : '');
	} // End inditip_button_shortcode ()

	/**
	 * Construct IndiTip button html.
	 * @access  private
	 * @since   1.1.0
	 * @return  HTML for IndiTip button's iframe
	 */
	private function generate_button_html () {
		// IndiTip needs these to register the tip
		$userid = get_option( 'inditip_userid' );

		$src = 'https://bigbutton.inditip.com/';
		$src .= '?o=' . urlencode( $userid );
		$src .= '&wt=' . urlencode( get_the_title( ) );
		$src .= '&wa=' . urlencode( get_the_author( ) );
		$src .= '&wl=' . urlencode( get_permalink( ) );

		// Style the inditip box
		$style = 'border:none;border-radius:0px;margin:0px;';

		$iframe_id = uniqid();
		$src .= '&ifid=' . $iframe_id;

		$inditipbox = '<iframe ';
		$inditipbox .= ' id="' . $iframe_id . '"';
		$inditipbox .= ' src="' . $src . '"';
		$inditipbox .= ' style="' . $style . '"';
        $inditipbox .= ' onmouseover="this.contentWindow.postMessage({action:\'mouseover\'},\'https://bigbutton.inditip.com\')"';
		$inditipbox .= '>';
		$inditipbox .= '
		<h3 style="text-align:center;font-family:Courier;">
			<a href="https://inditip.com" target="_blank">IndiTip</a> lets you support this article with a ₹5 tip.
		</h3>
		<h4 style="text-align:center;font-family:Courier;">
			Record your tip with a button click, and pay immediately or when convenient to you.
		</h4>
		<h4 style="text-align:center;font-family:Courier;">
			The website owner has placed an IndiTip button in this box.
		</h4>
		<h3 style="text-align:center;font-family:Courier;">
			You will need to <a href="http://bfy.tw/BSzu" target="_blank">enable iframes</a> in your browser\'s settings to activate it.
		</h3>
		';
		$inditipbox .= '</iframe>';

		return $inditipbox;

	} // End generate_button_html ()

}
