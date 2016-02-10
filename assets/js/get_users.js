/*==================== Scripts for Search plus Export Users plugin =====================*/

var chck;

//================== [PHPdev5] function to get meta key ====================
function meta_key_data() {
    jQuery.post(
            MyAjax.ajaxurl,
            {
                action: 'load-meta',
                security: jQuery( '#bk-ajax-nonce' ).val(),
            },
    function( meta ) {
        var meta_key_menu = '<option value="no_value">Select Meta Key</option>';
        var i;
        for ( i in meta ) {
            meta_key_menu += '<option value="' + meta[i].meta_key + '">' + meta[i].meta_key + '</option>';
        }
        jQuery( ".meta_name" ).last().html( meta_key_menu );
    }
    );
}

//================= [PHPdev5] function to Get Users'Role ====================
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

//======== [PHPdev5] function to Export Action for Users Table Lists =========
function export_users_lists() {
    if( jQuery( 'input.id_users' ).val() !="" ) {
        jQuery( 'input.export_csv' ).removeAttr( 'disabled' );
    }
    var ids = jQuery('.id_users').val();
    jQuery.post(
        MyAjax.ajaxurl,
        {
            action: 'export-users-lists',
            ids: ids
        },
        function( usersdata ) {
            // console.log(usersdata);
            if( usersdata != -1 ) {   
                var data = '';
                var i;
                for ( i in usersdata )
                {
                    data += "<tr class='results'>";
                    jQuery.each(usersdata[i], function(key, item) {
                        if( item.length == 0 )
                            data += "<td> - </td>";
                        else
                            data += "<td>" + item + "</td>";
                    });
                    data += "</tr>";
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
            }
            else {
              jQuery( ".show_content" ).html("<td>No Data</td>");  
            } 
        }
    );
}

//======== [PHPdev5] function to getnerate input field when input checked =========
    function createInput( chck ) {
        if( jQuery( chck ).is( ':checked' ) ) {
            jQuery('<input>', {
                id: 'meta_enter',
                class: 'meta_enter',
                name: 'meta_enter[]',
                type: 'text'

            }).appendTo( 'p.checkable_options' );
        }
        else if( jQuery( chck ).not( ':checked' ) ) {
            jQuery('input.meta_enter').remove();
        }
    }

//======== [PHPdev5] function to load usermeta for SOL =========
/*    function load_sol() {
        var file_data = jQuery('input.form-control').val();
        jQuery.post(
        MyAjax.ajaxurl,
        {
            action: 'load_usermeta_sol',
            file_data: file_data,

        },
        function( role ) {
            console.log(role);
            // jQuery( "#my-select" ).html( role );
        }
        );
    }*/

jQuery(document).ready(function($) {

    
/*    jQuery('input#load_header').live('click',function(e) {
        e.preventDefault();
        load_sol();
    });*/

    jQuery('#my-select').searchableOptionList({
        showSelectAll: true,
        texts: {
                noItemsAvailable: 'no usermeta found',
                selectAll: 'Select all usermeta',
                selectNone: 'Select none',
                searchplaceholder: 'Click to select usermeta'
        }
    });

//================= [PHPdev5] Call Function createInput(chck) ==============  
   createInput(chck);

//================= [PHPdev5] Call Function export_users_lists() ==============  
    export_users_lists();

//================= [PHPdev5] Call Function meta_key_data() ===================   
    meta_key_data();

//================= [PHPdev5] Call Function get_users_role() ==================
    get_users_role();

//===================== [PHPdev5] used to get all users =======================
    jQuery( '.btn_get_all' ).on( 'click', function(e) {
        e.preventDefault();
        jQuery('.id_user').removeAttr('value');
        jQuery('.id_users').removeAttr('value');
        jQuery('.export_by_id').removeAttr('value');
        jQuery.post(
            MyAjax.ajaxurl,
            {
                action: 'get_all_users',
                security: jQuery( '#bk-ajax-nonce' ).val(),
            },
        function( userdata ) {
            var data = '';
            var data = '';
            var i;
            jQuery( ".loader_show" ).fadeIn();
            for ( i in userdata )
            {
                data += "<tr class='results'>";
                jQuery.each(userdata[i], function(key, item) {
                    if( item.length == 0 )
                        data += "<td> - </td>";
                    else
                        data += "<td>" + item + "</td>";
                });
                data += "</tr>";
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
            jQuery( "#message" ).fadeOut(3000);
            jQuery( "input.hidden_val" ).val("D");
            jQuery( "input.export_csv" ).removeAttr( "disabled" );
        }
        );
    });
//============================= [PHPdev5] Add New Meta =========================
    jQuery( ".add_btn" ).on( 'click', function(e) {
        e.preventDefault();
        meta_key_data();
        //block UI
        jQuery.blockUI(
                {
                    message: '<h2><img src="../wp-content/plugins/search-plus-export-users/assets/images/image.gif" />&nbsp;Please Wait ...</h2>'
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

//=============== [PHPdev5] Search for users based on added value ==============
    jQuery( '.btn_get' ).on( 'click', function(e) {
        e.preventDefault();
        jQuery('.export_by_id').removeAttr( 'value' );
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
                    // console.log(appenddata);
                    if ( appenddata == -1 ) {
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
                        var headerdata;
                        jQuery( "#message" ).removeClass( "error" );
                        jQuery( "input.export_csv" ).removeAttr( "disabled" );
                        jQuery( ".loader_show" ).fadeIn();
                        var i;                        
                        for ( i in appenddata.dataTable )
                        {
                            data += "<tr class='results'>";
                            jQuery.each(appenddata.dataTable[i], function(key, item) {
                                if( item.length == 0 )
                                    data += "<td> - </td>";
                                else
                                    data += "<td>" + item + "</td>";
                            });
                            data += "</tr>";
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
                        jQuery(".export_by_id").val( appenddata.IDs );
                    }
                }
        );
    });

//===================== [PHPdev5] delete meta options =========================
    jQuery( ".removemeta_btn" ).live( 'click', function(e) {
        e.preventDefault();
        jQuery( this ).parent().parent().remove();
    });


});
/*================================== END of Scripts ==================================*/ 