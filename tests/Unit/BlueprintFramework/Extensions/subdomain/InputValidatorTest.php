<?php

namespace Pterodactyl\Tests\Unit\BlueprintFramework\Extensions\subdomain;

use PHPUnit\Framework\TestCase;
use Pterodactyl\BlueprintFramework\Extensions\subdomain\InputValidator;
use Pterodactyl\BlueprintFramework\Extensions\subdomain\ValidationResult;

class InputValidatorTest extends TestCase
{
    private InputValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new InputValidator();
    }

    // ================================================================
    // validateApiToken tests
    // ================================================================

    public function testValidApiTokenAccepted(): void
    {
        // Exactly 40 alphanumeric + underscore + hyphen chars
        $result = $this->validator->validateApiToken('abcdefghijklmnopqrstuvwxyz01234567890ABC');
        $this->assertTrue($result->valid);
    }

    public function testValidApiTokenWithUnderscoresAndHyphens(): void
    {
        $result = $this->validator->validateApiToken('abcdefghij_klmnopqrst-uvwxyz0123456789AB');
        $this->assertTrue($result->valid);
    }

    public function testApiTokenTooShort(): void
    {
        $result = $this->validator->validateApiToken('abc123');
        $this->assertFalse($result->valid);
        $this->assertNotEmpty($result->message);
    }

    public function testApiTokenTooLong(): void
    {
        $result = $this->validator->validateApiToken(str_repeat('a', 41));
        $this->assertFalse($result->valid);
    }

    public function testApiTokenWithInvalidChars(): void
    {
        $result = $this->validator->validateApiToken('abcdefghijklmnopqrstuvwxyz0123456789!@#$');
        $this->assertFalse($result->valid);
    }

    public function testApiTokenEmpty(): void
    {
        $result = $this->validator->validateApiToken('');
        $this->assertFalse($result->valid);
    }

    // ================================================================
    // validateSubdomainName tests
    // ================================================================

    public function testValidSubdomainName(): void
    {
        $result = $this->validator->validateSubdomainName('my-server1');
        $this->assertTrue($result->valid);
    }

    public function testValidSingleCharSubdomain(): void
    {
        $result = $this->validator->validateSubdomainName('a');
        $this->assertTrue($result->valid);
    }

    public function testValidTwoCharSubdomain(): void
    {
        $result = $this->validator->validateSubdomainName('ab');
        $this->assertTrue($result->valid);
    }

    public function testSubdomainMaxLength63(): void
    {
        // 63 characters - first and last are alphanumeric, middle can be alnum+hyphens
        $name = 'a' . str_repeat('b', 61) . 'c';
        $result = $this->validator->validateSubdomainName($name);
        $this->assertTrue($result->valid);
    }

    public function testSubdomainExceeding63Chars(): void
    {
        $name = 'a' . str_repeat('b', 62) . 'c'; // 64 chars
        $result = $this->validator->validateSubdomainName($name);
        $this->assertFalse($result->valid);
    }

    public function testSubdomainWithDots(): void
    {
        $result = $this->validator->validateSubdomainName('my.subdomain');
        $this->assertFalse($result->valid);
        $this->assertStringContainsString('Dots', $result->message);
    }

    public function testSubdomainWithUpperCase(): void
    {
        // Sanitization converts to lowercase, so this should pass
        $result = $this->validator->validateSubdomainName('MyServer');
        $this->assertTrue($result->valid);
    }

    public function testSubdomainWithLeadingWhitespace(): void
    {
        // Sanitization trims whitespace
        $result = $this->validator->validateSubdomainName('  myserver  ');
        $this->assertTrue($result->valid);
    }

    public function testSubdomainWithLeadingHyphen(): void
    {
        // After sanitization, leading hyphens are stripped, so '-test' becomes 'test'
        $result = $this->validator->validateSubdomainName('-test');
        $this->assertTrue($result->valid);
    }

    public function testSubdomainEmpty(): void
    {
        $result = $this->validator->validateSubdomainName('');
        $this->assertFalse($result->valid);
    }

    public function testSubdomainOnlyInvalidChars(): void
    {
        // After sanitization, all chars removed = empty
        $result = $this->validator->validateSubdomainName('!!!');
        $this->assertFalse($result->valid);
    }

    public function testSubdomainWildcardRejectedByDefault(): void
    {
        $result = $this->validator->validateSubdomainName('*test');
        $this->assertFalse($result->valid);
        $this->assertStringContainsString('Wildcard', $result->message);
    }

    public function testSubdomainWildcardAllowedWhenEnabled(): void
    {
        // '*test' after sanitization becomes 'test' (star is removed as invalid char)
        $result = $this->validator->validateSubdomainName('*test', wildcardAllowed: true);
        $this->assertTrue($result->valid);
    }

    public function testSubdomainFqdnLengthExceeded(): void
    {
        // Create a name that's fine on its own but exceeds 253 when combined with zone
        $name = str_repeat('a', 63); // max label
        $zoneName = str_repeat('b', 60) . '.' . str_repeat('c', 60) . '.' . str_repeat('d', 60) . '.com'; // long zone
        $result = $this->validator->validateSubdomainName($name, zoneName: $zoneName);
        $this->assertFalse($result->valid);
        $this->assertStringContainsString('253', $result->message);
    }

    // ================================================================
    // validateRecordType tests
    // ================================================================

    public function testRecordTypeAValid(): void
    {
        $result = $this->validator->validateRecordType('A');
        $this->assertTrue($result->valid);
    }

    public function testRecordTypeAAAAValid(): void
    {
        $result = $this->validator->validateRecordType('AAAA');
        $this->assertTrue($result->valid);
    }

    public function testRecordTypeCNAMEValid(): void
    {
        $result = $this->validator->validateRecordType('CNAME');
        $this->assertTrue($result->valid);
    }

    public function testRecordTypeLowercaseInvalid(): void
    {
        $result = $this->validator->validateRecordType('a');
        $this->assertFalse($result->valid);
    }

    public function testRecordTypeMXInvalid(): void
    {
        $result = $this->validator->validateRecordType('MX');
        $this->assertFalse($result->valid);
    }

    public function testRecordTypeTXTInvalid(): void
    {
        $result = $this->validator->validateRecordType('TXT');
        $this->assertFalse($result->valid);
    }

    public function testRecordTypeEmptyInvalid(): void
    {
        $result = $this->validator->validateRecordType('');
        $this->assertFalse($result->valid);
    }

    // ================================================================
    // validateTarget tests
    // ================================================================

    public function testTargetValidIPv4ForA(): void
    {
        $result = $this->validator->validateTarget('192.168.1.1', 'A');
        $this->assertTrue($result->valid);
    }

    public function testTargetValidIPv4ForAPublic(): void
    {
        $result = $this->validator->validateTarget('8.8.8.8', 'A');
        $this->assertTrue($result->valid);
    }

    public function testTargetInvalidIPv4ForA(): void
    {
        $result = $this->validator->validateTarget('999.999.999.999', 'A');
        $this->assertFalse($result->valid);
    }

    public function testTargetIPv6RejectedForA(): void
    {
        $result = $this->validator->validateTarget('2001:db8::1', 'A');
        $this->assertFalse($result->valid);
    }

    public function testTargetHostnameRejectedForA(): void
    {
        $result = $this->validator->validateTarget('example.com', 'A');
        $this->assertFalse($result->valid);
    }

    public function testTargetValidIPv6ForAAAA(): void
    {
        $result = $this->validator->validateTarget('2001:db8::1', 'AAAA');
        $this->assertTrue($result->valid);
    }

    public function testTargetFullIPv6ForAAAA(): void
    {
        $result = $this->validator->validateTarget('2001:0db8:85a3:0000:0000:8a2e:0370:7334', 'AAAA');
        $this->assertTrue($result->valid);
    }

    public function testTargetIPv4RejectedForAAAA(): void
    {
        $result = $this->validator->validateTarget('192.168.1.1', 'AAAA');
        $this->assertFalse($result->valid);
    }

    public function testTargetValidHostnameForCNAME(): void
    {
        $result = $this->validator->validateTarget('server.example.com', 'CNAME');
        $this->assertTrue($result->valid);
    }

    public function testTargetIPv4RejectedForCNAME(): void
    {
        $result = $this->validator->validateTarget('192.168.1.1', 'CNAME');
        $this->assertFalse($result->valid);
    }

    public function testTargetIPv6RejectedForCNAME(): void
    {
        $result = $this->validator->validateTarget('2001:db8::1', 'CNAME');
        $this->assertFalse($result->valid);
    }

    public function testTargetSingleLabelRejectedForCNAME(): void
    {
        // CNAME needs at least two labels (host.domain)
        $result = $this->validator->validateTarget('localhost', 'CNAME');
        $this->assertFalse($result->valid);
    }

    public function testTargetInvalidHostnameForCNAME(): void
    {
        $result = $this->validator->validateTarget('invalid..hostname', 'CNAME');
        $this->assertFalse($result->valid);
    }

    // ================================================================
    // validateZoneId tests
    // ================================================================

    public function testValidZoneId(): void
    {
        $result = $this->validator->validateZoneId('0123456789abcdef0123456789abcdef');
        $this->assertTrue($result->valid);
    }

    public function testZoneIdWithUppercase(): void
    {
        // Zone IDs must be lowercase hex
        $result = $this->validator->validateZoneId('0123456789ABCDEF0123456789ABCDEF');
        $this->assertFalse($result->valid);
    }

    public function testZoneIdTooShort(): void
    {
        $result = $this->validator->validateZoneId('0123456789abcdef');
        $this->assertFalse($result->valid);
    }

    public function testZoneIdTooLong(): void
    {
        $result = $this->validator->validateZoneId('0123456789abcdef0123456789abcdef0');
        $this->assertFalse($result->valid);
    }

    public function testZoneIdWithInvalidChars(): void
    {
        $result = $this->validator->validateZoneId('0123456789abcdef0123456789abcdeg');
        $this->assertFalse($result->valid);
    }

    public function testZoneIdEmpty(): void
    {
        $result = $this->validator->validateZoneId('');
        $this->assertFalse($result->valid);
    }

    // ================================================================
    // sanitizeSubdomainName tests
    // ================================================================

    public function testSanitizeTrimsWhitespace(): void
    {
        $result = $this->validator->sanitizeSubdomainName('  hello  ');
        $this->assertSame('hello', $result);
    }

    public function testSanitizeConvertsToLowercase(): void
    {
        $result = $this->validator->sanitizeSubdomainName('MyServer');
        $this->assertSame('myserver', $result);
    }

    public function testSanitizeRemovesInvalidChars(): void
    {
        $result = $this->validator->sanitizeSubdomainName('my_server!@#$%^&*()');
        $this->assertSame('myserver', $result);
    }

    public function testSanitizeRemovesLeadingHyphens(): void
    {
        $result = $this->validator->sanitizeSubdomainName('--myserver');
        $this->assertSame('myserver', $result);
    }

    public function testSanitizeRemovesTrailingHyphens(): void
    {
        $result = $this->validator->sanitizeSubdomainName('myserver--');
        $this->assertSame('myserver', $result);
    }

    public function testSanitizePreservesValidHyphensInMiddle(): void
    {
        $result = $this->validator->sanitizeSubdomainName('my-server-1');
        $this->assertSame('my-server-1', $result);
    }

    public function testSanitizeIdempotent(): void
    {
        $input = '  My--Server_Name!!  ';
        $first = $this->validator->sanitizeSubdomainName($input);
        $second = $this->validator->sanitizeSubdomainName($first);
        $this->assertSame($first, $second);
    }

    public function testSanitizeRemovesDots(): void
    {
        $result = $this->validator->sanitizeSubdomainName('my.server');
        $this->assertSame('myserver', $result);
    }

    public function testSanitizeEmptyInput(): void
    {
        $result = $this->validator->sanitizeSubdomainName('');
        $this->assertSame('', $result);
    }

    public function testSanitizeOnlyHyphens(): void
    {
        $result = $this->validator->sanitizeSubdomainName('---');
        $this->assertSame('', $result);
    }
}
