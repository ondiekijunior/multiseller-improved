<?php
final class MsSetting extends Model {
	private $_settings = array(
        'slr_full_name' => '',
        'slr_address_line1' => '',
        'slr_address_line2' => '',
        'slr_city' => '',
        'slr_state' => '',
        'slr_zip' => '',
        'slr_country' => 0,
        'slr_company' => '',
        'slr_website' => '',
		'slr_phone' => '',
        'slr_logo' => '',
	);

	public function getDefaults() {
		return $this->_settings;
	}
	public function getSettings($data = array()) {
		$sql = "SELECT
					name,
					value,
					is_encoded
				FROM `" . DB_PREFIX . "ms_setting` mset
				WHERE 1 = 1 "
				. (isset($data['seller_id']) ? " AND seller_id =  " .  (int)$data['seller_id'] : '')
				. (isset($data['seller_group_id']) ? " AND seller_group_id =  " .  (int)$data['seller_group_id'] : '')

				. (isset($data['name']) ? " AND name = " . $this->db->escape('name') : '');

		$res = $this->db->query($sql);

		$settings = array();

		foreach ($res->rows as $result) {
            if (!$result['is_encoded']) {
                $settings[$result['name']] = $result['value'];
            } else {
                $setting[$result['name']] = json_decode($result['value'], true);
            }
		}

        return $settings;
	}

	public function createSetting($data = array()) {
        foreach ($data['settings'] as $name => $value) {
            $value = is_array($value) ? json_encode($value) : $this->db->escape($value);
            $sql = "INSERT INTO " . DB_PREFIX . "ms_setting
             SET seller_id = " . (isset($data['seller_id']) ? (int)$data['seller_id'] : 'NULL') . ",
                seller_group_id = " . (isset($data['seller_group']) ? (int)$data['seller_group'] : 'NULL') . ",
                name = '" . $this->db->escape($name) . "',
                value = '" . $value . "'
                ON DUPLICATE KEY UPDATE
                name = '" . $this->db->escape($name) . "',
                value = '" . $value . "'";
			$this->db->query($sql);
		}
	}
    
	public function updateSetting($data = array()) {
		foreach ($data['settings'] as $name => $value) {
			$this->db->query("
				UPDATE " . DB_PREFIX . "ms_setting
				SET value = '" . is_array($value) ? $this->db->escape(json_encode($value)) : $this->db->escape($value) . "'
				WHERE name = '" . $this->db->escape($name) . "'"
				. (isset($data['seller_id']) ? " AND seller_id =  " .  (int)$data['seller_id'] : '')
				. (isset($data['seller_group_id']) ? " AND seller_group_id =  " .  (int)$data['seller_group_id'] : '')
			);
		}
	}
}

?>
