<?php

// include the fixed types
include "include/settings.php";
include "include/menu.php";
include "include/artical.php";
include "include/helpers.php";
// create the settings object
$settings = new settings ();

// include the proper model
include "models/{$settings->get('modelType')}.php";
// include the proper view type
include "views/{$settings->get('viewType')}.php";

// create the model
$model = new model ();
// create the view
$view = new view ( $settings );

if ($model->open ( $settings->get ( 'modelHost' ), $settings->get ( 'modelUser' ), // open the model
					$settings->get ( 'modelPass' ), $settings->get ( 'modelData' ) )) {
	$view->fatal ( $model->errorString () );
}
// ** user info trial

session_start();
if (isset($_SESSION['visits'])) {
	++$_SESSION['visits'];
	
} else { 
	$_SESSION['visits'] = 1; 
}

$view->addDebugMsg(sprintf("%-25s %s", "Key", "Value"));
foreach ($_SESSION as $key=>$value) {
	$view->addDebugMsg(sprintf("%-25s %s", $key, $value));
}

// ** end trial

// load the site settings from the model
$settings->load ( $model );
// build a trial menu
$articalMenu = new menu ( 2, $model );
$articalMenu->addToView ( $view );
// check for an artical to view, and show it
if (isset ( $_GET ['viewArtical'] )) {
	$artical = new artical ();
	$model->readArtical ( $_GET ['viewArtical'], $artical );
	if ($artical->viewable ())
		$view->addArtical ( $artical );
	else
		header ( 'X-PHP-Response-Code: 404', true, 404 );
}
$model->close ();

$view->display ();

?>