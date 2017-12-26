=== Plugin Name ===
Contributors: cmanon, jrfoell, lancewillett
Tags: strava, bicycle, cycling, biking, running, run, swimming, swim, gps, shortcode, widget, plugin
Requires at least: 4.6
Tested up to: 4.9
Stable tag: 1.2.0
License: GPLv2 or later

Show your Strava activity on your WordPress site.

== Description ==

This plugin uses the Strava API to embed maps and activity for athletes and clubs on your WordPress site. Included are several widgets and shortcodes for showing maps and activity summaries.

= Shortcodes =

[activity id=NUMBER] - add to any page or post. Shows a summary of the activity plus a map if a google maps key has been added.

Also takes the following optional parameters:

* som - english/metric (system of measure - override from default setting).
* map_width - width (width of image in pixels).
* map_height - height (height of image in pixels).
* athlete_token - specify a different athlete (you can copy this value from https://www.strava.com/settings/api or the wp-strava settings page at /wp-admin/options-general.php?page=wp-strava-options).
* markers - Display markers at the start/finish point (true/false, defaults to false).

[ride] is an alias for [activity] and will accept the same parameters (kept for backwards compatibility).

[route id=NUMBER] - add to any page or post. Shows a summary of the activity plus a map if a google maps key has been added.

This also takes the same optional parameters as the activity shortcode above.

= Widgets =

Strava Latest Activity List - shows a list of the last few activities.

Strava Latest Map - shows map of latest activity with option to limit latest map to activities of a certain minimum distance.

== Changelog ==

= 1.3.0 =
Added [route] shortcode and start/finish https://github.com/cmanon/wp-strava/pull/10/
Fixed error with /rides link (should be /activities). https://wordpress.org/support/topic/problem-with-link-4/

= 1.2.0 =
Added multi-athlete configuration. https://wordpress.org/support/topic/multi-strava-user/
Additional transitions from Ride -> Activity.
Updated setup instructions to reflect latest Strava API set up process.
Backwards Compatibility - removed PHP 5.3+ specific operator (should work with PHP 5.2 now - versions 1.1 and 1.1.1 don't). https://wordpress.org/support/topic/version-1-1-broken/
Reworked error reporting and formatting. https://wordpress.org/support/topic/updating-settings-failure/#post-9764942

= 1.1.1 =
Changes to better support translations through https://translate.wordpress.org.
Cleaned up formatting.

= 1.1 =
Added [activity] shortcode to deprecate [ride] in the future.
Fixed static method call error in shortcode.
Added title to Strava Latest Map Widget. https://wordpress.org/support/topic/change-widget-title-from-latest-ride-to-latest-run-or-something-else/
Added Lance Willett to contributors.
Added target="_blank" to widget hrefs.
Added Google Maps Key to settings (required for map images). https://wordpress.org/support/topic/the-google-maps-api-server-rejected-your-request-3/
Added cache clear option to remove transient & image data.
Cleaned up formatting.

= 1.0 =
Change to Strava API V3. https://wordpress.org/support/topic/does-not-work-354/
Switch ride shortcode to use static map.

= 0.70 =
Use WordPress HTTP API for all remote calls.
Use WordPress Settings API for settings page.

= 0.62 =
Refactor some code.
Fixed several bugs.
Added feature to show athlete name/link to the widget if the search option is by club.

= 0.61 =
Added option to select unit of measurements on the widget. https://wordpress.org/support/topic/feature-request-runs-in-minkm/

= 0.6 =
Initial version.

