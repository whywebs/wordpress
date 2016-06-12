<?php

class Paypal_Shopping_Cart_Order_Action_Now {

    public function __construct() {
        $this->order_id = intval($_GET['psc_order']);
        $this->order_action = sanitize_text_field($_GET['psc_action']);
        $this->order_processing = 'processing';
        $this->order_onhold = 'on-hold';
        $this->order_completed = 'completed';
        $this->order_cancelled = 'cancelled';
    }

    public function order_change_status($posted = null) {
        if ((isset($this->order_action) && !empty($this->order_action)) && 'view' != $this->order_action) {
            $this->update_postmeta_order_status();
            $this->update_postmeta_order_status_after();
        } else if ((isset($this->order_action) && !empty($this->order_action)) && 'view' == $this->order_action) {
            $this->update_postmeta_order_view($this->order_id);
        }
    }

    public function update_postmeta_order_status() {

        if ((isset($this->order_id) && !empty($this->order_id)) && (isset($this->order_action) && !empty($this->order_action))) {
            update_post_meta($this->order_id, '_order_action_status', $this->order_action);
        }
    }

    public function update_postmeta_order_status_after() {
        $post_url = admin_url('edit.php?post_type=' . 'psc_order');
        wp_redirect($post_url);
    }

    public function update_postmeta_order_view($id) {
        $post_url = admin_url('post.php?post=' . $id . '&action=edit');
        wp_redirect($post_url);
    }
}