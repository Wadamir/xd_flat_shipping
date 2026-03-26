<?php
class ControllerExtensionShippingXdFlat extends Controller
{
    private $error = array();

    public function index()
    {
        $this->load->language('extension/shipping/xd_flat');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');
        $this->load->model('extension/shipping/xd_flat');
        $this->load->model('tool/image');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('shipping_xd_flat', $this->request->post);

            $rates = isset($this->request->post['shipping_xd_flat_rate']) ? $this->request->post['shipping_xd_flat_rate'] : array();
            $this->model_extension_shipping_xd_flat->replaceRates($rates);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true));
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/shipping/xd_flat', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('extension/shipping/xd_flat', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true);

        if (isset($this->request->post['shipping_xd_flat_status'])) {
            $data['shipping_xd_flat_status'] = $this->request->post['shipping_xd_flat_status'];
        } else {
            $data['shipping_xd_flat_status'] = $this->config->get('shipping_xd_flat_status');
        }

        if (isset($this->request->post['shipping_xd_flat_sort_order'])) {
            $data['shipping_xd_flat_sort_order'] = $this->request->post['shipping_xd_flat_sort_order'];
        } else {
            $data['shipping_xd_flat_sort_order'] = $this->config->get('shipping_xd_flat_sort_order');
        }

        if (isset($this->request->post['shipping_xd_flat_icon_width'])) {
            $data['shipping_xd_flat_icon_width'] = (int)$this->request->post['shipping_xd_flat_icon_width'];
        } else {
            $data['shipping_xd_flat_icon_width'] = (int)$this->config->get('shipping_xd_flat_icon_width') ?: 32;
        }

        if (isset($this->request->post['shipping_xd_flat_icon_height'])) {
            $data['shipping_xd_flat_icon_height'] = (int)$this->request->post['shipping_xd_flat_icon_height'];
        } else {
            $data['shipping_xd_flat_icon_height'] = (int)$this->config->get('shipping_xd_flat_icon_height') ?: 32;
        }

        $this->load->model('localisation/tax_class');
        $data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

        $this->load->model('localisation/geo_zone');
        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        if (isset($this->request->post['shipping_xd_flat_rate'])) {
            $data['shipping_xd_flat_rate'] = $this->request->post['shipping_xd_flat_rate'];
        } else {
            $data['shipping_xd_flat_rate'] = $this->model_extension_shipping_xd_flat->getRates();
        }

        foreach ($data['shipping_xd_flat_rate'] as &$rate) {
            $rate['image'] = isset($rate['image']) ? $rate['image'] : '';

            if ($rate['image'] && is_file(DIR_IMAGE . $rate['image'])) {
                $rate['thumb'] = $this->model_tool_image->resize($rate['image'], 40, 40);
            } else {
                $rate['thumb'] = $this->model_tool_image->resize('no_image.png', 40, 40);
            }
        }
        unset($rate);

        $data['placeholder'] = $this->model_tool_image->resize('no_image.png', 40, 40);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/shipping/xd_flat', $data));
    }

    public function install()
    {
        $this->load->model('extension/shipping/xd_flat');
        $this->model_extension_shipping_xd_flat->install();
    }

    public function uninstall()
    {
        $this->load->model('extension/shipping/xd_flat');
        $this->model_extension_shipping_xd_flat->uninstall();
    }

    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/shipping/xd_flat')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }
}
