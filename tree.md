.
├── admin
│   ├── css
│   │   ├── admin-global.css
│   │   ├── certificate-settings.css
│   │   ├── certificate-style.css
│   │   ├── dashboard-style.css
│   │   ├── member-form-style.css
│   │   ├── member-images-style.css
│   │   ├── settings-style.css
│   │   ├── skp-tenaga-ahli
│   │   │   └── skp-tenaga-ahli.css
│   │   └── view-member-style.css
│   ├── js
│   │   ├── admin-global.js
│   │   ├── certificate-script.js
│   │   ├── certificate-settings.js
│   │   ├── dashboard-script.js
│   │   ├── member-form-script.js
│   │   ├── settings-script.js
│   │   ├── skp-tenaga-ahli
│   │   │   ├── skp-tenaga-ahli.js
│   │   │   ├── skp-tenaga-ahli-modal.js
│   │   │   └── skp-tenaga-ahli-status.js
│   │   └── view-member-script.js
│   └── views
│       ├── admin-add-member-page.php
│       ├── admin-edit-member-images.php
│       ├── admin-menu-page.php
│       ├── admin-settings-page.php
│       ├── admin-view-member-additional-info.php
│       ├── admin-view-member-modal-skp-perusahaan.php
│       ├── admin-view-member-modal-status-skp-perusahaan.php
│       ├── admin-view-member-page.php
│       ├── admin-view-member-skp-history.php
│       ├── admin-view-member-skp-perusahaan.php
│       ├── skp-tenaga-ahli
│       │   ├── admin-view-member-modal-skp-tenaga-ahli.php
│       │   ├── admin-view-member-modal-status-skp-tenaga-ahli.php
│       │   └── admin-view-member-skp-tenaga-ahli.php
│       └── tabs
│           ├── tab-permissions.php
│           ├── tab-roles.php
│           └── tab-services.php
├── asosiasi.php
├── assets
│   ├── css
│   │   ├── admin-style.css
│   │   ├── certificate-style.css
│   │   ├── dashboard-style.css
│   │   ├── skp-modal.css
│   │   └── skp-perusahaan.css
│   └── js
│       ├── admin-script.js
│       ├── member-skp-table-reload.js
│       ├── skp-operations.js
│       ├── skp-perusahaan
│       │   ├── skp-perusahaan.js
│       │   ├── skp-perusahaan-modal.js
│       │   ├── skp-perusahaan-status.js
│       │   └── skp-perusahaan-utils.js
│       └── skp-tenaga-ahli
│           ├── skp-tenaga-ahli.js
│           ├── skp-tenaga-ahli-modal.js
│           └── skp-tenaga-ahli-status.js
├── helpers
│   └── asosiasi-helpers.php
├── includes
│   ├── asosiasi-functions.php
│   ├── class-asosiasi-activator.php
│   ├── class-asosiasi-admin.php
│   ├── class-asosiasi-ajax-skp-perusahaan.php
│   ├── class-asosiasi-ajax-status-skp-perusahaan.php
│   ├── class-asosiasi-crud.php
│   ├── class-asosiasi-deactivator.php
│   ├── class-asosiasi-enqueue-member.php
│   ├── class-asosiasi-enqueue.php
│   ├── class-asosiasi-enqueue-settings.php
│   ├── class-asosiasi-enqueue-skp-perusahaan.php
│   ├── class-asosiasi-member-images.php
│   ├── class-asosiasi.php
│   ├── class-asosiasi-public.php
│   ├── class-asosiasi-services.php
│   ├── class-asosiasi-settings.php
│   ├── class-asosiasi-skp-cron.php
│   ├── class-asosiasi-skp-perusahaan.php
│   ├── class-asosiasi-status-skp-perusahaan.php
│   ├── class-asosiasi-upload-directories.php
│   ├── docgen
│   │   ├── assets
│   │   │   ├── css
│   │   │   │   └── asosiasi-docgen-style.css
│   │   │   └── js
│   │   │       └── asosiasi-docgen-script.js
│   │   ├── class-docgen-checker.php
│   │   ├── class-host-docgen-adapter.php
│   │   ├── modules
│   │   │   ├── comporo
│   │   │   │   ├── assets
│   │   │   │   │   └── host-docgen-compro-script.js
│   │   │   │   ├── providers
│   │   │   │   │   ├── class-host-docgen-compro-form-provider.php
│   │   │   │   │   └── class-host-docgen-compro-json-provider.php
│   │   │   │   └── views
│   │   │   │       └── host-docgen-compro-page.php
│   │   │   └── member-certificate
│   │   │       ├── class-host-docgen-compro-module.php
│   │   │       └── providers
│   │   │           └── class-member-certificate-provider.php
│   │   └── tree-docgen.md
│   ├── migrations
│   │   └── add-service-id-to-skp-perusahaan.php
│   └── skp-tenaga-ahli
│       ├── class-asosiasi-ajax-skp-tenaga-ahli.php
│       ├── class-asosiasi-ajax-status-skp-tenaga-ahli.php
│       ├── class-asosiasi-skp-tenaga-ahli.php
│       └── class-asosiasi-status-skp-tenaga-ahli.php
├── languages
│   ├── asosiasi-en_US.mo
│   └── asosiasi-en_US.po
├── LICENSE
├── public
│   ├── css
│   │   └── asosiasi-public.css
│   ├── js
│   │   └── asosiasi-public.js
│   └── views
│       └── public-member-list.php
├── README.md
├── sql
│   ├── certificate-log.sql
│   ├── member-images.sql
│   ├── members.sql
│   ├── services.sql
│   ├── skp-perusahaan.sql
│   ├── skp-tenaga-ahli.sql
│   └── status-history.sql
├── templates
│   └── member-certificate-template.docx
├── tree.md
└── uninstall.php

35 directories, 105 files
