<?php
require_once('configure.php');

if ( !empty( $_FILES ) ) {

	$allowedTypes = "application/pdf";

    if ($_FILES['file']['type'] != $allowedTypes) {
        header('HTTP/1.0 400 Bad Request');
        echo json_encode(array( 'message' => 'PDF types only.'));
        exit();
    }

    $file_name = date("dmyHis")."-".$_FILES[ 'file' ][ 'name' ];

    $tempPath = $_FILES[ 'file' ][ 'tmp_name' ];
    $uploadPath = INSTRUCTORS_PDF_DIR . $file_name;

    move_uploaded_file( $tempPath, $uploadPath );

    $answer = array( 'answer' => 'File transfer completed', 'file_name' => $file_name );
    $json = json_encode( $answer );

    echo $json;

} else {

    echo 'No files';

}
?>
