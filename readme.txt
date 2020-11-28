=== WP-Strava ===

Contributors: cmanon, jrfoell, lancewillett, dlintott, sebastianerb
Tags: strava, activity, bicycle, cycling, biking, running, run, swimming, swim, paddle, kayak, gps, shortcode, widget, plugin
Requires at least: 4.6
Tested up to: 5.5
Stable tag: 2.5.0
Requires PHP: 5.3
License: GPLv2 or later

Show your Strava activity on your WordPress site.

== Description ==

This plugin uses the Strava API to embed maps and activity for athletes and clubs on your WordPress site. Included are several widgets and shortcodes for showing maps and activity summaries.


= Cron =

Using WP-Strava 2.0+ requires a working WordPress cron configuration. By default, WordPress has a built-in cron system to run scheduled events, but it relies on your website getting frequent visitors. The Strava authentication token system expires after 6 hours if not refreshed. If you think your site will not get any visitors over the span on 6 hours, you might want to set up a _real_ cron: https://developer.wordpress.org/plugins/cron/hooking-wp-cron-into-the-system-task-scheduler/. Setting up this sort of cron is beyond the scope of support for this free plugin, so you should seek assistance through your host. Force-running the WordPress cron once an hour is good enough for WP-Strava.


= Blocks =

Strava Activity  - embed an activity in any page or post. Shows a summary of the activity plus a map if a google maps key has been added.

Paste in the full activity URL from Strava, such as https://www.strava.com/activities/1793155844 and click "Embed." The preview map shown in the editor is a static sample image, the actual activity map will be displayed on the front-end. In the side-panel you can selection options to show the image only (without the details table) and to display markers at the start & finish points.

= Shortcodes =

[activity id=NUMBER] - add to any page or post. Shows a summary of the activity plus a map if a google maps key has been added.

You should replace NUMBER with an activity ID from Strava. The easiest way to find it is from a Strava URL like https://www.strava.com/activities/1793155844 - where 1793155844 is the activity ID number.

Also takes the following optional parameters:

* som - english/metric (system of measure - override from default setting).
* map_width - width (width of image in pixels). Note both width and height parameters are limited to 640px except on premium API plans: https://developers.google.com/maps/documentation/maps-static/dev-guide#Imagesizes
* map_height - height (height of image in pixels). See note above on max height.
* client_id - specify a different athlete (you can copy this value from https://www.strava.com/settings/api or the wp-strava settings page at /wp-admin/options-general.php?page=wp-strava-options).
* markers - Display markers at the start/finish point (true/false, defaults to false).
* image_only - Display only the map image and not the table (true/false, defaults to false).

[ride] - an alias for [activity] that will accept the same parameters (kept for backwards compatibility).

[route id=NUMBER] - add to any page or post. Shows a summary of the activity plus a map if a google maps key has been added.

You should replace NUMBER with an route ID from Strava. The easiest way to find it is from a Strava URL like https://www.strava.com/routes/9001676 - where 9001676 is the route ID number.

This also takes the same optional parameters as the [activity] shortcode above.

[activities] - shows a list of recent activities the same way the "Strava Activities List" Widget does, but with a shortcode rather than a widget. Takes the following optional parameters:

* som - english/metric (system of measure - override from default setting).
* quantity - number of activities to show.
* client_id - specify a different athlete (you can copy this value from https://www.strava.com/settings/api or the wp-strava settings page at /wp-admin/options-general.php?page=wp-strava-options).
* strava_club_id - Will display activity from the specified Strava club ID instead of an athlete.
* date_start - Will display activities after specified date - must be [PHP DateTime compatible](https://www.php.net/manual/en/datetime.formats.php).
* date_end - Will display activities before the specified date - must be [PHP DateTime compatible](https://www.php.net/manual/en/datetime.formats.php).

[latest_map] - shows a map of your latest activity. Takes the following optional parameters:

* som - english/metric (system of measure - override from default setting).
* distance_min - show only the latest activity longer than this distance in km/mi.
* client_id - specify a different athlete (you can copy this value from https://www.strava.com/settings/api or the wp-strava settings page at /wp-admin/options-general.php?page=wp-strava-options).


= Widgets =

Strava Activities List - shows a list of the most recent activities.

Strava Latest Map - shows map of latest activity with option to limit latest map to activities of a certain minimum distance.


== Frequently Asked Questions ==

= Why am I getting "ERROR 401 Unauthorized"? =

When you have multiple athletes saved, the first is considered to be the default athlete. If you use a shortcode to display activity from anyone other than the default athlete, you must add the athlete token (found on the wp-strava settings page) to the shortcode, such as client_id=17791.


= Why is my Google Map not showing up? =

If your API key works with other Google Maps plugins but not WP Strava, you may need to enable the "Static Maps" functionality on your google account. This is especially true for people using G Suite accounts (not just a @gmail.com address). While logged into your G Suite email, visit https://console.developers.google.com/apis/library/static-maps-backend.googleapis.com/?q=static and make sure the "Static Maps API" is enabled. For more details see https://wordpress.org/support/topic/no-data-errors/


= I recently uploaded an activity, why is it not showing on my site? =

WP-Strava caches activity for one hour so your site doesn't hit the Strava API on every page load. If you recently uploaded activity and want to see it right away, go to the Settings -> Strava in the wp-admin dashboard, check the checkbox labeled "Clear cache (images & transient data)" and then click Save Changes.


- Why can't I remove and add an athlete at the same time? -

On the WP-Strava settings page you cannot currently remove and add another athlete at the same time. This is a known limitation. WP-Strava will remove the athlete(s) that you cleared the ID/Nickname fields for, but the new athlete will no be added. Please complete the add/remove operations as separate save actions on the WP-Strava settings page.


== Screenshots ==

1. WP Strava settings - this walks you through connecting the WP Strava plugin to your Strava account. You can connect multiple accounts by authenticating each one here. Add your Google Maps key for map display here. You can also set the system of measurement (miles/kilometers) and clear any saved data.
2. Strava Activities List Widget - shows a list of the most recent activities for the selected athlete.
3. Strava Activities List Widget Settings - settings for the Strava Activities List Widget.
4. Strava Latest Map Widget - shows a map of your most recent activity.
5. Strava Latest Map Widget Settings - settings for the Latest Map Widget. You can limit your activity by minimum distance to show only longer efforts.
6. Activity Shortcode - Shows a map of activity with some statistics.
7. Activity Shortcode Settings - An example activity shortcode. The client_id parameter is only needed if your site is connected to multiple athlete accounts.
8. Route Shortcode - Shows a map of a route.
9. Route Shortcode Settings - An example route shortcode. Add markers=true to show green/red start/stop points.
10. Activities Shortcode - Shows latest athlete activity in a page or post.
11. Activities Shortcode Settings - An example activities shortcode. The client_id parameter is only needed if your site is connected to multiple athlete accounts.
12. Strava Activity Block - Shows the activity block and options with a placeholder image in the editor.


== Changelog ==

= 2.5.0 =

Fix missing translation domain on "Save Changes" in settings. https://wordpress.org/support/topic/small-fix-in-settings-php-function-print_clear_input
Refined styles for responsive tables https://wordpress.org/support/topic/responsive-strava-activity-table/
Add activity description under image (if set) https://wordpress.org/support/topic/show-activity-description/
Add preview of activity in the block editor using server-side render
Add System of Measure override in Activity Block display options


= 2.4.0 =

Made activity table responsive https://wordpress.org/support/topic/responsive-strava-activity-table/
Fixed issue when reauthorization erases access tokens https://wordpress.org/support/topic/wp-strava-error-401-unauthorized/
Improve output escaping, documentation, and other coding standards


= 2.3.2 =

Added support to not link to activities https://wordpress.org/support/topic/feature-request-make-link-to-activity-optional


= 2.3.1 =

Added Image Only and Display Markers toggles to Activity Block.


= 2.3.0 =

Renamed LatestActivities classes to ActivitiesList.
Added exception handling to authorization process.
Added date_start and date_end to [activities] short code https://wordpress.org/support/topic/activities-shortcode-for-date-range/


= 2.2.0 =

Added rudimentary gutenberg block for single Activity.
Changed all Strava links to HTTPS.
Moved PHP classes from includes/ to src/.

= 2.1.0 =

Updated settings to work with WP 5.3.


= 2.0.1 =

Added [latest_map] shortcode https://wordpress.org/support/topic/show-latest-map-not-in-widget/
Code formatting cleanup, escaping, and input filtering
Added caching to all API requests https://wordpress.org/support/topic/data-not-cached/


= 2.0.0 =

Added new Strava "refresh tokens" ala https://developers.strava.com/docs/oauth-updates/#migration-instructions
Fixed long activity filter https://wordpress.org/support/topic/minimum-distance-on-last-activity-map-widget-not-working/


= 1.7.3 =

Added update notice.


= 1.7.2 =

Added setting to hide elevation.
Fixed hours for activities greater than 24 hours.
Added scale=2 to static map to which allows for greater pixel resolution (up to 1024x1024 at 2x) for Google Maps API Premium Plan subscribers https://developers.google.com/maps/documentation/maps-static/dev-guide#Imagesizes


= 1.7.1 =

Added PHPUnit tests for all System of Measure calculations.
Fixed swimpace calculation.
Fixed seconds display on pace.
Added Hide Activity Time option to hide time display from Latest Activities List.


= 1.7.0 =

Added Sebastian Erb to contributors.
Added Pace support (min/km) and (min/mile) for Activity Shortcode
Added Swimpace support (min/100m) for Activity Shortcode
Added 'image_only' attribute to [activity] and [route] shortcode to optionally remove data table.
Added boolean filtering to shortcodes to prevent false-positive "truthiness" to a shortcode attribute like image_only="false".
Removed 'max-width: none' from activity image to make it responsive.


= 1.6.0 =

Added class autoloader (removed composer autoloader).
Added exception handling and cleaned up error reporting.


= 1.5.1 =

rawurlencode() redirect_uri so authentication works more consistently.
Added FAQ about caching.
Changed to new-style phpcs ignores and ignored some additional lines.
Simplified auth token logic to troubleshoot https://wordpress.org/support/topic/problem-authenticating-with-strava/
Increased API request timeout to 30 seconds.


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

Added Daniel Lintott to contributors.
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


== Upgrade Notice ==

= 2.0.1 =

Adds API caching - speed up your page loads 8^)

= 2.0.0 =

Version 2.0 is mandatory after October 15th, 2019. 2.0 settings upgrade instructions: <a href="https://github.com/cmanon/wp-strava/wiki/2.0-Upgrade">https://github.com/cmanon/wp-strava/wiki/2.0-Upgrade</a>.


= 1.7.3 =

Version 2.0 is mandatory after October 15th, 2019. Try the 2.0 beta: <a href="https://github.com/cmanon/wp-strava/releases">https://github.com/cmanon/wp-strava/releases</a>. 2.0 settings upgrade instructions: <a href="https://github.com/cmanon/wp-strava/wiki/2.0-Upgrade">https://github.com/cmanon/wp-strava/wiki/2.0-Upgrade</a>.
