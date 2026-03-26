<?php
class ModelExtensionShippingXdFlat extends Model
{
	public function install()
	{
		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "xd_flat_rate` (
			`xd_flat_rate_id` INT(11) NOT NULL AUTO_INCREMENT,
			`title` VARCHAR(255) NOT NULL,
			`cost` DECIMAL(15,4) NOT NULL DEFAULT '0.0000',
			`tax_class_id` INT(11) NOT NULL DEFAULT '0',
			`geo_zone_id` INT(11) NOT NULL DEFAULT '0',
			`sort_order` INT(11) NOT NULL DEFAULT '0',
			`status` TINYINT(1) NOT NULL DEFAULT '1',
			PRIMARY KEY (`xd_flat_rate_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
	}

	public function uninstall()
	{
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "xd_flat_rate`");
	}

	public function getRates()
	{
		if (!$this->tableExists()) {
			return array();
		}

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "xd_flat_rate` ORDER BY sort_order ASC, xd_flat_rate_id ASC");

		return $query->rows;
	}

	public function replaceRates($rates)
	{
		if (!$this->tableExists()) {
			$this->install();
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "xd_flat_rate`");

		foreach ($rates as $rate) {
			$title = isset($rate['title']) ? trim($rate['title']) : '';
			$cost = isset($rate['cost']) ? (float)$rate['cost'] : 0;
			$tax_class_id = isset($rate['tax_class_id']) ? (int)$rate['tax_class_id'] : 0;
			$geo_zone_id = isset($rate['geo_zone_id']) ? (int)$rate['geo_zone_id'] : 0;
			$sort_order = isset($rate['sort_order']) ? (int)$rate['sort_order'] : 0;
			$status = isset($rate['status']) ? (int)$rate['status'] : 0;

			if ($title === '' && $cost == 0.0) {
				continue;
			}

			$this->db->query("INSERT INTO `" . DB_PREFIX . "xd_flat_rate` SET
				title = '" . $this->db->escape($title) . "',
				cost = '" . (float)$cost . "',
				tax_class_id = '" . (int)$tax_class_id . "',
				geo_zone_id = '" . (int)$geo_zone_id . "',
				sort_order = '" . (int)$sort_order . "',
				status = '" . (int)$status . "'");
		}
	}

	private function tableExists()
	{
		$query = $this->db->query("SHOW TABLES LIKE '" . $this->db->escape(DB_PREFIX . "xd_flat_rate") . "'");

		return $query->num_rows > 0;
	}
}
