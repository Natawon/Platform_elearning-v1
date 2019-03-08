<?php
if (isset($_COOKIE['site']) && $_COOKIE['site'] == "webview") {
	echo '<meta name="viewport" content="width=device-width, initial-scale=0.85, minimum-scale=0.85, maximum-scale=0.85, user-scalable=no">';
} else {
	echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">';
}