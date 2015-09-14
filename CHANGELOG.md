## Changelog

### 1.0
* Initial Release.

### 2.0
* Migrated all the architecture to RabbitMQ.

### 2.1
* Fixed ignorecase on git.

### 2.2
* Fix on extraction permissions.

### 2.3
* Added vhost config param for rabbitmq

### 2.4
* Added logging with stopwatch to executeFilterEngine

### 2.5
* Fixed is_mobile and in_opposition filters
* ExtractionContact entities are now added using raw query

### 2.6
* ExtractionContact entities are now added using raw query with configurable batches

### 2.7
* Changed delivery system to use file spool
* Extended ORM Extractor to fix memory problems
* Removed old code

### 2.8
 * Added auto spool flush when the deliver consumer adds an email to it
 * Added a new logout warning and ajax login system
 
### 2.9
 * Fixed dashboard statistics
 * Added an extraction activity log 
 * Finished the staging contact import API

### 2.10
 * Fixed login page

### 2.10.02
 * Added FETCH_EAGER to Extraction Exporting

### 2.11.00
 * Better UI for StagingContactAdmin
 * Added the possibility to Import or Update Leads
 * Refactored the StagingContactRepository
 * Better StagingContact Import Template