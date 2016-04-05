<?php require_once("../../../../wp-load.php"); ?>
<head>
	<link href="//cdn.bootcss.com/Selectivity.js/2.1.0/selectivity-full.css" rel="stylesheet">
	<script type="text/javascript" src="jquery.min.js"></script>
	<script type="text/javascript" src="../assets/js/filestyle.js"></script>
	<script src="//cdn.bootcss.com/Selectivity.js/2.1.0/selectivity-full.js"></script>
	<link href="//netdna.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" >
	<style>
	    body {
	      padding-top: 0px;  /* 60px to make the container go all the way to the bottom of the topbar */
	      padding-bottom: 10px;
	      position: relative; 
	      background: #f4f4f4;
	    }
	    .dropbox {
    		margin: 22px 43% 0px;
		}
		.text-before-center:before {
		    content: "\e027";
		    font-family: 'Glyphicons Halflings';
		    font-size: 18px;
		}
		.submit-style-btn {
			width: 200px;
		    height: 34px;
		    margin: 20 40%;
		    background: #e35950;
		    border: 0px;
		    border-color: #ba281e;
		    color: #fff;
		}
	</style>
</head>

<?php
 $option = get_option('speu_dropbox_config');
 if(empty( $option )) {
 	$option['app_key'] = "2yiumoyh9yz10xh";
 	$option['app_secret'] = "etex9mg2yqoxz51";
 	$option['access_token'] = "9xBz_RqYg6AAAAAAAAAA7ycedsuBw_YMUFEQz5eIw7EFoIyOw8Xz-GSVlR05haNA";
 }
error_reporting(E_ALL);
require_once("DropboxClient.php");
// you have to create an app at https://www.dropbox.com/developers/apps and enter details below:
$dropbox = new DropboxClient(array(
	'app_key' => $option['app_key'], 
	'app_secret' => $option['app_secret'],
	'app_full_access' => false,
),'en');



handle_dropbox_auth($dropbox,$option); // see below

// if there is no upload, show the form

if(empty($_FILES['the_upload'])) {
?>
<img src="logo.png" class="dropbox">
<form enctype="multipart/form-data" method="POST" action="">
<p>
    <h3 class="text-center text-before-center" style="color: #57A6EC;">
        Click to upload <code>CSV</code> file to <code>Dropbox</code> 
    </h3>
    <input type="file" name="the_upload[]" class="filestyle import_file" data-buttonName="btn-primary" data-placeholder="No file imported" data-badge="true" data-icon="glyphicon-inbox" value="<?php $speu_exported_file = get_option( 'speu_export_file'); echo 'C:/xampp/htdocs/export-results_2016-03-28 (7).csv'; ?>" multiple="multiple">
</p>
<p>
<input type="submit" class="submit-style-btn" name="submit-btn" value="Upload!">
<p class="waiting_dropbox_upload" style="display:none;text-align:center;margin-top:-28px;">Waiting .....</p>
</p>
<input type="hidden" value="<?php echo $_GET["check"]; ?>" >
</form>

<script type="text/javascript">
		
		$('input[type=submit]').on( 'click', function() {
			if( $( "input.import_file" ).val() != "" ){
				$('p.waiting_dropbox_upload').show();
			} 
/*			else {
				$('p.waiting_dropbox_upload').hide();
			}*/
		});
	    function CloseAndrefresh() {
            window.close();
            window.opener.location.reload(true);
        }

        $(window).load(function() {
        	if( $("input[type=hidden]").val() == 10 ) {
        		CloseAndrefresh();
        	}

        });
</script>
<?php }  else { ?> 

		<img src="logo.png" class="dropbox">
		<?php
		echo "<b>Uploading ......</b>";
	for( $i = 0;$i< sizeof($_FILES["the_upload"]["name"]);$i++ ) {
		$upload_name = $_FILES["the_upload"]["name"][$i];
		echo "<li>";
		echo "$upload_name";
		$meta = $dropbox->UploadFile($_FILES["the_upload"]["tmp_name"][$i], $upload_name);
		echo ".......... Done.";
		echo "</li>";
	}
	echo "<b>Your File is imported into your dropbox's account</b><br>";
}


// ================================================================================
// store_token, load_token, delete_token are SAMPLE functions! please replace with your own!
function store_token($token, $name)
{
	file_put_contents("tokens/$name.token", serialize($token));
}

function load_token($name)
{
	if(!file_exists("tokens/$name.token")) return null;
	return @unserialize(@file_get_contents("tokens/$name.token"));
}

function delete_token($name)
{
	@unlink("tokens/$name.token");
}
// ================================================================================

function handle_dropbox_auth($dropbox,$option)
{
	// first try to load existing access token
	$access_token = load_token($option['access_token']);
	if(!empty($access_token)) {
		$dropbox->SetAccessToken($access_token);
	}
	elseif(!empty($_GET['auth_callback'])) // are we coming from dropbox's auth page?
	{
		// then load our previosly created request token
		$request_token = load_token($_GET['oauth_token']);
		if(empty($request_token)) die('Request token not found!');
		
		// get & store access token, the request token is not needed anymore
		$access_token = $dropbox->GetAccessToken($request_token);	
		store_token($access_token, $option['access_token']);
		delete_token($_GET['oauth_token']);
	}

	// checks if access token is required
	if( !$dropbox->IsAuthorized() )
	{?>
		<img src="logo.png" class="dropbox">
		<?php
		// redirect user to dropbox auth page
		$return_url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']."?auth_callback=1&check=10&config=30"; 
		$auth_url = $dropbox->BuildAuthorizeUrl($return_url);
		$request_token = $dropbox->GetRequestToken();
		store_token($request_token, $request_token['t']);
		die("Authentication required. <a onclick=window.open('$auth_url','popUpWindow','height=400,width=600,left=10,top=10,,scrollbars=yes,menubar=no') return false;>Click here.</a>");
	}

} 


$config_url = plugin_dir_url(__FILE__)."account_config.php"; 
echo "<p class='text-center'>For your account's configuration click &nbsp;<a onclick=window.open('$config_url','popUpWindow','height=400,width=600,left=10,top=10,,scrollbars=yes,menubar=no') return false;><b>Here</b></a>&nbsp!</p>";
