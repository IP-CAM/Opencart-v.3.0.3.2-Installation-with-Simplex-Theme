<?php
class ModelLocalisationLocation extends Model {
	public function addLocation($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "location SET name = '" . $this->db->escape($data['name']) . "', address = '" . $this->db->escape($data['address']) . "', geocode = '" . $this->db->escape($data['geocode']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', telephone1 = '" . $this->db->escape($data['telephone1']).  "', telephone2 = '" . $this->db->escape($data['telephone2']). "' , email = '". $this->db->escape($data['email']) ."',  fax = '" . $this->db->escape($data['fax']) . "', image = '" . $this->db->escape($data['image']) . "', image_geo = '" . $this->db->escape($data['image_geo']) . "',location_order='" . $this->db->escape($data['location_order']) ."', is_online = '" . $this->db->escape($data['is_online']) . "', open = '" . $this->db->escape($data['open']) . "', comment = '" . $this->db->escape($data['comment']) . "'");
        /* added by it-lab* start */
        $location_id = $this->db->getLastId();

        foreach ($data['location_description'] as $language_id => $value) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "location_description SET location_id = '" . (int)$location_id . "', language_id = '" . (int)$language_id . "', address = '" . $this->db->escape($value['address']) . "', open = '" . $this->db->escape($value['open']). "', country = '" . $this->db->escape($value['country']) . "', city = '" . $this->db->escape($value['city']) . "', district = '" . $this->db->escape($value['district'])."'" );
        }
        return $location_id;
        /* added by it-lab* start end */
		return $this->db->getLastId();
	}

	public function editLocation($location_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "location SET name = '" . $this->db->escape($data['name']) . "', address = '" . $this->db->escape($data['address']) . "', geocode = '" . $this->db->escape($data['geocode']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', telephone1 = '" . $this->db->escape($data['telephone1']).  "', telephone2 = '" . $this->db->escape($data['telephone2']). "' , email = '". $this->db->escape($data['email']) . "', fax = '" . $this->db->escape($data['fax']) . "', image = '" . $this->db->escape($data['image']) ."', image_geo = '" . $this->db->escape($data['image_geo']) . "',location_order='" . $this->db->escape($data['location_order']) ."', is_online = '" . $this->db->escape($data['is_online']) ."', open = '" . $this->db->escape($data['open']) . "', comment = '" . $this->db->escape($data['comment']) . "'  WHERE location_id = '" . (int)$location_id . "'");
        /* added by it-lab* start */
        $this->db->query("DELETE FROM " . DB_PREFIX . "location_description WHERE location_id = '" . (int)$location_id . "'");

        foreach ($data['location_description'] as $language_id => $value) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "location_description SET location_id = '" . (int)$location_id . "', language_id = '" . (int)$language_id . "', address = '" . $this->db->escape($value['address']) . "', open = '" . $this->db->escape($value['open']). "', country = '" . $this->db->escape($value['country']) . "', city = '" . $this->db->escape($value['city']) . "', district = '" . $this->db->escape($value['district'])."'" );
        }
        /* added by it-lab* start end */
	}

	public function deleteLocation($location_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "location WHERE location_id = " . (int)$location_id);
	}

	public function getLocation($location_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "location WHERE location_id = '" . (int)$location_id . "'");

		return $query->row;
	}

	public function getLocations($data = array()) {
		$sql = "SELECT location_id, name, address, location_order FROM " . DB_PREFIX . "location";

		$sort_data = array(
			'name',
			'address',
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY name";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getTotalLocations() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "location");

		return $query->row['total'];
	}
    /* added by it-lab* start */
    public function getLocationDescriptions($location_id) {
        $location_description_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "location_description WHERE location_id = '" . (int)$location_id . "'");

        foreach ($query->rows as $result) {
            $location_description_data[$result['language_id']] = array(
                'address'          => $result['address'],
                'country'          => $result['country'],
                'city'             => $result['city'],
                'district'         => $result['district'],
                'open'             => $result['open']
            );
        }

        return $location_description_data;
    }

    public function getLocationDescription($location_id) {
        $location_description_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "location_description WHERE location_id = '" . (int)$location_id . "' AND language_id = '" . (int)$this->config->get('config_language_id') ."' LIMIT 1");

        foreach ($query->rows as $result) {
            $location_description_data = array(
                'address'          => $result['address'],
                'country'          => $result['country'],
                'city'             => $result['city'],
                'district'         => $result['district'],
                'open'             => $result['open']
            );
            break;
        }

        return $location_description_data;
    }

    /* added by it-lab* start end */
}
