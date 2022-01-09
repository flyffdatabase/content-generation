# flyffdatabase/content-generation

This project includes both the commands used to generate the Content directory used for @nuxt/content in Flyffdatabase/frontend and the search api server for whole site free-text search. 

## Content directory generation

```php artisan generate:content```

Use this command to fetch images and objects from the FlyFF Project-M API provided by the official developers.

## FreeText Search

The FreeText-Search server uses prabhatsharma/zinc as a lightweight drop-in replacement for elasticsearch. It is chosen instead of elasticsearch for its single node performance and easy setup process, it also has the benefit of a possible future upgrade path to ElasticSearch.
