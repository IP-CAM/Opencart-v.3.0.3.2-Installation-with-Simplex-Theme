<?php

class ModelToolImage extends Model {
	public function resize($filename, $width = 0, $height = 0, $default = '') {
		if(isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
			$server = HTTPS_SERVER;
		} else {
			$server = HTTP_SERVER;
		}

		if($filename) {
			$image_info = @getimagesize(DIR_IMAGE . $filename);
			if(!$image_info) {
				return $server . 'image/' . $filename;
			}
		} else {
			$filename = "no_image.png";
		}

		if(!is_file(DIR_IMAGE . $filename) || substr(str_replace('\\', '/', realpath(DIR_IMAGE . $filename)), 0, strlen(DIR_IMAGE)) != str_replace('\\', '/', DIR_IMAGE)) {
			return;
		}

		$image_old = $filename;
		
		$image_webp = 'cache/' . utf8_substr($filename, 0, utf8_strrpos($filename, '.')) . '-' . (int)$width . 'x' . (int)$height . '.webp';

		if(!is_file(DIR_IMAGE . $image_webp) || (filemtime(DIR_IMAGE . $image_old) > filemtime(DIR_IMAGE . $image_webp))) {
			list($width_orig, $height_orig, $image_type) = getimagesize(DIR_IMAGE . $image_old);

			if(!in_array($image_type, array(
				IMAGETYPE_PNG,
				IMAGETYPE_JPEG,
				IMAGETYPE_GIF
			))) {
				return DIR_IMAGE . $image_webp;
			}

			$path = '';

			$directories = explode('/', dirname($image_webp));

			foreach($directories as $directory) {
				$path = $path . '/' . $directory;

				if(!is_dir(DIR_IMAGE . $path)) {
					@mkdir(DIR_IMAGE . $path, 0777);
				}
			}

			if($width_orig != $width || $height_orig != $height) {
				$width != 0 ?: $width = $width_orig;
				$height != 0 ?: $height = $height_orig;
				$image = new Image(DIR_IMAGE . $image_old);
				$image->resize($width, $height, $default);
				$image->save(DIR_IMAGE . $image_webp);
			} else {
				copy(DIR_IMAGE . $image_old, DIR_IMAGE . $image_webp);
			}
		}
		if((isset($_SERVER['HTTP_ACCEPT']) === true) && (strstr($_SERVER['HTTP_ACCEPT'], 'image/webp') !== false)) {
			if($this->request->server['HTTPS']) {
				return $this->config->get('config_ssl') . 'image/' . $image_webp;
			} else {
				return $this->config->get('config_url') . 'image/' . $image_webp;
			}
		}
	}
}
