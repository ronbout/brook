<?php
/*
* Template name: Recruiters
*/

//* Add support for structural wraps for full-width homepage
add_theme_support( 'genesis-structural-wraps', array( 'header','subnav','footer-widgets', 'footer', '' ) );
function my_custom_loop() {
    // code for a completely custom loop
?>
<section class="feature-image">
<?php the_post_thumbnail('full', array('class' => 'feature-image')); ?>
<?php genesis_widget_area( 'featured-image-content', array( 'before' => '<div class="featured-content wrap">', 'after' => '</div>'));?>
</section>

<?php
$args = array(
        'post_type' => 'recruiters_sections',
        'order'     => 'ASC',
        'orderby'   => 'menu_order'
    );
    $loop = new WP_Query($args);
        while($loop->have_posts()) : $loop->the_post();
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