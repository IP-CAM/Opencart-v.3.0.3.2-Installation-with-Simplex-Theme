<?php

use Cart\Cart;
use Cart\Currency;
use Cart\Customer;
use Cart\Length;
use Cart\Tax;
use Cart\User;
use Cart\Weight;

/**
 * @property string $id
 * @property string $template
 * @property array $children
 * @property array $data
 * @property string $output
 * @property Loader $load
 * @property User $user
 * @property Url $url
 * @property Log $log
 * @property Request $request
 * @property Response $response
 * @property Cache $cache
 * @property Session $session
 * @property Language $language
 * @property Document $document
 * @property Customer $customer
 * @property Currency $currency
 * @property Config $config
 * @property Tax $tax
 * @property Weight $weight
 * @property Length $length
 * @property Cart $cart
 * @property Encryption $encryption
 * @property Openbay $openbay
 * @property Event $event
 * @property ModelAccountActivity $model_account_activity
 * @property ModelAccountAddress $model_account_address
 * @property ModelAccountAffiliate $model_account_affiliate
 * @property ModelAccountApi $model_account_api
 * @property ModelAccountCustomField $model_account_custom_field
 * @property ModelAccountCustomer $model_account_customer
 * @property ModelAccountCustomerGroup $model_account_customer_group
 * @property ModelAccountDownload $model_account_download
 * @property ModelAccountGdpr $model_account_gdpr
 * @property ModelAccountOrder $model_account_order
 * @property ModelAccountRecurring $model_account_recurring
 * @property ModelAccountReturn $model_account_return
 * @property ModelAccountReward $model_account_reward
 * @property ModelAccountSearch $model_account_search
 * @property ModelAccountTransaction $model_account_transaction
 * @property ModelAccountWishlist $model_account_wishlist
 * @property ModelApiExchange $model_api_exchange
 * @property ModelCatalogCategory $model_catalog_category
 * @property ModelCatalogGalleryAlbum $model_catalog_gallery_album
 * @property ModelCatalogInformation $model_catalog_information
 * @property ModelCatalogManufacturer $model_catalog_manufacturer
 * @property ModelCatalogProduct $model_catalog_product
 * @property ModelCatalogReview $model_catalog_review
 * @property ModelCheckoutMarketing $model_checkout_marketing
 * @property ModelCheckoutOrder $model_checkout_order
 * @property ModelCheckoutRecurring $model_checkout_recurring
 * @property ModelDesignBanner $model_design_banner
 * @property ModelDesignLayout $model_design_layout
 * @property ModelDesignTheme $model_design_theme
 * @property ModelDesignTranslation $model_design_translation
 * @property ModelLocalisationCountry $model_localisation_country
 * @property ModelLocalisationCurrency $model_localisation_currency
 * @property ModelLocalisationLanguage $model_localisation_language
 * @property ModelLocalisationLocation $model_localisation_location
 * @property ModelLocalisationOrderStatus $model_localisation_order_status
 * @property ModelLocalisationReturnReason $model_localisation_return_reason
 * @property ModelLocalisationZone $model_localisation_zone
 * @property ModelMarketingMarketing $model_marketing_marketing
 * @property ModelReportStatistics $model_report_statistics
 * @property ModelSettingApi $model_setting_api
 * @property ModelSettingEvent $model_setting_event
 * @property ModelSettingExtension $model_setting_extension
 * @property ModelSettingModule $model_setting_module
 * @property ModelSettingSetting $model_setting_setting
 * @property ModelSettingStore $model_setting_store
 * @property ModelToolImage $model_tool_image
 * @property ModelToolOnline $model_tool_online
 * @property ModelToolUpload $model_tool_upload
 * @property ModelExtensionPaymentMaibTransaction model_extension_payment_maib_transaction
 **/
abstract class Controller
{
    protected $registry;

    public function __construct($registry)
    {
        $this->registry = $registry;
    }

    public function __get($key)
    {
        return $this->registry->get($key);
    }

    public function __set($key, $value)
    {
        $this->registry->set($key, $value);
    }
}
