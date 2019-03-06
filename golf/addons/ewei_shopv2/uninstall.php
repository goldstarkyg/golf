<?php
$sql = "

DROP TABLE `ims_ewei_shop_adv`, 
`ims_ewei_shop_article`, 
`ims_ewei_shop_article_category`, 
`ims_ewei_shop_article_log`, 
`ims_ewei_shop_article_report`, 
`ims_ewei_shop_article_share`, 
`ims_ewei_shop_article_sys`, 
`ims_ewei_shop_banner`, 
`ims_ewei_shop_carrier`, 
`ims_ewei_shop_category`, 
`ims_ewei_shop_commission_apply`, 
`ims_ewei_shop_commission_bank`, 
`ims_ewei_shop_commission_clickcount`, 
`ims_ewei_shop_commission_level`, 
`ims_ewei_shop_commission_log`, 
`ims_ewei_shop_commission_rank`, 
`ims_ewei_shop_commission_shop`, 
`ims_ewei_shop_coupon`, 
`ims_ewei_shop_coupon_category`, 
`ims_ewei_shop_coupon_data`, 
`ims_ewei_shop_coupon_guess`, 
`ims_ewei_shop_coupon_log`, `ims_ewei_shop_creditshop_adv`, `ims_ewei_shop_creditshop_category`, `ims_ewei_shop_creditshop_goods`, `ims_ewei_shop_creditshop_log`, `ims_ewei_shop_customer`, `ims_ewei_shop_customer_category`, `ims_ewei_shop_customer_guestbook`, `ims_ewei_shop_customer_robot`, `ims_ewei_shop_designer`, `ims_ewei_shop_designer_menu`, `ims_ewei_shop_dispatch`, `ims_ewei_shop_diyform_category`, `ims_ewei_shop_diyform_data`, `ims_ewei_shop_diyform_temp`, `ims_ewei_shop_diyform_type`, `ims_ewei_shop_diypage`, `ims_ewei_shop_diypage_menu`, `ims_ewei_shop_diypage_template`, `ims_ewei_shop_diypage_template_category`, `ims_ewei_shop_exhelper_express`, `ims_ewei_shop_exhelper_senduser`, `ims_ewei_shop_exhelper_sys`, `ims_ewei_shop_express`, `ims_ewei_shop_feedback`, `ims_ewei_shop_form`, `ims_ewei_shop_form_category`, `ims_ewei_shop_globonus_bill`, `ims_ewei_shop_globonus_billo`, `ims_ewei_shop_globonus_billp`, `ims_ewei_shop_globonus_level`, `ims_ewei_shop_goods`, `ims_ewei_shop_goods_comment`, `ims_ewei_shop_goods_group`, `ims_ewei_shop_goods_option`, `ims_ewei_shop_goods_param`, `ims_ewei_shop_goods_spec`, `ims_ewei_shop_goods_spec_item`, `ims_ewei_shop_groups_adv`, `ims_ewei_shop_groups_category`, `ims_ewei_shop_groups_goods`, `ims_ewei_shop_groups_goods_atlas`, `ims_ewei_shop_groups_order`, `ims_ewei_shop_groups_order_refund`, `ims_ewei_shop_groups_paylog`, `ims_ewei_shop_groups_set`, `ims_ewei_shop_groups_verify`, `ims_ewei_shop_member`, `ims_ewei_shop_member_address`, `ims_ewei_shop_member_cart`, `ims_ewei_shop_member_favorite`, `ims_ewei_shop_member_group`, `ims_ewei_shop_member_history`, `ims_ewei_shop_member_level`, `ims_ewei_shop_member_log`, `ims_ewei_shop_member_message_template`, `ims_ewei_shop_member_rank`, `ims_ewei_shop_merch_account`, `ims_ewei_shop_merch_adv`, `ims_ewei_shop_merch_banner`, `ims_ewei_shop_merch_bill`, `ims_ewei_shop_merch_billo`, `ims_ewei_shop_merch_category`, `ims_ewei_shop_merch_category_swipe`, `ims_ewei_shop_merch_clearing`, `ims_ewei_shop_merch_group`, `ims_ewei_shop_merch_nav`, `ims_ewei_shop_merch_notice`, `ims_ewei_shop_merch_perm_log`, `ims_ewei_shop_merch_perm_role`, `ims_ewei_shop_merch_reg`, `ims_ewei_shop_merch_saler`, `ims_ewei_shop_merch_store`, `ims_ewei_shop_merch_user`, `ims_ewei_shop_nav`, `ims_ewei_shop_notice`, `ims_ewei_shop_order`, `ims_ewei_shop_order_comment`, `ims_ewei_shop_order_goods`, `ims_ewei_shop_order_refund`, `ims_ewei_shop_perm_log`, `ims_ewei_shop_perm_plugin`, `ims_ewei_shop_perm_role`, `ims_ewei_shop_perm_user`, `ims_ewei_shop_plugin`, `ims_ewei_shop_poster`, `ims_ewei_shop_postera`, `ims_ewei_shop_postera_log`, `ims_ewei_shop_postera_qr`, `ims_ewei_shop_poster_log`, `ims_ewei_shop_poster_qr`, `ims_ewei_shop_poster_scan`, `ims_ewei_shop_qa_adv`, `ims_ewei_shop_qa_category`, `ims_ewei_shop_qa_question`, `ims_ewei_shop_qa_set`, `ims_ewei_shop_refund_address`, `ims_ewei_shop_saler`, `ims_ewei_shop_sale_coupon`, `ims_ewei_shop_sale_coupon_data`, `ims_ewei_shop_sign_records`, `ims_ewei_shop_sign_set`, `ims_ewei_shop_sign_user`, `ims_ewei_shop_sms`, `ims_ewei_shop_sms_set`, `ims_ewei_shop_store`, `ims_ewei_shop_sysset`, `ims_ewei_shop_system_adv`, `ims_ewei_shop_system_article`, `ims_ewei_shop_system_banner`, `ims_ewei_shop_system_case`, `ims_ewei_shop_system_casecategory`, `ims_ewei_shop_system_category`, `ims_ewei_shop_system_company_article`, `ims_ewei_shop_system_company_category`, `ims_ewei_shop_system_copyright`, `ims_ewei_shop_system_copyright_notice`, `ims_ewei_shop_system_guestbook`, `ims_ewei_shop_system_link`, `ims_ewei_shop_system_setting`, `ims_ewei_shop_system_site`, `ims_ewei_shop_virtual_category`, `ims_ewei_shop_virtual_data`, `ims_ewei_shop_virtual_type`;

";
pdo_run($sql);
?>