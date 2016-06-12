<?php
/**
 * Kopa Framework Term_Meta
 *
 * This module allows you to define custom metabox for built-in or custom taxonomy
 *
 * @author 		Kopatheme
 * @category 	Term Meta
 * @package 	KopaFramework
 * @since       1.0.11
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Kopa_Admin_Term_Meta' ) ) {

/**
 * Kopa_Admin_Term_Meta Class
 */
class Kopa_Admin_Term_Meta {

	/**
	 * @access private
	 * @var array meta boxes settings
	 */
	private $settings = array();

    /**
     *
     * @var string
     * @access protected
     * $since 1.0
     */
    protected $form_type;

	/**
	 * Constructor
	 *
	 * @since 1.0.5
	 * @access public
	 */
	public function __construct( $settings ) {
		$this->settings = $settings;
        $this->add_meta_boxes();
		add_action( 'admin_enqueue_scripts', array( $this, 'meta_box_scripts' ) );
		//add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
	}

    public function meta_box_scripts() {
        wp_enqueue_script( 'kopa_media_uploader' );
        if ( function_exists( 'wp_enqueue_media' ) ) {
            wp_enqueue_media();
        }
    }

    function add_meta_boxes() {
        $metabox = $this->settings;
        foreach ( (array) $metabox['pages'] as $page ) {
            add_action( $page.'_add_form_fields', array( $this, 'output_new_form' ), 10, 2 );
            add_action( $page.'_edit_form_fields',array( $this, 'output_edit_form' ), 10, 2);

            add_action( 'created_' . $page, array($this, 'created_term_meta'), 10, 2 );
            add_action( 'edited_'.$page, array($this, 'edited_term_meta'), 10, 2 );
        }
    }

    public function output_new_form( $taxonomy ) {
        $metabox = $this->settings;
        /* Use nonce for verification */
        echo '<input type="hidden" name="' . esc_attr($metabox['id']) . '_nonce" value="' . wp_create_nonce( $metabox['id'] ) . '">';

        foreach ( $this->settings['fields'] as $value ) {
            if ( ! isset( $value['type'] ) ) continue;

            switch( $value['type'] ) {
                case 'text':
                case 'email':
                case 'number':
                case 'password':
                case 'url':
                if ( $value['type'] != 'password' ) {
                    $value['type'] = 'text';
                }
                ?>
                    <div class="form-field term-group">
                        <label for="<?php echo esc_attr($value['id']); ?>"><?php echo esc_html($value['title']); ?></label>
                        <input type="text" id="<?php echo esc_attr($value['id']); ?>" name="<?php echo esc_attr($value['id']); ?>" value=""/>
                    </div>
                    <?php break;

                case 'textarea':
                    $value = wp_parse_args( $value, array(
                        'rows' => '',
                    ) );
                    ?>
                    <div class="form-field term-group">
                        <label for="<?php echo esc_attr($value['id']); ?>"><?php echo esc_html($value['title']); ?></label>
                        <textarea type="text" id="<?php echo esc_attr($value['id']); ?>" name="<?php echo esc_attr($value['id']); ?>" rows="<?php echo esc_attr( $value['rows'] ); ?>"></textarea>
                    </div>
                    <?php break;
                case 'select':
                case 'multiselect':
                    $value = wp_parse_args( $value, array(
                        'size' => '',
                    ) );
                ?>


                <div class="form-field term-group">
                    <label for="<?php echo esc_attr($value['id']); ?>"><?php echo esc_html($value['title']); ?></label>
                    <select
                        <?php echo ( 'multiselect' === $value['type'] ? ' multiple="multiple"' : '' ); ?>
                        size="<?php echo esc_attr( $value['size']); ?>"
                        name="<?php echo esc_attr($value['id']) . ( 'multiselect' === $value['type'] ? '[]' : '' );?>"
                        id="<?php echo esc_attr( $value['id'] ) . ( 'multiselect' === $value['type'] ? ' multiple="multiple"' : '' ); ?>">

                        <?php
                            foreach ( $value['options'] as $key => $val ) {
                                echo sprintf('<option value="%s">%s</options>', esc_attr( $key ), esc_html( $val ));
                            }
                        ?>

                    </select>

                </div>

                    <?php
                    break;
                case 'checkbox':
                    $value = wp_parse_args( $value, array(
                        'std' => '',
                    ) );
                    ?>

                        <div class="form-field term-group">
                            <label for="<?php echo esc_attr($value['id']); ?>"><?php echo esc_html($value['title']); ?></label>
                            <input
                                type="<?php echo esc_attr( $value['type'] ); ?>"
                                name="<?php echo esc_attr( $value['id'] ); ?>"
                                id="<?php echo esc_attr( $value['id'] ); ?>" value="1"
                                <?php echo ( checked( $value['std'], 1, false ) ); ?>
                            />
                        </div>

                    <?php
                    break;
            }

        }
    }

    public function output_edit_form( $term, $taxonomy ) {
        $metabox = $this->settings;
        /* Use nonce for verification */
        echo '<input type="hidden" name="' . esc_attr($metabox['id']) . '_nonce" value="' . wp_create_nonce( $metabox['id'] ) . '">';

        foreach ( $this->settings['fields'] as $value ) {
            if ( ! isset( $value['type'] ) ) continue;

            $option_value = get_term_meta( $term->term_id, $value['id'], true );

            switch( $value['type'] ) {
                case 'text':
                case 'email':
                case 'number':
                case 'password':
                case 'url':
                if ( $value['type'] != 'password' ) {
                    $value['type'] = 'text';
                }
                ?>

                    <tr class="form-field term-group-wrap">
                        <th scope="row"><label for="<?php echo esc_attr($value['id']); ?>"><?php echo esc_html($value['title']); ?></label></th>
                        <td>
                            <input type="text" id="<?php echo esc_attr($value['id']); ?>" name="<?php echo esc_attr($value['id']); ?>" value="<?php echo esc_attr($option_value);?>" />
                        </td>
                    </tr>

                <?php break;
                case 'textarea':
                    $value = wp_parse_args( $value, array(
                        'rows' => '',
                    ) );
                    ?>

                    <tr class="form-field term-group-wrap">
                        <th scope="row"><label for="<?php echo esc_attr($value['id']); ?>"><?php echo esc_html($value['title']); ?></label></th>
                        <td>
                            <textarea type="text" id="<?php echo esc_attr($value['id']); ?>" name="<?php echo esc_attr($value['id']); ?>" rows="<?php echo esc_attr( $value['rows'] ); ?>"><?php echo esc_textarea( $option_value ); ?></textarea>
                        </td>
                    </tr>

                <?php break;
                case 'select':
                case 'multiselect':
                    $value = wp_parse_args( $value, array(
                        'size' => '',
                    ) );
                    ?>


                    <tr class="form-field term-group-wrap">
                        <th scope="row"><label for="<?php echo esc_attr($value['id']); ?>"><?php echo esc_html($value['title']); ?></label></th>
                        <td>
                            <select
                                <?php echo ( 'multiselect' === $value['type'] ? ' multiple="multiple"' : '' ); ?>
                                size="<?php echo esc_attr( $value['size']); ?>"
                                name="<?php echo esc_attr($value['id']) . ( 'multiselect' === $value['type'] ? '[]' : '' );?>"
                                id="<?php echo esc_attr( $value['id'] ) . ( 'multiselect' === $value['type'] ? ' multiple="multiple"' : '' ); ?>">

                                <?php
                                foreach ( $value['options'] as $key => $val ) {
                                    echo '<option value="'.esc_attr( $key ).'" ';
                                    if ( is_array( $option_value ) ) {
                                        echo selected( in_array( $key, $option_value ), true, false );
                                    } else {
                                        echo selected( $key, $option_value, false );
                                    }
                                    echo '>'.esc_html( $val ).'</option>';
                                }
                                ?>
                            </select>
                        </td>
                    </tr>

                    <?php
                    break;

                case 'checkbox':
                    $value = wp_parse_args( $value, array(
                        'std' => '',
                    ) );
                    ?>

                    <tr class="form-field term-group-wrap">
                        <th scope="row"><label for="<?php echo esc_attr($value['id']); ?>"><?php echo esc_html($value['title']); ?></label></th>
                        <td>
                            <input
                                type="<?php echo esc_attr( $value['type'] ); ?>"
                                name="<?php echo esc_attr( $value['id'] ); ?>"
                                id="<?php echo esc_attr( $value['id'] ); ?>" value="1"
                                <?php echo ( checked( $option_value, 1, false ) ); ?>
                                >
                        </td>
                    </tr>

                    <?php
                    break;

            }

        }
    }

    public function created_term_meta( $term_id, $tt_id ){

        $metabox = $this->settings;

        /* don't save if $_POST is empty */
        if ( empty( $_POST ) )
            return $term_id;

        /* don't save during autosave */
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return $term_id;

        /* verify nonce */
        if ( ! isset( $_POST[ $metabox['id'] . '_nonce'] ) || ! wp_verify_nonce( $_POST[ $metabox['id'] . '_nonce'], $metabox['id'] ) )
            return $term_id;

        foreach ( $this->settings['fields'] as $value ) {
            if( isset( $_POST[$value['id']] ) && '' !== $_POST[$value['id']] ){

                // Get the option name
                $option_value = null;

                if ( isset( $_POST[ $value['id'] ] ) ) {
                    $option_value = $_POST[ $value['id'] ];
                }

                // For a value to be submitted to database it must pass through a sanitization filter
                if ( has_filter( 'kopa_sanitize_option_' . $value['id'] ) ) {
                    $option_value = apply_filters( 'kopa_sanitize_option_' . $value['id'], $option_value, $value );
                }

                if ( ! is_null( $option_value ) ) {
                    add_term_meta( $term_id, $value['id'], $option_value, true );
                }
            }
        }
        return true;
    }

    public function edited_term_meta( $term_id, $tt_id ){
        $metabox = $this->settings;

        /* don't save if $_POST is empty */
        if ( empty( $_POST ) )
            return $term_id;

        /* don't save during autosave */
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return $term_id;

        /* verify nonce */
        if ( ! isset( $_POST[ $metabox['id'] . '_nonce'] ) || ! wp_verify_nonce( $_POST[ $metabox['id'] . '_nonce'], $metabox['id'] ) )
            return $term_id;

        foreach ( $this->settings['fields'] as $value ) {
            // Get the option name
            $option_value = null;

            if ( isset( $_POST[ $value['id'] ] ) ) {
                $option_value = $_POST[ $value['id'] ];
            }

            // For a value to be submitted to database it must pass through a sanitization filter
            if ( has_filter( 'kopa_sanitize_option_' . $value['id'] ) ) {
                $option_value = apply_filters( 'kopa_sanitize_option_' . $value['id'], $option_value, $value );
            }

            if ( ! is_null( $option_value ) ) {
                update_term_meta( $term_id, $value['id'], $option_value );
            }
        }
        return true;
}

}

}