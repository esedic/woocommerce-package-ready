<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Package ready for shipment Email
 *
 * An email sent to the customer when an order status is changed to Package ready for shipment.
 */
class WC_Email_Package_Ready extends WC_Email {
    /**
     * Constructor
     */
    public function __construct() {
        $this->id             = 'WC_Email_Package_Ready';
        $this->customer_email = true;
        $this->title          = __('Package ready for shipment', 'woocommerce-package-ready');
        $this->description    = __('This email is sent to customers when their order status is changed to Package ready for shipment', 'woocommerce-package-ready');
        
        // Template paths
        $this->template_html  = 'emails/package-ready-email.php';
        $this->template_plain = 'emails/plain/package-ready-email.php';
        
        $this->placeholders   = array(
            '{order_date}'   => '',
            '{order_number}' => '',
        );

        // Call parent constructor
        parent::__construct();

        // Other settings
        $this->recipient = $this->get_option('recipient', '{customer_email}');
        
        // Hook to the status transition specific to our custom status
        add_action('woocommerce_order_status_changed', array($this, 'maybe_trigger_email'), 10, 4);
    }
    
    /**
     * Check if we should trigger the email based on status transition
     */
    public function maybe_trigger_email($order_id, $from_status, $to_status, $order) {
        if ($to_status === 'package-ready') {
            $this->trigger($order_id, $order);
        }
    }

    /**
     * Trigger the sending of this email.
     *
     * @param int            $order_id The order ID.
     * @param WC_Order|false $order Order object.
     */
    public function trigger($order_id, $order = false) {
        $this->setup_locale();

        if ($order_id && !is_a($order, 'WC_Order')) {
            $order = wc_get_order($order_id);
        }

        if (is_a($order, 'WC_Order')) {
            $this->object                         = $order;
            $this->recipient                      = $order->get_billing_email();
            $this->placeholders['{order_date}']   = wc_format_datetime($this->object->get_date_created());
            $this->placeholders['{order_number}'] = $this->object->get_order_number();
        } else {
            return;
        }

        if ($this->is_enabled() && $this->get_recipient()) {
            $this->send($this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments());
        }

        $this->restore_locale();
    }

    /**
     * Get email subject.
     *
     * @return string
     */
    public function get_subject() {
        return $this->format_string($this->get_option('subject', __('Your {site_title} order #{order_number} is ready for shipment', 'woocommerce-package-ready')));
    }

    /**
     * Get email heading.
     *
     * @return string
     */
    public function get_heading() {
        return $this->format_string($this->get_option('heading', __('Your order is ready for shipment', 'woocommerce-package-ready')));
    }

    /**
     * Get content html.
     *
     * @return string
     */
    public function get_content_html() {
        return wc_get_template_html(
            $this->template_html,
            array(
                'order'              => $this->object,
                'email_heading'      => $this->get_heading(),
                'additional_content' => $this->get_additional_content(),
                'sent_to_admin'      => false,
                'plain_text'         => false,
                'email'              => $this,
            )
        );
    }

    /**
     * Get content plain.
     *
     * @return string
     */
    public function get_content_plain() {
        return wc_get_template_html(
            $this->template_plain,
            array(
                'order'              => $this->object,
                'email_heading'      => $this->get_heading(),
                'additional_content' => $this->get_additional_content(),
                'sent_to_admin'      => false,
                'plain_text'         => true,
                'email'              => $this,
            )
        );
    }

    /**
     * Initialize Settings Form Fields
     */
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled'    => array(
                'title'   => __('Enable/Disable', 'woocommerce-package-ready'),
                'type'    => 'checkbox',
                'label'   => __('Enable this email notification', 'woocommerce-package-ready'),
                'default' => 'yes',
            ),
            'subject'    => array(
                'title'       => __('Subject', 'woocommerce-package-ready'),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => sprintf(__('Available placeholders: %s', 'woocommerce-package-ready'), '{site_title}, {order_date}, {order_number}'),
                'placeholder' => $this->get_default_subject(),
                'default'     => '',
            ),
            'heading'    => array(
                'title'       => __('Email Heading', 'woocommerce-package-ready'),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => sprintf(__('Available placeholders: %s', 'woocommerce-package-ready'), '{site_title}, {order_date}, {order_number}'),
                'placeholder' => $this->get_default_heading(),
                'default'     => '',
            ),
            'email_type' => array(
                'title'       => __('Email type', 'woocommerce-package-ready'),
                'type'        => 'select',
                'description' => __('Choose which format of email to send.', 'woocommerce-package-ready'),
                'default'     => 'html',
                'class'       => 'email_type wc-enhanced-select',
                'options'     => $this->get_email_type_options(),
                'desc_tip'    => true,
            ),
        );
    }
}