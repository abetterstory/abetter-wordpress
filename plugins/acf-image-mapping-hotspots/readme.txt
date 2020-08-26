=== ACF: Image Hotspots Field ===
Contributors: rockwell15, eridesign
Tags: image mapping, hot spots, image coordinates
Requires at least: 3.5
Tested up to: 4.7
Stable tag: 0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Advanced Custom Fields add-on to allow the capturing of coordinates on an image, based on user clicks.

== Description ==

How to Use:

* Install plugin
* Create a custom field of this type & link it to an image field
* Go to the admin section you made the field for & add an image to the linked field
* The image will then load in the image mapping field, click to capture the coordinates, relative to the image
* Coordinates are stored as comma separated strings

TODO:

* Square mapping
* Polygon mapping



= Compatibility =

This ACF field type is compatible with:
* ACF 5

== Installation ==

1. Copy the `acf-relative_coordinates` folder into your `wp-content/plugins` folder
2. Activate the Image Hotspots plugin via the plugins admin page
3. Create a new field via ACF and select the Image Hotspots type
4. Please refer to the description for more info regarding the field type settings

== Screenshots ==
1. This shows the setup in the ACF settings for a hotspot field
2. This shows the setup in the ACF generated admin fields
3.
4. This shows the final product of what the hotspot field can produce
5. This illustrates that the linked image can be a sibling to the hotspot field itself, or any of it's repeater parents

== Changelog ==

= 1.0.0 =
* Initial Release.