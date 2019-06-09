/* global jobListing, gapi */

/**
 *
 *	jl.js
 *	javascript for the jobs page
 *	@author  Ronald Boutilier
 */
var searchState = {};

(function($) {
/***********************************************************
* constants and global variables
***********************************************************/

var submitWaitTime = 3000;
var submitTimer;
var defaultTitle = 'Jobs - Brookdale Careers';
var socialCodes = {'facebook'		: 'fb',
					'twitter'		: 'tw',
					'google_plus'	: 'gg',
					'reddit'		: 'red',
					'pinterest'		: 'pin',
					'linkedin'		: 'li'};

/***********************************************************
* misc functions 
***********************************************************/
/**
 * General purpose sorting routine for <div> listings
 * the data used for sorting is contained in data-dataVal 
 * attributes.  
 * 
 * @param {string} tableId		Id of the surrounding container
 * @param {string} dataVal		the data- value which contains the sort data in each row
 * @param {string} orderType	'asc' or 'desc' for the direction of sort
 * @param {string} sortClass	the class which identifies the rows to sort 
 * @returns {void}
 */
function jlSortDivs( tableId, dataVal, orderType, sortClass ) {
	var tableEl = jQuery('#' + tableId);
	var rows = jQuery('.' + sortClass);

	rows.sort(function(a, b){
		var keyA = jQuery(a).data(dataVal);
		var keyB = jQuery(b).data(dataVal);

		if ( orderType === 'asc') {
			return (keyA > keyB || keyB === 'undefined') ? 1 : -1;
		} else {
			return (keyA < keyB || keyA === 'undefined') ? 1 : -1;
		}
	});

	jQuery.each(rows, function(index, row){
		tableEl.append(row);
	});
}

/**
 * Sets the current page count of the job listings in a hidden input  
 * field so that the info is passed to the php ajax functions
 * @param {number} pgCnt	The new setting of the page count of the job listings
 * @returns {void}
 */
function jlSetPageCount( pgCnt ) {
	$( '#pagenumber' ).val( pgCnt );
}

/**
 * Retrieves the current page count of the job listings from a hidden input field
 * @returns {number}		The current page count of the job listings
 */
function jlGetPageCount() {
	return $( '#pagenumber' ).val();
}
/**
 * Sets the current jobs per page of the job listings in a hidden input  
 * field so that the info is passed to the php ajax functions
 * @param {number} jobsPage		The new setting of the  jobs per page of the job listings
 * @returns {void}
 */
function jlSetJobsPage( jobsPage ) {
	$( '#recordperpage').val( jobsPage );
}
/**
 * Toggles the display of the first filter section
 * if necessary, it also toggles the 3nd filter section
 * @returns {void}
 */
function jlToggleFilter2() {
	jQuery( '#jl-filter-1-open').toggleClass('btn-filters').toggleClass('btn-filters-active');
	if ( jQuery( '#jl-filter-2').css( 'display' ) === 'none' ) {
		jQuery( '#jl-filter-1-open').html(' Hide Filters');
	} else {		
		jQuery( '#jl-filter-1-open').html(' Filter Results');
	}
	
	if ( jQuery( '#jl-filter-3').css( 'display' ) !== 'none' ) {
		jlToggleFilter3();
	}
	$( '#jl-filter-2' ).slideToggle({easing:'easeInCubic'});
}
/**
 * Toggles the display of the second filter section
 * @returns {void}
 */
function jlToggleFilter3() {
	if ( jQuery( '#jl-filter-3').css( 'display' ) === 'none' ) {
		jQuery( '#jl-filter-2-open').html('Fewer Filters');
	} else {
		jQuery( '#jl-filter-2-open').html('More Filters');
	}
	$( '#jl-filter-3' ).slideToggle({easing:'easeInCubic'});
}


/***********************************************************
* modal message functions 
***********************************************************/
/**
 * Display modal popup for both msgs and ajax loading
 * @param {string} msg  The message to display
 * @param {boolean} closeBtn  whether to display the Close button
 * @returns {void}
 */
function jlDispMsg( msg, closeBtn ) {
	// create own modal popup window
	// closeBtn is true if normal msg box..  
	// false is for ajax temp disp
	
	if ( closeBtn ) {
		$( '#jl-msg-close').show();
	} else {
		$( '#jl-msg-close').hide();
	}
	
	$( '#jl-msg-text' ).html( msg );
	$( '#jl-msg-box' ).show();
	$( '#jl-modal-layer' ).show();
}
/**
 * Closes the Modal msg box
 * @returns {void}
 */
function jlCloseMsg() {
	$( '#jl-modal-layer' ).hide();
	$( '#jl-msg-box' ).hide();
}
/**
 * create own modal popup window for Map 
 * @param {string} map google map url
 * @returns {void}
 */
function jlDispMap( map ) {

	$( '#jl-map-data' ).html( map );
	$( '#jl-map-box' ).show();
	$( '#jl-modal-layer' ).show();
}
/**
 * closes modal map window
 * @returns {void}
 */
function jlCloseMap() {
	$( '#jl-modal-layer' ).hide();
	$( '#jl-map-box' ).hide();
}
/**
 * create own modal popup window for Social Media 
 * @returns {void}
 */
function jlDispSocial(  ) {

	$( '#jl-social-media-box' ).show();
	$( '#jl-modal-layer' ).show();
}
/**
 * closes modal social media window
 * @param {string} detailURL - need to replace the detail url with the placeholder
 * @returns {void}
 */
function jlCloseSocialMedia(detailURL) {
	$( '#jl-modal-layer' ).hide();
	$( '#jl-social-media-box' ).hide();
	// need to reverse the href in each button to the placeholder
	// each button has a url placeholder that gets changed out when
	// it is opened for a particular job as there is only one
	// social media display box for all the job details
	
	$('.synved-social-button-share').each(function () {
		var newURL = jobListing.socialURLPlaceholder;
		var UrlRe = new RegExp(buildSocialLink(this, detailURL), 'g');
		var TitleRe = new RegExp(jobListing.socialTitlePlaceholder, 'g');
		$(this).attr('href', $(this).attr('href').replace(UrlRe, newURL));
		var pageTitle = encodeURIComponent($(document).attr('title'));
		$(this).attr('href', $(this).attr('href').replace(TitleRe, pageTitle));
	});
}
/**
 * create own modal popup window for Apply 
 * @param {object} dataObj object of data elements for the Apply btns
 * @returns {void}
 */
function jlDispApply( dataObj ) {
	// first set up the apply href to the Apply with Brookdale button
	if (dataObj.hasOwnProperty('href')) {
		$('#jl-apply-brookdale').prop('href', dataObj.href);
	}
	
	// now create a span for the Indeed button and loop through the data 
	// attributes and attach all "indeed-apply-*" to the span
	$('#indeed-apply-li').append('<span class="indeed-apply-widget"></span>');
	var indeedSpan = $('.indeed-apply-widget');
	var newDataName;
	for ( var dataName in dataObj ) {		
		if (dataName.substr(0, 11) === 'indeedApply') {
			// jquery converts the data attributes to camelcase...which does
			// not work for Indeed...have to convert back
			newDataName = dataName.replace('indeedApply', 'data-indeed-apply-');
			indeedSpan.attr(newDataName, dataObj[dataName]);
		}
	}
	// finally run the javascript code to load the Indeed button
	// set up some manipulation of the styles as they are created 
	// on the fly by the Indeed widget code
	$(".indeed-apply-widget").on("DOMNodeInserted", function(e) {
		$(this).removeAttr("style"),
		$(e.target).hasClass("indeed-apply-button") && ($(".indeed-apply-button-label").text("Apply via  "),
		$(".indeed-apply-button-inner").attr("style", "background-image: none !important"),
		$(".indeed-apply-button-cm img").attr("src", "http://localhost/brook/wp-content/themes/genesis-sample/images/logo-indeed.png"));
	});
	loadIndeedButton();
	// we are done, use timer to wait for Indeed Btn and then show box
	lookForIndeedBtn();
}
/**
 * a timer that looks to see if the Indeed Apply Btn has
 * loaded and opens the modal when it is ready
 * @returns {void}
 */
function lookForIndeedBtn() {
	setTimeout( function() {
		if ( $('.indeed-apply-button').length ) {
			$( '#jl-apply-now-box' ).show();
			$( '#jl-apply-brookdale' ).removeClass('hidden');
			$( '#jl-modal-layer' ).show();
		} else {
			lookForIndeedBtn();
		}
	}, 5);
}
/**
 * this code removes all parts of the Indeed Button so that I 
 * re-instantiate it later with a different job
 * @returns {void}
 */
function removeIndeedBtn() {
	window.IndeedApply = null;
	$('#indeed-apply-iframe-holder').remove();
	$('.indeed-apply-popup-bg').remove();
	$('.indeed-apply-popup').remove();
	$('.indeed-apply-widget').remove();
	// now remove the <style> tag that was created
	var s = document.getElementsByTagName("STYLE");
	s = $.makeArray(s).filter(function(e) {
		return e.innerText.substring(0,10) === '#indeed-ia';
	});
	$(s).remove();

}
/**
 * closes modal map window
 * @returns {void}
 */
function jlCloseApply() {	
	$( '#jl-apply-brookdale' ).addClass('hidden');
	$( '#jl-modal-layer' ).hide();
	$( '#jl-apply-now-box' ).hide();
	removeIndeedBtn();
}

/***********************************************************
* ajax functions 
***********************************************************/
 /**
  * The ajax routine for retrieving the job listing.  It loads #listing-div after retrieving
  * the data.  Then, it binds the js code to these new elements, and sorts if needed
  * @param {string array} args - arguments to be passed to php 
  * @param {boolean} filterFlg - flag used to determine modal message
  * @param {boolean} detailFlag - flag used for loading detail page directly from query string
  * @param {string} src - string to be passed to the Apply Now buttons 
  * @returns {void}
  */
function jlAjaxLoadJobs( args, filterFlg, detailFlag, src) {
	// pg = page number to load
	// jobsPg = # jobs listed per page
	// args = object of search values
	
	var filterFlg  = ( typeof filterFlg === 'undefined' ) ? false : filterFlg;
	var detailFlag = ( typeof detailFlag === 'undefined') ? false : detailFlag;
	var src	= ( typeof src === 'undefined') ? 'BSLC' : src;
	
	// turn on msg modal to inform user that the jobs are loading
	var modalMsg = filterFlg ? 'Filtering Jobs...' : 'Loading Jobs...';
	jlDispMsg( '<br><br>' + modalMsg, false );
	
	// load ajax into main content
	jQuery.ajax( {
		url: jobListing.ajaxurl,
		type: 'POST',
		datatype: 'html',
		data: {
			action: 'list_jobs',
			security: jobListing.security,
			src: src,
			srch_args: args,
			detail_flag: detailFlag
		},
		success: function (responseText) {
			jlCloseMsg();
			jQuery( '#jl-form-div' ).css( 'min-height', '0' );
			if ( jQuery( '#jl-filter-2').css( 'display' ) !== 'none' ) {
				jlToggleFilter2();
			}
			// if the detail flag is set, we are automatically loading the detail
			// page based on a query string
			jQuery('#listing-div').html(responseText);
			jlAjaxPageSetup();
			// see if the listing needs to be sorted
			if ( $( '#jl-sort' ).val() !== 'none' && $('#jl-jobs-sort').length > 0 ) {
				runSort( $('#jl-jobs-sort') );
			}
			
			if (detailFlag) {
				// automatically load the Job Detail Page if it returned a result
				if ($('.jl-show-details').length) {
					jlLoadJobDetail($('.jl-show-details').first()[0], false);
				}
			} else {
				var newURL = convertFormToQuery();
				var state = {display: 'search',
							 title: defaultTitle,
							 formString: newURL.substring(1)};
				currentState = state;
				$(document).attr('title', state.title);
				// if we did not get here from a query string, we need to set the pushstate
				if ( ! jobListing.queryFlag ) {
					if (typeof window.history.pushState === 'function') {
						window.history.pushState(state, '', newURL);
					}
					/****** TEST OF GOOGLE ANALYTICS ****/
					if (typeof ga === 'function') {
						//ga('set', 'page', newURL);
					//	ga('send', 'pageview');
					}
				} else {
					// use the replace state so that we can store the current state in the browser history
					if (typeof window.history.replaceState === 'function') {
						window.history.replaceState(state, '', newURL);
					}
				}
				// have to turn off queryFlag now that we have a result
				jobListing.queryFlag = false;
			}
		},
		error: function (xhr, status, errorThrown) {
			jlCloseMsg();
			jQuery( '#jl-form-div' ).css( 'min-height', '0' );
			jQuery('#listing-div').html('Error loading page: ' + errorThrown);
		}
	});
}
/**
 * attaches click events to a number of the elements returned by the ajax call
 * @returns {void}
 */
function jlAjaxPageSetup() {
	// need to set up some filters to trigger jobs search upon click
	// also need to set up timing interval before trigger
	
	jlSetUpFilterTriggers();
	
	jlSetUpPagination();
	
	jlSetUpJobsPerPage();
	
	jlSetUpSort();
	
	jlSetUpMap();
	
	jlSetUpApply();
	
	jlSetUpDetailBtn();
	
	//jlRemoveMobileHover();
	
}

/***********************************************************
* set up functions 
***********************************************************/
/**
 * Sets up tooltips
 * @returns {void}
 */
function jlSetUpToolTips() {
	// create object with key:value =  html id: tooltip 
	
	var tips = {
			'jl-keywords': 'Keywords separated by spaces are considered to be separated by an " OR ". Example: Sales Manager is treated as Sales OR Manager.  \
							To search for an exact phrase, enclose the phrase in quotation marks. Example: "Sales Manager".',
			'jl-zipcode': 'Enter either a city (Nashville), a city and state separated by a comma (Brentwood, TN), a state only (TN), or a zipcode (along with the radius field). '
			
		};
		
	for ( var id in tips ) {
		$( '#' + id ).attr( 'title' , tips[id] );
	}
}

function jlSetUpFilterTriggers() {
	// set up the the filter checkboxes/radio buttons to run search
	// must use timer so that search is not immediate
	
	/**** right now only updating on checkbox, would be easy to add select inputs ****/

	jQuery( 'input:checkbox' ).each( function( i, cbox ) {
		jQuery( cbox ).change( function ( e ) {
			if ( submitTimer ) {
				clearTimeout( submitTimer );
			}
			
			submitTimer = setTimeout( function () {
				var args = jQuery( '#jl-job-search' ).serializeArray();
				jlAjaxLoadJobs( args, true);
			}, submitWaitTime);
		});
	});
	
}
/**
 * sets up the sort select button, which is loaded after the ajax return
 * @returns {void}
 */
function jlSetUpSort( ) {
	// set up sort
	jQuery('#jl-jobs-sort').change(function (e) {
		runSort( $( this ));
	});
}
/**
 * sets up and calls the jlSortDivs function to sort the job
 * listing divs, sort order is determined by the select passed in
 * @param {object} el  jQuery object of the sort select element
 * @returns {void}
 */
function runSort( el ) {
	var sortInfo = el.val().split('_');
	var divId = 'jl-listing-div';
	var dataVal = sortInfo[0];
	var orderType = sortInfo[1];

	//jlSortTable( tableId, dataVal, orderType );
	jlSortDivs( divId, dataVal, orderType, 'result-container');

	$( '#jl-sort' ).val( el.val() );
}
/**
 * sets up the map buttons to display a modal popup
 * @returns {void}
 */
function jlSetUpMap() {
	$( 'div.map-button').unbind("click");
	$( 'div.map-button').click( function( e ) {
		jlDispMap( $( this ).data('map') );
	});
}
/**
 * sets up the share buttons to display a social media modal popup
 * @param {string} detailURL - query string of the job detail page.  this is being
 *		passed in just in case the user is on IE9 and we were unable to pushstate
 * @returns {void}
 */
function jlSetUpSocial( detailURL) {
	$( 'div.social-button').unbind("click");
	$( 'div.social-button').click( function( e ) {
		// the buttons were created with PHP w/ no knowledge of the query string
		// it contains "urlplaceholder" as the url, so we need to replace that
		// string with the actual url in all the href attributes
		var UrlRe = new RegExp(jobListing.socialURLPlaceholder, 'g');
		var TitleRe = new RegExp(jobListing.socialTitlePlaceholder, 'g');
		$('.synved-social-button-share').each(function () {
			var newURL = buildSocialLink(this, detailURL);
			$(this).attr('href', $(this).attr('href').replace(UrlRe, newURL));
			var pageTitle = encodeURIComponent($(document).attr('title'));
			$(this).attr('href', $(this).attr('href').replace(TitleRe, pageTitle));
		});
		jlDispSocial();
		// set up the close button with the correct url to swap out with the urlplaceholder
		$( '#jl-social-media-close' ).click( function() {
			jlCloseSocialMedia(detailURL);
		});
	});
}

/**
 * @param {element} socialBtn - DOM element for the share link button
 * @param {string} detailURL - the job detail page url.  we need to switch 
 *					out the src=xxx with a source code for each social site
 * @returns {string} returns a string containing the URL for each 
 *					social media sharing button w/ src=xxx
 */
function buildSocialLink(socialBtn, detailURL) {
	//var newURL = encodeURIComponent(window.location.href);
	// ie9 does not have window.location.origin...what a freaking shocker!!
	if (!window.location.origin) {
		window.location.origin = window.location.protocol + "//" + window.location.hostname + 
				(window.location.port ? ':' + window.location.port: '');
	}
	// now need to replace the src/title parameter in the detail URL with the social site specific src/title
	// if it is sent to email, just keep the same detail URL with original src (i.e. not a job board)
	var provider = $(socialBtn).data('provider');
	var socialURL = detailURL;
	if (provider !== 'mail') {
		var regex = new RegExp(/([?|&]src=)[^\&]+/);
		 socialURL = detailURL.replace(regex, '$1' + socialCodes[provider]);
	}

	var newURL = encodeURIComponent( window.location.origin + window.location.pathname + socialURL );
	return newURL;
}
function jlSetUpApply() {
	$( 'div.apply-button').unbind("click");
	$( 'div.apply-button').click( function( e ) {
		var jobId = $( this ).data('job');
		var articleEl = $('#' + jobId);
		// pass in an object of all the data attrs
		jlDispApply( articleEl.data() );
	});
}
/**
 * sets up the pagination links.  Clicking a pagination link
 * will update the hidden page field and run the form submit
 * @returns {void}
 */
function jlSetUpPagination() {
	// set up pagination
	jQuery('.btn-pagination').each( function( index, a ) {
		$(a).click( function ( e ) {
			e.preventDefault();
			// get current page # and update hidden field
			var pg = $( this ).html();

			// check for "next"
			if ( $( this ).hasClass('next') ) {
				pg = parseInt(jlGetPageCount()) + 1;
			}
			// check for "previous"
			if ( $( this ).hasClass('prev') ) {
				pg = parseInt(jlGetPageCount()) - 1;
			}
			jlSetPageCount( pg );

			var args = jQuery( '#jl-job-search' ).serializeArray();
			jlAjaxLoadJobs( args );
		});
	});
}
/**
 * sets up the jobs/page select.  Changing the # jobs per page
 * will update the hidden jobsPage field and run the form submit
 * @returns {void}
 */
function jlSetUpJobsPerPage() {
	// change the jobs per page setting and run the ajax get from caching routine
	
	$('#jl-jobs-page').change( function( e ) {
		var jobsPage = $( this ).val();	
		var pg  = jlGetPageCount();
		var oldJobsPage = $( '#recordperpage').val();
		var newPg = Math.ceil( ((oldJobsPage * (pg - 1)) + 1) / jobsPage );
		jlSetJobsPage( jobsPage );
		jlSetPageCount( newPg );
					
		var args = jQuery( '#jl-job-search' ).serializeArray();
		jlAjaxLoadJobs( args );
		
	});
}
/*
 * sets up the Show Details button to load the job detail page
 * @returns {void}
 */
function jlSetUpDetailBtn() {
	$('.jl-show-details, .jl-job-title').click( function () {
		searchState = currentState;
		jlLoadJobDetail( this );
	});
}
/**
 * 
 * @param {object} btn  jQuery element object representing the
 * button that was clicked.  A hidden div for that job detail is
 * placed into the job-detail-div and displayed.  It also sets up
 * the images and video from data attributes.  Only an image is loaded
 * of the video until the user clicks on it
 * @param {boolean} updateURL - flag to determine whether we need to update
 *						the URL...depends on whether we have a query string 
 *						already.
 * @returns {void}
 */
function jlLoadJobDetail( btn, updateURL ) {
	var updateURL = (typeof updateURL === 'undefined') ? true : updateURL;
	var jobId = $( btn ).data('job');
	var articleEl = $('#' + jobId);
	$('#job-detail-div').html( articleEl.html());
	$('#search-right').addClass('hidden');
	$('#search-left').addClass('hidden');
	$('#job-detail-div').removeClass('hidden');
	// set up the Return to Search button
	$('.breadcrumb').click( function () {
		// if we got here from a search page, we just need to 
		// run the browsers Back button, otherwise,
		// just close the detail page and push the state with the 
		// Brookdale Job Number in the keywords field
		if (jobListing.queryFlag) {
			// get just the brookdale job id from the id data field
			var brookId = jobId.split('-');
			brookId = brookId[1];
			$('#jl-keywords').val(brookId);
			closeDetailPage();
			var newURL = convertFormToQuery();
			var state = {display: 'search',
						 title: defaultTitle,
						 formString: newURL.substring(1)};
			currentState = state;
			$(document).attr('title', state.title);
			if (typeof window.history.pushState === 'function') {
				window.history.pushState(state, '', newURL);
			}
			jobListing.queryFlag = false;
		} else {
			if (typeof window.history.pushState === 'function') {
				// cannot use the history.back if on ie9 or older as it
				// does not use the pushstate
				console.log('indeed flag: ', jobListing.indeedFlag);
				if (jobListing.indeedFlag) {
					// we may have lost our history when the Indeed apply
					// ran, so just reload the previous search
					// run the normal back click link in the detail page
					closeDetailPage();
					window.history.pushState(searchState, '', '?' + searchState.formString);
					$(document).attr('title', searchState.title);
				} else {
					history.back();
				}
			} else {
				closeDetailPage();
			}
		}
		// we have to reset the indeed Flag as that was only for using this link
		jobListing.indeedFlag = false;
	});
	// put the images and videos in now.  
	// faster than including them in during jobs listing
	var jobImage = articleEl.data('job_img');
	var locImage = articleEl.data('loc_img');
	var jobVideo = articleEl.data('vid');
	var jobTitle = articleEl.data('job_title');
	$('.img-assoc').css('background-image', 'url(' + jobImage + ')');
	$('.loc-image').html('<img src="' + locImage + '">');
	//$('.video-div').html('<iframe width="300" height="169" src="' + jobVideo + '" frameborder="0" allowfullscreen></iframe>');
	$('.video-div').html('<div class="youtube-container"><div class="youtube-player" data-id="' + jobVideo + '"></div></div>');
	jlSetUpVideo();
	jlSetUpMap();
	jlSetUpApply();
	jlSetUpSocial( articleEl.data('detail_url') );
	//jlRemoveMobileHover();
	$(window).scrollTop(0);
	// need to run this even if we are not pushing the state
	var state = {display: 'detail',
				 title: jobTitle + ' - Brookdale Careers',
				 queryFlag: jobListing.queryFlag,
				 id: jobId};
	currentState = state;
	var newURL = articleEl.data('detail_url');
	$(document).attr('title', state.title);
	if (updateURL) {
		// process the browser URL change
		if (typeof window.history.pushState === 'function') {
			window.history.pushState(state, '', newURL);
		}
		/****** TEST OF GOOGLE ANALYTICS ****/
		if (typeof ga === 'function') {
			//ga('set', 'page', newURL);
			//ga('send', 'pageview');
		}
	} else {
		// use the replace state so that we can store the current state in the browser history
		if (typeof window.history.replaceState === 'function') {
			window.history.replaceState(state, '', newURL);
		}
	}
}
function closeDetailPage() {
	$('#job-detail-div').empty();
	$('#job-detail-div').addClass('hidden');
	$('#search-right').removeClass('hidden');
	$('#search-left').removeClass('hidden');
}

/**
 * sets up the video image link to load the actual video when clicked
 * @returns {void}
 */
function jlSetUpVideo() {
	$('.youtube-player').each( function () {
		var p = document.createElement('div');
		p.innerHTML = labnolThumb($(this).data('id'));
		p.onclick = labnolIframe;
		$(this).append(p);
	});
};
 
 /**
  * returns the image element for the youtube video with a
  * play button image on top
  * @param {string} id	youtube video id
  * @returns {String}
  */
function labnolThumb(id) {
    return '<img class="youtube-thumb" src="//i.ytimg.com/vi/' + id + '/hqdefault.jpg"><div class="play-button"></div>';
}
 /**
  * sets up the youtube iframe and replaces the image that was 
  * in place.  Run when the user clicks on the video image
  * @returns {void}
  */
function labnolIframe() {
    var iframe = document.createElement("iframe");
    iframe.setAttribute("src", "//www.youtube.com/embed/" + this.parentNode.dataset.id + "?autoplay=1&autohide=2&border=0&wmode=opaque&enablejsapi=1&showinfo=0");
	//iframe.setAttribute("src", "//www.youtube.com/embed/" + this.parentNode.dataset.id + "?autoplay=1");
    iframe.setAttribute("frameborder", "0");
    iframe.setAttribute("id", "youtube-iframe");
    iframe.setAttribute("allowfullscreen", "allowfullscreen");
    this.parentNode.replaceChild(iframe, this);
}
/**
 * sets up the open and close buttons for the 2 filter segments
 * @returns {void}
 */
function jlSetUpOpenClose() {
	//  set up the open and close icons for the filters
	// not only hide/show the sections, but also change the clickable link
	$( '#jl-filter-1-open').click( function ( e ) {
			e.preventDefault();
			jlToggleFilter2();
	});
	
	$( '#jl-filter-2-open').click( function ( e ) {
			e.preventDefault();
			jlToggleFilter3();
	});
	
	$( '#jl-filter-2-close').click( function ( e ) {
			e.preventDefault();
			jlToggleFilter2();
	});
}
/**
 * Clears all the checkboxes
 * @returns {void}
 */
function jlSetUpClearFilters() {
	$('#jl-clear-filters').click( function () {
		//$('input:checkbox').removeAttr('checked');
		$('input:checkbox').prop('checked', false);
	});
}
/**
 * routine that runs when the Search button is clicked.  Prevents the default
 * submit, sets the page count = 1, serializes the form data and 
 * sends it to the ajax request
 * @returns {void}
 */
function jlSetUpSearchSubmit() {
	$( '#jl-search-submit' ).click( function ( e ) {
		// in case a filter has been checked, turn off the timer
		if ( submitTimer ) {
			clearTimeout( submitTimer );
		}
		// need to reset the page counter to 1
		e.preventDefault();
		jlSetPageCount( 1);
		$( '#jl-job-search' ).submit();
	});
	$( '#jl-job-search' ).submit( function ( e ) {
		e.preventDefault();
		var args = $( this ).serializeArray();
		jlAjaxLoadJobs( args );
	});
}
/**
 * sets up the modal Close buttons
 * @returns {void}
 */
function jlSetUpModal() {
	$( '#jl-msg-close' ).click( function() {
		jlCloseMsg();
	});
	$( '#jl-map-close' ).click( function() {
		jlCloseMap();
	});
	$( '#jl-apply-now-close' ).click( function() {
		jlCloseApply();
	});
}
/**
 * The checkbox label was used by the designer for something other than the label
 * so this routine has to set up a click event to check the box on li click
 * @returns {void}
 */
function jlSetUpLiClick() {
	$( '.checkmark-li' ).click( function(e) {
		if (e.target !== this) {
			return;
		}
		$(this).find('input[type="checkbox"]').click();
	});
}
/**
 * Adds a click event to the Join Now button
 * @returns {void}
 */
function jlSetUpJoinNow() {
	$('.btn-join').click( function() {
		window.open('https://career.staffingsoft.com/careers/brookdalecareers.html');
	});
}
/**
 * If the device is mobile, turn off all hover events to
 * solve the problem of "sticky" hovers
 * code from Michael Vartan
 * @returns {void}
 */
function jlRemoveMobileHover() {
	// Check if the device supports touch events
	if ('ontouchstart' in document.documentElement) {
		// Loop through each stylesheet
		for (var sheetI = document.styleSheets.length - 1; sheetI >= 0; sheetI--) {
			var sheet = document.styleSheets[sheetI];
			// Verify if cssRules exists in sheet
			try {
				if (sheet.cssRules) {
					// Loop through each rule in sheet
					for (var ruleI = sheet.cssRules.length - 1; ruleI >= 0; ruleI--) {
						var rule = sheet.cssRules[ruleI];
						// Verify rule has selector text
						if (rule.selectorText) {
							// Replace hover psuedo-class with active psuedo-class
							rule.selectorText = rule.selectorText.replace(":hover", ":active");
						}
					}
				}
			} catch(e) {
				if (e.name !== 'SecurityError') {
					throw e;
				}
				continue;
			}
		}
	}
}
/*function jlSetUpGoogleApis() {
	gapi.load('client', function () {
		gapi.client.setApiKey('xxx');
		gapi.client.load('urlshortener', 'v1');
	});
}*/
/**
 * inital page setup run on document.ready
 * @returns {void}
 */
function jlPageSetup() {

	jlSetUpOpenClose();
	
	jlSetUpClearFilters();
	
	//jlSetUpCityZip();
	
	jlSetUpToolTips();
	
	jlSetUpSearchSubmit();
	
	jlSetUpModal();
        
    jlSetUpLiClick();
	
	jlSetUpJoinNow();
	
	jlRemoveMobileHover();
	
	//jlSetUpGoogleApis();
}


/***********************************************************
* document ready function
***********************************************************/
/**
 * initial document ready routine which calls the page setup
 */
jQuery(document).ready( function() {
	// need to track our current state so that we can use browser buttons
	currentState = null;
	
	// only need to set up popState event if the browser uses pushState
	if (typeof window.history.pushState === 'function') {
		setUpPopState(); 
	}
	
	jlPageSetup();
	
	checkQueryString();
	
});

/**
 * code to deal with push state and tracking the page states
 * as well as processing query strings 
 * $query_flag and $q_string are passed down from php
 */

/**
 * decides if front-end needs to 
 * process the query string
 * the query flag is set earlier in the page
 * by PHP along with a query string already
 * containing the GET variables
 */
function checkQueryString() {
	if (jobListing.queryFlag) {
		// convert query string to array of values
		var query = convertQueryToObject(jobListing.query);
		// check if display = detail...otherwise assume search
		if (query.hasOwnProperty('display') && query.display[0] === 'details') {
			if (!query.hasOwnProperty('j') || !query['j'][0].length) return;
			getAjaxDetail(query);
		} else {
			// set the form inputs based on the query string
			convertQueryToForm(query);
			// and submit 
				var args = jQuery( '#jl-job-search' ).serializeArray();
				jlAjaxLoadJobs( args );
		}
	}
}

function convertQueryToObject(str) {
	var queryObj = {};
	// the serialize function converts ' ' to '+'.  
	// decoding does not revert that
	var tmpstr = str.replace(/\+/g,' ');
	var srchArgs = decodeURIComponent(tmpstr).split('&');
	for (var i = 0; i < srchArgs.length; i++) {
		if (srchArgs[i].length) {
			srchPair = srchArgs[i].split('=');
			srchPair[0] = srchPair[0].toLowerCase();
			if (queryObj.hasOwnProperty(srchPair[0])) {
				// checkbox that has multiple selections
				queryObj[srchPair[0]].push(srchPair[1]);
			} else {
				queryObj[srchPair[0]] = [srchPair[1]];
			}
		}
	}
	return queryObj;
}

function convertFormToQuery() {
	var tmpQuery = '?' + $( '#jl-job-search' ).serialize();
	// remove the empty fields, like zip, that still create query entries "zip=&..."
	tmpQuery = tmpQuery.replace('keywords=&','');
	tmpQuery = tmpQuery.replace('zip=&','');
	tmpQuery = tmpQuery.replace('radius=&','');
	return tmpQuery;
}

function convertQueryToForm(query) {
	var searchParm;
	// first clear all the current settings
	// clear checkboxes
	$('input:checkbox').prop('checked', false);
	// clear inputs
	$('input:text').val('');
	// clear radius select, other selects will have value
	$('#jl-zip-distance').prop('selectedIndex',0);
	// loop through the query items, setting the form
	for(searchParm in query) {
		switch(searchParm) {
			case 'keywords':
				$('#jl-keywords').val(query[searchParm][0]);
				break;
			case 'zip':
				$('#jl-zipcode').val(query[searchParm][0]);
				break;
			case 'radius':
				$('#jl-zip-distance').val(query[searchParm][0]);
				break;
			case 'pagenumber':
				$('#pagenumber').val(query[searchParm][0]);
				break;
			case 'recordperpage':
				$('#recordperpage').val(query[searchParm][0]);
				break;
			case 'jlsort':
				$('#jl-sort').val(query[searchParm][0]);
				break;
			case 'experiencelevel':
			case 'jobtype':
			case 'jobclass':
			case 'clientcategory':
			case 'officetype':
			case 'key':
			case 'department':
			case 'state':
				for (var i = 0; i < query[searchParm].length; i++) {
					$('#jl-job-search :input[name="' + searchParm + '"][value="' + query[searchParm][i] + '"]').prop('checked', true);
				}
				break;
		}
	}
}

function getAjaxDetail(query) {
	// run Ajax searching for the job id and then 
	// display the detail page when it returns
	var args = [{name: 'id', value: query['j'][0]},
				{name: 'pagenumber', value: 1},
				{name: 'recordperpage', value: 10},
				{name: 'jlsort', value: 'date_desc'}];
console.log('referrer: ', document.referrer);	
	var src = (query.hasOwnProperty('src')) ? query['src'][0] : 'BSLC';
	jlAjaxLoadJobs(args, false, true, src);
}

function setUpPopState() {
	window.addEventListener('popstate', function(e) {
		/*
		* now, need to figure out what to do based on a few possibilities
		* 1) we are at the first search and user clicks back
		* 2) we are at a search and the user clicks to go to another search
		* 3) we are at a search and the user clicks to go to a detail page
		* 4) we are at a detail page and the user clicks to go to a search		* 
		*/		
		oldState = currentState;
		currentState = e.state;
		
		console.log(oldState);
		console.log(currentState);
				
		if (e.state === null) {
			// no mechanism to bring back an empty search form w/o reloading page
			window.location.assign(window.location.href.split('?')[0]);
			return;
		}
		if ((oldState === null || oldState.display === 'search') && e.state.display === 'search') {
			// run the normal ajax procedure that we would from an 
			// initial page load with a query string
			jobListing.queryFlag = true;
			jobListing.query = e.state.formString;
			console.log('search string: ' + e.state.formString);
			checkQueryString();
			$(document).attr('title', e.state.title);
			return;
		}
		if (oldState.display === 'search' && e.state.display === 'detail') {
			// need to load the query flag from the state
			jobListing.queryFlag = e.state.queryFlag;
			// run the same action that occurs on a row click
			var detailBtn = $('.jl-show-details[data-job="' + e.state.id + '"]')[0];
			searchState = oldState;
			jlLoadJobDetail(detailBtn, false);
			$(document).attr('title', e.state.title);
			return;
		}
		if (oldState.display === 'detail' && e.state.display === 'search') {
			// run the normal back click link in the detail page
			closeDetailPage();
			$(document).attr('title', e.state.title);
			return;
		}
	});
}
})(jQuery);

/**
 * callback function for post Indeed Apply
 * will use to set flag as the iframe screws
 * up the browser history
 * MUST BE IN THE GLOBAL NAMESPACE
 * @returns {void}
 */
function jlPostApply() {
	if (window.history.state.display === 'detail') {
		jobListing.indeedFlag = true;
	}

}



