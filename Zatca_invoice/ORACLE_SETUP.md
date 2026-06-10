# Oracle 10g Connection — Laravel 10 / PHP 8.1

## Environment
- PHP 8.1.10 (Laragon)
- Laravel 10
- Oracle 10g server at `10.0.0.8:1521` SID `nfe`
- Oracle Instant Client **11.1.0.7.0** (Desktop)

---

## Problem Summary
`oci8 3.x` requires Instant Client **11.2+**, so native `oci8` fails with `ORA-24315`.  
Solution: use PHP's built-in `pdo_oci` extension instead of `yajra/laravel-pdo-via-oci8`.

---

## Steps Done

### 1. PHP Extensions (`php.ini`)
```ini
extension=pdo_oci
; extension=php_oci8_11g.dll  ← only if IC 11.2+ installed
```

### 2. Oracle Instant Client
- Folder: `C:\oracle\instantclient_11_1\`
- Add to **System PATH** (run as Administrator):
```powershell
[Environment]::SetEnvironmentVariable("Path", $env:Path + ";C:\oracle\instantclient_11_1", [EnvironmentVariableTarget]::Machine)
```

### 3. `.env`
```env
DB_CONNECTION=oracle

ORA_HOST=10.0.0.8
ORA_PORT=1521
ORA_DATABASE=nfe
ORA_USERNAME=lce
ORA_PASSWORD=lce
ORA_CHARSET=AL32UTF8
```

### 4. `config/database.php`
```php
'oracle' => [
    'driver'   => 'oracle',
    'host'     => env('ORA_HOST'),
    'port'     => env('ORA_PORT', '1521'),
    'database' => env('ORA_DATABASE'),
    'username' => env('ORA_USERNAME'),
    'password' => env('ORA_PASSWORD'),
    'charset'  => env('ORA_CHARSET', 'AL32UTF8'),
    'prefix'   => '',
],
```

### 5. Custom Driver (`app/Database/`)
| File | Purpose |
|---|---|
| `OracleConnector.php` | Builds `pdo_oci` DSN, sets `NLS_LANG` |
| `OracleConnection.php` | Laravel DB connection wrapper |
| `OracleQueryGrammar.php` | Oracle SQL grammar (ROWNUM / FETCH) |
| `OracleSchemaGrammar.php` | Schema grammar stub |
| `OracleProcessor.php` | Result processor stub |

### 6. `AppServiceProvider.php`
```php
Connection::resolverFor('oracle', function ($connection, $database, $prefix, $config) {
    $pdo = (new OracleConnector())->connect($config);
    return new OracleConnection($pdo, $database, $prefix, $config);
});
```

### 7. Composer Packages
```bash
composer require "yajra/laravel-oci8:^10.0" --ignore-platform-req=ext-oci8
composer require "doctrine/dbal:^3.6" --ignore-platform-req=ext-oci8
```

---

## Start Dev Server
```bash
set NLS_LANG=ARABIC_SAUDI ARABIA.AL32UTF8
set PATH=%PATH%;C:\oracle\instantclient_11_1
php artisan serve
```

---

## Browse Oracle Tables
```
http://127.0.0.1:8000/oracle/tables
http://127.0.0.1:8000/oracle/tables/{TABLE_NAME}
```

---

## Usage in Code
```php
// Raw query
DB::connection('oracle')->select('SELECT * FROM VAT_INVOICE WHERE ROWNUM <= 10');

// Model
class VatInvoice extends Model {
    protected $connection = 'oracle';
    protected $table      = 'VAT_INVOICE';
    protected $primaryKey = 'SERIAL';
    public    $timestamps = false;
}
```

---

## Upgrade Path
To enable full `oci8` support, replace IC 11.1 with **IC 11.2+**:
- Download: https://www.oracle.com/database/technologies/instant-client/winx64-64-downloads.html
- Then enable in `php.ini`: `extension=php_oci8_11g.dll`
