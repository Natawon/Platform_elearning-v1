<?php
require_once('configure.php');

// Resize Image
function resizeImage($image, $newWidth, $newHeight, $maintainRatio, $toPath){
	// Get sizes
	list($width, $height) = getimagesize($image);

	if ($maintainRatio === true) {
		if($width > $height && $newHeight < $height){
			$newHeight = $height / ($width / $newWidth);
		} else if ($width < $height && $newWidth < $width) {
			$newWidth = $width / ($height / $newHeight);
		} else {
			$newWidth = $width;
			$newHeight = $height;
		}
	}

	$detectedType = exif_imagetype($image);

	// Load
	if ($detectedType == IMAGETYPE_PNG) {
		$source = imagecreatefrompng($image);
	} elseif ($detectedType == IMAGETYPE_JPEG) {
		$source = Imagecreatefromjpeg($image);
	} elseif ($detectedType == IMAGETYPE_GIF) {
		$source = imagecreatefromgif($image);
	} else {
		return false;
	}

	$thumb = imagecreatetruecolor($newWidth, $newHeight);

	// Resize
	imagecopyresized($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

	// Output
	if ($detectedType == IMAGETYPE_PNG) {
		return imagepng($thumb, $toPath);
	} elseif ($detectedType == IMAGETYPE_JPEG) {
		return imagejpeg($thumb, $toPath);
	} elseif ($detectedType == IMAGETYPE_GIF) {
		return imagegif($thumb, $toPath);
	}

	return false;
}





