<?php
session_start();

/**
 * Functions I use to aid myself in the development of a website.
 *
 * Disclaimer:
 * Use it at your own risk, most functions were tested fully,
 * some have not been tested completely, and not all of them 
 * log errors, feel free to collaborate if you find any bugs.
 * 
 * @author Johnny Calderon
 *
 * CONTENT:
 * 	- Constants and Variables
 * 	- Configuration Functions
 * 	- Site Data Functions
 * 	- Markup Generator Functions
 * 	- File Handler Functions
 * 	- Analytic Functions
 * 	- Social Media Functions
 *  - Article / Blog Functions
 *  - Classes
 *  - Error Handler Functions
 */

/*******************************************/
/*         CONSTANTS AND VARIABLES         */
/*******************************************/

/**
 * Used for checking processing time of your whole website.
 * Echo get_process-time() in the last line of your last
 * included file to get the processing time as whole.
 */
define( 'START_TIME', microtime( true ) );

/**
 * ---- IMPORTANT!!!!!!!!!
 * Set PRODUCTION to true when deploying the site to production
 */
define( 'PRODUCTION', false );

/**
 * Store configuration parameters in variable
 */
$config_params = break_config();

/**
 * The root directory of your website will be chosen according
 * to PRODUCTION constant, production path on true, development path on false
 */
$root = ( PRODUCTION ) ? dirname( __FILE__ ) : $config_params['dev_url'];

/**
 * Associative array which stores all the errors produced during
 * runtime
 */
$site_errors = array();

/****************************************/
/*        CONFIGURATION FUNCTIONS       */
/****************************************/

/**
 * This function creates an xml config file to set constant elements of the website
 * You may use this file once in the index.php file at the top, before any other
 * function; once the file is ran, you can remove the function from your file.
 *
 * NOTE: Only few functions require the configuration file directly, however other make use of the config file indirectly, check the
 *       documentation. It is recommended to create the file at least with the site
 *       url.
 *
 * @param string $action Defaults to 'info' display configuration in HTML format,
 *               use 'create' to create the file and add settings, use 'update'
 *               to add, remove or update values.
 * @param array $settings Site's settings' parameters and values
 */
function site_config ( $action = 'info', $settings = array() ) {

	global $site_errors;
	$output = '';

	if ( $action == 'create' ) {	
		
		// Check for existing files
		if ( ! file_exists( 'site-config.json' ) ) {
			// Whether user wants json or xml
				
			/* JSON SETUP */
			$site_settings = array ( 'site_configuration' => array ( $settings ) );	
			
			$json_file = fopen( 'site-config.json' , 'w' );
			if ( fwrite( $json_file, json_encode ( $site_settings ) ) ) {
				$output = 'site-config.json created succesfully';
			} else {
				$output = 'Error writing info into site-config.json';
			}
			fclose( $json_file );
		
		} else {
			$site_errors['site_config() found a site-congif.json file already in the system'] = 'CREATE_CONFIG_ERROR';
		}
			
	} elseif ( $action == 'update' ) {
			
		/* JSON CONFIGURATION */
		$site_data = json_decode ( file_get_contents ( 'site-config.json' ), true );
		$cur_settings = $site_data['site_configuration'];
		
		foreach ( $settings as $setting => $value ) :
		
			$cur_settings[$setting] = $value;
			
		endforeach;
		
		$json_file = fopen( 'site-config.json' , 'w' );
		if ( fwrite( $json_file, json_encode ( $cur_settings ) ) ) {
			$output = 'site-config.json updated successfully';
		} else {
			$site_errors['site_config() could not update the file'] = 'UPDATE_CONFIG_ERROR';
		}

		fclose( $json_file );
	
	} elseif ( $action == 'info' ) {
			
		/* DATA FROM JSON CONFIG FILE */
		if ( file_exists ( 'site-config.json' ) ) {
			
			$site_data = json_decode ( file_get_contents ( 'site-config.json' ), true );
			
			$output .= '<div class="site-settings"><h1>SITE SETTINGS</h1><table class="settings-table" border="1"><tr><th>Setting</th><th>Value</th></tr>';
			foreach ( $site_data['site_configuration'] as $site_config ) :
				foreach ( $site_config as $setting => $value ) :
					$output .= sprintf( '<tr><td>%1$s</td><td>%2$s</td></tr>', $setting, $value );
				endforeach;
			endforeach;
			
			$output .= '</table></div><!-- .site-settings -->';
			
		} else {
			$site_errors['site_config() Could not find site-config.json'] = 'INFO_CONFIG_ERROR';
		}
	
	}

	return $output;
}

/**
 * Gets all parameters from site-config.json into an Assoc array
 * @return [Array]
 */
function break_config() {
	$output;

	$config = json_decode( file_get_contents( 'site-config.json' ), true );

	$output = $config['site_configuration'];

	return $output;
}

/**
 * GZIP ompression function
 */
function gzip_comp() {
	if ( substr_count ( $_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip' ) ) :
		ob_start("ob_gzhandler"); 
	else :
		ob_start();
	endif;
}

/**************************************/
/*        SITE DATA FUNCTIONS         */
/**************************************/

/**
 * Get the language of the site from config file
 *
 * @return Language from json o xml file
 */
function get_site_lang () {

	global $site_errors;
	$output = '';

	if ( file_exists( 'site-config.json' ) ) {
		$settings = json_decode ( file_get_contents( 'site-config.json' ) );
		
		$output = $settings->site_configuration->lang;
		
	} else {
		$site_errors['get_site_lang() Could not find site-config.json file.'] = 'CONFIG_ERROR';
	}

	return $output;
}

/**
 * Get the current file displayed by the browser
 * 
 * @return string Returns the file name
 */
function get_current_file () {
	$file = basename ( $_SERVER['PHP_SELF'] );
	return $file;
}

/**
 * Returns the site URL from site-config.xml
 *
 * @param string $action use 'echo' to display the result, defaults to return. 
 */
function get_site_url () {
	global $site_errors;
	$output = '';
	if ( file_exists ( 'site-config.json' ) ) {
		
		$settings = json_decode ( file_get_contents( 'site-config.json' ) );
		
		if ( isset( $settings->site_configuration->url ) && ! empty( $settings->site_configuration->url ) ) {

			$output = $settings->site_configuration->url;

		} else {
			$site_errors['URL parameter not found in configuration file'] = 'CONFIG_ERROR';
		}
		
	} else {
		$site_errors['get_site_url() Could not find site-config.json file.'] = 'CONFIG_ERROR';
	}

	return $output;
}

/**
 * Gets the requested url and returns it or echos it
 */
function get_requested_url () {
	$protocol = ( ! empty ( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443 ) ? "https://" : "http://";
    $url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	
	return $protocol . $url;
}

/**
 * Gets the file name without the extension to pass it as an ID
 * returns the result
 */
function get_page_id () {
	$file = get_current_file();
	$id = preg_replace ( '/\.php|\.html|\.css|\.js/', '', $file );
	
	return $id;
}

/**
 * Get the actual file name and displays it as the title followed by the site name
 *
 * @param string $title Defines the title for the home page if not entered will
 *              display Home, and it will change accross the pages
 * @param string $site Defines the site name if not passed a text version of the
 *               Domain will be used, this won't change accross the pages
 */
function get_page_title ( $title, $site ) {
	$output = '';
	
	if ( 'index' == get_page_id() ) {
		if ( $title == '' ) {
			$page = 'Home';
		} else {
			$page = $title;
		}
	} else {
		$raw_url = explode ( '/', get_requested_url() );
		$last_el = count ( $raw_url ) - 1;
		$raw_title = ucfirst ( str_replace ( array ( '-', '_' ), ' ', $raw_url[$last_el] ) );
		if ( preg_match ( '/(.php|.ph|.html|.htm)$/', $raw_title ) ) {
			$ar_title = explode ( '.', $raw_title );
			$page = $ar_title[0];
		} else {
			$page = $raw_title;
		}
		//$page = ucfirst ( str_replace ( array( '-', '_' ), ' ', get_page_id() ) );
	}
	$rpc = array( 'http://', 'https://', 'www.', '.com', '.net', '.biz', '.org', '.info', '.tk', '.it', '.ec' );
	$domain = ( $site == null || empty ( $site ) ) ? str_replace( $rpc, '', $_SERVER['HTTP_HOST'] ) : $site;	
	
	$output = $domain . ' | ' . $page; 
	return $output;
}

/**
 * Includes header file for the theme
 *
 * @param string $file The suffix of the file to be included
 */
function get_header ( $file = '' ) {
	global $root, $site_errors;

	$header_file = ( $file == null || $file == '' ) ? 'header.php' : 'header-' . $file . '.php';
	if ( file_exists ( $header_file ) ) {
		include $root . '/' . $header_file;
	} else {
		$site_errors['Header file could not be found'] = 'GET_ERROR';
	}
}

/**
 * Includes Footer file for the theme
 *
 * @param string $file The suffix of the file to be included
 */
function get_footer ( $file = '' ) {
	global $root, $site_errors;

	$footer_file = ( $file == null || $file == '' ) ? 'footer.php' : 'footer-' . $file . '.php';
	if ( file_exists ( $footer_file ) ) {
		include $root . '/' . $footer_file;
	} else {
		$site_errors['Footer file could not be found.'] = 'GET_ERROR';
	}
}

/**
 * Includes a sidebar file whereever it is called
 *
 * @param string $file The suffix of the file to be included e.g. sidebar-home.php
 */
function get_sidebar ( $file = '' ) {
	global $root, $site_errors;

	$sidebar_file = ( isset( $file ) && ! empty( $file ) ) ? 'sidebar-' . $file . '.php' : 'sidebar.php';

	if ( file_exists ( $sidebar_file ) ) {
		include $root . '/' . $sidebar_file;
	} else {
		$site_errors['Sidebar file could not be found'] = 'GET_ERROR';
	}
}

/****************************************/
/*      MARKUP GENERATOR FUNCTIONS      */
/****************************************/

/**
 * Add the default meta tags and any other custom meta tag to the document
 *
 * @param array $tags indicates the value and attribute of the meta tag
 * @param boolean $overwrite true: overwrites the defaults, false: display defaults
 */
function add_meta_tags ( $tags = array(), $overwrite = false ) {
	
	// didplay defaults if overwrite false
	if ( $overwrite == false ) {
		$defaults = array ( 
			array ( 'utf-8' => 'charset' ),
			array ( 'viewport' => 'name', 'width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0' => 'content' )
		);
		array_unshift ( $tags, $defaults[0], $defaults[1] );
	}
		
	foreach ( $tags as $tag => $content ) {
		printf ( '<meta' );
		foreach ( $content as $desc => $attr ) {
			printf ( ' %1$s="%2$s"', $attr, $desc );
		}
		printf ( '/>' );
	}
}

/**
 * Add default link tags and any other custom link tags to the document
 *
 * @param array $tags indicates the file and the rel attribute of the link tags
 * @param boolea $overwrite true: overwrites default, false: display defaults
 */
function add_style_tags ( $tags = array(), $overwrite = false ) {
	if ( $overwrite == false ) {
		$tags = array( 'style.css' => 'stylesheet' ) + $tags;
	}
	
	foreach ( $tags as $tag => $rel ) {
		if ( preg_match ( '/^http/', $tag ) ) {
			printf ( '<link rel="%1$s" href="%2$s"/>', $rel, $tag );
		} else {
			printf ( '<link rel="%1$s" href="%2$s"/>', $rel, get_site_url() . '/' . $tag );
		}
	}
}

/**
 * Add default scripts files plus any other custom script files
 * It also adds the html5shiv.js for backward html5 compatibility for IE browsers
 * which cannot be overwritten
 *
 * @param array $files defines javascript files to use in the document and whether
 *              they are local or remote, when remote full path must be passed
 * @param boolean $overwrite true: overwrites default files, false: display defaults
 */
function add_script_tags ( $files = array() ) {

	$output = '';

	$output .= sprintf ( '<!--[if lt IE 9]><script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->' );

	$files = array( 'https://www.google.com/recaptcha/api.js' => 'remote', ) + $files;
	
	foreach ( $files as $file => $location ) {
		if( $location == 'remote' ) {
			$output .= sprintf ( '<script type="text/javascript" src="%s"></script>', $file );
		} else {
			$output .= sprintf ( '<script type="text/javascript" src="%s"></script>', get_site_url() . '/js/' . $file );	
		}
	}

	echo $output;
}

/**
 * Creates a navigation bar where applied, if class 'main-nav' is applied the function will echo a navigation toggle element
 * @param  [array] $args Containing the available options for the navigation bar
 * @return [type]       [description]
 */
function add_navbar ( $args ) {
	$output = '';

	// setting defaults
	
	// Position: main-nav{default}, footer-nav, side-nav, content-nav
	$position = ( isset ( $args['position'] ) && ! empty ( $args['position'] ) ) ? $args['position'] : 'main-nav';

	// Toggle_type: text{default}, icon
	$toggle_type = ( isset ( $args['toggle_type'] ) && ! empty ( $args['toggle_type'] ) ) ? $args['toggle_type'] : 'text';

	$a_class = ( isset ( $args['link_class'] ) && ! empty ( $args['link_class'] ) ) ? $args['link_class'] : '';
	$tgl_txt_class = ( isset ( $args['toggle_class'] ) && ! empty ( $args['toggle_class'] ) ) ? 'class="' . $args['toggle_class'] . '"' : '';

	$search_bar = ( isset( $args['search_bar'] ) ) ? $args['search_bar'] : true;

	// Creating the actual navbar
	$output .= sprintf ( '<nav class="%s">', $position  );
	
	if ( $position == 'main-nav' ) {
		
		$toggle_id = ( empty( $args['toggle_id'] ) || $args['toggle_id'] == null ) ? '' : 'id="' . $args['toggle_id'] . '"';
		
		$toggle_class = 'toggle-menu';
		if ( $toggle_type == 'text' ) {
			$toggle_txt = 'MENU';
		} else {
			$toggle_txt = '';
			$toggle_class .= ' toggle-icon';
		}
		
		$output .= sprintf ( '<div %1$s class="%2$s"><a %4$s >%3$s</a></div>', $toggle_id, $toggle_class, $toggle_txt, $tgl_txt_class );
	}
	
	$nav_id = ( isset( $args['nav_id'] ) && ! empty ( $args['nav_id'] ) ) ? 'id="'. $args['nav_id'] .'"' : '';
	
	$output .= sprintf ( '<ul %s class="nav-container">', $nav_id );
	foreach ( $args['nav_links'] as $nav_item => $file ) {
		/**
		 * Compares each file to the current file to apply current class to item
		 */
		$comp_file = ( $file == '/' || empty ( $file ) || $file == '#') ? 'index.php' : $file;
		$current_class = ( get_current_file() == $comp_file ) ? 'current-nav' : '';

		/**
		 * Set classes if any
		 */
		if ( ! empty( $current_class ) || ! empty( $a_class ) ) {
			$link_class = 'class="';
			$link_class .= $current_class;
			$link_class .= ( empty( $current_class) ) ? '' : ' ';
			$link_class .= $a_class;
			$link_class .= '"';
		} else {
			$link_class = '';
		}

		$output .= sprintf ( '<li><a %1$s href="%2$s">%3$s</a></li>', $link_class, get_site_url() . '/' . $file, $nav_item );
	}

	if ( $search_bar !== false ) {
		$output .= '<li class="main-search-bar">
						<form action="search-page.php" method="get">
							<input type="search" name="search" placeholder="Search...">
							<input class="search-button" type="submit" name="submit" value="">
						</form>
					</li>';
	}

	$output .= sprintf ( '</ul></nav><!-- .%s -->', $position );

	echo $output;
}

/**
 * Add a single image to an HTML page.
 * Image will be wrapped inside a figure tag
 *
 * @param array $args multiple settings that will define the image element, parent and siblings
 * 		  
 */
function add_image ( $args ) {
	/*
	'path' => '',
	'class' => '',
	'img_attr' => array(),
	'link' => '',
	'link_title' => '',
	'caption' => '',
	'caption_attr' => array(),
	'caption_pos' => 'bottom'
	*/

	$output = '';

	// Default Variables
	$i_vals = '';
	$c_vals = '';
	$caption_pos = ( isset( $arg['caption_pos'] ) && ! empty( $arg['caption_pos'] ) ) ? $arg['caption_pos'] : 'bottom';

	// Set ID for image container
	$id = ( !empty ( $args['id'] ) ) ? 'id="' . $args['id'] . '"' : '';
	// Set Class for image container
	$class = ( ! empty ( $args['class'] ) ) ? 'class="' . $args['class'] .'"' : '';

	// setup the img attributes
	if ( ! empty ( $args['img_attr'] ) ) {
		foreach ( $args['img_attr'] as $i_attr => $i_val ) {
			$i_vals .= ' ' . $i_attr . '="' . $i_val . '"';
		}
	}
	
	// setup caption attributes
	if ( ! empty ( $args['caption_attr'] ) ) {
		foreach ( $args['caption_attr'] as $c_attr => $c_val ) {
			$c_vals .= ' ' . $c_attr . '="' . $c_val . '"';
		}
	}
	
	// set the link titles if any
	$link_title = '';
	if ( ! empty ( $args['link'] ) ) {
		$link_title = str_replace( array( '-', '_' ), '', $args['link'] );
	}
	
	// print the whole set containing figure, img and figcation tags
	$output .= sprintf ( '<figure %1$s %2$s>', $id, $class );
	
		// top caption
		if ( $caption_pos == 'top' ) {
			if ( ! empty ( $args['caption'] ) ) {
				$output .= sprintf ( '<figcaption %1$s><span>%2$s</span></caption>', $c_vals, $args['caption'] );
			}
		}
		
		// Actual image tag
		if ( ! empty ( $args['link'] ) ) {
			$output .= sprintf ( '<a class="img-link" href="%3$s" title="%4$s"><img %1$s src="%2$s"/></a>', $i_vals, $args['path'], $args['link'], $link_title );
		} else {
			$output .= sprintf ( '<img %1$s src="%2$s"/>', $i_vals, $args['path'] );
		}
		
		// bottom caption
		if ( $caption_pos == 'bottom' ) {
			if ( ! empty ( $args['caption'] ) ) {
				$output .= sprintf ( '<figcaption %1$s><span>%2$s</span></caption>', $c_vals, $args['caption'] );
			}
		}
		
	$output .= sprintf ( '</figure>' );

	echo $output;
}

/**
 * Embed an SVG file into an html document.
 * IMPORTANT: The framework is built to support modern browsers including IE9 and older
 * There is no fallback for VSG since IE9 supports SVG pretty good
 * @param  string $file the relative or full path of the image
 */
function embed_svg ( $args ) {
	$output = '';
	$path = $args['path'];
	$class = ( isset( $args['class'] ) || ! empty( $args['class'] ) ) ? 'class="' . $args['class'] . '"' : '';
	$link = ( isset( $args['link'] ) || ! empty( $args['link'] ) ) ? $args['link'] : '';


	// Get SVG file content
	$svg_array = preg_grep( '/^\<\?xml/', file( $args['path'], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES ), PREG_GREP_INVERT );

	$svg_content = implode( '', $svg_array );


	$output .= sprintf ( '<figure %s>', $class );
	if ( ! empty( $link ) ) {
		$output .= sprintf( '<a href="%s">', $link );
	}
	
	$output .= $svg_content;

	if ( ! empty( $link ) ) {
		$output .= '</a>';
	}
	$output .= '</figure>';

	echo $output;
}

/**
 * Creates a gallery from an especific folder in your image server's folder
 *
 * @param args array,
 * @param int $limit the number of images to display, defaults to 10
 * @param string $sort sort images in an especific order or random, defaults to ASC-name
 */
function create_gallery_from ( $args = array() ) {
	$output = '';
	// Set Counter, Variables, Patterns and Replaces
	$c = 0;
	$pattern = array( '/-thumb|(\.png|\.jpg|\.jpeg|\.gif|\.tiff|\.bmp|\.raw|\.jfif)/', '/-|_/', '/\&/' );
	$replace = array( '', ' ', '<br/>' );
	$image_ext = '/(\.png|\.jpg|\.jpeg|\.gif|\.tiff|\.bmp|\.raw|\.jfif)/';
	$thumb_ext = '/-thumb(\.png|\.jpg|\.jpeg|\.gif|\.tiff|\.bmp|\.raw|\.jfif)/';
	
 	$scan_path = ( empty ( $args['folder'] ) || $args['folder'] == null ) ? dirname ( __FILE__ ) . '/images' : dirname ( __FILE__ ) . '/images/' . $args['folder'];
	$img_path = ( empty ( $args['folder'] ) || $args['folder'] == null ) ? get_site_url() . '/images' : get_site_url() . '/images/' . $args['folder'];
	
 	$raw_images = array_diff( scandir ( $scan_path ), array( '.', '..' ) );
	$limit = ( empty ( $args['limit'] ) || $args['limit'] == null ) ? count( $raw_images ) : $args['limit'];
	$images = array();
	
	// If $args['size'] is randome, randomize images array first
	if ( $args['order'] == 'random' ) {
		shuffle ( $raw_images );
	}
	
	// Filtering the array using size and limit arguments
	foreach ( $raw_images as $type_img ) :
	
		if ( $args['size'] == 'thumbnail' ) {
		
			if ( preg_match ( $thumb_ext, $type_img ) ) {
				if ( $c < $limit ) {
					$images[$c] = $type_img;
					$c++;
				}
			}
			
		} else {
		
			if ( preg_match ( $image_ext, $type_img ) ) {
				if ( $c < $limit ) {
					$images[$c] = $type_img;
					$c++;
				}
			}
			
		}
	endforeach;
	
	// Sort array of images
	if ( $args['order'] != 0 ) {
		arsort ( $images );
	}
	
	// Defined figcaption if true
	if ( $args['caption'] == true ) {
		$caption = '<figcaption class="caption-box">
						<p class="caption-text">%s</p>
				    </figcaption>';
	}
	
	// Finally Print it out
	foreach ( $images as $image ) :
		printf ( '<figure class="%s">', $args['class'] );
		
		if ( ! empty ( $args['before'] ) ) {
			printf ( $args['before'] );
		}
		
		$image_name = preg_replace ( $pattern, $replace, $image );
		
		// Top Caption
		if ( $args['caption_pos'] == 'top' ) {
			printf ( $caption, $image_name );
		}
		
		// actual image
		printf ( '<img src="%1$s/%2$s" title="%3$s" alt="%3$s"/>', $img_path, $image, $image_name );
		
		// Bottom Caption 
		if ( $args['caption_pos'] == 'bottom' ) {
			printf ( $caption, $image_name );
		}
		
		if ( ! empty ( $args['after'] ) ) {
			printf ( $args['before'] );
		}
		
		printf ( '</figure>' );
	endforeach;
}

/**
 * Adds a custom or predefined contact form.
 * This form does not support drop down input tags such as option yet
 * @param array $args Set of options to set the contact form
 *                    email : Recipient email is mandatory
 *                    class : Class of the contact form
 *                    textarea_placeholder : Placeholder text of textarea
 *                    						 defaults to "Type your message"
 *                    g_captcha : activate google captcha, defaults to true
 *                    captcha_options : is an array to set up options as per
 *                    					google reCaptcha docs:
 *                    					- data_sitekey
 *                    					- data_type
 *                    					- data_theme
 *                    					- data_callback
 *                    fields : an array to set up the custom input fields
 *                    		   if assoc array used, the keys will be used to
 *                    		   set <label> tags, otherwise no <label> tags
 *                    		   will be used
 *                    textarea : activate textarea field, defaults to true
 *                    submit_text : text shown submit button, defaults to "submit"
 *                    
 *                    		   			
 */
function add_contact_form ( $args = array() ) {
	global $root;
	//Set default variables
	$output = '';
	$str_p = '';
	$end_p = '';	
	$has_label = false;		// Set <label> tags off by default
	$form_class = ( isset ( $args['class'] ) && ! empty ( $args['class'] ) ) ? $args['class'] : '';
	$txtarea_plcholder = ( isset ( $args['textarea_placeholder'] ) && ! empty ( $args['textarea_placeholder'] ) ) ? $args['textarea_placeholder'] : 'Type your message';

	// Enable Google reCaptcha by default
	$g_captcha = ( isset ( $args['g_captcha'] ) ) ? $args['g_captcha'] : true;

	// Google reCaptcha options
	$captcha_sitekey = ( ! empty( $args['captcha_options']['data_sitekey'] ) ) ? $args['captcha_options']['data_sitekey'] : 'YOUR SITE KEY';
	$captcha_type = ( ! empty( $args['captcha_options']['data_type'] ) ) ? 'data-type="' . $args['captcha_options']['data_type'] . '"' : '';
	$captcha_theme = ( ! empty( $args['captcha_options']['data_theme'] ) ) ? 'data-theme="' . $args['captcha_options']['data_theme'] . '"' : '';
	$captcha_callback = ( ! empty( $args['captcha_options']['data_callback'] ) ) ? 'data-callback="' . $args['captcha_options']['data_callback'] . '"' : '';

	// Get data for hidden fields used to check google recaptcha response
	$hidden_fields = array(
			'visitor_ip' => $_SERVER['REMOTE_ADDR'],
			'visitor_useragent' => $_SERVER['HTTP_USER_AGENT']
		);
	
	// Default fields
	$fields = ( isset ( $args['fields'] ) && is_array( $args['fields'] ) ) ? $args['fields'] : array (
			array ( 'name' => 'name', 'type' => 'text', 'placeholder' => 'Name', 'required' => 'required' ),
			array ( 'name' => 'email', 'type' => 'email', 'placeholder' => 'e-mail', 'required' => 'required' ),
			array ( 'name' => 'subject', 'type' => 'text', 'placeholder' => 'Subject', 'required' => 'required' ),
		);

	// If form has been submitted include the message-script
	if ( isset ( $_POST['submit'] ) ) {
		include $root . '/message-script.php';
	}

	// Check if email address has been passed, if not advice the admin
	if ( ! isset ( $args['email'] ) || empty ( $args['email'] ) ) {
		$output = '<p>You have not declare an email, messages won&#39;t be sent</p>';
	}
	
	// if form has been submitted, store the message that was sent by the form
	if ( ! empty ( $message ) ) {
		$output .= sprintf ( $message, $_POST['name'] );
	}
	
	$output .= sprintf ( '<form class="mail-form %1$s" method="POST" action="%2$s" >', $form_class, get_requested_url() );

	// Add all the input fields into the form
	foreach ( $fields as $type => $field ) {
		$str_p = ( $field['type'] == 'hidden') ?  '' : '<p>';
		$end_p = ( $field['type'] == 'hidden') ?  '' : '</p>';
		if ( is_int ( $type ) ) {
			$output .= $str_p;
			$output .= '<input';
			foreach ( $field as $attr => $val ) {
				$output .= sprintf ( ' %1$s="%2$s"', $attr, $val );
			}	 
			$output .= '/>';
			$output .= $end_p;
		} else {
			$has_label = true;
			$output .= $str_p;
			$output .= sprintf ( '<label class="form-label">%s</label><input', $type );
			foreach ( $field as $attr => $val ) {
				$output .= sprintf ( ' %1$s="%2$s"', $attr, $val );
			}	 
			$output .= '/>';
			$output .= $end_p;
		}
	}
	
	// Include textarea if enabled
	if ( ! isset ( $args['textarea'] ) || $args['textarea'] == true ) {
		if ( $has_label ) {
			$output .= sprintf( '<p><label>%s</label>', $txtarea_plcholder );
			$txtarea_plcholder = '';
		}
		$output .= sprintf ( '<textarea name="message" placeholder="%s"></textarea>', $txtarea_plcholder );

		if ( $has_label ) {
			$output .= '</p>';
		}
	}

	// Add hidden fields
	foreach ($hidden_fields as $key => $value) {
		$output .= sprintf( '<input name="%1$s" type="hidden" value="%2$s"/>', $key, $value );
	}

	// Enable Google no Captcha ReCaptcha
	if ( $g_captcha ) {
		$output .= sprintf( '<div class="g-recaptcha" data-sitekey="%1$s" %2$s %3$s %4$s></div>', $captcha_sitekey, $captcha_type, $captcha_theme, $captcha_callback );
	}
	
	$submit_txt = ( empty ( $args['submit_text'] ) ) ? 'Send Message' : $args['submit_text'];

	$output .= sprintf ( '<input id="submit-form" name="submit" type="submit" value="%s"/>', $submit_txt );
	$output .= '</form>';

	echo $output;
}

/*************************************/
/*      FILE HANDLER FUNCTIONS       */
/*************************************/

/**
 * Get Images File name and path and returns an array
 * @param  array  $args folder: String, Path to get the images from
 *                      sort: String, Sort images by Alpha-Num or Last-Mod.
 *                      limit: Integer, Limit the array output.
 * @return array $output Array of image files.
 */
function get_images_from ( $args = array() ) {
	global $root;
	$output;
	$img_files = array();

	// Folder defaults to images folder in your root directory.
	$scan_path = ( isset( $args['folder'] ) && ! empty ( $args['folder'] ) ) ? $root . '/images/' . $args['folder'] : $root . '/images';

	$rel_path = ( isset( $args['folder'] ) && ! empty ( $args['folder'] ) ) ? get_site_url() . '/images/' . $args['folder'] : get_site_url() . '/images';

	$raw_files = array_diff( scandir ( $scan_path ), array( '.', '..' ) );

	$sort = ( isset( $args['sort'] ) && ! empty( $args['sort'] ) ) ? $args['sort'] : 'LMOD_DESC';

	if ( isset( $args['regex'] ) && ! empty( $args['regex'] ) ) {
		$raw_files = preg_grep( $args['regex'] , $raw_files );
	}

	// Add last date modified to array if sorted by last modified date.
	if ( $sort == 'LMOD_ASC' || $sort == 'LMOD_DESC' ) {
		foreach ( $raw_files as $file ) {
			$img_key = $rel_path . '/' . $file;
			$img_files[$img_key] = filemtime( $scan_path . '/' . $file );
		}
	} else {
		foreach ( $raw_files as $img_name ) {
			$img_path = $rel_path . '/' . $img_name;
			array_push( $img_files, $img_path );  
		}
	}

	// LMOD_ASC: Last Modified date descendant
	// LMOD_DESC (Default): Last Modified date ascendant
	// ASC: Name Numeric-Alpha Ascendant
	// DESC: Name Numeric-Alpha Descendant
	switch ( $sort ) {
		case 'LMOD_ASC':
			asort( $img_files );
			$img_files = array_keys( $img_files );
			break;
		case 'ASC':
			sort( $img_files );
			break;
		case 'DESC':
			rsort( $img_files );
			break;
		default:
			arsort( $img_files );
			$img_files = array_keys( $img_files );
			break;
	}


	// Get the files needed by limit
	if ( isset( $args['limit'] ) && ! empty( $args['limit'] ) ) {
		$output = array_slice( $img_files, 0, $args['limit']);
	} else {
		$output = $img_files;
	}

	return ( empty( $output ) ) ? false : $output;
}

/**
 * Get Json File Content
 * Returns an array of the json file content limited by the limit option;
 *
 * @param $args array Set of options to customize the output
 */
function get_json_content( $args = array() ) {
	global $root;

	// Set the absolute path of the file
	$file = $root . '/' . $args['path'];

	$output_obj = array();

	$file_content = json_decode ( file_get_contents ( $file ), true );

	// Return array contained by the json object or
	// return the full object
	if ( isset( $args['object'] ) && ! empty( $args['object'] ) ) {
		$obj = $args['object'];
		return $output_obj[$obj];
	} else {
		return $output_obj;
	}
}

/*************************************/
/*       ANALYTICS FUNCTIONS     	 */
/*************************************/

/**
 * Add Google Analytics
 */
function add_analytics ( $args = array() ) {
	$ggl_ID = $args['google_ID'];
	$ggl_domain = $args['site_domain'];
	
	$analytics = '<script>
		(function(i,s,o,g,r,a,m){i["GoogleAnalyticsObject"]=r;i[r]=i[r]||function(){
		(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		})(window,document,"script","//www.google-analytics.com/analytics.js","ga");
		ga("create", "%1$s", "%2$s");
		ga("send", "pageview");
	</script>';
	
	printf( $analytics, $ggl_ID, $ggl_domain );
}

/*****************************************/
/*        SOCIAL MEDIA FUNCTIONS         */
/*****************************************/
/**
 * facebook url to get counts
 * https://api.facebook.com/method/fql.query?query={select total_count,like_count,comment_count,share_count,click_count from link_stat where url='http//domain.com'}&format=json"
 * Code to get counts
 * $query = "select total_count,like_count,comment_count,share_count,click_count from link_stat where url='{$url}'";
 * $call = "https://api.facebook.com/method/fql.query?query=" . rawurlencode($query) . "&format=json";
 * $ch = curl_init();
 * curl_setopt($ch, CURLOPT_URL, $call);
 * curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
 * $output = curl_exec($ch);
 * curl_close($ch);
 * return json_decode($output);
 */
 
/**
 * Twitter URL to get counts for URL's
 * http://urls.api.twitter.com/1/urls/count.json?url=yoururl.com
 */
 
/**
 * Social Widgets Class
 */
function social_init( $args = array() ) {
	
	// Facebook SDK Sccript
	$fb_script = '<div id="fb-root"></div>
		<script>(function(d, s, id) {
		  var js, fjs = d.getElementsByTagName(s)[0];
		  if (d.getElementById(id)) return;
		  js = d.createElement(s); js.id = id;
		  js.src = "//connect.facebook.net/%1$s/sdk.js#xfbml=1&appId=%2$s&version=v2.0";
		  fjs.parentNode.insertBefore(js, fjs);
		}(document, "script", "facebook-jssdk"));</script><!-- facebook script -->';
		
	// Google Script
	$gg_script = '<script type="text/javascript">
		%s
		(function() {
	    var po = document.createElement("script"); po.type = "text/javascript"; po.async = true;
	    po.src = "https://apis.google.com/js/platform.js";
	    var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(po, s);
	  })();
	</script><!-- google plus script -->';
	
	// Pinterest Script
	$pin_script = '<script type="text/javascript" %s async src="//assets.pinterest.com/js/pinit.js"></script><!-- pinterest script -->';
	
	// Twitter script
	$tw_script = '<script>
	!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location) ? "http":"https";if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document, "script", "twitter-wjs");</script><!-- twitter Script -->';
	
	// Linkedin script
	$in_script = '<script src="//platform.linkedin.com/in.js" type="text/javascript">lang: %s</script><!-- linkedin Script -->';	
		
	// Languages
	$lang_ggcodes = array (
		'spanish_latin' => 'es-419',
		'spanish' => 'es',
		'english_UK' => 'en-GB',
		'english_USA' => 'en-US',
		'portugues_BRA' => 'pt-BR',
		'portugues_POR' => 'pt-PT',
		'french' => 'fr'
	);
	
	$lang_fbcodes = array (
		'spanish_latin' => 'es_LA',
		'spanish' => 'es_ES',
		'english_UK' => 'en_GB',
		'english_USA' => 'en_US',
		'portugues_BRA' => 'pt_BR',
		'portugues_POR' => 'pt_PT',
		'french' => 'fr_FR'
	);
	
	// Set Variables
	$fb_ID = (string)$args['facebook_APPID'];
	if ( empty ( $args['language'] ) || $args['language'] == 'english_USA' ) :
		$gg_lang = '';
		$fb_lang = 'en_US';
	else :
		$gg_lang = 'window.___gcfg = {lang: "' . $lang_ggcodes[$args['language']] . '"};';
		$fb_lang = $lang_fbcodes[$args['language']];
	endif;
	
	// Set Pinterest variables
	$pin_hover;
	if ( $args['pin_hover'] == true ) {
		$pin_hover = 'data-pin-hover="true"';
	} else {
		$pin_hover = '';
	}
	
	// Print facebook script
	if ( $args['facebook'] == true || empty ( $args['facebook'] ) || $args['facebook'] == null ) {
		printf ( $fb_script, $fb_lang, $fb_ID );
	}
	
	// Print google script
	if ( $args['google'] == true || empty ( $args['google'] ) || $args['google'] == null ) {
		printf ( $gg_script, $gg_lang );
	}
	
	// Print pinterest script
	if ( $args['pinterest'] == true || empty ( $args['pinterest'] ) || $args['pinterest'] == null ) {
		printf ( $pin_script, $pin_hover );
	}
	
	// Print twitter script
	if ( $args['twitter'] == true || empty ( $args['twitter'] ) || $args['twitter'] == null ) {
		printf ( $tw_script );
	}
	
	// Print linkedin script
	if ( $args['linkedin'] == true || empty ( $args['linkedin'] ) || $args['linked'] == null ) {
		printf ( $in_script, $fb_lang );
	}
}

/**
 * Like Bar
 * Includes facebook likes, +1's
 */
function likes_bar ( $args = array() ) {
	$bar_id = ( empty ( $args['id'] ) || $args['id'] == null ) ? '' : 'id="' . $args['id'] . '"';
	$bar_class;
	$fb_url;
	
	if ( $args['facebook']['data-href'] == null || empty ( $args['facebook']['data-href'] ) ) :
		if ( get_page_id() == 'index' ) {
			$fb_url = get_site_url();
		} else {
			$fb_url = get_site_url() . '/' . get_current_file();
		}
	else :
		$fb_url = $args['facebook']['data-href'];
	endif;
	
	// Facebook defaults
	$fb_defaults = array(
		'data-layout' => 'button_count',
		'data-action' => 'like',
		'data-show-faces' => 'false',
		'data-share' => 'false',
		'data-colorscheme' => 'light',
	);
	
	// Google defaults
	$gg_defaults = array(
		'data-size' => 'medium',
		'data-annotation' => 'bubble',
		'data-align' => 'left',
		'expandTo' => '',
		'data-recommendations' => 'false',
	);
	 
	// Assign defaults to variables
	$fb_options = ( empty ( $args['facebook'] ) || $args['facebook'] == null )? $fb_defaults : $args['facebook'];
	$gg_options = ( empty ( $args['google'] ) || $args['google'] == null )? $gg_defaults : $args['google'];
	
	if ( $args['bar_style'] == 'standard' || empty ( $args['bar_style'] ) || $args['bar_style'] == null ) :
	
		// set class attribute for like bar
		$bar_class = 'standard-bar';
		printf ( '<div class="like-bar %s"><ul>', $bar_class );
		
		if ( $args['facebook'] != false || empty ( $args['facebook'] ) || $args['facebook'] == null ) {
			printf ( '<li class="wgt-button"><div class="fb-like"' );
			foreach ( $fb_options as $data => $value ) :
				printf ( ' %1$s="%2$s"', $data , $value );
			endforeach;
			printf ( '></div><!-- .fb-like --></li>' );
		}
		
		if ( $args['google'] != false || empty ( $args['google'] ) || $args['google'] == null ) {
			printf ( '<li class="wgt-button"><div class="g-plusone"' );
			foreach ( $gg_options as $data => $value ) :
				printf ( ' %1$s="%2$s"', $data , $value );
			endforeach;
			printf ( '></div><!-- .g-plusone --></li>' );
		}
			
		printf ( '</ul></div>' );
		
	elseif ( $args['bar_style'] == 'drawer' ) :
		
		// Set class attribute for like bar
		$bar_class = 'drawer-bar';
		$fb_counts = reset ( get_fb_counts( $fb_url ) );
	
		printf ( '<div class="like-bar %1$s"><ul><li class="wgt-button"><div class="fb-top wgt-cover"><span class="count-box">%2$s</span></div><div class="fb-like"', $bar_class, $fb_counts->like_count );
		foreach ( $fb_options as $data => $value ) :
			printf ( ' %1$s="%2$s"', $data , $value );
		endforeach;
		
		printf ( '></div><!-- .fb-like --></li><li class="wgt-button"><div class="gg-top wgt-cover"><span class="count-box">%s</span></div><div class="g-plusone"', $gg_counts );
		foreach ( $gg_options as $data => $value ) :
			printf ( ' %1$s="%2$s"', $data , $value );
		endforeach;
		printf ( '></div><!-- .g-plusone --></li></ul></div>' );
		
	endif;	
}

/**
 * Share bar
 * Includes social buttons to share page url: Facebook, Google+, Twitter, Pinterest and Linkedin
 */
function share_bar ( $args = array() ) {
	
	// Facebook defaults
	$fb_options = array(
		// data-href : Actual url if not defined
		// data-width : Depends on layout
		'data-type' => 'button_count' // box_count, button, icon
	);
	
	// Google defaults
	$gg_options = array(
		// data-href : Actual url if not defined
		// data-width
		// data-height
		'data-annotation' => 'bubble', // inline, vertical-bubble, none
		'data-align' => 'left', // right
		'data-expandTo' => '' // left, top, right, bottom
	);
	
	// Twitter defaults
	$tw_options = array(
		// data-url :actual url if not defined
		// data-via : rel="me"
		// data-text : <title> text
		// data-related :Recommended accounts
		// data-hashtag : 
		// data-counturl : URL being shared
		// data-size
		'data-count' => 'horizontal', // vertical, none
		'data-lang' => 'en',
		'data-dnt' => 'true',
	);
	
	$tw_language = array(
		'en' => 'Tweet',
		'es' => 'Twittear'
	);
	
	// Pinterest defaults
	$pin_options = array(
		// pin-url : Url of site if not defined button will select current url
		// pin-image : Image to use, if not defined button will choose any image available
		// 'data-pin-shape' => 'round' // when not specified shows rectangular button
		// data-pin-height : 20 small, 28 large
		'data-pin-color' => 'white', // Gray or Red
		'data-pin-config' => 'beside' // above, none
	);
	
	// Linkedin defaults
	$in_options = array(
		// data-url : The actual url if not defined
		'data-counter' => 'right' //Top
	);
	
	// Assign options to variables
	if ( is_array( $args['facebook'] ) ) {
		foreach ( $args['facebook'] as $key => $value ) {
			$fb_defaults[$key] = $value;
		}
	}
	
	if ( is_array( $args['google'] ) ) {
		foreach ( $args['google'] as $key => $value ) {
			$gg_options[$key] = $value;
		}
	}
	
	if ( is_array( $args['twitter'] ) ) {
		foreach ( $args['twitter'] as $key => $value ) {
			$tw_options[$key] = $value;
		}
	}
	
	if ( is_array( $args['pinterest'] ) ) {
		foreach ( $args['pinterest'] as $key => $value ) {
			$pin_options[$key] = $value;
		}
	}
	
	if ( is_array( $args['linkedin'] ) ) {
		foreach ( $args['linkedin'] as $key => $value ) {
			$in_options[$key] = $value;
		}
	}
	
	if ( $args['bar_style'] == 'standard' || $args['bar_style'] == null || empty( $args['bar_style'] ) ) :
	
		$bar_class = 'standard-bar';
		
		// print starting container
		printf ( '<div class="share-bar %s"><ul>', $bar_class );
		
		if ( ! empty ( $args['bar_text'] ) ) {
			printf ( '<li class="wgt-text" >%s</li>', $args['bar_text'] );
		}
		
		// Print facebook share
		if ( $args['facebook'] !== false ) {
			printf ( '<li class="wgt-button"><div class="fb-share-button"' );
			foreach ( $fb_options as $data => $value ) :
				printf ( ' %1$s="%2$s"', $data, $value );
			endforeach;
			printf ( '></div></li><!-- facebook -->' );
		}
		
		// Print google share
		if ( $args['google'] !== false ) {
			printf ( '<li class="wgt-button"><div class="g-plus" data-action="share"' );
			foreach ( $gg_options as $data => $value ) :
				printf ( ' %1$s="%2$s"', $data, $value );
			endforeach;
			printf ( '></div></li><!-- googleplus -->' );
		}
		
		// Print linkedin share
		if ( $args['linkedin'] !== false ) {
			printf ( '<li class="wgt-button"><script type="IN/Share"' );
			foreach ( $in_options as $data => $value ) :
				printf ( ' %1$s="%2$s"', $data, $value );
			endforeach;
			printf ( '></script></li><!-- linkedin -->' );
		}
		
		// Print Pin it button
		if ( $args['pinterest'] !== false ) {
			
			$pin_do;
			$pin_urlencoded;
			$pin_button_image;
			
			// Pin Button images
			$pin_large = array (
				'rect' => 'pinit_fg_en_rect_%s_28.png',
				'round' => 'pinit_fg_en_round_red_28.png'
			);
			
			$pin_height = array(
				'small' => '20',
				'large' => '28'
			);
			
			$pin_small = array(
				'rect' => 'pinit_fg_en_rect_%s_20.png',
				'round' => 'pinit_fg_en_round_red_16.png'
			);
			
			// Set the url and image to be pinned if pin-image is defined
			if ( ! empty ( $pin_options['pin-image'] ) ) :
				
				$pin_urlencoded = '?url=';
				$pin_urlencoded .= ( $pin_options['pin-url'] == null || empty ( $pin_options['pin-url'] ) ) ? rawurlencode ( get_site_url() . '/' . get_current_file() ) : rawurlencode ( $pin_options['pin-url'] );
				$pin_urlencoded .= '&media=' . rawurlencode ( $pin_options['pin-image'] );
				$pin_do = 'buttonPin';
			else :
				$pin_urlencoded = '';
				$pin_do = 'buttonBookmark';
			endif;
			
			// Set button image according to data-pin-height, data-pin-color and data-pin-shape
			if ( empty ( $pin_options['data-pin-shape'] ) || $pin_options['data-pin-shape'] == null || $pin_options['data-pin-shape'] == 'rect' ) {
			
				if ( empty ( $pin_options['data-pin-height'] ) || $pin_options['data-pin-height'] == null || $pin_options['data-pin-height'] == 'small' ) {
					$pin_button_image = $pin_small['rect'];
				} elseif ( $pin_options['data-pin-height'] == 'large' ) {
					$pin_button_image = $pin_large['rect'];
				}
				
			 } else {
			
				if ( empty ( $pin_options['data-pin-height'] ) || $pin_options['data-pin-height'] == null ) {
					$pin_button_image = $pin_small[$pin_options['data-pin-shape']];
				} elseif ( $pin_options['data-pin-height'] == 'large' ) {
					$pin_button_image = $pin_large[$pin_options['data-pin-shape']];
				}
			
			}
			
			// Start Printing actual button
			printf ( '<li class="wgt-button">' );
			printf ( '<a href="//www.pinterest.com/pin/create/button/%1$s" data-pin-do="%2$s" data-pin-height="%3$s"', $pin_urlencoded, $pin_do, $pin_height[$pin_options['data-pin-height']] );
			
			foreach ( $pin_options as $data => $value ) :
				if ( $data != 'pin-url' || $data != 'pin-image' || $data != 'data-pin-height' ) {
					printf ( ' %1$s="%2$s"', $data, $value );
				}
			endforeach;
			
			printf ( '><img src="//assets.pinterest.com/images/pidgets/' );
			printf ( $pin_button_image, $pin_options['data-pin-color'] );
			printf ( '"/></a></li><!-- pinterest -->' );
		}
		
		// Print tweet button
		if ( $args['twitter'] !== false ) {
			printf ( '<li class="wgt-button"><a href="https://twitter.com/share" class="twitter-share-button"' );
			foreach ( $tw_options as $data => $value ) :
				printf ( ' %1$s="%2$s"', $data, $value );
			endforeach;
			printf ( '>%s</a></li><!-- twitter -->', $tw_language[$tw_options['data-lang']] );
		}
				
		printf ( '</ul></div>' );
	
	elseif ( $args['bar_style'] == 'drawer' ) :
	
	endif;
}

/**
 * Get facebook pagefeed widget
 * @param  array  $args [description]
 * @return [type]       [description]
 */
function fb_pagefeed ( $args = array() ) {
	$href = $args['href'];
	$data_href = 'data-href="' . $href . '"';
	$data_hide_cover = ( isset ( $args['hide_cover'] ) && ! empty( $args['hide_cover'] ) ) ? 'data-hide-cover="' . $args['hide_cover'] . '"' : 'data-hide-cover="false"';
	$data_show_facepile = ( isset ( $args['show_friend_faces'] ) && ! empty( $args['show_friend_faces'] ) ) ? 'data-show-facepile="' . $args['show_friend_faces'] . '"' : 'data-show-facepile="true"';
	$data_show_posts = ( isset ( $args['show_posts'] ) && ! empty( $args['show_posts'] ) ) ? 'data-show-posts="' . $args['show_posts'] . '"' : 'data-show-posts="false"';
	$data_width = ( isset( $args['width'] ) && ! empty ( $args['width'] ) ) ? 'data-width="' . $args['width'] . '"' : '';
	$data_height = ( isset( $args['height'] ) && ! empty ( $args['height'] ) ) ? 'data-height="' . $args['height'] . '"' : '';

	$pattern = array( '/http:\/\/facebook.com\//', '/\.-_/' );
	$replace_pattern = array( '', ' ' );
	$blockquote = preg_replace( $pattern, $replace_pattern, $href );

	//Pagefeed markup
	$page_box = sprintf ( '<div class="fb-page" %1$s %2$s %3$s %4$s %5$s %6$s><div class="fb-xfbml-parse-ignore"><blockquote cite="%7$s"><a href="%7$s">%8$s</a></blockquote></div></div>', $data_href, $data_hide_cover, $data_show_facepile, $data_show_posts, $data_width, $data_height, $href, $blockquote );

	echo $page_box;
}

/**
 * Get facebook counts
 */
function get_fb_counts ( $url ) {
	$request = "select total_count,like_count,comment_count,share_count,click_count from link_stat where url='{$url}'";
	$call = 'https://api.facebook.com/method/fql.query?query=' . rawurlencode( $request ) . '&format=json';	
	//	$ch = curl_init();
	//	curl_setopt( $ch, CURLOPT_URL, $call );
	//	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	//	$counts = curl_exec( $ch );
	//	curl_close( $ch );
	return json_decode( file_get_contents( $call ) );
	//return json_decode( $counts );
}

/****************************/
/* ARTICLE / BLOG FUNCTIONS */
/****************************/

/* The following functions are set to work with article or blog content files.
 * These files are: .content and  .json
 * .json files contains the following:
 * article: id: int,
 * 			title: string,
 * 			excerpt: html, *keep it short
 * 			content: file name containing the content ex. blog-post.content
 * 			footer: html
 * 			status: Published, Draft, Disabled, Deleted
 * 			url: permalink of the page
 * 			date: date format
 * 			author: String
 * 			author_page: url
 * .json files are named after the title of the blog post or aticle,
 * prepending the id number ex. 1-my-first-blog-post.json,
 * 32-the-last-article.json
 * 
 * .content files contain the body of the article, noting else
 * .content files are simply named after the title of the blog post or article
 * ex. my-first-blog-post.content, the-last-article.content
 */

function get_article_list () {
	global $root;

	$articles_dir = $root . '/content';

	$raw_files = array_diff( scandir ( $articles_dir ), array( '.', '..' ) );
	$content_files = array_values ( preg_grep( '/\.json/' , $raw_files ) );

	return $content_files;
}

/**
 * Uses get_article_list() to retrieve an Assoc array with
 * article id + filename
 * @return Array           Returns an array of ID -> Article File Name
 */
function get_articleID_list () {
	$article_list = get_article_list();
	$id_pattern = '/^[0-9]*/';
	$article_files = array();

	foreach ( $article_list as $article_json ) {
		preg_match( $id_pattern , $article_json, $key_id );
		$article_files[$key_id[0]] = $article_json;
	}

	return $article_files;
}

function get_article_feed ( $args = array() ) {

	global $root;

	$articles = array();
	$articles_content = array();
	$output = '';
	$button_link = '';

	$limit = ( isset( $args['limit'] ) && ! empty( $args['limit'] ) ) ? $args['limit'] : 10;
	$sort = ( isset( $args['sort'] ) && ! empty( $args['sort'] ) ) ? $args['sort'] : 'LMOD_DESC';
	$list_view = ( isset( $args['list_view'] ) && ! empty( $args['list_view'] ) ) ? $args['list_view'] : false;
	if ( ! $list_view ) {
		$image_switch = ( isset( $args['image'] ) && ! empty( $args['image'] ) ) ? $args['image'] : false;
	} else {
		$image_switch = false;
	}

	$article_list = get_article_list();

	// Slice the original array if the count of the array 
	// is higher than the limit
	if ( count( $article_list ) > $limit ) {
		$article_list = array_slice( $article_list, 0, $limit );
	}

	// Attach last modified date to the files array
	if ( $sort == 'LMOD_ASC' || $sort == 'LMOD_DESC' ) {
		foreach ( $article_list as $article_file ) {
			$art_key = get_site_url() . '/content/' . $article_file;
			$articles[$art_key] = filemtime( $root . '/content/' . $article_file );
		}

	} else {
		foreach ( $article_list  as $art_name ) {
			$article_path = get_site_url() . '/content/' . $art_name;
			array_push( $articles, $article_path );
		}
	}

	// Sort the files array depending in the sort option
	switch ( $sort ) {
		case 'LMOD_ASC':
			asort( $articles );
			$articles = array_keys( $articles );
			break;
		case 'ASC':
			sort( $articles );
			break;
		case 'DESC':
			rsort( $articles );
			break;
		default:
			arsort( $articles );
			$articles = array_keys( $articles );
			break;
	}

	// Retrieve data from files
	foreach ( $articles as $article_name ) {
		$full_art = json_decode( file_get_contents( $article_name ), true );
		$the_article = $full_art['article'];
		$output .= sprintf( '<div id="%s" class="article-list-item">', 'post-' . $the_article['id'] );
		$output .= '<div class="article-list-card">';

		if ( $image_switch ) {
			$output .= '<figure class="article-head-image">';
			$output .= sprintf( '<img src="%1$s" alt="%2$s">', $the_article['image'], $the_article['title'] );
			$output .= '</figure>';
		}

		$output .= sprintf( '<h2 class="article-list-title"><a href="%1$s">%2$s</a></h2>', $the_article['url'], $the_article['title'] );

		if ( ! $list_view ) {
			$output .= sprintf( '<div class="article-list-excerpt"><p class="excerpt-text"><i>%s</i></p></div>', $the_article['excerpt'] );
			$button_link = sprintf( '<div class="listing-link button-wrapper"><a href="%s">Continue reading</a></div>', $the_article['url'] );
		}

		$output .= $button_link;
		$output .= '</div><!-- .article-list-card --></div><!-- .article-list-item -->';
	}

	echo $output;
}

function get_article_data ( $article_data ) {
	global $root;

	$file_data = json_decode( file_get_contents( $root . '/content/' . $article_data ), true );

	return $file_data['article'];
}

function get_article ( $article_file, $options = array() ) {
	global $root;
	$data = get_article_data ( $article_file );
	$header = '<header class="article-header"><h1 class="article-title">%1$s</h1><section class="article-excerpt">%2$s</section></header>';
	$footer = '<footer class="article-footer">%s</footer>';

	printf( $header, $data['title'], $data['excerpt'] );
	include_once $root . '/content/' . $data['content'];
	if ( isset( $data['footer'] ) && ! empty( $data['footer'] ) ) {
		printf( $footer, $data['footer'] );
	}
}

function get_article_header ( $article_file ) {
	global $root;

	$data = get_article_data ( $article_file );
	$output = '<header class="article-header">';
	$output .= '<h1 class="article-title">';
	$output .= $data['title'];
	$output .= '</h1>';
	$output .= '<section class="article-excerpt">';
	$output .= $data['excerpt'];
	$output .= '</section></header>';

	return $output;
}

function get_article_title ( $article_file ) {
	global $root;
	$data = get_article_data ( $article_file );
	return $data['title'];
}

function get_article_excerpt ( $article_file ) {
	global $root;
	$data = get_article_data ( $article_file );
	return $data['excerpt'];
}

function get_article_image ( $article_file ) {
	global $root;
	$data = get_article_data( $article_file );
	return $data['image'];
}

function get_article_date ( $article_file ) {
	global $root;
	$data = get_article_data ( $article_file );
	return $data['date'];
}

function get_article_author ( $article_file ) {
	global $root;
	$data = get_article_data ( $article_file );
	return $data['author'];
}

function get_article_authorpage ( $article_file ) {
	global $root;
	$data = get_article_data ( $article_file );
	return $data['author_page'];
}

function get_article_status ( $article_file ) {
	global $root;
	$data = get_article_data ( $article_file );
	return $data['status'];
}

function get_article_url ( $article_file ) {
	global $root;
	$data = get_article_data ( $article_file );
	return $data['url'];
}

/***************************************/
/*               CLASSES               */
/***************************************/

/**
 * Pagination class
 */
class paginate {
	private $items_page;
	private $item_array;
	private $items_count;
	private $actual_page;
	public $page_content;
	
	public function __construct( $args = array() ) {
		$this->items_page = ( empty ( $args['items_per_page'] ) ) ? '5' : $args['items_per_page'];
		$this->item_array = $args['items'];
		$this->items_count = count( $this->item_array );
		$this->actual_page = ( empty ( $_GET['p'] ) || $_GET['p'] == null ) ? 0 : $_GET['p'];
		
		$start_item = $this->actual_page * $this->items_page;
		
		if ( $this->items_count <= $this->items_page ) {
			$this->page_content = $this->item_array;
		} else {
			$this->page_content = array_slice( $this->item_array, $start_item, $this->items_page );
		}
		
	}
	
	private function get_pages () {
		$n = ceil ( $this->items_count / $this->items_page );
		
		$counter = 0;
		
		$pages = array();
		
		while ( $counter < $n ) :
			$pages[] = $counter;
			$counter++;
		endwhile;
		
		return $pages;
	}
	
	public function pages_bar ( $options ) {
		$bar_text = ( empty ( $options['bar_text'] ) || $options['bar_text'] == null ) ? 'Pages' : $options['bar_text'];
		
		$ul_class = 'paginator';
		
		if ( $this->items_count > $this->items_page ) {
			printf ( '<ul class="%1$s"><li class="bar-text">%2$s</li>', $ul_class, $bar_text );
			
			
			if ( $this->actual_page > 0 ) {
				$prev_url = 'href="' . get_site_url() . '/' . get_current_file() . '?p=' . ( $this->actual_page - 1 ) . '"';
				printf( '<li class="prev-page page-button"><a %s>&lt;</a></li>', $prev_url );
			}
			
			foreach ( $this->get_pages() as $page ) :
				
				$url;
				
				if ( $page == ( $this->actual_page) ) {
					$url = '';
					$li_class = 'page-button current-page';
				} else {
					$url = 'href="' . get_site_url() . '/' . get_current_file() . '?p=' . $page . '"';
					$li_class = 'page-button';
				}
				
				printf( '<li class="%1$s"><a %3$s>%2$d</a></li>', $li_class, ( $page + 1 ), $url );
			endforeach;
			
			if ( $this->actual_page < ( count ( $this->get_pages() ) - 1 )  ) {
				$next_url = 'href="' . get_site_url() . '/' . get_current_file() . '?p=' . ( $this->actual_page + 1 ) . '"';
				printf( '<li class="next-page page-button"><a %s>&gt;</a></li>', $next_url );
			}
			
			printf ( '</ul>' );
		}
	}
	
}

/**********************************/
/*         ERROR HANDLERS         */
/**********************************/
/**
 * Send the errors to the javascript console, it is recommended to run
 * this function in the footer file of your website right before the
 * closing body tag
 * @return [array] Contains categorize error in the form of
 *                 Error-Description => Error-Type
 */
function get_errors () {
	global $site_errors;

	$output = '<script type="text/javascript">';

	if ( isset ( $site_errors ) && ! empty ( $site_errors ) ) {

		foreach ( $site_errors as $error => $type ) {
			$output .= sprintf( 'console.error( "%1$s : %2$s" );', $type, $error );
		}

	} else {
		$output .= 'console.log("NO ERRORS FOUND!");';
	}

	$output .= '</script>';

	echo $output;		
}

/**
 * Sends the process time to the console log through javascript
 */
function get_process_time () {
	$end_time = microtime( true );
	$time_p = $end_time - START_TIME;
	$output = sprintf( '<script type="text/javascript">console.log( "Your php site ran in: " + %s );</script>', $time_p );

	echo $output;
}

?>