Feature: checkout
  In order to use my app
  As a user
  I want Checkout with Place Order process.


Background:
  Given: I am authenticated as "test_user"


Scenario: Place an order.
  Given The model "Cart" exists with data {"brand":"markten","customer_id":"c8aa185e-d849-11e8-b513-0242c0a89065","products":[{"sku":"x-y-z-1","name":"Markten Vapor 1","qty":4,"price":15.6}]}
  When request method is post
    And request body is {"cart":{"brand":"markten","customer_id":"c8aa185e-d849-11e8-b513-0242c0a89065","products":[{"sku":"x-y-z-1","name":"Markten Vapor 1","qty":4,"price":15.6}],"subtotal":{"title":"Subtotal","value":25.5},"discount":{"discount":10,"coupon_code":"birthday_10_percent"},"shipping":{"title":"by_train","price":0},"tax":17,"giftcardaccount":{"title":"amex","value":1},"grand_total":{"title":"grand total","value":55},"giftwrapping":[],"method":"fedex_3_5_days"},"agreement":true}
    And request path is /carts/{cart_id}/checkout
  Then response status is 200
    And response body is {"order_ref":"string"}


