<?php
class ControllerPaymentBizumpayment extends Controller {
    private $error = array();

    public function index() {
        $this->language->load('payment/bizumpayment');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('bizumpayment', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
        }

        $data['heading_title'] = $this->language->get('heading_title');

        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_all_zones'] = $this->language->get('text_all_zones');

        $data['entry_bank'] = $this->language->get('entry_bank');
        $data['entry_total'] = $this->language->get('entry_total');
        $data['entry_order_status'] = $this->language->get('entry_order_status');
        $data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_sort_order'] = $this->language->get('entry_sort_order');

        $data['help_total'] = $this->language->get('help_total');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_payment'),
            'href' => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );
              $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('payment/bizumpayment', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        $data['action'] = $this->url->link('payment/bizumpayment', 'token=' . $this->session->data['token'], 'SSL');

        $data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

        if (isset($this->request->post['bizumpayment_total'])) {
            $data['bizumpayment_total'] = $this->request->post['bizumpayment_total'];
        } else {
            $data['bizumpayment_total'] = $this->config->get('bizumpayment_total');
        }

        if (isset($this->request->post['bizumpayment_order_status_id'])) {
            $data['bizumpayment_order_status_id'] = $this->request->post['bizumpayment_order_status_id'];
        } else {
            $data['bizumpayment_order_status_id'] = $this->config->get('bizumpayment_order_status_id');
        }

        $this->load->model('localisation/order_status');

        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        if (isset($this->request->post['bizumpayment_geo_zone_id'])) {
            $data['bizumpayment_geo_zone_id'] = $this->request->post['bizumpayment_geo_zone_id'];
        } else {
            $data['bizumpayment_geo_zone_id'] = $this->config->get('bizumpayment_geo_zone_id');
        }

        $this->load->model('localisation/geo_zone');

        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        if (isset($this->request->post['bizumpayment_status'])) {
            $data['bizumpayment_status'] = $this->request->post['bizumpayment_status'];
        } else {
            $data['bizumpayment_status'] = $this->config->get('bizumpayment_status');
        }

        if (isset($this->request->post['bizumpayment_sort_order'])) {
            $data['bizumpayment_sort_order'] = $this->request->post['bizumpayment_sort_order'];
        } else {
            $data['bizumpayment_sort_order'] = $this->config->get('bizumpayment_sort_order');
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller
('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('payment/bizumpayment.tpl', $data));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'payment/bizumpayment')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }
}
