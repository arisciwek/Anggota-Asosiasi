# Project Structure
Version: 2.1.0
Last Updated: 2024-03-15

asosiasi/
├── asosiasi.php (v2.1.0)
├── uninstall.php (v1.0.0)
├── readme.txt (v1.0.0)
├── assets/
│   ├── css/
│   │   ├── admin-global.css        # Global admin styles (v2.1.0)
│   │   ├── form-style.css          # Form specific styles (v2.1.0)
│   │   ├── view-member-style.css   # Member view styles (v2.1.0)
│   │   └── skp-perusahaan.css     # SKP Perusahaan styles (v1.4.5)
│   └── js/
│       ├── admin-script.js         # Global admin scripts (v2.1.0)
│       ├── form-script.js          # Form handling scripts (v2.1.0)
│       ├── view-member-script.js   # Member view scripts (v2.1.0)
│       └── skp-perusahaan.js      # SKP Perusahaan scripts (v1.4.5)
├── includes/
│   ├── class-asosiasi.php (v2.1.0)
│   ├── class-asosiasi-admin.php (v2.1.0)
│   ├── class-asosiasi-activator.php (v2.2.0)
│   ├── class-asosiasi-deactivator.php (v1.1.0)
│   ├── class-asosiasi-enqueue.php (v1.3.0)
│   ├── class-asosiasi-crud.php (v1.1.0)
│   ├── class-asosiasi-services.php (v1.0.0)
│   ├── class-asosiasi-public.php (v1.1.0)
│   ├── class-asosiasi-member-images.php (v2.1.0)
│   ├── class-asosiasi-skp-perusahaan.php (v1.2.0)
│   ├── class-asosiasi-skp-cron.php (v1.0.0)
│   └── class-asosiasi-ajax-skp-perusahaan.php (v1.4.5)
├── admin/
│   ├── css/
│   │   ├── admin-global.css        # Global admin styles (v2.1.0)
│   │   ├── dashboard-style.css     # Dashboard specific styles (v2.1.0)
│   │   ├── form-style.css          # Form styles (v2.1.0)
│   │   ├── view-member-style.css   # Member view styles (v2.1.0)
│   │   └── skp-modal.css          # SKP modal styles (v1.0.0)
│   ├── js/
│   │   ├── admin-script.js         # Global admin scripts (v2.1.0)
│   │   ├── dashboard-script.js     # Dashboard specific scripts (v2.1.0)
│   │   ├── form-script.js          # Form handling scripts (v2.1.0)
│   │   ├── view-member-script.js   # Member view scripts (v2.1.0)
│   │   └── skp-operations.js       # SKP operations scripts (v1.0.0)
│   └── views/
│       ├── admin-menu-page.php (v2.1.0)
│       ├── admin-add-member-page.php (v2.6.0)
│       ├── admin-edit-member-images.php (v2.1.0)
│       ├── admin-view-member-page.php (v2.2.0)
│       ├── admin-view-member-modal-skp-perusahaan.php (v1.2.0)
│       ├── admin-view-member-skp-perusahaan.php (v1.2.4)
│       ├── admin-settings-page.php (v2.1.2)
│       └── tabs/
│           ├── tab-permissions.php (v2.1.0)
│           ├── tab-roles.php (v2.1.0)
│           └── tab-services.php (v2.1.0)
├── public/
│   ├── css/
│   │   └── asosiasi-public.css (v1.3.0)
│   ├── js/
│   │   └── asosiasi-public.js (v1.3.0)
│   └── views/
│       └── public-member-list.php (v1.3.0)
└── languages/
    ├── asosiasi.pot
    ├── asosiasi-en_US.mo
    └── asosiasi-en_US.po

Changelog:
2.1.0 - 2024-03-15
- Added member images functionality
- Added edit photos page and related assets
- Added permissions and roles management
- Updated tab structure for settings page
- Reorganized admin assets

2.0.0 - 2024-03-13
- Added SKP Perusahaan feature
- Added SKP management interface
- Added modal components
- Enhanced admin views organization

1.3.0 - 2024-03-08
- Added public features
- Enhanced asset organization
- Added enqueue handler

1.0.0 - Initial release
- Basic plugin structure
- Member management functionality