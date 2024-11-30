# Anggota Asosiasi

A WordPress plugin to manage association members along with their SKP (Company Qualification Certificate) status and members data management.

## Description

This plugin provides a complete solution for managing association members, including:

- Member registration and management
- SKP (Company Qualification Certificate) tracking
- Member data management
- Photo/document management
- Document generation capability (requires DocGen Implementation plugin)

## Features

- **Member Management**
  - Add/Edit/Delete members
  - Member photos management
  - Detailed member information tracking
  - Service category assignments

- **SKP Management**
  - Track company SKP status
  - SKP document upload
  - Status history tracking
  - Automatic expiry notifications

- **Admin Interface**
  - Clean and intuitive dashboard
  - Member listing with search and filter
  - Permission management
  - Service category management

- **Document Generation**
  - Integration with DocGen Implementation plugin
  - Certificate generation
  - Company profile generation

## Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher
- DocGen Implementation plugin (for document generation features)

## Installation

1. Upload the `asosiasi` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to the Asosiasi menu to start managing members

## Configuration

1. Go to Asosiasi > Settings
2. Configure organization details
3. Set up service categories
4. Configure user permissions
5. Set up document templates if using document generation features

## Usage

### Adding a New Member

1. Go to Asosiasi > Add Member
2. Fill in the member details
3. Upload required photos
4. Assign service categories
5. Click Save

### Managing SKP

1. Go to member details page
2. Click on SKP Management tab
3. Add or update SKP information
4. Upload SKP documents
5. Track status changes

## Shortcodes

```
[asosiasi_member_list]
```
Displays a list of association members.

Parameters:
- `layout`: 'grid' or 'list' (default: 'list')
- `limit`: Number of members to show (default: -1 for all)

## Developers

### Documentation

Full documentation for developers is available in the `/docs` directory.

### Database Tables

- `wp_asosiasi_members` - Member information
- `wp_asosiasi_services` - Service categories
- `wp_asosiasi_member_services` - Member-service relationships
- `wp_asosiasi_skp_perusahaan` - SKP records
- `wp_asosiasi_member_images` - Member photos
- `wp_asosiasi_skp_status_history` - SKP status changes

### Hooks & Filters

Documentation for available hooks and filters can be found in the developer documentation.

## License

GPL v2 or later

## Credits

Developed by Arisciwek