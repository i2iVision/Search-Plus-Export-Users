/*=========== [PHPdev5] Scripts for Search plus Export Users plugin ===========*/

//================= [PHPdev5] function used to get meta key ====================
function meta_key_data() {
    jQuery.post(
            MyAjax.ajaxurl,
            {
                action: 'load-meta',
                security: jQuery( '#bk-ajax-nonce' ).val(),
            },
    function( meta ) {
        var meta_key_menu = "";
        var i;
        for ( i in meta ) {
            meta_key_menu += '<option value="' + meta[i].meta_key + '">' + meta[i].meta_key + '</option>';
        }
        jQuery( ".meta_name" ).last().html( meta_key_menu );

    }
    );
}

//================= [PHPdev5] Get Users'Role ====================
function get_users_role() {
    jQuery.post(
            MyAjax.ajaxurl,
            {
                action: 'user-role',
                security: jQuery( '#bk-ajax-nonce' ).val(),
            },
    function( role ) {
        jQuery( ".user_role" ).html( role );
    }
    );
}


jQuery(document).ready(function($) {

//================= [PHPdev5] Call Function meta_key_data() ====================   
    meta_key_data();

//================= [PHPdev5] Call Function get_users_role() ====================
    get_users_role();

//================= [PHPdev5] used to get all users ===================
    jQuery( '.btn_get_all' ).on( 'click', function(e) {
        e.preventDefault();
        jQuery.post(
            MyAjax.ajaxurl,
            {
                action: 'get_all_users',
                security: jQuery( '#bk-ajax-nonce' ).val(),
            },
        function( userdata ) {
            var data = '';
            jQuery( ".loader_show" ).fadeIn();
            for( i in userdata ) {
                data += "<tr class='results'><td>" + userdata[i].userid + "</td>";
                data += "<td>" + userdata[i].username + "</td>";
                data += "<td>" + userdata[i].useremail + "</td></tr>";
            }
            jQuery( ".show_content" ).html( data );
            var count = jQuery( ".results" ).length;
            if ( count == 0 ) {
                jQuery( ".no_items" ).text( "0 item" );
            }
            else if ( count == 1 ) {
                jQuery( ".no_items" ).text( "1 item" );
            }
            else {
                jQuery( ".no_items" ).text( count + " " + "items" );
            }
            jQuery( "#message" ).removeClass( "error" );
            jQuery( "#message" ).fadeIn();
            jQuery( ".notice-success p" ).text( "Now, You Can Export Results on Excel Sheet" );
            jQuery( ".loader_show" ).fadeOut();
            jQuery( "#message" ).fadeOut();
            jQuery( "input.hidden_val" ).val("D");
            jQuery( "input.export_csv" ).removeAttr( "disabled" );
        }
        );
    });
//==================== [PHPdev5] Add New Meta =========================
    jQuery( ".add_btn" ).on( 'click', function(e) {
        e.preventDefault();
        meta_key_data();
        //block UI
        jQuery.blockUI(
                {
                    message: '<h2><img src="../wp-content/plugins/search-plus-export-users/images/image.gif" />&nbsp;Please Wait ...</h2>'
                });
        var add = "";
        add = '<div class="append_divs"><select name="meta_name[]" class="meta_name primary"></select>' +
                '<select name="meta_compare[]" class="meta_compare primary">' +
                '<option value="="> = </option>' +
                '<option value="!=">!= </option>' +
                '<option value=">"> > </option>' +
                '<option value=">="> >= </option>' +
                '<option value="<"> < </option>' +
                '<option value="<="> <= </option>' +
                '<option value="LIKE"> LIKE </option>' +
                '<option value="NOT LIKE"> NOT LIKE </option>' +
                '<option value="IN"> IN </option>' +
                '<option value="NOT IN"> NOT IN </option>' +
                '<option value="BETWEEN"> BETWEEN </option>' +
                '<option value="NOT BETWEEN"> NOT BETWEEN </option>' +
                '<option value="EXISTS"> EXISTS </option>' +
                '<option value="NOT EXISTS"> NOT EXISTS </option>' +
                '<option value="REGEXP"> REGEXP </option>' +
                '<option value="NOT REGEXP"> NOT REGEXP </option>' +
                '<option value="RLIKE"> RLIKE </option>' +
                '</select>' +
                '<span><input type="text" style="margin-left:4px;" name="meta_value[]" class="meta_value primary" placeholder="Search for value"></span>' +
                '<span><input type="button" class="removemeta_btn button-secondary" value="-" /></span></div>';
        jQuery( ".div_add" ).append( add );
        setTimeout( jQuery.unblockUI, 100 );
    });

//================= [PHPdev5] Search for users based on added value =====================
    jQuery( '.btn_get' ).on( 'click', function(e) {
        e.preventDefault();
        jQuery( ".hidden_val" ).removeAttr( "value" );
        var op = jQuery( "input.op_args:checked" ).val();
        var select_data = "";
        var input_data = "";
        var compare_value = "";
        select_data = jQuery( ".meta_name" ).map(function() {
            return jQuery( this ).val();
        }).get();

        input_data = jQuery( ".meta_value" ).map(function() {
            return jQuery( this ).val();
        }).get();

        compare_value = jQuery( ".meta_compare" ).map(function() {
            return jQuery( this ).val();
        }).get();

        var role_value = jQuery( ".user_role" ).val();
        jQuery.post(
                MyAjax.ajaxurl,
                {
                    action: 'append_meta',
                    security: jQuery( '#bk-ajax-nonce' ).val(),
                    meta_name: select_data,
                    meta_value: input_data,
                    meta_compare: compare_value,
                    op_args: op,
                    user_role: role_value,
                },
                function( appenddata ) {
                    if (appenddata == -1) {
                        jQuery( ".loader_show" ).fadeIn();
                        jQuery( ".show_content" ).html( "<td>no data found</td>" );
                        jQuery( ".no_items" ).text( "0 item" );
                        jQuery( ".loader_show" ).fadeOut();
                        jQuery( "#message" ).addClass( "error" );
                        jQuery( "#message" ).show();
                        jQuery( "input.export_csv" ).attr( "disabled", "disabled" );
                        jQuery( ".notice-success p" ).text( "No Data Found" );
                    }
                    else {
                        var data;
                        jQuery( "#message" ).removeClass( "error" );
                        jQuery( "input.export_csv" ).removeAttr( "disabled" );
                        jQuery( ".loader_show" ).fadeIn();
                        var i;
                        for ( i in appenddata )
                        {
                            data += "<tr class='results'><td>" + appenddata[i].id + "</td>";
                            data += "<td>" + appenddata[i].username + "</td>";
                            data += "<td>" + appenddata[i].email + "</td></tr>";

                        }
                        jQuery( ".show_content" ).html( data );
                        var count = jQuery( ".results" ).length;
                        if ( count == 0 ) {
                            jQuery( ".no_items" ).text( "0 item" );
                        }
                        else if (count == 1) {
                            jQuery( ".no_items" ).text( "1 item" );
                        }
                        else {
                            jQuery( ".no_items" ).text( count + " " + "items" );
                        }
                        jQuery( "#message" ).show();
                        jQuery( ".notice-success p" ).text( "Now, You Can Export Results on Excel Sheet" );
                        jQuery( ".loader_show" ).fadeOut();
                    }
                }
        );
    });

//=============== [PHPdev5] delete meta options ==================
    jQuery( ".removemeta_btn" ).live( 'click', function(e) {
        e.preventDefault();
        jQuery( this ).parent().parent().remove();
    });


});
/*======================= END of Scripts =======================*/ 