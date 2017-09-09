$(document).ready(function(){
	var qlist = $('.as-q-list');
	var layoutdefault = getCookie('layoutdefault');
	var layout = getCookie('layout');
	if(! layout)
		layout = layoutdefault;
	//alert(layout);
	// get cookies
	function getCookie(cname) {
		var name = cname + "=";
		var ca = document.cookie.split(';');
		for(var i=0; i<ca.length; i++) {
			var c = ca[i];
			while (c.charAt(0)==' ') c = c.substring(1);
			if (c.indexOf(name) != -1) return c.substring(name.length,c.length);
		}
		return "";
	}

	// Isotope or page list for question lists
	if(layout == 'list')
		layout_list();
	else if(layout == 'masonry')
		qlist.isotope({
			itemSelector: '.as-q-list-item',
			layoutMode: 'masonry',
			resizable: false,
			// layout mode options
		});
	// Infinite Scroll
	var scroll_loading = false;
	function InfinitScroll(){
		as_show_waiting_after(document.getElementById('infinite-ajax-load-more'), true);
		if(scroll_loading == false){
			scroll_loading = true;
			$.ajax({
				url: it_ajax_infinite_page_url,
				data: { page: it_ajax_infinite_page_number },
				type: "POST"
			}).done(function(data) {
				$('#ajax-holder').html( data );
				if($('#ajax-holder > .as-q-list > div').length < 1){
					$('#infinite-ajax-load-more').html('There is nothing more here!');
				}else{
					$('#ajax-holder > .as-q-list > div').each(function( index ) {
						id = $(this).attr('id');
						if(!( $('.as-part-q-list > form > .as-q-list #' + id ).length )){
								var elem = $('#ajax-holder > .as-q-list #'+id);
								qlist.append( elem );
								if(layout != 'qlist')
									qlist.isotope( 'appended', elem ).fadeIn();
						}
					});
					as_hide_waiting(document.getElementById('infinite-ajax-load-more'));
					Prepare_share_buttons();
					scroll_loading = false;
					it_ajax_infinite_page_number+=1;
					if(layout == 'list')
						layout_list();
					else if(layout == 'masonry')
						qlist.isotope({
							itemSelector: '.as-q-list-item',
							layoutMode: 'masonry',
							// layout mode options
						});

				}
			});
		}
	}
	$(".infinite-ajax-load-more").click(function (e) {
		InfinitScroll();
		e.preventDefault();
	});
	if(it_ajax_infinite_autoload == 1){
	$(window).scroll(function() {
	   if($(window).scrollTop() + $(window).height() > $(document).height() - 300) {
			if( $('#infinite-ajax-suggest').length ) // check if infinity is enabled in this page
				InfinitScroll();
	   }
	});
	}
	// layout
	$('#list-layout-btn').on('click', function(event) {
		layout_list();
		layout = 'list';
		document.cookie="layout=list";
	});
	$('#masonry-layout-btn').on('click', function(event) {
		layout_masonry();
		layout = 'masonry';
		document.cookie="layout=masonry";
	});
	
	// change layout style to list instead of masonry
	function layout_list() {
		$( '.as-q-list-item' ).each(function( index ) {
			if ($(this).hasClass('col-md-4')) {
				$(this).removeClass( "col-md-4" );
				$(this).addClass( "col-md-12 q-layout-list" );
				var featured_image = $(this).find( ".featured-image-item" );
				if( $(featured_image).length ){
					$(featured_image).removeClass( "featured-image-item" );
					$(featured_image).addClass( "featured-image-item-list col-md-6" );
				}
			}
		});
		qlist.isotope();
	}
	$(window).resize(function () {
		if(layout == 'list')
			layout_list();
	});
	// change layout style to masonry instead of list
	function layout_masonry() {
		$( '.as-q-list-item' ).each(function( index ) {
			if ($(this).hasClass('col-md-12')) {
				$(this).removeClass( "col-md-12 q-layout-list" );
				$(this).addClass( "col-md-4" );
				var featured_image = $(this).find( ".featured-image-item-list" );
				if( $(featured_image).length ){
					$(featured_image).removeClass( "featured-image-item-list col-md-6" );
					$(featured_image).addClass( "featured-image-item" );
				}
			}
		});
		qlist.isotope();
	}
	// share links in question list
	//$(".share-item-list").click(function () {
	$( "body" ).delegate( ".share-item-list", "click",function () {
		//show share link
		elem = $(this).parents('.as-q-item-main');
		$(elem).find(".share-container").fadeIn(500);
		$( elem ).find(".share-list-buttons li a").each(function( index ) {
			$( this ).on('click', function(e){
				url = $( this ).attr('href');
				w = 580;
				h = 470;
				// Fixes dual-screen position                         Most browsers      Firefox
				var dualScreenLeft = window.screenLeft !== undefined ? window.screenLeft : screen.left;
				var dualScreenTop = window.screenTop !== undefined ? window.screenTop : screen.top;

				var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
				var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

				var left = ((width / 2) - (w / 2)) + dualScreenLeft;
				var top = ((height / 3) - (h / 3)) + dualScreenTop;

				var newWindow = window.open(url, title, 'scrollbars=yes, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);

				// Puts focus on the newWindow
				if (window.focus) {
					newWindow.focus();
				}
				e.preventDefault();
			});
		});
	});
	// close share buttons list in question lists
	$(".as-q-item-main").delegate(".share-close", "click", function() {
		$(this).parent().hide();
	});
	// back to top
	if ( ($(window).height() + 100) < $(document).height() ) {
		$('#top-link-block').removeClass('hidden').affix({
			// how far to scroll down before link "slides" into view
			offset: {top:100}
		});
	}
	// Remove Search if user Resets Form or hits Escape!
	$('body, .main-navbar form[role="search"] button[type="reset"]').on('click keyup', function(event) {
		if (event.which == 27 && $('.main-navbar form[role="search"]').hasClass('active') ||
			$(event.currentTarget).attr('type') == 'reset') {
			closeSearch();
		}
	});


	// header search bar
	function closeSearch() {
        var $form = $('.main-navbar form[role="search"].active')
    	$form.find('input').val('');
		$form.removeClass('active');
	}

	// Show Search if form is not active // event.preventDefault() is important, this prevents the form from submitting
	$(document).on('click', '.main-navbar form[role="search"]:not(.active) button[type="submit"]', function(event) {
		event.preventDefault();
		var $form = $(this).closest('form'),
			$input = $form.find('input');
		$form.addClass('active');
		$input.focus();
	});
});
// When page load is finished
	$(window).bind("load", function() {
		Prepare_share_buttons();
	});
	function Prepare_share_buttons(){
		// share count
		var share_btn = [];
		$( '.as-q-item-main' ).each(function( index ) {
			// create share links
			share_btn[index] = $(this).find(".share-item-list");
			link = $(share_btn[index]).attr( "data-share-link" );
			title = $(share_btn[index]).attr( "data-share-title" );
			if(!($(this).find(".share-container").length))
				$(this).append(
				'<div class="share-container" style="display:none;">'+
				'<button class="close share-close" type="button"><span aria-hidden="true">×</span></button>'+
				'<ul class="share-list-buttons">'+
				'<li><a class="btn-share btn-facebook" href="https://www.facebook.com/sharer/sharer.php?u=' + link + '"><i class="fa  fa-facebook"></i><span class="share-site-title">FaceBook</span></a></li>'+
				'<li><a class="btn-share btn-twitter" href="http://twitter.com/home?status=' + title + ' ' + link + '"><i class="fa fa-twitter"></i><span class="share-site-title">Twitter</span></a></li>'+
				'<li><a class="btn-share btn-google-plus" href="https://plus.google.com/share?url=' + link + '"><i class="fa fa-google-plus"></i><span class="share-site-title">Google+</span></a></li>'+
				'<li><a class="btn-share btn-pinterest" href="http://pinterest.com/pin/create/button/?url=' + link + '&description=' + title + '"><i class="fa fa-pinterest"></i><span class="share-site-title">Pinterest</span></a></li>'+
				'</ul></div>'
				);
		});
	}
// override as_reveal to show error boxes
as_reveal = function(elem, type, callback){
	if(!($(elem).find(".close").length) && elem.className=='as-error')
		elem.innerHTML = elem.innerHTML + '<button type="button" data-dismiss="alert" class="close" onclick="$(\'#errorbox\').remove();"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>';
  	if (elem)
		$(elem).fadeIn(400,'swing', callback);
};
// override as_favorite_click for favorite list buttons
as_favorite_click = function(elem){
	var ens=elem.name.split('_');
	if(ens[0]=="favorite-login"){
		// show error message for user who is not logged in
		var mess=document.getElementById('errorbox');
		var anchor=ens[1];
		if (!mess) {
			var mess=document.createElement('div');
			mess.id='errorbox';
			mess.className='as-error';
			mess.innerHTML='Please Register or log in to favorite a post!';
			mess.style.display='none';
		}
		var postelem=document.getElementById(anchor);
		var e=postelem.parentNode.insertBefore(mess, postelem);
		as_reveal(mess);
	}else{
		// favorite post
		var postid = ens[2];
		var code=$('#fav_code_'+postid).val();
		var count = $(elem).text();
		
		as_ajax_post('favorite', {entitytype:ens[1], entityid:ens[2], favorite:parseInt(ens[3]), code:code},
			function (lines) {
				if (lines[0]=='1'){
					//$('.as-favoriting-'+postid).hide();
					var container = $('.as-favoriting-'+postid);
					//as_set_inner_html($(elem).parent(), 'favoriting', lines.slice(1).join("\n"));
					$('.as-favoriting-'+postid).html(lines.slice(1).join("\n"));
					if(ens[3]==0){
						count = parseInt(count) - 1;
						$('.as-favoriting-'+postid).find('.as-favorite').html(count);
					}else{
						count = parseInt(count) + 1;
						$('.as-favoriting-'+postid).find('.as-unfavorite').html(count);
					}
					//$(elem).hide();
				}else if (lines[0]=='0') {
					alert(lines[1]);
					as_hide_waiting(elem);
				} else
					as_ajax_error();
			}
		);
		
		as_show_waiting_after(elem, false);
	}
	return false;
}

