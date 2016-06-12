<?php

add_action( 'widgets_init', array('Fastnews_Light_Widget_Articles_Carousel', 'register_widget'));

class Fastnews_Light_Widget_Articles_Carousel extends Kopa_Widget{

    public static function register_widget(){
        register_widget('Fastnews_Light_Widget_Articles_Carousel');
    }

    public function __construct() {
        $this->widget_cssclass = 'kp-featured-news-widget';
        $this->widget_description = esc_attr__('Display Articles Carousel Widget', 'fastnews-light-toolkit');
        $this->widget_id = 'fastnews-light-posts-carousel';
        $this->widget_name = esc_attr__('(Fastnews light) Articles Carousel', 'fastnews-light-toolkit');

        $all_cats = get_categories();
        $categories = array();
        $categories[''] = esc_attr__('---Select categories---', 'fastnews-light-toolkit');
        foreach ($all_cats as $cat) {
            $categories[$cat->term_id] = $cat->name;
        }

        $all_tags = get_tags();
        $tags = array();
        $tags[''] = esc_attr__('---Select tags---', 'fastnews-light-toolkit');
        foreach ($all_tags as $tag) {
            $tags[$tag->term_id] = $tag->name;
        }

        $this->settings = array(
            'title' => array(
                'type' => 'text',
                'std' => '',
                'label' => esc_attr__('Title', 'fastnews-light-toolkit')
            ),
            'categories' => array(
                'type' => 'multiselect',
                'std' => '',
                'label' => esc_attr__('Categories', 'fastnews-light-toolkit'),
                'options' => $categories,
                'size' => '5',
            ),
            'relation' => array(
                'type' => 'select',
                'label' => esc_attr__('Relation', 'fastnews-light-toolkit'),
                'std' => 'OR',
                'options' => array(
                    'AND' => esc_attr__('AND', 'fastnews-light-toolkit'),
                    'OR' => esc_attr__('OR', 'fastnews-light-toolkit'),
                ),
            ),
            'tags' => array(
                'type' => 'multiselect',
                'std' => '',
                'label' => esc_attr__('Tags', 'fastnews-light-toolkit'),
                'options' => $tags,
                'size' => '5',
            ),
            'orderby' => array(
                'type' => 'select',
                'std' => 'date',
                'label' => esc_attr__('Order by', 'fastnews-light-toolkit'),
                'options' => array(
                    'ID' => esc_attr__('Post id', 'fastnews-light-toolkit'),
                    'title' => esc_attr__('Title', 'fastnews-light-toolkit'),
                    'date' => esc_attr__('Date', 'fastnews-light-toolkit'),
                    'rand' => esc_attr__('Random', 'fastnews-light-toolkit'),
                    'comment_count' => esc_attr__('Number of comments', 'fastnews-light-toolkit'),
                ),
            ),
            'order' => array(
                'type' => 'select',
                'std' => 'DESC',
                'label' => esc_attr__('Ordering', 'fastnews-light-toolkit'),
                'options' => array(
                    'ASC' => esc_attr__('ASC', 'fastnews-light-toolkit'),
                    'DESC' => esc_attr__('DESC', 'fastnews-light-toolkit'),
                ),
            ),
            'number_of_article' => array(
                'type' => 'number',
                'std' => '5',
                'label' => esc_attr__('Number of posts', 'fastnews-light-toolkit'),
                'min' => '1',
            ),
            'limit' => array(
                'type' => 'number',
                'std' => '55',
                'label' => esc_attr__('Exerpt of posts', 'fastnews-light-toolkit'),
                'min' => '1',
            ),
            'kopa_timestamp' => array(
                'type' => 'select',
                'std' => '',
                'label' => esc_attr__('Timestamp (ago)', 'fastnews-light-toolkit'),
                'options' => array(
                    '' => esc_attr__('-- Select --', 'fastnews-light-toolkit'),
                    '-1 week' => esc_attr__('1 week', 'fastnews-light-toolkit'),
                    '-2 week' => esc_attr__('2 weeks', 'fastnews-light-toolkit'),
                    '-3 week' => esc_attr__('3 weeks', 'fastnews-light-toolkit'),
                    '-1 month' => esc_attr__('1 months', 'fastnews-light-toolkit'),
                    '-2 month' => esc_attr__('2 months', 'fastnews-light-toolkit'),
                    '-3 month' => esc_attr__('3 months', 'fastnews-light-toolkit'),
                    '-4 month' => esc_attr__('4 months', 'fastnews-light-toolkit'),
                    '-5 month' => esc_attr__('5 months', 'fastnews-light-toolkit'),
                    '-6 month' => esc_attr__('6 months', 'fastnews-light-toolkit'),
                    '-7 month' => esc_attr__('7 months', 'fastnews-light-toolkit'),
                    '-8 month' => esc_attr__('8 months', 'fastnews-light-toolkit'),
                    '-9 month' => esc_attr__('9 months', 'fastnews-light-toolkit'),
                    '-10 month' => esc_attr__('10 months', 'fastnews-light-toolkit'),
                    '-11 month' => esc_attr__('11 months', 'fastnews-light-toolkit'),
                    '-1 year' => esc_attr__('1 year', 'fastnews-light-toolkit'),
                    '-2 year' => esc_attr__('2 years', 'fastnews-light-toolkit'),
                    '-3 year' => esc_attr__('3 years', 'fastnews-light-toolkit'),
                    '-4 year' => esc_attr__('4 years', 'fastnews-light-toolkit'),
                    '-5 year' => esc_attr__('5 years', 'fastnews-light-toolkit'),
                    '-6 year' => esc_attr__('6 years', 'fastnews-light-toolkit'),
                    '-7 year' => esc_attr__('7 years', 'fastnews-light-toolkit'),
                    '-8 year' => esc_attr__('8 years', 'fastnews-light-toolkit'),
                    '-9 year' => esc_attr__('9 years', 'fastnews-light-toolkit'),
                    '-10 year' => esc_attr__('10 years', 'fastnews-light-toolkit'),
                ),
            ),
            'scroll_items' => array(
                'type' => 'number',
                'std' => '1',
                'label' => esc_attr__('Scroll Items', 'fastnews-light-toolkit'),
                'min' => '1',
            ),
            'columns' => array(
                'type' => 'number',
                'std' => '3',
                'label' => esc_attr__('Number of Columns', 'fastnews-light-toolkit'),
                'min' => '1',
            ),
            'autoplay' => array(
                'type' => 'checkbox',
                'std' => '1',
                'label' => esc_attr__('Auto Play', 'fastnews-light-toolkit')
            ),
            'excerpt' => array(
                'type' => 'checkbox',
                'std' => '1',
                'label' => esc_attr__('Show excerpt', 'fastnews-light-toolkit')
            ),
            'duration' => array(
                'type' => 'number',
                'std' => '500',
                'label' => esc_attr__('Duration of the transition (milliseconds)', 'fastnews-light-toolkit'),
                'min' => '100',
            ),
            'timeout_duration' => array(
                'type' => 'number',
                'std' => '2500',
                'label' => esc_attr__('The amount of milliseconds the carousel will pause', 'fastnews-light-toolkit'),
                'min' => '100',
            ),
        );
        parent::__construct();
    }

    function widget($args, $instance){
        ob_start();
        extract($args);

        $title = apply_filters('widget_title', $instance['title'], $instance, $this->id_base);
        $query_args_new = kopa_build_query($instance);

        $posts = new WP_Query($query_args_new);
        if($instance['excerpt']=='false'){
            echo '<div class="widget kp-full-featured-news-widget">';
        } else{
            echo '<div class="widget kp-featured-news-widget">';
        }
        if ( ! empty( $title ) ) {
            echo $before_title . $title . $after_title;
        }

        if ( $posts->have_posts() ) { ?>

            <div class="list-carousel responsive">
                <ul class="kopa-featured-news-carousel clearfix" data-next-id="#<?php echo $this->get_field_id( 'next-1' ); ?>" data-prev-id="#<?php echo $this->get_field_id( 'prev-1' ); ?>" data-scroll-items="<?php echo isset($instance['scroll_items']) ? $instance['scroll_items'] : 1; ?>" data-columns="<?php echo isset($instance['columns']) ? $instance['columns'] : 3 ; ?>" data-autoplay="<?php echo isset($instance['autoplay']) ? $instance['autoplay'] : 'false' ; ?>" data-duration="<?php echo isset($instance['duration']) ? $instance['duration'] : 500 ; ?>" data-timeout-duration="<?php echo isset($instance['timeout_duration']) ? $instance['timeout_duration'] : 2500 ; ?>">
                    <?php while ( $posts->have_posts() ) {
                    $posts->the_post(); ?>
                    <li style="width: 160px;">
                        <article class="entry-item clearfix">
                            <?php if(has_post_thumbnail()){?>
                            <div class="entry-thumb">
                                <a href="<?php the_permalink(); ?>"><img src="<?php echo kopa_get_image_src(get_the_ID(), 'article-carousel-image-size') ?>" alt="<?php echo get_the_title(); ?>"></a>
                            </div>
                            <?php } ?>
                            <div class="entry-content">
                                <header>
                                    <h4 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
                                    <span class="entry-date"><a href="<?php the_permalink() ?>">&mdash; <?php the_time( get_option( 'date_format' ) ); ?></a></span>
                                </header>
                                <?php the_excerpt(); ?>
                            </div><!--entry-content-->
                        </article><!--entry-item-->
                    </li>
                    <?php } // endwhile ?>
                </ul><!--kopa-featured-news-carousel-->
                <div class="clearfix"></div>
                <div class="carousel-nav clearfix">
                    <a id="<?php echo $this->get_field_id( 'prev-1' ); ?>" class="carousel-prev" href="#" ></a>
                    <a id="<?php echo $this->get_field_id( 'next-1' ); ?>" class="carousel-next" href="#" ></a>
                </div>
            </div>

        <?php
        }


        wp_reset_postdata();
        echo $after_widget;
        $content = ob_get_clean();
        echo $content;
    }

}