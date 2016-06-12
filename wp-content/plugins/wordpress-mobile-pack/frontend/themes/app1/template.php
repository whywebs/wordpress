<?php

$app_settings = WMobilePack_Application::load_app_settings();

$export_path = plugins_url()."/".WMP_DOMAIN."/export/";

$frontend_path = plugins_url()."/".WMP_DOMAIN."/frontend/";
$theme_path = $frontend_path."themes/app".$app_settings['theme']."/";

// check fonts
$loaded_fonts = array(
    $app_settings['font_headlines'],
    $app_settings['font_subtitles'],
    $app_settings['font_paragraphs'],
);

$loaded_fonts = array_unique($loaded_fonts);

// check if locale file exists
$texts_json_exists = WMobilePack_Application::check_language_file(get_locale());

if ($texts_json_exists === false) {
    echo "ERROR, unable to load language file. Please check the '".WMP_DOMAIN."/frontend/locales/' folder.";
}

?>
<!DOCTYPE HTML>
<html manifest="" <?php language_attributes(); ?>>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-touch-fullscreen" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black" />
    <link rel="apple-touch-icon-precomposed" href="" />
    <meta name="mobile-web-app-capable" content="yes" />
    <link rel="manifest" href="<?php echo $export_path."content.php?content=androidmanifest";?>" />

    <?php if ($app_settings['icon'] != ''): // icon path for Firefox ?>
        <link rel="shortcut icon" href="<?php echo $app_settings['icon'];?>"/>
    <?php endif;?>

    <title><?php echo get_bloginfo("name");?></title>
    <style type="text/css">
        /**
        * Example of an initial loading indicator.
        * It is recommended to keep this as minimal as possible to provide instant feedback
        * while other resources are still being loaded for the first time
        */
        html, body {
            height: 100%;
            width: 100%;
            margin: 0;
            padding: 0;
            background-color: #e5e8e3;
        }

        #appLoadingIndicator {
            position: absolute;
            top: 50%;
            margin-top: -8px;
            text-align: center;
            width: 100%;
            height: 16px;
            -webkit-animation-name: appLoadingIndicator;
            -webkit-animation-duration: 0.5s;
            -webkit-animation-iteration-count: infinite;
            -webkit-animation-direction: linear;
            animation-name: appLoadingIndicator;
            animation-duration: 0.5s;
            animation-iteration-count: infinite;
            animation-direction: linear;
        }

        #appLoadingIndicator > * {
            background-color: #c6cdbe;
            display: inline-block;
            height: 16px;
            width: 16px;
            -webkit-border-radius: 8px;
            -moz-border-radius: 8px;
            border-radius: 8px;
            margin: 0 2px;
            opacity: 0.8;
        }

        @-webkit-keyframes appLoadingIndicator{
            0% {
                opacity: 0.8
            }
            50% {
                opacity: 0
            }
            100% {
                opacity: 0.8
            }
        }

        @keyframes appLoadingIndicator{
            0% {
                opacity: 0.8
            }
            50% {
                opacity: 0
            }
            100% {
                opacity: 0.8
            }
        }
    </style>

    <script type="text/javascript" pagespeed_no_defer="">
        var appticles = {
            exportPath: "<?php echo $export_path;?>",
            creditsPath: "<?php echo $theme_path."others/credits.json";?>",
            <?php if ($app_settings['display_website_link']):?>
                websiteUrl: '<?php echo home_url(); echo parse_url(home_url(), PHP_URL_QUERY) ? '&' : '?'; echo WMobilePack_Cookie::$prefix; ?>theme_mode=desktop',
            <?php endif;?>

            logo: "<?php echo $app_settings['logo'];?>",
            icon: "<?php echo $app_settings['icon'];?>",
            defaultCover: "<?php echo $app_settings['cover'] != '' ? $app_settings['cover'] : $frontend_path."images/pattern-".rand(1, 6).".jpg";;?>",
            userCover: <?php echo intval($app_settings['cover'] != '');?>,
            hasFacebook: 0,
            hasTwitter: 0,
            hasGoogle: 0,
            commentsToken: "<?php echo $app_settings['comments_token'];?>",
            articlesPerCard: <?php if ($app_settings['posts_per_page'] == 'single') echo 1; elseif ($app_settings['posts_per_page'] == 'double') echo 2; else echo '"auto"' ;?>
        }
    </script>

    <!-- core -->
    <?php if ($app_settings['theme_timestamp'] != ''):?>
        <link rel="stylesheet" href="<?php echo WMP_FILES_UPLOADS_URL.'theme-'.$app_settings['theme_timestamp'].'.css';?>" type="text/css" />
    <?php else: ?>
        <link rel="stylesheet" href="<?php echo $theme_path;?>css/colors-<?php echo $app_settings['color_scheme'];?>-fonts-<?php echo $app_settings['font_headlines'];?>.css?date=20151210" type="text/css" />
    <?php endif;?>

    <!-- custom fonts -->
    <?php foreach ($loaded_fonts as $font_no):?>
        <link rel="stylesheet" href="<?php echo $frontend_path."fonts/font-".$font_no.".css?date=20151207";?>" type="text/css">
    <?php endforeach;?>

    <script src="<?php echo $export_path.'content.php?content=apptexts&locale='.get_locale();?>" type="text/javascript"></script>
    <script src="<?php echo $theme_path;?>js/app.js?date=20150919" type="text/javascript"></script>

    <?php
        // check if google analytics id was set
        if ($app_settings['google_analytics_id'] != ''):
    ?>

        <script type="text/javascript" pagespeed_no_defer="">

            var _gaq = _gaq || [];
            _gaq.push(['_setAccount', '<?php echo $app_settings['google_analytics_id'];?>']);
            _gaq.push(['_trackPageview']);

            (function() {
                var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
                ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
                var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
            })();

        </script>

    <?php endif;?>

</head>
<body>
<div id="appLoadingIndicator">
    <div></div>
    <div></div>
    <div></div>
</div>
</body>
</html>
