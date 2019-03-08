<?php
require_once('configure.php');
require_once('functions.php');

if ( !empty( $_FILES ) ) {

	$allowedTypes = array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF);
	$detectedType = exif_imagetype($_FILES['file']['tmp_name']);
	$error = !in_array($detectedType, $allowedTypes);

	if (!in_array($detectedType, $allowedTypes)) {
		header('HTTP/1.0 400 Bad Request');
		echo json_encode(array( 'message' => 'Image types only.'));
		exit();
	}

	list($width, $height) = getimagesize($_FILES['file']['tmp_name']);
	$isResize = false;

    if ($width != 480 || $height != 270) {
    	$isResize = true;
        // header('HTTP/1.0 400 Bad Request');
        // echo json_encode(array( 'message' => 'File transfer failed. The image resolution must be 2000 x 1500 pixel.'));
        // exit();
    }

	// list($width, $height) = getimagesize($_FILES['file']['tmp_name']);

	// /* Calculator 16:9 */
	// $multiplier_width = 16;
	// $multiplier_height = 9;

	// $div_width = $width / $multiplier_width;
	// $div_height = $height / $multiplier_height;

	// $div_width = number_format($div_width, 2, '.', '');
	// $div_height = number_format($div_height, 2, '.', '');

	// /* End Calculator 16:9 */

	// if ($div_width != $div_height) {
	//     header('HTTP/1.0 400 Bad Request');
	//     echo json_encode(array( 'message' => 'File transfer failed. The image aspect ratio must be 16:9.'));
	//     exit();
	// }

    $file_name = date("dmyHis")."-".$_FILES[ 'file' ][ 'name' ];

    $tempPath = $_FILES[ 'file' ][ 'tmp_name' ];
    $uploadPath = COURSES_THUMBNAIL_DIR . $file_name;

    if ($isResize) {
    	if (!resizeImage($tempPath, 480, 270, false, $uploadPath)) {
	    	move_uploaded_file( $tempPath, $uploadPath );
	    }
    } else {
    	move_uploaded_file( $tempPath, $uploadPath );
    }


    $answer = array( 'answer' => 'File transfer completed', 'file_name' => $file_name );
    $json = json_encode( $answer );

    echo $json;

} else {

    echo 'No files';

}
?>
