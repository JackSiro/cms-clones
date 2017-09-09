<?php
/*
	don't allow this page to be requested directly from browser 
*/
	if (!defined('AS_VERSION')) {
			header('Location: /');
			exit;
	}
/*
	Definitions
*/	
	define('MYTHEME_DIR', dirname( __FILE__ ));
	define('MYTHEME_URL',  as_opt('site_url') . 'as-theme/' . as_get_site_theme() . '/');
	
	// set layout cookies
	$layout = as_opt('it_layout_lists');
	if($layout)
		setcookie('layoutdefault', $layout, time()+86400*3650, '/', AS_COOKIE_DOMAIN);
	else
		setcookie('layoutdefault', 'masonry', time()+86400*3650, '/', AS_COOKIE_DOMAIN);

	require MYTHEME_DIR. '/functions.php';		
	require MYTHEME_DIR. '/as-layer-base.php';		
	
	if(isset($_REQUEST['qat_ajax_req'])){
		as_register_layer('/as-layer-ajax.php', 'AST Ajax Theme Layer', MYTHEME_DIR , MYTHEME_URL );
		die();
	}

	

/*
	Omit PHP closing tag to help avoid accidental output
*/