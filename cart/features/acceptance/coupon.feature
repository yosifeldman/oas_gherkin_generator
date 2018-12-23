Feature: coupon
  In order to use my app
  As a user
  I want add coupon to the Cart.


Background:
  Given: I am authenticated as "test_user"


Scenario: Add a coupon discount
  Given The model "Cart" exists with data {"brand":"markten","customer_id":"c8aa185e-d849-11e8-b513-0242c0a89065","products":[{"sku":"x-y-z-1","name":"Markten Vapor 1","qty":4,"price":15.6}]}
  When request method is post
    And request body is {"coupon_code":"M10-first-time-10-percent"}
    And request path is /carts/{cart_id}/coupon
  Then response status is 200
    And response body is {"brand":"string","customer_id":"string","products":[{"sku":"string","name":"string","qty":"integer","price":"number"}],"subtotal":{"title":"string","value":"number"},"discount":{"discount":"number","coupon_code":"string"},"shipping":{"title":"string","price":"number"},"tax":"number","giftcardaccount":{"title":"string","value":"number"},"grand_total":{"title":"string","value":"number"},"giftwrapping":[],"method":"string"}


Scenario: Remove coupon discount.
  Given The model "Cart" exists with data {"brand":"markten","customer_id":"c8aa185e-d849-11e8-b513-0242c0a89065","products":[{"sku":"x-y-z-1","name":"Markten Vapor 1","qty":4,"price":15.6}]}
  When request method is delete
    And request path is /carts/{cart_id}/coupon
  Then response status is 204


Scenario: Get all coupons
  Given 
  When request method is get
    And request path is /coupons
  Then response status is 200
    And response body is {"data":[{"coupon_code":"string","reward_points_balance":"integer","reward_currency_amount":"number","shipping_discount":"number"}]}


Scenario: Create new coupon
  Given 
  When request method is post
    And request body is {"coupon_code":"MX-10-NEW","reward_points_balance":10,"reward_currency_amount":25.5,"shipping_discount":0}
    And request path is /coupons
  Then response status is 201
    And response body is {"coupon_code":"string","reward_points_balance":"integer","reward_currency_amount":"number","shipping_discount":"number"}


Scenario: Get specific coupon
  Given The model "Coupon" exists with data {"coupon_code":"MX-10-NEW","reward_points_balance":10,"reward_currency_amount":25.5,"shipping_discount":0}
  When request method is get
    And request path is /coupons/{coupon_code}
  Then response status is 200
    And response body is {"coupon_code":"string","reward_points_balance":"integer","reward_currency_amount":"number","shipping_discount":"number"}


Scenario: Delete coupon
  Given The model "Coupon" exists with data {"coupon_code":"MX-10-NEW","reward_points_balance":10,"reward_currency_amount":25.5,"shipping_discount":0}
  When request method is delete
    And request path is /coupons/{coupon_code}
  Then response status is 204


