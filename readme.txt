=== Plugin Name ===
Contributors: cmanon, jrfoell, lancewillett
Tags: strava, bicycle, cycling, biking, running, run, swimming, swim, gps, shortcode, widget, plugin
Requires at least: 4.6
Tested up to: 4.7
Stable tag: 1.1.1
License: GPLv2 or later

Show your Strava activity on your WordPress site.

== Description ==

This plugin uses the Strava V3 API to embed maps and activity for
athletes and clubs on your WordPress site. Included are several
widgets and shortcodes for showing maps and activity summaries.

= Shortcodes =

[activity id=NUMBER] - add to any page or post. Also takes the following
optional parameters:

* som - english/metric (system of measure - override from default setting)
* map_width - width (width of image in pixels)
* map_height - height (height of image in pixels)

= Widgets =

Strava Latest Rides - shows a list of the last few activities

Strava Latest Map - shows map of latest activity with option to limit
latest map to activities of a certain minimum distance

== Changelog ==

= 1.2.0 =
Added multi-athlete configuration

= 1.1.1 =
Changes to better support translations through https://translate.wordpress.org
Cleaned up formatting

= 1.1 =
Added [activity] shortcode to deprecate [ride] in the future
Fixed static method call error in shortcode
Added title to Strava Latest Map Widget
Added Lance Willett to contributors
Added target="_blank" to widget hrefs
Added Google Maps Key to settings (required for map images)
Added cache clear option to remove transient & image data
Cleaned up formatting

= 1.0 =
Change to Strava API V3
Switch ride shortcode to use static map

= 0.70 =
Use WordPress HTTP API for all remote calls
Use WordPress Settings API for settings page

= 0.62 =
Refactor some code.
Fixed several bugs.
Added feature to show athlete name/link to the widget if the search option is by club.

= 0.61 =
Added option to select unit of measurements on the widget.

= 0.6 =
Initial version.

