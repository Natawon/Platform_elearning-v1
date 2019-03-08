<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

require_once('vendor/autoload.php');
require_once('configure.php');


$postdata = file_get_contents("php://input");
$request = json_decode($postdata);


if ( !empty( $_FILES ) ) {
    $allowedTypes = "application/pdf";

    if ($_FILES['file']['type'] != $allowedTypes) {
        header('HTTP/1.0 400 Bad Request');
        echo json_encode(array( 'message' => 'PDF types only.'));
        exit();
    }

    $fileNameWithoutExt = str_replace('.pdf', '', $_FILES[ 'file' ][ 'name' ]);
    if ($fileNameWithoutExt == "" || preg_match('/[^A-Za-z0-9._\-]/', $fileNameWithoutExt)) {
        header('HTTP/1.0 400 Bad Request');
        echo json_encode(array( 'message' => 'ชื่อไฟล์ต้องเป็นภาษาอังกฤษและมีอักขระได้แค่ "-", "_" เท่านั้น'));
        exit();
    }

    $file_name = date("dmyHis")."-".$_FILES[ 'file' ][ 'name' ];

    $tempPath = $_FILES[ 'file' ][ 'tmp_name' ];
    $uploadPath = SLIDES_PDF_DIR . $file_name;

    $pdf = new Imagick($_FILES[ 'file' ][ 'tmp_name' ]);
    $totalPages = $pdf->getNumberImages();

    /* Find Durations */

    if ($totalPages > 4) {
        $diff = ($totalPages % 4);

        $rang_one = ($totalPages / 4);
        $rang_two = ($totalPages / 4);
        $rang_three = ($totalPages / 4);
        $rang_four = ($totalPages / 4);

        if ($diff == 1) {
            $rang_one += 0.75;
            $rang_two -= 0.25;
            $rang_three -= 0.25;
            $rang_four -= 0.25;
        } else if ($diff == 2) {
            $rang_one += 0.5;
            $rang_two += 0.5;
            $rang_three -= 0.5;
            $rang_four -= 0.5;
        } else if ($diff == 3) {
            $rang_one += 0.25;
            $rang_two += 0.25;
            $rang_three += 0.25;
            $rang_four -= 0.75;
        }

        /*if ($diff != 0) {
            $rang_one += 0.5;
            $rang_two -= 0.5;
        }*/

        $last_one = $rang_one;
        $last_two = $rang_two + $last_one;
        $last_three = $rang_three + $last_two;
        $last_four = $rang_four + $last_three;

        $range = array(array(1, $last_one), array($last_one + 1, $last_two), array($last_two + 1, $last_three), array($last_three + 1, $last_four));
        // $range = array(array(1, $last_one), array($last_one + 1, $last_two));
    } else {
        $range = array(array(1, $totalPages));
    }
    /* End Find */

    move_uploaded_file( $tempPath, $uploadPath );

    $answer = array( 'answer' => 'File transfer completed', 'file_name' => $file_name, 'totalPages' => $totalPages, 'range' => $range);
    $json = json_encode( $answer );

    echo $json;

} else if (isset($request->action) && $request->action == 'convert') {

    $pathToPdf = $request->file_name;

    /*$pdf = new Spatie\PdfToImage\Pdf(SLIDES_PDF_DIR.$pathToPdf);
    $totalPages = $pdf->getNumberOfPages();*/

    $totalPages = $request->totalPages;
    $durations = $request->durations;

    $file = array();

    $pdf_file = SLIDES_PDF_DIR.$pathToPdf;
    $save_to = 'demo.jpg';
    foreach (range($durations[0], $durations[1]) as $pageNumber) {
        $file_name = date('dmyHis').'-'.explode('-', pathinfo($pathToPdf, PATHINFO_FILENAME), 2)[1];
        $file_name .= '_'.$pageNumber.'.jpg';
        $file_path = SLIDES_PICTURE_DIR.$file_name;

        $index = $pageNumber-1;
        $img = new imagick($pdf_file."[".$index."]");

        //set background color
        // $img->setImageBackgroundColor('white');
        // $img = $img->flattenImages();
        $img->setImageBackgroundColor('white');
        $img->setImageAlphaChannel(imagick::ALPHACHANNEL_REMOVE); // 11
        $img->mergeImageLayers(imagick::LAYERMETHOD_FLATTEN);

        // set quality
        // $img->setCompression(Imagick::COMPRESSION_JPEG);
        // $img->setCompressionQuality(100);

        //set resolution
        $img->setResolution(930, 698);

        //reduce the dimensions - scaling will lead to black color in transparent regions
        $img->scaleImage(930, 698);

        //set new format
        $img->setImageFormat('jpg');

        //save image file
        $img->writeImage($file_path);


        // echo $pageNumber;

        $results = array(
            "is_success" => true,
            // "pageNumber" => $pageNumber,
            "file_name" => $file_name
        );

        array_push($file, $results);
    }

    $progressFiles = ($durations[1] / $totalPages) * 100;

    if ($progressFiles == 100) {
        // unlink(SLIDES_PDF_DIR.$pathToPdf);
        $answer = array( 'answer' => 'Files convert completed.', 'file' => $file ,"progressFiles" => $progressFiles);
    } else {
        $answer = array( 'answer' => 'Files in processing.', 'file' => $file ,"progressFiles" => $progressFiles);
    }

    $json = json_encode( $answer );

    echo $json;

} else if (isset($request->action) && $request->action == 'delete') {

    header('HTTP/1.0 403 Forbidden');
    echo json_encode(array( 'message' => 'Access Denied.'));
    exit();

    $answer = array( 'deleted_files' => array(), 'failed_files' => array());

    foreach ($request->files as $file) {

        if ( !unlink( SLIDES_PICTURE_DIR.$file->file_name ) ) {
            $answer['failed_files'][] = $file->file_name;
        } else {
            $answer['deleted_files'][] = $file->file_name;
        }

    }

    $json = json_encode( $answer );

    echo $json;

}  else if (isset($request->action) && $request->action == 'duplicate') {

    header('HTTP/1.0 403 Forbidden');
    echo json_encode(array( 'message' => 'Access Denied.'));
    exit();

    $answer = array( 'duplicated_files' => array(), 'failed_files' => array());

    foreach ($request->files as $index => $file) {

        $file_name = date('dmyHis').'-'.explode('-', $file->file_name, 2)[1];

        if ( !copy( SLIDES_PICTURE_DIR.$file->file_name, SLIDES_PICTURE_DIR.$file_name ) ) {
            $answer['failed_files'][$index] = $file;
        } else {
            $answer['duplicated_files'][$index]['id'] = $file->id;
            $answer['duplicated_files'][$index]['file_name'] = $file_name;
        }

    }

    $json = json_encode( $answer );

    echo $json;

} else {

    echo 'No files';

}





