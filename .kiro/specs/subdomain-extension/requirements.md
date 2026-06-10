# Requirements Document

## Introduction

The Subdomain Extension is a Blueprint-compatible extension for the Pterodactyl panel that enables administrators to securely manage Cloudflare DNS subdomains for their servers. Administrators can connect Cloudflare API tokens, view available domains/zones, create and delete subdomain records pointing to panel servers, and view a consolidated dashboard of all subdomain-to-server mappings. The extension enforces security through encrypted token storage, input validation, rate limiting, and comprehensive audit logging.

## Glossary

- **Extension**: A Blueprint-compatible module that adds functionality to the Pterodactyl panel
- **Admin**: An authenticated administrator user of the Pterodactyl panel with access to the admin area
- **Cloudflare_API_Token**: A scoped Cloudflare API token with Zone:Read and DNS:Edit permissions
- **Zone**: A Cloudflare DNS zone representing a registered domain (e.g., "example.com")
- **Subdomain_Record**: A DNS record (A, AAAA, or CNAME) for a subdomain within a zone
- **Settings_Table**: The database table used by BlueprintExtensionLibrary for key-value data persistence
- **BlueprintExtensionLibrary**: The Blueprint framework library providing dbGet, dbSet, dbGetMany, dbSetMany, and dbForget methods
- **Controller**: The subdomainExtensionController that handles HTTP requests at `/admin/extensions/subdomain/`
- **CloudflareService**: The service component responsible for all Cloudflare API v4 interactions
- **EncryptionService**: The service component responsible for encrypting and decrypting sensitive API tokens
- **InputValidator**: The service component responsible for validating and sanitizing all user inputs
- **AuditLogger**: The service component responsible for recording security-relevant actions
- **Zone_Cache**: A cached copy of Cloudflare zone data with a configurable TTL (default 300 seconds)
- **FQDN**: Fully Qualified Domain Name (e.g., "mc1.example.com")
- **Rate_Limiter**: A component that restricts the frequency of API calls and admin actions

## Requirements

### Requirement 1: Cloudflare API Token Configuration

**User Story:** As an admin, I want to connect Cloudflare API tokens to the extension, so that I can manage DNS subdomains for my domains.

#### Acceptance Criteria

1. WHEN an admin submits a Cloudflare API token via the configuration form, THE Controller SHALL validate the token format against the pattern `^[A-Za-z0-9_-]{40}$`
2. WHEN a token passes format validation, THE CloudflareService SHALL verify the token against Cloudflare's token verification endpoint (`GET /user/tokens/verify`) with a request timeout of 10 seconds
3. WHEN a token passes Cloudflare verification, THE CloudflareService SHALL confirm the token has minimum required permissions of Zone:Read and DNS:Edit
4. IF a token fails format validation, THEN THE Controller SHALL return an error message specifying the invalid format without storing the token
5. IF a token fails Cloudflare verification or lacks required permissions, THEN THE Controller SHALL return an error message indicating the verification failure reason without storing the token
6. IF the Cloudflare verification request times out or fails due to a network error, THEN THE Controller SHALL return an error message indicating the verification service is unavailable without storing the token
7. WHEN a token passes all validation and verification, THE EncryptionService SHALL encrypt the token using AES-256-CBC before storage
8. WHEN a token is encrypted, THE Controller SHALL store the encrypted token in the Settings_Table with the key pattern `subdomain::cf_token_{uuid}` and add the token ID to the `subdomain::cf_tokens_index`
9. IF an admin submits a token that is already stored (identified by matching the Cloudflare token identity from the verification response), THEN THE Controller SHALL return an error message indicating the token is already configured without creating a duplicate entry
10. WHEN a token is successfully stored, THE AuditLogger SHALL log the token addition with the admin ID, token ID, and timestamp
11. WHEN displaying stored tokens in the admin interface, THE Controller SHALL show only the last 4 characters of the token for identification

### Requirement 2: Cloudflare API Token Removal

**User Story:** As an admin, I want to remove previously configured Cloudflare API tokens, so that I can revoke access to domains that should no longer be managed.

#### Acceptance Criteria

1. WHEN an admin requests deletion of an API token, THE Controller SHALL verify the token exists in the Settings_Table using the key pattern `subdomain::cf_token_{uuid}`, and if found, remove the encrypted token data and its entry from `subdomain::cf_tokens_index` in a single operation such that both removals succeed or neither is applied
2. WHEN a token is successfully deleted, THE AuditLogger SHALL log the token removal with the admin ID, token ID, and timestamp
3. IF a token deletion is requested for a non-existent token ID, THEN THE Controller SHALL return an error indicating the token was not found without modifying any stored data
4. IF subdomain records exist that are associated with the token being deleted, THEN THE Controller SHALL reject the deletion and return an error indicating the token cannot be removed while active subdomain records depend on it
5. WHEN a token is successfully deleted, THE Controller SHALL invalidate any Zone_Cache entries that were fetched using the deleted token

### Requirement 3: Zone Listing and Caching

**User Story:** As an admin, I want to view all available domains (zones) from my connected Cloudflare account, so that I can choose which domain to create subdomains under.

#### Acceptance Criteria

1. WHEN an admin navigates to the extension admin page, THE Controller SHALL retrieve and aggregate the list of zones from all configured tokens, deduplicating zones that appear under multiple tokens
2. WHILE the Zone_Cache is valid (less than the configured cache TTL, default 300 seconds, since the cached_at timestamp), THE Controller SHALL serve zone data from the cache without contacting Cloudflare
3. WHEN the Zone_Cache is expired or missing, THE CloudflareService SHALL fetch a fresh zone list from Cloudflare's zones endpoint for each configured token
4. WHEN fresh zone data is retrieved, THE Controller SHALL update the Zone_Cache in the Settings_Table with the new zone data and the current Unix timestamp
5. WHEN displaying zones, THE Controller SHALL show zone name, zone status, and assigned nameservers for each zone
6. IF no tokens are configured, THEN THE Controller SHALL display a setup-required view prompting the admin to add a token
7. IF all configured tokens are valid but the aggregated zone list is empty, THEN THE Controller SHALL display a message indicating no zones are available for the connected tokens
8. WHEN an admin triggers a manual cache refresh, THE Controller SHALL invalidate the current Zone_Cache, fetch fresh zone data from Cloudflare, and log the zone refresh action via the AuditLogger

### Requirement 4: Subdomain Record Creation

**User Story:** As an admin, I want to create subdomain DNS records that point to my panel servers, so that users can connect to servers using friendly subdomain addresses.

#### Acceptance Criteria

1. WHEN an admin submits a subdomain creation request, THE Rate_Limiter SHALL check that the admin has not exceeded 10 subdomain creations per minute
2. IF the rate limit is exceeded, THEN THE Controller SHALL return a 429 error indicating too many requests and the number of seconds until the limit resets
3. WHEN a subdomain creation request is received, THE InputValidator SHALL validate the subdomain name matches the pattern `^[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?$`
4. WHEN a subdomain creation request is received, THE InputValidator SHALL validate the record type is one of A, AAAA, or CNAME
5. WHEN the record type is A, THE InputValidator SHALL validate the target is a valid IPv4 address
6. WHEN the record type is AAAA, THE InputValidator SHALL validate the target is a valid IPv6 address
7. WHEN the record type is CNAME, THE InputValidator SHALL validate the target is a valid hostname and not an IP address
8. IF any validation fails (subdomain name, record type, or target), THEN THE Controller SHALL return an error message identifying which field failed and the reason for failure, without creating any DNS record
9. WHEN a subdomain creation request is received, THE InputValidator SHALL validate the zone_id matches the pattern `^[0-9a-f]{32}$` and the server_id references an existing server in the Pterodactyl panel
10. IF the target server already has the configured maximum number of subdomain records assigned (default 5), THEN THE Controller SHALL return an error message indicating the per-server subdomain limit has been reached
11. WHEN all validations pass, THE CloudflareService SHALL create the DNS record via the Cloudflare API (`POST /zones/{zone_id}/dns_records`) within a timeout of 10 seconds per attempt
12. WHEN the DNS record is successfully created, THE Controller SHALL store the subdomain record mapping in the Settings_Table with the key pattern `subdomain::record_{cf_record_id}`
13. WHEN a subdomain record is stored, THE Controller SHALL update the zone's record index (`subdomain::zone_{zone_id}_records`) and the server's record index (`subdomain::server_{server_id}_records`)
14. WHEN a subdomain is successfully created, THE AuditLogger SHALL log the creation with admin ID, subdomain name, zone, record type, target, and server assignment

### Requirement 5: Subdomain Record Deletion

**User Story:** As an admin, I want to delete subdomain DNS records, so that I can remove subdomains that are no longer needed.

#### Acceptance Criteria

1. WHEN an admin requests deletion of a subdomain record, THE CloudflareService SHALL delete the DNS record from Cloudflare via the API (`DELETE /zones/{zone_id}/dns_records/{record_id}`)
2. WHEN the DNS record is successfully deleted from Cloudflare, THE Controller SHALL remove the record mapping from the Settings_Table with the key `subdomain::record_{record_id}`
3. WHEN a record mapping is removed, THE Controller SHALL update the zone's record index (`subdomain::zone_{zone_id}_records`) and the server's record index (`subdomain::server_{server_id}_records`) to remove the deleted record ID
4. WHEN a subdomain is successfully deleted, THE AuditLogger SHALL log the deletion with admin ID, subdomain name, zone, record ID, and request IP address
5. IF the Cloudflare API returns a non-success response other than "record not found" during deletion, THEN THE Controller SHALL return an error message indicating the deletion failed, preserve the local record mapping unchanged, and the AuditLogger SHALL log the failed deletion attempt
6. IF the Cloudflare API indicates the record does not exist (404 or record-not-found error), THEN THE Controller SHALL remove the local record mapping and indexes as if the deletion succeeded and display a notice that the record was already removed externally
7. IF a deletion is requested for a record ID that does not exist in the Settings_Table, THEN THE Controller SHALL return an error indicating the record was not found without making any Cloudflare API call
8. IF the API token associated with the subdomain record has been removed or fails decryption, THEN THE Controller SHALL return an error indicating the token is unavailable and preserve the local record mapping

### Requirement 6: Server-Subdomain Dashboard

**User Story:** As an admin, I want to see which servers are using which subdomains, so that I can manage subdomain assignments and identify conflicts.

#### Acceptance Criteria

1. WHEN an admin views the extension dashboard, THE Controller SHALL display a consolidated list of all subdomain-to-server mappings sorted by creation timestamp in descending order (newest first)
2. WHEN displaying subdomain mappings, THE Controller SHALL show the FQDN, record type, target, associated server name, and creation timestamp for each record
3. WHEN displaying the dashboard, THE Controller SHALL provide the ability to filter records by server and by zone, displaying only records matching the selected filter
4. IF a record references a server that no longer exists in the Pterodactyl panel, THEN THE Controller SHALL display the record with a visual indicator that the server is unavailable and show the original server name from the stored mapping
5. IF a server has no assigned subdomains, THEN THE Controller SHALL display a message stating that the server has no active subdomain records
6. THE Controller SHALL paginate the subdomain list displaying a maximum of 20 records per page, with navigation controls to move between pages when total records exceed 20
7. WHEN two or more subdomain records within the same zone share the same subdomain label, THE Controller SHALL visually highlight those records as conflicting entries

### Requirement 7: Extension Settings Management

**User Story:** As an admin, I want to configure extension behavior settings, so that I can tune rate limits, cache duration, and allowed record types for my environment.

#### Acceptance Criteria

1. WHEN an admin updates extension settings, THE Controller SHALL validate each submitted setting value against its defined constraints and store only valid values in the Settings_Table
2. THE Extension SHALL provide configurable settings for: rate limit per minute (default 30, minimum 1, maximum 120), cache TTL in seconds (default 300, minimum 60, maximum 3600), allowed record types (default A, AAAA, CNAME; valid options limited to A, AAAA, and CNAME), maximum subdomains per server (default 5, minimum 1, maximum 50), and wildcard permission (default disabled, accepts enabled or disabled only)
3. WHEN settings are updated, THE AuditLogger SHALL log the change with admin ID, changed setting keys, previous values, and new values
4. IF a setting value fails validation, THEN THE Controller SHALL return an error message indicating which setting failed and why, and preserve the previous value for that setting
5. IF a settings update request contains a mix of valid and invalid values, THEN THE Controller SHALL reject the entire update, preserve all previous settings, and return error messages for each invalid value
6. WHEN an admin views the settings page, THE Controller SHALL display the current value for each configurable setting alongside its default value

### Requirement 8: Security - Encrypted Token Storage

**User Story:** As an admin, I want API tokens stored securely, so that a database breach does not expose my Cloudflare credentials.

#### Acceptance Criteria

1. THE EncryptionService SHALL encrypt all Cloudflare API tokens using Laravel's AES-256-CBC encryption with the application's APP_KEY before any write to the Settings_Table
2. WHEN the EncryptionService decrypts a token, THE EncryptionService SHALL verify the MAC (message authentication code) of the encrypted payload and confirm it matches the ciphertext before returning the plaintext value
3. IF decryption fails due to corrupted or tampered ciphertext, THEN THE EncryptionService SHALL throw a decryption exception, refuse to return any data, and THE Controller SHALL display an error message indicating the token is corrupted without revealing the ciphertext or plaintext content
4. THE Controller SHALL ensure tokens are never present in plaintext in application logs, HTTP responses, error messages, exception stack traces, debug output, or any transmission outside the EncryptionService boundary
5. IF a stored token cannot be decrypted, THEN THE Controller SHALL mark the affected token as invalid in the admin interface and prompt the admin to remove and re-add the token

### Requirement 9: Security - Input Validation and Sanitization

**User Story:** As an admin, I want all inputs validated and sanitized, so that the extension is protected against injection attacks and malformed data.

#### Acceptance Criteria

1. IF a subdomain name contains a dot character (`.`), THEN THE InputValidator SHALL reject the input and return a validation error message indicating that dots are not permitted in subdomain names
2. THE InputValidator SHALL enforce a minimum label length of 1 character, a maximum label length of 63 characters, and a total FQDN length of no more than 253 characters, rejecting inputs outside these bounds with a validation error message indicating the length constraint violated
3. IF a zone ID does not match the pattern `^[0-9a-f]{32}$`, THEN THE InputValidator SHALL reject the input and return a validation error message indicating an invalid zone ID format
4. IF a subdomain name begins with an asterisk character (`*`) AND the wildcard_allowed setting is not set to boolean true, THEN THE InputValidator SHALL reject the input and return a validation error message indicating that wildcard subdomains are not permitted
5. WHEN the InputValidator sanitizes a subdomain name, THE InputValidator SHALL perform the following operations in order: trim leading and trailing whitespace, convert all characters to lowercase, remove any characters not matching `[a-z0-9-]`, and then remove any leading or trailing hyphen characters
6. THE Controller SHALL use Laravel FormRequest classes for all server-side input validation on POST, PUT, PATCH, and DELETE requests
7. IF a Laravel FormRequest validation fails, THEN THE Controller SHALL return a response containing field-level error messages identifying each input that failed validation, without exposing internal system details
8. THE InputValidator SHALL apply sanitization (criterion 5) before applying validation rules (criteria 1 through 4) on subdomain name inputs

### Requirement 10: Security - Authorization and CSRF Protection

**User Story:** As an admin, I want the extension protected against unauthorized access and cross-site request forgery, so that only authenticated administrators can manage subdomains.

#### Acceptance Criteria

1. THE Controller SHALL enforce that all extension routes under `/admin/extensions/subdomain/` require admin-level authentication via AdminFormRequest, which verifies the user is non-null and has `root_admin` privilege
2. IF a request to any extension route fails admin-level authentication, THEN THE Controller SHALL reject the request with a 403 Forbidden response and not process the action
3. THE Controller SHALL require valid CSRF tokens on all state-changing requests (POST, PUT, PATCH, DELETE) via the `web` middleware group's VerifyCsrfToken middleware
4. IF a state-changing request is received with an invalid or missing CSRF token, THEN THE Controller SHALL reject the request with a 419 response and not process the action
5. THE Extension SHALL register all token and subdomain management endpoints exclusively within the admin route group, exposing no routes accessible to unauthenticated users or non-admin authenticated users
6. WHEN an admin attempts to assign a subdomain to a server, THE Controller SHALL validate that the server exists in the Pterodactyl panel database
7. IF a subdomain assignment references a server ID that does not exist in the Pterodactyl panel, THEN THE Controller SHALL return an error message indicating the server was not found and not create the DNS record

### Requirement 11: Security - Rate Limiting

**User Story:** As an admin, I want rate limiting on API operations, so that the extension does not exhaust Cloudflare API quotas or allow abuse.

#### Acceptance Criteria

1. THE Rate_Limiter SHALL enforce a configurable maximum number of Cloudflare API calls per fixed 60-second window (default 30, configurable range 1 to 1200)
2. THE Rate_Limiter SHALL enforce a maximum of 10 subdomain creation requests per fixed 60-second window per admin
3. WHEN the Cloudflare API returns a 429 response with a retry-after header, THE CloudflareService SHALL block further requests to that endpoint until the retry-after duration expires
4. IF the Cloudflare API returns a 429 response without a retry-after header, THEN THE CloudflareService SHALL block further requests for a default cooldown of 60 seconds
5. IF a rate limit is exceeded, THEN THE Controller SHALL return an error message indicating the specific limit that was exceeded, the limit value, and the number of seconds remaining until the window resets
6. WHEN the Rate_Limiter blocks a request due to the global API call limit, THE Rate_Limiter SHALL also prevent any queued or subsequent Cloudflare API calls from being dispatched until the current window resets

### Requirement 12: Security - Audit Logging

**User Story:** As an admin, I want all security-relevant actions recorded in an audit log, so that I can review activity and investigate incidents.

#### Acceptance Criteria

1. THE AuditLogger SHALL record token additions, token removals, subdomain creations, subdomain deletions, settings changes, and zone refresh actions
2. WHEN logging an action, THE AuditLogger SHALL include timestamp in ISO 8601 UTC format, admin ID, admin email, action type, resource type, resource ID, request IP address, and result (success or failure)
3. IF an operation fails due to validation rejection or Cloudflare API error, THEN THE AuditLogger SHALL log the failure using the same fields as successful actions, with the result set to failure and the resource type indicating the failed operation context
4. THE AuditLogger SHALL maintain an audit index capped at 500 most recent entries, removing the oldest entry when a new entry would exceed the cap
5. WHEN an admin views the audit log, THE Controller SHALL display entries in reverse chronological order, paginated at 25 entries per page, with filtering by a single selected action type

### Requirement 13: Error Handling - Cloudflare API Failures

**User Story:** As an admin, I want the extension to handle Cloudflare API errors gracefully, so that failures do not break the admin interface or lose data.

#### Acceptance Criteria

1. IF the Cloudflare API returns a network timeout (no response within 10 seconds) or connection failure, THEN THE CloudflareService SHALL retry with exponential backoff (1s, 2s, 4s) up to 3 attempts before reporting failure
2. IF all retry attempts fail and cached zone data exists, THEN THE Controller SHALL display the cached zone data with a staleness indicator showing the cache age and an error message indicating Cloudflare service unavailability
3. IF all retry attempts fail and no cached zone data exists, THEN THE Controller SHALL display an empty state view with an error message indicating Cloudflare service unavailability and a manual retry option
4. IF a DNS record creation fails due to a conflict (record already exists), THEN THE Controller SHALL display an error indicating the subdomain already exists in that zone and SHALL NOT create any local record mapping in the Settings_Table
5. IF the Cloudflare API returns a 401 or 403 response for a previously stored token, THEN THE Controller SHALL update the token status to "needs_re_verification" in the Settings_Table and display a warning to the admin indicating the token requires re-verification
6. WHEN a Cloudflare API error occurs, THE AuditLogger SHALL log the failure including the API endpoint called, HTTP status code received, Cloudflare error message, associated token ID, and the operation that was being attempted

### Requirement 14: Extension Registration and Structure

**User Story:** As an admin, I want the extension to follow Blueprint framework conventions, so that it installs and operates consistently with other Blueprint extensions.

#### Acceptance Criteria

1. THE Extension SHALL provide a conf.yml manifest containing an `info` section (with `identifier` set to "subdomain", `name`, `description`, `version`, and `target` fields) and an `admin` section (with `view` referencing the admin Blade template path and `controller` referencing the admin controller path)
2. THE Extension SHALL provide a controller class named `subdomainExtensionController` in the namespace `Pterodactyl\Http\Controllers\Admin\Extensions\subdomain` implementing `index` (GET), `post` (POST), `put` (PUT), `update` (PATCH), and `delete` (DELETE with `target` and `id` parameters) methods, which the Blueprint framework automatically routes at `/admin/extensions/subdomain/`
3. THE Extension SHALL store all configuration, subdomain mappings, token data, cache, and audit log entries exclusively using BlueprintExtensionLibrary's dbGet, dbSet, dbGetMany, dbSetMany, and dbForget methods with the table name "subdomain" as the first argument, and SHALL NOT create custom database tables or use direct database queries
4. THE Extension SHALL provide Blade templates for the admin interface that extend Pterodactyl's admin layout (using `@extends` and `@section` directives consistent with existing extension views) and render within the existing admin panel navigation structure
5. WHEN the Blueprint framework loads installed extensions, THE Extension SHALL be discoverable via its conf.yml identifier "subdomain" and SHALL appear in the admin extensions list with its declared name, description, and version
