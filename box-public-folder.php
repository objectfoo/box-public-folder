<?php 
/*
Plugin Name: Box Public Folder
URI: http://nwempire.com
Description: Plugin to show the last X changes to a public box.com folder, ordered by modification date via RSS
Version: 0.3.0
Author: SatakeST
Author URL: http://objectfoo.com
License: GPLv2
*/

class WPBoxPublicFolder {
    const SETTINGS_ID       = 'BPF_SETTINGS_ID';
    const KEY_COUNT         = 'BPF_COUNT';
    const KEY_URI           = 'BPF_URI';
    const DEFAULT_COUNT     = 10;
    const DEFAULT_URI       = '';
    const NONCE_SEED        = 'O-t$$ D|d*JQ3Hb[xWhFpIZ,hQkf86Q7fA_3q[tsJf|(>+?H56Z&:)[,)~vg6DhZ';

	// php 4 constructor
    public function WPBoxPublicFunction() { $this-->__construct(); }

    // php 5 constructor
	function __construct() {
        add_action ('admin_init', array ($this, 'setup_settings') );
        add_action ('admin_menu', array ($this, 'admin_menu') );
        add_action ('init', array ($this, 'register_assets') );
        add_shortcode ('box-com', array ($this, 'shortcode') );
        add_action ('wp_ajax_BPF_get_public_folder', array ($this, 'ajax_get_public_folder') );
	}
	
	// respond to ajax request
	function ajax_get_public_folder() {
        check_ajax_referer (self::NONCE_SEED);
        $opts = $this->get_options();
        include 'BoxDotComAPI.php';
        $boxAPI = new BoxDotComAPI ($opts[self::KEY_URI]); // options

        header( "Content-Type: application/json" );
        echo $boxAPI->getPublicFolder ($opts[self::KEY_COUNT]);
	    die();
	}
	
	// activate plugin
	function activate() {
        $this->get_options();
	}
	
	// register script and style requirements
	function register_assets() {

	    wp_register_script (
	        'BPF_scripts',                                      // script alias
	        plugins_url ('box-public-folder.js', __FILE__),     // path
	        array ('jquery'),                                   // dependancies
	        '1.0',                                              // version
	        true );                                             // load in footer?

            $nonce = wp_create_nonce (self::NONCE_SEED);
            $protocol = isset ($_SERVER["HTTPS"]) ? 'https://' : 'http://';
            $params = array(
                'ajaxurl'       => admin_url ('admin-ajax.php', $protocol),
                'action'        => 'BPF_get_public_folder',
                '_ajax_nonce'   => $nonce );

            wp_localize_script ('BPF_scripts', 'BPF_params', $params);
        
            wp_register_style(
                'BPF_styles', 
                plugins_url( 'box-public-folder.css', __FILE__ ),
                array(),
                '1.0',
                'all' );
	}
    
    // handle the shortcode
    function shortcode($atts) {
        wp_enqueue_script ('BPF_scripts');
        wp_enqueue_style ('BPF_styles');
        $opts = get_option( self::SETTINGS_ID );

        $html = array();
        $html[]= '<div id="box-public-folder">';
        $html[]= '<ul id="box-public-folder-file-list">';
        $html[]= sprintf('<li class="chrome"><a class="feed-link" href="%s">Go To Community Folder &raquo;</a></li>',
            $opts[self::KEY_URI]);
        $html[]= sprintf('<li id="box-loading"><img style="display:block; margin: 1em auto;" src="%s"></li>',
            plugins_url('img/loading.gif', __FILE__));
        $html[]=  '</ul>';
        $html[]= '</div>';

        return implode("\n", $html);
    }
    
    // get options from wp
    function get_options() {
      $defaults = array (
        self::KEY_COUNT   => self::DEFAULT_COUNT,
	        self::KEY_URI     => self::DEFAULT_URI );
	    $opts = get_option (self::SETTINGS_ID);
	    if (!empty ($opts) ) {
	        foreach ($opts as $key => $val) {
	            $defaults[$key] = $val;
	        }
	    }
	    update_option (self::SETTINGS_ID, $defaults);

	    return $defaults;
	}

    // Administration
	function admin_menu() {
		add_options_page (
		    'Box.com Public Folder',
		    'Box.com Public Folder',
            'manage_options',
            __FILE__,
            array ($this, 'settings_page') );
	}

    function settings_page() { ?>
    <div class="wrap">
        <?php screen_icon() ?>
        <h2>Box.com Public Folder</h2>
        <form action="options.php" method="post">
            <?php settings_fields( self::SETTINGS_ID ) ?>
            <?php do_settings_sections( __FILE__ ) ?>
            <p class="submit">
                <input type="submit" 
                class="button-primary"
                name="Submit"
                value="<?php esc_attr_e('Save Changes') ?>" />
            </p>
        </form>
    </div>
    <?php
    }

    function setup_settings() {
        // Settings Section
	    add_settings_section ('main', 'Settings', array ($this, 'section_text'), __FILE__ );

        // Settings Fields
        add_settings_field (
            self::KEY_COUNT,
            'Number of files to show',
            array ($this, 'count_field'),
            __FILE__,
            'main' );

        add_settings_field (
            self::KEY_URI,
            'box.com public folder RSS Feed URI',
            array ($this, 'uri_field'),
            __FILE__,
            'main' );

        // register settings
        register_setting (self::SETTINGS_ID, self::SETTINGS_ID, Array($this, 'validate'));
    }
    
    function validate( $opts ) {
      $valid = array();
      $valid[self::KEY_COUNT] = DEFAULT_COUNT;
      $valid[self::KEY_URI] = DEFAULT_URI;
      
      // count
      $opts[self::KEY_COUNT] = trim( $opts[self::KEY_COUNT]);
      $valid[self::KEY_COUNT] = intval($opts[self::KEY_COUNT]);
      
      // URI
      $opts[self::KEY_URI] = preg_replace('/\/rss\.xml$/', '', trim($opts[self::KEY_URI]));
      $valid[self::KEY_URI] = $opts[self::KEY_URI];
      
      return $valid;
    }    
    
    function section_text() {
        echo '';
    }

    function count_field() {
	    $opts = get_option( self::SETTINGS_ID );
	    printf( '<input id="%1$s" name="%2$s[%1$s]" class="small-text" type="text" value="%3$s" />',
	        self::KEY_COUNT,
	        self::SETTINGS_ID,
	        $opts[self::KEY_COUNT] );
	    echo '<span class="description"></span>';
    }

    function uri_field() {
	    $opts = get_option( self::SETTINGS_ID );
	    printf( '<input id="%1$s" class="regular-text" name="%2$s[%1$s]" type="text" value="%3$s" />',
	        self::KEY_URI,
	        self::SETTINGS_ID,
	        $opts[self::KEY_URI] );
	    echo '<span class="description"></span>';
    }
}

$wp_bpf = new WPBoxPublicFolder;
if (isset ($wp_bpf) ) {
    register_activation_hook (__FILE__, array ($wp_bpf, 'activate'));
}