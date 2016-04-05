<?php 
	require_once("../../../../wp-load.php"); 
	require_once( plugin_dir_path( __FILE__ ).'../speu.php' );

?>
<head>
	<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
	<style>
	    body {
	      padding-top: 0px;  /* 60px to make the container go all the way to the bottom of the topbar */
	      padding-bottom: 10px;
	      position: relative; 
	      background: #f4f4f4;
	    }
	    .dropbox {
    		margin: 22px 38% 0px;
		}
		div.config {
			padding: 6px;
		}
	</style>
</head>
<div class="config">
<img src="logo.png" class="dropbox">
<form role="form" action="" method="post">
  <div class="form-group">
    <label for="key">App Key:</label>
    <input type="text" name="app_key" value="<?php  $option = get_option('speu_dropbox_config'); echo $option['app_key']; ?>" class="form-control" id="key">
  </div>
  <div class="form-group">
    <label for="secret">App Secret:</label>
    <input type="text" name="app_secret" value="<?php  $option = get_option('speu_dropbox_config'); echo $option['app_secret']; ?>" class="form-control" id="secret">
  </div>
  <div class="form-group">
    <label for="token">Access Token:</label>
    <input type="text" name="access_token" value="<?php  $option = get_option('speu_dropbox_config'); echo $option['access_token']; ?>" class="form-control" id="token">
  </div>
 <button type="submit" name="submit_config" class="btn btn-default">Submit</button>
</form>
</div>

<?php 
$config = array();
if( isset( $_POST["submit_config"] ) ) {
	$config['app_key'] = $_POST['app_key'];
	$config['app_secret'] = $_POST['app_secret'];
	$config['access_token'] = $_POST['access_token'];
	update_option( 'speu_dropbox_config', $config );
	header("Location: sample-form.php?check=10&config=30");
}
?>