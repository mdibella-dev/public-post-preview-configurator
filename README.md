public-post-preview-configurator WordPress project
==================================================

Project description
-------------------
This plugin contains a WordPress plugin in a source folder and additional build and test resources. If you want to use the project files as a WordPress plugin use only the files in the **src** folder!

The official WordPress project is [http://wordpress.org/extend/plugins/public-post-preview-configurator](http://wordpress.org/extend/plugins/public-post-preview-configurator).

Plugin description
------------------
Enables you to configure 'public post preview' WordPress plugin with a user interface.

With this plugin it's possible to configure the expiration time of a link provided by the 'public post preview' plugin.

Build
-----
This project contains files to use **Grunt**. With **grunt wppot** you can update the .pot file according to the source code.

Test
----
This projects contains several **behat** specs. A folder **install** with several files and a proper **behat.yml** is necessary. In **install** there must exist:

*  WordPress installation file (for the test a German installation file)
*  Plugin installation files
*  SQLite database file