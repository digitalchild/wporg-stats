## WPOrg Stats ## 

A simple WordPress plugin to display statistics from a WordPress.org plugin. The information is retrieved from https://api.wordpress.org/plugins/info/1.0/{your-slug}.json

Results are cached for 12 hours to ensure optimal loading times for the site. 


# Usage # 

- Plugins 

Show total downloads 

[wpps_show_plugin_info slug="wordpress-org-slug-here"] 

Show any other info from the plugin that is available from the API.

[wpps_show_plugin_info slug="wordpress-org-plugin-slug-here" info="see below" ] 

This will show the stat that you define as per the json object returned from https://api.wordpress.org/plugins/info/1.0/{your-slug}.json 

The following info are available. 

* name 
* slug
* version 
* author
* author_profile
* contributors 
* requires
* tested
* compatibility
* rating
* ratings (array of 1-5 rating counts)
* num_ratings (total ratings count)
* support_threads (total support threads)
* support_threads_resolved ( total support threads resolved )
* downloaded (total number of dowonloads)
* last_updated 
* added
* homepage
* sections
* download_link
* screenshots (array of screen shots with src and caption as sub keys of screenshot number )
* tags (array of tags with tag slugs as keys)
* version (array of versions with version number as keys)
* donate_link 

Example 

Show the Last updated [wpps_show_stat slug="wordpress-org-slug-here" stat="last_updated" ] 

- Themes 

Show total downloads 

[wpps_show_theme_info slug="wordpress-org-theme-slug-here"] 

Show any other info from the theme that is available from the API 

[wpps_show_theme_info slug="wordpress-org-theme-slug-here" info="see below"] 

name
slug
version
preview_url
author
screenshot_url
rating
num_ratings
downloaded
last_updated
homepage
download_link
tags
