<?php
switch ($_GET['task']) {
	case 'fromWindow':
		echo '
			<script type="text/javascript">
				window.opener.CKEDITOR.tools.callFunction('.$_GET['CKEditorFuncNum'].', "'.$_GET['file_name'].'" );
				window.close();
			</script>';
		break;

	case 'iframe':
		echo '
			<script type="text/javascript">
				window.parent.CKEDITOR.tools.callFunction('.$_GET['CKEditorFuncNum'].', "'.$_GET['file_name'].'", "'.$_GET['message'].'");
			</script>';
		break;

	default:
		# code...
		break;
}

exit();