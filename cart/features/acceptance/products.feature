Feature: products
  In order to use my app
  As a user
  I want CRUD operations on Products inside the Cart.


Background:
  Given: I am authenticated as "test_user"


Scenario: Add one or more Product items to the Cart.
  Given The model "Cart" exists with data {"brand":"markten","customer_id":"c8aa185e-d849-11e8-b513-0242c0a89065","products":[{"sku":"x-y-z-1","name":"Markten Vapor 1","qty":4,"price":15.6}]}
  When request method is post
    And request body is {"data":[{"sku":"x-y-z-1","name":"Markten Vapor 1","qty":4,"price":15.6}]}
    And request path is /carts/{cart_id}/items
  Then response status is 200
    And response body is {"brand":"string","customer_id":"string","products":[{"sku":"string","name":"string","qty":"integer","price":"number"}],"subtotal":{"title":"string","value":"number"},"discount":{"discount":"number","coupon_code":"string"},"shipping":{"title":"string","price":"number"},"tax":"number","giftcardaccount":{"title":"string","value":"number"},"grand_total":{"title":"string","value":"number"},"giftwrapping":[],"method":"string"}


Scenario: Remove all items from the Cart.
  Given The model "Cart" exists with data {"brand":"markten","customer_id":"c8aa185e-d849-11e8-b513-0242c0a89065","products":[{"sku":"x-y-z-1","name":"Markten Vapor 1","qty":4,"price":15.6}]}
  When request method is delete
    And request path is /carts/{cart_id}/items
  Then response status is 204


Scenario: Update product quantity, price, etc, BUT not sku.
  Given The model "Cart" exists with data {"brand":"markten","customer_id":"c8aa185e-d849-11e8-b513-0242c0a89065","products":[{"sku":"x-y-z-1","name":"Markten Vapor 1","qty":4,"price":15.6}]}
    And The "item_id" is "M10X-020300"
  When request method is put
    And request body is {"sku":"x-y-z-1","name":"Markten Vapor 1","qty":4,"price":15.6}
    And request path is /carts/{cart_id}/items/{item_id}
  Then response status is 200
    And response body is {"brand":"string","customer_id":"string","products":[{"sku":"string","name":"string","qty":"integer","price":"number"}],"subtotal":{"title":"string","value":"number"},"discount":{"discount":"number","coupon_code":"string"},"shipping":{"title":"string","price":"number"},"tax":"number","giftcardaccount":{"title":"string","value":"number"},"grand_total":{"title":"string","value":"number"},"giftwrapping":[],"method":"string"}


Scenario: Delete the product from the Cart.
  Given The model "Cart" exists with data {"brand":"markten","customer_id":"c8aa185e-d849-11e8-b513-0242c0a89065","products":[{"sku":"x-y-z-1","name":"Markten Vapor 1","qty":4,"price":15.6}]}
    And The "item_id" is "M10X-020300"
  When request method is delete
    And request path is /carts/{cart_id}/items/{item_id}
  Then response status is 204


