## 1. Create Empty Quote for Customer

Endpoint: `POST /V1/immutable-quotes/quotes`

Description: Creates a new empty quote for a specific customer

Request Parameters:

- `customerId (int, required)`: The ID of the customer for whom the quote is created
- `quoteTitle (string, required)`: A title to assign to the quote (visible in My Account section)

Response: Returns the ID of the newly created quote (int).

Example Request:

```code
POST /V1/immutable-quotes/quotes
{
"customerId": 123,
"quoteTitle": "Special Quote for Customer 123"
}
```

Response:

```code
Response code: 200
Response body: 123
```

Error responses:
```code
Customer not found for the provided id:
Response code: 404
Response body: No such entity with %fieldName = %fieldValue

Failed to save the quote data:
Response code: 400
Response body: Failed to create empty immutable cart for customer %1
```


## 2. Activate Quote for Customer

Endpoint: POST /V1/immutable-quotes/quotes/:quoteId/enable

Description: Activates a specific quote for a customer and any other active quotes for this customer will be deactivated.

```code
URL Parameters:

- quoteId (int, required): The ID of the quote to activate.

Request Body Parameters:

- customerId (int, required): The ID of the currently logged in user, automatically filled in by Magento

Response: Returns true if the quote was successfully activated.
```
Example Request:

```code
POST /V1/immutable-quotes/quotes/45/enable
```

Error responses:
```code
Quote not found for the provided id:
Response code: 404
Response body: No such entity with %fieldName = %fieldValue

Quote does not belong to the customer:
Response code: 400
Response body: Quote does not belong to the logged in customer

Quote was already used to place an order:
Response code: 400
Response body: Quote with id "%1" was already used to place an order

Failed to save the quote data:
Response code: 400
Response body: There has been ar error while trying to active the quote. Message: %1
```

## 3. Fetch customer quotes

Endpoint: `{{BASE_URL}}/rest/V1/carts/search?searchCriteria[filter_groups][0][filters][0][field]=customer_id&searchCriteria[filter_groups][0][filters][0][value]=1&searchCriteria[filter_groups][0][filters][0][condition_type]=eq&searchCriteria[filter_groups][1][filters][0][field]=reserved_order_id&searchCriteria[filter_groups][1][filters][0][condition_type]=null`

```code
Request Parameters:

- `customerId (int, required)`: The ID of the customer for whom the quotes are listed
- `reserved_order_id (null)`: Filter for getting quotes that do not have an associated order
```
Response:
```code
{
    "items": [
        {
            "id": 75,
            "created_at": "2025-10-13 12:54:02",
            "updated_at": "2025-10-15 21:26:48",
            "is_active": false,
            "is_virtual": false,
            "items_count": 2,
            "items_qty": 2,
            "customer": {
                "id": 1,
                "group_id": 1,
                "default_billing": "1",
                "default_shipping": "4",
                "created_at": "2023-05-17 08:46:30",
                "updated_at": "2025-10-15 16:36:42",
                "created_in": "Default Store View",
                "dob": "2025-10-04",
                "email": "qq@asd.com",
                "firstname": "qwe",
                "lastname": "we",
                "gender": 0,
                "store_id": 1,
                "website_id": 1
            },
            "billing_address": {
                "id": 261,
                "region": "Västerbottens län",
                "region_id": 1058,
                "region_code": "SE-AC",
                "country_id": "SE",
                "street": ["qwe", "wqe"],
                "telephone": "1231231231",
                "postcode": "123123",
                "city": "qwe",
                "firstname": "qwe",
                "lastname": "we",
                "customer_id": 1,
                "email": "qq@asd.com"
            },
            "orig_order_id": 0,
            "currency": {
                "global_currency_code": "USD",
                "base_currency_code": "USD",
                "store_currency_code": "USD",
                "quote_currency_code": "USD"
            },
            "customer_is_guest": false,
            "customer_note_notify": true,
            "store_id": 1,
            "extension_attributes": {
                "metadata": {
                    "metadata_id": 16,
                    "quote_id": 75,
                    "is_immutable": true,
                    "title": "qq"
                }
            }
        },
        {
            "id": 81,
            "created_at": "2025-10-13 13:11:05",
            "updated_at": "2025-10-15 21:28:19",
            "is_active": false,
            "is_virtual": false,
            "items_count": 0,
            "items_qty": 0,
            "customer": {
                "id": 1,
                "group_id": 1,
                "default_billing": "1",
                "default_shipping": "4",
                "created_at": "2023-05-17 08:46:30",
                "updated_at": "2025-10-15 16:36:42",
                "created_in": "Default Store View",
                "dob": "2025-10-04",
                "email": "qq@asd.com",
                "firstname": "qwe",
                "lastname": "we",
                "gender": 0,
                "store_id": 1,
                "website_id": 1
            },
            "billing_address": {
                "id": 235,
                "region": null,
                "region_id": null,
                "region_code": null,
                "country_id": null,
                "street": [""],
                "telephone": null,
                "postcode": null,
                "city": null,
                "firstname": null,
                "lastname": null,
                "customer_id": 1,
                "email": "qq@asd.com"
            },
            "orig_order_id": 0,
            "currency": {
                "global_currency_code": "USD",
                "base_currency_code": "USD",
                "store_currency_code": "USD",
                "quote_currency_code": "USD"
            },
            "customer_is_guest": false,
            "customer_note_notify": true,
            "store_id": 1,
            "extension_attributes": {
                "metadata": {
                    "metadata_id": 21,
                    "quote_id": 81,
                    "is_immutable": true,
                    "title": "qq"
                }
            }
        }
    ],
    "search_criteria": {
        "filter_groups": [
            {
                "filters": [
                    {
                        "field": "customer_id",
                        "value": "1",
                        "condition_type": "eq"
                    }
                ]
            },
            {
                "filters": [
                    {
                        "field": "reserved_order_id",
                        "value": null,
                        "condition_type": "null"
                    }
                ]
            }
        ]
    },
    "total_count": 2
}

```
