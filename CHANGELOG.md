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
 
### 2.11.15
 * Removed all Gender validations
    * M - All male contacts
    * F - All female contacts
    * N/a - All empty or invalid gender fields

### 2.11.16
* Changed way contacts are updated

### 2.11.17
* Removed old code 

### 2.11.18
* Fixed RepeatedValidator

### 2.11.19
* Changes Deduplication LOAD function

### 2.11.20
* Removed LOAD DATA system from uploadDeduplicationsByFile

### 2.11.21
* Removed forcing deduplication template

### 2.12.01
* Added better extraction edit on filtration

### 2.12.02
* Flush extraction changes on edit

### 2.12.03
* Publish extraction after the object is updated

### 2.12.04
* Save old extraction quantity to publish after edit

### 2.12.05
* Fixed missing object

### 2.12.06
* Edit will now update and publish extraction

### 2.12.07
* Added more aggregations to the datacard

### 2.12.08
* Changed the way new leads are persisted
* Added a new unique constrain to the lead table (phone and country_id)

### 2.12.09
* Fixed Excel extension from XLSX to XLS

### 2.12.10
* Added missing indexes on StagingContactDQP and Contact

### 2.12.11
* Fixed wrong index

### 2.12.12
* Converted Address, Firstname and Lastname to Uppercase

### 2.12.13
* Added lock_date to the Lock table
* Converted expiration_date to date
* Removed LockHistory

### 2.12.14
* Added missing lock_date creation on lead persist

### 2.12.15
* Fixed PhoneValidator Exception catch

### 2.12.16
* Added more expiration time to locks

### 2.12.17
* Removed future locks from specific lock types

### 2.12.18
* Added unique index on campaign for name+client_id

### 2.12.19
* Added more filters to the extraction list

### 2.12.20
* Refactored BasicContactFilters for better query generation

### 2.13.01
* Refactored all FilterEngine filters for better performance

### 2.13.02
* Fixed invalid filter in filter

### 2.13.03
* Quick fix on BasicContacts with multiple values

### 2.13.04
* Added batch validation on addContacts

### 2.13.05
* Added campaign to extraction edit

### 2.13.06
* Added campaign to extraction filtering

### 2.13.07
* Fixed range filters

### 2.13.08
* Compiled IN filters in on expression

### 2.13.09
* Fixed AND OR on filters

### 2.14.01
* Changed the way contacts are updated and saved

### 2.14.02
* Added more filtration options to the DataCard

### 2.14.03
* Made filters summary easier to lookup

### 2.14.04
* Added pagination to the DataCard

### 2.14.05
* Fixed staging_contact_processed

### 2.14.07
* Fixed update_initial_locks

### 2.14.08
* Fixed ajax extraction status not updating extraction

### 2.14.09
* Added is_ready_to_use as an extraction filter

### 2.14.10
* Added more admin options to extraction edit

### 2.14.11
* Fixed staging cleanup query

### 2.14.12
* Updated the LeadAdmin board to be usable by the Brokers

### 2.14.13
* Removed composer.lock from the .gitignore file
* Updated symfony dependencies
* Added nullable to source.external_id

### 2.14.14
* Fixed wrong indentation on the services.yml file

### 2.14.15
* Removed old BlameableListener (Gedmo resolved the problem)

### 2.15.01
* Changed dedup raw sql instead of doctrine
* Deploy script

### 2.15.02
* Fixed extraction_deduplication phone type
* Fixed batch update

### 2.15.03
* Fixed batch update on last iteration
