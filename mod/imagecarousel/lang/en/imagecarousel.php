<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    mod_imagecarousel
 * @copyright  2024 Zajuna Team
 * @author     Zajuna Team
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Basic module strings
$string['modulename'] = 'Image Carousel';
$string['modulenameplural'] = 'Image Carousels';
$string['modulename_help'] = 'The Image Carousel module allows you to create image slideshows in your course.';
$string['pluginname'] = 'Image Carousel';
$string['pluginadministration'] = 'Image Carousel Administration';
$string['availability'] = 'Availability';
$string['availablefrom'] = 'Available from';
$string['availablefrom_help'] = 'Select the date and time from which the carousel will be visible to students.';
$string['availableuntil'] = 'Available until';
$string['availableuntil_help'] = 'Select the date and time until which the carousel will remain visible. Leave empty for no end date.';
$string['availableuntil_error'] = 'The "Available until" date/time cannot be earlier than the "Available from" date/time.';
$string['imagecarousel'] = 'Image Carousel';
$string['imagecarouselname'] = 'Name';
$string['imagecarouselname_help'] = 'Name of the image carousel';
$string['imagecarouselsettings'] = 'General settings';

$string['manageimages'] = 'Manage Images';
$string['addnewimage'] = 'Add new image';

// Table strings
$string['position'] = 'Position';
$string['preview'] = 'Preview';
$string['image_url'] = 'Image URL';
$string['text'] = 'Text';
$string['text_url'] = 'Text URL';
$string['actions'] = 'Actions';
$string['moveup'] = 'Move up';
$string['movedown'] = 'Move down';
$string['visibility'] = 'Visibility';
$string['visible'] = 'Visible';
$string['hidden'] = 'Hidden';
$string['image_visibility_enabled'] = 'Image set to visible in the carousel';
$string['image_visibility_disabled'] = 'Image hidden from the carousel';
$string['image_visibility_toggle_error'] = 'The image could not be updated';

// Messages
$string['position_warning'] = 'Note: When using the up/down arrows you can reorder the images. Changes will be applied immediately.';

// Edit form strings
$string['edit_image'] = 'Edit image';
$string['add_image'] = 'Add new image';
$string['image_url_help'] = 'Enter the URL of the image';
$string['text_help'] = 'Text to display over the image';
$string['text_url_help'] = 'URL for the text link (optional)';
$string['save_changes'] = 'Save changes';
$string['cancel'] = 'Cancel';
$string['file_picker'] = 'Upload carousel image';
$string['file_picker_help'] = 'You can upload an image for the carousel or select one from the file picker. The recommended image size is 1920x720 pixels for desktop and 1680x720 pixels for mobile. If you upload a file, the image URL field will be ignored.';
$string['file_area_description'] = 'Carousel image files';
$string['image_requirements'] = 'Carousel Images';
$string['image_url_optional'] = 'Alternatively, you can provide an external image URL';

// Form validation
$string['error_no_image'] = 'You must either upload an image or provide an image URL';
$string['error_invalid_image'] = 'The file must be an image (JPG, PNG or WebP)';
$string['error_empty_title'] = 'The carousel title cannot be empty';

// Text customization
$string['text_customization'] = 'Text customization';
$string['text_color'] = 'Text color';
$string['text_color_help'] = 'Color of the text that appears over the image';
$string['text_size'] = 'Text size';
$string['text_size_help'] = 'Size of the text in em (e.g., 1em = 16px). If you want to use responsive text, you can use em units. If you want to use a fixed size, you can use px units.';
$string['text_size_explanation'] = 'If you want to use responsive text, you can use em units. If you want to use a fixed size, you can use px units.';
$string['text_position'] = 'Base position';
$string['text_position_help'] = 'Select the initial position where the text will be placed on the image';
$string['text_position_custom'] = 'Custom position';
$string['text_position_custom_help'] = 'Custom position values (top, right, bottom, left)';
$string['text_background'] = 'Text background';
$string['text_background_explanation'] = 'Choose the opacity for the text background';
$string['text_padding'] = 'Text padding';
$string['text_padding_help'] = 'Padding around the text (e.g., 5px)';
$string['text_border_radius'] = 'Text border radius';
$string['text_border_radius_help'] = 'Border radius of the text container (e.g., 8px)';

// Text style options
$string['text_style'] = 'Text style';
$string['text_style_help'] = 'Style options for the text';
$string['text_bold'] = 'Bold';
$string['text_italic'] = 'Italic';
$string['text_underline'] = 'Underline';

// Position options
$string['position_top_left'] = 'Top left';
$string['position_top_center'] = 'Top center';
$string['position_top_right'] = 'Top right';
$string['position_center_left'] = 'Center left';
$string['position_center'] = 'Center';
$string['position_center_right'] = 'Center right';
$string['position_bottom_left'] = 'Bottom left';
$string['position_bottom_center'] = 'Bottom center';
$string['position_bottom_right'] = 'Bottom right';
$string['position_custom'] = 'Custom position';

// Position labels
$string['position_top'] = 'Top';
$string['position_right'] = 'Right';
$string['position_bottom'] = 'Bottom';
$string['position_left'] = 'Left';

// Position placeholders
$string['position_top_placeholder'] = 'Distance from top (e.g., 10px)';
$string['position_right_placeholder'] = 'Distance from right (e.g., 20px)';
$string['position_bottom_placeholder'] = 'Distance from bottom (e.g., 30px)';
$string['position_left_placeholder'] = 'Distance from left (e.g., 40px)';

// Position adjustment
$string['position_adjustment'] = 'Fine position adjustment';
$string['position_adjustment_desc'] = 'Adjust text position from base position';
$string['position_adjust_top'] = 'From top';
$string['position_adjust_right'] = 'From right';
$string['position_adjust_bottom'] = 'From bottom';
$string['position_adjust_left'] = 'From left';
$string['position_adjust_top_placeholder'] = '↓↑ ±10px';
$string['position_adjust_right_placeholder'] = '←→ ±10px';
// $string['position_adjust_bottom_placeholder'] = '↑↓ ±10px';
// $string['position_adjust_left_placeholder'] = '← ±10px';
$string['position_adjustment_help'] = 'Adjust the distance from the base position. The first box represents the Y-axis (vertical displacement) and the second box represents the X-axis (horizontal displacement).
Positive values move the text down/right, while negative values move it up/left. NOTE: To keep the text responsive, it is recommended to use em units.';

$string['text_color_opacity'] = 'Text opacity';
$string['text_background_opacity'] = 'Background opacity';

// Default values
$string['default_text_size'] = '1em';
$string['default_text_size_explanation'] = 'If you want to use responsive text, you can use em units. If you want to use a fixed size, you can use px units.';
$string['default_text_padding'] = '0em';
$string['default_text_border_radius'] = '0em';
$string['default_position_top'] = '±0em ↓↑';
$string['default_position_right'] = '±0em ←→';
// $string['default_position_bottom'] = '±0em ↓';
// $string['default_position_left'] = '±0em ←';

// Size units help
$string['size_units_info'] = 'Size units information';
$string['size_units_help'] = 'You can use different CSS units for sizes:
• px (pixels): Fixed size (e.g., 16px)
• em: Relative to parent element size (e.g., 1.2em)
• rem: Relative to root element size (e.g., 1.2rem)
• %: Percentage of parent element (e.g., 120%)
• vw/vh: Relative to viewport width/height (e.g., 5vw)';

$string['delete_image'] = 'Delete image';
$string['delete_image_confirmation'] = 'Confirm deletion';
$string['delete_image_confirmation_desc'] = 'Are you sure you want to delete this image from the carousel?';
$string['image_deleted'] = 'Image deleted successfully';
$string['delete_error'] = 'Error deleting image';
$string['image_moved_up'] = 'Image moved up';
$string['image_moved_down'] = 'Image moved down';
$string['save_changes'] = 'Changes saved successfully';

$string['file_upload'] = 'Upload image';

// Support for desktop and mobile versions
$string['images_section'] = 'Carousel images';
$string['desktop_image_label'] = 'Desktop Image';
$string['desktop_image_info'] = 'This image will be displayed on large screen devices (computers, tablets). The recommended resolution is 1920x720 pixels.';
$string['desktop_image_info_help'] = 'Shown on large screens. Recommended resolution: 1920x720 px.';
$string['mobile_image_label'] = 'Mobile Image';
$string['mobile_image_info'] = 'This image will be displayed on mobile devices. If not provided, the desktop image will be used on all devices. The recommended resolution is 1680×720 pixels (horizontal for mobile).';
$string['mobile_image_info_help'] = 'Shown on mobile devices. Recommended resolution: 1680x720 px.';
$string['image_file_desktop'] = 'Upload desktop image';
$string['image_desktop'] = 'Or use URL for desktop image';
$string['image_desktop_help'] = 'Here you can enter a URL for the desktop image';
$string['image_file_mobile'] = 'Upload mobile image';
$string['image_mobile'] = 'Or use URL for mobile image';
$string['image_mobile_help'] = 'Here you can enter a URL for the mobile image';
$string['image_mobile_url'] = 'Here you can enter a URL for the mobile image';
$string['error_no_desktop_image'] = 'You must upload an image or provide a URL for the desktop version';
$string['error_text_word_limit'] = 'Text cannot exceed 150 words';

// Capabilities
$string['imagecarousel:addinstance'] = 'Add a new image carousel';
$string['imagecarousel:view'] = 'View image carousel';
$string['imagecarousel:manageitems'] = 'Manage carousel images';

// Carousel images
$string['carouselimages'] = 'Carousel images';
$string['uploadimage'] = 'Upload image';
$string['desktopimage'] = 'Desktop image';
$string['desktopimage_desc'] = 'This image will be displayed on desktop devices. Recommended size: 1920x720 pixels.';
$string['desktopimage_url'] = 'Alternative URL for desktop image';
$string['mobileimage'] = 'Mobile image';
$string['mobileimage_desc'] = 'This image will be displayed on mobile devices. Recommended size: 1680x720 pixels.';
$string['mobileimage_url'] = 'Alternative URL for mobile image';
$string['error_invalid_image'] = 'The file is not a valid image. Accepted formats: JPG, PNG, WEBP.';

// Image actions
$string['addimage'] = 'Add new image';
$string['editimage'] = 'Edit image';
$string['deleteimage'] = 'Delete image';
$string['deleteimageconfirm'] = 'Are you sure you want to delete this image?';
$string['imagesaved'] = 'Image saved successfully';

// Text for images
$string['textsection'] = 'Text and link';
$string['texturl'] = 'URL when clicking on the text';
$string['textcolor'] = 'Text color';
$string['textcoloropacity'] = 'Text color opacity';
$string['textsize'] = 'Text size';
$string['textposition'] = 'Text position';
$string['textposition_top'] = 'Top position';
$string['textposition_right'] = 'Right position';
$string['textposition_bottom'] = 'Bottom position';
$string['textposition_left'] = 'Left position';
$string['textbackground'] = 'Text background color';
$string['textbackgroundopacity'] = 'Text background opacity';
$string['textpadding'] = 'Text padding';
$string['textborderradius'] = 'Text border radius';
$string['textstylebold'] = 'Bold';
$string['textstyleitalic'] = 'Italic';
$string['textstyleunderline'] = 'Underline';

// Predefined positions
$string['position_topleft'] = 'Top left';
$string['position_topcenter'] = 'Top center';
$string['position_topright'] = 'Top right';
$string['position_middleleft'] = 'Middle left';
$string['position_middlecenter'] = 'Middle center';
$string['position_middleright'] = 'Middle right';
$string['position_bottomleft'] = 'Bottom left';
$string['position_bottomcenter'] = 'Bottom center';
$string['position_bottomright'] = 'Bottom right';
$string['position_custom'] = 'Custom';

// General settings
$string['width'] = 'Carousel width';
$string['width_help'] = 'Width of the image carousel. Eg: 100%, 800px';
$string['height'] = 'Carousel height';
$string['height_help'] = 'Height of the image carousel. Eg: 400px';
$string['autoplay'] = 'Autoplay';
$string['autoplay_help'] = 'Images will automatically change after a certain time';
$string['autoplaytime'] = 'Autoplay time';
$string['autoplaytime_help'] = 'Time in milliseconds to change to the next image. Eg: 5000 (5 seconds)';
$string['showcontrols'] = 'Show controls';
$string['showcontrols_help'] = 'Show navigation buttons for the carousel';
$string['showindicators'] = 'Show indicators';
$string['showindicators_help'] = 'Show position indicators on the carousel';
$string['imagesfound'] = '{$a} images found.';

// Errors
$string['error_text_overflow'] = 'Text is too long. Maximum 255 characters.';
$string['error_invalid_url'] = 'The provided URL is not valid. Make sure it starts with http:// or https://';
$string['error_desktop_height'] = 'The desktop image is recommended to be 1920x720 pixels.';

// Form field indications
$string['required_field'] = 'REQUIRED';

// Others
$string['manage'] = 'Manage images';
$string['manage_images'] = 'Manage carousel images';

// Additional errors
$string['no_image_specified'] = 'No image specified for editing';
$string['image_not_found'] = 'The requested image does not exist';
$string['invalid_action'] = 'Invalid action';
$string['noimagesfound'] = 'No images found in this carousel';
$string['error_no_file_uploaded'] = 'No uploaded file found. Please select an image.';
$string['error_no_desktop_image'] = 'You must upload an image or provide a URL for the desktop version';
$string['error_no_image'] = 'You must upload at least one image (desktop or mobile) or provide a URL';

// Image deletion
$string['delete_image_title'] = 'Delete image';
$string['delete_image_confirm'] = 'Are you sure you want to delete this image? This action cannot be undone.';
$string['image_deleted'] = 'Image deleted successfully.';
$string['error_deleting_image'] = 'Error while deleting the image.';

// Image creation and updating
$string['image_added'] = 'Image added successfully.';
$string['image_updated'] = 'Image updated successfully.';
$string['imagesaved'] = 'Image saved successfully.';

// Current images section
$string['current_images'] = 'Current images';
$string['desktop_image_label'] = 'Desktop image';
$string['mobile_image_label'] = 'Mobile image';

// Validation errors
$string['invalid_image_id'] = 'Invalid or unspecified image ID';
