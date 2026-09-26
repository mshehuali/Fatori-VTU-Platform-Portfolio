# VTU Platform — Backend Engineering Portfolio

A sanitized Laravel-based digital-services platform demonstrating backend engineering concepts in transactional wallet management, payment processing, webhook-driven workflows, database transactions, authentication and authorization, and automated testing.

This repository is a public portfolio version of a larger application. Production credentials, customer data, payment-provider secrets, and security-sensitive business logic have been excluded or replaced with safe demonstration components.

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