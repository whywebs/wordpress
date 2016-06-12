<?php

add_action( 'widgets_init', array('Fastnews_Light_Widget_Flexslider', 'register_widget'));

class Fastnews_Light_Widget_Flexslider extends Kopa_Widget{

    public static function register_widget(){
        register_widget('Fastnews_Light_Widget_Flexslider');
    }

    public function __construct() {
        $this->widget_cssclass = 'kp-slider-widget';
        $this->widget_description = esc_attr__('A Posts Slider Widget', 'fastnews-light-toolkit');
        $this->widget_id = 'fastnews-light-flexslider';
        $this->widget_name = esc_attr__('(Fastnews light) Flexslider', 'fastnews-light-toolkit');

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
            'animation' => array(
                'type' => 'select',
                'std' => 'slide',
                'label' => esc_attr__('Animation', 'fastnews-light-toolkit'),
                'options' => array(
                    'slide' => esc_attr__('Slide', 'fastnews-light-toolkit'),
                    'fade' => esc_attr__('Fade', 'fastnews-light-toolkit'),
                ),
            ),
            'direction' => array(
                'type' => 'select',
                'std' => 'slide',
                'label' => esc_attr__('Direction', 'fastnews-light-toolkit'),
                'options' => array(
                    'horizontal' => esc_attr__('Horizontal', 'fastnews-light-toolkit'),
                    'vertical' => esc_attr__('Vertical', 'fastnews-light-toolkit'),
                ),
            ),
            'slideshow_speed' => array(
                'type' => 'number',
                'std' => '7000',
                'label' => esc_attr__('Speed of the slideshow cycling', 'fastnews-light-toolkit'),
                'min' => '1000',
            ),
            'animation_speed' => array(
                'type' => 'number',
                'std' => '600',
                'label' => esc_attr__('Speed of animations', 'fastnews-light-toolkit'),
                'min' => '10',
            ),
            'is_auto_play' => array(
                'type' => 'checkbox',
                'std' => '1',
                'label' => esc_attr__('Auto Play', 'fastnews-light-toolkit'),
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
        );
        parent::__construct();
    }

    function widget($args, $instance){
        ob_start();
        extract($args);

        $title = apply_filters('widget_title', $instance['title'], $instance, $this->id_base);
        $query_args_new = kopa_build_query($instance);

        $posts = new WP_Query($query_args_new);
        echo $before_widget;

        if ( ! empty( $title ) ) {
            echo $before_title . $title . $after_title;
        }

        if ( $posts->have_posts() ) {
            while ( $posts->have_posts() ) {
                $posts->the_post(); ?>

            <div class="widget kp-article-list-widget clearfix">
                <article class="entry-item clearfix">
                    <?php if(has_post_thumbnail()){ ?>
                    <div class="entry-thumb">
                        <a href="<?php the_permalink(); ?>"><img src="<?php echo kopa_get_image_src(get_the_ID(), 'article-list-image-size') ?>" alt="<?php echo get_the_title(); ?>"></a>
                    </div>
                    <?php } ?>
                    <div class="entry-content">
                        <h4 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
                        <span class="entry-date">&mdash; <?php the_time( get_option( 'date_format' ) ); ?></span>
                        <?php the_excerpt(); ?>
                    </div>
                </article>
            </div>

            <?php }
        }

        wp_reset_postdata();
        echo $after_widget;
        $content = ob_get_clean();
        echo $content;
    }

}