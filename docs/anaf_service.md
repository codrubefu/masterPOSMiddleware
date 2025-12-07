# ANAF VAT Verification Service

## Overview

This service integrates with the Romanian ANAF (Agenția Națională de Administrare Fiscală) API to verify and register taxpayer information including VAT registration status, cash accounting VAT, inactive/reactive status, split VAT payment, and RO e-Factura registration.

## Features

- ✅ Verify VAT registration status (Art. 316 Cod Fiscal)
- ✅ Check TVA la încasare (Cash accounting VAT)
- ✅ Check inactive/reactive taxpayer status
- ✅ Check split VAT payment (plata defalcată a TVA)
- ✅ Check RO e-Factura registration
- ✅ Automatic client registration from ANAF data
- ✅ Batch processing (up to 100 CUI per request)
- ✅ Rate limiting (1 request/second as per ANAF requirements)
- ✅ Auto-refresh stale data

## API Endpoints

### 1. Verify VAT Status

Check VAT registration status for one or multiple CUI numbers.

**Endpoint:** `POST /api/anaf/verify`

**Request Body:**
```json
{
    "cui": "12345678",
    "data": "2024-12-04"
}
```

Or for multiple CUI:
```json
{
    "cui": ["12345678", "87654321"],
    "data": "2024-12-04"
}
```

**Response:**
```json
{
    "success": true,
    "message": "VAT status retrieved successfully",
    "data": {
        "cod": 200,
        "message": "SUCCESS",
        "found": [
            {
                "date_generale": {
                    "cui": "12345678",
                    "data": "2024-12-04",
                    "denumire": "COMPANY NAME SRL",
                    "adresa": "Street Name, Nr. 1",
                    "nrRegCom": "J40/12345/2020",
                    "statusRO_e_Factura": true
                },
                "inregistrare_scop_Tva": {
                    "scpTVA": true
                },
                "inregistrare_RTVAI": {
                    "statusTvaIncasare": false
                },
                "stare_inactiv": {
                    "statusInactivi": false
                },
                "inregistrare_SplitTVA": {
                    "statusSplitTVA": false
                }
            }
        ],
        "notFound": []
    }
}
```

### 2. Register Client from ANAF

Register or update a client using ANAF data.

**Endpoint:** `POST /api/anaf/register-client`

**Request Body:**
```json
{
    "cui": "12345678",
    "data": "2024-12-04"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Client registered/updated successfully",
    "data": {
        "idcl": 1,
        "cui": "12345678",
        "nume": "COMPANY NAME SRL",
        "adresa": "Street Name, Nr. 1",
        "nrRegCom": "J40/12345/2020",
        "scpTVA": true,
        "statusRO_e_Factura": true,
        "statusTvaIncasare": false,
        "statusInactivi": false,
        "statusSplitTVA": false,
        "last_anaf_check": "2024-12-04T10:30:00.000000Z"
    }
}
```

### 3. Batch Register Clients

Register multiple clients at once (max 100 CUI).

**Endpoint:** `POST /api/anaf/batch-register`

**Request Body:**
```json
{
    "cui_list": ["12345678", "87654321", "11223344"],
    "data": "2024-12-04"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Clients registered/updated successfully",
    "data": {
        "registered_count": 3,
        "clients": [
            {
                "idcl": 1,
                "cui": "12345678",
                "nume": "COMPANY NAME SRL"
            }
        ]
    }
}
```

### 4. Refresh Client Data

Update an existing client's data from ANAF.

**Endpoint:** `POST /api/anaf/refresh-client/{id}`

**Request Body:**
```json
{
    "data": "2024-12-04"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Client data refreshed from ANAF",
    "data": {
        "idcl": 1,
        "cui": "12345678",
        "nume": "COMPANY NAME SRL",
        "last_anaf_check": "2024-12-04T10:30:00.000000Z"
    }
}
```

### 5. Get Client with Auto-Refresh

Get client info with optional automatic refresh if data is stale.

**Endpoint:** `GET /api/anaf/client/{id}?refresh=true&days=30`

**Query Parameters:**
- `refresh` (boolean): Enable auto-refresh if data is old
- `days` (integer): Number of days before data is considered stale (default: 30)

**Response:**
```json
{
    "success": true,
    "data": {
        "idcl": 1,
        "cui": "12345678",
        "nume": "COMPANY NAME SRL",
        "scpTVA": true,
        "statusRO_e_Factura": true,
        "last_anaf_check": "2024-12-04T10:30:00.000000Z"
    }
}
```

## Client Model Fields

### ANAF General Data
- `cui` - Fiscal identification code
- `nrRegCom` - Trade register number
- `codPostal` - Postal code
- `act` - Authorization act
- `stare_inregistrare` - Registration status
- `data_inregistrare` - Registration date
- `cod_CAEN` - CAEN code (activity code)
- `iban` - Bank account
- `statusRO_e_Factura` - RO e-Factura registration status
- `organFiscalCompetent` - Competent fiscal authority
- `forma_de_proprietate` - Ownership form
- `forma_organizare` - Organization form
- `forma_juridica` - Legal form

### VAT Registration (Scop TVA)
- `scpTVA` - VAT payer status
- `data_inceput_ScpTVA` - VAT registration start date
- `data_sfarsit_ScpTVA` - VAT registration end date
- `data_anul_imp_ScpTVA` - VAT cancellation operation date
- `mesaj_ScpTVA` - VAT cancellation legal basis

### Cash Accounting VAT (TVA la Încasare)
- `statusTvaIncasare` - Cash accounting VAT status
- `dataInceputTvaInc` - Start date
- `dataSfarsitTvaInc` - End date
- `dataActualizareTvaInc` - Update date
- `dataPublicareTvaInc` - Publication date
- `tipActTvaInc` - Update type

### Inactive/Reactive Status
- `statusInactivi` - Inactive status
- `dataInactivare` - Inactivation date
- `dataReactivare` - Reactivation date
- `dataPublicare` - Publication date
- `dataRadiere` - Deletion date

### Split VAT (Plata Defalcată)
- `statusSplitTVA` - Split VAT payment status
- `dataInceputSplitTVA` - Start date
- `dataAnulareSplitTVA` - Cancellation date

### Social Headquarters Address (prefix: `s`)
- `sdenumire_Strada` - Street name
- `snumar_Strada` - Street number
- `sdenumire_Localitate` - Locality name
- `scod_Localitate` - Locality code
- `sdenumire_Judet` - County name
- `scod_Judet` - County code
- `scod_JudetAuto` - County auto code
- `stara` - Country
- `sdetalii_Adresa` - Address details
- `scod_Postal` - Postal code

### Fiscal Domicile Address (prefix: `d`)
- `ddenumire_Strada` - Street name
- `dnumar_Strada` - Street number
- `ddenumire_Localitate` - Locality name
- `dcod_Localitate` - Locality code
- `ddenumire_Judet` - County name
- `dcod_Judet` - County code
- `dcod_JudetAuto` - County auto code
- `dtara` - Country
- `ddetalii_Adresa` - Address details
- `dcod_Postal` - Postal code

### Tracking
- `last_anaf_check` - Last ANAF verification timestamp

## Usage Examples

### PHP Service Usage

```php
use App\Services\AnafService;

$anafService = app(AnafService::class);

// Verify single CUI
$result = $anafService->verifyVatStatus('12345678');

// Register client from ANAF
$client = $anafService->registerClientFromAnaf('12345678');

// Batch register
$clients = $anafService->batchRegisterClients(['12345678', '87654321']);

// Check if refresh needed
if ($anafService->needsRefresh($client, 30)) {
    $client = $anafService->refreshIfNeeded($client);
}
```

### cURL Examples

**Verify VAT Status:**
```bash
curl -X POST https://your-domain.com/api/anaf/verify \
  -H "Content-Type: application/json" \
  -d '{
    "cui": "12345678",
    "data": "2024-12-04"
  }'
```

**Register Client:**
```bash
curl -X POST https://your-domain.com/api/anaf/register-client \
  -H "Content-Type: application/json" \
  -d '{
    "cui": "12345678",
    "data": "2024-12-04"
  }'
```

**Batch Register:**
```bash
curl -X POST https://your-domain.com/api/anaf/batch-register \
  -H "Content-Type: application/json" \
  -d '{
    "cui_list": ["12345678", "87654321"],
    "data": "2024-12-04"
  }'
```

## ANAF API Limitations

1. **Maximum 100 CUI per request** - The service automatically chunks larger batches
2. **Rate limit: 1 request per second** - Automatically enforced by the service
3. **Excessive usage penalties** - The service respects ANAF's rate limits to avoid penalties

## Database Migration

Run the migration to add ANAF fields to the client table:

```bash
php artisan migrate
```

This will add all necessary ANAF-related fields to the `client` table.

## Error Handling

The service handles various error scenarios:

- **404**: Client not found in ANAF registry
- **422**: Validation errors (invalid CUI format, missing required fields)
- **500**: ANAF API errors or connection issues

All errors are logged for debugging purposes.

## Logging

The service logs all important operations:
- ANAF API requests and responses
- Client registration/updates
- Rate limiting events
- Errors and exceptions

Check Laravel logs at `storage/logs/laravel.log`.

## Testing

Test the integration:

```php
// In tinker or a test
$anafService = app(\App\Services\AnafService::class);
$result = $anafService->verifyVatStatus('14399840'); // Valid CUI for testing
```

## Notes

- The `data` parameter is optional and defaults to today's date
- CUI numbers are automatically sanitized (RO prefix removed, special chars stripped)
- Boolean status fields are stored as 0/1 in the database
- Date fields automatically handle NULL values from ANAF
- The service respects ANAF's rate limiting to avoid penalties
