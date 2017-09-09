/*
	APS Market (c) Jatin Soni
	http://www.q2amarket.com/

	File:           js/snow-core.js
	Version:        Snow 1.4
	Description:    JavaScript helpers for SnowFlat theme

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.
*/
$(document).ready(function () {

	/**
	 * Account menu box toggle script
	 */
	$('#asm-account-toggle').click(function (e) {
		e.stopPropagation();
		$(this).toggleClass('account-active');
		$('.asm-account-items').slideToggle(100);
	});

	$(document).click(function () {
		$('#asm-account-toggle.account-active').removeClass('account-active');
		$('.asm-account-items:visible').slideUp(100);
	});

	$('.asm-account-items').click(function (event) {
		event.stopPropagation();
	});

	/**
	 * Main navigation toggle script
	 */
	$('.asm-menu-toggle').click(function () {
		$('.as-nav-main').slideToggle(100);
		$(this).toggleClass('current');
	});

	/*
	 * Sidepannel Toggle Click Function
	 */
	$('#asm-sidepanel-toggle').click(function () {
		$('#asm-sidepanel-mobile').toggleClass('open');
		$(this).toggleClass('active');
		$(this).find('i').toggleClass('icon-right-open-big');
	});

	/**
	 * Toggle search box for small screen
	 */
	$('#asm-search-mobile').click(function () {
		$(this).toggleClass('active');
		$('#the-top-search').slideToggle('fast');
	});


	/*
	 * Add wrapper to users point on users list
	 */
	$('.as-top-users-score').wrapInner('<div class="asm-user-score-icon"></div>');

	/*
	 * add wrapper to the message sent note 'td'
	 */
	$('.as-part-form-message .as-form-tall-ok').wrapInner('<div class="asm-pm-message"></div>');

	// fix the visible issue for main nav, top search-box
	$(window).resize(function () {
		if (window.matchMedia('(min-width: 980px)').matches) {
			$(".asm-search.the-top .as-search").hide();
			$(".as-nav-main").show('fast', function() { $(this).css('display','inline-block'); });
		} else {
			$(".asm-search.the-top .as-search").show();
			$(".as-nav-main").hide();
			$('.asm-menu-toggle').removeClass('current');
		}
	});

});
