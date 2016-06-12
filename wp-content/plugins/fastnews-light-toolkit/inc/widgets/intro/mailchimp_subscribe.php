<?php

add_action( 'widgets_init', array('Fastnews_Mailchimp_Subscribe', 'register_widget'));

class Fastnews_Mailchimp_Subscribe extends Kopa_Widget {

    public static function register_widget(){
        register_widget('Fastnews_Mailchimp_Subscribe');
    }

    public function __construct() {
        $this->widget_cssclass = 'kopa-newsletter-widget';
        $this->widget_description = esc_attr__('Display mailchimp newsletter subscription form', 'fastnews-light-toolkit');
        $this->widget_id = 'fastnews_light_widget_mailchimp_subscribe';
        $this->widget_name = esc_attr__('(Fastnews light) Mailchimp Subscribe', 'fastnews-light-toolkit');

        $this->settings = array(
            'title' => array(
                'type' => 'text',
                'std' => esc_attr__('NewsLetter', 'fastnews-light-toolkit'),
                'label' => esc_attr__('Title', 'fastnews-light-toolkit')
            ),
            'mailchimp_form_action' => array(
                'type' => 'text',
                'std' => '',
                'label' => esc_attr__('Mailchimp Form Action', 'fastnews-light-toolkit')
            ),
            'mailchimp_enable_popup' => array(
                'type' => 'checkbox',
                'std' => '',
                'label' => esc_attr__('Enable evil popup mode', 'fastnews-light-toolkit')
            ),
            'description' => array(
                'type' => 'textarea',
                'std' => '',
                'label' => esc_attr__('Description', 'fastnews-light-toolkit')
            ),
            'placeholder' => array(
                'type' => 'text',
                'std' => esc_attr__('Subscribe to newsletter...', 'fastnews-light-toolkit'),
                'label' => esc_attr__('Placeholder', 'fastnews-light-toolkit')
            ),
            'submit_btn' => array(
                'type' => 'text',
                'std' => esc_attr__('Subscribe', 'fastnews-light-toolkit'),
                'label' => esc_attr__('Text submit', 'fastnews-light-toolkit')
            )
        );
        parent::__construct();
    }

    function widget( $args, $instance ) {
        ob_start();
        extract($args);
        extract($instance);

        echo $before_widget;

        if ( ! empty( $title ) ) {
            echo $before_title . $title . $after_title;
        }

        if ( ! empty( $mailchimp_form_action ) ) : ?>

        <form action="<?php echo esc_url( $mailchimp_form_action ); ?>" method="post" class="newsletter-form clearfix" <?php echo $mailchimp_enable_popup ? 'target="_blank"' : ''; ?>>
            <p class="input-email clearfix">
                <input type="text" onfocus="if(this.value==this.defaultValue)this.value='';" onblur="if(this.value=='')this.value=this.defaultValue;" name="EMAIL" value="<?php echo esc_attr($placeholder); ?>" class="email" size="40">
                <input type="submit" value="<?php echo esc_attr($submit_btn); ?>" class="submit">
            </p>
        </form>
        <p><?php echo $description; ?></p>

        <?php
        endif;
        echo $after_widget;
        $content = ob_get_clean();
        echo $content;
    }
}