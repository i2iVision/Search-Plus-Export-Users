<?php require_once( plugin_dir_path( __FILE__ ).'../speu.php' ); ?>
<head>
<link href="//cdn.bootcss.com/Selectivity.js/2.1.0/selectivity-full.css" rel="stylesheet">
<script src="//cdn.bootcss.com/Selectivity.js/2.1.0/selectivity-full.js"></script>
<link href="//netdna.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<style>
    body {
      padding-top: 0px;  /* 60px to make the container go all the way to the bottom of the topbar */
      padding-bottom: 10px;
      position: relative; 
      background: #f4f4f4;
    }
    .submit-style-btn {
        width: 200px;
        height: 34px;
        margin: 20 40%;
        background: #e35950;
        border: 0px;
        margin-top: 25px;
        border-color: #ba281e;
        color: #fff;
    }
</style>
</head>
<div class="container-fluid">
    <h1>Setting</h1> 
    <!--show message-->
    <div id="message" class="updated notice notice-success below-h2">
    </div>
    <!-- Notes for Users (Deprecated) -->

    <!-- End Of -->

        <div class="row wr">
            <div class="col-md-12">
                <div class="tabbable" id="tabs">
                    <ul class="nav nav-tabs">
                        <li class="active">
                            <a href="#panel-654295" data-toggle="tab"><b>Import</b></a>
                        </li>
                        <li>
                            <a href="#panel-730640" data-toggle="tab"><b>Dropbox</b></a>
                        </li>
                        <li>
                            <a href="#panel-730642" data-toggle="tab"><b>Section 3</b></a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="panel-654295">
                            <h3 class="text-center" style="color: #57A6EC;">
                                Click to upload <code>CSV</code> file 
                            </h3>
                            <form method="post" enctype="multipart/form-data">
                            <input type="file" name="csv_file" class="filestyle import_file" data-buttonName="btn-primary" data-placeholder="No file" data-icon="glyphicon-inbox">
                                <span style="display: block;color:#ccc;text-align:center;">You can choose csv file from your dropbox desktop version.</span>
                                </br>
                                <p class="checkable_options">
                                    <input type="radio" name="checkable" class="checkable" value="1" <?php if( $_POST["checkable"] == 1 ) echo "checked"; else echo ""; ?> onclick="createInput(this)" /><span class="checkname">with generic passwords</span>
                                    <input type="radio" name="checkable" class="checkable" value="2" <?php if( $_POST["checkable"] == 2 ) echo "checked"; else echo ""; ?> onclick="createInput(this)" /><span class="checkname">with custom one password for all</span>
                                    <input type="radio" name="checkable" class="checkable" value="3" <?php if( $_POST["checkable"] == 3 ) echo "checked"; else echo ""; ?> onclick="createInput(this)" /><span class="checkname">without all usermeta</span>
                                    <input type="radio" name="checkable" class="checkable" value="4" <?php if( $_POST["checkable"] == 4 ) echo "checked"; else echo ""; ?> onclick="createInput(this)" /><span class="checkname">with  some usermeta</span>
<!--                                 <select id="my-select" name="character[]" multiple="multiple" style="display:none;">
                                    <option value="Peter">Peter Griffin</option>
                                    <option value="Lois">Lois Griffin</option>
                                    <option value="Chris">Chris Griffin</option>
                                    <option value="Meg">Meg Griffin</option>
                                    <option value="Stewie">Stewie Griffin</option>
                                </select> -->
                                <input type="text" class="meta_enter" name="meta_enter" placeholder="Write usermeta separated by comma. ex(first_name,last_name,...)" style="display:none;margin: 25px 104px;width: 800px;height: 34px;font-size:12px;border-radius: 4px;padding: 13px;background-color: #eee;" >
                                </p>
                                <p class="import">
                                    <!-- Import Buttton -->
                                    <input type="submit" class="submit-style-btn import_btn" name="import_btn" value="Import CSV File" />
                                </p>
                            </form>
                        </div>
                        <div class="tab-pane" id="panel-730640">
                            <p>
                            <iframe src="http://localhost/modern/wp-content/plugins/Search-Plus-Export-Users/dropbox/sample-form.php" width="1139px" height="500px" ></iframe>                           
                            </p>
                        </div>
                        <div class="tab-pane" id="panel-730642">
                            <p>
                                Comming Soooooooon
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</div>
