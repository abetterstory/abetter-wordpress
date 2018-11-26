# 0.9
* field group display rules are correctly applied for translated posts now (wpmlbridge-125)
* automatically set translation preferences for repeater subfields based on repeater main field (wpmlbridge-23)
* display original field value during creation of translated post (wpmlbridge-144)
* field set to copy-once is correctly synchronised between languages
* fixed display of custom post types and taxonomies in relationship select boxes when posts and/or taxonomies are set to "display as translated" (acfml-95)
* taxonomy fields inside repeater field are correctly copied now during post duplication (acfml-96)
* ACF attachments fields (images, galleries, files...) has translated metadata on secondary language pages (acfml-88)

# 0.8
* added support for WPML "display translated" mode (wpmlbridge-131)
* fixed issue with reordering repeater field (wpmlbridge-98)
* fixed enqueue scripts notices (wpmlbridge-150)
* fixed support for WYSIWYG fields in Translation Editor (wpmlbridge-90)

# 0.7
* Fields are now synchronised also during standard post creation when has "Copy" set (wpmlbridge-101) 

# 0.6
* Introduced support for clone fields (wpmlbridge-46)

# 0.5.1
* Fixed impossible duplication of field groups (wpmlbridge-91)

# 0.5
* Fixed issue with field group overwriting: fields are no longer duplicated
* Fixed xliff file generation performance (wpmlbridge-25)
* Fixed maximum nesting level error when duplicating repeater field (wpmlbridge-68)

# 0.4
* Fixed problem with returned wrong data type after conversion (one-item arrays retruned as strings)
* Fixed fields dissapearance when translating field groups
* Added support for Gallery field

# 0.3

* added support for ACF Pro
* convert() method now returns original object id if translation is missing
* fixed not working repeater field

# 0.2

* Moved fix about xliff support from WPML Translation Management to this plugin. If you use xliff files to send documents 
to translation, define WPML_ACF_XLIFF_SUPPORT to be true in wp-config.php file.  

# 0.1

* Initial release
* Fixes issues during post translation with field of types: Post Object, Page Link, Relationship, Taxonomy, Repeater