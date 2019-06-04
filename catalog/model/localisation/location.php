<?php
class ModelLocalisationLocation extends Model {
	public function getLocation($location_id) {
		$query = $this->db->query("SELECT location_id, name, address, geocode, telephone, fax, image, open, comment FROM " . DB_PREFIX . "location WHERE location_id = '" . (int)$location_id . "'");

		return $query->row;
	}
    /* added by it-lab* start */
    public function getLocationDescriptions() {
        $locationt_description_data = array();

        $query = $this->db->query("SELECT  l.location_id AS location_id, ld.address AS address ,ld.open AS open FROM  " . DB_PREFIX . "location AS l  JOIN ".DB_PREFIX."location_description AS ld ON l.location_id=ld.location_id  WHERE language_id = '" . (int)$this->config->get('config_language_id')."'");

        foreach ($query->rows as $result) {
            $locationt_description_data[] = $result;
        }

        return $locationt_description_data;
    }
    /* added by it-lab* start end */
}