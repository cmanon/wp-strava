=== WP-Strava ===

Contributors: cmanon, jrfoell, lancewillett, dlintott
Tags: strava, activity, bicycle, cycling, biking, running, run, swimming, swim, gps, shortcode, widget, plugin
Requires at least: 4.6
Tested up to: 4.9
Stable tag: 1.4.3
Requires PHP: 5.2
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

[ride] - an alias for [activity] that will accept the same parameters (kept for backwards compatibility).

[route id=NUMBER] - add to any page or post. Shows a summary of the activity plus a map if a google maps key has been added.

This also takes the same optional parameters as the [activity] shortcode above.

[activities] - shows a list of recent activities the same way the "Strava Latest Activities List" does, but with a shortcode rather than a widget. Takes the following optional parameters:

* som - english/metric (system of measure - override from default setting).
* quantity - number of activities to show.
* athlete_token - specify a different athlete (you can copy this value from https://www.strava.com/settings/api or the wp-strava settings page at /wp-admin/options-general.php?page=wp-strava-options).
* strava_club_id - Will display activity from the specified Strava club ID instead of an athlete.


= Widgets =

Strava Latest Activity List - shows a list of the last few activities.

Strava Latest Map - shows map of latest activity with option to limit latest map to activities of a certain minimum distance.

== Frequently Asked Questions ==

= Why am I getting "ERROR 401 Unauthorized"? =

When you have multiple athletes saved, the first is considered to be the default athlete. If you use a shortcode to display activity from anyone other than the default athlete, you must add the athlete token (found on the wp-strava settings page) to the shortcode, such as athlete_token=c764a2b199cff281e39f24671760c1b9c9fe005e

= Why is my Google Map not showing up? =

If your API key works with other Google Maps plugins but not WP Strava, you may need to enable the "Static Maps" functionality on your google account. This is especially true for people using G Suite accounts (not just a @gmail.com address). While logged into your G Suite email, visit https://console.developers.google.com/apis/library/static-maps-backend.googleapis.com/?q=static and make sure the "Static Maps API" is enabled. For more details see https://wordpress.org/support/topic/no-data-errors/

== Screenshots ==

1. WP Strava settings - this walks you through connecting the WP Strava plugin to your strava account. You can connect multiple accounts by authenticating each one here. Add your Google Maps key for map display here. You can also set the system of measurement (miles/kilometers) and clear any saved data.
2. Latest Activities List Widget - shows a list of the most recent activities for the selected athlete.
3. Latest Activities List Widget Settings - settings for the Latest Activities List Widget.
4. Latest Map Widget - shows a map of your most recent activity.
5. Latest Map Widget Settings - settings for the Latest Map Widget. You can limit your activity by minimum distance to show only longer efforts.
6. Activity Shortcode - Shows a map of activity with some statistics.
7. Activity Shortcode Settings - An example activity shortcode. The athlete_token parameter is only needed if your site is connected to multiple athlete accounts.
8. Route Shortcode - Shows a map of a route.
9. Route Shortcode Settings - An example route shortcode. Add markers=true to show green/red start stop points.
10. Activities Shortcode - Shows latest athlete activity in a page or post.
11. Activities Shortcode Settings - An example activities shortcode. The athlete_token parameter is only needed if your site is connected to multiple athlete accounts.

== Changelog ==

= 1.5.0 =

Added additional checks for abridged club data to avoid undefined index/property errors https://wordpress.org/support/topic/club-activities-bugs-strava/
Added composer with PSR-0 autoloader (will switch to PSR-4 once WP's PHP 5.2 requirement goes away).
Moved files into appropriate place to support autoloader.
Added WordPress-Extra coding standards rule definition to project

= 1.4.3 =

Fix WPStrava_Activity class not found error.

= 1.4.2 =

Better Club ID support.
Refined cache clearing to include club IDs.
Removed links to 'app.strava.com'
Fixed unclosed href anchor on activity shortcode.

= 1.4.1 =
Fix array indices on map widget

= 1.4.0 =

Added dlintott to contributors.
Fixed non-existent settings js from being enqueued.
Changed all 'ride' styles and functions to 'activity'.
Added inline documentation.
Updated coding standards to WordPress where possible.
Added Screenshots.
Removed target="_blank" from hrefs for accessibility best practices.
Added links from activity and route shortcodes to respective strava page.
Removed all instances of extract().

= 1.3.0 =

Added [route] shortcode and start/finish https://github.com/cmanon/wp-strava/pull/10/
Fixed error with /rides link (should be /activities). https://wordpress.org/support/topic/problem-with-link-4/
Added [activities] shortcode to show list of activity


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

