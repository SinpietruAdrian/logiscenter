# Improvements

1. Improved performance by needing a single query to fetch all quote metadata (current + future data from 3rd parties)
2. Improved FE actions restrictions by implementing the middleware, just so we don't need to create a plugin for each FE action
3. Implement an easily extensible architecture, new FE restrictions or new quote metadata can be added easily
4. Made use of exiting endpoints and functionality as much as possible
5. Added configurable rate limiting for the newly introduced endpoints
6. Added action logging in order to properly trace actions and quote data for each action
7. Beside the backend I added much needed FE functionality for My Account/Checkout area
