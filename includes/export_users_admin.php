<!-- Search plus Export Users Admin Page -->
<div class="wrap">
    <h1>Search / Export Users</h1>
    <h4><code>Searching</code> for <code>users</code> with specific keywords and <code>exporting</code> results in <code>CSV</code> file</h4>
    <!--show message-->
    <div id="message" class="updated notice notice-success below-h2">
        <p></p>
    </div>

    <form name="user_form" method="post" >
        <?php
        echo '<input type="hidden" name="bk-ajax-nonce" id="bk-ajax-nonce" value="' . wp_create_nonce( 'bk-ajax-nonce' ) . '" />';
        ?>
        <?php wp_nonce_field( 'export-form' ); ?>
        <div class="loader">
            <span><input type="submit" class="btn_get_all button-primary" name="get_all" value="Get All Users" /></span>
            <span class="loader_show"><img src="<?PHP echo plugins_url( '/assets/images/bx_loader.gif', __FILE__ ); ?>" width="16px" height="16px" /></span>
        </div>       
        <input type="hidden" name="hidden_val" class="hidden_val" value="" />

        <!-- Notes for users -->
<!--         <div class="pos_div"><div class="notes">
                <h4>Notes</h4>
            </div>
            <div class="form_notes">
                <?php echo get_option('notes-users'); ?>
            </div></div> -->
            <!-- End of -->

        <h4>BY:</h4>
        <span>
            <input type="button" class="add_btn button-secondary right" value="+"  />
        </span>
        <!-- User Role -->
        <div class="op_role">
            <span class="role_name">Role</span>
            <select class="user_role primary" name="user_role"></select>
        </div>
        <!-- End Of -->

        <!-- start radio button for Relation Between Values -->
        <div class="form-group">
            <span class="op_name">Operation</span>
            <div class="col-md-4 op"> 
                <label class="radio-inline" for="operation">
                    <input type="radio" name="op_args" class="op_args" value="AND" checked="checked" />
                    AND
                </label> 
                <label class="radio-inline" for="operation">
                    <input type="radio" name="op_args" class="op_args" value="OR" />
                    OR
                </label>
            </div>
        </div> 
        <!-- end radio buttons -->

        <div class="div_add">
            <!-- Drop-Down Menu for meta-name -->
            <select name="meta_name[]" class="meta_name primary" data-live-search="true">
            </select>
            <!-- Drop-Down Menu for meta-compare -->
            <select name="meta_compare[]" class="meta_compare primary">
                <option value="="> = </option>
                <option value="!=">!= </option>
                <option value=">"> > </option>
                <option value=">="> >= </option>
                <option value="<"> < </option>
                <option value="<="> <= </option>
                <option value="LIKE"> LIKE </option>
                <option value="NOT LIKE"> NOT LIKE </option>
                <option value="IN"> IN </option>
                <option value="NOT IN"> NOT IN </option>
                <option value="BETWEEN"> BETWEEN </option>
                <option value="NOT BETWEEN"> NOT BETWEEN </option>
                <option value="EXISTS"> EXISTS </option>
                <option value="NOT EXISTS"> NOT EXISTS </option>
                <option value="REGEXP"> REGEXP </option>
                <option value="NOT REGEXP"> NOT REGEXP </option>
                <option value="RLIKE"> RLIKE </option>
            </select>
            <span><input type="text" name="meta_value[]" class="meta_value primary" placeholder="Search for value"></span>
            <!-- Search Button -->
            <span><input type="submit" class="btn_get button-primary" name="search_user" value="Search" /></span>
        </div>
        <!-- <div> has class to separate search & table with hidden bar -->
        <div class="line-separator"></div>
        <span class="no_items">0 item</span>
        <!--table for showing data-->
        <input type="hidden" name="export_by_id" class="export_by_id" value="" />
        <table class="wp-list-table widefat fixed striped">
            <thead class="tbl_header">
            <?php 
                $all_fields = getFieldSearch();
                $all_keys = array_keys( $all_fields );
                $all_values = array_values( $all_fields );
                foreach( $all_values as $tbl_header ) {
                    echo '<th style="font-weight:bold;">'. $tbl_header .'</th>';
                }
            ?>
            </thead>
            <tbody class="show_content">
            <td>No Data</td>
            </tbody>
        </table>
        <p class="submit">
            <!-- Export Buttton -->
            <input type="submit" class="button-primary export_csv" name="download_csv" value="Export" disabled />
        </p>
        <input type="hidden" name="idusers" class="id_users" value="<?php echo $_GET['ids'] ?>" />
    </form>

</div>
