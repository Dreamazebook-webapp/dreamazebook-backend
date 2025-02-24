<?php

return [
    // 基本操作消息
    'list_success' => '获取绘本列表成功',
    'detail_success' => '获取绘本详情成功',
    'create_success' => '创建绘本成功',
    'update_success' => '更新绘本成功',
    'delete_success' => '删除绘本成功',
    'restore_success' => '恢复绘本成功',
    'force_delete_success' => '永久删除绘本成功',
    'options_success' => '获取绘本配置选项成功',
    
    // 错误消息
    'not_found' => '绘本不存在',
    'create_failed' => '创建绘本失败',
    'update_failed' => '更新绘本失败',
    'delete_failed' => '删除绘本失败',
    'restore_failed' => '恢复绘本失败',
    'force_delete_failed' => '永久删除绘本失败',
    
    // 验证消息
    'language_not_supported' => '不支持的语言',
    'gender_not_supported' => '不支持的性别',
    'skincolor_not_supported' => '不支持的肤色',
    'variant_not_found' => '未找到对应的变体',
    'variant_exists' => '该变体组合已存在',
    'page_variant_exists' => '该页面变体组合已存在',
    
    // 变体和页面
    'variant_success' => '获取绘本变体成功',
    'pages_success' => '获取绘本页面成功',
    
    // 业务规则消息
    'cannot_unpublish' => '已发布的绘本不能取消发布',
    'cannot_delete_published' => '已发布的绘本不能删除',

    // 页面相关消息
    'page' => [
        // 基本操作
        'force_delete_success' => '永久删除页面成功',
        'force_delete_failed' => '永久删除页面失败',
        'publish_success' => '页面发布成功',
        'publish_failed' => '页面发布失败',
        'hide_success' => '页面隐藏成功',
        'hide_failed' => '页面隐藏失败',
        
        // 变体操作
        'variants_create_success' => '创建变体页面成功',
        'variants_create_failed' => '创建变体页面失败',
        
        // 翻译相关
        'missing_translations' => '缺少部分语言的翻译',
        'language_not_supported' => '不支持的语言',
        'translation_not_found' => '未找到翻译',
        'translation_update_success' => '更新翻译成功',
        'translation_update_failed' => '更新翻译失败',
        'translation_delete_success' => '删除翻译成功',
        'translation_delete_failed' => '删除翻译失败',
        'translation_detail_success' => '获取翻译详情成功',
        'list_success' => '获取页面列表成功',
        'create_success' => '创建页面成功',
        'create_failed' => '创建页面失败',
        'detail_success' => '获取页面详情成功',
        'update_success' => '更新页面成功',
        'update_failed' => '更新页面失败',
        'not_found' => '页面不存在',
        'trashed_success' => '获取已删除页面列表成功',
        'delete_success' => '删除页面成功',
        'delete_failed' => '删除页面失败',
        'restore_success' => '恢复页面成功',
        'restore_failed' => '恢复页面失败',
    ],

    // 页面变体相关消息
    'page_variant' => [
        'no_sequence_with_masks' => '该页面没有角色序列，不需要蒙版',
        'masks_required' => '该页面需要提供角色蒙版',
        'masks_count_mismatch' => '蒙版数量(:masks)与角色数量(:sequence)不匹配',
        'invalid_mask_url' => '蒙版URL无效'
    ],

    // 选择类型相关
    'choices_type' => [
        'min_pages_error' => '选择类型为:type时，总页数不能少于:pages页',
        'type_names' => [
            '1' => '8选4',
            '2' => '16选8'
        ]
    ]
]; 