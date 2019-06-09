<?php
/*
* Template name: Custom Homepage
*/

//* Add support for structural wraps for full-width homepage
add_theme_support( 'genesis-structural-wraps', array( 'header','subnav','footer-widgets', 'footer', '' ) );
function my_custom_loop() {
    // code for a completely custom loop
?>

<section style="clear: both;">
<div style="max-width:425px; float:left; padding:20px 20px 50px 60px;">
<p style="margin: 90px 0 50px 0"> Click on the video to hear associates explain what it means to work for Brookdale Senior Living and how they make a difference in the lives of seniors. Then search for jobs to find an opportunity that fits you.</p>

<h2>Let's redefine senior care</h2>
<a class="button-light" title="Search Jobs" href="/brook/jobs/"><i class="fa fa-search-minus"></i> Search Jobs <i class="fa fa-chevron-right"></i></a>

</div>
<div style="">
<iframe width="560" height="315" src="https://www.youtube.com/embed/ueeqvE-Gyqg" frameborder="0" allowfullscreen></iframe>

</div>
</section>

<section class="feature-image">
<?php the_post_thumbnail('full', array('class' => 'feature-image')); ?>
<?php genesis_widget_area( 'featured-image-content', array( 'before' => '<div class="featured-content wrap">', 'after' => '</div>'));?>
</section>

<?php
$args = array(
        'post_type' => 'homepage_section',
        'order'     => 'ASC',
        'orderby'   => 'menu_order'
    );
    $homepage = new WP_Query($args);
        while($homepage->have_posts()) : $homepage->the_post();
             
            // $homepageImage = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'large');
            // $breakUrl = str_replace(home_url(), '', $homepageImage[0]); 
?>

<section class="banner-image">
<?php the_post_thumbnail('full', array('class' => 'banner')); ?>
</section>
            <div id="section_<?php echo the_ID(); ?>" class="row full-width">
                <div class="section-content wrap" role="main">
                    <!-- <h2><?php the_title(); ?></h2> -->
                    <div class="entry-content">
                        <?php the_content(); ?>
                    </div>
                </div>
            </div>

        <?php endwhile;
    wp_reset_postdata();
}

/** Replace the standard loop with our custom loop */
remove_action( 'genesis_loop', 'genesis_do_loop' );
add_action( 'genesis_loop', 'my_custom_loop' );

genesis();