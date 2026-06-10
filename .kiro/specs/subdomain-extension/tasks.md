# Implementation Plan: Subdomain Extension

## Overview

This implementation plan builds the Subdomain Extension for the Pterodactyl Blueprint framework. The extension enables admins to manage Cloudflare DNS subdomains for their servers. Implementation follows the established Blueprint extension patterns (conf.yml manifest, controller with standard methods, BlueprintExtensionLibrary for data persistence, Blade templates for admin views). Tasks are ordered to build foundational services first, then the controller logic, then the admin UI, and finally integration wiring.

## Tasks

- [x] 1. Set up extension structure and configuration
  - [x] 1.1 Create the conf.yml manifest and extension directory structure
    - Create `.blueprint/extensions/subdomain/` directory with `assets/`, `private/`, `public/`, and `views/` subdirectories
    - Create `conf.yml` with `info` section (identifier: "subdomain", name: "Subdomain", description, version: "1.0.0", target: "beta-2026-01") and `admin` section (view and controller paths)
    - Create placeholder `subdomain.png` icon in assets
    - _Requirements: 14.1, 14.5_

  - [x] 1.2 Create the InputValidator service class
    - Create `app/BlueprintFramework/Extensions/subdomain/InputValidator.php`
    - Implement `validateApiToken(string $token): ValidationResult` — validates against `^[A-Za-z0-9_-]{40}$`
    - Implement `validateSubdomainName(string $name): ValidationResult` — validates against `^[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?$`, rejects dots, enforces max 63 char label / 253 char FQDN
    - Implement `validateRecordType(string $type): ValidationResult` — accepts only A, AAAA, CNAME
    - Implement `validateTarget(string $target, string $type): ValidationResult` — IPv4 for A, IPv6 for AAAA, hostname (not IP) for CNAME
    - Implement `validateZoneId(string $zoneId): ValidationResult` — validates against `^[0-9a-f]{32}$`
    - Implement `sanitizeSubdomainName(string $name): string` — trim, lowercase, remove invalid chars, strip leading/trailing hyphens
    - Implement wildcard check: reject `*` prefix unless `wildcard_allowed` is true
    - Apply sanitization before validation (requirement 9.8)
    - _Requirements: 1.1, 4.3, 4.4, 4.5, 4.6, 4.7, 9.1, 9.2, 9.3, 9.4, 9.5, 9.8_

  - [ ]* 1.3 Write property tests for InputValidator
    - **Property 1: Token Format Validation** — any string matching `^[A-Za-z0-9_-]{40}$` accepted, all others rejected
    - **Property 7: Subdomain Name Validation** — regex match, no dots, max 63 chars, wildcard rules
    - **Property 8: Subdomain Sanitization Idempotence** — `sanitize(sanitize(x)) == sanitize(x)`
    - **Property 9: Record Type Validation** — only A, AAAA, CNAME accepted
    - **Property 10: Target Validation by Record Type** — IPv4 for A, IPv6 for AAAA, hostname for CNAME
    - **Property 11: Zone ID Format Validation** — only `^[0-9a-f]{32}$` accepted
    - **Validates: Requirements 1.1, 4.3-4.7, 9.1-9.5**

  - [x] 1.4 Create the EncryptionService class
    - Create `app/BlueprintFramework/Extensions/subdomain/EncryptionService.php`
    - Implement `encrypt(string $plaintext): string` using Laravel `Crypt::encryptString()`
    - Implement `decrypt(string $ciphertext): string` using Laravel `Crypt::decryptString()` with MAC verification
    - Implement `isEncrypted(string $value): bool` to check encrypted payload structure
    - Handle `DecryptException` for tampered/corrupted ciphertext
    - _Requirements: 1.7, 8.1, 8.2, 8.3_

  - [ ]* 1.5 Write property tests for EncryptionService
    - **Property 3: Encryption Round-Trip** — `decrypt(encrypt(x)) == x` for any valid token
    - **Property 4: Encryption Tamper Detection** — modifying any byte of ciphertext causes decryption failure
    - **Validates: Requirements 1.6, 8.1, 8.2, 8.3**

- [x] 2. Implement core services
  - [x] 2.1 Create the CloudflareService class
    - Create `app/BlueprintFramework/Extensions/subdomain/CloudflareService.php`
    - Implement `verifyToken(string $token): TokenVerificationResult` — calls `GET /user/tokens/verify` with 10s timeout
    - Implement `listZones(string $token): array` — calls `GET /zones`, parses zone list
    - Implement `listDnsRecords(string $token, string $zoneId): array` — calls `GET /zones/{zone_id}/dns_records`
    - Implement `createDnsRecord(string $token, string $zoneId, string $name, string $type, string $content, bool $proxied, int $ttl): DnsRecordResult` — calls `POST /zones/{zone_id}/dns_records` with 10s timeout
    - Implement `deleteDnsRecord(string $token, string $zoneId, string $recordId): bool` — calls `DELETE /zones/{zone_id}/dns_records/{record_id}`
    - Implement retry logic with exponential backoff (1s, 2s, 4s) for network failures, up to 3 attempts
    - Handle Cloudflare 429 responses: respect `retry-after` header or default 60s cooldown
    - Validate permission set includes Zone:Read and DNS:Edit on verification
    - _Requirements: 1.2, 1.3, 1.5, 1.6, 4.11, 5.1, 11.3, 11.4, 13.1, 13.4_

  - [ ]* 2.2 Write property test for CloudflareService permission validation
    - **Property 2: Permission Set Validation** — token accepted if and only if permissions contain both Zone:Read and DNS:Edit
    - **Validates: Requirements 1.3, 1.5**

  - [x] 2.3 Create the AuditLogger service class
    - Create `app/BlueprintFramework/Extensions/subdomain/AuditLogger.php`
    - Inject `BlueprintExtensionLibrary` for data persistence
    - Implement `log(string $action, int $adminId, array $details): void` — stores entry with ISO 8601 timestamp, admin email, action type, resource type, resource ID, IP, result
    - Implement `getRecent(int $limit = 50): array` — retrieves entries in reverse chronological order
    - Implement `getByAction(string $action, int $limit = 50): array` — filters by action type
    - Manage `subdomain::audit_index` capped at 500 entries, removing oldest on overflow
    - Storage key pattern: `subdomain::audit_{timestamp}_{random}`
    - _Requirements: 1.10, 2.2, 4.14, 5.4, 7.3, 12.1, 12.2, 12.3, 12.4, 12.5_

  - [ ]* 2.4 Write property tests for AuditLogger
    - **Property 15: Audit Log Entry Completeness** — all required fields present and non-empty
    - **Property 16: Audit Log Index Cap** — index never exceeds 500 entries
    - **Property 17: Audit Log Chronological Ordering** — entries returned newest first
    - **Validates: Requirements 12.2, 12.3, 12.4, 12.5**

  - [x] 2.5 Create the RateLimiter service class
    - Create `app/BlueprintFramework/Extensions/subdomain/RateLimiter.php`
    - Implement per-admin subdomain creation limit (default 10/minute)
    - Implement global Cloudflare API call limit (configurable, default 30/minute)
    - Track sliding window using timestamps stored via BlueprintExtensionLibrary
    - Implement `checkLimit(int $adminId, string $action): RateLimitResult` — returns allowed/denied with seconds until reset
    - Handle Cloudflare 429 blocking (respect retry-after or default 60s)
    - _Requirements: 4.1, 4.2, 11.1, 11.2, 11.3, 11.4, 11.5, 11.6_

  - [ ]* 2.6 Write property test for RateLimiter
    - **Property 14: Rate Limiter Enforcement** — at most N requests allowed in 60-second window, (N+1)th rejected with 429
    - **Validates: Requirements 4.1, 11.1, 11.2**

- [x] 3. Checkpoint - Ensure all service tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [x] 4. Implement the controller
  - [x] 4.1 Create FormRequest validation classes
    - Create `app/Http/Requests/Admin/Extensions/Subdomain/SubdomainPostRequest.php` — validates token input for POST (add token)
    - Create `app/Http/Requests/Admin/Extensions/Subdomain/SubdomainPutRequest.php` — validates subdomain creation inputs (subdomain, zone_id, record_type, target, server_id)
    - Create `app/Http/Requests/Admin/Extensions/Subdomain/SubdomainUpdateRequest.php` — validates settings update inputs
    - All extend `AdminFormRequest` to enforce admin-only access
    - Include CSRF protection via `web` middleware group
    - _Requirements: 9.6, 9.7, 10.1, 10.2, 10.3, 10.4_

  - [x] 4.2 Create the subdomainExtensionController
    - Create `app/Http/Controllers/Admin/Extensions/subdomain/subdomainExtensionController.php`
    - Set namespace to `Pterodactyl\Http\Controllers\Admin\Extensions\subdomain`
    - Inject `BlueprintExtensionLibrary`, `CloudflareService`, `EncryptionService`, `InputValidator`, `AuditLogger`, `RateLimiter`
    - Implement `index()` method: retrieve tokens, load zone cache or refresh, load subdomain mappings with pagination (20/page), pass data to view
    - Implement `post(SubdomainPostRequest $request)` method: validate token format, verify with Cloudflare, check permissions, encrypt, store with UUID key, update tokens index, audit log
    - Implement `put(SubdomainPutRequest $request)` method: rate limit check, validate all inputs (subdomain, record_type, target, zone_id, server_id), check per-server subdomain limit (default 5), create DNS record via CloudflareService, store mapping, update zone/server indexes, audit log
    - Implement `update(SubdomainUpdateRequest $request)` method: validate settings values, reject entire batch on any invalid value, store valid settings, audit log changes with previous/new values
    - Implement `delete(string $target, string $id)` method: handle both token deletion (with active record check) and subdomain deletion (Cloudflare API + local cleanup), handle 404 from Cloudflare gracefully, update indexes, audit log
    - Handle duplicate token detection (requirement 1.9)
    - Token display masking: show only last 4 characters (requirement 1.11)
    - Zone cache logic: check TTL (default 300s), serve cached or refresh
    - Mark tokens as "needs_re_verification" on 401/403 from Cloudflare (requirement 13.5)
    - _Requirements: 1.1-1.11, 2.1-2.5, 3.1-3.8, 4.1-4.14, 5.1-5.8, 6.1-6.7, 7.1-7.6, 10.6, 10.7, 13.1-13.6, 14.2, 14.3_

  - [ ]* 4.3 Write property tests for controller token display
    - **Property 5: Token Display Masking** — display shows only last 4 characters, all preceding obscured
    - **Property 6: Token Deletion Consistency** — after deletion, token data gone from Settings_Table AND cf_tokens_index
    - **Validates: Requirements 1.9, 1.11, 2.1, 2.2**

  - [ ]* 4.4 Write property tests for subdomain record operations
    - **Property 12: Record Index Consistency Invariant** — zone and server indexes always match actual records
    - **Property 13: Cache Validity Determination** — cache valid iff (current_time - cached_at) < TTL
    - **Property 19: Pagination Boundary** — each page has at most 20 records, total pages = ceil(total/20)
    - **Property 20: Settings Preservation on Invalid Update** — invalid values never stored, previous preserved
    - **Property 21: Server Existence Validation** — assignment rejected for non-existent server IDs
    - **Validates: Requirements 4.10, 4.11, 5.2, 5.3, 3.2, 6.5, 7.4, 10.6**

- [x] 5. Checkpoint - Ensure controller logic is complete
  - Ensure all tests pass, ask the user if questions arise.

- [x] 6. Implement admin views
  - [x] 6.1 Create the main admin Blade template
    - Create `.blueprint/extensions/subdomain/views/admin/view.blade.php`
    - Extend Pterodactyl admin layout using `@extends` and `@section` directives (consistent with existing extensions)
    - Build tabbed interface: Tokens, Subdomains, Dashboard, Settings, Audit Log
    - **Tokens tab**: form to add new token (input + label), list of configured tokens showing masked value (last 4 chars), label, status, delete button
    - **Subdomains tab**: form to create subdomain (subdomain name, zone dropdown, record type dropdown, target input, server dropdown, proxied toggle), list of existing records with delete buttons
    - **Dashboard tab**: consolidated subdomain-to-server mapping table with FQDN, record type, target, server name, timestamp; filters by server and zone; pagination controls (20/page); conflict highlighting for duplicate subdomains in same zone; "unassigned" indicator for records without server
    - **Settings tab**: form with rate_limit_per_minute, cache_ttl, allowed_record_types, max_subdomains_per_server, wildcard_allowed fields showing current and default values
    - **Audit Log tab**: paginated list (25/page) with action type filter, reverse chronological order, showing timestamp, admin email, action, resource, result
    - Include CSRF tokens in all forms (`@csrf`)
    - Display setup-required view when no tokens configured
    - Show staleness indicator for cached zone data when Cloudflare unavailable
    - Show "needs re-verification" warning for invalid tokens
    - _Requirements: 1.11, 3.5, 3.6, 3.7, 6.1-6.7, 7.6, 8.4, 8.5, 12.5, 13.2, 13.3, 13.5, 14.4_

  - [ ]* 6.2 Write property test for subdomain display completeness
    - **Property 18: Subdomain Display Completeness** — rendered output contains FQDN, record type, target, server name (or "unassigned"), creation timestamp
    - **Validates: Requirements 3.5, 6.2**

- [x] 7. Integration and wiring
  - [x] 7.1 Wire extension into Blueprint framework routing
    - Verify controller is discoverable by Blueprint at the path specified in conf.yml
    - Ensure routes register automatically at `/admin/extensions/subdomain/` via `routes/blueprint.php`
    - Verify admin-only access enforcement via AdminFormRequest on all routes
    - Confirm extension appears in admin extensions list with correct name, description, and version
    - _Requirements: 10.1, 10.2, 10.5, 14.2, 14.5_

  - [x] 7.2 Wire service dependencies and test end-to-end flow
    - Ensure controller constructor properly injects all services (BlueprintExtensionLibrary, CloudflareService, EncryptionService, InputValidator, AuditLogger, RateLimiter)
    - Verify token add flow: format validation → Cloudflare verify → permission check → encrypt → store → audit
    - Verify subdomain create flow: rate limit → input validation → server check → Cloudflare create → store mapping → update indexes → audit
    - Verify subdomain delete flow: lookup record → Cloudflare delete → remove mapping → update indexes → audit
    - Verify settings update flow: validate all → reject batch on failure → store → audit with old/new values
    - Verify cache refresh flow: check TTL → serve cached or fetch fresh → update cache timestamp
    - Verify error handling: retry with backoff, graceful fallback to cached data, proper error messages
    - _Requirements: 1.1-1.11, 4.1-4.14, 5.1-5.8, 7.1-7.6, 13.1-13.6_

  - [ ]* 7.3 Write integration tests for complete extension flows
    - Test token configuration flow with mocked Cloudflare responses
    - Test subdomain CRUD operations end-to-end through controller
    - Test rate limiting behavior across multiple requests
    - Test cache invalidation and refresh cycle
    - Test error scenarios: network failure, invalid token, DNS conflict, deleted server
    - Test audit log recording for all operations
    - _Requirements: 1.1-1.11, 4.1-4.14, 5.1-5.8, 11.1-11.6, 12.1-12.5, 13.1-13.6_

- [x] 8. Final checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties from the design document
- Unit tests validate specific examples and edge cases
- The extension uses PHP following the established Blueprint extension patterns (see nebula extension)
- All data persistence uses BlueprintExtensionLibrary with "subdomain" table prefix — no custom database tables
- The Cloudflare API base URL is `https://api.cloudflare.com/client/v4/`
- Laravel's built-in `Crypt` facade handles AES-256-CBC encryption using the application's `APP_KEY`

## Task Dependency Graph

```json
{
  "waves": [
    { "id": 0, "tasks": ["1.1", "1.2", "1.4"] },
    { "id": 1, "tasks": ["1.3", "1.5", "2.1", "2.3", "2.5"] },
    { "id": 2, "tasks": ["2.2", "2.4", "2.6", "4.1"] },
    { "id": 3, "tasks": ["4.2"] },
    { "id": 4, "tasks": ["4.3", "4.4", "6.1"] },
    { "id": 5, "tasks": ["6.2", "7.1"] },
    { "id": 6, "tasks": ["7.2"] },
    { "id": 7, "tasks": ["7.3"] }
  ]
}
```
