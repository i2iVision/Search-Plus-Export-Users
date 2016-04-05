=== Search Plus Export Users ===
Auther: i2ivision ( PHPdev5 )
Version: 1.2
Plugin URL: www.i2ivision.com
Tags: search,filter,export,csv,file,users,meta,keys,role,operation,import,options,dropbox
Requires at least: 3.0
Tested up to: 4.3
Stable tag: 4.3.1
License: GPLv3

Search Plus Export Users is a plugin for Searching for users with specific keywords and Exporting results in CSV file and newly feature in setting page.

=== Description ===
Search Plus Export Users's Features:

1. you can get all users in your site by clicking "Get All Users" button to return all users on the table with fields:{ ID - User Name - Email }.
2. you can search for users with specifc keywords such as :
	a. choose a "Role" that you want to search with.
	b. choose an operation that you want for search result:
		b1. if "AND", you should search with valid keyword for users because if you type a missing name in "search for value field" that hasn't relationship with key in drop-down menu,no data will return
		b2. if "OR", you should search with valid keyword for users and if you type a missing name in "search for value field",it will return valid result
		and if all search values are valid , it will return all result for all search values you typed.
	c. after that, choose filed from drop-down menu that you want to search with and type search value in "search for value" field next to.
3. you can search with multiple keys and values by clicking "+" button
4. you can remove fields that you added for searching, if you don't need it.
5. then click "Search" button to show results on the table.
6. after searching for users that you want, you can click "Export" button to export results in CSV file.
7. Add some hooks for plugin users :
	a. add_filter( 'export_users_template' , filename.php )
		hook used to change plugin main template ( return String ) 
	b.add_filter( 'speu_add_fields' , users fields array )
		hook used to remove or add new field for users search or export ( return Array )
		Note: array keys must be like users fields name in Users table : ( 'ID', 'user_login', 'user_nicename', 'user_email', 'user_url', 'user_registered', 'user_status', 'display_name' )
		Note: you can't add user password or user activation key
	c. add_filter( 'speu_csv_file_name' , name of csv file )
		hook used to change name of csv file ( return String)
	d. add_filter( 'speu_roles_search' , users roles array )
		hook used to remove or add new role to users role array ( return Array )
8. Create Import option for the exported CSV
	On the import screen make options like :
		- Export users with generic passwords
		- Export users with custom one password for all.
		- Export users without all usermeta 
		- Export users with  some usermeta.
9. Create export option for exported CSV into dropbox account
	on the import screen make option for :
		- Enter configuration for your dropbox api such as (app_id, access_token, etc.).
		- choose the file to export into dropbox.
		- you can choose multiple files to export.

=== Installation ===
1.  Upload your plugin folder to the '/wp-content/plugins/' directory.
2.  Activate the plugin through the 'Plugins/' menu in WordPress.


=== Frequently Asked Questions ===
there is no FAQ just yet.


=== Screenshots ===
There is no screenshots just yet.


=== Changelog ===
= 0.1.5 =
 Beta release

= 1.0 =
 first release

= 1.2 =
 second release

=== Upgrade Notice ===
= 0.1.5 =
 Add bulk action ( export user) on admin users page &
 Add some hooks to the plugin

= 1.0 =
 convert ( Search Plus Export Users ) plugin to object oriented

= 1.1 =
Create Import option for the exported CSV

= 1.2 =
Create export option for exported CSV into dropbox account