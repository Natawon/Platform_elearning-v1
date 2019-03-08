<?php
require_once('configure.php');

if ( !empty( $_FILES ) ) {

	$allowedTypes = array(
		"application/pdf",
		"application/msword",
		"application/vnd.openxmlformats-officedocument.wordprocessingml.document",
		"application/vnd.ms-excel",
		"application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
		"application/vnd.ms-powerpoint",
		"application/vnd.openxmlformats-officedocument.presentationml.presentation",
	);
	$detectedType = $_FILES['file']['type'];

	if (!in_array($detectedType, $allowedTypes)) {
		header('HTTP/1.0 400 Bad Request');
		echo json_encode(array( 'message' => 'Document types only.'));
		exit();
	}

    $file_name = $_FILES[ 'file' ][ 'name' ];

    $tempPath = $_FILES[ 'file' ][ 'tmp_name' ];
    $uploadPath = DOCUMENTS_FILE_DIR . $file_name;

    move_uploaded_file( $tempPath, $uploadPath );

    $answer = array( 'answer' => 'File transfer completed', 'file_name' => $file_name, 'type' => $_FILES['file']['type'], 'header' => get_headers($tempPath, 1));
    $json = json_encode( $answer );

    echo $json;

} else {

    echo 'No files';

}
?>
