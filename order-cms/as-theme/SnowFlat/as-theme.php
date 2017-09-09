<?php
/*
	Snow Theme for AppSmata Package
	Copyright (C) 2014 APS Market <http://www.q2amarket.com>

	File:           as-theme.php
	Version:        Snow 1.4
	Description:    APS theme class

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.
*/

class as_html_theme extends as_html_theme_base
{
	// use local font files instead of Google Fonts
	private $localfonts = true;

	// theme subdirectories
	private $js_dir = 'js';
	private $icon_url = 'images/icons';

	private $fixed_topbar = false;
	private $welcome_widget_class = 'wet-asphalt';
	private $purchase_search_box_class = 'turquoise';
	// Size of the user avatar in the navigation bar
	private $nav_bar_avatar_size = 52;

	public function head_metas()
	{
		$this->output('<meta name="viewport" content="width=device-width, initial-scale=1"/>');
		parent::head_metas();
	}

	public function head_ass()
	{
		// add RTL CSS file
		//if ($this->isRTL)
		//	$this->content['css_src'][] = $this->rooturl . 'as-styles-rtl.css?' . AS_VERSION;

		// add Ubuntu font CSS file from Google Fonts
		if ($this->localfonts)
			$this->content['css_src'][] = $this->rooturl . 'fonts/ubuntu.css?' . AS_VERSION;
		else
			$this->content['css_src'][] = '//fonts.googleapis.com/css?family=Ubuntu:400,700,400italic,700italic';

		parent::head_ass();

		// output some dynamic CSS inline
		$this->head_inline_ass();
	}

	public function head_script()
	{
		$jsUrl = $this->rooturl . $this->js_dir . '/snow-core.js?' . AS_VERSION;
		$this->content['script'][] = '<script src="' . $jsUrl . '"></script>';

		parent::head_script();
	}

	public function logged_in()
	{
		parent::logged_in();
		if (as_is_logged_in()) {
			$userpoints = as_get_logged_in_points();
			$pointshtml = $userpoints == 1
				? as_lang_html_sub('main/1_point', '1', '1')
				: as_html(number_format($userpoints))
			;
			//$this->output('<div class="asm-logged-in-points">' . $pointshtml . '</div>');
		}
	}

	public function body_places()
	{
		$class = 'as-template-' . as_html($this->template);

		if (isset($this->content['categoryids'])) {
			foreach ($this->content['categoryids'] as $categoryid)
				$class .= ' as-category-' . as_html($categoryid);
		}

		if ($this->template === 'admin' && as_request_part(1) === 'approve')
			$class .= ' asm-approve-users';

		if ($this->fixed_topbar)
			$class .= ' asm-body-fixed';

		$this->output('class="' . $class . ' as-body-js-off"');
	}

	public function nav_user_search()
	{
		// outputs login form if user not logged in
		$this->output('<div class="asm-account-items-wrapper">');

		$this->asm_user_account();

		$this->output('<div class="asm-account-items clearfix">');

		if (!as_is_logged_in()) {
			if (isset($this->content['navigation']['user']['login']) && !AS_FINAL_EXTERNAL_USERS) {
				$login = $this->content['navigation']['user']['login'];
				$this->output(
					'<form action="' . $login['url'] . '" method="post">',
						'<input type="text" name="emailhandle" dir="auto" placeholder="' . trim(as_lang_html(as_opt('allow_login_email_only') ? 'users/email_label' : 'users/email_handle_label'), ':') . '"/>',
						'<input type="password" name="password" dir="auto" placeholder="' . trim(as_lang_html('users/password_label'), ':') . '"/>',
						'<div><input type="checkbox" name="remember" id="asm-rememberme" value="1"/>',
						'<label for="asm-rememberme">' . as_lang_html('users/remember') . '</label></div>',
						'<input type="hidden" name="code" value="' . as_html(as_get_form_security_code('login')) . '"/>',
						'<input type="submit" value="' . $login['label'] . '" class="as-form-tall-button as-form-tall-button-login" name="dologin"/>',
					'</form>'
				);

				// remove regular navigation link to log in page
				unset($this->content['navigation']['user']['login']);
			}
		}

		$this->nav('user');
		$this->output('</div> <!-- END asm-account-items -->');
		$this->output('</div> <!-- END asm-account-items-wrapper -->');
	}

	/**
	 * Modify markup for topbar.
	 *
	 * @since Snow 1.4
	 */
	public function nav_main_sub()
	{
		$this->output('<div class="asm-main-nav-wrapper clearfix">');
		$this->output('<div class="sb-toggle-left asm-menu-toggle"><i class="icon-th-list"></i></div>');
		$this->nav_user_search();
		$this->logo();
		$this->nav('main');
		$this->output('</div> <!-- END asm-main-nav-wrapper -->');
		if ($this->template == 'admin') return;
		$this->nav('sub');
	}

	public function nav_link($navlink, $class)
	{
		if (isset($navlink['note']) && !empty($navlink['note'])) {
			$search = array(' - <', '> - ');
			$replace = array(' <', '> ');
			$navlink['note'] = str_replace($search, $replace, $navlink['note']);
		}
		parent::nav_link($navlink, $class);
	}

	public function body_content()
	{
		$sub_navigation = @$this->content['navigation']['sub'];
		//$sub_navigation = @$this->nav('sub');
		$this->body_prefix();
		$this->notices();

		$this->widgets('full', 'top');
		$this->header();

		$this->output('<div class="as-body-wrapper">', '');
		$this->widgets('full', 'high');

		$this->output('<div class="as-main-wrapper">', '');
		$this->adminpanel($sub_navigation);
		$this->main();
		$this->sidepanel();
		$this->output('</div> <!-- END main-wrapper -->');

		$this->widgets('full', 'low');
		$this->output('</div> <!-- END body-wrapper -->');

		$this->footer();

		$this->body_suffix();
	}

	public function header()
	{
		$class = $this->fixed_topbar ? ' fixed' : '';

		$this->output('<div id="asm-topbar" class="clearfix' . $class . '">');

		$this->nav_main_sub();
		$this->output('</div> <!-- END asm-topbar -->');

		$this->output($this->purchase_button());
		$this->asm_search('the-top', 'the-top-search');
	}

	public function footer()
	{
		$this->output('<div class="asm-footer-box">');

		$this->output('<div class="asm-footer-row">');
		$this->widgets('full', 'bottom');
		$this->output('</div> <!-- END asm-footer-row -->');

		parent::footer();
		$this->output('</div> <!-- END asm-footer-box -->');
	}

	public function sidepanel()
	{
		if (($this->template == 'user') || ($this->template == 'admin')) return;

		$this->output('<div id="asm-sidepanel-toggle"><i class="icon-left-open-big"></i></div>');
		$this->output('<div class="as-sidepanel" id="asm-sidepanel-mobile">');
		$this->asm_search();
		$this->widgets('side', 'top');
		$this->sidebar();
		$this->widgets('side', 'high');
		$this->nav('cat', 1);
		$this->widgets('side', 'low');
		if (isset($this->content['sidepanel']))
			$this->output_raw($this->content['sidepanel']);
		$this->feed();
		$this->widgets('side', 'bottom');
		$this->output('</div>', '');
	}

	public function sidebar()
	{
		if (isset($this->content['sidebar'])) {
			$sidebar = $this->content['sidebar'];
			if (!empty($sidebar)) {
				$this->output('<div class="as-sidebar ' . $this->welcome_widget_class . '">');
				$this->output_raw($sidebar);
				$this->output('</div>', '');
			}
		}
	}

	public function adminpanel($sub_navigation)
	{
		if ($this->template == 'admin') {
			$this->output('<div id="asm-adminpanel-toggle"><i class="icon-left-open-big"></i></div>');
			$this->output('<div class="as-adminpanel" id="asm-adminpanel-mobile">');
			//$this->output('<h1>Dashboard</h1>');	
			
			$this->output( '<div class="as-left-side-bar" id="sidebar" role="navigation">', '' );
			if ( count( $sub_navigation ) ) {

				$this->output( '<div class="navlist-group">', '' );

				foreach ( $sub_navigation as $key => $sub_navigation_item ) {
					$this->as_nav_side_bar_item( $sub_navigation_item );
				}
				$this->output( '</div>', '' );
			}
			$this->output( '</div>', '<!-- END of left-menu-->' );
			$this->output('</div>', '');
		}
	}

	public function as_nav_side_bar_item( $nav_item )
	{
		$class = ( !!@$nav_item['selected'] ) ? ' active' : '';
		$icon = ( !!@$nav_item['icon'] ) ? as_get_fa_icon( @$nav_item['icon'] ) : '';
		$this->output( '<a href="' . $nav_item['url'] . '" class="navlist-group-item ' . $class . '">' . $icon . $nav_item['label'] . '</a>' );
	}
	
    function as_get_fa_icon( $icon )
    {
        if ( !empty( $icon ) ) {
            return '<span class="fa fa-' . $icon . '"></span> ';
        } else {
            return '';
        }
    }

	public function left_side_bar( $sub_navigation )
	{

		$this->output( '<div class="as-left-side-bar" id="sidebar" role="navigation">', '' );
		if ( count( $sub_navigation ) ) {

			$this->output( '<div class="navlist-group">', '' );

			foreach ( $sub_navigation as $key => $sub_navigation_item ) {
				$this->as_nav_side_bar_item( $sub_navigation_item );
			}
			$this->output( '</div>', '' );
			if ( $this->template === 'admin' ) {
				unset( $this->content['navigation']['sub'] );
			}
		}
		$this->output( '</div>', '<!-- END of left-side-bar -->' );
	}

	public function q_item_title($q_item)
	{
		$closedText = as_lang('main/closed');
		$imgHtml = empty($q_item['closed'])
			? ''
			: '<img src="' . $this->rooturl . $this->icon_url . '/closed-q-list.png" class="asm-q-list-close-icon" alt="' . $closedText . '" title="' . $closedText . '"/>';

		$this->output(
			'<div class="as-q-item-title">',
			// add closed note in title
			$imgHtml,
			'<a href="' . $q_item['url'] . '">' . $q_item['title'] . '</a>',
			'</div>'
		);
	}

	public function title()
	{
		$q_view = isset($this->content['q_view']) ? $this->content['q_view'] : null;

		// RSS feed link in title
		if (isset($this->content['feed']['url'])) {
			$feed = $this->content['feed'];
			$label = isset($feed['label']) ? $feed['label'] : '';
			$this->output('<a href="' . $feed['url'] . '" title="' . $label . '"><i class="icon-rss asm-title-rss"></i></a>');
		}

		// link title where appropriate
		$url = isset($q_view['url']) ? $q_view['url'] : false;

		// add closed image
		$closedText = as_lang('main/closed');
		$imgHtml = empty($q_view['closed'])
			? ''
			: '<img src="' . $this->rooturl . $this->icon_url . '/closed-q-view.png" class="asm-q-view-close-icon" alt="' . $closedText . '" width="24" height="24" title="' . $closedText . '"/>';

		if (isset($this->content['title'])) {
			$this->output(
				$imgHtml,
				$url ? '<a href="' . $url . '">' : '',
				$this->content['title'],
				$url ? '</a>' : ''
			);
		}
	}

	public function q_item_stats($q_item)
	{
		$this->output('<div class="as-q-item-stats">');

		$this->voting($q_item);
		$this->a_count($q_item);
		parent::view_count($q_item);

		$this->output('</div>');
	}

	public function view_count($q_item) {}

	public function q_view_stats($q_view)
	{
		$this->output('<div class="as-q-view-stats">');

		$this->voting($q_view);
		$this->a_count($q_view);
		parent::view_count($q_view);

		$this->output('</div>');
	}

	public function q_view_main($q_view)
	{
		$this->output('<div class="as-q-view-main">');

		if (isset($q_view['main_form_places']))
			$this->output('<form ' . $q_view['main_form_places'] . '>'); // form for buttons on purchase

		$this->post_avatar_meta($q_view, 'as-q-view');
		$this->q_view_content($q_view);
		$this->q_view_extra($q_view);
		$this->q_view_follows($q_view);
		$this->q_view_closed($q_view);
		$this->post_places($q_view, 'as-q-view');

		$this->q_view_buttons($q_view);
		$this->c_list(isset($q_view['c_list']) ? $q_view['c_list'] : null, 'as-q-view');

		if (isset($q_view['main_form_places'])) {
			if (isset($q_view['buttons_form_hidden']))
				$this->form_hidden_elements($q_view['buttons_form_hidden']);
			$this->output('</form>');
		}

		$this->c_form(isset($q_view['c_form']) ? $q_view['c_form'] : null);

		$this->output('</div> <!-- END as-q-view-main -->');
	}

	public function a_item_main($a_item)
	{
		$this->output('<div class="as-a-item-main">');

		$this->post_avatar_meta($a_item, 'as-a-item');

		if (isset($a_item['main_form_places']))
			$this->output('<form ' . $a_item['main_form_places'] . '>'); // form for buttons on reply

		if ($a_item['hidden'])
			$replyState = 'hidden';
		elseif ($a_item['selected'])
			$replyState = 'selected';
		else
			$replyState = null;

		if (isset($replyState))
			$this->output('<div class="as-a-item-' . $replyState . '">');

		$this->a_selection($a_item);
		if (isset($a_item['error']))
			$this->error($a_item['error']);
		$this->a_item_content($a_item);

		if (isset($replyState))
			$this->output('</div>');

		$this->a_item_buttons($a_item);

		if (isset($a_item['c_list']))
			$this->c_list($a_item['c_list'], 'as-a-item');

		if (isset($a_item['main_form_places'])) {
			if (isset($a_item['buttons_form_hidden']))
				$this->form_hidden_elements($a_item['buttons_form_hidden']);
			$this->output('</form>');
		}

		$this->c_form(isset($a_item['c_form']) ? $a_item['c_form'] : null);

		$this->output('</div> <!-- END as-a-item-main -->');
	}

	public function c_item_main($c_item)
	{
		$this->post_avatar_meta($c_item, 'as-c-item');

		if (isset($c_item['error']))
			$this->error($c_item['error']);

		if (isset($c_item['expand_places']))
			$this->c_item_expand($c_item);
		elseif (isset($c_item['url']))
			$this->c_item_link($c_item);
		else
			$this->c_item_content($c_item);

		$this->output('<div class="as-c-item-footer">');
		$this->c_item_buttons($c_item);
		$this->output('</div>');
	}

	public function attribution()
	{
		$this->output('<div class="as-attribution"></div>');
		parent::attribution();
	}

	private function asm_user_account()
	{
		if (as_is_logged_in()) {
			// get logged-in user avatar
			$handle = as_get_logged_in_user_field('handle');
			$toggleClass = 'asm-logged-in';

			if (AS_FINAL_EXTERNAL_USERS)
				$tobar_avatar = as_get_external_avatar_html(as_get_logged_in_user_field('userid'), $this->nav_bar_avatar_size, true);
			else {
				$tobar_avatar = as_get_user_avatar_html(
					as_get_logged_in_user_field('flags'),
					as_get_logged_in_user_field('email'),
					$handle,
					as_get_logged_in_user_field('avatarblobid'),
					as_get_logged_in_user_field('avatarwidth'),
					as_get_logged_in_user_field('avatarheight'),
					$this->nav_bar_avatar_size,
					false
				);
			}

			$avatar = strip_tags($tobar_avatar, '<img>');
			if (!empty($avatar))
				$handle = '';
		}
		else {
			// display login icon and label
			$handle = $this->content['navigation']['user']['login']['label'];
			$toggleClass = 'asm-logged-out';
			$avatar = '<i class="icon-key asm-auth-key"></i>';
		}

		// finally output avatar with div place
		$handleBlock = empty($handle) ? '' : '<div class="asm-account-handle">' . as_html($handle) . '</div>';
		$this->output(
			'<div id="asm-account-toggle" class="' . $toggleClass . '">',
			$avatar,
			$handleBlock,
			'</div>'
		);
	}

	private function asm_search($addon_class = null, $ids = null)
	{
		$id = isset($ids) ? ' id="' . $ids . '"' : '';

		$this->output('<div class="asm-search ' . $this->purchase_search_box_class . ' ' . $addon_class . '"' . $id . '>');
		$this->search();
		$this->output('</div>');
	}

	private function head_inline_ass()
	{
		$css = array('<style>');

		if (!as_is_logged_in())
			$css[] = '.as-nav-user { margin: 0 !important; }';

		if (as_request_part(1) !== as_get_logged_in_handle()) {
			$css[] = '@media (max-width: 979px) {';
			$css[] = ' body.as-template-user.fixed, body[class*="as-template-user-"].fixed { padding-top: 118px !important; }';
			$css[] = ' body.as-template-users.fixed { padding-top: 95px !important; }';
			$css[] = '}';
			$css[] = '@media (min-width: 980px) {';
			$css[] = ' body.as-template-users.fixed { padding-top: 105px !important;}';
			$css[] = '}';
		}

		$css[] = '</style>';

		$this->output_array($css);
	}

	private function purchase_button()
	{
		return
			'<div class="asm-purchase-search-box">' .
			'<div class="asm-purchase-mobile">' .
			'<a href="' . as_path('purchase', null, as_path_to_root()) . '" class="' . $this->purchase_search_box_class . '">' .
			as_lang_html('main/nav_purchase') .
			'</a>' .
			'</div>' .
			'<div class="asm-search-mobile ' . $this->purchase_search_box_class . '" id="asm-search-mobile">' .
			'</div>' .
			'</div>';
	}

}
