# B2B Immutable Quote technical test

## Install

The module is located at `app/code/Logiscenter/ImmutableQuote`. Enable it and apply declarative schema, then compile and flush cache:

1. `bin/magento module:enable Logiscenter_ImmutableQuote`
2. `bin/magento setup:upgrade`
3. `bin/magento setup:di:compile` (production mode)
4. `bin/magento cache:flush`

Assign the B2B Immutable Quotes ACL permissions (`Logiscenter_ImmutableQuote::view`, `Logiscenter_ImmutableQuote::set_immutable`) to the API integration role. Configure the default rate in **Stores > Configuration > Sales > B2B Immutable Quotes**.

This module does not ship a custom account page or controller. Customers use Adobe Commerce B2B's
native **My Quotes** section (`negotiable_quote/quote/index`) to request, view, activate and check
out quotes; this module only merges a read-only "Immutable" column (with its filter) into that
native grid so the lock state is visible there. It adds a single boolean, `is_immutable`, directly
on the native `negotiable_quote` table and enforces it at every write boundary; the field can only
be changed through the REST API described in [API_DOCUMENTATION.md](API_DOCUMENTATION.md).

Translations for English and Spanish (`es_ES`) ship in `i18n/`; Magento loads them automatically
per store/admin locale, no extra configuration needed.

See [ARCHITECTURE.md](ARCHITECTURE.md) for the full rationale.

## Tests

PHPUnit unit tests live under `Test/Unit` and cover the model layer (rate limiting, audit
logging, the mutation guard, the immutable-quote cache and management service, and the address
comparator). Run them with Magento's own unit test suite, scoped to this module:

```
vendor/bin/phpunit -c dev/tests/unit/phpunit.xml.dist app/code/Logiscenter/ImmutableQuote/Test/Unit
```

## Logging

Audit trail entries are persisted to the `logiscenter_immutable_quote_audit` table and also mirrored,
along with module debug/error messages, to `var/log/b2b_immutable_quote.log`.