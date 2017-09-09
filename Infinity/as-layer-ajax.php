<?php
/* don't allow this page to be requested directly from browser */	
if (!defined('AS_VERSION')) {
		header('Location: /');
		exit;
}
	

// will clear all HTML out put
class as_html_theme_layer extends as_html_theme_base {
	function doctype(){		
		if(isset($_REQUEST['ajax_req'])){
			if ( $_REQUEST['action']=='activitylist' )
				$this->output(  $this->activitylist() );
			if ( $_REQUEST['action']=='messagelist' )
				$this->output(  $this->messagelist() );
			as_db_disconnect();
			return null;
		}
	}
	// empty page response from html code
	function html(){}
	function finish(){}
	
	// available functions
	
	function activitylist(){
		// get points for each activity
		require_once AS_INCLUDE_DIR.'as-db-points.php';
		require_once AS_INCLUDE_DIR.'as-db-users.php';
		$optionnames=as_db_points_option_names();
		$options=as_get_options($optionnames);
		$multi = (int)$options['points_multiple'];
		$upvote = '';
		$downvote = '';
		if(@$options['points_per_q_voted_up']) {
			$upvote = '_up';
			$downvote = '_down';
		}
		$event_point['in_q_vote_up'] = (int)$options['points_per_q_voted'.$upvote]*$multi;
		$event_point['in_q_vote_down'] = (int)$options['points_per_q_voted'.$downvote]*$multi*(-1);
		$event_point['in_q_unvote_up'] = (int)$options['points_per_q_voted'.$upvote]*$multi*(-1);
		$event_point['in_q_unvote_down'] = (int)$options['points_per_q_voted'.$downvote]*$multi;
		$event_point['in_a_vote_up'] = (int)$options['points_per_a_voted'.$upvote]*$multi;
		$event_point['in_a_vote_down'] = (int)$options['points_per_a_voted'.$downvote]*$multi*(-1);
		$event_point['in_a_unvote_up'] = (int)$options['points_per_a_voted'.$upvote]*$multi*(-1);
		$event_point['in_a_unvote_down'] = (int)$options['points_per_a_voted'.$downvote]*$multi;
		$event_point['in_a_select'] = (int)$options['points_a_selected']*$multi;
		$event_point['in_a_unselect'] = (int)$options['points_a_selected']*$multi*(-1);
		$event_point['q_post'] = (int)$options['points_post_q']*$multi;
		$event_point['a_post'] = (int)$options['points_post_a']*$multi;
		$event_point['a_select'] = (int)$options['points_select_a']*$multi;
		$event_point['q_vote_up'] = (int)$options['points_vote_up_q']*$multi;
		$event_point['q_vote_down'] = (int)$options['points_vote_down_q']*$multi;
		$event_point['a_vote_up'] = (int)$options['points_vote_up_a']*$multi;
		$event_point['a_vote_down'] = (int)$options['points_vote_down_a']*$multi;
		/*
		// Exclude Activities
		$exclude = array(
			'u_login',
			'u_logout',
			'u_password',
			'u_reset',
			'u_save',
			'u_edit',
			'u_block',
			'u_unblock',
			'feedback',
			'search',
			'badge_awarded',
		);
		$excludes = "'".implode("','",$exclude)."'";
		$eventslist = as_db_read_all_assoc(
			as_db_query_sub( 
				'SELECT UNIX_TIMESTAMP(datetime) AS datetime, userid, postid, effecteduserid, event, params FROM ^userlog WHERE userid=# AND 
				DATE_SUB(CURDATE(),INTERVAL # DAY) <= datetime
				AND event NOT IN (' . $excludes .') ORDER BY datetime DESC'.(as_opt('qat_activity_number')?' LIMIT '.(int)as_opt('qat_activity_number'):''),
				$userid, as_opt('qat_activity_age')
			)
		);
		*/
		// Get Events
		$userid = as_get_logged_in_userid();
		$eventslist = as_db_read_all_assoc(
			as_db_query_sub( 
				'SELECT UNIX_TIMESTAMP(datetime) AS datetime, userid, postid, effecteduserid, event, params FROM ^userlog WHERE effecteduserid=# AND 
				DATE_SUB(CURDATE(),INTERVAL # DAY) <= datetime
				ORDER BY datetime DESC'.(as_opt('qat_activity_number')?' LIMIT '.(int)as_opt('qat_activity_number'):''),
				$userid, as_opt('qat_activity_age')
			)
		);
		$event = array();
		$output='';
		$i=0;
		//
		$userids = array();
		foreach ($eventslist as $event){
			$userids[$event['userid']]=$event['userid'];
			$userids[$event['effecteduserid']]=$event['effecteduserid'];
		}
		$handles = as_db_user_get_userid_handles($userids);
		// get event's: time, type, parameters
		// get post id of questions
		foreach ($eventslist as $event){
			$title='';
			$link='';
			$vote_status = '';
			$handle = $handles[$event['userid']];
			$user_link = as_path('user/'.$handle);
			$datetime = $event['datetime'];
			$event['date'] = as_html(as_time_to_string(as_opt('db_time')-$datetime));
			$event['params'] = json_decode($event['params'],true);
			$output .='<li>';
			switch($event['event']){
				case 'related': // related question to an answer
					$url = as_path_html(as_q_request($event['postid'], $event['params']['title']), null, as_opt('site_url'),null,null);

					$output .='<div class="event-icon pull-left icon-chat"></div>
								<div class="event-content">
									<p class="title"><strong class="avatar"><a href="' . $user_link . '">' . $handle . '</a></strong>
									<span class="what">Asked a question related to your answer</span>
									</p>
									<a class="title" href="' . $url . '">'. $event['params']['title'] . '</a>
									<span class="date"> ' . $event['date'] . '</span>
								</div>';						
					break;
				case 'a_post': // user's question had been answered
					$anchor = as_anchor('A', $event['postid']);
					$url = as_path_html(as_q_request($event['params']['qid'], $event['params']['qtitle']), null, as_opt('site_url'),null,$anchor);
					$output .='<div class="event-icon pull-left icon-chat"></div>
								<div class="event-content">
									<p class="title"><strong class="avatar"><a href="' . $user_link . '">' . $handle . '</a></strong>
									<span class="what">Answered</span>
									</p>
									<a class="title" href="' . $url . '">'. $event['params']['qtitle'] . '</a>
									<span class="date"> ' . $event['date'] . '</span>
								</div>';
					break;
				case 'c_post': // user's question had been commented
					$anchor = as_anchor('C', $event['postid']);
					$url = as_path_html(as_q_request($event['params']['qid'], $event['params']['qtitle']), null, as_opt('site_url'),null,$anchor);
					$output .='<div class="event-icon pull-left icon-chat"></div>
								<div class="event-content">
									<p class="title"><strong class="avatar"><a href="' . $user_link . '">' . $handle . '</a></strong>
									<span class="what">Commented</span>
									</p>
									<a class="title" href="' . $url . '">'. $event['params']['qtitle'] . '</a>
									<span class="date"> ' . $event['date'] . '</span>
								</div>';
					break;
				case 'q_reshow': // user's question had been answered
					$url = as_path_html(as_q_request($event['params']['qid'], $event['params']['qtitle']), null, as_opt('site_url'),null,null);
					$output .='<div class="event-icon pull-left icon-chat"></div>
								<div class="event-content">
									<p class="title">
										<span class="what">your question had been accepted</span>
									</p>
									<a class="title" href="' . $url . '">'. $event['params']['qtitle'] . '</a>
									<span class="date"> ' . $event['date'] . '</span>
								</div>';
					break;
				case 'a_reshow': // user's question had been answered
					$anchor = as_anchor('A', $event['postid']);
					$url = as_path_html(as_q_request($event['params']['qid'], $event['params']['qtitle']), null, as_opt('site_url'),null,$anchor);
					$output .='<div class="event-icon pull-left icon-chat"></div>
								<div class="event-content">
									<p class="title">
										<span class="what">your answer had been made visible</span>
									</p>
									<a class="title" href="' . $url . '">'. $event['params']['qtitle'] . '</a>
									<span class="date"> ' . $event['date'] . '</span>
								</div>';
					break;
				case 'c_reshow': // user's question had been answered
					$anchor = as_anchor('C', $event['postid']);
					$url = as_path_html(as_q_request($event['params']['qid'], $event['params']['qtitle']), null, as_opt('site_url'),null,$anchor);
					$output .='<div class="event-icon pull-left icon-chat"></div>
								<div class="event-content">
									<p class="title">
										<span class="what">your comment had been accepted</span>
									</p>
									<a class="title" href="' . $url . '">'. $event['params']['qtitle'] . '</a>
									<span class="date"> ' . $event['date'] . '</span>
								</div>';
					break;
				case 'a_select': // user's question had been answered
					$anchor = as_anchor('A', $event['postid']);
					$url = as_path_html(as_q_request($event['params']['qid'], $event['params']['qtitle']), null, as_opt('site_url'),null,$anchor);
					$output .='<div class="event-icon pull-left icon-chat"></div>
								<div class="event-content">
									<p class="title"><strong class="avatar"><a href="' . $user_link . '">' . $handle . '</a></strong>
										<span class="what">selected your answer</span>
									</p>
									<a class="title" href="' . $url . '">'. $event['params']['qtitle'] . '</a>
									<span class="date"> ' . $event['date'] . '</span>
								</div>';
					break;
				case 'in_q_vote': // user's question had been answered
					$anchor = as_anchor('Q', $event['postid']);
					$url = as_path_html(as_q_request($event['params']['qid'], $event['params']['qtitle']), null, as_opt('site_url'),null,$anchor);
					if($event['params']['q_vote_up'])
						$vote_status .= '<span>' . $event['params']['q_vote_up'] . ' upvotes</span>';
					if($event['params']['q_vote_down']){
						if (isset($vote_status))
							$vote_status .= ' - ';
						$vote_status .= '<span>' . $event['params']['q_vote_down'] . ' downvotes</span>';
					}
					if( ($event['params']['favorite']) && ($event['params']['favorite']>=1) ){
						if (isset($vote_status))
							$vote_status .= ' - ';
						$vote_status .= '<span>' . $event['params']['favorite'] . ' favourites</span>';
					}
					$output .='<div class="event-icon pull-left icon-chat"></div>
								<div class="event-content">
									<p class="title">
										<span class="what">your question received new votes: ' . $vote_status .'</span>
									</p>
									<a class="title" href="' . $url . '">'. $event['params']['qtitle'] . '</a>
									<span class="date"> ' . $event['date'] . '</span>
								</div>';
					break;
				case 'in_a_vote': // user's question had been answered
					$anchor = as_anchor('A', $event['postid']);
					$url = as_path_html(as_q_request($event['params']['qid'], $event['params']['qtitle']), null, as_opt('site_url'),null,$anchor);
					if($event['params']['a_vote_up'])
						$vote_status = '<span>' . $event['params']['a_vote_up'] . ' upvotes</span>';
					if($event['params']['a_vote_down']){
						if (isset($vote_status))
							$vote_status .= ' - ';
						$vote_status .= '<span>' . $event['params']['a_vote_down'] . ' downvotes</span>';
					}
					$output .='<div class="event-icon pull-left icon-chat"></div>
								<div class="event-content">
									<p class="title">
										<span class="what">your answer received new votes: ' . $vote_status .'</span>
									</p>
									<a class="title" href="' . $url . '">'. $event['params']['qtitle'] . '</a>
									<span class="date"> ' . $event['date'] . '</span>
								</div>';
					break;
				case 'q_approve':
					$anchor = as_anchor('Q', $event['postid']);
					$url = as_path_html(as_q_request($event['params']['qid'], $event['params']['qtitle']), null, as_opt('site_url'),null,$anchor);
					$output .='<div class="event-icon pull-left icon-chat"></div>
								<div class="event-content">
									<p class="title">
									<span class="what">your question was approved</span>
									</p>
									<a class="title" href="' . $url . '">'. $event['params']['qtitle'] . '</a>
									<span class="date"> ' . $event['date'] . '</span>
								</div>';
					break;
				case 'a_approve':
					$anchor = as_anchor('A', $event['postid']);
					$url = as_path_html(as_q_request($event['params']['qid'], $event['params']['qtitle']), null, as_opt('site_url'),null,$anchor);
					$output .='<div class="event-icon pull-left icon-chat"></div>
								<div class="event-content">
									<p class="title">
									<span class="what">your answer was approved</span>
									</p>
									<a class="title" href="' . $url . '">'. $event['params']['qtitle'] . '</a>
									<span class="date"> ' . $event['date'] . '</span>
								</div>';
					break;
				case 'c_approve':
					$anchor = as_anchor('C', $event['postid']);
					$url = as_path_html(as_q_request($event['params']['qid'], $event['params']['qtitle']), null, as_opt('site_url'),null,$anchor);
					$output .='<div class="event-icon pull-left icon-chat"></div>
								<div class="event-content">
									<p class="title">
									<span class="what">your comment was approved</span>
									</p>
									<a class="title" href="' . $url . '">'. $event['params']['qtitle'] . '</a>
									<span class="date"> ' . $event['date'] . '</span>
								</div>';
					break;
				case 'q_reject':
					$anchor = as_anchor('C', $event['postid']);
					$url = as_path_html(as_q_request($event['params']['qid'], $event['params']['qtitle']), null, as_opt('site_url'),null,$anchor);
					$output .='<div class="event-icon pull-left icon-chat"></div>
								<div class="event-content">
									<p class="title">
									<span class="what">your question was rejected</span>
									</p>
									<span class="title">' . $event['params']['qtitle'] . '</span>
									<span class="date"> ' . $event['date'] . '</span>
								</div>';
					break;
				case 'a_reject':
				case 'c_reject':
					break;
				case 'u_level':
					$output .='<div class="event-icon pull-left icon-chat"></div>
								<div class="event-content">
									<p class="title">
										<span class="what">You level had been changed from' . as_html(as_user_level_string($event['params']['oldlevel'])) . ' to ' . as_html(as_user_level_string($event['params']['level'])) . '</span>
									</p>
								</div>';
					break;
				case 'a_post':

					break;
			}
		}
		$output .='</li>';
		return $output;
	}
	function messagelist(){
		require_once AS_INCLUDE_DIR.'as-db-users.php';
		// Get Events
		$message_events = array(
			'u_message',
			'u_wall_post',
		);
		$events = "'".implode("','",$message_events)."'";
		$userid = as_get_logged_in_userid();
		$eventslist = as_db_read_all_assoc(
			as_db_query_sub(
				'SELECT UNIX_TIMESTAMP(datetime) AS datetime, userid, postid, effecteduserid, event, params FROM ^userlog WHERE effecteduserid=# AND 
				DATE_SUB(CURDATE(),INTERVAL # DAY) <= datetime AND
				event IN (' . $events .')
				ORDER BY datetime DESC'.(as_opt('qat_activity_number')?' LIMIT '.(int)as_opt('qat_activity_number'):''),
				$userid, as_opt('qat_activity_age')
			)
		);
		$event = array();
		$output='';
		//
		$userids = array();
		foreach ($eventslist as $event){
			$userids[$event['userid']]=$event['userid'];
			$userids[$event['effecteduserid']]=$event['effecteduserid'];
		}
		$handles = as_db_user_get_userid_handles($userids);
		// get event's: time, type, parameters
		// get post id of questions
		foreach ($eventslist as $event){
			$title='';
			$link='';
			$handle = $handles[$event['userid']];
			$user_link = as_path('user/'.$handle);
			$reciever_handle = $handles[$event['effecteduserid']];
			$reciever_link = as_path('user/'.$reciever_handle);
			$datetime = $event['datetime'];
			$event['date'] = as_html(as_time_to_string(as_opt('db_time')-$datetime));
			$event['params'] = json_decode($event['params'],true);
			$message = substr($event['params']['message'], 0, 20);
			$output .='<li>';
			switch($event['event']){
				case 'u_message': // related question to an answer
					$url = as_path_html(as_path('message/' . $handle ));
					$output .='<div class="event-icon pull-left icon-chat"></div>
								<div class="event-content">
									<p class="title"><strong class="avatar"><a href="' . $user_link . '">' . $handle . '</a></strong>
									<span class="what">left you a message</span>
									</p>
									<a class="title" href="' . $url . '">'. $message . '</a>
									<span class="date"> ' . $event['date'] . '</span>
								</div>';						
					break;
				case 'u_wall_post': // user's question had been answered
					$url = as_path_html(as_path('user/' . $reciever_handle . '/wall' ));
					$output .='<div class="event-icon pull-left icon-chat"></div>
								<div class="event-content">
									<p class="title"><strong class="avatar"><a href="' . $user_link . '">' . $handle . '</a></strong>
									<span class="what">posted a message on your wall</span>
									</p>
									<a class="title" href="' . $url . '">'. $message . '</a>
									<span class="date"> ' . $event['date'] . '</span>
								</div>';						
					break;
			}
		}
		$output .='</li>';
		return $output;
	}
}
