<?php

if (!function_exists('kopa_get_socials')) {

    function kopa_get_socials() {
        $kopa_socials = array(
            array(
                'title' => 'Facebook',
                'id' => 'facebook',
                'display'=>'false'
            ),
            array(
                'title' => 'Twitter',
                'id' => 'twitter',
                'display'=>'false'
            ),
            array(
                'title' => 'Instagram',
                'id' => 'instagram',
                'display'=>'false'
            ),
            array(
                'title' => 'Youtube',
                'id' => 'youtube',
                'display'=>'false'
            ),
            array(
                'title' => 'Rss',
                'id' => 'rss',
                'display'=>'false'
            ),
            array(
                'title' => 'Google plus',
                'id' => 'google-plus',
                'display'=>'false'
            )
        );

        return apply_filters('new_lotus_custom_socials', $kopa_socials);
    }
}

if ( ! function_exists('kopa_social_links') ) {
    function kopa_social_links() {
        $social_links = kopa_get_socials();
        $assing_socials = array();
        if ( $social_links ) {
            foreach ( $social_links as $k => $v ) {
                $value_theme_mod = get_theme_mod($v['id'].'_url');
                if ( ! empty($value_theme_mod) ) {
                    $assing_socials[] = $v;
                }
            }
        }

        if ( $assing_socials ) {
            echo '<ul class="list-unstyled">';
                foreach ( $assing_socials as $social ) {
                    if ( 'rss' == $social['id'] ) {
                        $social_url =  get_bloginfo('rss2_url');
                    } else {
                        $social_url = get_theme_mod($social['id'] . '_url','');
                    }
                    echo sprintf(' <li><a href="%s" class="fa fa-%s" target="_blank" rel="nofollow" title="%s"></a></li>', esc_url($social_url), $social['id'], esc_attr__('Follow us via ', 'new-lotus-toolkit') . esc_attr($social['title']));
                }
            echo '</ul>';
        }
    }
}
