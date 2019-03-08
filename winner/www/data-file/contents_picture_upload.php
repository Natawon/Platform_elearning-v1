<?php
require_once('configure.php');

if ( !empty( $_FILES ) ) {

	$allowedTypes = array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF);
	$detectedType = exif_imagetype($_FILES['upload']['tmp_name']);
	$error = !in_array($detectedType, $allowedTypes);

	if (!in_array($detectedType, $allowedTypes)) {
		// header('HTTP/1.0 400 Bad Request');
        header('Location: ' . CALLBACK_CKEDITOR_PATH . '?CKEditorFuncNum='. $_GET['CKEditorFuncNum'] . '&file_name=&message=File wrong type.');
		exit();
	}

    $file_name = $_FILES[ 'upload' ][ 'name' ];

    $tempPath = $_FILES[ 'upload' ][ 'tmp_name' ];
    $uploadPath = CONTENTS_PICTURE_DIR . $file_name;
    $ckfile = BASE_IMG_PATH . "contents/picture/" . $file_name;

    move_uploaded_file( $tempPath, $uploadPath );

    if(isset($_GET['CKEditorFuncNum'])){
        $CKEditorFuncNum = $_GET['CKEditorFuncNum'];
        header('Location: ' . CALLBACK_CKEDITOR_PATH . '?task=iframe&CKEditorFuncNum='. $CKEditorFuncNum . '&file_name=' . $ckfile); /* . '&message=File transfer completed');*/
        exit();
    } else {
        $answer = array( 'answer' => 'File transfer completed', 'file_name' => $file_name );
        $json = json_encode( $answer );

        echo $json;
    }


} else {
    header('Location: ' . CALLBACK_CKEDITOR_PATH . '?CKEditorFuncNum='. $_GET['CKEditorFuncNum'] . '&file_name=&message=No files');
    exit();

}
?>
