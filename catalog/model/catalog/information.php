<?php
class ModelCatalogInformation extends Model {
	public function getInformation($information_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "information i LEFT JOIN " . DB_PREFIX . "information_description id ON (i.information_id = id.information_id) LEFT JOIN " . DB_PREFIX . "information_to_store i2s ON (i.information_id = i2s.information_id) WHERE i.information_id = '" . (int)$information_id . "' AND id.language_id = '" . (int)$this->config->get('config_language_id') . "' AND i2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND i.status = '1'");

		return $query->row;
	}
    public function getInformationFull($information_id) {
        $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "information i LEFT JOIN " . DB_PREFIX . "information_description id ON (i.information_id = id.information_id) LEFT JOIN " . DB_PREFIX . "information_to_store i2s ON (i.information_id = i2s.information_id) WHERE i.information_id = '" . (int)$information_id . "' AND id.language_id = '" . (int)$this->config->get('config_language_id') . "' AND i2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND i.status = '1'");

        return $query->row;
    }

	public function getInformations() {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "information i LEFT JOIN " . DB_PREFIX . "information_description id ON (i.information_id = id.information_id) LEFT JOIN " . DB_PREFIX . "information_to_store i2s ON (i.information_id = i2s.information_id) WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "' AND i2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND i.status = '1' ORDER BY i.sort_order, LCASE(id.title) ASC");

		return $query->rows;
	}

	public function getInformationLayoutId($information_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "information_to_layout WHERE information_id = '" . (int)$information_id . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "'");

		if ($query->num_rows) {
			return (int)$query->row['layout_id'];
		} else {
			return 0;
		}
	}
    /* added by it-lab start */

    public function getCategories($information_id) {
        $query = $this->db->query("SELECT category_id FROM " . DB_PREFIX . "information_to_category WHERE information_id = '" . (int)$information_id  ."'");

        if ($query->rows) {
            return $query->rows;
        } else {
            return '';
        }
    }

    public function getFotterTitle(){
        return $this->getFooterTitle();
    }
    public function getFooterTitle(){
        $data=array();
        $query =$this->db->query("SELECT ftd.title,ft.footertitle_id FROM " . DB_PREFIX . "footertitle ft LEFT JOIN " . DB_PREFIX . "footertitle_description ftd ON (ft.footertitle_id = ftd.footertitle_id) where ftd.language_id = '" . (int)$this->config->get('config_language_id') . "' and ft.status=1 order by ft.sort_order");

        foreach($query->rows as $row){
            $query2 = $this->db->query("SELECT * FROM " . DB_PREFIX . "footerlink f LEFT JOIN " . DB_PREFIX . "footerlink_description fd ON (f.footerlink_id = fd.footerlink_id) where fd.language_id = '" . (int)$this->config->get('config_language_id') . "' and f.status=1 and f.selectheading='".$row['footertitle_id']."' order by f.sort_order");
            $subtitle=array();
            foreach($query2->rows as $row2){
                $subtitle[]=array('title' => $row2['title'], 'link' =>$row2['link']);
            }

            $data[]=array(
                'title' => $row['title'],
                'sub_title' => $subtitle
            );
        }

        return $data;
    }
    public function getDateWithMonth($date,$lang){
        $date=strtotime($date);
        $mon = date("n", $date);
        $day = date("d", $date);
        $year = date("Y", $date);


        $months = array(
            "ro" => ["ianuarie", "febuarie", "martie", "aprilie", "mai", "iunie", "iulie", "august", "septembrie", "octombrie", "noembrie", "decembrie"],
            "ru" => ["января", "февраля", "марта", "апреля", "мая", "июня", "июля", "августа", "сентября", "октября", "ноября", "декабря"],
            "en" => ["january", "february", "march", "april ", "may", "june ", "july ", "august ", "september ", "october ", "november", "december"]
        );

        $mon_=$months[$lang][$mon-1];
        $date_str = "$day $mon_ $year";
        return $date_str;

    }
	/* added by it-lab start */
	public function getTotalInformationsByCategory($category_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "information_to_category WHERE category_id=".$category_id);

		return $query->row['total'];
	}
	/* added by it-lab end */
	/* added by it-lab end */
    public function getPreviousInformation($informationId){
        return $this->db->query("SELECT i.`information_id` FROM `" . DB_PREFIX . "information` i INNER JOIN " . DB_PREFIX . "information_to_category itc on i.`information_id` = itc.`information_id` WHERE `sort_order` > (SELECT `sort_order` FROM " . DB_PREFIX . "information WHERE `information_id` = $informationId) and itc.category_id in (SELECT `category_id` FROM " . DB_PREFIX . "information_to_category WHERE `information_id` = $informationId) ORDER BY sort_order LIMIT 1")->row['information_id'] ?? null;
    }
}