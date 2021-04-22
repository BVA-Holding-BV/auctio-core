<?php
namespace AuctioCore\Api\AdCurve\Entity;

use AuctioCore\Api\Base;

class Product extends Base {

    /**
     * Unique ID per product
     * @var string
     */
    public $shop_code;
    /**
     * Unique ID per variant. A product (shop_code) can contain multiple variants (variant_id)
     * @var string
     */
    public $variant_id;
    /**
     * Category id
     * @var int
     */
    public $category_id;
    /**
     * Category tree of the product split by ‘->’
     * @var string
     */
    public $shop_category;
    /**
     * Product deeplink without any referrer parameters
     * @var string
     */
    public $deeplink;
    /**
     * Name of the product
     * @var string
     */
    public $product_name;
    /**
     * Selling price of the product including VAT
     * @var int
     */
    public $selling_price;
    /**
     * Selling price of the product excluding VAT
     * @var int
     */
    public $selling_price_ex;
    /**
     * Link to the main image of the product
     * @var string
     */
    public $picture_link;
    /**
     * Original product price excluding VAT
     * @var int
     */
    public $cost_price;
    /**
     * Delivery costs including VAT
     * @var int
     */
    public $delivery_cost;
    /**
     * Period it takes to deliver the product
     * @var string
     */
    public $delivery_period;
    /**
     * Set it to ‘false’ to disable a product. Ignore otherwise
     * @var boolean
     */
    public $enabled;
    /**
     * Before price including VAT
     * @var int
     */
    public $market_price;
    /**
     * Max CPO of the product
     * @var int
     */
    public $max_cpo;
    /**
     * Brand of the product
     * @var string
     */
    public $product_brand;
    /**
     * Description of the product
     * @var string
     */
    public $product_description;
    /**
     * International Article Number of the product
     * @var string
     */
    public $product_ean;
    /**
     * How many products there are available
     * @var int
     */
    public $product_in_stock;
    /**
     * Promotion text for the product
     * @var string
     */
    public $promotion_text;
    /**
     * Starting date from when the product will be included in the feed (YYYY-MM-DD)
     * @var \AuctioCore\Api\AdCurve\Entity\Custom\Date
     */
    public $start_date;
    /**
     * The stock status of the product
     * @var string
     */
    public $stock_status;
    /**
     * Starting date from when the product will be NOT included in the feed (YYYY-MM-DD)
     * @var \AuctioCore\Api\AdCurve\Entity\Custom\Date
     */
    public $stop_date;
    /**
     * Manufacture product id (SKU)
     * @var string
     */
    public $vendor_code;
    /**
     * URL Image 2
     * @var string
     */
    public $user31;
    /**
     * URL Image 3
     * @var string
     */
    public $user32;
    /**
     * URL Image 4
     * @var string
     */
    public $user33;
    /**
     * URL Image 5
     * @var string
     */
    public $user34;
    /**
     * URL Image 6
     * @var string
     */
    public $user35;
    /**
     * MAGENTO ONLY - Unique code for a productrange that contains all colors and sizes
     * @var string
     */
    public $user1;
    /**
     * MAGENTO ONLY - All sizes the product can have. In case of multiple sizes, seperate each size by a comma
     * @var string
     */
    public $user2;
    /**
     * MAGENTO ONLY - All sizes which are currently available. In case of multiple sizes, seperate each size by a comma
     * @var string
     */
    public $user3;
    /**
     * MAGENTO ONLY - Amount of products in stock per size. In case of multiple sizes; seperate each size by a comma. Use the same order as used in sizes_available (user3 field)
     * @var string
     */
    public $user4;
    /**
     * MAGENTO ONLY - Current selling price of the products in stock per size. In case of multiple sizes/prices, separate each price by a comma. Use the same order as used in sizes_available (user3 field)
     * @var string
     */
    public $user5;
    /**
     * MAGENTO ONLY - Before price of the products in stock per size. In case of multiple sizes/prices, separate each price by a comma. Use the same order as used in sizes_available (user3 field)
     * @var string
     */
    public $user6;
    /**
     * MAGENTO ONLY - SKUs of the products in stock per size. In case of multiple sizes/SKUs, separate each SKU by a comma. Use the same order as used in sizes_available (user3 field)
     * @var string
     */
    public $user7;
    /**
     * MAGENTO ONLY - EANs of the products in stock per size. In case of multiple sizes/EANs, separate each EAN by a comma. Use the same order as used in sizes_available (user3 field)
     * @var string
     */
    public $user8;
    /**
     * MAGENTO ONLY - Color of the product
     * @var string
     */
    public $user9;
    /**
     * MAGENTO ONLY - Material of the product
     * @var string
     */
    public $user10;
    /**
     * MAGENTO ONLY - Gender of the product
     * @var string
     */
    public $user11;

    /**
     * @var string
     */
    public $product_type;
    /**
     * @var string
     */
    public $product_model;
    /**
     * @var string
     */
    public $product_color;
    /**
     * @var string
     */
    public $product_material;
    /**
     * @var string
     */
    public $product_size;
}