# VTU Platform — Backend Engineering Portfolio

A Laravel-based digital-services platform demonstrating backend engineering concepts including transactional wallet management, payment processing, asynchronous webhook handling, database integrity, authentication, authorization, and automated testing.

## Engineering Focus

- Transaction-safe wallet operations
- Idempotent payment processing
- Queue-based webhook handling
- Database transactions and row-level locking
- Payment lifecycle and state management
- Audit-friendly transaction records
- Role-based administrative access
- Automated feature and integration testing
- Provider-independent payment gateway abstraction

## Architecture

```text
PaymentGatewayInterface
        ↓
DemoPaymentGateway
        ↓
PaymentService
        ↓
PaymentRequest
        ↓
PaymentWebhookController
        ↓
ProcessPaymentWebhook
        ↓
WalletService
        ↓
WalletAudit