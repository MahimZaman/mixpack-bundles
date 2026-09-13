<?php

defined('WP_UNINSTALL_PLUGIN') || exit;

delete_option('mixpack_bundles_version');
delete_option('mixpack_bundles_installed_at');
