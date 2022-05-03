<?php
/*
 * Template name: jobs
 */

//* incude job init processing
include_once(get_stylesheet_directory().'/jl/jl_jobs_init.php');

function job_form() {
	global $search_section_class, $detail_section_class, $job_detail, $crawler_flag, $detail_flag, $debug_string;
	global $social_url_placeholder, $social_title_placeholder;
	echo $debug_string;
	?>

<div class="outter">
  <?php if (! $crawler_flag || ! $detail_flag) { ?>
  <div class="wrapper">
    <div id="search-left" class="left <?php echo $search_section_class; ?>">
      <form id="jl-job-search" method="post">
        <h1><span class="jl-opp">Opportunity</span> lives here every day.</h1>
        <div class="searchfield">
          <h3>job/keyword</h3>
          <input type="text" id="jl-keywords" name="keywords">
        </div>
        <div class="searchfield">
          <h3>city, st or zip code</h3>
          <input type="text" id="jl-zipcode" name="zip">
        </div>
        <div id="radius-search-div">
          <div class="searchfield">
            <h3>Zipcode Radius</h3>
            <select class="jl-select" id="jl-zip-distance" name="radius">
              <option value="">&nbsp;Radius</option>
              <option value="5">&nbsp;&nbsp; 5 Miles</option>
              <option value="10">&nbsp;10 Miles</option>
              <option value="20">&nbsp;20 Miles</option>
              <option value="30">&nbsp;30 Miles</option>
              <option value="40">&nbsp;40 Miles</option>
              <option value="50">&nbsp;50 Miles</option>
              <option value="75">&nbsp;75 Miles</option>
              <option value="100">100 Miles</option>
            </select>
          </div>
          <input type="image" id="jl-search-submit" class="btn btn-search"
            src="<?php echo get_stylesheet_directory_uri(); ?>/images/icon-search.png" alt="submit">
        </div>
        <div id="btn-filter-container">
          <div class="btn btn-filters" id="jl-filter-1-open">Filter Results</div>
          <div class="btn btn-filters" id="jl-clear-filters">Clear Filters</div>
        </div>
        <div class="filter-container" id="jl-filter-2">
          <h2>Filter Search Results</h2>
          <div class="btn btn-moreFilters" id="jl-filter-2-open">More Filters</div>
          <div class="clear"></div>
          <div class="filter-type">
            <h4>Salary Range</h4>
            <ul>
              <?php jl_get_salary(); ?>
            </ul>
          </div>
          <div class="filter-type">
            <h4>Job Type</h4>
            <ul>
              <?php jl_get_job_type(); ?>
            </ul>
          </div>
          <div class="filter-type">
            <h4>Job Class</h4>
            <ul>
              <?php jl_get_job_class(); ?>
            </ul>
          </div>
          <div class="filter-type-wide">
            <h4>Companies</h4>
            <div class="jl-check-div">
              <ul class="li-block">
                <?php jl_get_companies(); ?>
              </ul>
            </div>
          </div>
        </div><!-- end of filter-container 2-->

        <div class="filter-container" id="jl-filter-3">
          <h2>Advanced Filters</h2>
          <div class="clear"></div>
          <div class="filter-type">
            <h4>Office Type</h4>
            <ul>
              <?php jl_get_office_type(); ?>
            </ul>
          </div>
          <div class="filter-type-wide">
            <h4>Brookdale Key</h4>
            <div class="jl-check-div">
              <ul>
                <?php jl_get_keys();
										; ?>
              </ul>
            </div>
          </div>
          <div class="filter-type">
            <h4>Dept Code</h4>
            <div class="jl-check-div">
              <ul>
                <?php jl_get_dept_code(); ?>
              </ul>
            </div>
          </div>
          <div class="filter-type">
            <h4>State</h4>
            <div class="jl-check-div">
              <ul class="li-block">
                <?php jl_get_states(); ?>
              </ul>
            </div>
          </div>
        </div><!-- end of filter-container 3-->
        <input type="hidden" id="pagenumber" name="pagenumber" value="1">
        <input type="hidden" id="recordperpage" name="recordperpage" value="10">
        <input type="hidden" id="jl-sort" name="jlsort" value="date_desc">
      </form>
      <div id="listing-div">
      </div><!-- listing-div-->
    </div> <!-- end of left -->

    <div id="search-right" class="right <?php echo $search_section_class; ?>">
      <div class="right-container join-container">
        <h2>Career Center</h2>
        <p>Sign in to our Career Center where first time users can create a profile and returning job seekers can
          update your profile and resume, check the status of jobs you’ve applied for and manage email alerts about new
          jobs.
        </p>
        <div class="btn btn-join">Sign In</div>
      </div><!-- end of right-container -->
      <!--<div class="right-container join-container">
					<h2>Join our Talent Network</h2>
					<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam at porttitor sem. Aliquam erat volutpat.</p>
					<div class="btn btn-join">Join Now</div>
				</div>-->
      <!-- end of right-container -->
      <div class="right-container join-container">
        <h2>Application Tips</h2><br />
        <?php include(get_stylesheet_directory().'/jl/jl_application_tips.php'); ?>
      </div><!-- end of right-container -->
    </div><!-- end of right -->
  </div><!-- end of wrapper -->
  <?php } ?>
  <div id="job-detail-div" class="<?php echo $detail_section_class; ?>">
    <?php echo $job_detail; ?>
  </div>
  <div id="jl-modal-layer">
    <div id="jl-msg-box" class="modalContainer">
      <div>
        <p id="jl-msg-text">Some sample text</p>
        <div id="jl-msg-close" class="btn btn-close">Close</div>
      </div>
    </div><!-- jl-msg-box -->
    <div id="jl-map-box" class="modalContainer">
      <div id="jl-map-container">
        <p id="jl-map-data"></p>
        <div id="jl-map-close" class="btn btn-close">Close</div>
      </div>
    </div><!-- jl-map-box -->
    <div id="jl-social-media-box" class="modalContainer">
      <div id="jl-social-media-container">
        <p>Share This Job With: </p>
        <?php 
					if (function_exists('synved_social_share_markup')) {
						echo synved_social_share_markup(array('url' => $social_url_placeholder,
															  'title' => $social_title_placeholder));
				   } else {
						echo '<p>Social Media Feather Plugin Not Found</p>';
				   }?>
        <div id="jl-social-media-close" class="btn btn-close">Close</div>
      </div>
    </div><!-- jl-social-media-box -->
    <div id="jl-apply-now-box" class="modalContainer">
      <div id="jl-apply-now-container">
        <ul id="jl-apply-ul">
          <li>
            <a id="jl-apply-brookdale" class="apply hidden" href=">" target="_blank">
              Apply with Brookdale
            </a>
          </li>
          <li id="indeed-apply-li"></li>
        </ul>
        <div id="jl-apply-now-close" class="btn btn-close">Close</div>
      </div>
      <script id="indeed-apply-js" async="true">
      var loadIndeedButton = function() {
        window.IndeedApply || function(a, b) {
          function c() {
            var c = e.getElementById("indeed-apply-js").attributes["dataindeed-apply-qs"],
              d = "";
            return c && "" != c.value && (d = "&" + c.value), [
              "<body onload=\"var d=document;d.getElementsByTagName('head')[0].appendChild(d.createElement('script')).src='",
              g ? b : a, "&ms=" + +new Date, d, "'\"></body>"
            ].join("")
          }
          var d = window,
            e = document,
            f = document.location.href;
          d.IndeedApply = d.IndeedApply || {};
          var h, d = e.body,
            g = /^https:\/\//.test(f);
          if (!d) return h = arguments.callee, setTimeout(function() {
            h(a, b)
          }, 100);
          var j, f = e.createElement("div"),
            i = e.createElement("iframe");
          f.style.display = "none", d.insertBefore(f, d.firstChild).id = "indeed-apply-iframe-holder", i
            .frameBorder = "0", i.id = "indeed-apply-iframe", i.allowTransparency = "true", f.appendChild(i);
          try {
            i.contentWindow.document.open()
          } catch (k) {
            j = "javascript:var d=document.open();d.domain='" + e.domain + "';", i.src = j + "void(0);"
          }
          try {
            var l = i.contentWindow.document;
            l.write(c()), l.close()
          } catch (m) {
            i.src = j + 'd.write("' + c().replace(/"/g, '\\"') + '");d.close();'
          }
        }("https://apply.indeed.com/indeedapply/env?", "https://apply.indeed.com/indeedapply/env?https=1")
      };
      </script>
    </div><!-- jl-apply-now-box -->
  </div>
</div>

<?php
}

function test_loop() {
	if (have_posts()) :

		do_action('genesis_before_while');
		while (have_posts()) : the_post();

			do_action('genesis_before_entry');

			printf('<article %s>', genesis_attr('entry'));

			do_action('genesis_entry_header');

			do_action('genesis_before_entry_content');

			printf('<div %s>', genesis_attr('entry-content'));
			//do_action( 'genesis_entry_content' );
			job_form();
			do_action('genesis_after_entry_content');

			do_action('genesis_entry_footer');

			echo '</article>';

			do_action('genesis_after_entry');

		endwhile; //* end of one post
		do_action('genesis_after_endwhile');

	else : //* if no posts exist
		do_action('genesis_loop_else');
	endif; //* end loop
}

remove_action('genesis_loop', 'genesis_do_loop');
add_action('genesis_loop', 'test_loop');
genesis();