<?php
require_once('configure.php');

if ( !empty( $_FILES ) ) {

	$allowedTypes = array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF);
	$detectedType = exif_imagetype($_FILES['file']['tmp_name']);
	$error = !in_array($detectedType, $allowedTypes);

	if (!in_array($detectedType, $allowedTypes)) {
		header('HTTP/1.0 400 Bad Request');
		echo json_encode(array( 'message' => 'Image types only.'));
		exit();
	}

    // list($width, $height) = getimagesize($_FILES['file']['tmp_name']);

    // if ($width != 670 && $height != 80) {
    //     header('HTTP/1.0 400 Bad Request');
    //     echo json_encode(array( 'message' => 'File transfer failed. The image resolution must be 670 x 80 pixel.'));
    //     exit();
    // }

    $file_name = $_FILES[ 'file' ][ 'name' ];

    $tempPath = $_FILES[ 'file' ][ 'tmp_name' ];
    $uploadPath = METHODS_PICTURE_DIR . $file_name;

    move_uploaded_file( $tempPath, $uploadPath );

    $answer = array( 'answer' => 'File transfer completed', 'file_name' => $file_name );
    $json = json_encode( $answer );

    echo $json;

} else {

    echo 'No files';

}
?>
