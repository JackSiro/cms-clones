<?php
/*
	don't allow this page to be requested directly from browser 
*/
	if (!defined('AS_VERSION')) {
			header('Location: /');
			exit;
	}
/*
	Theme Override
*/		
	
class as_html_theme extends as_html_theme_base
{
	/*
	* doctype for preparing content before setting up the theme
	*
	* @since 1.0.0
	* @compatible yes
	*/	
		function doctype(){
			// print HTML5 doctype with full plugin compatibility
			ob_start();
			as_html_theme_base::doctype();
			$output = ob_get_clean();
			$doctype = str_replace('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">', '<!DOCTYPE html>', $output);
			$this->output($doctype);
		}


	/*
	* build html layout
	*
	* @since 1.0.0
	* @compatible no
	*/	
		function html()
		{
			$this->output(
				'<html>'
			);
			
			$this->head();
			$this->body();
			
			$this->output(
				'</html>'
			);
		}
	/*
	* responsive view point
	*
	* @since 1.0.0
	* @compatible yes
	*/	
		function head_metas()
		{
			as_html_theme_base::head_metas();
			$this->output('<meta name="viewport" content="width=device-width, initial-scale=1.0">');
		}
	/*
	* custom CSS
	*
	* @since 1.0.0
	* @compatible yes
	*/	
		function head_css()
		{
			// prepare CSS
			$this->output('<link rel="stylesheet" type="text/css" href="' .$this->rooturl .'css/bootstrap.min.css"/>');
			if(as_opt('it_custom_style_created'))
				$this->output('<link rel="stylesheet" type="text/css" href="' .$this->rooturl.'css/dynamic.css"/>');
			else
				$this->output('<style type="text/css">' . as_opt('it_custom_css') . '</style>');
				
			if (($this->template=='purchase') or ($this->template=='order' && substr(as_get_state(),0,4)=='edit')){
				$this->output('<link rel="stylesheet" type="text/css" href="' .$this->rooturl .'css/purchase.css"/>');
			}
			if($this->request=='admin/it_options'){
				$this->output('<link rel="stylesheet" type="text/css" href="' . $this->rooturl . 'css/admin.css"/>');
				$this->output('<link rel="stylesheet" type="text/css" href="' . $this->rooturl . 'css/spectrum.css"/>'); // color picker
			}

			$googlefonts = json_decode(as_opt('it_typo_googlefonts'), true);
			if (isset($googlefonts) && !empty($googlefonts))
				foreach ($googlefonts as $font_name) {
					$font_name = str_replace(" ", "+", $font_name);
					$link      = 'http://fonts.googleapis.com/css?family=' . $font_name;
					$this->output('<link href="' . $link . '" rel="stylesheet" type="text/css">');
				}

			$fav = as_opt('it_favicon_url');
			if( $fav )
				$this->output('<link rel="shortcut icon" href="' .  $fav . '" type="image/x-icon">');

			as_html_theme_base::head_css();
		}	
	/*
	* include JS files
	*
	* @since 1.0.0
	* @compatible yes
	*/	
		function head_script()
		{
			as_html_theme_base::head_script();
			// set JS variables
			$variables = '';
			$variables .= 'it_root_url = "' . MYTHEME_URL .'";';
			$variables .= 'it_featured_url_abs = "' . as_opt('it_featured_url_abs') .'";';
			$variables .= 'it_ajax_category_url = "' . MYTHEME_URL . 'ajax_category.php";';
			$variables .= 'it_new_category_icon = "' . as_opt('it_new_cat_icon') .'";';
			$variables .= 'it_ajax_featured_upload_url = "' . MYTHEME_URL . 'ajax_upload.php";';
			$variables .= 'it_ajax_featured_delete_url = "' . MYTHEME_URL . 'ajax_delete.php";';
			$variables .= 'it_ajax_infinite_page_url = "' . MYTHEME_URL . 'ajax_infinite_page.php";';
			$variables .= 'it_ajax_infinite_page_number = 2;';
			$variables .= 'it_ajax_infinite_page_items_count = ' .as_opt('page_size_home') . ';';
			if(as_opt('it_infinite_scroll_auto_enable'))
				$variables .= 'it_ajax_infinite_autoload = 1;';
			else
				$variables .= 'it_ajax_infinite_autoload = 0;';
			$this->output('<script>' . $variables . '</script>');
			// prepare JS scripts include Bootstrap's JS file
			$this->output('<script src="'.$this->rooturl.'js/bootstrap.min.js" type="text/javascript"></script>');
			$this->output('<script src="'.$this->rooturl.'js/isotope.min.js" type="text/javascript"></script>');
			$this->output('<script src="'.$this->rooturl.'js/main.js" type="text/javascript"></script>');
			if (($this->template=='purchase') or ($this->template=='order' && substr(as_get_state(),0,4)=='edit')){
				$this->output('<script src="'.$this->rooturl.'js/purchase.js" type="text/javascript"></script>');
				$this->output('<script src="'.$this->rooturl.'js/magicsuggest.min.js" type="text/javascript"></script>');
				$this->output('<script src="'.$this->rooturl.'js/uploadfile.min.js" type="text/javascript"></script>');
			}
			if($this->request=='admin/it_options'){
				$this->output('<script src="'.$this->rooturl.'js/admin.js" type="text/javascript"></script>');
				$this->output('<script src="'.$this->rooturl.'js/jquery.uploadfile.min.js" type="text/javascript"></script>');
				$this->output('<script src="'.$this->rooturl.'js/chosen.jquery.min.js" type="text/javascript"></script>');
				$this->output('<script src="'.$this->rooturl.'js/spectrum.js" type="text/javascript"></script>');
			}
		}	
	
	/*
	* Load main body content
	*
	* @since 1.0.0
	* @compatible no
	*/
		function body_content()
		{
			$this->body_prefix();
			$this->notices();

			$this->widgets('full', 'top');
			
			// Q2A header
			$this->widgets('full', 'high');
			$this->header();

			$this->output('<div class="container-fluid' . (as_opt('it_nav_fixed')?' fixed-nav-container':'') . '">');

			// list of pages with no sidebar
			$pages = array("qa", "orders", "user", "user-wall", "user-activity", "Linux", "user-orders", "user-answers");
			// pages without sidebar
			if(in_array($this->template, $pages)){ 
				// Q2A default body
				$this->output('<section class="as-main-content col-md-12">');
				$this->main();     
				$this->output('</section>');
			}// pages with sidebar
			else{
				$postid = @$this->content['q_view']['raw']['postid'];
				if(isset($postid)){
					require_once AS_INCLUDE_DIR.'as-db-metas.php';
					$image = as_db_postmeta_get($postid, 'et_featured_image');
					if( (!(empty($image)))&& (substr(as_get_state(),0,4)!='edit') )
						$this->output('<img class="featured-image img-thumbnail" src="'.as_opt('it_featured_url_abs')  .'featured/'. $image.'"/>');
				}
				// Q2A sidebar
				if ( ($this->request=='admin/it_options') && (as_get_logged_in_level() >= AS_USER_LEVEL_ADMIN) ){
					// Q2A default body
					$this->output('<section class="as-main-content col-md-12">');
					$this->main();     
					$this->output('</section>');
				}else{
					$this->output('<aside class="as-main-sidebar col-md-3">');
					$this->sidepanel();
					$this->output('</aside>');

					// Q2A default body
					$this->output('<section class="as-main-content col-md-9">');
					$this->main();     
					$this->output('</section>');
				}
			}
			

			$this->output('</div>');
			
			// Q2A Footer
			$this->widgets('full', 'low');
			$this->footer();

			

			$this->widgets('full', 'bottom');
			
			$this->body_suffix();
		}
	/*
	* main_parts
	* load all basic part of content, here changed to load them from php files
	*
	* @since 1.0.0
	* @compatible yes
	*/	
		function main_parts($content)
		{
			$this->output('<article class="as-q-content-article' . (as_opt('it_layout_lists')=='qlist'?' qlist-defaul':'') . '">');
			as_html_theme_base::main_parts($content);
			$this->output('</article>');
		}	
	/*
	* header
	* loads "HEADER" place including user navigation and logo
	*
	* @since 1.0.0
	* @compatible no
	*/	
		function header()
		{
			$this->output('<header class="main-header">');
			
			$this->output('<nav id="menu" class="navbar navbar-default main-navbar' . (as_opt('it_nav_fixed')?' navbar-fixed-top':'') . '" role="navigation">');
			
			$nav_type = as_opt('it_nav_type');
			if($nav_type == 'standard')
				$this->show_nav_standard('main','collapse navbar-collapse nav-main');
			else
				$this->show_nav('main','collapse navbar-collapse nav-main');
			
			$this->output('</nav>');
			
			$this->output('</header>');
		}
		
		function search()
		{
			$search=$this->content['search'];
			
			$this->output(
				'<form '.$search['form_places'].' class="navbar-form" role="search">',
				@$search['form_extra']
			);
			$this->output('<div class="input-group search-group">
				<input type="text" '.$search['field_places'].' value="'.@$search['value'].'" class="form-control" placeholder="Search">
				<span class="input-group-btn">
					<button type="reset" class="btn btn-default">
						<span class="fa fa-times">
							<span class="sr-only">Close</span>
						</span>
					</button>
					<button type="submit" class="btn btn-default">
						<span class="fa fa-search">
							<span class="sr-only">Search</span>
						</span>
					</button>
				</span>
				</div>
			');
			
			$this->output(
				'</form>'
			);
		}
	/*
	* show_nav *** Replacement for Q2A theme function "nav()"
	* shows navigation menus
	*
	* @since 1.0.0
	* @compatible no
	*/	
		function show_nav($navtype, $class=null, $level=null)
		{
			$navigation=@$this->content['navigation'][$navtype];//vardump($navigation);
			if (($navtype=='main') && isset($navigation)){
				//Responsive Navigation button
				$this->output('<div class="navbar-header">');
				$this->logo();
				$this->output('<button class="navbar-toggle collapsed" data-target=".nav-main" data-toggle="collapse" type="button"><span class="sr-only">Toggle navigation</span><span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span></button>');
				$this->output('</div>');
				// Main Nav Items
				$orders_nav = $navigation['orders'];
				$this->output('<div class="' . $class . '">');
				$this->output('<ul class="as-nav-main-list nav navbar-nav navbar-right ">');

				$page_order = '';
				if( (($this->template=='qa') or ($this->template=='orders')) && ((as_opt('it_layout_masonry_list')!='qlist') && as_opt('it_layout_choose')) )
					$page_order = '
						<li class="divider"></li>
						<li role="presentation" class="dropdown-header">List Layout</li>
						<li class="dropdown-layout-container">
							<a href="#" id="masonry-layout-btn" class="btn btn-default" title="Masonry"><i class="fa fa-th"></i></a>
							<a href="#" id="list-layout-btn" class="btn btn-default" title="List"><i class="fa fa-th-list"></i></a>
						</li>

					';
					
				$this->output('
					<li class="dropdown as-nav-main-single-item as-nav-main-single-submit as-nav-main-single-purchase">
						<a class="as-submit-item dropdown-toggle" data-toggle="dropdown" href="' . $orders_nav['url'] .'">Browse</span></a>

							<ul class="dropdown-menu with-arrow sub-nav-brows" role="menu">
								<li><a href="' . as_path_html('orders') . '">' . as_lang('main/nav_qs') . '</a></li>
								<li><a href="' . as_path_html('orders', array('sort' => 'hot')) . '">' . as_lang_html('main/nav_hot') . '</a></li>
								<li><a href="' . as_path_html('orders', array('sort' => 'votes')) . '">' . as_lang('main/nav_most_votes') . '</a></li>
								<li class="divider"></li>
								<li><a href="' . as_path_html('activity') . '">'  . as_lang_html('main/nav_activity') . '</a></li>
								' . $page_order . '
							</ul>
					</li>
					<li class="as-nav-main-single-item as-nav-main-single-submit as-nav-main-single-purchase">
						<a class="as-submit-item" href="' . $navigation['purchase']['url'] .'">' . as_lang_html('main/nav_purchase') . '</a>
					</li>
				');
				if (as_is_logged_in()) {
					$handle =  as_get_logged_in_handle();
					$user_nav = array();
					$this->output('
						<li class="as-nav-main-single-item as-nav-main-single-profile dropdown">
							<a class="dropdown-toggle" data-toggle="dropdown" href="' . as_path_html('user/' .$handle) .'">' . $handle .'</a>
							<ul class="user-nav dropdown-menu with-arrow">
					');
					if (as_get_logged_in_level() >= AS_USER_LEVEL_EDITOR) {
						$user_nav['admin'] = array('label' => as_lang_html('main/nav_admin'), 'url' => as_path_html('admin'));
					}
						// Theme Options
					if (as_get_logged_in_level() >= AS_USER_LEVEL_ADMIN) {
						$user_nav['it_options'] = array(
							'label' => 'Theme Options',
							'url' => as_path_html('admin/it_options'),
						);
						if ($this->request == 'admin/it_options'){
							$user_nav['it_options']['selected'] = true;
						}
					}
					
					$logout = $this->content['navigation']['user']['logout'];
					unset($this->content['navigation']['user']['logout']);
					array_merge($user_nav , $this->content['navigation']['user']);
					$user_nav['profile'] = array('label' => 'profile', 'url' => as_path_html('user/' . $handle));
					
					if(!(AS_FINAL_EXTERNAL_USERS)) 
						$user_nav['account'] = array('label' => 'account', 'url' => as_path_html('account'));
					$user_nav['favorites'] = array('label' => 'favorites', 'url' => as_path_html('favorites'));
					if(!(AS_FINAL_EXTERNAL_USERS))
						$user_nav['wall'] = array('label' => 'wall', 'url' => as_path_html('user/'.$handle.'/wall'), 'icon' =>'icon-edit');
					$user_nav['recent_activity'] = array('label' => 'recent activity', 'url' => as_path_html('user/'.$handle.'/activity'), 'icon' =>'icon-time');
					$user_nav['all_orders'] = array('label' => 'all orders', 'url' => as_path_html('user/'.$handle.'/orders'), 'icon' =>'icon-order');
					$user_nav['all_answers'] = array('label' => 'all answers', 'url' => as_path_html('user/'.$handle.'/answers'), 'icon' =>'icon-answer');
					$user_nav['logout'] = $logout;
					$navigation=@$user_nav;
					foreach ($navigation as $a) {
                        if (isset($a['url'])) {
                            echo '<li' . (isset($a['selected']) ? ' class="active"' : '') . '><a href="' . @$a['url'] . '" title="' . @$a['label'] . '">' . @$a['label'] . '</a></li>';
							if($a['label']=='Theme Options')
								echo '<li class="divider"></li>';
                        }
                    }
					$this->output('
							</ul>
						</li>
					');
				}else{
					$login=@$this->content['navigation']['user']['login'];
					$register=@$this->content['navigation']['user']['register'];
					if (isset($login) && !AS_FINAL_EXTERNAL_USERS) {
						$this->output('
							<li class="as-nav-main-single-item as-nav-main-single-submit as-nav-main-single-purchase dropdown">
								<a class="dropdown-toggle as-submit-item" data-toggle="dropdown" href="' . $login['url'] .'">Login</a>
								<ul class="user-nav dropdown-menu with-arrow">
						');
						$this->output(
								'<form class="form-signin" id="as-loginform" action="'.$login['url'].'" method="post">',
								'<input class="form-control" type="text" id="as-userid" name="emailhandle" placeholder="'.trim(as_lang_html(as_opt('allow_login_email_only') ? 'users/email_label' : 'users/email_handle_label'), ':').'" />',
								'<input class="form-control" type="password" id="as-password" name="password" placeholder="'.trim(as_lang_html('users/password_label'), ':').'" />',
								'<div id="as-rememberbox"><input type="checkbox" name="remember" id="as-rememberme" value="1"/>',
								'<label for="as-rememberme" id="as-remember">'.as_lang_html('users/remember').'</label></div>',
								'<input type="hidden" name="code" value="'.as_html(as_get_form_security_code('login')).'"/>',
								'<input type="submit" class="btn btn-primary btn-block" value="'.$login['label'].'" id="as-login" name="dologin" />',
								'<hr>',
								'<p class="text-muted text-center"><small>Do not have an account?</small></p>
								<a class="btn btn-default btn-block" href="' . $register['url'] . '">Sign Up</a>',
							'</form>'
						);
						$this->output('
								</ul>
							</li>'
						);
					}
				}

				$this->output('</ul>');
				//vardump(@$this->content['navigation']['user']);
							
				$this->search();
				$this->output('</div>');
				//unset($navigation);
			}else
			if (isset($navigation) || ($navtype=='user')) {
				$this->output('<nav class="' . $class . '">');
				
				if ($navtype=='user')
					$this->logged_in();
					
				// reverse order of 'opposite' items since they float right
				foreach (array_reverse($navigation, true) as $key => $navlink)
					if (@$navlink['opposite']) {
						unset($navigation[$key]);
						$navigation[$key]=$navlink;
					}
				
				$this->set_context('nav_type', $navtype);
				$this->nav_list($navigation, 'nav-'.$navtype, $level);
				$this->nav_clear($navtype);
				$this->clear_context('nav_type');
	
				$this->output('</nav>');
			}
		}
	/*
	* show_nav_standard *** Replacement for Q2A theme function "nav()" & Theme function "show_nav"
	* shows navigation menus
	*
	* @since 1.1.0
	* @compatible no
	*/	
		function show_nav_standard($navtype, $class=null, $level=null)
		{
			$navigation=@$this->content['navigation'][$navtype];
			if (($navtype=='main') && isset($navigation)){
				//Responsive Navigation button
				$this->output('<div class="navbar-header">');
				$this->logo();
				$this->output('<button class="navbar-toggle collapsed" data-target=".nav-main" data-toggle="collapse" type="button"><span class="sr-only">Toggle navigation</span><span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span></button>');
				$this->output('</div>');
				
				$this->output('<div class="' . $class . '">');
				$this->output('<ul class="as-nav-main-list nav navbar-nav navbar-right ">');

				$page_order = '';
				if( (($this->template=='qa') or ($this->template=='orders')) && ((as_opt('it_layout_masonry_list')!='qlist') && as_opt('it_layout_choose')) )
					$page_order = '
						<li class="divider"></li>
						<li role="presentation" class="dropdown-header">List Layout</li>
						<li class="dropdown-layout-container">
							<a href="#" id="masonry-layout-btn" class="btn btn-default" title="Masonry"><i class="fa fa-th"></i></a>
							<a href="#" id="list-layout-btn" class="btn btn-default" title="List"><i class="fa fa-th-list"></i></a>
						</li>

					';
			
			$this->set_context('nav_type', $navtype);
			// reverse order of 'opposite' items since they float right
			foreach (array_reverse($navigation, true) as $key => $navlink) {
				if (@$navlink['opposite']) {
					unset($navigation[$key]);
					$navigation[$key] = $navlink;
				}
			}
			$index = 0;
			foreach ($navigation as $key => $navlink) {
				$this->set_context('nav_key', $key);
				$this->set_context('nav_index', $index++);
				// $this->nav_item($key, $navlink, $class, $level);
				$suffix = strtr($key, array( // map special character in navigation key
					'$' => '',
					'/' => '-',
				));

				$this->output('<li class="as-nav-main-single-item as-'.$class.'-item'.(@$navlink['opposite'] ? '-opp' : '').
					(@$navlink['state'] ? (' as-'.$class.'-'.$navlink['state']) : '').' as-'.$class.'-'.$suffix.'">');
				$this->nav_link($navlink, $class);

				if (count(@$navlink['subnav']))
					$this->nav_list($navlink['subnav'], $class, 1+$level);

				$this->output('</li>');
			}
			$this->clear_context('nav_key');
			$this->clear_context('nav_index');
			$this->clear_context('nav_type');
		

			if (as_is_logged_in()) {
					$handle =  as_get_logged_in_handle();
					$user_nav = array();
					$this->output('
						<li class="as-nav-main-single-item as-nav-main-single-profile dropdown">
							<a class="dropdown-toggle" data-toggle="dropdown" href="' . as_path_html('user/' .$handle) .'">' . $handle .'</a>
							<ul class="user-nav dropdown-menu with-arrow">
					');
					// Theme Options
					if (as_get_logged_in_level() >= AS_USER_LEVEL_ADMIN) {
						$user_nav['it_options'] = array(
							'label' => 'Theme Options',
							'url' => as_path_html('admin/it_options'),
						);
						if ($this->request == 'admin/it_options'){
							$user_nav['it_options']['selected'] = true;
						}
					}
					
					$logout = $this->content['navigation']['user']['logout'];
					unset($this->content['navigation']['user']['logout']);
					array_merge($user_nav , $this->content['navigation']['user']);
					$user_nav['profile'] = array('label' => 'profile', 'url' => as_path_html('user/' . $handle));
					
					if(!(AS_FINAL_EXTERNAL_USERS)) 
						$user_nav['account'] = array('label' => 'account', 'url' => as_path_html('account'));
					$user_nav['favorites'] = array('label' => 'favorites', 'url' => as_path_html('favorites'));
					if(!(AS_FINAL_EXTERNAL_USERS))
						$user_nav['wall'] = array('label' => 'wall', 'url' => as_path_html('user/'.$handle.'/wall'), 'icon' =>'icon-edit');
					$user_nav['recent_activity'] = array('label' => 'recent activity', 'url' => as_path_html('user/'.$handle.'/activity'), 'icon' =>'icon-time');
					$user_nav['all_orders'] = array('label' => 'all orders', 'url' => as_path_html('user/'.$handle.'/orders'), 'icon' =>'icon-order');
					$user_nav['all_answers'] = array('label' => 'all answers', 'url' => as_path_html('user/'.$handle.'/answers'), 'icon' =>'icon-answer');
					$user_nav['logout'] = $logout;
					$navigation=@$user_nav;
					foreach ($navigation as $a) {
                        if (isset($a['url'])) {
                            echo '<li' . (isset($a['selected']) ? ' class="active"' : '') . '><a href="' . @$a['url'] . '" title="' . @$a['label'] . '">' . @$a['label'] . '</a></li>';
							if($a['label']=='Theme Options')
								echo '<li class="divider"></li>';
                        }
                    }
					$this->output($page_order);
					$this->output('
							</ul>
						</li>
					');
				}else{
					$login=@$this->content['navigation']['user']['login'];
					$register=@$this->content['navigation']['user']['register'];
					if (isset($login) && !AS_FINAL_EXTERNAL_USERS) {
						$this->output('
							<li class="as-nav-main-single-item as-nav-main-single-submit as-nav-main-single-purchase dropdown">
								<a class="dropdown-toggle as-submit-item" data-toggle="dropdown" href="' . $login['url'] .'">Login</a>
								<ul class="user-nav dropdown-menu with-arrow">
						');
						$this->output(
								'<form class="form-signin" id="as-loginform" action="'.$login['url'].'" method="post">',
								'<input class="form-control" type="text" id="as-userid" name="emailhandle" placeholder="'.trim(as_lang_html(as_opt('allow_login_email_only') ? 'users/email_label' : 'users/email_handle_label'), ':').'" />',
								'<input class="form-control" type="password" id="as-password" name="password" placeholder="'.trim(as_lang_html('users/password_label'), ':').'" />',
								'<div id="as-rememberbox"><input type="checkbox" name="remember" id="as-rememberme" value="1"/>',
								'<label for="as-rememberme" id="as-remember">'.as_lang_html('users/remember').'</label></div>',
								'<input type="hidden" name="code" value="'.as_html(as_get_form_security_code('login')).'"/>',
								'<input type="submit" class="btn btn-primary btn-block" value="'.$login['label'].'" id="as-login" name="dologin" />',
								'<hr>',
								'<p class="text-muted text-center"><small>Do not have an account?</small></p>
								<a class="btn btn-default btn-block" href="' . $register['url'] . '">Sign Up</a>',
							'</form>'
						);
						$this->output($page_order);
						$this->output('
								</ul>
							</li>'
						);
					}
				}
				$this->output('</ul>');
				//vardump(@$this->content['navigation']['user']);
							
				$this->search();
				$this->output('</div>');
				//unset($navigation);
			}else
			if (isset($navigation) || ($navtype=='user')) {
				$this->output('<nav class="' . $class . '">');
				
				if ($navtype=='user')
					$this->logged_in();
					
				// reverse order of 'opposite' items since they float right
				foreach (array_reverse($navigation, true) as $key => $navlink)
					if (@$navlink['opposite']) {
						unset($navigation[$key]);
						$navigation[$key]=$navlink;
					}
				
				$this->set_context('nav_type', $navtype);
				$this->nav_list($navigation, 'nav-'.$navtype, $level);
				$this->nav_clear($navtype);
				$this->clear_context('nav_type');
	
				$this->output('</nav>');
			}
		}
	/*
	* nav_item
	* shows navigation menu items
	* Add [ 'as-nav-main-sub-' + class ] to navigation item's class
	*
	* @since 1.0.0
	* @compatible yes
	*/	
		function nav_item($key, $navlink, $class, $level=null)
		{
			// main navigation items with sub navigations will have a new class
			if ( $level>=1)
				$class.='-sub';
			// main navigation items with No sub navigations will have a new class
			if ($class=='nav-main' && !count(@$navlink['subnav']))
				$class.='-single';
			// main navigation items which are current page will have a new class
			if (@$navlink['selected'])
				$navlink['state']='selected';

			as_html_theme_base::nav_item($key, $navlink, $class, $level=null);
		}
	/*
	* nav_item
	* shows navigation menu items
	* Add [ 'as-nav-main-sub-' + class ] to navigation item's class
	*
	* @since 1.0.0
	* @compatible no
	*/			
		function page_title_error()
		{
			if( ($this->template=='order') or ($this->template=='purchase') ){
				$this->output('<h1>');
				$this->title();
				$this->output('</h1>');
				if (isset($this->content['error']))
					$this->error(@$this->content['error']);
			}elseif( ($this->template=='place') or ($this->template=='orders') ){
					// fill array with breadcrumb fields and show them
					$bc = array(); // breadcrumb
					$bc[0]['title']=as_opt('site_title');
					$bc[0]['content']='<i class="fa fa-home"></i>';
					$bc[0]['url']=as_opt('site_url');
					if($this->template=='place'){
						$bc[1]['title']='Places';
						$bc[1]['content']='Places';
						$bc[1]['url']=as_path_html('places');
						$req = explode('/',$this->request);
						$place = $req[count($req)-1];
						$bc[2]['title']= $place;
						$bc[2]['content']='Place "' . $place . '"';
						$bc[2]['url']=as_path_html($this->request, null, null, null, null);
					}elseif($this->template=='orders'){
						$req = explode('/',$this->request);
						$cat = $req[count($req)-1];
						if(count($req)>1){
							$category_name = $this->content["q_list"]["qs"][0]["raw"]["categoryname"];
							$bc[1]['title']='Categories';
							$bc[1]['content']='Categories';
							$bc[1]['url']=as_path_html('categories');
							$bc[2]['title']= $category_name;
							$bc[2]['content']= $category_name ;
							$bc[2]['url']=as_path_html($this->request, null, null, null, null);
						}else{
							unset($bc);
						}
					}
					if(isset($bc)){
						$this->output('<div class="header-buttons btn-group btn-breadcrumb pull-left">');
						foreach($bc as $item)
							$this->output(' <a href="' . $item['url'] . '" title="' . $item['title'] . '" class="btn btn-default">' . $item['content'] . '</a>');
						$this->output('</div>');
					}
			}else{
				as_html_theme_base::page_title_error();
			}
			if( ($this->template=='admin') or ($this->template=='users')  or ($this->template=='user') or (as_opt('it_nav_type') == 'standard'))
				$this->show_nav('sub','nav navbar-nav sub-navbar pull-right');
			as_html_theme_base::q_view_clear();
		}
	/*
	* q_view_main
	* form is limited to order buttons and comment section
	*
	* @since 1.0.0
	* @compatible no
	*/
		function q_view_main($q_view)
		{
			$this->output('<div class="as-q-view-main">');

			$this->view_count($q_view);
			$this->q_view_content($q_view);
			$this->q_view_extra($q_view);
			$this->q_view_follows($q_view);
			$this->q_view_closed($q_view);
			$this->post_places($q_view, 'as-q-view');
			$this->post_avatar_meta($q_view, 'as-q-view');

			if (isset($q_view['main_form_places']))
				$this->output('<form '.$q_view['main_form_places'].'>'); // form for buttons on order

			$this->q_view_buttons($q_view);
			$this->c_list(@$q_view['c_list'], 'as-q-view');
			
			if (isset($q_view['main_form_places'])) {
				$this->form_hidden_elements(@$q_view['buttons_form_hidden']);
				$this->output('</form>');
			}
			
			$this->c_form(@$q_view['c_form']);
			
			$this->output('</div> <!-- END as-q-view-main -->');
		}
	/*
	* q_view
	* Question Item : favorite is removed
	*
	* @since 1.0.0
	* @compatible yes
	*/
		function q_view($q_view)
		{
			if (!empty($q_view)) {
				$this->output('<div class="as-q-view'.(@$q_view['hidden'] ? ' as-q-view-hidden' : '').rtrim(' '.@$q_view['classes']).'"'.rtrim(' '.@$q_view['places']).'>');

				$this->q_view_main($q_view);
				$this->q_view_clear();
				
				$this->output('</div> <!-- END as-q-view -->', '');
			}
		}		
	/*
	* q_list
	* add excerpt
	* add featured image
	* count of order favourites
	* add comments count for orders
	* add a class to all order list items
	*
	* @since 1.0.0
	* @compatible no
	*/
		function q_list($q_list)
		{
			if(as_opt('it_layout_lists') == 'qlist'){
				as_html_theme_base::q_list($q_list);
				return;
			}
			if (count(@$q_list['qs'])) { // first check it is not an empty list and the feature is turned on
			//	Collect the order ids of all items in the order list (so we can do this in one DB query)
				$postids=array();
				foreach ($q_list['qs'] as $order)
					if (isset($order['raw']['postid']))
						$postids[]=$order['raw']['postid'];
				
				if (count($postids)) {
				//	Retrieve favourite count
					$userid = as_get_logged_in_userid();
					$result=as_db_query_sub('SELECT userid,entityid FROM ^userfavorites WHERE entitytype=$ AND entityid IN (#)', 'Q', $postids);
					while ($row=mysqli_fetch_row($result)){
						if ($row[0]==$userid)// loged in user favorited this post
							$faved_post[$row[1]] = 1;

						if(isset($favs[ $row[1] ])){
							$favs[ $row[1] ] = $favs[ $row[1] ] + 1;
						} else
							$favs[ $row[1] ]=1;
						}
				//	Retrieve comment count
					$result=as_db_query_sub('SELECT postid,parentid FROM ^posts WHERE type=$ AND parentid IN (#)', 'C', $postids);
					$comment_list=as_db_read_all_assoc($result, 'postid');
					foreach ($comment_list as $key => $value) 
						if(isset($comments[ $value['parentid'] ]))
							$comments[ $value['parentid'] ] = $comments[ $value['parentid'] ]+1;
						else
							$comments[ $value['parentid'] ]=1;
					if(as_opt('it_excerpt_field_enable') or as_opt('it_enable_except')){
					//	Get the regular expression fragment to use for blocked words and the maximum length of content to show
						$blockwordspreg=as_get_block_words_preg();
						if(as_opt('it_excerpt_field_enable')){
							$maxlength= as_opt('it_excerpt_field_length');
							//	Retrieve Excerpt Text for all orders
							$result=as_db_query_sub('SELECT postid,content FROM ^postmetas WHERE postid IN (#) AND title=$', $postids,'et_excerpt_text');
							$excerpt_text=as_db_read_all_assoc($result, 'postid');
							// set excerpt from field info
							foreach ($q_list['qs'] as $index => $order) {
								// from field
								if(! empty( $excerpt_text[$order['raw']['postid']]['content']) ){
									$text=as_viewer_text($excerpt_text[$order['raw']['postid']]['content'], '', array('blockwordspreg' => $blockwordspreg));
									$text=as_shorten_string_line($text, $maxlength);
									$q_list['qs'][$index]['excerpt']=as_html($text);
								// from post content
								}elseif(as_opt('it_enable_except')){
									// Retrieve the content for these orders from the database and put into an array
									$result=as_db_query_sub('SELECT postid, content, format FROM ^posts WHERE postid IN (#)', $postids);
									$postinfo=as_db_read_all_assoc($result, 'postid');
									$thispost = @$postinfo[$order['raw']['postid']];
									if (isset($thispost)) {
										$text=as_viewer_text($thispost['content'], $thispost['format'], array('blockwordspreg' => $blockwordspreg));
										$text=as_shorten_string_line($text, $maxlength);
										$q_list['qs'][$index]['excerpt']=as_html($text);
									}
								}
							}

								
						}else{ // as_opt('it_enable_except')  ==> excerpt from order content instead of excerpt field
							$maxlength= as_opt('it_except_len');
							$result=as_db_query_sub('SELECT postid, content, format FROM ^posts WHERE postid IN (#)', $postids);
							$postinfo=as_db_read_all_assoc($result, 'postid');
							foreach ($q_list['qs'] as $index => $order) {
								$thispost = @$postinfo[$order['raw']['postid']];
								if (isset($thispost)) {
									$text=as_viewer_text($thispost['content'], $thispost['format'], array('blockwordspreg' => $blockwordspreg));
									$text=as_shorten_string_line($text, $maxlength);
									$q_list['qs'][$index]['excerpt']=as_html($text);
								}
							}
						}
					}
				//	Retrieve featured images for all list orders
					if(as_opt('it_feature_img_enable')){
						$result=as_db_query_sub('SELECT postid,content FROM ^postmetas WHERE postid IN (#) AND title=$', $postids,'et_featured_image');
						$featured_images=as_db_read_all_assoc($result, 'postid');
					}
				//	Now meta information for each order
					foreach ($q_list['qs'] as $index => $order) {

						if(as_opt('it_feature_img_enable')){
							$featured_image = @$featured_images[$order['raw']['postid']]['content'];
							if (isset($featured_image)) {
								$q_list['qs'][$index]['featured']= as_opt('it_featured_url_abs') .'featured/'. $featured_image;
							}
						}

						if (isset($comments[ $order['raw']['postid'] ])) 
							$q_list['qs'][$index]['comments']= $comments[ $order['raw']['postid'] ];
						else
							$q_list['qs'][$index]['comments']= 0;

						$q_list['qs'][$index]['favourited']=0;
						if (isset($favs[ $order['raw']['postid'] ])){
							$q_list['qs'][$index]['favourites']= $favs[ $order['raw']['postid'] ];
							if(isset($faved_post[ $order['raw']['postid'] ]))
								$q_list['qs'][$index]['favourited']=1;
						}else
							$q_list['qs'][$index]['favourites']= 0;
					}
				}
			}

			if (isset($q_list['qs'])) {
				$this->output('<div class="as-q-list row'.($this->list_vote_disabled($q_list['qs']) ? ' as-q-list-vote-disabled' : '').'">', '');
				$this->q_list_items($q_list['qs']);
				$this->output('</div> <!-- END as-q-list -->', '');
			}
		}
	/*
	* q_list_items
	* add a class to all order list items
	*
	* @since 1.0.0
	* @compatible no
	*/	
		function q_list_items($q_items)
		{
			if(as_opt('it_layout_lists') == 'qlist'){
				as_html_theme_base::q_list_items($q_items);
				return;
			}
			foreach ($q_items as $key => $q_item)
				$q_items[$key]['classes'] .= ' col col-md-4';
			as_html_theme_base::q_list_items($q_items);
		}
	/*
	* q_item_title
	* show featured image in lists
	*
	* @since 1.0.0
	* @compatible no
	*/	
		function q_item_title($q_item)
		{
			$this->output('<div class="as-q-item-title">');
			$this->output('<a href="'.$q_item['url'].'">');
			if (isset($q_item['featured'])){
				$fileName = $q_item['featured'];
				$exts = substr(strrchr($fileName,'.'),1);
				$withoutExt = preg_replace("/\\.[^.\\s]{3,4}$/", "", $fileName);
				$thumbnailName = $withoutExt.'_.'.$exts;
				$this->output('<img class="featured-image-item" src="' . $thumbnailName . '"/>');
			}
			$this->output('<h2>' . $q_item['title'] . '</h2>');
			if(@$q_item['excerpt'])
				$this->output('<SPAN class="as-excerpt">' . $q_item['excerpt'] . '</SPAN>');
			$this->output('</a>');
			$this->output('</div>');
		}
	/*
	* a_selection
	* customize "Select best answer" button
	*
	* @since 1.1.0
	* @compatible no
	*/
		public function a_selection($post)
		{
			$this->output('<div class="as-a-selection">');

			if (isset($post['select_places']))
				$this->output('<button '.$post['select_places'].' type="submit" class="btn btn-default as-a-select"/>' . as_lang_html('order/select_text') . '</button>');
			elseif (isset($post['unselect_places']))
				$this->output('<button '.$post['unselect_places'].' type="submit" class="btn btn-success as-a-unselect"/>' . @$post['select_text'] . '</button>');
			elseif ($post['selected'])
				$this->output('<div class="as-a-selected">&nbsp;</div>');

			//if (isset($post['select_text']))
			//	$this->output('<div class="as-a-selected-text">'.@$post['select_text'].'</div>');

			$this->output('</div>');
		}
	
	/*
	* q_view_buttons
	* only show form buttons to logged in user
	*
	* @since 1.0.0
	* @compatible no
	*/		
		function q_view_buttons($q_view)
		{
			// show buttons if user is logged in
			$userid = as_get_logged_in_userid();
			if ( isset($userid) )
				as_html_theme_base::q_view_buttons($q_view);

		}

	
	/*
	* title
	* add link to order title
	*
	* @since 1.0.0
	* @compatible no
	*/		
		function title()
		{
			if (isset($this->content['q_view'])){
				$postid = $this->content['q_view']['raw']['postid'];
				$qlink = as_q_path($postid, $this->content['q_view']['raw']['title']);
				$this->output('<a class="q-entry-title" href="' . $qlink . '">');
				as_html_theme_base::title();
				$this->output('</a>');
			}else
				as_html_theme_base::title();
		}

	/*
	* favorite
	* remove favorite from order item
	*
	* @since 1.0.0
	* @compatible yes
	* @relative a_count()
	* replace with favorite_main()
	*/
		function favorite()
		{

		}
		function favorite_main($post)
		{
			$favorite=@$this->content['favorite'];
			if (isset($favorite)){
				$this->output('<form '.$favorite['form_places'].'>');
				as_html_theme_base::favorite();
				$this->form_hidden_elements(@$favorite['form_hidden']);
				$this->output('</form>');
			}
		}
	/*
	* q_item_stats
	* remove Stats: votes , answer count, ...
	*
	* @since 1.0.0
	* @compatible no
	*/		
		function q_item_stats($q_item)
		{
		}
	/*
	* post_places
	* hide places in lists
	* add container opener before places and close after buttons
	*
	* @since 1.0.0
	* @compatible yes
	* @related: post_avatar_meta
	*/
		function post_places($post, $class)
		{ 
			//if (!( ($this->template=='qa') or ($this->template=='orders') ))
			// if it's not in a order list
			if ($class != 'as-q-item'){
				$this->voting_inner_html($post);
				as_html_theme_base::post_places($post, $class);
			}
		}
	/*
	* post_avatar_meta
	* hide all user meta excerpt user avatar in order lists
	*
	* @since 1.0.0
	* @compatible no
	* @related: post_places
	*/
		function post_avatar_meta($post, $class, $avatarprefix=null, $metaprefix=null, $metaseparator='<br/>')
		{
			if(as_opt('it_layout_lists') == 'qlist'){
				as_html_theme_base::post_avatar_meta($post, $class, $avatarprefix, $metaprefix, $metaseparator);
				return;
			}
			// check if it's a order list or order item	
			if ($class != 'as-q-item')//if (!( ($this->template=='qa') or ($this->template=='orders') ))
				as_html_theme_base::post_avatar_meta($post, $class, $avatarprefix, $metaprefix, $metaseparator);
			else
				$this->post_avatar($post, $class, $avatarprefix);
		}
	/*
	* post_avatar
	* hover effect for avatars
	*
	* @since 1.0.0
	* @compatible no
	*/
		function post_avatar($post, $class, $prefix=null)
		{
			if(as_opt('it_layout_lists') == 'qlist'){
				as_html_theme_base::post_avatar($post, $class, $prefix=null);
				return;
			}
			// check if it's a order list or order item
			if ($class != 'as-q-item')//if (!( ($this->template=='qa') or ($this->template=='orders') ))
				as_html_theme_base::post_avatar($post, $class, $prefix);
			else{
				$qlink = as_q_path($post['raw']['postid'], $post['raw']['title'],true);
				$this->output('<div class="q-item-meta">');
				// set avatar
				if (isset($post['avatar'])) {
					if (isset($prefix))
						$this->output($prefix);

					$this->output('<section class="'.$class.'-avatar">' . $post['avatar']);
						$this->output('<section class="popup-user-avatar">');
						as_html_theme_base::post_meta_what($post, $class);
						as_html_theme_base::post_meta_who($post, $class);
						$this->output('</section>');
					$this->output('</section>');
				}
				// set category
				if ($post["raw"]["categoryid"]){
					require_once AS_INCLUDE_DIR.'as-db-metas.php';
					$categoryid = $post["raw"]["categoryid"];
					$catname = $post["raw"]["categoryname"];
					$catbackpath = $post["raw"]["categorybackpath"];
					$et_category = json_decode( as_db_categorymeta_get($categoryid, 'et_category'), true );
					$this->output('<section class="'.$class.'-category">');
						$categorypathprefix = 'orders/';
						$this->output('<a class="'.$class.'-category-link" title="' . $et_category['et_cat_title'] . '" href="'.as_path_html($categorypathprefix.implode('/', array_reverse(explode('/', $catbackpath)))).'">');
							if (!(empty($et_category['et_cat_icon48'])))
								$this->output('<img class="as-category-image" width="48" height="48" alt="' . $et_category['et_cat_desc'] . '" src="' . $et_category['et_cat_icon48'] . '">');
							else
								$this->output(as_html($catname));
						$this->output('</a>');
						if (!(empty($et_category['et_cat_desc']))){
							$this->output('<section class="'.$class.'-category-description">');
							$this->output($et_category['et_cat_desc']);
							$this->output('</section>');
						}
					$this->output('</section>');
				}
				$this->output('</div>');
				$this->output('<div class="as-item-meta-bar">');
					// Voting
					$this->voting_inner_html($post);
					// favourites
					if(as_is_logged_in()){
						$favourited = $post['favourited'];
						$favorite=as_favorite_form(AS_ENTITY_QUESTION, $post['raw']['postid'], $favourited, 
							as_lang($favourited ? 'order/remove_q_favorites' : 'order/add_q_favorites'));
						if (isset($favorite)){
							//$this->output('<form '.$favorite['form_places'].'>');
							$this->output('<div class="as-favoriting as-favoriting-' . $post['raw']['postid'] . '" '.@$favorite['favorite_places'].'>');
							$this->favorite_inner_html($favorite,$post['favourites']);
							$this->output('</div>');
							$this->output('<input type="hidden" id="fav_code_'. $post['raw']['postid'] . '" name="fav_code" value="'.@$favorite['form_hidden']['code'].'"/>');
							//$this->output('</form>');
						}
					}else{
						$this->output('<div class="as-favoriting as-favoriting-' . $post['raw']['postid'] . '" '.@$favorite['favorite_places'].'>');
						$this->output('<button class="btn btn-default btn-xs fa fa-heart as-favorite" type="button" onclick="return as_favorite_click(this);" name="favorite-login_q' . $post['raw']['postid'] . '" title="Favourite">' . $post['favourites'] . '</button>');
						//<button class="btn btn-default btn-xs fa fa-heart as-favorite" type="button" onclick="return as_favorite_click(this);" name="favorite_Q_125_1" title="Add to my favorites">2</button>
						$this->output('</div>');
					}
					// discussions
					$this->output('<div class="as-list-discussions">');
						$this->output('<a class="btn btn-default btn-xs fa fa-comment discussions-item-list" href="'. $qlink .'">' . ($post['comments']+$post["answers_raw"]) . '</a>');
					$this->output('</div>');
					// Share
					$this->output('<div class="as-list-share">');
						$this->output('<button type="button" class="btn btn-default btn-xs fa fa-share-alt share-item-list" data-share-link="'. $qlink .'" data-share-title="'.$post['raw']['title'].'"></button>');
					$this->output('</div>');
				$this->output('</div>');
			}
			//as_html_theme_base::voting_inner_html($post);
		}
	/*
	* favorite_inner_html
	* bootstrap & order list compatible favourite button
	*
	* @since 1.0.0
	* @compatible no
	*/

		function favorite_inner_html($favorite,$favorites=null)
		{
			$places = '';
			if(isset($favorite['favorite_add_places'])){
				$places = $favorite['favorite_add_places'];
				$class= 'as-favorite';
			}elseif(isset($favorite['favorite_remove_places'])){
				$places = $favorite['favorite_remove_places'];
				$class= 'as-unfavorite';
			}
			$this->favorite_button($places, $class,$favorites);
		}
	/*
	* favorite_button
	* bootstrap & order list compatible favourite buttons
	*
	* @since 1.0.0
	* @compatible no
	*/
	
		function favorite_button($places, $class,$favorites=null)
		{
			if (isset($places))
				$this->output('<button ' . $places . ' class="btn btn-default btn-xs fa fa-heart ' . $class . '" type="button">' . $favorites . '</button>');
		}
	/*
	* voting_inner_html
	* voting for order lists
	*
	* @since 1.0.0
	* @compatible no
	*/
		function voting_inner_html($post){
			// Voting
			if(isset($post['vote_view'])){ // don't show on order edit form
				if( ( ($this->template=='order') or ($this->template=='voting') ) ) {
					if( isset($post['main_form_places']) )
						$this->output('<form '.$post['main_form_places'].'>'); // form for voting buttons
					$this->output('<div class="as-voting-item '.(($post['vote_view']=='updown') ? 'as-voting-updown' : 'as-voting-net').'" '.@$post['vote_places'].'>');
						$this->output('<div class="as-item-vote-buttons">');
							$this->list_vote_buttons($post);
						$this->output('</div>');
					$this->output('</div>');
					$this->form_hidden_elements(@$post['voting_form_hidden']);
					if( isset($post['main_form_places']) )
						$this->output('</form>');	
				}else{
					$this->output('<div class="as-voting-item '.(($post['vote_view']=='updown') ? 'as-voting-updown' : 'as-voting-net').'" '.@$post['vote_places'].'>');
						$this->output('<div class="as-item-vote-buttons">');
							$this->list_vote_buttons($post);
						$this->output('</div>');
					$this->output('</div>');
				}
			}
}

		
		function list_vote_buttons($post)
		{
			$onclick='onclick="return as_vote_click(this);"';
			$anchor=urlencode(as_anchor($post['raw']['type'], $post['raw']['postid']));
			
			//v($post['vote_up_places']);
			//v($post['vote_down_places']);
			if ($post['vote_up_places']==' ')
				$post['vote_up_places']='title="'.as_lang_html('main/voted_up_popup').'" name="'.as_html('vote_'.$post['raw']['postid'].'_0_'.$anchor).'" '.$onclick;
			if ($post['vote_down_places']==' ')
				$post['vote_down_places']='title="'.as_lang_html('main/voted_down_popup').'" name="'.as_html('vote_'.$post['raw']['postid'].'_0_'.$anchor).'" '.$onclick;
			
			switch (@$post['vote_state'])
			{
				case 'voted_up':
					$this->post_hover_button($post, 'vote_up_places', '&#xf087;', 'fa btn btn-success as-item-vote-one-button as-voted-up');
					$this->output('<div class="as-list-vote-count">' . $post['netvotes_view']['data'] . '</div>');
					$this->post_hover_button($post, 'vote_down_places', '&#xf088;', 'fa btn btn-danger as-item-vote-one-button as-vote-down');
					break;
					
				case 'voted_up_disabled':
					$this->post_disabled_button($post, 'vote_up_places', '&#xf087;', 'fa btn btn-success as-item-vote-one-button as-vote-up');
					$this->output('<div class="as-list-vote-count">' . $post['netvotes_view']['data'] . '</div>');
					$this->post_hover_button($post, 'vote_down_places', '&#xf088;', 'fa btn btn-danger as-item-vote-one-button as-voted-down');
					break;
					
				case 'voted_down':
					$this->post_hover_button($post, 'vote_up_places', '&#xf087;', 'fa btn btn-success as-item-vote-one-button as-vote-up');
					$this->output('<div class="as-list-vote-count">' . $post['netvotes_view']['data'] . '</div>');
					$this->post_hover_button($post, 'vote_down_places', '&#xf088;', 'fa btn btn-danger as-item-vote-one-button as-voted-down');
					break;
					
				case 'voted_down_disabled':
					$this->post_hover_button($post, 'vote_up_places', '&#xf087;', 'fa btn btn-success as-item-vote-one-button as-voted-up');
					$this->output('<div class="as-list-vote-count">' . $post['netvotes_view']['data'] . '</div>');
					$this->post_disabled_button($post, 'vote_down_places', '&#xf088;', 'fa btn btn-danger as-item-vote-one-button as-vote-down');
					break;
					
				case 'up_only':
					$this->post_hover_button($post, 'vote_up_places', '&#xf087;', 'fa btn btn-success as-item-vote-first-button as-vote-up');
					$this->output('<div class="as-list-vote-count">' . $post['netvotes_view']['data'] . '</div>');
					$this->post_disabled_button($post, 'vote_down_places', '&#xf088;', 'fa btn btn-danger as-item-vote-second-button as-vote-down');
					break;
				
				case 'enabled':
					$this->post_hover_button($post, 'vote_up_places', '&#xf087;', 'fa btn btn-success as-item-vote-first-button as-vote-up');
					$this->output('<div class="as-list-vote-count">' . $post['netvotes_view']['data'] . '</div>');
					$this->post_hover_button($post, 'vote_down_places', '&#xf088;', 'fa btn btn-danger as-item-vote-second-button as-vote-down');
					break;

				default:
					$this->post_disabled_button($post, 'vote_up_places', '&#xf087;', 'fa btn btn-success as-item-vote-first-button as-vote-up');
					$this->output('<div class="as-list-vote-count">' . $post['netvotes_view']['data'] . '</div>');
					$this->post_disabled_button($post, 'vote_down_places', '&#xf088;', 'fa btn btn-danger as-item-vote-second-button as-vote-down');
					break;
			}
		}

	/*
	* page_links_list
	* Related: suggest_next()
	* don't load infinite Scroll with page numbers
	*
	* @since 1.1.0
	* @compatible yes
	*/
		function page_links_list($page_items)
		{
			if(!( ($this->template=='qa' && as_opt('it_infinite_scroll_home_enable')) || ($this->template=='orders' && as_opt('it_infinite_scroll_as_enable')) ))
				as_html_theme_base::page_links_list($page_items);
		}
	/*
	* suggest_next
	* Ajax infinite page load
	*
	* @since 1.0.0
	* @compatible yes
	*/
		function suggest_next()
		{
			if( ($this->template=='qa' && as_opt('it_infinite_scroll_home_enable')) || ($this->template=='orders' && as_opt('it_infinite_scroll_as_enable')) ){
				$this->output('<div id="infinite-ajax-suggest" class="as-suggest-next infinite-ajax-suggest">');
				$this->output('<a href="#" id="infinite-ajax-load-more"  class="infinite-ajax-load-more">Load More</a>');
				$this->output('</div>');
			}else
				as_html_theme_base::suggest_next();
		}
	/*
	* footer
	* HTML5 Footer
	*
	* @since 1.0.0
	* @compatible yes
	*/
		function footer()
		{
			$this->output('<footer class="as-footer-container">');
			as_html_theme_base::footer();
			$this->output('</footer>', '');
			$this->output('<div id="ajax-holder" style="display:none;visibility:hidden;"></div>', '');
			$this->output('
			<span id="top-link-block" class="hidden">
				<a href="#top" class="well well-sm"  onclick="$(\'html,body\').animate({scrollTop:0},\'slow\');return false;">
					<i class="fa fa-chevron-up"></i>
				</a>
			</span>
			', '');
		}
	/*
	* footer
	* HTML5 Footer
	*
	* @since 1.1.0
	* @compatible yes
	*/
		public function attribution()
		{
			// Hi there. I'd really appreciate you displaying this link on your Q2A site. Thank you - Gideon
			$this->output('<div class="as-attribution"></div>');
			as_html_theme_base::attribution();
		}
}
	
/*
	Omit PHP closing place to help avoid accidental output
*/