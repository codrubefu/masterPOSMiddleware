# Copilot Instructions for masterPOSMiddleware

## Architecture Overview

This is a **Laravel 10 middleware application** that bridges POS (Point of Sale) systems with an SQL Server database and fiscal printer integration. It operates as a data transformation layer between cash register terminals (`casa`) and backend storage.

### Core Components

- **BonService** (`app/Services/BonService.php`): Generates fiscal receipts by writing formatted text files to `casa/*/bon.txt` using templates from `app/Services/BonExample/`. Manages bon (receipt) numbering via root-level `bon` file counter.
- **BonDatabaseService** (`app/Services/BonDatabaseService.php`): Persists transaction data to SQL Server tables (`trzcfe`, `trzdetcf`, `trzdetcfaux`). Models have `timestamps = false` and use `dbo.` schema prefix.
- **Casa Configuration**: Multi-terminal setup via `config/casa.php` where each `casa` ID maps to a file path (e.g., `casa/1/bon.txt`). Casa #1 uses `base_path()`, others use `storage_path()`.

### Data Flow

```
POS Terminal → API Request → PaymentsController
    ↓
BonService (writes formatted receipt to casa/{id}/bon.txt)
    ↓
BonDatabaseService (persists to SQL Server: trzcfe, trzdetcf tables)
```

## Database Architecture

**SQL Server (MS SQL)** is the primary database:
- Connection: `sqlsrv` driver with PDO
- Models use explicit table names with `dbo.` schema prefix (e.g., `dbo.trzcfe`)
- All models have `public $timestamps = false`
- Primary keys are explicitly defined (not always auto-increment)

### Key Tables & Models

- **trzcfe** (`TrzCfe`): Transaction headers (bon fiscali) with fields like `idfirma`, `idcl`, `stotalron`, `modp` (payment method)
- **trzdetcf** (`TrzDetCf`): Transaction line items (products) with `nrbonf`, `art`, `cant`, `preturon`
- **company** (`Company`): Single-record company info, fetched via `Company::first()`

Payment methods in `modp`: `ppRON` (mixed), `numRON` (cash), `ccRON` (card).

See `docs/database_schema_trzcfe.md` for complete field definitions.

## Development Environment

### Docker Setup

```bash
# Start containers
docker-compose up -d

# Setup network and import database
sudo docker network create laravel-net-pos
sudo docker network connect laravel-net-pos laravel-app-pos
sudo docker exec -i sql-server-pos /opt/mssql-tools18/bin/sqlcmd -S localhost -U sa -P 'YourStrong!Passw0rd' -C -i /var/opt/mssql/data/spa.sql
```

**Services:**
- `laravel-app-pos`: PHP 8.2-Apache (port 8082)
- `sql-server-pos`: MS SQL Server 2022 (port 1434 external, 1433 internal)

### Key Commands

```bash
# Install dependencies
composer install
npm install

# Run application (via Docker)
docker-compose up

# Development assets
npm run dev

# Build assets
npm run build

# Tests
php artisan test  # or vendor/bin/phpunit
```

## Project Conventions

### API Structure

- **Public routes**: Articles, customers, rapoarte (reports), payments endpoints
- **Protected routes**: Use `api.secret` middleware with `X-API-Secret` header or `api_secret` parameter
- Authentication via Sanctum configured but most endpoints are public

### Service Layer Patterns

1. **Template-based output**: BonService loads templates (`.txt` files with `sprintf` placeholders), applies `stripcslashes()` for escape sequences
2. **File-based state**: Bon counter stored in root `bon` file, incremented atomically
3. **Constructor injection**: Services injected via controller constructors (e.g., `PaymentsController`)

### Model Conventions

- Always set `protected $table = 'dbo.tablename'` with schema prefix
- Always set `public $timestamps = false`
- Always define `protected $primaryKey` explicitly
- Use `protected $fillable` for mass assignment

### Naming Patterns

- Romanian field names (e.g., `stotalron`, `itotalron`, `nrbonfint`)
- `trz*` prefix for transaction tables
- Services end with `Service` suffix
- Controllers end with `Controller` suffix

## Common Development Scenarios

### Adding a New Bon Template

1. Create template in `app/Services/BonExample/` with `sprintf` placeholders
2. Add method in `BonService` that calls `loadTemplate()`, `sprintf()`, `writeToBonFile()`
3. Template codes: `33` (start), `47` (product name), `35` (quantity/price), `48` (header), `49` (line item), `53` (payment type), `56` (end)

### Creating Database Transactions

```php
// Always fetch company first
$this->company = Company::first();

// Use explicit field names, not mass assignment for critical data
$model = new TrzDetCf();
$model->idfirma = $this->company->idfirma ?? null;
$model->casa = $data['casa'] ?? 1;
$model->save();
```

### API Response Pattern

```php
return response()->json([
    'success' => true,
    'message' => 'Operation completed',
    'data' => [/* results */]
], 200);
```

## Critical Paths & Integration Points

- **Receipt generation**: `casa/{id}/bon.txt` must be writable by web server
- **Bon counter**: Root `bon` file must exist and be writable
- **SQL Server connection**: Requires `pdo_sqlsrv` extension (configured in Dockerfile)
- **Casa configuration**: Each terminal needs path configured in `config/casa.php`

## Testing Notes

- `PaymentsController::isPaymentDone()` has random success (20% true) for testing
- Use `Log::info()` extensively for debugging payment flows
- Test data in `casa/1/bon*.txt` files (numbered bons)

## External Dependencies

- **dompdf** (`barryvdh/laravel-dompdf`): PDF generation (likely for invoices)
- **MS SQL Server**: Primary data store
- **Vite**: Frontend asset building
- **Sanctum**: API authentication layer

## Important Notes

- This is a **middleware**, not a full application—focus on data transformation and persistence
- Payment processing integration is async (check `isPaymentDone` endpoint)
- Multi-currency support exists (`itotaleur`, `itotalusd`) but currently unused (NULL)
- Fiscal compliance is critical—bon numbering must never skip or duplicate
