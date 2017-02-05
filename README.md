

# Endpoints

## Products
* GET `/calderawp_api/v2/products`
    * All products
    * Arguments:
        * `per_page` Default: 10. # of products per page
        * `page` Default: 1. Page #
        * `soon` Default: false. Show coming soon products only?
        * `product_slug` Default: false. Retrieve specific product by slug.
        * `search` Default: false. Keyword search for products. 
* GET `/calderawp_api/v2/products/<id>`
    * A specific product by ID
* GET `/calderawp_api/v2/products/cf-add-ons`
    * Caldera Forms Add-ons
    * Arguments:
        * `per_page` Default: 10. # of products per page
        * `page` Default: 1. Page #
        * `soon` Default: false. Show coming soon products only?
        * `slug` Default: false. Allows searching products by slug.
        * `category` Default: false. Product category.
* GET `/calderawp_api/v2/products/caldera-search`
    * Products in Caldera Search Bundle
* GET `/calderawp_api/v2/products/cf-bundles`
    * Caldera Forms Bundles
* GET `/calderawp_api/v2/products/featured`
    * Featured products
    * Arguments:
        * `per_page` Default: 10. # of products per page
        * `page` Default: 1. Page #
        
## Documentation
* GET `/calderawp_api/v2/docs`
    * All docs
    * Arguments:
        * `per_page` Default: 10. # of docs per page
        * `page` Default: 1. Page #
        * `doc_slug` Default: false. Retrieve specific doc by slug. 
        * `product_slug` Default: false. Retrieve docs for product, by slug 
        * `product_id` Default: false. Retrieve docs for product, by id
        * `search` Default: false. Keyword search for docs. 
* GET `/calderawp_api/v2/docs/<id>`
    * Specific doc, by ID