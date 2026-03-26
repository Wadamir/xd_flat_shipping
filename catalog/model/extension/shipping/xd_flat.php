<?php
class ModelExtensionShippingXdFlat extends Model
{
    public function getQuote($address)
    {
        $this->load->language('extension/shipping/xd_flat');
        $this->load->model('tool/image');

        if (!$this->config->get('shipping_xd_flat_status')) {
            return array();
        }

        $quote_data = array();
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "xd_flat_rate` WHERE status = '1' ORDER BY sort_order ASC, xd_flat_rate_id ASC");

        foreach ($query->rows as $rate) {
            if (!$this->isRateAvailable($rate, $address)) {
                continue;
            }

            $title = trim($rate['title']) !== '' ? $rate['title'] : $this->language->get('text_description');
            $cost = (float)$rate['cost'];
            $tax_class_id = (int)$rate['tax_class_id'];
            $image = '';

            if (!empty($rate['image']) && is_file(DIR_IMAGE . $rate['image'])) {
                $image = $this->model_tool_image->resize($rate['image'], 32, 32);
            }

            $quote_data[(int)$rate['xd_flat_rate_id']] = array(
                'code' => 'xd_flat.' . (int)$rate['xd_flat_rate_id'],
                'title' => $title,
                'image' => $image,
                'cost' => $cost,
                'tax_class_id' => $tax_class_id,
                'text' => $this->currency->format($this->tax->calculate($cost, $tax_class_id, $this->config->get('config_tax')), $this->session->data['currency'])
            );
        }

        if (!$quote_data) {
            return array();
        }

        return array(
            'code' => 'xd_flat',
            'title' => $this->language->get('text_title'),
            'quote' => $quote_data,
            'sort_order' => $this->config->get('shipping_xd_flat_sort_order'),
            'error' => false
        );
    }

    private function isRateAvailable($rate, $address)
    {
        if ((int)$rate['geo_zone_id'] === 0) {
            return true;
        }

        $query = $this->db->query("SELECT zone_to_geo_zone_id FROM `" . DB_PREFIX . "zone_to_geo_zone` WHERE geo_zone_id = '" . (int)$rate['geo_zone_id'] . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0') LIMIT 1");

        return (bool)$query->num_rows;
    }
}
