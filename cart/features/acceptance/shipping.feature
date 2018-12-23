Feature: shipping
  In order to use my app
  As a user
  I want add Shipping method to the Cart.


Background:
  Given: I am authenticated as "test_user"


Scenario: Add shipping method.
  Given The model "Cart" exists with data {"brand":"markten","customer_id":"c8aa185e-d849-11e8-b513-0242c0a89065","products":[{"sku":"x-y-z-1","name":"Markten Vapor 1","qty":4,"price":15.6}]}
  When request method is post
    And request body is {"shipping_method":"by-airplane"}
    And request path is /carts/{cart_id}/shipping
  Then response status is 200
    And response body is {"brand":"string","customer_id":"string","products":[{"sku":"string","name":"string","qty":"integer","price":"number"}],"subtotal":{"title":"string","value":"number"},"discount":{"discount":"number","coupon_code":"string"},"shipping":{"title":"string","price":"number"},"tax":"number","giftcardaccount":{"title":"string","value":"number"},"grand_total":{"title":"string","value":"number"},"giftwrapping":[],"method":"string"}


Scenario: Get all shipping methods
  Given 
  When request method is get
    And request path is /shipping
  Then response status is 200
    And response body is {"data":[{"method":"string"}]}


