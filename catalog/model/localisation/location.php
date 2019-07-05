<?php
class ModelLocalisationLocation extends Model {
	public function getLocation($location_id) {
		$query = $this->db->query("SELECT location_id, name, address, geocode, telephone, telephone1, telephone2, fax, image, open, comment FROM " . DB_PREFIX . "location WHERE location_id = '" . (int)$location_id . "'");

		return $query->row;
	}
    /* added by it-lab* start */
    public function getLocationDescriptions() {
        $locationt_description_data = array();

        $query = $this->db->query("SELECT  l.location_id AS location_id, ld.address AS address ,ld.open AS open,ld.country AS country, ld.city AS city,ld.district AS district,l.telephone AS telephone,l.image_geo AS image_geo,l.geocode AS geocode,l.is_online AS is_online FROM  " . DB_PREFIX . "location AS l  JOIN ".DB_PREFIX."location_description AS ld ON l.location_id=ld.location_id  WHERE language_id = '" . (int)$this->config->get('config_language_id')."'" . " ORDER BY l.location_order");

        foreach ($query->rows as $result) {
            $locationt_description_data[$result["location_id"]] = $result;
        }

        return $locationt_description_data;
    }
    public function getLocations() {
        $query = $this->db->query("SELECT location_id, name, address, geocode, telephone, telephone1, telephone2, fax, image, open, comment FROM " . DB_PREFIX . "location" . " ORDER BY l.location_order");

        return $query->rows;
    }
    /* added by it-lab* start end */
}