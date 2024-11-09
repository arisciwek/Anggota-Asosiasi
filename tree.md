```markdown
# Project Structure
Version: 1.1.0
Last Updated: 2024-03-09

asosiasi/
├── asosiasi.php (v1.2.0)
├── uninstall.php (v1.0.0)
├── readme.txt (v1.0.0)
├── assets/
│   ├── css/
│   │   ├── admin-style.css        # Global admin styles (v1.0.0)
│   │   ├── public-style.css       # Global public styles (v1.0.0)
│   │   ├── dashboard-style.css    # Dashboard specific styles (v1.0.0)
│   │   └── skp-perusahaan.css    # SKP Perusahaan styles (v1.0.0)
│   ├── js/
│   │   ├── admin-script.js        # Global admin scripts (v1.0.0)
│   │   ├── public-script.js       # Global public scripts (v1.0.0)
│   │   ├── dashboard-script.js    # Dashboard specific scripts (v1.0.0)
│   │   └── skp-perusahaan.js     # SKP Perusahaan scripts (v1.0.0)
│   └── images/
│       └── logo.png
├── includes/
│   ├── class-asosiasi-public.php (v1.1.0)
│   ├── class-asosiasi-admin.php (v1.1.0)
│   ├── class-asosiasi.php (v1.1.0)
│   ├── class-asosiasi-activator.php (v1.4.0)
│   ├── class-asosiasi-deactivator.php (v1.1.0)
│   ├── class-asosiasi-crud.php (v1.1.0)
│   ├── class-asosiasi-services.php (v1.0.0)
│   ├── class-asosiasi-skp-perusahaan.php (v1.0.0)
│   ├── class-asosiasi-skp-cron.php (v1.0.0)
│   ├── class-asosiasi-ajax-perusahaan.php (v1.0.0)
│   └── asosiasi-functions.php (v1.0.0)
├── admin/
│   ├── css/
│   │   └── dashboard-style.css    # Dashboard specific styles (v1.0.0)
│   ├── js/
│   │   └── dashboard-script.js    # Dashboard specific scripts (v1.0.0)
│   └── views/
│       ├── admin-menu-page.php (v1.0.0)
│       ├── admin-add-member-page.php (v1.0.0)
│       ├── admin-view-member-page.php (v1.0.0)
│       ├── admin-view-member-modal-skp-perusahaan.php (v1.0.0)
│       ├── admin-view-member-skp-perusahaan.php (v1.0.0)
│       └── admin-settings-page.php (v1.0.0)
├── public/
│   ├── css/
│   │   └── asosiasi-public.css (v1.0.0)
│   ├── js/
│   │   └── asosiasi-public.js (v1.0.0)
│   └── views/
│       └── public-member-list.php (v1.0.0)
└── languages/
    ├── asosiasi.pot
    ├── asosiasi-en_US.mo
    └── asosiasi-en_US.po

Changelog:
1.1.0 - Added SKP Perusahaan feature
- Added new files for SKP Perusahaan functionality
- Added versioning information
- Updated folder structure documentation

1.0.0 - Initial release
- Basic plugin structure
- Member management functionality
```