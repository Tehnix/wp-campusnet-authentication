<?php
if ($_POST['cn_auth_hidden'] === 'TRUE') {
	// Form data has been sent
	$uni = $_POST['cn_auth_uni'];
	update_option('cn_auth_uni', $uni);
	$appname = $_POST['cn_auth_appname'];
	update_option('cn_auth_appname', $appname);
	$apptoken = $_POST['cn_auth_apptoken'];
	update_option('cn_auth_apptoken', $apptoken);
	$elementid = $_POST['cn_auth_elementid'];
	update_option('cn_auth_elementid', $elementid);
	echo '<div class="updated"><p><strong>Options saved</strong></p></div>';
} else {
	// Normal page display
	$uni = get_option('cn_auth_uni');
	$appname = get_option('cn_auth_appname');
	$apptoken = get_option('cn_auth_apptoken');
	$elementid = get_option('cn_auth_elementid');
}
?>



<div class="wrap">
	<h2><?php _e(__( 'CampusNet Authentication Settings', 'cn_auth_trdom' )); ?></h2>
	<?php
	if (!function_exists('curl_init')){
		_e('You need cURL for this plugin to work!!');
	}
	?>
	<form name="cn_auth_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
		<input type="hidden" name="cn_auth_hidden" value="TRUE">
		<p><?php _e("University: " ); ?><input type="text" name="cn_auth_uni" value="<?php echo $uni; ?>" size="20"><?php _e(" ex: dtu, ihk etc" ); ?></p>
		<p><?php _e("App Name: " ); ?><input type="text" name="cn_auth_appname" value="<?php echo $appname; ?>" size="20"></p>
		<p><?php _e("App Token: " ); ?><input type="text" name="cn_auth_apptoken" value="<?php echo $apptoken; ?>" size="20"></p>
		<p><?php _e("Element id: " ); ?><input type="text" name="cn_auth_elementid" value="<?php echo $elementid; ?>" size="20"><?php _e(" ex: 343001 (use 0 for none)" ); ?></p>

		<p class="submit">
			<input type="submit" name="Submit" value="<?php _e('Update Options', 'cn_auth_trdom' ) ?>" />
		</p>
	</form>
</div>
