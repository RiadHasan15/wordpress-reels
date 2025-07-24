# BuddyPress Reels Enhanced - Shortcode Version

## Overview

This is the shortcode-only version of BuddyPress Reels Enhanced (v3.1). Instead of using custom post types and archive pages, this version uses regular WordPress posts with reel metadata and provides maximum flexibility through shortcodes.

## Key Changes from Custom Post Type Version

- ✅ **No Custom Post Type**: Uses regular WordPress posts with `bpr_is_reel` metadata
- ✅ **No Archive Pages**: No automatic `/reels/` archive page
- ✅ **Pure Shortcode Approach**: Display reels anywhere using shortcodes
- ✅ **Full Flexibility**: Place reel feeds in any page, post, or widget area
- ✅ **Same Great Features**: All video functionality, BuddyPress integration, and styling preserved

## Available Shortcodes

### Main Vertical Feed
```
[bpr_reels_feed]
[bpr_reels_feed count="20"]
[bpr_reels_feed user_id="123" count="10"]
```

### Profile Feed (Single Column)
```
[bpr_profile_feed]
[bpr_profile_feed user_id="123" posts_per_page="10"]
```

### Grid View (3-Column TikTok Style)
```
[bpr_reels_grid]
[bpr_reels_grid user_id="123" posts_per_page="12"]
```

### Upload Form
```
[bpr_upload_form]
```

## Usage Examples

### Create a Reels Page
1. Create a new WordPress page (e.g., "Reels")
2. Add the shortcode: `[bpr_reels_feed count="20"]`
3. Publish the page

### Add Reels to User Profiles
In your BuddyPress profile template or using BuddyPress Profile Tabs, add:
```php
echo do_shortcode('[bpr_reels_grid user_id="' . bp_displayed_user_id() . '"]');
```

### Widget Areas
Add reels to sidebars or widget areas using the shortcode widget:
```
[bpr_reels_feed count="5"]
```

## How It Works

### Video Storage
- Videos are uploaded as regular WordPress posts
- Each reel post has metadata:
  - `bpr_video`: Attachment ID of the video file
  - `bpr_is_reel`: Flag marking the post as a reel (`1`)

### BuddyPress Integration
- Automatic activity stream integration
- Profile tab creation (optional)
- User avatar and profile linking

### Features Preserved
- Instagram-style vertical scrolling
- Auto-play on scroll
- Global mute controls
- Video controls and interactions
- Load more functionality
- Responsive design
- BuddyPress activity integration

## Installation & Setup

1. Upload the plugin files to `/wp-content/plugins/buddypress-reels/`
2. Activate the plugin through WordPress admin
3. Configure settings at **Settings > BP Reels**
4. Use shortcodes to display reels wherever needed

## Settings

Configure the plugin at **Settings > BP Reels**:
- Maximum file size (MB)
- Allowed video formats
- Autoplay settings
- Default mute state
- Comment system toggle

## Debug

Use the included `debug-feed.php` file to troubleshoot shortcode rendering and check reel counts.

## Compatibility

- WordPress 5.0+
- BuddyPress 8.0+ (optional but recommended)
- PHP 7.4+

## Benefits of Shortcode Approach

1. **Maximum Flexibility**: Place reels anywhere on your site
2. **No URL Conflicts**: No custom post type URLs to manage
3. **Theme Agnostic**: Works with any theme
4. **Widget Compatible**: Use in any widget area
5. **Page Builder Friendly**: Works with Gutenberg, Elementor, etc.
6. **Custom Layouts**: Create unique reel page layouts

## Migration from Custom Post Type Version

If migrating from the custom post type version:
1. Existing `bpr_reel` posts will continue to work but won't appear in shortcodes
2. New uploads will create regular posts with reel metadata
3. Update any custom templates to use shortcodes instead of archive templates

This version gives you complete control over where and how reels are displayed while maintaining all the powerful video features and BuddyPress integration you expect.