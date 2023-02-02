<?php
class ControllerPaymentBizumpayment extends Controller {
    public function index() {
        $this->language->load('payment/bizumpayment');

        $data['text_instruction'] = $this->language->get('text_instruction');
        $data['text_description'] = $this->language->get('text_description');
        $data['text_payment'] = $this->language->get('text_payment');

        $data['button_confirm'] = $this->language->get('button_confirm');

        $data['bizum_number'] = $this->config->get('bizumpayment_number');

        $data['continue'] = $this->url->link('checkout/success');

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/bizumpayment.tpl')) {
            return $this->load->view($this->config->get('config_template') . '/template/payment/bizumpayment.tpl', $data);
        } else {
            return $this->load->view('default/template/payment/bizumpayment.tpl', $data);
        }
    }

    public function confirm() {
        $this->language->load('payment/bizumpayment');

        $this->load->model('checkout/order');

        $comment = $this->language->get('text_instruction') . "\n\n";
        $comment .= $this->config->get('bizumpayment_number') . "\n\n";
        $comment .= $this->language->get('text_payment');

        $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('bizumpayment_order_status_id'), $comment, true);
    }
}
