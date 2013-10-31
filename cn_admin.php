<?php
if (filter_input(INPUT_POST, 'cn_auth_hidden') === 'TRUE') {
	// Form data has been sent
	$uni = filter_input(INPUT_POST, 'cn_auth_uni');
	update_option('cn_auth_uni', $uni);
	$appname = filter_input(INPUT_POST, 'cn_auth_appname');
	update_option('cn_auth_appname', $appname);
	$apptoken = filter_input(INPUT_POST, 'cn_auth_apptoken');
	update_option('cn_auth_apptoken', $apptoken);
	$elementid = filter_input(INPUT_POST, 'cn_auth_elementid');
	update_option('cn_auth_elementid', $elementid);
	$role = filter_input(INPUT_POST, 'cn_auth_role');
	update_option('cn_auth_role', $role);
	echo '<div class="updated"><p><strong>Options saved</strong></p></div>';
} else {
	// Normal page display
	$uni = get_option('cn_auth_uni');
	$appname = get_option('cn_auth_appname');
	$apptoken = get_option('cn_auth_apptoken');
	$elementid = get_option('cn_auth_elementid');
	$role = get_option('cn_auth_role');
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
		<p><?php _e("Role for new users: " ); ?><select name="cn_auth_role" id="role">
			<!-- selected="selected" -->
				<option <?php if ($role == "administrator") { _e('selected="selected"'); } ?> value="administrator">Administrator</option>
				<option <?php if ($role == "subscriber") { _e('selected="selected"'); } ?> value="subscriber">Subscriber</option>
				<option <?php if ($role == "contributor") { _e('selected="selected"'); } ?> value="contributor">Contributor</option>
				<option <?php if ($role == "author") { _e('selected="selected"'); } ?> value="author">Author</option>
				<option <?php if ($role == "editor") { _e('selected="selected"'); } ?> value="editor">Editor</option>
			</select>
		</p>
		<p class="submit">
			<input type="submit" name="Submit" value="<?php _e('Update Options', 'cn_auth_trdom' ) ?>" />
		</p>
	</form>
</div>
