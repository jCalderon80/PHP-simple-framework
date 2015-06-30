# Simple PHP Framework
## Functions and classes

[By Johnny Calderon](http://iguana-web.net)

[Released under GPL ](http://www.gnu.org/licenses/gpl-3.0.txt)

Over time I have created functions to aid myself in the creation of website, I have put all those together and developed a little framework for myself, I love php so this is the base of this little framework, it uses json to store some data and blog content, I like to keep it simple so I haven't included the use of a database at the moment but soon I will integrate it and make switchable depending on the needs.

## VARIABLES & CONSTANTS

### START_TIME
Records the starting time in microseconds

### PRODUCTION
set true when deploying to production, false for development.

### $root
If *PRODUCTION* is false will direct to the 'dev_url' parametere in config-site.json, otherwise will direct to 'url'.

### $config_params
Stores the data returned by function break_config().

### $site_errors**
Stores error data

 - _**NOTE:**_ _At the moment not all functions store errors._


----

## CONFIGURATION FUNCTIONS

### site_config ( $action[string], $settings[array] )

 - **Description.** Creates or updates the main configuration file, and retrieves data from it.

- **Parameters.**
  - **$action** [Default]:info | create | update.

  - **$settings** Required when using $action create and update

        *At the moment there are no defaults for any of the settings all must be defined when using '$action=create'*.
    - *charset* - Website charset.
    - *url* - Website production url.
    - *lang* - Website base language.
    - *dev_url* - I usually keep the core files under a separate folder.

```php:
<?php
    // Create configuration file
    $settings = array ( 'charset'=>'utf-8', 'url'=>'example.com', 'lang'=>'en', 'dev_url'=>'localhost:8080/mysite' );
    site_config( 'create', $settings );
?>```

```php:
<?php
    // Update configuration file
    $new_settings = array( 'lang'=>'es' );
    site_config( 'update', $new_settings );
?>
```

```php:
<?php
    // Retrieve configuration data into an HTML table
    site_config();
?>
```

### break_config()

- **Description.**  Returns the configuration file data in an associative array.

### gzip_comp()

- **Description** perform GZIP compression.

----
## SITE DATA FUNCTIONS

### get_site\_lang()

### get_current\_file()

### get_site\_url()

### get_requested\_url()

### get_page\_id()

### get_page\_title()

### get_header()

### get_footer()

### get_sidebar()

----
## MARKUP GENERATORS

### add_meta\_tags()

### add_style\_tags()

### add_script\_tags()

### add_navbar()

### add_image()

### embed_svg()

### create_gallery\_from()

### add_contact_form()

----

## FILE HANDLER FUNCTIONS

### get_images\_from()

### get_json\_content()

----
## ANALYTICS

### add_analytics()

----

## SOCIAL API FUNCTIONS

### social_init()

### likes_bar()

### share_bar()

### fb_pagefeed()

### get_fb\_counts()

----

## BLOG / ARTICLES FUNCTIONS

### get_article\_list()

### get_articleID\_list()

### get_article\_data()

### get_article()

### get_article\_header()

### get_article\_title()

### get_article\_excerpt()

### get_article\_image()

### get_article\_date()

### get_article\_author()

### get_article\_authorpage()

### get_article\_status()

### get_article\_url()

----
## CLASSES

### class paginate

----

## PROCESS & ERROR HANDLER FUNCTIONS

### get_errors()

### get_process\_time()