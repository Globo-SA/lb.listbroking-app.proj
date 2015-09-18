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
 
### 2.11.01
 * Uncommented necessary code
 
### 2.11.02
 * Fixed updateContactDimensions trying to add empty Dimensions

### 2.11.03
 * Fixed Birthdate Validator removing leads with 18 years
 
### 2.11.04
 * Moved moveInvalidContactsToDQP out of the ProcessStagingContactsCommand for better performance
 
### 2.11.05
 * Removed progress bar from ProcessStagingContactsCommand for cleaner logs
 
### 2.11.06
 * Fixed concurrency problem when passing StagingContacts to the DQP table 
 
### 2.11.07
 * Refactored ProcessStagingContactsCommand and added stopwatch to some processes
 
### 2.11.08
 * Added more waiting time before exiting ProcessStagingContactsCommand
 
### 2.11.09
 * Refactored moveInvalidContactsToDQP to stop concurrency problems
 
### 2.11.10
 * Changed MAX_RUNNING tasks to 20 for ProcessStagingContactsCommand
 
### 2.11.11
 * Added cache on loadStagingContactDimensions
 
### 2.11.12
 * Reverted cache on loadStagingContactDimensions
 
### 2.11.13
 * Changed MAX_RUNNING tasks to 80 for ProcessStagingContactsCommand
 
### 2.11.14
 * Removed old validations
    * Empty Gender and Birthdate and <18
    * Empty Birthdate 
    * Birthdate <18
 
### 2.11.14
 * Removed all Gender validations
    * M - All male contacts
    * F - All female contacts
    * N/a - All empty or invalid gender fields