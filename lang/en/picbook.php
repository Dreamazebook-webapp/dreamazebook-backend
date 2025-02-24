<?php

return [
    // Basic operation messages
    'list_success' => 'Successfully retrieved picbook list',
    'detail_success' => 'Successfully retrieved picbook details',
    'create_success' => 'Successfully created picbook',
    'update_success' => 'Successfully updated picbook',
    'delete_success' => 'Successfully deleted picbook',
    'restore_success' => 'Successfully restored picbook',
    'force_delete_success' => 'Successfully permanently deleted picbook',
    'options_success' => 'Successfully retrieved picbook options',
    
    // Error messages
    'not_found' => 'Picbook not found',
    'create_failed' => 'Failed to create picbook',
    'update_failed' => 'Failed to update picbook',
    'delete_failed' => 'Failed to delete picbook',
    'restore_failed' => 'Failed to restore picbook',
    'force_delete_failed' => 'Failed to permanently delete picbook',
    
    // Validation messages
    'language_not_supported' => 'Language not supported',
    'gender_not_supported' => 'Gender not supported',
    'skincolor_not_supported' => 'Skin color not supported',
    'variant_not_found' => 'Variant not found',
    'variant_exists' => 'This variant combination already exists',
    'page_variant_exists' => 'This page variant combination already exists',
    
    // Variant and pages
    'variant_success' => 'Successfully retrieved picbook variant',
    'pages_success' => 'Successfully retrieved picbook pages',
    
    // Business rule messages
    'cannot_unpublish' => 'Cannot unpublish a published picbook',
    'cannot_delete_published' => 'Cannot delete a published picbook',

    // Page related messages
    'page' => [
        // Basic operations
        'force_delete_success' => 'Successfully permanently deleted page',
        'force_delete_failed' => 'Failed to permanently delete page',
        'publish_success' => 'Successfully published page',
        'publish_failed' => 'Failed to publish page',
        'hide_success' => 'Successfully hidden page',
        'hide_failed' => 'Failed to hide page',
        
        // Variant operations
        'variants_create_success' => 'Successfully created variant pages',
        'variants_create_failed' => 'Failed to create variant pages',
        
        // Translation related
        'missing_translations' => 'Missing translations for some languages',
        'language_not_supported' => 'Language not supported',
        'translation_not_found' => 'Translation not found',
        'translation_update_success' => 'Successfully updated translation',
        'translation_update_failed' => 'Failed to update translation',
        'translation_delete_success' => 'Successfully deleted translation',
        'translation_delete_failed' => 'Failed to delete translation',
        'translation_detail_success' => 'Successfully retrieved translation details',
        'list_success' => 'Successfully retrieved page list',
        'create_success' => 'Successfully created page',
        'create_failed' => 'Failed to create page',
        'detail_success' => 'Successfully retrieved page details',
        'update_success' => 'Successfully updated page',
        'update_failed' => 'Failed to update page',
        'not_found' => 'Page not found',
        'trashed_success' => 'Successfully retrieved trashed page list',
        'delete_success' => 'Successfully deleted page',
        'delete_failed' => 'Failed to delete page',
        'restore_success' => 'Successfully restored page',
        'restore_failed' => 'Failed to restore page',
    ],

    // Page variant related messages
    'page_variant' => [
        'no_sequence_with_masks' => 'This page has no character sequence, masks are not needed',
        'masks_required' => 'Character masks are required for this page',
        'masks_count_mismatch' => 'Number of masks (:masks) does not match number of characters (:sequence)',
        'invalid_mask_url' => 'Invalid mask URL'
    ],

    // Choice type related
    'choices_type' => [
        'min_pages_error' => 'For choice type :type, total pages cannot be less than :pages',
        'type_names' => [
            '1' => 'Choose 4 from 8',
            '2' => 'Choose 8 from 16'
        ]
    ]
]; 