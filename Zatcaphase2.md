# ZatcaPHP — ZATCA Phase 2 E-Invoicing Integration

**ZatcaPHP** is a standalone PHP library for **Saudi ZATCA Phase 2 e-invoicing** (device onboarding, certificate management, invoice signing, QR generation, and API reporting/clearance). It is located at `Zatca_invoice/ZatcaPHP/`.

---

## Directory Structure

```
ZatcaPHP/
├── CsrAndOnboarding.php              # Entry: device onboarding (4 steps)
├── ReportingAndClearance.php         # Entry: post-onboarding invoice submission
├── Helpers/
│   ├── ApiHelper.php                 # cURL client for all ZATCA APIs
│   ├── CsrGenerator.php              # EC secp256k1 key pair + CSR generation
│   └── InvoiceHelper.php             # XML manipulation (modify, extract hash/QR)
├── Signer/
│   ├── EInvoiceSigner.php            # Invoice signing orchestrator
│   └── QRCodeGenerator.php           # ZATCA-compliant TLV QR code generator
├── Resources/
│   ├── Invoice.xml                   # Base UBL 2.1 invoice template
│   ├── ZatcaDataUbl.xml              # UBL extension signature template
│   ├── ZatcaDataSignature.xml        # QR + signature fragment
│   └── xslfile.xsl                   # XSLT to strip signatures during canonicalization
├── certificate/
│   └── certificateInfo.json          # Persisted certificate + state (ICV, PIH)
└── README.md
```

---

## Main Classes

| Class | File | Key Methods | Purpose |
|---|---|---|---|
| `CsrGenerator` | `Helpers/CsrGenerator.php` | `generateCsr()`, `generatePrivateKey()` | EC secp256k1 key pair + CSR |
| `InvoiceHelper` | `Helpers/InvoiceHelper.php` | `ModifyXml()`, `ExtractInvoiceHashAndBase64QrCode()`, `updateCompanyIDInXmlTemplate()` | XML manipulation |
| `ApiHelper` | `Helpers/ApiHelper.php` | `complianceCSID()`, `complianceChecks()`, `productionCSID()`, `invoiceReporting()`, `invoiceClearance()`, `renewalCSID()` | cURL HTTP client with retry logic |
| `EInvoiceSigner` | `Signer/EInvoiceSigner.php` | `GetRequestApi()`, `SignSimplifiedInvoice()` | XSLT → C14N → SHA-256 → ECDSA sign → pack XML |
| `QRCodeGenerator` | `Signer/QRCodeGenerator.php` | `generateQRCode()` | TLV tags 1-9 → Base64 QR |

---

## ZATCA API Endpoints

| API | Method | Endpoint | Used In |
|---|---|---|---|
| Compliance CSID | POST | `/e-invoicing/{env}/compliance` | Onboarding Step 2 |
| Compliance Checks | POST | `/e-invoicing/{env}/compliance/invoices` | Onboarding Step 3 |
| Production CSID | POST | `/e-invoicing/{env}/production/csids` | Onboarding Step 4 |
| Invoice Reporting | POST | `/e-invoicing/{env}/invoices/reporting/single` | Post-onboarding (simplified) |
| Invoice Clearance | POST | `/e-invoicing/{env}/invoices/clearance/single` | Post-onboarding (standard) |

Where `{env}` is one of: `developer-portal`, `simulation`, `production`.

---

## Workflow

### Onboarding (`CsrAndOnboarding.php`)

```mermaid
flowchart TB
    subgraph Onboarding["ONBOARDING (CsrAndOnboarding.php)"]
        STEP1["1. Generate CSR + EC Private Key<br/>(CsrGenerator)"]
        STEP2["2. Get Compliance CSID<br/>(ApiHelper::complianceCSID)"]
        STEP3["3. Submit 6 Sample Documents<br/>Standard: Invoice, Credit Note, Debit Note<br/>Simplified: Invoice, Credit Note, Debit Note<br/>(InvoiceHelper + EInvoiceSigner + ApiHelper)"]
        STEP4["4. Get Production CSID<br/>(ApiHelper::productionCSID)"]
        STEP1 --> STEP2 --> STEP3 --> STEP4
    end

    subgraph PostOnboarding["POST-ONBOARDING (ReportingAndClearance.php)"]
        LOAD["Load certInfo from JSON"]
        LOOP["For each invoice type (6):"]
        LOAD --> LOOP
        LOOP --> MODIFY["InvoiceHelper::ModifyXml<br/>(set ID, UUID, ICV, PIH, type)"]
        MODIFY --> SIGN["EInvoiceSigner::GetRequestApi<br/>XSLT → C14N → SHA-256 →<br/>ECDSA Sign → Pack XML → QR"]
        SIGN --> SUBMIT["ApiHelper::invoiceReporting()<br/>or invoiceClearance()"]
        SUBMIT --> UPDATE["Update ICV + PIH in JSON"]
        UPDATE --> LOOP
    end

    STEP4 -->|Persists to| JSON["certificate/certificateInfo.json"]
    JSON --> LOAD
```

### Invoice Signing Pipeline (`EInvoiceSigner`)

```mermaid
sequenceDiagram
    participant Caller
    participant EInvoiceSigner
    participant XSLT
    participant QRCodeGenerator
    participant ApiHelper

    Caller->>EInvoiceSigner: GetRequestApi(xml, cert, privateKey)
    EInvoiceSigner->>EInvoiceSigner: Extract UUID from XML
    EInvoiceSigner->>XSLT: Apply xslfile.xsl (strip signatures)
    EInvoiceSigner->>EInvoiceSigner: C14N canonicalization
    EInvoiceSigner->>EInvoiceSigner: SHA-256 hash → base64
    EInvoiceSigner->>EInvoiceSigner: Base64 encode entire XML

    alt Standard Invoice
        EInvoiceSigner-->>Caller: Return { invoiceHash, uuid, invoice }
    else Simplified Invoice
        EInvoiceSigner->>EInvoiceSigner: Generate signature timestamp
        EInvoiceSigner->>EInvoiceSigner: Parse X.509 cert (issuer, serial)
        EInvoiceSigner->>EInvoiceSigner: Compute signedPropertiesHash
        EInvoiceSigner->>EInvoiceSigner: ECDSA sign (openssl_sign)
        EInvoiceSigner->>QRCodeGenerator: generateQRCode(xml, hash, sig, cert)
        QRCodeGenerator-->>EInvoiceSigner: Base64 TLV QR
        EInvoiceSigner->>EInvoiceSigner: Populate UBL extension template
        EInvoiceSigner->>EInvoiceSigner: Insert UBL extension + QR into XML
        EInvoiceSigner-->>Caller: Return { invoiceHash, uuid, invoice }
    end
```

### QR Code TLV Structure

| Tag | Value | Example |
|---|---|---|
| 1 | Seller Name | `شركة عبد الغني حسين حامد للمصاعد` |
| 2 | VAT Number | `399999999900003` |
| 3 | Timestamp (ISO 8601) | `2025-06-30T14:30:00Z` |
| 4 | Invoice Total | `115.00` |
| 5 | VAT Total | `15.00` |
| 6 | Invoice Hash (base64) | ... |
| 7 | ECDSA Signature (base64) | ... |
| 8 | ECDSA Public Key (base64) | ... |
| 9 | Certificate Signature (base64) | ... (simplified invoices only) |

---

## Technical Details

| Aspect | Details |
|---|---|
| **Dependencies** | No Composer — pure PHP with `require_once` |
| **PHP Extensions** | `openssl`, `curl`, `dom`, `xsl`, `simplexml`, `bcmath`, `mbstring` |
| **Authentication** | HTTP Basic Auth (`base64(binarySecurityToken:secret)`) |
| **API Version** | `Accept-Version: V2` |
| **Hash Algorithm** | SHA-256 |
| **Signature Algorithm** | ECDSA with secp256k1 curve |
| **Invoice Standard** | UBL 2.1 |
| **ICV/PIH Chaining** | Invoice Counter Value + Previous Invoice Hash for audit trail |
| **Origin** | [mabaega/ZatcaPHP](https://github.com/mabaega/ZatcaPHP) |

---

## Security Notes

- Private keys are stored in **plaintext** in `certificate/certificateInfo.json` — should be encrypted or moved to a secure vault in production
- Test data is hardcoded: company `TST-886431145-399999999900003`, OTP `123456`
- Environment is set via `$environmentType` variable (`NonProduction` / `Simulation` / `Production`)
