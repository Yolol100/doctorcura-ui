<?php

declare(strict_types=1);

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

delete_option( 'm3_exchange_rate_chf_eur' );
delete_transient( 'dc_all_product_cats' );
wp_clear_scheduled_hook( 'm3_update_currency_rate' );

// Remove plugin-owned category presentation metadata. Product medical content is
// deliberately retained because it is authored business/content data.
foreach ( [ '_cat_is_for_man', '_cat_is_for_woman', 'dc_img_id_male', 'dc_img_id_female' ] as $meta_key ) {
    delete_metadata( 'term', 0, $meta_key, '', true );
}
