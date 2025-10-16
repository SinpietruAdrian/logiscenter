## Executive Summary

My approach for this implementation was to reuse as much as I could of the core functionality of Magento in terms of APIs and quotes-related logic. I also improved on the previous solution by implementing a middleware functionality for frontend actions and by reducing as much as possible the query count necessary for adding additional attributes to the quote—by this module or by other modules extending the quote functionality.

As key improvements over the previous implementation, I can mention:

1. A single DB query for an indefinite number of additional attributes.

2. Improved extensibility, as all quote data exists in the `quote_metadata` table, which can be updated by any module, and all values will be set as Extension Attributes on the quote object.

3. The frontend guard can be easily extended to include additional actions.


## Data Model Decision

I went for a hybrid between option A and B. So I decided to extended the core functionality through extension attributes but the metadata management is based on a single table that can eventually be extended by any 3rd party.

This approach completely eliminates the micro table issue as any 3rd party needs to do two things to have the new attribute available on the quote object: define a new db column for the `quote_metadata` table & define the new field as an extension attribute for `Logiscenter\ImmutableQuote\Api\Data\QuoteMetadataInterface`.

The trade-offs I identified are:

- any 3rd party module becomes dependent on this module so future changes might break their implementations
- for getList one query is being executed for each result item (might be an easy fix based on the Join Processor)
- in theory scalability might become an issue if too many new columns are needed for the `quote_metadata` table

Migration should be easy as any 3rd party needs to map the data based on the `quote_id` that already exists in the `quote_metadata` table.

## Micro-Table Architecture Analysis

Regarding the current implementation I can say that I understand the initial approach, because no one could have known how requirements would evolve.

In terms of scalability, it's obvious this approach is not optimal as any new module that introduces data would need one new join, so you run into problems like: performance, query building issue (select parameter conflicts), memory usage.

## Design Patterns Used
Plugin\Observer Patterns:

- customized core functionality without altering core code
- dispatched various events that in my implementation I used for logging user actions or that can be used by 3rd parties for custom logic
- implemented middleware like functionality on order to prevent various actions

Many others like Factory, Repository and so on but they are core Magento, so I wouldn't go into detail.

## Event Architecture

Events list:
1. immutable_quote_action_blocked:
   - dispatched when a FE action is blocked for a user
   - payload: action (Controller action name), quote (the quote object)
2. immutable_quote_created
   - dispatched when an admin user creates an immutable quote
   - payload: quote (the quote object)
3. immutable_quote_activated
   - dispatched when a user activates a new quote (normal or immutable) from My Account section
   - payload: customer_id (the id of the currently logged-in user), quote (the quote object)

## Performance Optimizations

While I haven't worked directly on caching (the only request that I think could benefit from caching is for fetching the customer quotes), the requests count and memory usage will go down drastically with this implementation for the reasons mentioned above.
Also, the performance improvements would be more noticeable when 3rd party modules would switch to an approach based on this module.

## Security Measures

Mostly all restrictions are based on the `Logiscenter_ImmutableQuote::manage` role but making them more granular based on new specifications would be a matter of minutes.

For the backend\frontend multiple validations were added:
- making sure quote activation is made for quotes belonging to the logged-in user
- only admin users with proper ACL can create immutable quotes
- on the FE the customer can view only his quotes
- FE template data is escaped properly
- optional & configurable rate limiting was added for all Immutable Quote related actions (`Sales > Immutable Quote > Rate limiting`)

https://developer.adobe.com/commerce/webapi/get-started/rate-limiting/ -> for rate limiting Redis logger needs to be configured as described here

## Trade-Offs and Limitations

The current implementation isn't perfect, but it surely improves over the previous one.
What I would improve:
- `getList` for the default quotes endpoints in order to not have N queries for N returned quotes for fetching metadata
- graphQL endpoints for FE, most necessary being for fetching the list of quotes for the user
- work on some type of caching for the customer quotes
- add a `is_visible` flag on Immutable Quotes as with the current implementation they appear in My Account instantly after reaction
