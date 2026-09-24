# B2B Immutable Quote technical test

## Overview

`Logiscenter_ImmutableQuote` adds an auditable, API-only immutability lock on top of Adobe
Commerce B2B's native Negotiable Quote workflow. Once a quote has been negotiated, an authorized
administrator or integration can lock it via REST so its items, quantities, addresses, shipping
method and coupons can no longer be changed by the buyer or any client — while checkout and order
placement keep working normally. Every lock/unlock and every blocked modification attempt is
recorded in an audit trail. See [ARCHITECTURE.md](ARCHITECTURE.md) for the full design rationale
and [API_DOCUMENTATION.md](API_DOCUMENTATION.md) for the REST contract.

## Requirements

| Component | Version |
|---|---|
| PHP | 8.1, 8.2 or 8.3 |
| Adobe Commerce (Magento) | 2.4.8-p4 (Enterprise Edition) |
| Adobe Commerce B2B (`magento/extension-b2b`) | ^1.5 |
