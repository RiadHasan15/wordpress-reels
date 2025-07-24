# BuddyPress Reels Enhanced

A lightweight, shortcode-based WordPress plugin for creating Instagram-style vertical video reels with BuddyPress integration.

## Features

✅ **Pure Shortcode System** - No custom post types or unwanted URLs  
✅ **Instagram-Style Interface** - Vertical scrolling, tap to pause, global mute controls  
✅ **Multiple Display Modes** - Feed, profile, and grid layouts  
✅ **BuddyPress Integration** - Activity stream integration and user profiles  
✅ **Mobile Optimized** - Touch-friendly controls and responsive design  
✅ **Theme Compatible** - Works with any WordPress theme  

## Installation

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin through the WordPress admin
3. Configure settings at **Settings > BP Reels**
4. Use shortcodes on any page or post

## Shortcodes

### Upload Form
```php
[bpr_upload_form]
```
Displays a video upload form for logged-in users.

### Vertical Feed
```php
[bpr_reels_feed count="20"]
```
Instagram-style vertical scrolling feed. Parameters:
- `count` - Number of reels to show (default: 20)
- `user_id` - Show reels from specific user only
- `orderby` - Sort order (default: date)

### Profile Feed
```php
[bpr_profile_feed user_id="123" posts_per_page="10"]
```
Profile-optimized feed with user stats. Parameters:
- `user_id` - User ID (auto-detects BuddyPress displayed user)
- `posts_per_page` - Number of reels per page (default: 10)

### Grid Layout
```php
[bpr_reels_grid user_id="123" posts_per_page="12"]
```
TikTok-style 3-column grid. Parameters:
- `user_id` - User ID (auto-detects BuddyPress displayed user)  
- `posts_per_page` - Number of reels to show (default: 12)

## Usage Examples

### Basic Setup
Create a page called "Reels" and add:
```php
[bpr_upload_form]
[bpr_reels_feed count="30"]
```

### User Profile Integration
In BuddyPress profile templates, add:
```php
[bpr_reels_grid]
```

### Mixed Content Page
```php
<h2>Latest Reels</h2>
[bpr_reels_feed count="5"]

<h2>Upload Your Own</h2>
[bpr_upload_form]
```

## Technical Details

### Data Storage
- Uses regular WordPress posts with metadata
- `bpr_is_reel` meta field identifies reel posts
- `bpr_video` meta field stores video attachment ID
- No custom post types or database tables

### Video Support
- **Formats**: MP4, WebM, MOV (configurable)
- **Size Limit**: 50MB default (configurable)
- **Controls**: Instagram-style tap-to-pause, global mute

### BuddyPress Integration
- Creates activity stream entries for new reels
- Uses BuddyPress avatars and profile links
- Compatible with BuddyPress themes and templates

## Settings

Access plugin settings at **Settings > BP Reels**:

- **Max File Size** - Upload size limit in MB
- **Allowed Formats** - Comma-separated video formats
- **Autoplay** - Enable/disable video autoplay
- **Default Muted** - Start videos muted (recommended)
- **Enable Comments** - Allow comments on reel posts

## Requirements

- WordPress 5.0+
- PHP 7.4+
- BuddyPress (optional, for enhanced features)

## Browser Support

- Chrome 60+
- Firefox 55+
- Safari 11+
- Edge 79+

## License

This plugin is released under the GPL v2 license.