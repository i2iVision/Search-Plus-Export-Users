<div class="wrap">
    <h1>Notes</h1> 
    <!--show message-->
    <div id="message" class="updated notice notice-success below-h2">
        <p></p>
    </div>
    <form name="user_setting" method="post" >
        <?php wp_nonce_field('notes-users'); ?>
        <h3>Notes for Users</h3>
        <div><textarea class="notes_users" name="notes_users" placeholder="Enter Notes for Users...." ><?php echo esc_attr(get_option('notes-users')); ?></textarea></div>
        <input type="submit" class="save_notes button-primary" name="save_notes" value="Save" />
    </form>
</div>
