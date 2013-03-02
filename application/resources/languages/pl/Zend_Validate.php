<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Translate
 * @subpackage Ressource
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id:$
 */

/**
 * EN-Revision: 22668
 */
return array(
    // Zend_Validate_Alnum
    "Invalid type given. String, integer or float expected" => "Podano nieprawidłowy typ. Oczekiwany string, integer lub float",
    "'%value%' contains characters which are non alphabetic and no digits" => "'%value%' zawiera znaki, które nie są literami alfabetu ani cyframi",
    "'%value%' is an empty string" => "'%value%' jest pusta",

    // Zend_Validate_Alpha
    "Invalid type given. String expected" => "Podano nieprawidłowy typ. Oczekiwany string",
    "'%value%' contains non alphabetic characters" => "'%value%' zawiera znaki z poza alfabetu",
    "'%value%' is an empty string" => "'%value%' jest pusta",

    // Zend_Validate_Barcode
    "'%value%' failed checksum validation" => "Nie powiodło się sprawdzanie sumy kontrolnej dla '%value%'",
    "'%value%' contains invalid characters" => "'%value%' zawiera nieprawidłowe znaki",
    "'%value%' should have a length of %length% characters" => "'%value%' powinna mieć długość %length% znaków",
    "Invalid type given. String expected" => "Podano nieprawidłowy typ. Oczekiwany string",

    // Zend_Validate_Between
    "'%value%' is not between '%min%' and '%max%', inclusively" => "'%value%' nie jest pomiędzy '%min%' i '%max%', włącznie",
    "'%value%' is not strictly between '%min%' and '%max%'" => "'%value%' nie jest ściśle pomiędzy '%min%' i '%max%'",

    // Zend_Validate_Callback
    "'%value%' is not valid" => "'%value%' jest nieprawidłowa",
    "An exception has been raised within the callback" => "An exception has been raised within the callback",

    // Zend_Validate_Ccnum
    "'%value%' must contain between 13 and 19 digits" => "'%value%' musi zawierać od 13 do 19 cyfr",
    "Luhn algorithm (mod-10 checksum) failed on '%value%'" => "Algorytm Luhn (mod-10 checksum) nie powiódł się dla '%value%'",

    // Zend_Validate_CreditCard
    "'%value%' seems to contain an invalid checksum" => "'%value%' seems to contain an invalid checksum",
    "'%value%' must contain only digits" => "'%value%' must contain only digits",
    "Invalid type given. String expected" => "Podano nieprawidłowy typ. Oczekiwany string",
    "'%value%' contains an invalid amount of digits" => "'%value%' contains an invalid amount of digits",
    "'%value%' is not from an allowed institute" => "'%value%' is not from an allowed institute",
    "'%value%' seems to be an invalid creditcard number" => "'%value%' seems to be an invalid creditcard number",
    "An exception has been raised while validating '%value%'" => "An exception has been raised while validating '%value%'",

    // Zend_Validate_Date
    "Invalid type given. String, integer, array or Zend_Date expected" => "Podano nieprawidłowy typ. Oczekiwany string, integer, array lub Zend_Date",
    "'%value%' does not appear to be a valid date" => "'%value%' nie wydaje się być poprawną datą",
    "'%value%' does not fit the date format '%format%'" => "'%value%' nie pasuje do formatu daty '%format%'",

    // Zend_Validate_Db_Abstract
    "No record matching '%value%' was found" => "Brak wpisów pasujących do '%value%'",
    "A record matching '%value%' was found" => "Jest wpis pasujący do '%value%'",

    // Zend_Validate_Digits
    "Invalid type given. String, integer or float expected" => "Podano nieprawidłowy typ. Oczekiwany string, integer lub float",
    "'%value%' must contain only digits" => "'%value%' must contain only digits",
    "'%value%' is an empty string" => "'%value%' jest pusta",

    // Zend_Validate_EmailAddress
    "Invalid type given. String expected" => "Podano nieprawidłowy typ. Oczekiwany string",
    "'%value%' is no valid email address in the basic format local-part@hostname" => "'%value%' nie jest prawidłowym adresem e-mail w podstawowym formacie część-lokalna@nazwa-hosta",
    "'%hostname%' is no valid hostname for email address '%value%'" => "'%hostname%' jest nieprawidłową nazwą hosta dla adresu e-mail '%value%'",
    "'%hostname%' does not appear to have a valid MX record for the email address '%value%'" => "'%hostname%' wydaje się mieć nieprawidłowy rekord MX dla adresu e-mail '%value%'",
    "'%hostname%' is not in a routable network segment. The email address '%value%' should not be resolved from public network" => "'%hostname%' nie jest w routowanym segmencie sieci. Adres e-mail '%value%' nie będzie rozpoznany w sieci publicznej",
    "'%localPart%' can not be matched against dot-atom format" => "'%localPart%' can not be matched against dot-atom format",
    "'%localPart%' can not be matched against quoted-string format" => "'%localPart%' can not be matched against quoted-string format",
    "'%localPart%' is no valid local part for email address '%value%'" => "'%localPart%' jest niepoprawną lokalną częścią adresu e-mail '%value%'",
    "'%value%' exceeds the allowed length" => "'%value%' przekracza dopuszczalna długość",

    // Zend_Validate_File_Count
    "Too many files, maximum '%max%' are allowed but '%count%' are given" => "Zbyt wiele plików, maksymalnie '%max%' jest dozwolone, a '%count%' zostało podane",
    "Too few files, minimum '%min%' are expected but '%count%' are given" => "Zbyt mało plików, minimalnie '%min%' jest dozwolone, a '%count%' zostało podane",

    // Zend_Validate_File_Crc32
    "File '%value%' does not match the given crc32 hashes" => "File '%value%' does not match the given crc32 hashes",
    "A crc32 hash could not be evaluated for the given file" => "A crc32 hash could not be evaluated for the given file",
    "File '%value%' is not readable or does not exist" => "File '%value%' is not readable or does not exist",

    // Zend_Validate_File_ExcludeExtension
    "File '%value%' has a false extension" => "File '%value%' has a false extension",
    "File '%value%' is not readable or does not exist" => "File '%value%' is not readable or does not exist",

    // Zend_Validate_File_ExcludeMimeType
    "File '%value%' has a false mimetype of '%type%'" => "File '%value%' has a false mimetype of '%type%'",
    "The mimetype of file '%value%' could not be detected" => "The mimetype of file '%value%' could not be detected",
    "File '%value%' is not readable or does not exist" => "File '%value%' is not readable or does not exist",

    // Zend_Validate_File_Exists
    "File '%value%' does not exist" => "Plik '%value%' nie istnieje",

    // Zend_Validate_File_Extension
    "File '%value%' has a false extension" => "Plik '%value%' ma złe rozszerzenie",
    "File '%value%' is not readable or does not exist" => "Nie można odczytać pliku '%value%' lub nie istnieje",

    // Zend_Validate_File_FilesSize
    "All files in sum should have a maximum size of '%max%' but '%size%' were detected" => "All files in sum should have a maximum size of '%max%' but '%size%' were detected",
    "All files in sum should have a minimum size of '%min%' but '%size%' were detected" => "All files in sum should have a minimum size of '%min%' but '%size%' were detected",
    "One or more files can not be read" => "One or more files can not be read",

    // Zend_Validate_File_Hash
    "File '%value%' does not match the given hashes" => "File '%value%' does not match the given hashes",
    "A hash could not be evaluated for the given file" => "A hash could not be evaluated for the given file",
    "File '%value%' is not readable or does not exist" => "File '%value%' is not readable or does not exist",

    // Zend_Validate_File_ImageSize
    "Maximum allowed width for image '%value%' should be '%maxwidth%' but '%width%' detected" => "Maximum allowed width for image '%value%' should be '%maxwidth%' but '%width%' detected",
    "Minimum expected width for image '%value%' should be '%minwidth%' but '%width%' detected" => "Minimum expected width for image '%value%' should be '%minwidth%' but '%width%' detected",
    "Maximum allowed height for image '%value%' should be '%maxheight%' but '%height%' detected" => "Maximum allowed height for image '%value%' should be '%maxheight%' but '%height%' detected",
    "Minimum expected height for image '%value%' should be '%minheight%' but '%height%' detected" => "Minimum expected height for image '%value%' should be '%minheight%' but '%height%' detected",
    "The size of image '%value%' could not be detected" => "The size of image '%value%' could not be detected",
    "File '%value%' is not readable or does not exist" => "File '%value%' is not readable or does not exist",

    // Zend_Validate_File_IsCompressed
    "File '%value%' is not compressed, '%type%' detected" => "File '%value%' is not compressed, '%type%' detected",
    "The mimetype of file '%value%' could not be detected" => "The mimetype of file '%value%' could not be detected",
    "File '%value%' is not readable or does not exist" => "File '%value%' is not readable or does not exist",

    // Zend_Validate_File_IsImage
    "File '%value%' is no image, '%type%' detected" => "File '%value%' is no image, '%type%' detected",
    "The mimetype of file '%value%' could not be detected" => "The mimetype of file '%value%' could not be detected",
    "File '%value%' is not readable or does not exist" => "File '%value%' is not readable or does not exist",

    // Zend_Validate_File_Md5
    "File '%value%' does not match the given md5 hashes" => "File '%value%' does not match the given md5 hashes",
    "A md5 hash could not be evaluated for the given file" => "A md5 hash could not be evaluated for the given file",
    "File '%value%' is not readable or does not exist" => "File '%value%' is not readable or does not exist",

    // Zend_Validate_File_MimeType
    "File '%value%' has a false mimetype of '%type%'" => "File '%value%' has a false mimetype of '%type%'",
    "The mimetype of file '%value%' could not be detected" => "The mimetype of file '%value%' could not be detected",
    "File '%value%' is not readable or does not exist" => "File '%value%' is not readable or does not exist",

    // Zend_Validate_File_NotExists
    "File '%value%' exists" => "File '%value%' exists",

    // Zend_Validate_File_Sha1
    "File '%value%' does not match the given sha1 hashes" => "File '%value%' does not match the given sha1 hashes",
    "A sha1 hash could not be evaluated for the given file" => "A sha1 hash could not be evaluated for the given file",
    "File '%value%' is not readable or does not exist" => "File '%value%' is not readable or does not exist",

    // Zend_Validate_File_Size
    "Maximum allowed size for file '%value%' is '%max%' but '%size%' detected" => "Maximum allowed size for file '%value%' is '%max%' but '%size%' detected",
    "Minimum expected size for file '%value%' is '%min%' but '%size%' detected" => "Minimum expected size for file '%value%' is '%min%' but '%size%' detected",
    "File '%value%' is not readable or does not exist" => "File '%value%' is not readable or does not exist",

    // Zend_Validate_File_Upload
    "File '%value%' exceeds the defined ini size" => "File '%value%' exceeds the defined ini size",
    "File '%value%' exceeds the defined form size" => "Plik '%value%' jest za duży",
    "File '%value%' was only partially uploaded" => "File '%value%' was only partially uploaded",
    "File '%value%' was not uploaded" => "File '%value%' was not uploaded",
    "No temporary directory was found for file '%value%'" => "No temporary directory was found for file '%value%'",
    "File '%value%' can't be written" => "File '%value%' can't be written",
    "A PHP extension returned an error while uploading the file '%value%'" => "A PHP extension returned an error while uploading the file '%value%'",
    "File '%value%' was illegally uploaded. This could be a possible attack" => "File '%value%' was illegally uploaded. This could be a possible attack",
    "File '%value%' was not found" => "File '%value%' was not found",
    "Unknown error while uploading file '%value%'" => "Unknown error while uploading file '%value%'",

    // Zend_Validate_File_WordCount
    "Too much words, maximum '%max%' are allowed but '%count%' were counted" => "Too much words, maximum '%max%' are allowed but '%count%' were counted",
    "Too less words, minimum '%min%' are expected but '%count%' were counted" => "Too less words, minimum '%min%' are expected but '%count%' were counted",
    "File '%value%' is not readable or does not exist" => "File '%value%' is not readable or does not exist",

    // Zend_Validate_Float
    "Invalid type given. String, integer or float expected" => "Podano nieprawidłowy typ. Oczekiwany string, integer lub float",
    "'%value%' does not appear to be a float" => "'%value%' does not appear to be a float",

    // Zend_Validate_GreaterThan
    "'%value%' is not greater than '%min%'" => "'%value%' is not greater than '%min%'",

    // Zend_Validate_Hex
    "Invalid type given. String expected" => "Podano nieprawidłowy typ. Oczekiwany string",
    "'%value%' has not only hexadecimal digit characters" => "'%value%' has not only hexadecimal digit characters",

    // Zend_Validate_Hostname
    "Invalid type given. String expected" => "Podano nieprawidłowy typ. Oczekiwany string",
    "'%value%' appears to be an IP address, but IP addresses are not allowed" => "'%value%' appears to be an IP address, but IP addresses are not allowed",
    "'%value%' appears to be a DNS hostname but cannot match TLD against known list" => "'%value%' appears to be a DNS hostname but cannot match TLD against known list",
    "'%value%' appears to be a DNS hostname but contains a dash in an invalid position" => "'%value%' appears to be a DNS hostname but contains a dash in an invalid position",
    "'%value%' appears to be a DNS hostname but cannot match against hostname schema for TLD '%tld%'" => "'%value%' appears to be a DNS hostname but cannot match against hostname schema for TLD '%tld%'",
    "'%value%' appears to be a DNS hostname but cannot extract TLD part" => "'%value%' appears to be a DNS hostname but cannot extract TLD part",
    "'%value%' does not match the expected structure for a DNS hostname" => "'%value%' does not match the expected structure for a DNS hostname",
    "'%value%' does not appear to be a valid local network name" => "'%value%' does not appear to be a valid local network name",
    "'%value%' appears to be a local network name but local network names are not allowed" => "'%value%' appears to be a local network name but local network names are not allowed",
    "'%value%' appears to be a DNS hostname but the given punycode notation cannot be decoded" => "'%value%' appears to be a DNS hostname but the given punycode notation cannot be decoded",

    // Zend_Validate_Iban
    "Unknown country within the IBAN '%value%'" => "Unknown country within the IBAN '%value%'",
    "'%value%' has a false IBAN format" => "'%value%' has a false IBAN format",
    "'%value%' has failed the IBAN check" => "'%value%' has failed the IBAN check",

    // Zend_Validate_Identical
    "The two given tokens do not match" => "Dwa podane tokeny nie pasują",
    "No token was provided to match against" => "Żaden token nie został podany",

    // Zend_Validate_InArray
    "'%value%' was not found in the haystack" => "'%value%' was not found in the haystack",

    // Zend_Validate_Int
    "Invalid type given. String or integer expected" => "Podano nieprawidłowy typ. Oczekiwany string lub integer",
    "'%value%' does not appear to be an integer" => "'%value%' does not appear to be an integer",

    // Zend_Validate_Ip
    "Invalid type given. String expected" => "Podano nieprawidłowy typ. Oczekiwany string",
    "'%value%' does not appear to be a valid IP address" => "'%value%' does not appear to be a valid IP address",

    // Zend_Validate_Isbn
    "Invalid type given. String or integer expected" => "Podano nieprawidłowy typ. Oczekiwany string lub integer",
    "'%value%' is no valid ISBN number" => "'%value%' is no valid ISBN number",

    // Zend_Validate_LessThan
    "'%value%' is not less than '%max%'" => "'%value%' is not less than '%max%'",

    // Zend_Validate_NotEmpty
    "Invalid type given. String, integer, float, boolean or array expected" => "Podano nieprawidłowy typ. Oczekiwany string, integer, float, boolean lub array",
    "Value is required and can't be empty" => "To pole nie może być puste",

    // Zend_Validate_PostCode
    "Invalid type given. String or integer expected" => "Podano nieprawidłowy typ. Oczekiwany string lub integer",
    "'%value%' does not appear to be a postal code" => "'%value%' does not appear to be a postal code",

    // Zend_Validate_Regex
    "Invalid type given. String, integer or float expected" => "Podano nieprawidłowy typ. Oczekiwany string, integer lub float",
    "'%value%' does not match against pattern '%pattern%'" => "Dozwolone tylko polskie litery i cyfry",
    "There was an internal error while using the pattern '%pattern%'" => "There was an internal error while using the pattern '%pattern%'",

    // Zend_Validate_Sitemap_Changefreq
    "'%value%' is no valid sitemap changefreq" => "'%value%' is no valid sitemap changefreq",
    "Invalid type given. String expected" => "Podano nieprawidłowy typ. Oczekiwany string",

    // Zend_Validate_Sitemap_Lastmod
    "'%value%' is no valid sitemap lastmod" => "'%value%' is no valid sitemap lastmod",
    "Invalid type given. String expected" => "Podano nieprawidłowy typ. Oczekiwany string",

    // Zend_Validate_Sitemap_Loc
    "'%value%' is no valid sitemap location" => "'%value%' is no valid sitemap location",
    "Invalid type given. String expected" => "Podano nieprawidłowy typ. Oczekiwany string",

    // Zend_Validate_Sitemap_Priority
    "'%value%' is no valid sitemap priority" => "'%value%' is no valid sitemap priority",
    "Invalid type given. Numeric string, integer or float expected" => "Podano nieprawidłowy typ. Numeric string, integer or float expected",

    // Zend_Validate_StringLength
    "Invalid type given. String expected" => "Podano nieprawidłowy typ. Oczekiwany string",
    "'%value%' is less than %min% characters long" => "Minimalna dopuszczalna ilość znaków wynosi %min%. Podano mniej",
    "'%value%' is more than %max% characters long" => "Maksymalna dopuszczalna ilość znaków wynosi %min%. Podano więcej",
);
