## Installation

For installing the `Logiscenter_ImmutableQuote` module follow this steps:
1. Place module files in `{/path/to/project}/app/code`
2. Run `bin\magento module:enable Logiscenter_ImmutableQuote`
3. Run `bin\magento setup:upgrade & bin\magento setup:static-content:deploy & bin\magento setup:di:compile & bin\magento c:f`

## Configuration steps
For interacting with the newly introduced endpoints, make sure the admin user has the `Manage Immutable Quotes` permission.

For configuring the rate limiting, in the admin see the available configurations at `Sales > Immutable Quote > Rate :imiting`

## Usage examples

1. For the creation of an immutable quote:
- obtain an admin token from `{{BASE_URL}}/rest/V1/integration/admin/token` and set it as `Bearer Token` for future requests
- call the `POST {{BASE_URL}}/rest/V1/immutable-quotes/quotes` with the following data:
`
  {
  "customer_id": 1,
  "quote_title": "Test Title"
  }`
- add items, set the billing\shipping address via the default quote endpoints

2. For the FE customer

- navigate to My Quotes section in My Account
- from the list of available quotes click on the name to view the quote details
- identify locked quotes by the Locked icon
- to activate a specific quote click on the Activate button

## Testing
Unit & Integration tests have been written and they can be ran from the command line.
