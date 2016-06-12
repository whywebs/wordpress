<?php

if ( ! class_exists('umShortcodesController') ) :
class umShortcodesController {
    
    function __construct() {
        global $userMeta;
        
        add_shortcode( 'user-meta',                 array( $this, 'init' ) );
        
        add_shortcode( 'user-meta-login',           array( $this, 'loginShortcode' ) );
        add_shortcode( 'user-meta-profile',         array( $this, 'profileShortcode' ) );
        add_shortcode( 'user-meta-registration',    array( $this, 'registrationShortcode' ) ); 
        add_shortcode( 'user-meta-field',           array( $this, 'fieldShortcode' ) );   
        add_shortcode( 'user-meta-field-value',     array( $this, 'fieldValueShortcode' ) );
        
        //add_action( 'media_buttons_context',        array( $this, 'addUmButton' ) );
        //add_action( 'admin_footer',                 array( $this, 'shortcodeGeneratorPopup' ) );
    }


    function init( $atts ) {
        global $userMeta;
        
        extract( shortcode_atts( array(
            'type'      => 'profile', // profile, registration, profile-registration, public, field-value
            'form'      => null,
            'diff'      => null,
            'id'        => null, // Field ID or meta_key for field-value
            'key'       => null,
    	), $atts, 'user-meta' ) );
                
        
        $actionType = strtolower( $type );
        
        // Replace "both" to "profile-registration" and "none" to "public"
        $actionType = str_replace( array( 'both', 'none' ), array( 'profile-registration', 'public' ), $actionType );
        
        if ( $actionType == 'login' ) :
            return $userMeta->userLoginProcess( $form ); 
        
        elseif ( $actionType == 'field' ) :
            return $this->fieldShortcode( array(
                'id'    => $id
            ) );
        
        elseif ( $actionType == 'field-value' ) :
            return $this->fieldValueShortcode( array(
                'id'    => $id,
                'key'   => $key
            ) );
        
        elseif ( $actionType == 'reset-password' ) :
            return $this->resetPasswordShortcode();
        
        elseif ( $actionType == 'email-verification' ) :
            return $this->emailVerificationShortcode();
        
        else :
            return $userMeta->userUpdateRegisterProcess( $actionType, $form, $diff ); 
        
        endif;
    }   
    
    
    function loginShortcode( $atts ) {
        global $userMeta;
        
        if ( ! $userMeta->isPro() )
            return self::getProError();
        
        extract( shortcode_atts( array(
            'form'      => null,
    	), $atts ) );
        
        return $userMeta->userLoginProcess( $form ); 
    }
    
    
    function profileShortcode( $atts ) {
        global $userMeta;
        
        extract( shortcode_atts( array(
            'form'      => null,
            'diff'      => null,
    	), $atts ) );
        
        return $userMeta->userUpdateRegisterProcess( 'profile', $form, $diff ); 
    }
    
    
    function registrationShortcode( $atts ) {
        global $userMeta;
        
        extract( shortcode_atts( array(
            'form'      => null,
    	), $atts ) );
        
        return $userMeta->userUpdateRegisterProcess( 'registration', $form ); 
    }
    
    
    function fieldShortcode( $atts ) {
        global $userMeta;
        
        extract( shortcode_atts( array(
            'id'        => null,
    	), $atts ) );
        
        if ( ! $userMeta->isPro() )
            return self::getProError();                                 

        $userMeta->enqueueScripts( array( 
            'user-meta',           
            'jquery-ui-all',
            'fileuploader',
            'wysiwyg',
            'jquery-ui-datepicker',
            'jquery-ui-slider',
            'timepicker',
            'validationEngine',
            'password_strength',
            'placeholder',
            'multiple-select'
        ) );                      
        $userMeta->runLocalization(); 
        
        $umField = new umField( $id );
        
        return $umField->generateField();
    }
    
    
    function fieldValueShortcode( $atts ) {
        global $userMeta;

        extract( shortcode_atts( array(
            'id'        => null,
            'key'       => null,
        ), $atts ) );

        if ( ! $userMeta->isPro() )
            return self::getProError();                                

        if ( empty( $id ) && empty( $key ) )
            return $userMeta->showError( 'Please provide field id or meta_key.', 'info', false );

        $umField = new umField( $id );

        return $umField->displayValue( $key );
    }
    
    
    function resetPasswordShortcode() {
        global $userMeta;
                
        if ( ! $userMeta->isPro() )
            return self::getProError();                                 

        $userMeta->enqueueScripts( array(
            'user-meta',           
            'validationEngine',
            'password_strength',
        ) );                      
        $userMeta->runLocalization();
        
        $action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : '';
        switch ( $action ) {
            case 'resetpass' :
            case 'rp' :
                return $userMeta->resetPassword();
            break; 

            default:
                $config = $userMeta->getExecutionPageConfig( 'lostpassword' );
                $config[ 'only_lost_pass_form' ] = true;
                return $userMeta->lostPasswordForm( $config ); 
            break;
        }
    }
    
    
    function emailVerificationShortcode() {
       global $userMeta;
                
        if ( ! $userMeta->isPro() )
            return self::getProError();                                   

        $userMeta->enqueueScripts( array(
            'user-meta',           
            'validationEngine',
            'password_strength',
        ) );                      
        $userMeta->runLocalization();
          
        $action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : '';
        switch ( $action ) {
            case 'email_verification' :
            case 'ev' :
                return $userMeta->emailVerification();
            break;
        }
    }
    
    
    function addUmButton( $context ) {
        global $userMeta, $pagenow;
        
        if ( ! in_array( $pagenow, array( 'post.php', 'post-new.php' ) ) )
            return $context;
        
        if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) )
             return $context;

        $img = $userMeta->assetsUrl . 'images/ump-icon.png';

        $container_id = 'um_shortcode_popup';

        $title = __( 'Add User Meta Shortcode', $userMeta->name );

        $context .= "<a class='thickbox' title='{$title}'
        href='#TB_inline?width=600&height=600&inlineId={$container_id}'>
        <img src='{$img}' /></a>";

        return $context;        
    }
    
    
    function shortcodeGeneratorPopup() {
        global $userMeta, $pagenow;
        
        if ( ! in_array( $pagenow, array( 'post.php', 'post-new.php' ) ) )
            return;
        
        if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) )
             return;
        
         $userMeta->enqueueScripts( array( 
            'user-meta',
            'user-meta-admin',
        ) );                      
        $userMeta->runLocalization();         
        
        $actionTypes = $userMeta->validActionType(); 
        array_unshift( $actionTypes, null ); 
        
        $formsList = $userMeta->getFormsName();
        array_unshift( $formsList, null );       
        
        $userMeta->render( 'shortcodePopup', array(
            'actionTypes'   => $actionTypes,
            'formsList'     => $formsList,
            'roles'         => $userMeta->getRoleList(),
        ) );
    }
    
    
    function getProError() {
        global $userMeta;
        return '<div style="color:red">This shortcode is only supported on pro version. Get ' . $userMeta->getProLink( 'User Meta Pro' ) . '</div>';
    }
    
}
endif;
