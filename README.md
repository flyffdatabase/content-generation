# flyffdatabase/content-generation

This project includes both the commands used to generate the Content directory used for @nuxt/content in Flyffdatabase/frontend and the search api server for whole site free-text search. 

## Content directory generation

```php artisan generate:content```

Use this command to fetch images and objects from the FlyFF Project-M API provided by the official developers.

## FreeText Search

The FreeText-Search server uses prabhatsharma/zinc as a lightweight drop-in replacement for elasticsearch. It is chosen instead of elasticsearch for its single node performance and easy setup process, it also has the benefit of a possible future upgrade path to ElasticSearch.

## NOTE: https not handled

flyffdb.info uses cloudflares https proxy to handle https termination. This keeps the code simple as we dont have to deal with any of the fancy https configuration and maintenance. End users are not supposed to directly connect to any of these endpoints because they do not implement any caching (thats done on cdn layer aka. cloudflare).
