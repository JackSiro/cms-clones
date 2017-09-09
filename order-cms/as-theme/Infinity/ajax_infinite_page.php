<?php

//	Output this header as early as possible
	header('Content-Type: text/plain; charset=utf-8');


//	Ensure no PHP errors are shown in the Ajax response
	//@ini_set('display_errors', 0);


//	Load the Q2A base file which sets up a bunch of crucial functions
	require_once '../../as-include/as-base.php';
	as_report_process_stage('init_ajax');		

//	Get general Ajax parameters from the POST payload, and clear $_GET
	as_set_request(as_post_text('as_request'), as_post_text('as_root'));
	
	require_once AS_INCLUDE_DIR.'as-db-selects.php';
	require_once AS_INCLUDE_DIR.'as-app-format.php';
	require_once AS_INCLUDE_DIR.'as-app-users.php';
	require_once AS_INCLUDE_DIR.'as-app-options.php';
	require_once AS_INCLUDE_DIR.'as-app-q-list.php';
	//require_once AS_INCLUDE_DIR.'as-page.php';
	//as_set_template('sa');
	$pagesize=as_opt('page_size_home');
	$page_number = (int)$_POST['page'];
	$limit = (int)$pagesize * $page_number;
	$userid=as_get_logged_in_userid();
	
	list($questions1, $questions2)=as_db_select_with_pending(
		as_db_qs_selectspec($userid, 'created', 0, null, null, false, false, $limit),
		as_db_recent_a_qs_selectspec($userid, 0, null, null, false, false, $limit)
	);
	$questions=as_any_sort_and_dedupe(array_merge($questions1, $questions2));
	
	array_splice($questions, 0,(int)$pagesize * ($page_number-1) );
	
	$as_content=it_q_list_page_content(
		$questions, // questions
		$pagesize, // questions per page
		0, // start offset
		null, // total count (null to hide page links)
		'', // title if some questions
		'', // title if no questions
		null, // categories for navigation
		null, // selected category id
		false, // show question counts in category navigation
		'', // prefix for links in category navigation
		null, // prefix for RSS feed paths (null to hide)
		'', // next 3 lines to check end of question list
		//(count($questions)<$pagesize) // suggest what to do next
		//	? as_html_suggest_ask($categoryid)
		//	: as_html_suggest_qs_tags(as_using_tags(), as_category_path_request($categories, $categoryid)),
		null, // page link params
		null // category nav params
	);

	//echo "AS_AJAX_RESPONSE\n1\n";
	
	
	$themeclass=as_load_theme_class(as_get_site_theme(), 'qa', null, null);
	$themeclass->q_list($as_content["q_list"]);
	die();

function it_q_list_page_content($questions, $pagesize, $start, $count, $sometitle, $nonetitle,
		$navcategories, $categoryid, $categoryqcount, $categorypathprefix, $feedpathprefix, $suggest,
		$pagelinkparams=null, $categoryparams=null, $dummy=null)
	{
		
		require_once AS_INCLUDE_DIR.'as-app-format.php';
		require_once AS_INCLUDE_DIR.'as-app-updates.php';
	
		$userid=as_get_logged_in_userid();
		
		
	//	Chop down to size, get user information for display

		if (isset($pagesize))
			$questions=array_slice($questions, 0, $pagesize);
	
		$usershtml=as_userids_handles_html(as_any_get_userids_handles($questions));


		$as_content['q_list']['form']=array(
			'tags' => 'method="post" action="'.as_self_html().'"',
			
			'hidden' => array(
				'code' => as_get_form_security_code('vote'),
			),
		);
		
		$as_content['q_list']['qs']=array();
		
		if (count($questions)) {
			$as_content['title']=$sometitle;
		
			$defaults=as_post_html_defaults('Q');
				
			foreach ($questions as $question)
				$as_content['q_list']['qs'][]=as_any_to_q_html_fields($question, $userid, it_cookie_get(),
					$usershtml, null, as_post_html_options($question, $defaults));

		} else
			$as_content['title']=$nonetitle;
		
			
		if (isset($count) && isset($pagesize))
			$as_content['page_links']=as_html_page_links(as_request(), $start, $pagesize, $count, as_opt('pages_prev_next'), $pagelinkparams);
		
			
		return $as_content;
	}
	function it_cookie_get()
	{
		return isset($_COOKIE['as_id']) ? as_gpc_to_string($_COOKIE['as_id']) : null;
	}
/*
	Omit PHP closing tag to help avoid accidental output
*/