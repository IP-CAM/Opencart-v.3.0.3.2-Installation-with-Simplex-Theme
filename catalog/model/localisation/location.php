<?php
class ModelLocalisationLocation extends Model {
	public function getLocation($location_id) {
		$query = $this->db->query("SELECT location_id, name, address, geocode, telephone, fax, image, open, comment FROM " . DB_PREFIX . "location WHERE location_id = '" . (int)$location_id . "'");

		return $query->row;
	}
    /* added by it-lab* start */
    public function getLocationDescriptions($location_id) {
        $locationt_description_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "location_description WHERE location__id = '" . (int)$location_id . "'");

        foreach ($query->rows as $result) {
            $locationt_description_data[$result['language_id']] = array(
                'address'          => $result['address'],
                'open'             => $result['open']
            );
        }

        return $locationt_description_data;
    }
    /* added by it-lab* start end */

}