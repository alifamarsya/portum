# Panduan Git Timeline Portum — Versi Lengkap + Kode Siap Copas
Dokumen ini menggantikan `Panduan_Git_Timeline_Portum_..._Super_Rinci.docx` dan catatan koreksi sebelumnya (`CATATAN-ISI-KODE-PER-STEP.md`). Semua kode di bawah diambil apa adanya dari `portum-app.zip` referensi kalian, sudah disusun ulang urutannya supaya setiap step benar-benar bisa `php artisan migrate` / `route:list` / `test` tanpa manggil class yang belum ada di step itu.

Pola kerja, aturan anti-conflict, ritual setelah merge, format logbook, dan Definition of Done **tetap ikuti dokumen asli** — yang berubah di sini cuma **urutan & isi file per step**. Kalian sudah sampai Step 9 versi lama; karena urutan filenya berubah, disarankan reset branch `develop` dan mulai ulang dari Step 5 memakai urutan baru ini (Step 1-4 tidak berubah).
## Ringkasan Perubahan Urutan dari Versi Lama

| Step (baru) | Yang berubah dari versi lama |
|---|---|
| 5 — Foundation | `config/modules.php` **dikeluarkan**; `routes/web.php` dibuat minimal (isi bertahap di step 8/9/12); `bootstrap/app.php` tanpa alias middleware dulu |
| 6 — Database Foundation | Migration `data_warehouse` dan `notifications` **dikeluarkan** (pindah ke step 14 & 13) |
| 7 — Baseline QA | Hanya `ExampleTest` (Feature+Unit) — test lain butuh fitur yang belum ada |
| 8 — Auth/RBAC | Ditambah: layout dasar (`layouts/app.blade.php` dkk), seeder awal, EDIT `bootstrap/app.php` + `routes/web.php` |
| 9 — Domain Models | Ditambah: `ModuleController.php` (mesin CRUD generik — sebelumnya tidak pernah disebutkan di step manapun!), `config/modules.php`, `AppServiceProvider.php`, EDIT `routes/web.php`. Model Data Warehouse **dikeluarkan** (pindah ke step 14) |
| 11 — Audit Chain | Tidak berubah, tapi TIDAK lagi disebut ulang di step 12 |
| 12 — Dashboard/Admin | Ditambah: `Panduan.php`, `RisalahRapat.php`, `PanduanController.php`, `RisalahRapatController.php` (sebelumnya tidak pernah disebutkan!), EDIT `routes/web.php`. `AuditLogController.php` **dikeluarkan** (sudah dibuat di step 11) |
| 13 — Distributed | `routes/console.php` **dikeluarkan** (pindah ke step 14); ditambah migration `notifications_table` |
| 14 — Data Warehouse/ETL | Ditambah migration + 7 model DW (dipindah dari step 9); `routes/console.php` (utuh, sekarang semua command yang dirujuk sudah ada) |
---

# STEP 1 — Marsya: Repository
Tidak ada kode aplikasi. Ikuti dokumen asli: buat repo `portum-app`, tambahkan Dirli & Zahra sebagai collaborator, buat Project board (Backlog → In Progress → Review → Testing → Done), buat Milestone `Foundation`.

# STEP 2 — Marsya: Laravel → main

```bash
cd C:\laragon\www
composer create-project laravel/laravel portum-app
cd C:\laragon\www\portum-app
php -v
composer -V
php artisan --version
```
Isi `.env` lokal (jangan commit), lalu:

```bash
git init
git add .
git commit -m "chore: initialize Laravel project"
git branch -M main
git remote add origin <URL-REPOSITORY>
git push -u origin main
```
**Hanya Marsya push main di tahap ini.**

# STEP 3 — Marsya: main → develop

```bash
git checkout -b develop
git push -u origin develop
git branch -a
```

# STEP 4 — Dirli & Zahra: Clone
Dirli clone setelah develop di-push Marsya. Zahra boleh menyusul kapan saja setelahnya. Keduanya tidak push di step ini.

```bash
cd C:\laragon\www
git clone <URL-REPOSITORY>
cd portum-app
git checkout develop
git pull origin develop
php artisan --version
```

---

# STEP 5 — Marsya: Foundation

```bash
git checkout develop
git pull origin develop
git checkout -b feature/foundation
```
Buat file-file berikut (copy utuh dari ZIP referensi, kecuali `routes/web.php` dan `bootstrap/app.php` yang sengaja disederhanakan dulu):

`app/Http/Controllers/Controller.php`
```php
<?php

namespace App\Http\Controllers;

abstract class Controller
{
    //
}
```

`bootstrap/app.php` — versi awal, TANPA alias middleware dulu — diedit lagi di Step 8
```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Alias middleware modul & superadmin ditambahkan di STEP 8
        // (setelah CheckModulePermission & EnsureSuperadmin dibuat).
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

```

`bootstrap/providers.php` — versi awal, kosong — diedit lagi di Step 9
```php
<?php

return [
    // App\Providers\AppServiceProvider ditambahkan di STEP 9
];

```

`routes/web.php` — versi awal, minimal — diedit lagi di Step 8, 9, 12
```php
<?php

use Illuminate\Support\Facades\Route;

// Route ditambahkan bertahap: auth di Step 8, mesin modul di Step 9,
// dashboard/admin di Step 12.

```

`config/app.php`
```php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application, which will be used when the
    | framework needs to place the application's name in a notification or
    | other UI elements where an application name needs to be displayed.
    |
    */

    'name' => env('APP_NAME', 'Laravel'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | the application so that it's available within Artisan commands.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. The timezone
    | is set to "UTC" by default as it is suitable for most use cases.
    |
    */

    'timezone' => 'UTC',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by Laravel's translation / localization methods. This option can be
    | set to any locale for which you plan to have translation strings.
    |
    */

    'locale' => env('APP_LOCALE', 'en'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is utilized by Laravel's encryption services and should be set
    | to a random, 32 character string to ensure that all encrypted values
    | are secure. You should do this prior to deploying the application.
    |
    */

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', (string) env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode Driver
    |--------------------------------------------------------------------------
    |
    | These configuration options determine the driver used to determine and
    | manage Laravel's "maintenance mode" status. The "cache" driver will
    | allow maintenance mode to be controlled across multiple machines.
    |
    | Supported drivers: "file", "cache"
    |
    */

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

];
```

`config/auth.php`
```php
<?php

use App\Models\User;

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option defines the default authentication "guard" and password
    | reset "broker" for your application. You may change these values
    | as required, but they're a perfect start for most applications.
    |
    */

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | which utilizes session storage plus the Eloquent user provider.
    |
    | All authentication guards have a user provider, which defines how the
    | users are actually retrieved out of your database or other storage
    | system used by the application. Typically, Eloquent is utilized.
    |
    | Supported: "session"
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | All authentication guards have a user provider, which defines how the
    | users are actually retrieved out of your database or other storage
    | system used by the application. Typically, Eloquent is utilized.
    |
    | If you have multiple user tables or models you may configure multiple
    | providers to represent the model / table. These providers may then
    | be assigned to any extra authentication guards you have defined.
    |
    | Supported: "database", "eloquent"
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', User::class),
        ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | These configuration options specify the behavior of Laravel's password
    | reset functionality, including the table utilized for token storage
    | and the user provider that is invoked to actually retrieve users.
    |
    | The expiry time is the number of minutes that each reset token will be
    | considered valid. This security feature keeps tokens short-lived so
    | they have less time to be guessed. You may change this as needed.
    |
    | The throttle setting is the number of seconds a user must wait before
    | generating more password reset tokens. This prevents the user from
    | quickly generating a very large amount of password reset tokens.
    |
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    | Here you may define the number of seconds before a password confirmation
    | window expires and users are asked to re-enter their password via the
    | confirmation screen. By default, the timeout lasts for three hours.
    |
    */

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
```

`config/database.php`
```php
<?php

use Illuminate\Support\Str;
use Pdo\Mysql;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for database operations. This is
    | the connection which will be utilized unless another connection
    | is explicitly specified when you execute a query / statement.
    |
    */

    'default' => env('DB_CONNECTION', 'sqlite'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Below are all of the database connections defined for your application.
    | An example configuration is provided for each database system which
    | is supported by Laravel. You're free to add / remove connections.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DB_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
            'busy_timeout' => null,
            'journal_mode' => null,
            'synchronous' => null,
            'transaction_mode' => 'DEFERRED',
        ],

        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                Mysql::ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'mariadb' => [
            'driver' => 'mariadb',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                Mysql::ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => env('DB_SSLMODE', 'prefer'),
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            // 'encrypt' => env('DB_ENCRYPT', 'yes'),
            // 'trust_server_certificate' => env('DB_TRUST_SERVER_CERTIFICATE', 'false'),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run on the database.
    |
    */

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as Memcached. You may define your connection settings here.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug((string) env('APP_NAME', 'laravel')).'-database-'),
            'persistent' => env('REDIS_PERSISTENT', false),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
            'max_retries' => env('REDIS_MAX_RETRIES', 3),
            'backoff_algorithm' => env('REDIS_BACKOFF_ALGORITHM', 'decorrelated_jitter'),
            'backoff_base' => env('REDIS_BACKOFF_BASE', 100),
            'backoff_cap' => env('REDIS_BACKOFF_CAP', 1000),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
            'max_retries' => env('REDIS_MAX_RETRIES', 3),
            'backoff_algorithm' => env('REDIS_BACKOFF_ALGORITHM', 'decorrelated_jitter'),
            'backoff_base' => env('REDIS_BACKOFF_BASE', 100),
            'backoff_cap' => env('REDIS_BACKOFF_CAP', 1000),
        ],

    ],

];
```

`config/cache.php`
```php
<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Cache Store
    |--------------------------------------------------------------------------
    |
    | This option controls the default cache store that will be used by the
    | framework. This connection is utilized if another isn't explicitly
    | specified when running a cache operation inside the application.
    |
    */

    'default' => env('CACHE_STORE', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Cache Stores
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the cache "stores" for your application as
    | well as their drivers. You may even define multiple stores for the
    | same cache driver to group types of items stored in your caches.
    |
    | Supported drivers: "array", "database", "file", "memcached",
    |                    "redis", "dynamodb", "storage", "octane",
    |                    "session", "failover", "null"
    |
    */

    'stores' => [

        'array' => [
            'driver' => 'array',
            'serialize' => false,
        ],

        'database' => [
            'driver' => 'database',
            'connection' => env('DB_CACHE_CONNECTION'),
            'table' => env('DB_CACHE_TABLE', 'cache'),
            'lock_connection' => env('DB_CACHE_LOCK_CONNECTION'),
            'lock_table' => env('DB_CACHE_LOCK_TABLE'),
        ],

        'file' => [
            'driver' => 'file',
            'path' => storage_path('framework/cache/data'),
            'lock_path' => storage_path('framework/cache/data'),
        ],

        'storage' => [
            'driver' => 'storage',
            'disk' => env('CACHE_STORAGE_DISK'),
            'path' => env('CACHE_STORAGE_PATH', 'framework/cache/data'),
        ],

        'memcached' => [
            'driver' => 'memcached',
            'persistent_id' => env('MEMCACHED_PERSISTENT_ID'),
            'sasl' => [
                env('MEMCACHED_USERNAME'),
                env('MEMCACHED_PASSWORD'),
            ],
            'options' => [
                // Memcached::OPT_CONNECT_TIMEOUT => 2000,
            ],
            'servers' => [
                [
                    'host' => env('MEMCACHED_HOST', '127.0.0.1'),
                    'port' => env('MEMCACHED_PORT', 11211),
                    'weight' => 100,
                ],
            ],
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => env('REDIS_CACHE_CONNECTION', 'cache'),
            'lock_connection' => env('REDIS_CACHE_LOCK_CONNECTION', 'default'),
        ],

        'dynamodb' => [
            'driver' => 'dynamodb',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'table' => env('DYNAMODB_CACHE_TABLE', 'cache'),
            'endpoint' => env('DYNAMODB_ENDPOINT'),
        ],

        'octane' => [
            'driver' => 'octane',
        ],

        'failover' => [
            'driver' => 'failover',
            'stores' => [
                'database',
                'array',
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Key Prefix
    |--------------------------------------------------------------------------
    |
    | When utilizing the APC, database, memcached, Redis, and DynamoDB cache
    | stores, there might be other applications using the same cache. For
    | that reason, you may prefix every cache key to avoid collisions.
    |
    */

    'prefix' => env('CACHE_PREFIX', Str::slug((string) env('APP_NAME', 'laravel')).'-cache-'),

    /*
    |--------------------------------------------------------------------------
    | Serializable Classes
    |--------------------------------------------------------------------------
    |
    | This value determines the classes that can be unserialized from cache
    | storage. By default, no PHP classes will be unserialized from your
    | cache to prevent gadget chain attacks if your APP_KEY is leaked.
    |
    */

    'serializable_classes' => false,

];
```

`config/session.php`
```php
<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Session Driver
    |--------------------------------------------------------------------------
    |
    | This option determines the default session driver that is utilized for
    | incoming requests. Laravel supports a variety of storage options to
    | persist session data. Database storage is a great default choice.
    |
    | Supported: "file", "cookie", "database", "memcached",
    |            "redis", "dynamodb", "array"
    |
    */

    'driver' => env('SESSION_DRIVER', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Session Lifetime
    |--------------------------------------------------------------------------
    |
    | Here you may specify the number of minutes that you wish the session
    | to be allowed to remain idle before it expires. If you want them
    | to expire immediately when the browser is closed then you may
    | indicate that via the expire_on_close configuration option.
    |
    */

    'lifetime' => (int) env('SESSION_LIFETIME', 120),

    'expire_on_close' => env('SESSION_EXPIRE_ON_CLOSE', false),

    /*
    |--------------------------------------------------------------------------
    | Session Encryption
    |--------------------------------------------------------------------------
    |
    | This option allows you to easily specify that all of your session data
    | should be encrypted before it's stored. All encryption is performed
    | automatically by Laravel and you may use the session like normal.
    |
    */

    'encrypt' => env('SESSION_ENCRYPT', false),

    /*
    |--------------------------------------------------------------------------
    | Session File Location
    |--------------------------------------------------------------------------
    |
    | When utilizing the "file" session driver, the session files are placed
    | on disk. The default storage location is defined here; however, you
    | are free to provide another location where they should be stored.
    |
    */

    'files' => storage_path('framework/sessions'),

    /*
    |--------------------------------------------------------------------------
    | Session Database Connection
    |--------------------------------------------------------------------------
    |
    | When using the "database" or "redis" session drivers, you may specify a
    | connection that should be used to manage these sessions. This should
    | correspond to a connection in your database configuration options.
    |
    */

    'connection' => env('SESSION_CONNECTION'),

    /*
    |--------------------------------------------------------------------------
    | Session Database Table
    |--------------------------------------------------------------------------
    |
    | When using the "database" session driver, you may specify the table to
    | be used to store sessions. Of course, a sensible default is defined
    | for you; however, you're welcome to change this to another table.
    |
    */

    'table' => env('SESSION_TABLE', 'sessions'),

    /*
    |--------------------------------------------------------------------------
    | Session Cache Store
    |--------------------------------------------------------------------------
    |
    | When using one of the framework's cache driven session backends, you may
    | define the cache store which should be used to store the session data
    | between requests. This must match one of your defined cache stores.
    |
    | Affects: "dynamodb", "memcached", "redis"
    |
    */

    'store' => env('SESSION_STORE'),

    /*
    |--------------------------------------------------------------------------
    | Session Sweeping Lottery
    |--------------------------------------------------------------------------
    |
    | Some session drivers must manually sweep their storage location to get
    | rid of old sessions from storage. Here are the chances that it will
    | happen on a given request. By default, the odds are 2 out of 100.
    |
    */

    'lottery' => [2, 100],

    /*
    |--------------------------------------------------------------------------
    | Session Cookie Name
    |--------------------------------------------------------------------------
    |
    | Here you may change the name of the session cookie that is created by
    | the framework. Typically, you should not need to change this value
    | since doing so does not grant a meaningful security improvement.
    |
    */

    'cookie' => env(
        'SESSION_COOKIE',
        Str::slug((string) env('APP_NAME', 'laravel')).'-session'
    ),

    /*
    |--------------------------------------------------------------------------
    | Session Cookie Path
    |--------------------------------------------------------------------------
    |
    | The session cookie path determines the path for which the cookie will
    | be regarded as available. Typically, this will be the root path of
    | your application, but you're free to change this when necessary.
    |
    */

    'path' => env('SESSION_PATH', '/'),

    /*
    |--------------------------------------------------------------------------
    | Session Cookie Domain
    |--------------------------------------------------------------------------
    |
    | This value determines the domain and subdomains the session cookie is
    | available to. By default, the cookie will be available to the root
    | domain without subdomains. Typically, this shouldn't be changed.
    |
    */

    'domain' => env('SESSION_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | HTTPS Only Cookies
    |--------------------------------------------------------------------------
    |
    | By setting this option to true, session cookies will only be sent back
    | to the server if the browser has a HTTPS connection. This will keep
    | the cookie from being sent to you when it can't be done securely.
    |
    */

    'secure' => env('SESSION_SECURE_COOKIE'),

    /*
    |--------------------------------------------------------------------------
    | HTTP Access Only
    |--------------------------------------------------------------------------
    |
    | Setting this value to true will prevent JavaScript from accessing the
    | value of the cookie and the cookie will only be accessible through
    | the HTTP protocol. It's unlikely you should disable this option.
    |
    */

    'http_only' => env('SESSION_HTTP_ONLY', true),

    /*
    |--------------------------------------------------------------------------
    | Same-Site Cookies
    |--------------------------------------------------------------------------
    |
    | This option determines how your cookies behave when cross-site requests
    | take place, and can be used to mitigate CSRF attacks. By default, we
    | will set this value to "lax" to permit secure cross-site requests.
    |
    | See: https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Set-Cookie#samesitesamesite-value
    |
    | Supported: "lax", "strict", "none", null
    |
    */

    'same_site' => env('SESSION_SAME_SITE', 'lax'),

    /*
    |--------------------------------------------------------------------------
    | Partitioned Cookies
    |--------------------------------------------------------------------------
    |
    | Setting this value to true will tie the cookie to the top-level site for
    | a cross-site context. Partitioned cookies are accepted by the browser
    | when flagged "secure" and the Same-Site attribute is set to "none".
    |
    */

    'partitioned' => env('SESSION_PARTITIONED_COOKIE', false),

    /*
    |--------------------------------------------------------------------------
    | Session Serialization
    |--------------------------------------------------------------------------
    |
    | This value controls the serialization strategy for session data, which
    | is JSON by default. Setting this to "php" allows the storage of PHP
    | objects in the session but can make an application vulnerable to
    | "gadget chain" serialization attacks if the APP_KEY is leaked.
    |
    | Supported: "json", "php"
    |
    */

    'serialization' => 'json',

];
```

`config/queue.php`
```php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Queue Connection Name
    |--------------------------------------------------------------------------
    |
    | Laravel's queue supports a variety of backends via a single, unified
    | API, giving you convenient access to each backend using identical
    | syntax for each. The default queue connection is defined below.
    |
    */

    'default' => env('QUEUE_CONNECTION', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Queue Connections
    |--------------------------------------------------------------------------
    |
    | Here you may configure the connection options for every queue backend
    | used by your application. An example configuration is provided for
    | each backend supported by Laravel. You're also free to add more.
    |
    | Drivers: "sync", "database", "beanstalkd", "sqs", "redis",
    |          "deferred", "background", "failover", "null"
    |
    */

    'connections' => [

        'sync' => [
            'driver' => 'sync',
        ],

        'database' => [
            'driver' => 'database',
            'connection' => env('DB_QUEUE_CONNECTION'),
            'table' => env('DB_QUEUE_TABLE', 'jobs'),
            'queue' => env('DB_QUEUE', 'default'),
            'retry_after' => (int) env('DB_QUEUE_RETRY_AFTER', 90),
            'after_commit' => false,
        ],

        'beanstalkd' => [
            'driver' => 'beanstalkd',
            'host' => env('BEANSTALKD_QUEUE_HOST', 'localhost'),
            'queue' => env('BEANSTALKD_QUEUE', 'default'),
            'retry_after' => (int) env('BEANSTALKD_QUEUE_RETRY_AFTER', 90),
            'block_for' => 0,
            'after_commit' => false,
        ],

        'sqs' => [
            'driver' => 'sqs',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'prefix' => env('SQS_PREFIX', 'https://sqs.us-east-1.amazonaws.com/your-account-id'),
            'queue' => env('SQS_QUEUE', 'default'),
            'suffix' => env('SQS_SUFFIX'),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'after_commit' => false,
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => env('REDIS_QUEUE_CONNECTION', 'default'),
            'queue' => env('REDIS_QUEUE', 'default'),
            'retry_after' => (int) env('REDIS_QUEUE_RETRY_AFTER', 90),
            'block_for' => null,
            'after_commit' => false,
        ],

        'deferred' => [
            'driver' => 'deferred',
        ],

        'background' => [
            'driver' => 'background',
        ],

        'failover' => [
            'driver' => 'failover',
            'connections' => [
                'database',
                'deferred',
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Job Batching
    |--------------------------------------------------------------------------
    |
    | The following options configure the database and table that store job
    | batching information. These options can be updated to any database
    | connection and table which has been defined by your application.
    |
    */

    'batching' => [
        'database' => env('DB_CONNECTION', 'sqlite'),
        'table' => 'job_batches',
    ],

    /*
    |--------------------------------------------------------------------------
    | Failed Queue Jobs
    |--------------------------------------------------------------------------
    |
    | These options configure the behavior of failed queue job logging so you
    | can control how and where failed jobs are stored. Laravel ships with
    | support for storing failed jobs in a simple file or in a database.
    |
    | Supported drivers: "database-uuids", "dynamodb", "file", "null"
    |
    */

    'failed' => [
        'driver' => env('QUEUE_FAILED_DRIVER', 'database-uuids'),
        'database' => env('DB_CONNECTION', 'sqlite'),
        'table' => 'failed_jobs',
    ],

];
```

`config/filesystems.php`
```php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
```

`config/logging.php`
```php
<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Processor\PsrLogMessageProcessor;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that is utilized to write
    | messages to your logs. The value provided here should match one of
    | the channels present in the list of "channels" configured below.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Deprecations Log Channel
    |--------------------------------------------------------------------------
    |
    | This option controls the log channel that should be used to log warnings
    | regarding deprecated PHP and library features. This allows you to get
    | your application ready for upcoming major versions of dependencies.
    |
    */

    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace' => env('LOG_DEPRECATIONS_TRACE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Laravel
    | utilizes the Monolog PHP logging library, which includes a variety
    | of powerful log handlers and formatters that you're free to use.
    |
    | Available drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog", "custom", "stack"
    |
    */

    'channels' => [

        'stack' => [
            'driver' => 'stack',
            'channels' => explode(',', (string) env('LOG_STACK', 'single')),
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => env('LOG_DAILY_DAYS', 14),
            'replace_placeholders' => true,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => env('LOG_SLACK_USERNAME', env('APP_NAME', 'Laravel')),
            'emoji' => env('LOG_SLACK_EMOJI', ':boom:'),
            'level' => env('LOG_LEVEL', 'critical'),
            'replace_placeholders' => true,
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => env('LOG_PAPERTRAIL_HANDLER', SyslogUdpHandler::class),
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
                'connectionString' => 'tls://'.env('PAPERTRAIL_URL').':'.env('PAPERTRAIL_PORT'),
            ],
            'processors' => [PsrLogMessageProcessor::class],
        ],

        'stderr' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => StreamHandler::class,
            'handler_with' => [
                'stream' => 'php://stderr',
            ],
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'processors' => [PsrLogMessageProcessor::class],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => env('LOG_LEVEL', 'debug'),
            'facility' => env('LOG_SYSLOG_FACILITY', LOG_USER),
            'replace_placeholders' => true,
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],

    ],

];
```

`config/mail.php`
```php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    |
    | This option controls the default mailer that is used to send all email
    | messages unless another mailer is explicitly specified when sending
    | the message. All additional mailers can be configured within the
    | "mailers" array. Examples of each type of mailer are provided.
    |
    */

    'default' => env('MAIL_MAILER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    |
    | Here you may configure all of the mailers used by your application plus
    | their respective settings. Several examples have been configured for
    | you and you are free to add your own as your application requires.
    |
    | Laravel supports a variety of mail "transport" drivers that can be used
    | when delivering an email. You may specify which one you're using for
    | your mailers below. You may also add additional mailers if needed.
    |
    | Supported: "smtp", "sendmail", "mailgun", "ses", "ses-v2",
    |            "postmark", "resend", "log", "array",
    |            "failover", "roundrobin"
    |
    */

    'mailers' => [

        'smtp' => [
            'transport' => 'smtp',
            'scheme' => env('MAIL_SCHEME'),
            'url' => env('MAIL_URL'),
            'host' => env('MAIL_HOST', '127.0.0.1'),
            'port' => env('MAIL_PORT', 2525),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN', parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_HOST)),
        ],

        'ses' => [
            'transport' => 'ses',
        ],

        'postmark' => [
            'transport' => 'postmark',
            // 'message_stream_id' => env('POSTMARK_MESSAGE_STREAM_ID'),
            // 'client' => [
            //     'timeout' => 5,
            // ],
        ],

        'resend' => [
            'transport' => 'resend',
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],

        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],

        'failover' => [
            'transport' => 'failover',
            'mailers' => [
                'smtp',
                'log',
            ],
            'retry_after' => 60,
        ],

        'roundrobin' => [
            'transport' => 'roundrobin',
            'mailers' => [
                'ses',
                'postmark',
            ],
            'retry_after' => 60,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    |
    | You may wish for all emails sent by your application to be sent from
    | the same address. Here you may specify a name and address that is
    | used globally for all emails that are sent by your application.
    |
    */

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => env('MAIL_FROM_NAME', env('APP_NAME', 'Laravel')),
    ],

];
```

`config/services.php`
```php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
```

`database/factories/UserFactory.php`
```php
<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
   public function definition(): array
{
    return [
        'username' => fake()->unique()->userName(),
        'password' => bcrypt('password'),
        'nama_lengkap' => fake()->name(),
        'role_id' => 1,
        'is_active' => true,
        'must_change_pwd' => false,
    ];
}

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
```

`database/migrations/0001_01_01_000001_create_cache_table.php`
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->bigInteger('expiration')->index();
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->bigInteger('expiration')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cache');
        Schema::dropIfExists('cache_locks');
    }
};
```

`database/migrations/2026_07_23_003817_create_sessions_table.php`
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
```

```bash
php artisan migrate
php artisan serve
git add app/Http/Controllers/Controller.php bootstrap/ config/ database/factories database/migrations routes/web.php
git commit -m "feat(core): establish application foundation"
git push -u origin feature/foundation
```
PR `feature/foundation → develop`, reviewer **Dirli**. Setelah approved, Marsya merge, lalu semua `git checkout develop && git pull origin develop`.

---

# STEP 6 — Dirli: Database Foundation

```bash
git checkout develop
git pull origin develop
git checkout -b feature/database-foundation
```

`database/migrations/2026_01_01_000001_create_roles_table.php`
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            $table->string('label');
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
```

`database/migrations/2026_01_01_000002_create_role_permissions_table.php`
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->string('perm_key');
            $table->boolean('can_write')->default(false);
            $table->primary(['role_id', 'perm_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
    }
};
```

`database/migrations/2026_01_01_000003_create_users_table.php`
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('password');
            $table->string('nama_lengkap');
            $table->string('email')->nullable();
            $table->string('jabatan')->nullable();
            $table->string('bagian')->nullable();
            $table->foreignId('role_id')->constrained('roles');
            $table->boolean('is_active')->default(true);
            $table->boolean('must_change_pwd')->default(false);
            $table->timestamp('last_login')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
```

`database/migrations/2026_01_01_000004_create_audit_log_table.php`
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Tabel ini memenuhi CPMK Teknologi Blockchain: prev_hash + hash
    // membentuk rantai hash sederhana pada audit trail, sehingga
    // perubahan pada baris lama akan merusak rantai dan bisa dideteksi
    // lewat proses verifikasi ulang (recompute hash tiap baris).
    public function up(): void
    {
        Schema::create('audit_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('username')->nullable();
            $table->string('aksi');
            $table->string('modul')->nullable();
            $table->string('entitas')->nullable();
            $table->string('entitas_id')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('prev_hash', 64)->nullable();
            $table->string('hash', 64);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_log');
    }
};
```

`database/migrations/2026_01_01_000005_create_reference_tables.php`
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('panduan', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('kategori')->default('Umum');
            $table->longText('konten')->nullable();
            $table->integer('urutan')->default(0);
            $table->string('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::create('risalah_rapat', function (Blueprint $table) {
            $table->id();
            $table->string('nomor')->nullable();
            $table->string('judul');
            $table->date('tanggal');
            $table->string('waktu')->nullable();
            $table->string('tempat')->nullable();
            $table->string('pemimpin')->nullable();
            $table->text('peserta')->nullable();
            $table->text('agenda')->nullable();
            $table->text('pembahasan')->nullable();
            $table->text('keputusan')->nullable();
            $table->text('tindak_lanjut')->nullable();
            $table->string('lampiran')->nullable();
            $table->string('dibuat_oleh')->nullable();
            $table->timestamps();
        });

        Schema::create('ref_akun', function (Blueprint $table) {
            $table->id();
            $table->string('nama_beban');
            $table->string('rekening_debet')->nullable();
            $table->text('contoh_keterangan')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ref_akun');
        Schema::dropIfExists('risalah_rapat');
        Schema::dropIfExists('panduan');
    }
};
```

`database/migrations/2026_01_01_000006_create_umum_rt_tables.php`
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Kolom maker_id/checker_id/approval_status di bawah ini
    // adalah bagian dari alur Maker-Checker (CPMK Blockchain: akuntabilitas
    // & integritas transaksi) dan akan ditulis ke audit_log berhash-chain.
    public function up(): void
    {
        Schema::create('um_kendaraan', function (Blueprint $table) {
            $table->id();
            $table->string('no_polisi');
            $table->string('jenis')->nullable();
            $table->string('merk')->nullable();
            $table->string('tahun')->nullable();
            $table->string('peruntukan')->nullable();
            $table->string('driver')->nullable();
            $table->string('status')->default('Aktif');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('um_biaya_harian', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('kategori')->default('BBM');
            $table->string('kendaraan')->nullable();
            $table->string('nama_beban')->nullable();
            $table->string('rekening_debet')->nullable();
            $table->string('rekening_kredit')->nullable();
            $table->text('uraian')->nullable();
            $table->decimal('jumlah', 18, 2)->default(0);
            $table->string('no_nota')->nullable();
            $table->string('dokumen')->nullable();
            $table->string('status')->default('Draft');
            $table->foreignId('maker_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('checker_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('approval_status')->default('Diajukan');
            $table->timestamp('approved_at')->nullable();
            $table->text('catatan_approval')->nullable();
            $table->string('dibuat_oleh')->nullable();
            $table->timestamps();
        });

        Schema::create('um_permintaan_cabang', function (Blueprint $table) {
            $table->id();
            $table->string('no_permintaan')->nullable();
            $table->date('tanggal');
            $table->string('unit_kerja')->nullable();
            $table->string('jenis')->default('ATK');
            $table->text('uraian')->nullable();
            $table->string('jumlah')->nullable();
            $table->string('satuan')->nullable();
            $table->string('dokumen')->nullable();
            $table->string('status')->default('Diajukan');
            $table->foreignId('maker_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('checker_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('approval_status')->default('Diajukan');
            $table->timestamp('approved_at')->nullable();
            $table->string('petugas')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('um_generate_log', function (Blueprint $table) {
            $table->id();
            $table->date('periode_awal')->nullable();
            $table->date('periode_akhir')->nullable();
            $table->string('kategori')->nullable();
            $table->integer('jumlah_item')->default(0);
            $table->decimal('total', 18, 2)->default(0);
            $table->string('dibuat_oleh')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('um_generate_log');
        Schema::dropIfExists('um_permintaan_cabang');
        Schema::dropIfExists('um_biaya_harian');
        Schema::dropIfExists('um_kendaraan');
    }
};
```

`database/migrations/2026_01_01_000007_create_aset_logistik_tables.php`
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('as_invoice_sewa', function (Blueprint $table) {
            $table->id();
            $table->string('no_invoice')->nullable();
            $table->string('vendor')->nullable();
            $table->string('jenis_sewa')->nullable();
            $table->date('periode_mulai')->nullable();
            $table->date('periode_selesai')->nullable();
            $table->decimal('nilai', 18, 2)->default(0);
            $table->date('jatuh_tempo')->nullable();
            $table->string('dokumen')->nullable();
            $table->string('status')->default('Belum Bayar');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('as_aset', function (Blueprint $table) {
            $table->id();
            $table->string('kode_aset')->nullable();
            $table->string('nama_aset');
            $table->string('kategori')->nullable();
            $table->string('lokasi')->nullable();
            $table->date('tanggal_perolehan')->nullable();
            $table->decimal('nilai_perolehan', 18, 2)->default(0);
            $table->integer('umur_ekonomis')->default(48);
            $table->string('kondisi')->default('Baik');
            $table->string('penanggung_jawab')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('as_amortisasi', function (Blueprint $table) {
            $table->id();
            $table->string('nama_biaya');
            $table->decimal('nilai_perolehan', 18, 2)->default(0);
            $table->date('tanggal_mulai')->nullable();
            $table->integer('umur_bulan')->default(12);
            $table->decimal('nilai_per_bulan', 18, 2)->default(0);
            $table->decimal('akumulasi', 18, 2)->default(0);
            $table->decimal('nilai_buku', 18, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('as_pks', function (Blueprint $table) {
            $table->id();
            $table->string('no_pks')->nullable();
            $table->string('judul');
            $table->string('vendor')->nullable();
            $table->string('div_owner')->nullable();
            $table->date('tanggal_mulai')->nullable();
            $table->date('jatuh_tempo')->nullable();
            $table->decimal('nilai', 18, 2)->default(0);
            $table->string('dokumen')->nullable();
            $table->string('status')->default('Aktif');
            $table->boolean('memo_dibuat')->default(false);
            $table->foreignId('maker_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('checker_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('approval_status')->default('Diajukan');
            $table->timestamp('approved_at')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('as_memo_sewa_cabang', function (Blueprint $table) {
            $table->id();
            $table->string('no_memo')->nullable();
            $table->string('cabang')->nullable();
            $table->string('jenis')->nullable();
            $table->date('tanggal')->nullable();
            $table->decimal('nilai', 18, 2)->default(0);
            $table->string('dokumen')->nullable();
            $table->string('status_persetujuan')->default('Diajukan');
            $table->foreignId('maker_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('checker_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('as_temuan', function (Blueprint $table) {
            $table->id();
            $table->string('no_temuan')->nullable();
            $table->string('sumber')->nullable();
            $table->text('uraian')->nullable();
            $table->date('tanggal_temuan')->nullable();
            $table->date('batas_tindak_lanjut')->nullable();
            $table->string('status')->default('Open');
            $table->string('penanggung_jawab')->nullable();
            $table->text('tindak_lanjut')->nullable();
            $table->string('dokumen')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('as_temuan');
        Schema::dropIfExists('as_memo_sewa_cabang');
        Schema::dropIfExists('as_pks');
        Schema::dropIfExists('as_amortisasi');
        Schema::dropIfExists('as_aset');
        Schema::dropIfExists('as_invoice_sewa');
    }
};
```

`database/migrations/2026_01_01_000008_create_pengadaan_tables.php`
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pg_memo_internal', function (Blueprint $table) {
            $table->id();
            $table->string('no_memo')->nullable();
            $table->string('dari_unit')->nullable();
            $table->string('ke_unit')->nullable();
            $table->string('perihal')->nullable();
            $table->date('tanggal')->nullable();
            $table->string('jenis')->nullable();
            $table->string('dokumen')->nullable();
            $table->string('status')->default('Masuk');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('pg_penawaran', function (Blueprint $table) {
            $table->id();
            $table->string('no_penawaran')->nullable();
            $table->string('vendor');
            $table->text('barang_jasa')->nullable();
            $table->decimal('nilai', 18, 2)->default(0);
            $table->date('tanggal')->nullable();
            $table->string('dokumen')->nullable();
            $table->string('status')->default('Diterima');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('pg_negosiasi', function (Blueprint $table) {
            $table->id();
            $table->string('no_berita_acara')->nullable();
            $table->string('vendor');
            $table->text('barang_jasa')->nullable();
            $table->decimal('nilai_awal', 18, 2)->default(0);
            $table->decimal('nilai_nego', 18, 2)->default(0);
            $table->date('tanggal')->nullable();
            $table->text('hasil')->nullable();
            $table->string('dokumen')->nullable();
            $table->timestamps();
        });

        Schema::create('pg_draft_dokumen', function (Blueprint $table) {
            $table->id();
            $table->string('jenis')->default('PKS');
            $table->string('no_dokumen')->nullable();
            $table->string('judul');
            $table->string('vendor')->nullable();
            $table->date('tanggal')->nullable();
            $table->string('status')->default('Draft');
            $table->string('file')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('pg_spk', function (Blueprint $table) {
            $table->id();
            $table->string('no_spk')->nullable();
            $table->string('vendor');
            $table->text('pekerjaan')->nullable();
            $table->decimal('nilai', 18, 2)->default(0);
            $table->date('tanggal_terbit')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->string('dokumen')->nullable();
            $table->string('status')->default('Berjalan');
            $table->foreignId('maker_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('checker_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('approval_status')->default('Diajukan');
            $table->timestamp('approved_at')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('pg_reminder', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('kategori')->nullable();
            $table->date('tanggal_jatuh_tempo');
            $table->string('status')->default('Aktif');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pg_reminder');
        Schema::dropIfExists('pg_spk');
        Schema::dropIfExists('pg_draft_dokumen');
        Schema::dropIfExists('pg_negosiasi');
        Schema::dropIfExists('pg_penawaran');
        Schema::dropIfExists('pg_memo_internal');
    }
};
```

`database/migrations/2026_01_01_000009_create_arsip_surat_memo_tables.php`
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['sr_surat_masuk', 'sr_surat_keluar', 'sr_memo_masuk', 'sr_memo_keluar'] as $table) {
            Schema::create($table, function (Blueprint $t) {
                $t->id();
                $t->integer('nomor_agenda')->nullable();
                $t->string('no_surat')->nullable();
                $t->string('pengirim')->nullable();
                $t->string('perihal');
                $t->date('tanggal');
                $t->string('penerima')->nullable();
                $t->string('lokasi_arsip')->nullable();
                $t->string('lampiran')->nullable();
                $t->string('dibuat_oleh')->nullable();
                $t->timestamps();
            });
        }
    }

    public function down(): void
    {
        foreach (['sr_memo_keluar', 'sr_memo_masuk', 'sr_surat_keluar', 'sr_surat_masuk'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
```

`database/migrations/2026_01_01_000011_create_queue_tables.php`
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Tabel bawaan Laravel untuk queue driver 'database'. Ini fondasi
    // CPMK Sistem Komputasi Terdistribusi: job diproses worker terpisah
    // dari proses request HTTP, tidak lagi blocking seperti versi Python.
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
    }
};
```

```bash
php artisan migrate
php artisan migrate:status
git add database/migrations
git commit -m "feat(database): create Portum database schema"
git push -u origin feature/database-foundation
```
PR → develop. Reviewer **Marsya**. Merge → semua pull.

---

# STEP 7 — Zahra: Baseline QA

```bash
git checkout develop
git pull origin develop
git checkout -b test/baseline
```

`tests/Feature/ExampleTest.php`
```php
<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        // Route '/' dilindungi middleware 'auth', sehingga guest
        // harus diarahkan ke halaman login (302), bukan 200.
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }
}
```

`tests/Unit/ExampleTest.php`
```php
<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_that_true_is_true(): void
    {
        $this->assertTrue(true);
    }
}
```

```bash
php artisan test
git add tests
git commit -m "test: establish baseline application tests"
git push -u origin test/baseline
```
PR → develop. Reviewer **Dirli**. Merge → Zahra pull develop.

---

# STEP 8 — Marsya: Authentication + RBAC

```bash
git checkout develop
git pull origin develop
git checkout -b feature/auth-rbac
```

`app/Models/User.php`
```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory;

    protected $table = 'users';

    protected $fillable = [
        'username',
        'password',
        'nama_lengkap',
        'email',
        'jabatan',
        'bagian',
        'role_id',
        'is_active',
        'must_change_pwd',
        'last_login'
    ];

    protected function casts(): array
    {
        return [
        'is_active' => 'boolean',
        'must_change_pwd' => 'boolean',
        'last_login' => 'datetime'
        ];
    }
    use Notifiable;

    protected $hidden = ['password', 'rememberToken'];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
```

`app/Models/Role.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';

    protected $fillable = [
        'nama',
        'label',
        'deskripsi'
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function permissions()
    {
        return $this->hasMany(RolePermission::class);
    }
}
```

`app/Models/RolePermission.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    protected $table = 'role_permissions';

    public $timestamps = false;
    
    protected $fillable = [
        'role_id',
        'perm_key',
        'can_write'
    ];
}
```

`app/Http/Controllers/Auth/LoginController.php`
```php
<?php

namespace App\Http\Controllers\Auth;

use App\Concerns\LogsAudit;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Login berbasis username (bukan email), setara auth session sederhana
// di portum.py -- dipertahankan supaya user tidak perlu diberi email
// palsu hanya untuk login.
class LoginController extends Controller
{
    use LogsAudit;

    public function show()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['username' => 'Username atau password salah.'])->onlyInput('username');
        }

        $request->session()->regenerate();
        auth()->user()->update(['last_login' => now()]);
        $this->audit('LOGIN', 'Auth', 'User', auth()->id(), 'Login ke sistem');

        if (auth()->user()->must_change_pwd) {
            return redirect()->route('password.force-change');
        }

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        $this->audit('LOGOUT', 'Auth', 'User', auth()->id(), 'Logout dari sistem');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function forceChangeForm()
    {
        return view('auth.force-change');
    }

    public function forceChange(Request $request)
    {
        $request->validate(['password' => 'required|string|min:8|confirmed']);
        $user = auth()->user();
        $user->update([
            'password' => bcrypt($request->password),
            'must_change_pwd' => false,
        ]);
        $this->audit('UPDATE', 'Auth', 'User', $user->id, 'Mengganti password wajib saat login pertama');
        return redirect()->route('dashboard')->with('status', 'Password berhasil diganti.');
    }
}
```

`app/Http/Middleware/CheckModulePermission.php`
```php
<?php

namespace App\Http\Middleware;

use App\Models\RolePermission;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// RBAC per-modul, setara matriks role/permission di versi Python, tapi
// sekarang ditegakkan lewat middleware di setiap route/group, bukan dicek
// manual di tiap handler. Daftarkan di bootstrap/app.php sebagai alias
// 'permission', lalu pakai di route: ->middleware('permission:aset_logistik,write')
class CheckModulePermission
{
    public function handle(Request $request, Closure $next, string $permKey, string $mode = 'read'): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401);
        }

        $perm = RolePermission::where('role_id', $user->role_id)
            ->where('perm_key', $permKey)
            ->first();

        if (!$perm) {
            abort(403, "Role Anda tidak memiliki akses ke modul {$permKey}.");
        }

        if ($mode === 'write' && !$perm->can_write) {
            abort(403, "Role Anda hanya bisa melihat modul {$permKey}, tidak bisa mengubah.");
        }

        return $next($request);
    }
}
```

`app/Http/Middleware/EnsureSuperadmin.php`
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// Halaman Manajemen User/Role & Audit Log hanya untuk superadmin --
// menu-nya juga disembunyikan di sidebar untuk role lain, tapi
// route-nya tetap harus ditutup di sisi server (jangan andalkan UI saja).
class EnsureSuperadmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->role?->nama === 'superadmin', 403, 'Halaman ini khusus Super Administrator.');
        return $next($request);
    }
}
```

`app/Policies/ApprovalPolicy.php`
```php
<?php

namespace App\Policies;

use App\Models\User;

// Policy generik untuk semua model yang punya alur Maker-Checker
// (UmBiayaHarian, UmPermintaanCabang, AsPks, AsMemoSewaCabang, PgSpk).
// Aturan inti CPMK Blockchain/akuntabilitas: checker TIDAK BOLEH orang
// yang sama dengan maker -- supaya approval tidak bisa self-approve.
class ApprovalPolicy
{
    public function approve(User $user, object $transaksi): bool
    {
        if ($transaksi->approval_status !== 'Diajukan') {
            return false;
        }

        if ((int) $transaksi->maker_id === (int) $user->id) {
            return false; // maker tidak boleh jadi checker dirinya sendiri
        }

        return true;
    }

    public function reject(User $user, object $transaksi): bool
    {
        return $this->approve($user, $transaksi);
    }
}
```

`resources/views/layouts/app.blade.php`
```blade
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') — Portum</title>
    @include('partials.head-assets')
    <style>
        /* Sidebar berdiri sendiri dan tidak ikut scroll halaman */
        @media (min-width: 1024px) {
            .portum-sidebar-rail { position: fixed; inset: 0 auto 0 0; width: 272px; z-index: 50; }
            .portum-main { margin-left: 272px; min-height: 100vh; }
        }
        .portum-nav-details > summary { list-style: none; }
        .portum-nav-details > summary::-webkit-details-marker { display: none; }
        .portum-nav-details[open] > summary .portum-chevron { transform: rotate(90deg); }
        .portum-chevron { transition: transform .18s ease; }
        .portum-submenu { animation: portumSubmenu .16s ease-out; }
        @keyframes portumSubmenu { from { opacity: .2; transform: translateY(-3px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="bg-canvas text-ink antialiased">
@php
    $user = auth()->user();
    $permissions = $user?->role?->permissions?->keyBy('perm_key') ?? collect();

    $canAccess = fn (string $key) => $user?->role?->nama === 'superadmin' || $permissions->has($key);
    $canWrite  = fn (string $key) => $user?->role?->nama === 'superadmin' || (bool) optional($permissions->get($key))->can_write;

    /*
     * Kategori = dropdown.
     * Setiap child mengarah ke sub-modulnya sendiri.
     * Permission parent tetap menjadi pengaman tampilan menu.
     */
    $operationalGroups = [
        [
            'perm' => 'umum_rt',
            'label' => 'Umum & Rumah Tangga',
            'icon' => 'building',
            'children' => [
                ['key' => 'kendaraan', 'label' => 'Kendaraan & Driver'],
                ['key' => 'biaya_harian', 'label' => 'Biaya BBM / Perawatan / Rumah Tangga'],
                ['key' => 'permintaan_cabang', 'label' => 'Permintaan Cabang (ATK/Inventaris)'],
            ],
        ],
        [
            'perm' => 'aset_logistik',
            'label' => 'Aset & Logistik',
            'icon' => 'building',
            'children' => [
                ['key' => 'invoice_sewa', 'label' => 'Invoice Sewa'],
                ['key' => 'aset', 'label' => 'Data Aset & Inventaris'],
                ['key' => 'amortisasi', 'label' => 'Amortisasi Aset'],
                ['key' => 'pks', 'label' => 'PKS & Jatuh Tempo'],
                ['key' => 'memo_sewa_cabang', 'label' => 'Memo Sewa Cabang'],
                ['key' => 'temuan', 'label' => 'Temuan Aset'],
            ],
        ],
        [
            'perm' => 'pengadaan',
            'label' => 'Pengadaan & Pemeliharaan',
            'icon' => 'cart',
            'children' => [
                ['key' => 'memo_internal', 'label' => 'Memo Internal'],
                ['key' => 'penawaran', 'label' => 'Penawaran'],
                ['key' => 'negosiasi', 'label' => 'Negosiasi'],
                ['key' => 'draft_dokumen', 'label' => 'Draft Dokumen'],
                ['key' => 'spk', 'label' => 'SPK'],
                ['key' => 'reminder', 'label' => 'Reminder'],
            ],
        ],
        [
            'perm' => 'arsip_surat_memo',
            'label' => 'Arsip Surat & Memo',
            'icon' => 'file-text',
            'children' => [
                ['key' => 'surat_masuk', 'label' => 'Surat Masuk'],
                ['key' => 'surat_keluar', 'label' => 'Surat Keluar'],
                ['key' => 'memo_masuk', 'label' => 'Memo Masuk'],
                ['key' => 'memo_keluar', 'label' => 'Memo Keluar'],
            ],
        ],
    ];
@endphp

{{-- DESKTOP: fixed rail. Logo dan sidebar tetap terpisah seperti referensi Google Drive. --}}
<div class="portum-sidebar-rail hidden lg:block bg-white">
    <div class="h-[112px] px-7 flex items-center bg-white">
        <img src="{{ asset('images/bank-sulteng.png') }}"
             alt="Bank Sulteng"
             class="w-[190px] h-auto object-contain object-left">
    </div>

    <aside class="absolute top-[112px] left-0 right-0 bottom-0 bg-gradient-to-b from-brand to-brand-dark text-slate-200 rounded-tr-[56px] shadow-[8px_0_24px_rgba(16,24,39,.10)] flex flex-col overflow-hidden">
        <nav class="flex-1 overflow-y-auto px-4 pt-8 pb-5 text-[13px]">
            <div class="space-y-1">
                <a href="{{ route('dashboard') }}"
                   class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition
                   {{ request()->routeIs('dashboard') ? 'bg-gradient-to-r from-[#D9A52A] to-[#C78E16] text-white shadow-gold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    @include('partials.icon', ['name' => 'home', 'class' => 'w-[19px] h-[19px] flex-shrink-0'])
                    <span>Dashboard</span>
                </a>

                @if ($canAccess('analytics_dw'))
                    <a href="{{ route('analitik') }}"
                       class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition
                       {{ request()->routeIs('analitik') ? 'bg-white/10 text-white font-semibold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                        @include('partials.icon', ['name' => 'chart', 'class' => 'w-[19px] h-[19px] flex-shrink-0'])
                        <span>Analitik DW</span>
                    </a>
                @endif
            </div>

            <div class="mt-7">
                <p class="px-4 mb-3 text-[10.5px] font-bold uppercase tracking-[.08em] text-gold">Operasional</p>
                <div class="space-y-1">
                    @foreach ($operationalGroups as $group)
                        @if ($canAccess($group['perm']))
                            @php
                                $groupActive = collect($group['children'])->contains(fn ($child) => request()->route('key') === $child['key']);
                            @endphp
                            <details class="portum-nav-details" {{ $groupActive ? 'open' : '' }}>
                                <summary class="flex items-center justify-between gap-3 px-4 py-2.5 rounded-xl cursor-pointer transition text-slate-300 hover:bg-white/10 hover:text-white {{ $groupActive ? 'bg-white/10 text-white font-semibold' : '' }}">
                                    <span class="flex items-center gap-3 min-w-0">
                                        @include('partials.icon', ['name' => $group['icon'], 'class' => 'w-[19px] h-[19px] flex-shrink-0'])
                                        <span class="truncate">{{ $group['label'] }}</span>
                                    </span>
                                    <span class="portum-chevron text-slate-400 text-lg leading-none">›</span>
                                </summary>
                                <div class="portum-submenu mt-1 ml-3 pl-4 border-l border-white/10 space-y-0.5">
                                    @foreach ($group['children'] as $child)
                                        @php $active = request()->route('key') === $child['key']; @endphp
                                        <a href="{{ route('modul.index', $child['key']) }}"
                                           class="flex items-center gap-2 px-3 py-2 rounded-lg text-[12px] leading-4 transition {{ $active ? 'bg-white/10 text-white font-semibold' : 'text-slate-400 hover:bg-white/10 hover:text-white' }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $active ? 'bg-gold' : 'bg-slate-500' }} flex-shrink-0"></span>
                                            <span>{{ $child['label'] }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </details>
                        @endif
                    @endforeach
                </div>
            </div>

            @php
                $referenceItems = [
                    ['perm' => 'risalah', 'label' => 'Risalah Rapat', 'route' => 'risalah.index', 'active' => request()->routeIs('risalah.*'), 'icon' => 'file-text'],
                    ['perm' => 'panduan', 'label' => 'Panduan', 'route' => 'panduan.index', 'active' => request()->routeIs('panduan.*'), 'icon' => 'book'],
                    ['perm' => 'ref_akun', 'label' => 'Referensi Akun', 'route' => 'modul.index', 'parameter' => 'ref_akun', 'active' => request()->route('key') === 'ref_akun', 'icon' => 'file-text'],
                ];
                $visibleReferences = collect($referenceItems)->filter(fn ($item) => $canAccess($item['perm']));
            @endphp

            @if ($visibleReferences->isNotEmpty())
                <div class="mt-7">
                    <p class="px-4 mb-3 text-[10.5px] font-bold uppercase tracking-[.08em] text-gold">Referensi</p>
                    <div class="space-y-1">
                        @foreach ($visibleReferences as $item)
                            <a href="{{ isset($item['parameter']) ? route($item['route'], $item['parameter']) : route($item['route']) }}"
                               class="nav-link flex items-center gap-3 px-4 py-2.5 rounded-xl transition
                               {{ $item['active'] ? 'bg-white/10 text-white font-semibold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                                @include('partials.icon', ['name' => $item['icon'], 'class' => 'w-[19px] h-[19px] flex-shrink-0'])
                                <span>{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($user?->role?->nama === 'superadmin')
                <div class="mt-7">
                    <p class="px-4 mb-3 text-[10.5px] font-bold uppercase tracking-[.08em] text-gold">Administrasi</p>
                    <div class="space-y-1">
                        <a href="{{ route('admin.users.index') }}" class="nav-link flex items-center gap-3 px-4 py-2.5 rounded-xl transition {{ request()->routeIs('admin.users.*') ? 'bg-white/10 text-white font-semibold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                            @include('partials.icon', ['name' => 'users', 'class' => 'w-[19px] h-[19px] flex-shrink-0'])
                            <span>Manajemen User</span>
                        </a>
                        <a href="{{ route('admin.roles.index') }}" class="nav-link flex items-center gap-3 px-4 py-2.5 rounded-xl transition {{ request()->routeIs('admin.roles.*') ? 'bg-white/10 text-white font-semibold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                            @include('partials.icon', ['name' => 'shield', 'class' => 'w-[19px] h-[19px] flex-shrink-0'])
                            <span>Role &amp; Permission</span>
                        </a>
                        <a href="{{ route('admin.audit-log.index') }}" class="nav-link flex items-center gap-3 px-4 py-2.5 rounded-xl transition {{ request()->routeIs('admin.audit-log.*') ? 'bg-white/10 text-white font-semibold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                            @include('partials.icon', ['name' => 'file-text', 'class' => 'w-[19px] h-[19px] flex-shrink-0'])
                            <span>Audit Log</span>
                        </a>
                    </div>
                </div>
            @endif
        </nav>

        <div class="px-6 py-4 border-t border-white/10 flex items-center gap-3">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 shadow-[0_0_0_4px_rgba(52,211,153,.10)] flex-shrink-0"></span>
            <div class="min-w-0">
                <p class="text-[11px] text-slate-300 font-medium">Sistem Aktif</p>
                <p class="text-[10px] text-slate-500 truncate">Rantai audit aktif · v1.0</p>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-white/10 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-[#D9A52A] text-white flex items-center justify-center font-bold flex-shrink-0">
                {{ strtoupper(substr($user->nama_lengkap, 0, 1)) }}
            </div>
            <div class="min-w-0 leading-tight">
                <p class="text-[13px] font-semibold text-white truncate">{{ $user->nama_lengkap }}</p>
                <p class="text-[10.5px] text-slate-400 truncate">{{ $user->role->label }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="ml-auto">
                @csrf
                <button class="text-slate-400 hover:text-white transition" title="Keluar" aria-label="Keluar">
                    @include('partials.icon', ['name' => 'logout', 'class' => 'w-4 h-4'])
                </button>
            </form>
        </div>
    </aside>
</div>

{{-- Mobile header --}}
<div class="lg:hidden bg-white border-b border-slate-200 px-4 py-3 flex items-center justify-between">
    <img src="{{ asset('images/bank-sulteng.png') }}" alt="Bank Sulteng" class="w-[145px] h-auto">
    <div class="flex items-center gap-2">
        <span class="text-sm font-semibold">{{ $user->nama_lengkap }}</span>
        <div class="w-9 h-9 rounded-full bg-[#D9A52A] text-white flex items-center justify-center font-bold">{{ strtoupper(substr($user->nama_lengkap, 0, 1)) }}</div>
    </div>
</div>

{{-- Content tidak berada di dalam container sidebar; sidebar fixed terpisah. --}}
<div class="portum-main min-w-0 bg-white">
    <header class="h-[72px] bg-white px-5 lg:px-8 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3 min-w-0">
            <div class="hidden sm:flex w-full max-w-[360px] items-center gap-2.5 bg-[#F4F5F7] rounded-full px-4 py-2.5 text-slate-400">
                @include('partials.icon', ['name' => 'search', 'class' => 'w-[18px] h-[18px] flex-shrink-0'])
                <span class="text-[12px] truncate">Cari data, dokumen, atau modul...</span>
            </div>
            <h1 class="sr-only">@yield('title', 'Dashboard')</h1>
        </div>

        <div class="flex items-center gap-4 flex-shrink-0">
            <button class="relative text-slate-600 hover:text-brand transition" aria-label="Notifikasi">
                @include('partials.icon', ['name' => 'bell', 'class' => 'w-[22px] h-[22px]'])
                <span class="absolute -right-2 -top-2 min-w-[17px] h-[17px] px-1 rounded-full bg-red-500 text-white text-[9px] font-bold flex items-center justify-center">3</span>
            </button>
            <button class="text-slate-600 hover:text-brand transition" aria-label="Bantuan">?</button>
            <button class="text-slate-600 hover:text-brand transition" aria-label="Pengaturan">⚙</button>
            <div class="h-8 w-px bg-slate-200"></div>
            <div class="hidden sm:block text-right leading-tight">
                <p class="text-[13px] font-semibold text-ink">{{ $user->nama_lengkap }}</p>
                <p class="text-[10.5px] text-slate-500">{{ $user->role->label }}</p>
            </div>
            <div class="w-10 h-10 rounded-full bg-[#D9A52A] text-white flex items-center justify-center font-bold">
                {{ strtoupper(substr($user->nama_lengkap, 0, 1)) }}
            </div>
            <span class="text-slate-500">⌄</span>
        </div>
    </header>

    <main class="px-5 lg:px-8 pb-8">
        @if (session('status'))
            <div class="mb-5 flex items-start gap-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm animate-enter">
                @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-[18px] h-[18px] flex-shrink-0 mt-0.5', 'stroke' => 2])
                <span>{{ session('status') }}</span>
            </div>
        @endif
        @if ($errors->any())
            <div class="mb-5 flex items-start gap-2.5 rounded-xl bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm animate-enter">
                @include('partials.icon', ['name' => 'alert', 'class' => 'w-[18px] h-[18px] flex-shrink-0 mt-0.5', 'stroke' => 2])
                <ul class="list-disc pl-4 space-y-0.5">
                    @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                </ul>
            </div>
        @endif
        <div class="animate-enter">@yield('content')</div>
    </main>
</div>
</body>
</html>
```

`resources/views/partials/brand-mark.blade.php`
```blade
@php $cls = $class ?? 'w-8 h-8'; @endphp
<svg viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" class="{{ $cls }}">
    <rect x="3" y="10" width="15" height="12" rx="6" stroke="currentColor" stroke-width="2.4"/>
    <rect x="14" y="10" width="15" height="12" rx="6" stroke="#BF8F3D" stroke-width="2.4"/>
</svg>
```

`resources/views/partials/head-assets.blade.php`
```blade
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          brand: {
            DEFAULT: '#16233D',
            light: '#223257',
            dark: '#0D1526',
          },
          gold: {
            DEFAULT: '#BF8F3D',
            soft: '#E9D9B6',
            light: '#F4E9D2',
          },
          canvas: '#F5F3EE',
          ink: '#1C2333',
        },
        boxShadow: {
          card: '0 1px 2px rgba(16, 24, 39, 0.04), 0 1px 12px rgba(16, 24, 39, 0.05)',
          hover: '0 4px 10px rgba(16, 24, 39, 0.06), 0 8px 30px rgba(16, 24, 39, 0.08)',
          brand: '0 8px 24px rgba(22, 35, 61, 0.18)',
          gold: '0 8px 20px rgba(191, 143, 61, 0.22)',
        },
      },
      fontFamily: {
        sans: ['Manrope', 'ui-sans-serif', 'system-ui'],
        mono: ['"IBM Plex Mono"', 'ui-monospace', 'monospace'],
      },
    },
  }
</script>
<style>
  body { font-family: 'Manrope', ui-sans-serif, system-ui; }
  .font-mono { font-family: 'IBM Plex Mono', ui-monospace, monospace; }

  ::-webkit-scrollbar { width: 7px; height: 7px; }
  ::-webkit-scrollbar-track { background: transparent; }
  ::-webkit-scrollbar-thumb { background: #d8d3c8; border-radius: 999px; }
  ::-webkit-scrollbar-thumb:hover { background: #c7c0b0; }

  aside ::-webkit-scrollbar-thumb { background: rgba(255,255,255,.14); }
  aside ::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,.22); }

  ::selection { background: #BF8F3D; color: #fff; }

  a:focus-visible, button:focus-visible, input:focus-visible, select:focus-visible, textarea:focus-visible {
    outline: 2px solid #BF8F3D; outline-offset: 2px; border-radius: 6px;
  }

  input[type="checkbox"], input[type="radio"] { accent-color: #16233D; }

  @keyframes fadeInUp { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
  .animate-enter { animation: fadeInUp .35s ease both; }

  details > summary { list-style: none; }
  details > summary::-webkit-details-marker { display: none; }
  details[open] .chev { transform: rotate(180deg); }

  .nav-link { position: relative; }
  .nav-link.active::before {
    content: ''; position: absolute; left: -12px; top: 50%; transform: translateY(-50%);
    width: 3px; height: 16px; border-radius: 999px; background: #BF8F3D;
  }
</style>
```

`resources/views/partials/icon.blade.php`
```blade
@php
    $cls = $class ?? 'w-5 h-5';
    $sw = $stroke ?? 1.7;
    $icons = [
        'home'        => '<path d="M4 11.2 12 4.5l8 6.7"/><path d="M5.5 9.8V19a1 1 0 0 0 1 1H9.8v-5.2a1.7 1.7 0 0 1 1.7-1.7h1a1.7 1.7 0 0 1 1.7 1.7V20h3.3a1 1 0 0 0 1-1V9.8"/>',
        'chart'       => '<rect x="3.5" y="12.5" width="4" height="8" rx="1"/><rect x="10" y="8.5" width="4" height="12" rx="1"/><rect x="16.5" y="4.5" width="4" height="16" rx="1"/>',
        'truck'       => '<rect x="2.5" y="7.5" width="11" height="9" rx="1.2"/><path d="M13.5 10.5H17l3 3v3h-2"/><circle cx="7" cy="18" r="1.6"/><circle cx="16.5" cy="18" r="1.6"/><path d="M13.5 16.4H10"/>',
        'archive'     => '<rect x="3" y="4.5" width="18" height="4" rx="1"/><path d="M4.5 8.5V18a1.2 1.2 0 0 0 1.2 1.2h12.6A1.2 1.2 0 0 0 19.5 18V8.5"/><path d="M10 12.5h4"/>',
        'wrench'      => '<path d="M14.7 6.3a3.8 3.8 0 0 0-5 4.6L4.3 16.3a1.7 1.7 0 0 0 2.4 2.4l5.4-5.4a3.8 3.8 0 0 0 4.6-5l-2.4 2.4-2-2 2.4-2.4Z"/>',
        'inbox'       => '<path d="M4 12.5h4.3l1.4 2.4h4.6l1.4-2.4H20"/><path d="M5.2 6.8 4 12.5V18a1.3 1.3 0 0 0 1.3 1.3h13.4A1.3 1.3 0 0 0 20 18v-5.5l-1.2-5.7a1.3 1.3 0 0 0-1.27-1.05H6.47A1.3 1.3 0 0 0 5.2 6.8Z"/>',
        'book'        => '<path d="M12 6.3c-1.6-1-4-1.3-6-1.1a1 1 0 0 0-.9 1v11a.9.9 0 0 0 1 .9c2-.2 4.5.1 5.9 1.2 1.4-1.1 3.9-1.4 5.9-1.2a.9.9 0 0 0 1-.9v-11a1 1 0 0 0-.9-1c-2-.2-4.4.1-6 1.1Z"/><path d="M12 6.3V19.3"/>',
        'sliders'     => '<path d="M4 6h9M17 6h3M4 12h3M9 12h11M4 18h13M19 18h1"/><circle cx="15" cy="6" r="1.8"/><circle cx="7" cy="12" r="1.8"/><circle cx="17" cy="18" r="1.8"/>',
        'users'       => '<circle cx="8.5" cy="8" r="3"/><path d="M2.8 19c.6-3 2.9-5 5.7-5s5.1 2 5.7 5"/><circle cx="17" cy="9" r="2.4"/><path d="M15.7 14.2c2.2.4 3.9 2.1 4.4 4.6"/>',
        'shield'      => '<path d="M12 3.5 19 6v5.3c0 4.2-2.8 7.4-7 8.7-4.2-1.3-7-4.5-7-8.7V6l7-2.5Z"/><path d="m9.2 12 1.9 1.9 3.7-3.9"/>',
        'clock'       => '<circle cx="12" cy="12" r="8.2"/><path d="M12 7.5V12l3 2"/>',
        'logout'      => '<path d="M9.5 20H6a1.6 1.6 0 0 1-1.6-1.6V5.6A1.6 1.6 0 0 1 6 4h3.5"/><path d="M15.5 16.3 20 12l-4.5-4.3M20 12H9.7"/>',
        'search'      => '<circle cx="10.6" cy="10.6" r="6.1"/><path d="m19.5 19.5-4.2-4.2"/>',
        'plus'        => '<path d="M12 5.5v13M5.5 12h13"/>',
        'pencil'      => '<path d="m14.3 4.8 4.9 4.9L7.8 18.1l-5 1 1-5Z"/><path d="m12.3 6.8 4.9 4.9"/>',
        'trash'       => '<path d="M4.5 7h15"/><path d="M9.5 7V5.3A1.3 1.3 0 0 1 10.8 4h2.4a1.3 1.3 0 0 1 1.3 1.3V7"/><path d="M6.5 7 7.3 19a1.5 1.5 0 0 0 1.5 1.4h6.4a1.5 1.5 0 0 0 1.5-1.4L17.5 7"/><path d="M10.3 11v6M13.7 11v6"/>',
        'chevron-down'=> '<path d="m6 9 6 6 6-6"/>',
        'chevron-right'=>'<path d="m9 6 6 6-6 6"/>',
        'check-circle'=> '<circle cx="12" cy="12" r="8.2"/><path d="m8.3 12.3 2.4 2.4 5-5.4"/>',
        'x-circle'    => '<circle cx="12" cy="12" r="8.2"/><path d="m9.2 9.2 5.6 5.6M14.8 9.2l-5.6 5.6"/>',
        'alert'       => '<path d="M12 4.2 21 19.5H3L12 4.2Z"/><path d="M12 10v4.2"/><circle cx="12" cy="17" r=".15" fill="currentColor" stroke-width="2.6"/>',
        'bank'        => '<path d="M3.5 9.5 12 4l8.5 5.5"/><path d="M4.5 9.5h15V19a1 1 0 0 1-1 1H5.5a1 1 0 0 1-1-1V9.5Z"/><path d="M8 13v4M12 13v4M16 13v4"/>',
        'key'         => '<circle cx="7.3" cy="14.7" r="3.3"/><path d="m9.6 12.4 7.9-7.9M15 6l2 2M17.9 3.1l2 2"/>',
        'link'        => '<path d="M9 15 15 9"/><path d="M11 6.5 12.4 5a3.7 3.7 0 0 1 5.2 5.2l-1.5 1.4M13 17.5 11.6 19a3.7 3.7 0 0 1-5.2-5.2l1.5-1.4"/>',
        'file-text'   => '<path d="M7 3.5h7l4 4V19a1.2 1.2 0 0 1-1.2 1.2H7A1.2 1.2 0 0 1 5.8 19V4.7A1.2 1.2 0 0 1 7 3.5Z"/><path d="M14 3.5V8h4.2"/><path d="M8.5 12.5h7M8.5 15.8h4.5"/>',
        'trend-up'    => '<path d="m4 16 5.5-5.5L13 14l7-7"/><path d="M16.5 7H20v3.5"/>',
        'building'    => '<rect x="5" y="3.5" width="10" height="17" rx="1"/><path d="M15 9.5h4.5v10a1 1 0 0 1-1 1H15"/><path d="M8 7.5h1M8 11h1M8 14.5h1M11.5 7.5h1M11.5 11h1M11.5 14.5h1"/>',
        'bell'        => '<path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9Z"/><path d="M10 21h4"/>',
        'menu'        => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'lock'        => '<rect x="5" y="10.5" width="14" height="9" rx="1.4"/><path d="M8 10.5V7.8a4 4 0 0 1 8 0v2.7"/>',
    ];
    $svg = $icons[$name] ?? $icons['file-text'];
@endphp
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="{{ $sw }}" stroke-linecap="round" stroke-linejoin="round" class="{{ $cls }}">{!! $svg !!}</svg>
```

`resources/views/auth/login.blade.php`
```blade
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — PORTUM | Bank Sulteng</title>
<style>
:root{--navy:#07366f;--gold:#dca51b;--text:#10284a;--muted:#71809a;--line:#d9e0e9}
*{box-sizing:border-box}body{margin:0;min-height:100vh;font-family:Inter,system-ui,-apple-system,"Segoe UI",sans-serif;background:#f5f8fc;color:var(--text)}
.login-page{min-height:100vh;padding:18px;display:flex;align-items:stretch;justify-content:center}
.login-shell{width:min(1400px,100%);min-height:calc(100vh - 36px);display:grid;grid-template-columns:56% 44%;overflow:hidden;border-radius:24px;background:#fff;box-shadow:0 18px 55px rgba(20,46,82,.12)}
.brand-panel {
    position: relative;
    min-height: 720px;
    overflow: hidden;
    padding: 42px 64px 34px;

    display: flex;
    flex-direction: column;

    color: #fff;

    background:
        linear-gradient(
            180deg,
            rgba(11, 57, 111, .30) 0%,
            rgba(6, 45, 91, .72) 58%,
            rgba(4, 38, 79, .98) 100%
        ),
        url("{{ asset('images/bank-sulteng-building.png') }}")
        center center / cover no-repeat;
}
.brand-content,.brand-footer{position:relative;z-index:2}.brand-logo{width:min(255px,48%);height:auto;display:block;filter:drop-shadow(0 2px 8px rgba(0,0,0,.08))}
.brand-copy{max-width:510px;margin-top:auto;padding-bottom:24px}.brand-copy h1{margin:0 0 8px;font-size:clamp(42px,4vw,58px);line-height:1;font-weight:800;letter-spacing:.02em}.subtitle{margin:0 0 22px;color:#f2b92c;font-size:clamp(20px,2vw,27px);font-weight:650}.description{margin:0;max-width:500px;font-size:17px;line-height:1.65;color:rgba(255,255,255,.94)}
.security-note{display:flex;align-items:center;gap:15px;margin-top:48px;max-width:480px}.security-icon{width:48px;height:48px;flex:0 0 48px;display:grid;place-items:center;border:2px solid #f0b72a;border-radius:50%;color:#f0b72a;font-size:21px}.security-note p{margin:0;color:#fff;font-size:15px;line-height:1.55}.brand-footer{margin-top:auto;padding-top:20px;font-size:13px;color:rgba(255,255,255,.85);text-align:center}
.form-panel{display:flex;align-items:center;justify-content:center;padding:52px 8%;background:#fff}.login-card{width:min(500px,100%)}.login-card h2{margin:0;color:var(--navy);font-size:clamp(32px,3vw,43px);line-height:1.12;font-weight:800}.login-intro{margin:12px 0 40px;color:var(--muted);font-size:16px;line-height:1.5}
.alert{margin-bottom:22px;padding:13px 15px;border:1px solid #f1b7b7;border-radius:10px;background:#fff5f5;color:#9c2c2c;font-size:14px}.field{margin-bottom:23px}.field label{display:block;margin-bottom:9px;font-size:15px;font-weight:700}.input-wrap{position:relative}.input-icon{position:absolute;left:17px;top:50%;transform:translateY(-50%);width:21px;height:21px;color:#6d7c92;pointer-events:none}.form-input{width:100%;height:56px;padding:0 50px 0 51px;border:1px solid var(--line);border-radius:10px;outline:none;background:#fff;color:var(--text);font-size:15px;transition:.2s}.form-input:focus{border-color:#2b6fb5;box-shadow:0 0 0 4px rgba(43,111,181,.11)}.form-input::placeholder{color:#8c98aa}
.password-toggle{position:absolute;right:14px;top:50%;transform:translateY(-50%);border:0;background:transparent;color:#64748b;cursor:pointer;padding:5px}.password-toggle svg{width:21px;height:21px}.login-options{margin:2px 0 30px;display:flex;align-items:center}.remember{display:inline-flex;align-items:center;gap:10px;font-size:14px;cursor:pointer}.remember input{width:19px;height:19px;accent-color:var(--navy)}
.login-button{width:100%;height:56px;border:0;border-radius:10px;background:linear-gradient(180deg,#0a407f,#07366f);color:#fff;font-size:16px;font-weight:800;letter-spacing:.04em;cursor:pointer;box-shadow:0 8px 18px rgba(7,54,111,.18)}.login-button:hover{filter:brightness(1.06)}
.admin-help{margin-top:28px;text-align:center;color:#66758b;font-size:14px}.admin-help a{color:#0758a4;font-weight:700;text-decoration:none}
@media(max-width:980px){.login-shell{grid-template-columns:1fr}.brand-panel{min-height:440px;padding:32px 38px}.brand-copy{margin-top:70px}.brand-logo{width:220px}.form-panel{padding:55px 38px}}
@media(max-width:600px){.login-page{padding:0}.login-shell{min-height:100vh;border-radius:0}.brand-panel{min-height:365px;padding:28px 25px}.brand-logo{width:190px}.brand-copy{margin-top:52px}.brand-copy h1{font-size:38px}.subtitle{font-size:19px}.description,.security-note,.brand-footer{display:none}.form-panel{padding:42px 25px 50px;align-items:flex-start}.login-card h2{font-size:31px}}
</style>
</head>
<body>
<div class="login-page">
<main class="login-shell">
<section class="brand-panel">
<div class="brand-content">
<img class="brand-logo" src="{{ asset('images/bank-sulteng.png') }}" alt="Bank Sulteng">
<div class="brand-copy">
<h1>PORTUM</h1>
<p class="subtitle">Portal Umum &amp; Aset</p>
<p class="description">Sistem terintegrasi untuk pengelolaan data Umum, Aset, dan Pengadaan secara efisien dan transparan.</p>
<div class="security-note"><div class="security-icon">⌾</div><p><strong>Akses terbatas untuk pengguna terdaftar</strong><br>Terenkripsi &amp; diaudit menyeluruh.</p></div>
</div>
</div>
<div class="brand-footer">© {{ date('Y') }} PT Bank Sulteng. All rights reserved.</div>
</section>

<section class="form-panel">
<div class="login-card">
<h2>Selamat Datang</h2>
<p class="login-intro">Masuk untuk melanjutkan ke Portal Umum &amp; Aset.</p>

@if ($errors->any())
<div class="alert" role="alert">{{ $errors->first() }}</div>
@endif

<form method="POST" action="{{ route('login') }}">
@csrf
<div class="field">
<label for="username">Username</label>
<div class="input-wrap">
<svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="3.5"/><path d="M5 20c.8-3.2 3.1-5 7-5s6.2 1.8 7 5"/></svg>
<input id="username" name="username" type="text" class="form-input" value="{{ old('username') }}" placeholder="Masukkan username Anda" autocomplete="username" autofocus required>
</div>
</div>

<div class="field">
<label for="password">Password</label>
<div class="input-wrap">
<svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="5" y="10" width="14" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
<input id="password" name="password" type="password" class="form-input" placeholder="Masukkan password Anda" autocomplete="current-password" required>
<button type="button" class="password-toggle" id="togglePassword" aria-label="Tampilkan password">
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2.5 12s3.4-6 9.5-6 9.5 6 9.5 6-3.4 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>
</button>
</div>
</div>

<div class="login-options"><label class="remember"><input type="checkbox" name="remember" value="1"><span>Ingat saya</span></label></div>
<button type="submit" class="login-button">MASUK</button>
</form>

<div class="admin-help">Belum memiliki akun? <a href="mailto:administrator@banksulteng.co.id">Hubungi administrator.</a></div>
</div>
</section>
</main>
</div>
<script>
const passwordInput=document.getElementById('password'),toggle=document.getElementById('togglePassword');
toggle?.addEventListener('click',function(){const show=passwordInput.type==='password';passwordInput.type=show?'text':'password';this.setAttribute('aria-label',show?'Sembunyikan password':'Tampilkan password');});
</script>
</body>
</html>
```

`resources/views/auth/force-change.blade.php`
```blade
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ganti Password — Portum</title>
    @include('partials.head-assets')
</head>
<body class="bg-canvas text-ink antialiased min-h-screen flex items-center justify-center p-6">
    <div class="absolute inset-0 opacity-[0.35] pointer-events-none"
         style="background-image: radial-gradient(circle, #ddd6c8 1px, transparent 1px); background-size: 24px 24px;"></div>

    <div class="w-full max-w-sm bg-white rounded-2xl shadow-hover border border-slate-200 p-8 relative">
        <div class="flex items-center gap-2.5 mb-6">
            @include('partials.brand-mark', ['class' => 'w-7 h-7 text-brand'])
            <span class="font-bold text-brand">Portum</span>
        </div>

        <div class="w-11 h-11 rounded-xl bg-gold-light text-gold flex items-center justify-center mb-4">
            @include('partials.icon', ['name' => 'key', 'class' => 'w-5 h-5'])
        </div>

        <h1 class="text-lg font-bold mb-1">Ganti password Anda</h1>
        <p class="text-sm text-slate-500 mb-6">Ini login pertama Anda &mdash; buat password baru sebelum melanjutkan.</p>

        @if ($errors->any())
            <div class="mb-4 flex items-start gap-2.5 rounded-xl bg-red-50 border border-red-200 text-red-700 px-3.5 py-2.5 text-sm">
                @include('partials.icon', ['name' => 'alert', 'class' => 'w-4 h-4 flex-shrink-0 mt-0.5', 'stroke' => 2])
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('password.force-change.submit') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-[13px] font-medium mb-1.5 text-slate-700">Password Baru</label>
                <input type="password" name="password" required minlength="8"
                       class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:border-brand focus:ring-1 focus:ring-brand transition">
            </div>
            <div>
                <label class="block text-[13px] font-medium mb-1.5 text-slate-700">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" required minlength="8"
                       class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:border-brand focus:ring-1 focus:ring-brand transition">
            </div>
            <button class="w-full bg-brand text-white rounded-lg py-2.5 text-sm font-semibold hover:bg-brand-light transition shadow-brand">Simpan &amp; Lanjutkan</button>
        </form>
    </div>
</body>
</html>
```

`database/seeders/RolePermissionSeeder.php`
```php
<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    // Data role & matriks permission dipindahkan 1:1 dari portum.py (fungsi init_db),
    // supaya perilaku hak akses tidak berubah saat cutover ke Laravel.
    public function run(): void
    {
        $roles = [
            ['id' => 1, 'nama' => 'superadmin', 'label' => 'Super Administrator', 'deskripsi' => 'Akses penuh seluruh modul & pengaturan'],
            ['id' => 2, 'nama' => 'pimpinan', 'label' => 'Pimpinan Divisi', 'deskripsi' => 'Akses lihat seluruh modul (monitoring)'],
            ['id' => 3, 'nama' => 'umum_rt', 'label' => 'Staf Umum & Rumah Tangga', 'deskripsi' => 'Bagian Umum & Rumah Tangga'],
            ['id' => 4, 'nama' => 'aset', 'label' => 'Staf Aset & Logistik', 'deskripsi' => 'Bagian Aset/Inventaris & Logistik'],
            ['id' => 5, 'nama' => 'pengadaan', 'label' => 'Staf Pengadaan', 'deskripsi' => 'Bagian Pengadaan & Pemeliharaan Aset'],
        ];
        foreach ($roles as $r) {
            Role::updateOrCreate(['id' => $r['id']], $r);
        }

        $perms = [
            [1, 'dashboard', 1], [1, 'umum_rt', 1], [1, 'aset_logistik', 1], [1, 'pengadaan', 1], [1, 'risalah', 1],
            [1, 'panduan', 1], [1, 'analytics_dw', 1], [1, 'user_mgmt', 1], [1, 'role_mgmt', 1], [1, 'audit_log', 1], [1, 'ref_akun', 1],
            [2, 'dashboard', 0], [2, 'analytics_dw', 0], [2, 'umum_rt', 1], [2, 'aset_logistik', 1], [2, 'pengadaan', 1], [2, 'risalah', 0],
            [2, 'panduan', 0], [2, 'audit_log', 0], [2, 'ref_akun', 0],
            [3, 'dashboard', 1], [3, 'umum_rt', 1], [3, 'risalah', 1], [3, 'panduan', 0], [3, 'ref_akun', 0],
            [4, 'dashboard', 1], [4, 'aset_logistik', 1], [4, 'risalah', 1], [4, 'panduan', 0], [4, 'ref_akun', 0],
            [5, 'dashboard', 1], [5, 'pengadaan', 1], [5, 'risalah', 1], [5, 'panduan', 0], [5, 'ref_akun', 0],
        ];
        foreach ($perms as [$roleId, $key, $write]) {
            DB::table('role_permissions')->updateOrInsert(
                ['role_id' => $roleId, 'perm_key' => $key],
                ['can_write' => $write]
            );
        }
    }
}
```

`database/seeders/UserSeeder.php`
```php
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    // PENTING: password default versi Python (admin/admin2026, dst.) tampil
    // polos di README lama — di seeder ini tiap user diberi password acak yang
    // digenerate saat seeding, WAJIB dicatat/dibagikan lewat jalur aman (bukan
    // dokumen publik), dan flag must_change_pwd dipaksa true untuk semuanya.
    public function run(): void
    {
        $users = [
            ['username' => 'admin', 'nama_lengkap' => 'Administrator Portal', 'email' => 'admin@banksulteng.co.id', 'jabatan' => 'IT Admin', 'bagian' => 'Divisi Umum', 'role_id' => 1],
            ['username' => 'pimpinan', 'nama_lengkap' => 'Pemimpin Divisi Umum', 'email' => 'pimdiv.umum@banksulteng.co.id', 'jabatan' => 'Pemimpin Divisi', 'bagian' => 'Divisi Umum', 'role_id' => 2],
            ['username' => 'adol', 'nama_lengkap' => 'Pak Adol', 'jabatan' => 'Staf Umum & RT', 'bagian' => 'Umum & Rumah Tangga', 'role_id' => 3],
            ['username' => 'irma', 'nama_lengkap' => 'Kaka Irma', 'jabatan' => 'Staf Aset & Logistik', 'bagian' => 'Aset/Inventaris & Logistik', 'role_id' => 4],
            ['username' => 'pengadaan', 'nama_lengkap' => 'Staf Pengadaan', 'jabatan' => 'Staf Pengadaan', 'bagian' => 'Pengadaan & Pemeliharaan', 'role_id' => 5],
        ];

        foreach ($users as $u) {
            $plain = $u['username'] . '2026';
            User::updateOrCreate(
                ['username' => $u['username']],
                [...$u, 'password' => Hash::make($plain), 'must_change_pwd' => true, 'is_active' => true]
            );
            $this->command?->line("{$u['username']} -> password sementara: {$plain}");
        }
    }
}
```

`database/seeders/DatabaseSeeder.php` — versi awal, RefAkunSeeder ditambahkan di Step 9
```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            UserSeeder::class,
        ]);
    }
}

```

`bootstrap/app.php` — EDIT dari Step 5 — tambahkan alias middleware
```php
<?php

use App\Http\Middleware\CheckModulePermission;
use App\Http\Middleware\EnsureSuperadmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Alias dipakai di route: ->middleware('permission:aset_logistik,write')
        $middleware->alias([
            'permission' => CheckModulePermission::class,
            'superadmin' => EnsureSuperadmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
```

`routes/web.php` — EDIT dari Step 5 — tambahkan route login/logout/ganti-password
```php
<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/ganti-password-wajib', [LoginController::class, 'forceChangeForm'])->name('password.force-change');
    Route::post('/ganti-password-wajib', [LoginController::class, 'forceChange'])->name('password.force-change.submit');

    // Route dashboard, mesin modul, dan admin ditambahkan di Step 9 & 12.
});

```

```bash
php artisan migrate:fresh --seed
php artisan route:list
php artisan test
git add app/Models/User.php app/Models/Role.php app/Models/RolePermission.php app/Http/Controllers/Auth app/Http/Middleware app/Policies resources/views/auth resources/views/layouts resources/views/partials database/seeders bootstrap/app.php routes/web.php
git commit -m "feat(auth): implement authentication and RBAC"
git push -u origin feature/auth-rbac
```
PR → develop. Reviewer **Dirli**. Merge → Marsya pull develop.

---

# STEP 9 — Dirli: Domain Models + Module Engine

```bash
git checkout develop
git pull origin develop
git checkout -b feature/domain-models
```
**Model** (urut per grup):

*Grup Reference:*

`app/Models/RefAkun.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefAkun extends Model
{
    protected $table = 'ref_akun';

    public $timestamps = false;
    
    protected $fillable = [
        'nama_beban',
        'rekening_debet',
        'contoh_keterangan'
    ];
}
```

*Grup Umum/RT:*

`app/Models/UmKendaraan.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UmKendaraan extends Model
{
    protected $table = 'um_kendaraan';

    protected $fillable = [
        'no_polisi',
        'jenis',
        'merk',
        'tahun',
        'peruntukan',
        'driver',
        'status',
        'keterangan'
    ];
}
```

`app/Models/UmBiayaHarian.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UmBiayaHarian extends Model
{
    protected $table = 'um_biaya_harian';

    protected $fillable = [
        'tanggal',
        'kategori',
        'kendaraan',
        'nama_beban',
        'rekening_debet',
        'rekening_kredit',
        'uraian',
        'jumlah',
        'no_nota',
        'dokumen',
        'status',
        'maker_id',
        'checker_id',
        'approval_status',
        'approved_at',
        'catatan_approval',
        'dibuat_oleh'
    ];

    protected function casts(): array
    {
        return [
        'tanggal' => 'date',
        'jumlah' => 'decimal:2',
        'approved_at' => 'datetime'
        ];
    }

    public function maker()
    {
        return $this->belongsTo(User::class, 'maker_id');
    }

    public function checker()
    {
        return $this->belongsTo(User::class, 'checker_id');
    }
}
```

`app/Models/UmPermintaanCabang.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UmPermintaanCabang extends Model
{
    protected $table = 'um_permintaan_cabang';

    protected $fillable = [
        'no_permintaan',
        'tanggal',
        'unit_kerja',
        'jenis',
        'uraian',
        'jumlah',
        'satuan',
        'dokumen',
        'status',
        'maker_id',
        'checker_id',
        'approval_status',
        'approved_at',
        'petugas',
        'keterangan'
    ];

    protected function casts(): array
    {
        return [
        'tanggal' => 'date',
        'approved_at' => 'datetime'
        ];
    }

    public function maker()
    {
        return $this->belongsTo(User::class, 'maker_id');
    }

    public function checker()
    {
        return $this->belongsTo(User::class, 'checker_id');
    }
}
```

`app/Models/UmGenerateLog.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UmGenerateLog extends Model
{
    protected $table = 'um_generate_log';

    protected $fillable = [
        'periode_awal',
        'periode_akhir',
        'kategori',
        'jumlah_item',
        'total',
        'dibuat_oleh'
    ];

    protected function casts(): array
    {
        return [
        'periode_awal' => 'date',
        'periode_akhir' => 'date',
        'total' => 'decimal:2'
        ];
    }
}
```

*Grup Aset/Logistik:*

`app/Models/AsAset.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsAset extends Model
{
    protected $table = 'as_aset';

    protected $fillable = [
        'kode_aset',
        'nama_aset',
        'kategori',
        'lokasi',
        'tanggal_perolehan',
        'nilai_perolehan',
        'umur_ekonomis',
        'kondisi',
        'penanggung_jawab',
        'keterangan'
    ];

    protected function casts(): array
    {
        return [
        'tanggal_perolehan' => 'date',
        'nilai_perolehan' => 'decimal:2'
        ];
    }

    public function amortisasi()
    {
        return $this->hasMany(AsAmortisasi::class, 'nama_biaya', 'nama_aset');
    }
}
```

`app/Models/AsAmortisasi.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsAmortisasi extends Model
{
    protected $table = 'as_amortisasi';

    protected $fillable = [
        'nama_biaya',
        'nilai_perolehan',
        'tanggal_mulai',
        'umur_bulan',
        'nilai_per_bulan',
        'akumulasi',
        'nilai_buku',
        'keterangan'
    ];

    protected function casts(): array
    {
        return [
        'tanggal_mulai' => 'date',
        'nilai_perolehan' => 'decimal:2',
        'nilai_per_bulan' => 'decimal:2',
        'akumulasi' => 'decimal:2',
        'nilai_buku' => 'decimal:2'
        ];
    }

    public function factAmortisasi()
    {
        return $this->hasMany(FactAmortisasiAset::class, 'as_amortisasi_id');
    }
}
```

`app/Models/AsPks.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsPks extends Model
{
    protected $table = 'as_pks';

    protected $fillable = [
        'no_pks',
        'judul',
        'vendor',
        'div_owner',
        'tanggal_mulai',
        'jatuh_tempo',
        'nilai',
        'dokumen',
        'status',
        'memo_dibuat',
        'maker_id',
        'checker_id',
        'approval_status',
        'approved_at',
        'keterangan'
    ];

    protected function casts(): array
    {
        return [
        'tanggal_mulai' => 'date',
        'jatuh_tempo' => 'date',
        'nilai' => 'decimal:2',
        'memo_dibuat' => 'boolean',
        'approved_at' => 'datetime'
        ];
    }

    public function maker()
    {
        return $this->belongsTo(User::class, 'maker_id');
    }

    public function checker()
    {
        return $this->belongsTo(User::class, 'checker_id');
    }
}
```

`app/Models/AsMemoSewaCabang.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsMemoSewaCabang extends Model
{
    protected $table = 'as_memo_sewa_cabang';

    protected $fillable = [
        'no_memo',
        'cabang',
        'jenis',
        'tanggal',
        'nilai',
        'dokumen',
        'status_persetujuan',
        'maker_id',
        'checker_id',
        'approved_at',
        'keterangan'
    ];

    protected function casts(): array
    {
        return [
        'tanggal' => 'date',
        'nilai' => 'decimal:2',
        'approved_at' => 'datetime'
        ];
    }

    public function maker()
    {
        return $this->belongsTo(User::class, 'maker_id');
    }

    public function checker()
    {
        return $this->belongsTo(User::class, 'checker_id');
    }
}
```

`app/Models/AsTemuan.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsTemuan extends Model
{
    protected $table = 'as_temuan';

    protected $fillable = [
        'no_temuan',
        'sumber',
        'uraian',
        'tanggal_temuan',
        'batas_tindak_lanjut',
        'status',
        'penanggung_jawab',
        'tindak_lanjut',
        'dokumen'
    ];

    protected function casts(): array
    {
        return [
        'tanggal_temuan' => 'date',
        'batas_tindak_lanjut' => 'date'
        ];
    }
}
```

*Grup Pengadaan:*

`app/Models/PgMemoInternal.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PgMemoInternal extends Model
{
    protected $table = 'pg_memo_internal';

    protected $fillable = [
        'no_memo',
        'dari_unit',
        'ke_unit',
        'perihal',
        'tanggal',
        'jenis',
        'dokumen',
        'status',
        'keterangan'
    ];

    protected function casts(): array
    {
        return [
        'tanggal' => 'date'
        ];
    }
}
```

`app/Models/PgPenawaran.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PgPenawaran extends Model
{
    protected $table = 'pg_penawaran';

    protected $fillable = [
        'no_penawaran',
        'vendor',
        'barang_jasa',
        'nilai',
        'tanggal',
        'dokumen',
        'status',
        'keterangan'
    ];

    protected function casts(): array
    {
        return [
        'tanggal' => 'date',
        'nilai' => 'decimal:2'
        ];
    }
}
```

`app/Models/PgNegosiasi.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PgNegosiasi extends Model
{
    protected $table = 'pg_negosiasi';

    protected $fillable = [
        'no_berita_acara',
        'vendor',
        'barang_jasa',
        'nilai_awal',
        'nilai_nego',
        'tanggal',
        'hasil',
        'dokumen'
    ];

    protected function casts(): array
    {
        return [
        'tanggal' => 'date',
        'nilai_awal' => 'decimal:2',
        'nilai_nego' => 'decimal:2'
        ];
    }
}
```

`app/Models/PgDraftDokumen.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PgDraftDokumen extends Model
{
    protected $table = 'pg_draft_dokumen';

    protected $fillable = [
        'jenis',
        'no_dokumen',
        'judul',
        'vendor',
        'tanggal',
        'status',
        'file',
        'keterangan'
    ];

    protected function casts(): array
    {
        return [
        'tanggal' => 'date'
        ];
    }
}
```

`app/Models/PgSpk.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PgSpk extends Model
{
    protected $table = 'pg_spk';

    protected $fillable = [
        'no_spk',
        'vendor',
        'pekerjaan',
        'nilai',
        'tanggal_terbit',
        'tanggal_selesai',
        'dokumen',
        'status',
        'maker_id',
        'checker_id',
        'approval_status',
        'approved_at',
        'keterangan'
    ];

    protected function casts(): array
    {
        return [
        'tanggal_terbit' => 'date',
        'tanggal_selesai' => 'date',
        'nilai' => 'decimal:2',
        'approved_at' => 'datetime'
        ];
    }

    public function maker()
    {
        return $this->belongsTo(User::class, 'maker_id');
    }

    public function checker()
    {
        return $this->belongsTo(User::class, 'checker_id');
    }
}
```

`app/Models/PgReminder.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PgReminder extends Model
{
    protected $table = 'pg_reminder';

    protected $fillable = [
        'judul',
        'kategori',
        'tanggal_jatuh_tempo',
        'status',
        'catatan'
    ];

    protected function casts(): array
    {
        return [
        'tanggal_jatuh_tempo' => 'date'
        ];
    }
}
```

*Grup Arsip:*

`app/Models/SrSuratMasuk.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SrSuratMasuk extends Model
{
    protected $table = 'sr_surat_masuk';

    protected $fillable = [
        'nomor_agenda',
        'no_surat',
        'pengirim',
        'perihal',
        'tanggal',
        'penerima',
        'lokasi_arsip',
        'lampiran',
        'dibuat_oleh'
    ];

    protected function casts(): array
    {
        return [
        'tanggal' => 'date'
        ];
    }
}
```

`app/Models/SrSuratKeluar.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SrSuratKeluar extends Model
{
    protected $table = 'sr_surat_keluar';

    protected $fillable = [
        'nomor_agenda',
        'no_surat',
        'pengirim',
        'perihal',
        'tanggal',
        'penerima',
        'lokasi_arsip',
        'lampiran',
        'dibuat_oleh'
    ];

    protected function casts(): array
    {
        return [
        'tanggal' => 'date'
        ];
    }
}
```

`app/Models/SrMemoMasuk.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SrMemoMasuk extends Model
{
    protected $table = 'sr_memo_masuk';

    protected $fillable = [
        'nomor_agenda',
        'no_surat',
        'pengirim',
        'perihal',
        'tanggal',
        'penerima',
        'lokasi_arsip',
        'lampiran',
        'dibuat_oleh'
    ];

    protected function casts(): array
    {
        return [
        'tanggal' => 'date'
        ];
    }
}
```

`app/Models/SrMemoKeluar.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SrMemoKeluar extends Model
{
    protected $table = 'sr_memo_keluar';

    protected $fillable = [
        'nomor_agenda',
        'no_surat',
        'pengirim',
        'perihal',
        'tanggal',
        'penerima',
        'lokasi_arsip',
        'lampiran',
        'dibuat_oleh'
    ];

    protected function casts(): array
    {
        return [
        'tanggal' => 'date'
        ];
    }
}
```

**`config/modules.php`** — dibangun bertahap per grup di atas (potongan baris sesuai `CATATAN-ISI-KODE-PER-STEP.md`). Karena semua grup memang selesai di step ini, langsung tempel utuh:

`config/modules.php`
```php
<?php

// Konfigurasi mesin CRUD generik -- diekstrak 1:1 dari dictionary RES
// di portum.py (versi Python) supaya label, tipe field, opsi select,
// dan field mana yang wajib tetap identik dengan aplikasi lama.
// Dipakai oleh App\Http\Controllers\ModuleController.

return [
    'kendaraan' => [
        'model' => 'App\\Models\\UmKendaraan',
        'perm' => 'umum_rt',
        'modul' => 'Umum & Rumah Tangga',
        'judul' => 'Kendaraan & Driver',
        'maker_checker' => false,
        'fields' => [
            'no_polisi' => [
                'label' => 'No. Polisi',
                'type' => 'text',
                'list' => true,
                'req' => true,
            ],
            'jenis' => [
                'label' => 'Jenis',
                'type' => 'text',
                'list' => true,
                'req' => false,
            ],
            'merk' => [
                'label' => 'Merk',
                'type' => 'text',
                'list' => true,
                'req' => false,
            ],
            'tahun' => [
                'label' => 'Tahun',
                'type' => 'text',
                'list' => false,
                'req' => false,
            ],
            'peruntukan' => [
                'label' => 'Peruntukan',
                'type' => 'text',
                'list' => true,
                'req' => false,
            ],
            'driver' => [
                'label' => 'Driver',
                'type' => 'text',
                'list' => true,
                'req' => false,
            ],
            'status' => [
                'label' => 'Status',
                'type' => 'select',
                'list' => true,
                'fmt' => 'badge',
                'req' => false,
                'opts' => ['Aktif', 'Servis', 'Nonaktif'],
            ],
            'keterangan' => [
                'label' => 'Keterangan',
                'type' => 'textarea',
                'list' => false,
                'req' => false,
            ],
        ],
    ],
    'biaya_harian' => [
        'model' => 'App\\Models\\UmBiayaHarian',
        'perm' => 'umum_rt',
        'modul' => 'Umum & Rumah Tangga',
        'judul' => 'Biaya BBM / Perawatan / Rumah Tangga',
        'maker_checker' => true,
        'fields' => [
            'tanggal' => [
                'label' => 'Tanggal',
                'type' => 'date',
                'list' => true,
                'fmt' => 'date',
                'req' => true,
            ],
            'kategori' => [
                'label' => 'Kategori',
                'type' => 'select',
                'list' => true,
                'fmt' => 'badge',
                'req' => true,
                'opts' => ['BBM', 'Perawatan', 'Rumah Tangga'],
            ],
            'kendaraan' => [
                'label' => 'Kendaraan',
                'type' => 'text',
                'list' => true,
                'req' => false,
            ],
            'nama_beban' => [
                'label' => 'Nama Beban (Akun)',
                'type' => 'select',
                'list' => false,
                'req' => false,
                'opts_sql' => 'SELECT nama_beban AS v, nama_beban || " — " || rekening_debet AS t FROM ref_akun ORDER BY nama_beban',
            ],
            'rekening_debet' => [
                'label' => 'Rekening Debet',
                'type' => 'text',
                'list' => false,
                'req' => false,
            ],
            'rekening_kredit' => [
                'label' => 'Rekening Kredit',
                'type' => 'text',
                'list' => false,
                'req' => false,
            ],
            'uraian' => [
                'label' => 'Uraian',
                'type' => 'textarea',
                'list' => true,
                'req' => false,
            ],
            'jumlah' => [
                'label' => 'Jumlah',
                'type' => 'money',
                'list' => true,
                'fmt' => 'money',
                'req' => false,
            ],
            'no_nota' => [
                'label' => 'No. Nota',
                'type' => 'text',
                'list' => false,
                'req' => false,
            ],
            'status' => [
                'label' => 'Status',
                'type' => 'select',
                'list' => true,
                'fmt' => 'badge',
                'req' => false,
                'opts' => ['Draft', 'Diajukan', 'Dibukukan'],
            ],
            'dibuat_oleh' => [
                'label' => 'Dibuat Oleh',
                'type' => 'auto_user',
                'list' => false,
                'req' => false,
            ],
            'dokumen' => [
                'label' => 'Dokumen (Lampiran)',
                'type' => 'file',
                'list' => true,
                'req' => false,
            ],
        ],
    ],
    'permintaan_cabang' => [
        'model' => 'App\\Models\\UmPermintaanCabang',
        'perm' => 'umum_rt',
        'modul' => 'Umum & Rumah Tangga',
        'judul' => 'Permintaan Cabang (ATK/Inventaris)',
        'maker_checker' => true,
        'fields' => [
            'no_permintaan' => [
                'label' => 'No. Permintaan',
                'type' => 'text',
                'list' => true,
                'req' => false,
            ],
            'tanggal' => [
                'label' => 'Tanggal',
                'type' => 'date',
                'list' => true,
                'fmt' => 'date',
                'req' => true,
            ],
            'unit_kerja' => [
                'label' => 'Unit Kerja / Cabang',
                'type' => 'text',
                'list' => true,
                'req' => false,
            ],
            'jenis' => [
                'label' => 'Jenis',
                'type' => 'select',
                'list' => true,
                'fmt' => 'badge',
                'req' => false,
                'opts' => ['ATK', 'Inventaris', 'Perbaikan', 'Lainnya'],
            ],
            'uraian' => [
                'label' => 'Uraian',
                'type' => 'textarea',
                'list' => true,
                'req' => false,
            ],
            'jumlah' => [
                'label' => 'Jumlah',
                'type' => 'text',
                'list' => false,
                'req' => false,
            ],
            'satuan' => [
                'label' => 'Satuan',
                'type' => 'text',
                'list' => false,
                'req' => false,
            ],
            'status' => [
                'label' => 'Status',
                'type' => 'select',
                'list' => true,
                'fmt' => 'badge',
                'req' => false,
                'opts' => ['Diajukan', 'Disetujui', 'Diproses', 'Selesai', 'Ditolak'],
            ],
            'petugas' => [
                'label' => 'Petugas',
                'type' => 'text',
                'list' => false,
                'req' => false,
            ],
            'keterangan' => [
                'label' => 'Keterangan',
                'type' => 'textarea',
                'list' => false,
                'req' => false,
            ],
            'dokumen' => [
                'label' => 'Dokumen (Lampiran)',
                'type' => 'file',
                'list' => true,
                'req' => false,
            ],
        ],
    ],
    'invoice_sewa' => [
        'model' => 'App\\Models\\AsInvoiceSewa',
        'perm' => 'aset_logistik',
        'modul' => 'Aset & Logistik',
        'judul' => 'Tagihan / Invoice Sewa',
        'maker_checker' => false,
        'fields' => [
            'no_invoice' => [
                'label' => 'No. Invoice',
                'type' => 'text',
                'list' => true,
                'req' => false,
            ],
            'vendor' => [
                'label' => 'Vendor',
                'type' => 'text',
                'list' => true,
                'req' => true,
            ],
            'jenis_sewa' => [
                'label' => 'Jenis Sewa',
                'type' => 'select',
                'list' => true,
                'req' => false,
                'opts' => ['Aplikasi', 'Mesin ATM', 'Brankas', 'Perangkat IT', 'Software', 'Hardware'],
            ],
            'periode_mulai' => [
                'label' => 'Periode Mulai',
                'type' => 'date',
                'list' => false,
                'req' => false,
            ],
            'periode_selesai' => [
                'label' => 'Periode Selesai',
                'type' => 'date',
                'list' => false,
                'req' => false,
            ],
            'nilai' => [
                'label' => 'Nilai',
                'type' => 'money',
                'list' => true,
                'fmt' => 'money',
                'req' => false,
            ],
            'jatuh_tempo' => [
                'label' => 'Jatuh Tempo',
                'type' => 'date',
                'list' => true,
                'fmt' => 'date',
                'req' => false,
            ],
            'status' => [
                'label' => 'Status',
                'type' => 'select',
                'list' => true,
                'fmt' => 'badge',
                'req' => false,
                'opts' => ['Belum Bayar', 'Proses', 'Lunas'],
            ],
            'keterangan' => [
                'label' => 'Keterangan',
                'type' => 'textarea',
                'list' => false,
                'req' => false,
            ],
            'dokumen' => [
                'label' => 'Dokumen (Lampiran)',
                'type' => 'file',
                'list' => true,
                'req' => false,
            ],
        ],
    ],
    'aset' => [
        'model' => 'App\\Models\\AsAset',
        'perm' => 'aset_logistik',
        'modul' => 'Aset & Logistik',
        'judul' => 'Inventarisasi Aset',
        'maker_checker' => false,
        'fields' => [
            'kode_aset' => [
                'label' => 'Kode Aset',
                'type' => 'text',
                'list' => true,
                'req' => false,
            ],
            'nama_aset' => [
                'label' => 'Nama Aset',
                'type' => 'text',
                'list' => true,
                'req' => true,
            ],
            'kategori' => [
                'label' => 'Kategori',
                'type' => 'text',
                'list' => true,
                'req' => true,
            ],
            'lokasi' => [
                'label' => 'Lokasi',
                'type' => 'text',
                'list' => true,
                'req' => false,
            ],
            'tanggal_perolehan' => [
                'label' => 'Tgl Perolehan',
                'type' => 'date',
                'list' => false,
                'fmt' => 'date',
                'req' => false,
            ],
            'nilai_perolehan' => [
                'label' => 'Nilai Perolehan',
                'type' => 'money',
                'list' => true,
                'fmt' => 'money',
                'req' => false,
            ],
            'umur_ekonomis' => [
                'label' => 'Umur Ekonomis (bln)',
                'type' => 'number',
                'list' => false,
                'req' => false,
            ],
            'kondisi' => [
                'label' => 'Kondisi',
                'type' => 'select',
                'list' => true,
                'fmt' => 'badge',
                'req' => false,
                'opts' => ['Baik', 'Rusak Ringan', 'Rusak Berat'],
            ],
            'penanggung_jawab' => [
                'label' => 'Penanggung Jawab',
                'type' => 'text',
                'list' => false,
                'req' => false,
            ],
            'keterangan' => [
                'label' => 'Keterangan',
                'type' => 'textarea',
                'list' => false,
                'req' => false,
            ],
        ],
    ],
    'amortisasi' => [
        'model' => 'App\\Models\\AsAmortisasi',
        'perm' => 'aset_logistik',
        'modul' => 'Aset & Logistik',
        'judul' => 'Amortisasi Biaya',
        'maker_checker' => false,
        'fields' => [
            'nama_biaya' => [
                'label' => 'Nama Biaya',
                'type' => 'text',
                'list' => true,
                'req' => true,
            ],
            'nilai_perolehan' => [
                'label' => 'Nilai Perolehan',
                'type' => 'money',
                'list' => true,
                'fmt' => 'money',
                'req' => false,
            ],
            'tanggal_mulai' => [
                'label' => 'Tgl Mulai',
                'type' => 'date',
                'list' => false,
                'fmt' => 'date',
                'req' => false,
            ],
            'umur_bulan' => [
                'label' => 'Umur (bulan)',
                'type' => 'number',
                'list' => true,
                'req' => false,
            ],
            'nilai_per_bulan' => [
                'label' => 'Nilai / Bulan',
                'type' => 'money',
                'list' => true,
                'fmt' => 'money',
                'req' => false,
                'help' => 'Dihitung otomatis bila dikosongkan (nilai ÷ umur)',
            ],
            'akumulasi' => [
                'label' => 'Akumulasi',
                'type' => 'money',
                'list' => false,
                'fmt' => 'money',
                'req' => false,
            ],
            'nilai_buku' => [
                'label' => 'Nilai Buku',
                'type' => 'money',
                'list' => true,
                'fmt' => 'money',
                'req' => false,
            ],
            'keterangan' => [
                'label' => 'Keterangan',
                'type' => 'textarea',
                'list' => false,
                'req' => false,
            ],
        ],
    ],
    'pks' => [
        'model' => 'App\\Models\\AsPks',
        'perm' => 'aset_logistik',
        'modul' => 'Aset & Logistik',
        'judul' => 'PKS & Jatuh Tempo',
        'maker_checker' => true,
        'fields' => [
            'no_pks' => [
                'label' => 'No. PKS',
                'type' => 'text',
                'list' => true,
                'req' => false,
            ],
            'judul' => [
                'label' => 'Judul PKS',
                'type' => 'text',
                'list' => true,
                'req' => true,
            ],
            'vendor' => [
                'label' => 'Vendor / Pihak',
                'type' => 'text',
                'list' => true,
                'req' => false,
            ],
            'div_owner' => [
                'label' => 'Divisi Owner',
                'type' => 'text',
                'list' => true,
                'req' => false,
            ],
            'tanggal_mulai' => [
                'label' => 'Tgl Mulai',
                'type' => 'date',
                'list' => false,
                'fmt' => 'date',
                'req' => false,
            ],
            'jatuh_tempo' => [
                'label' => 'Jatuh Tempo',
                'type' => 'date',
                'list' => true,
                'fmt' => 'date',
                'req' => false,
            ],
            'nilai' => [
                'label' => 'Nilai',
                'type' => 'money',
                'list' => false,
                'fmt' => 'money',
                'req' => false,
            ],
            'status' => [
                'label' => 'Status',
                'type' => 'select',
                'list' => true,
                'fmt' => 'badge',
                'req' => false,
                'opts' => ['Aktif', 'Akan Jatuh Tempo', 'Berakhir', 'Diperpanjang'],
            ],
            'memo_dibuat' => [
                'label' => 'Memo ke Div Owner Dibuat',
                'type' => 'checkbox',
                'list' => true,
                'req' => false,
            ],
            'keterangan' => [
                'label' => 'Keterangan',
                'type' => 'textarea',
                'list' => false,
                'req' => false,
            ],
            'dokumen' => [
                'label' => 'Dokumen (Lampiran)',
                'type' => 'file',
                'list' => true,
                'req' => false,
            ],
        ],
    ],
    'memo_sewa_cabang' => [
        'model' => 'App\\Models\\AsMemoSewaCabang',
        'perm' => 'aset_logistik',
        'modul' => 'Aset & Logistik',
        'judul' => 'Memo Sewa Cabang',
        'maker_checker' => true,
        'fields' => [
            'no_memo' => [
                'label' => 'No. Memo',
                'type' => 'text',
                'list' => true,
                'req' => false,
            ],
            'cabang' => [
                'label' => 'Cabang',
                'type' => 'text',
                'list' => true,
                'req' => true,
            ],
            'jenis' => [
                'label' => 'Jenis Sewa',
                'type' => 'select',
                'list' => true,
                'req' => false,
                'opts' => ['Galeri ATM', 'Gedung Kantor', 'Gudang', 'Rumah Dinas'],
            ],
            'tanggal' => [
                'label' => 'Tanggal',
                'type' => 'date',
                'list' => true,
                'fmt' => 'date',
                'req' => false,
            ],
            'nilai' => [
                'label' => 'Nilai',
                'type' => 'money',
                'list' => true,
                'fmt' => 'money',
                'req' => false,
            ],
            'status_persetujuan' => [
                'label' => 'Status Persetujuan',
                'type' => 'select',
                'list' => true,
                'fmt' => 'badge',
                'req' => false,
                'opts' => ['Diajukan', 'Persetujuan Prinsip', 'Disetujui', 'Ditolak'],
            ],
            'keterangan' => [
                'label' => 'Keterangan',
                'type' => 'textarea',
                'list' => false,
                'req' => false,
            ],
            'dokumen' => [
                'label' => 'Dokumen (Lampiran)',
                'type' => 'file',
                'list' => true,
                'req' => false,
            ],
        ],
    ],
    'temuan' => [
        'model' => 'App\\Models\\AsTemuan',
        'perm' => 'aset_logistik',
        'modul' => 'Aset & Logistik',
        'judul' => 'Tindak Lanjut Temuan',
        'maker_checker' => false,
        'fields' => [
            'no_temuan' => [
                'label' => 'No. Temuan',
                'type' => 'text',
                'list' => true,
                'req' => false,
            ],
            'sumber' => [
                'label' => 'Sumber',
                'type' => 'select',
                'list' => true,
                'req' => false,
                'opts' => ['Audit Internal', 'OJK', 'KAP', 'Lainnya'],
            ],
            'uraian' => [
                'label' => 'Uraian Temuan',
                'type' => 'textarea',
                'list' => true,
                'req' => true,
            ],
            'tanggal_temuan' => [
                'label' => 'Tgl Temuan',
                'type' => 'date',
                'list' => false,
                'fmt' => 'date',
                'req' => false,
            ],
            'batas_tindak_lanjut' => [
                'label' => 'Batas Tindak Lanjut',
                'type' => 'date',
                'list' => true,
                'fmt' => 'date',
                'req' => false,
            ],
            'status' => [
                'label' => 'Status',
                'type' => 'select',
                'list' => true,
                'fmt' => 'badge',
                'req' => false,
                'opts' => ['Open', 'Proses', 'Selesai'],
            ],
            'penanggung_jawab' => [
                'label' => 'Penanggung Jawab',
                'type' => 'text',
                'list' => false,
                'req' => false,
            ],
            'tindak_lanjut' => [
                'label' => 'Tindak Lanjut',
                'type' => 'textarea',
                'list' => false,
                'req' => false,
            ],
            'dokumen' => [
                'label' => 'Dokumen (Lampiran)',
                'type' => 'file',
                'list' => true,
                'req' => false,
            ],
        ],
    ],
    'memo_internal' => [
        'model' => 'App\\Models\\PgMemoInternal',
        'perm' => 'pengadaan',
        'modul' => 'Pengadaan & Pemeliharaan',
        'judul' => 'Memo Internal',
        'maker_checker' => false,
        'fields' => [
            'no_memo' => [
                'label' => 'No. Memo',
                'type' => 'text',
                'list' => true,
                'req' => false,
            ],
            'dari_unit' => [
                'label' => 'Dari Unit',
                'type' => 'text',
                'list' => true,
                'req' => false,
            ],
            'ke_unit' => [
                'label' => 'Ke Unit',
                'type' => 'text',
                'list' => true,
                'req' => false,
            ],
            'perihal' => [
                'label' => 'Perihal',
                'type' => 'text',
                'list' => true,
                'req' => true,
            ],
            'tanggal' => [
                'label' => 'Tanggal',
                'type' => 'date',
                'list' => true,
                'fmt' => 'date',
                'req' => false,
            ],
            'jenis' => [
                'label' => 'Jenis',
                'type' => 'select',
                'list' => false,
                'req' => false,
                'opts' => ['Divisi', 'Cabang', 'Cabang Pembantu', 'Kantor Kas'],
            ],
            'status' => [
                'label' => 'Status',
                'type' => 'select',
                'list' => true,
                'fmt' => 'badge',
                'req' => false,
                'opts' => ['Masuk', 'Diproses', 'Selesai'],
            ],
            'keterangan' => [
                'label' => 'Keterangan',
                'type' => 'textarea',
                'list' => false,
                'req' => false,
            ],
            'dokumen' => [
                'label' => 'Dokumen (Lampiran)',
                'type' => 'file',
                'list' => true,
                'req' => false,
            ],
        ],
    ],
    'penawaran' => [
        'model' => 'App\\Models\\PgPenawaran',
        'perm' => 'pengadaan',
        'modul' => 'Pengadaan & Pemeliharaan',
        'judul' => 'Penawaran Vendor',
        'maker_checker' => false,
        'fields' => [
            'no_penawaran' => [
                'label' => 'No. Penawaran',
                'type' => 'text',
                'list' => true,
                'req' => false,
            ],
            'vendor' => [
                'label' => 'Vendor',
                'type' => 'text',
                'list' => true,
                'req' => true,
            ],
            'barang_jasa' => [
                'label' => 'Barang / Jasa',
                'type' => 'textarea',
                'list' => true,
                'req' => false,
            ],
            'nilai' => [
                'label' => 'Nilai Penawaran',
                'type' => 'money',
                'list' => true,
                'fmt' => 'money',
                'req' => false,
            ],
            'tanggal' => [
                'label' => 'Tanggal',
                'type' => 'date',
                'list' => true,
                'fmt' => 'date',
                'req' => false,
            ],
            'status' => [
                'label' => 'Status',
                'type' => 'select',
                'list' => true,
                'fmt' => 'badge',
                'req' => false,
                'opts' => ['Diterima', 'Evaluasi', 'Negosiasi', 'Ditolak'],
            ],
            'keterangan' => [
                'label' => 'Keterangan',
                'type' => 'textarea',
                'list' => false,
                'req' => false,
            ],
            'dokumen' => [
                'label' => 'Dokumen (Lampiran)',
                'type' => 'file',
                'list' => true,
                'req' => false,
            ],
        ],
    ],
    'negosiasi' => [
        'model' => 'App\\Models\\PgNegosiasi',
        'perm' => 'pengadaan',
        'modul' => 'Pengadaan & Pemeliharaan',
        'judul' => 'Negosiasi (Berita Acara)',
        'maker_checker' => false,
        'fields' => [
            'no_berita_acara' => [
                'label' => 'No. Berita Acara',
                'type' => 'text',
                'list' => true,
                'req' => false,
            ],
            'vendor' => [
                'label' => 'Vendor',
                'type' => 'text',
                'list' => true,
                'req' => true,
            ],
            'barang_jasa' => [
                'label' => 'Barang / Jasa',
                'type' => 'textarea',
                'list' => true,
                'req' => false,
            ],
            'nilai_awal' => [
                'label' => 'Nilai Awal',
                'type' => 'money',
                'list' => true,
                'fmt' => 'money',
                'req' => false,
            ],
            'nilai_nego' => [
                'label' => 'Nilai Nego',
                'type' => 'money',
                'list' => true,
                'fmt' => 'money',
                'req' => false,
            ],
            'tanggal' => [
                'label' => 'Tanggal',
                'type' => 'date',
                'list' => true,
                'fmt' => 'date',
                'req' => false,
            ],
            'hasil' => [
                'label' => 'Hasil',
                'type' => 'textarea',
                'list' => false,
                'req' => false,
            ],
            'dokumen' => [
                'label' => 'Dokumen (Lampiran)',
                'type' => 'file',
                'list' => true,
                'req' => false,
            ],
        ],
    ],
    'draft_dokumen' => [
        'model' => 'App\\Models\\PgDraftDokumen',
        'perm' => 'pengadaan',
        'modul' => 'Pengadaan & Pemeliharaan',
        'judul' => 'Draft Dokumen (PKS/NDA/SPK)',
        'maker_checker' => false,
        'fields' => [
            'jenis' => [
                'label' => 'Jenis',
                'type' => 'select',
                'list' => true,
                'fmt' => 'badge',
                'req' => false,
                'opts' => ['PKS', 'NDA', 'SPK', 'Lainnya'],
            ],
            'no_dokumen' => [
                'label' => 'No. Dokumen',
                'type' => 'text',
                'list' => true,
                'req' => false,
            ],
            'judul' => [
                'label' => 'Judul',
                'type' => 'text',
                'list' => true,
                'req' => true,
            ],
            'vendor' => [
                'label' => 'Vendor',
                'type' => 'text',
                'list' => true,
                'req' => false,
            ],
            'tanggal' => [
                'label' => 'Tanggal',
                'type' => 'date',
                'list' => true,
                'fmt' => 'date',
                'req' => false,
            ],
            'status' => [
                'label' => 'Status',
                'type' => 'select',
                'list' => true,
                'fmt' => 'badge',
                'req' => false,
                'opts' => ['Draft', 'Review', 'Final'],
            ],
            'keterangan' => [
                'label' => 'Keterangan',
                'type' => 'textarea',
                'list' => false,
                'req' => false,
            ],
            'file' => [
                'label' => 'Dokumen (Lampiran)',
                'type' => 'file',
                'list' => true,
                'req' => false,
            ],
        ],
    ],
    'spk' => [
        'model' => 'App\\Models\\PgSpk',
        'perm' => 'pengadaan',
        'modul' => 'Pengadaan & Pemeliharaan',
        'judul' => 'SPK (Surat Perintah Kerja)',
        'maker_checker' => true,
        'fields' => [
            'no_spk' => [
                'label' => 'No. SPK',
                'type' => 'text',
                'list' => true,
                'req' => false,
            ],
            'vendor' => [
                'label' => 'Vendor',
                'type' => 'text',
                'list' => true,
                'req' => true,
            ],
            'pekerjaan' => [
                'label' => 'Pekerjaan',
                'type' => 'textarea',
                'list' => true,
                'req' => false,
            ],
            'nilai' => [
                'label' => 'Nilai',
                'type' => 'money',
                'list' => true,
                'fmt' => 'money',
                'req' => false,
            ],
            'tanggal_terbit' => [
                'label' => 'Tgl Terbit',
                'type' => 'date',
                'list' => true,
                'fmt' => 'date',
                'req' => false,
            ],
            'tanggal_selesai' => [
                'label' => 'Tgl Selesai',
                'type' => 'date',
                'list' => false,
                'fmt' => 'date',
                'req' => false,
            ],
            'status' => [
                'label' => 'Status',
                'type' => 'select',
                'list' => true,
                'fmt' => 'badge',
                'req' => false,
                'opts' => ['Berjalan', 'Selesai', 'Batal'],
            ],
            'keterangan' => [
                'label' => 'Keterangan',
                'type' => 'textarea',
                'list' => false,
                'req' => false,
            ],
            'dokumen' => [
                'label' => 'Dokumen (Lampiran)',
                'type' => 'file',
                'list' => true,
                'req' => false,
            ],
        ],
    ],
    'reminder' => [
        'model' => 'App\\Models\\PgReminder',
        'perm' => 'pengadaan',
        'modul' => 'Pengadaan & Pemeliharaan',
        'judul' => 'Reminder Schedule',
        'maker_checker' => false,
        'fields' => [
            'judul' => [
                'label' => 'Judul',
                'type' => 'text',
                'list' => true,
                'req' => true,
            ],
            'kategori' => [
                'label' => 'Kategori',
                'type' => 'select',
                'list' => true,
                'req' => true,
                'opts' => ['PKS', 'Sewa', 'SPK', 'Pemeliharaan', 'Lainnya'],
            ],
            'tanggal_jatuh_tempo' => [
                'label' => 'Tgl Jatuh Tempo',
                'type' => 'date',
                'list' => true,
                'fmt' => 'date',
                'req' => true,
            ],
            'status' => [
                'label' => 'Status',
                'type' => 'select',
                'list' => true,
                'fmt' => 'badge',
                'req' => false,
                'opts' => ['Aktif', 'Selesai'],
            ],
            'catatan' => [
                'label' => 'Catatan',
                'type' => 'textarea',
                'list' => false,
                'req' => false,
            ],
        ],
    ],
    'ref_akun' => [
        'model' => 'App\\Models\\RefAkun',
        'perm' => 'ref_akun',
        'modul' => 'Pengaturan',
        'judul' => 'Referensi Akun Biaya',
        'maker_checker' => false,
        'fields' => [
            'nama_beban' => [
                'label' => 'Nama Beban',
                'type' => 'text',
                'list' => true,
                'req' => true,
            ],
            'rekening_debet' => [
                'label' => 'Rekening Debet',
                'type' => 'text',
                'list' => true,
                'req' => false,
            ],
            'contoh_keterangan' => [
                'label' => 'Contoh Keterangan',
                'type' => 'textarea',
                'list' => true,
                'req' => false,
            ],
        ],
    ],
    'surat_masuk' => [
        'model' => 'App\\Models\\SrSuratMasuk',
        'perm' => 'risalah',
        'modul' => 'Arsip Surat & Memo',
        'judul' => 'Surat Masuk',
        'maker_checker' => false,
        'fields' => [
            'nomor_agenda' => [
                'label' => 'Nomor Agenda',
                'type' => 'number',
                'list' => true,
                'req' => true,
                'help' => 'Rekomendasi otomatis (+10 setiap hari baru, +1 di hari yang sama) — boleh diubah / disisip nomor untuk tanggal sebelumnya.',
            ],
            'no_surat' => [
                'label' => 'No. Surat',
                'type' => 'text',
                'list' => true,
                'req' => false,
            ],
            'pengirim' => [
                'label' => 'Pengirim',
                'type' => 'text',
                'list' => true,
                'req' => false,
            ],
            'perihal' => [
                'label' => 'Perihal',
                'type' => 'text',
                'list' => true,
                'req' => true,
            ],
            'tanggal' => [
                'label' => 'Tanggal',
                'type' => 'date',
                'list' => true,
                'fmt' => 'date',
                'req' => true,
                'default' => 'today',
            ],
            'penerima' => [
                'label' => 'Penerima',
                'type' => 'text',
                'list' => true,
                'req' => false,
            ],
            'lokasi_arsip' => [
                'label' => 'Lokasi Arsip',
                'type' => 'text',
                'list' => true,
                'req' => false,
                'help' => 'cth: Lemari A / Ordner 3 / Map Merah',
            ],
            'lampiran' => [
                'label' => 'Lampiran',
                'type' => 'file',
                'list' => true,
                'req' => false,
            ],
            'dibuat_oleh' => [
                'label' => 'Dibuat Oleh',
                'type' => 'auto_user',
                'list' => false,
                'req' => false,
            ],
        ],
    ],
    'surat_keluar' => [
        'model' => 'App\\Models\\SrSuratKeluar',
        'perm' => 'risalah',
        'modul' => 'Arsip Surat & Memo',
        'judul' => 'Surat Keluar',
        'maker_checker' => false,
        'fields' => [
            'nomor_agenda' => [
                'label' => 'Nomor Agenda',
                'type' => 'number',
                'list' => true,
                'req' => true,
                'help' => 'Rekomendasi otomatis (+10 setiap hari baru, +1 di hari yang sama) — boleh diubah / disisip nomor untuk tanggal sebelumnya.',
            ],
            'no_surat' => [
                'label' => 'No. Surat',
                'type' => 'text',
                'list' => true,
                'req' => false,
            ],
            'pengirim' => [
                'label' => 'Pengirim (Unit)',
                'type' => 'text',
                'list' => true,
                'req' => false,
            ],
            'perihal' => [
                'label' => 'Perihal',
                'type' => 'text',
                'list' => true,
                'req' => true,
            ],
            'tanggal' => [
                'label' => 'Tanggal',
                'type' => 'date',
                'list' => true,
                'fmt' => 'date',
                'req' => true,
                'default' => 'today',
            ],
            'penerima' => [
                'label' => 'Penerima / Tujuan',
                'type' => 'text',
                'list' => true,
                'req' => false,
            ],
            'lokasi_arsip' => [
                'label' => 'Lokasi Arsip',
                'type' => 'text',
                'list' => true,
                'req' => false,
                'help' => 'cth: Lemari A / Ordner 3 / Map Merah',
            ],
            'lampiran' => [
                'label' => 'Lampiran',
                'type' => 'file',
                'list' => true,
                'req' => false,
            ],
            'dibuat_oleh' => [
                'label' => 'Dibuat Oleh',
                'type' => 'auto_user',
                'list' => false,
                'req' => false,
            ],
        ],
    ],
    'memo_masuk' => [
        'model' => 'App\\Models\\SrMemoMasuk',
        'perm' => 'risalah',
        'modul' => 'Arsip Surat & Memo',
        'judul' => 'Memo Masuk',
        'maker_checker' => false,
        'fields' => [
            'nomor_agenda' => [
                'label' => 'Nomor Agenda',
                'type' => 'number',
                'list' => true,
                'req' => true,
                'help' => 'Rekomendasi otomatis (+10 setiap hari baru, +1 di hari yang sama) — boleh diubah / disisip nomor untuk tanggal sebelumnya.',
            ],
            'no_surat' => [
                'label' => 'No. Surat',
                'type' => 'text',
                'list' => true,
                'req' => false,
            ],
            'pengirim' => [
                'label' => 'Pengirim',
                'type' => 'text',
                'list' => true,
                'req' => false,
            ],
            'perihal' => [
                'label' => 'Perihal',
                'type' => 'text',
                'list' => true,
                'req' => true,
            ],
            'tanggal' => [
                'label' => 'Tanggal',
                'type' => 'date',
                'list' => true,
                'fmt' => 'date',
                'req' => true,
                'default' => 'today',
            ],
            'penerima' => [
                'label' => 'Penerima',
                'type' => 'text',
                'list' => true,
                'req' => false,
            ],
            'lokasi_arsip' => [
                'label' => 'Lokasi Arsip',
                'type' => 'text',
                'list' => true,
                'req' => false,
                'help' => 'cth: Lemari A / Ordner 3 / Map Merah',
            ],
            'lampiran' => [
                'label' => 'Lampiran',
                'type' => 'file',
                'list' => true,
                'req' => false,
            ],
            'dibuat_oleh' => [
                'label' => 'Dibuat Oleh',
                'type' => 'auto_user',
                'list' => false,
                'req' => false,
            ],
        ],
    ],
    'memo_keluar' => [
        'model' => 'App\\Models\\SrMemoKeluar',
        'perm' => 'risalah',
        'modul' => 'Arsip Surat & Memo',
        'judul' => 'Memo Keluar',
        'maker_checker' => false,
        'fields' => [
            'nomor_agenda' => [
                'label' => 'Nomor Agenda',
                'type' => 'number',
                'list' => true,
                'req' => true,
                'help' => 'Rekomendasi otomatis (+10 setiap hari baru, +1 di hari yang sama) — boleh diubah / disisip nomor untuk tanggal sebelumnya.',
            ],
            'no_surat' => [
                'label' => 'No. Surat',
                'type' => 'text',
                'list' => true,
                'req' => false,
            ],
            'pengirim' => [
                'label' => 'Pengirim (Unit)',
                'type' => 'text',
                'list' => true,
                'req' => false,
            ],
            'perihal' => [
                'label' => 'Perihal',
                'type' => 'text',
                'list' => true,
                'req' => true,
            ],
            'tanggal' => [
                'label' => 'Tanggal',
                'type' => 'date',
                'list' => true,
                'fmt' => 'date',
                'req' => true,
                'default' => 'today',
            ],
            'penerima' => [
                'label' => 'Penerima / Tujuan',
                'type' => 'text',
                'list' => true,
                'req' => false,
            ],
            'lokasi_arsip' => [
                'label' => 'Lokasi Arsip',
                'type' => 'text',
                'list' => true,
                'req' => false,
                'help' => 'cth: Lemari A / Ordner 3 / Map Merah',
            ],
            'lampiran' => [
                'label' => 'Lampiran',
                'type' => 'file',
                'list' => true,
                'req' => false,
            ],
            'dibuat_oleh' => [
                'label' => 'Dibuat Oleh',
                'type' => 'auto_user',
                'list' => false,
                'req' => false,
            ],
        ],
    ],
];
```

**Mesin CRUD generik + view-nya:**

`app/Http/Controllers/ModuleController.php`
```php
<?php

namespace App\Http\Controllers;

use App\Concerns\LogsAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Services\AmortisasiCalculator;

// Mesin CRUD generik untuk 20 modul transaksional Portum, setara "RES"
// di portum.py -- satu controller melayani semua modul lewat {key} di
// route, konfigurasinya diambil dari config/modules.php. Menjaga arsitektur
// aslinya (config-driven), tapi sekarang di atas Eloquent + Laravel RBAC.
class ModuleController extends Controller
{
    use LogsAudit;
    public function __construct(private AmortisasiCalculator $amortisasiCalculator)
    {
    }
    private function config(string $key): array
    {
        $cfg = config("modules.$key");
        abort_if(!$cfg, 404, "Modul '$key' tidak ditemukan.");
        return $cfg;
    }

    private function authorizeModule(string $key, string $mode = 'read'): array
    {
        $cfg = $this->config($key);
        $user = auth()->user();

        $perm = \App\Models\RolePermission::where('role_id', $user->role_id)
            ->where('perm_key', $cfg['perm'])
            ->first();

        abort_if(!$perm, 403, "Role Anda tidak memiliki akses ke modul {$cfg['modul']}.");
        abort_if($mode === 'write' && !$perm->can_write, 403, "Role Anda hanya bisa melihat modul {$cfg['modul']}.");

        return $cfg;
    }

    public function index(Request $request, string $key)
    {
        $cfg = $this->authorizeModule($key, 'read');
        $model = $cfg['model'];

        $items = $model::query()
            ->when($request->q, function ($q) use ($cfg, $request) {
                $q->where(function ($qq) use ($cfg, $request) {
                    foreach (array_keys($cfg['fields']) as $field) {
                        $qq->orWhere($field, 'like', '%' . $request->q . '%');
                    }
                });
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('modules.index', compact('cfg', 'items', 'key'));
    }

    public function create(string $key)
    {
        $cfg = $this->authorizeModule($key, 'write');
        return view('modules.form', ['cfg' => $cfg, 'key' => $key, 'item' => null]);
    }

    public function store(Request $request, string $key)
    {
        $cfg = $this->authorizeModule($key, 'write');
        $data = $this->validated($request, $cfg);
        $data = $this->hitungAmortisasiJikaPerlu($key, $data);

        if ($cfg['maker_checker']) {
            $data['maker_id'] = auth()->id();
            $data['approval_status'] = 'Diajukan';
        }
        if (array_key_exists('dibuat_oleh', $cfg['fields'])) {
            $data['dibuat_oleh'] = auth()->user()->nama_lengkap;
        }

        $item = $cfg['model']::create($data);
        $this->audit('CREATE', $cfg['modul'], $cfg['judul'], $item->id, 'Menambahkan data baru');

        return redirect()->route('modul.index', $key)->with('status', "{$cfg['judul']} berhasil ditambahkan.");
    }

    public function edit(string $key, int $id)
    {
        $cfg = $this->authorizeModule($key, 'write');
        $item = $cfg['model']::findOrFail($id);
        return view('modules.form', ['cfg' => $cfg, 'key' => $key, 'item' => $item]);
    }

    public function update(Request $request, string $key, int $id)
    {
        $cfg = $this->authorizeModule($key, 'write');
        $item = $cfg['model']::findOrFail($id);
        $data = $this->validated($request, $cfg);
        $data = $this->hitungAmortisasiJikaPerlu($key, $data);

        $item->update($data);
        $this->audit('UPDATE', $cfg['modul'], $cfg['judul'], $item->id, 'Mengubah data');

        return redirect()->route('modul.index', $key)->with('status', "{$cfg['judul']} berhasil diperbarui.");
    }

    public function destroy(string $key, int $id)
    {
        $cfg = $this->authorizeModule($key, 'write');
        $item = $cfg['model']::findOrFail($id);
        $item->delete();
        $this->audit('DELETE', $cfg['modul'], $cfg['judul'], $id, 'Menghapus data');

        return redirect()->route('modul.index', $key)->with('status', "{$cfg['judul']} berhasil dihapus.");
    }

    // --- Alur Maker-Checker (CPMK Blockchain) ---

    public function approve(string $key, int $id)
    {
        $cfg = $this->authorizeModule($key, 'write');
        abort_unless($cfg['maker_checker'], 404);
        $item = $cfg['model']::findOrFail($id);

        Gate::authorize('approve', $item);

        $item->update([
            'checker_id' => auth()->id(),
            'approval_status' => 'Disetujui',
            'approved_at' => now(),
        ]);
        $this->audit('APPROVE', $cfg['modul'], $cfg['judul'], $item->id, 'Menyetujui transaksi (checker)');

        return back()->with('status', 'Disetujui.');
    }

    public function reject(Request $request, string $key, int $id)
    {
        $cfg = $this->authorizeModule($key, 'write');
        abort_unless($cfg['maker_checker'], 404);
        $item = $cfg['model']::findOrFail($id);

        Gate::authorize('reject', $item);

        $item->update([
            'checker_id' => auth()->id(),
            'approval_status' => 'Ditolak',
            'approved_at' => now(),
            'catatan_approval' => $request->input('catatan'),
        ]);
        $this->audit('REJECT', $cfg['modul'], $cfg['judul'], $item->id, 'Menolak transaksi (checker): ' . $request->input('catatan'));

        return back()->with('status', 'Ditolak.');
    }

    private function validated(Request $request, array $cfg): array
    {
        $rules = [];
        foreach ($cfg['fields'] as $field => $meta) {
            $type = $meta['type'] ?? 'text';
            $rule = ($meta['req'] ?? false) ? 'required' : 'nullable';
            $rule .= match ($type) {
                'date' => '|date',
                'number', 'money' => '|numeric|min:0',
                'checkbox' => '|boolean',
                'file' => '|string',
                'select' => isset($meta['opts']) ? '|string|in:' . implode(',', $meta['opts']) : '|string|max:2000',
                default => '|string|max:2000',
            };
            $rules[$field] = $rule;
        }
        return $request->validate($rules);
    }

    private function hitungAmortisasiJikaPerlu(string $key, array $data): array
{
    if ($key !== 'amortisasi') {
        return $data;
    }

    if (empty($data['nilai_per_bulan']) && !empty($data['nilai_perolehan']) && !empty($data['umur_bulan'])) {
        $data['nilai_per_bulan'] = $this->amortisasiCalculator->hitungNilaiPerBulan(
            (float) $data['nilai_perolehan'],
            (int) $data['umur_bulan']
        );
    }

    if (!empty($data['tanggal_mulai']) && !empty($data['nilai_per_bulan'])) {
        $bulanBerjalan = $this->amortisasiCalculator->hitungBulanBerjalan(new \DateTime($data['tanggal_mulai']));
        $data['akumulasi'] = $this->amortisasiCalculator->hitungAkumulasi((float) $data['nilai_per_bulan'], $bulanBerjalan);
        $data['nilai_buku'] = $this->amortisasiCalculator->hitungNilaiBuku((float) $data['nilai_perolehan'], $data['akumulasi']);
    }

    return $data;
}
}
```

`resources/views/modules/index.blade.php`
```blade
@extends('layouts.app')
@section('title', $cfg['judul'])
@section('content')
    <div class="flex items-start justify-between mb-5">
        <div>
            <p class="text-[12px] font-semibold uppercase tracking-wide text-gold mb-0.5">{{ $cfg['modul'] }}</p>
            <h1 class="text-xl font-bold text-ink">{{ $cfg['judul'] }}</h1>
        </div>
        <a href="{{ route('modul.create', $key) }}"
           class="inline-flex items-center gap-1.5 bg-brand text-white text-sm font-medium px-4 py-2.5 rounded-lg hover:bg-brand-light transition shadow-card">
            @include('partials.icon', ['name' => 'plus', 'class' => 'w-4 h-4'])
            Tambah Data
        </a>
    </div>

    <form method="GET" class="mb-4">
        <div class="relative w-72">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                @include('partials.icon', ['name' => 'search', 'class' => 'w-4 h-4'])
            </span>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari data..."
                   class="border border-slate-300 rounded-lg pl-9 pr-3.5 py-2 text-sm w-full bg-white focus:border-brand focus:ring-1 focus:ring-brand transition">
        </div>
    </form>

    @php $listFields = collect($cfg['fields'])->filter(fn ($f) => $f['list'] ?? false); @endphp

    <div class="bg-white rounded-xl border border-slate-200 shadow-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-left text-[12px] uppercase tracking-wide text-slate-500">
                        @foreach ($listFields as $field => $meta)
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">{{ $meta['label'] }}</th>
                        @endforeach
                        @if ($cfg['maker_checker'])
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Approval</th>
                        @endif
                        <th class="px-4 py-3 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($items as $item)
                        <tr class="hover:bg-slate-50/60 transition">
                            @foreach ($listFields as $field => $meta)
                                <td class="px-4 py-3 text-ink">
                                    @if (($meta['type'] ?? '') === 'money')
                                        <span class="font-mono text-[13px]">Rp {{ number_format((float) $item->$field, 0, ',', '.') }}</span>
                                    @elseif (($meta['type'] ?? '') === 'checkbox')
                                        <span class="px-2 py-0.5 rounded-full text-xs {{ $item->$field ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                            {{ $item->$field ? 'Ya' : 'Tidak' }}
                                        </span>
                                    @elseif (($meta['type'] ?? '') === 'date')
                                        @php $val = $item->$field; @endphp
                                        {{ $val ? ($val instanceof \Illuminate\Support\Carbon ? $val->format('d M Y') : \Illuminate\Support\Carbon::parse($val)->format('d M Y')) : '-' }}
                                    @elseif (($meta['type'] ?? '') === 'select')
                                        <span class="px-2 py-0.5 rounded-full text-xs bg-slate-100 text-slate-600">{{ $item->$field }}</span>
                                    @else
                                        {{ \Illuminate\Support\Str::limit((string) $item->$field, 40) }}
                                    @endif
                                </td>
                            @endforeach
                            @if ($cfg['maker_checker'])
                                <td class="px-4 py-3">
                                    <span @class([
                                        'px-2.5 py-1 rounded-full text-xs font-medium',
                                        'bg-amber-50 text-amber-700' => $item->approval_status === 'Diajukan',
                                        'bg-emerald-50 text-emerald-700' => $item->approval_status === 'Disetujui',
                                        'bg-red-50 text-red-700' => $item->approval_status === 'Ditolak',
                                    ])>{{ $item->approval_status }}</span>
                                </td>
                            @endif
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-3 text-[13px]">
                                    @if ($cfg['maker_checker'] && $item->approval_status === 'Diajukan')
                                        <form method="POST" action="{{ route('modul.approve', [$key, $item->id]) }}">
                                            @csrf
                                            <button class="text-emerald-700 font-medium hover:underline">Setujui</button>
                                        </form>
                                        <form method="POST" action="{{ route('modul.reject', [$key, $item->id]) }}">
                                            @csrf
                                            <button class="text-red-600 font-medium hover:underline">Tolak</button>
                                        </form>
                                    @endif
                                    <a href="{{ route('modul.edit', [$key, $item->id]) }}" class="text-brand font-medium hover:underline">Ubah</a>
                                    <form method="POST" action="{{ route('modul.destroy', [$key, $item->id]) }}"
                                          onsubmit="return confirm('Hapus data ini?')">
                                        @csrf @method('DELETE')
                                        <button class="text-slate-400 hover:text-red-600 transition">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-14 text-center">
                                <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                                    @include('partials.icon', ['name' => 'archive', 'class' => 'w-5 h-5'])
                                </div>
                                <p class="text-slate-400 text-sm mb-2">Belum ada data {{ strtolower($cfg['judul']) }}.</p>
                                <a href="{{ route('modul.create', $key) }}" class="text-brand text-sm font-medium hover:underline">+ Tambah data pertama</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $items->links() }}</div>
@endsection
```

`resources/views/modules/form.blade.php`
```blade
@extends('layouts.app')
@section('title', ($item ? 'Ubah' : 'Tambah') . ' — ' . $cfg['judul'])
@section('content')
    <p class="text-[12px] font-semibold uppercase tracking-wide text-gold mb-0.5">{{ $cfg['modul'] }}</p>
    <h1 class="text-xl font-bold text-ink mb-5">{{ $item ? 'Ubah' : 'Tambah' }} {{ $cfg['judul'] }}</h1>

    <form method="POST"
          action="{{ $item ? route('modul.update', [$key, $item->id]) : route('modul.store', $key) }}"
          class="bg-white rounded-xl border border-slate-200 shadow-card p-6 max-w-3xl">
        @csrf
        @if ($item) @method('PUT') @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
            @foreach ($cfg['fields'] as $field => $meta)
                @continue(in_array($field, ['maker_id', 'checker_id', 'approval_status', 'approved_at', 'catatan_approval', 'dibuat_oleh']))
                @php $isWide = in_array($meta['type'] ?? '', ['textarea']); @endphp
                <div class="{{ $isWide ? 'md:col-span-2' : '' }}">
                    <label class="block text-[13px] font-medium mb-1.5 text-slate-700">
                        {{ $meta['label'] }} @if($meta['req'] ?? false)<span class="text-red-500">*</span>@endif
                    </label>

                    @if (($meta['type'] ?? '') === 'textarea')
                        <textarea name="{{ $field }}" rows="3"
                            class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:border-brand focus:ring-1 focus:ring-brand transition">{{ old($field, $item?->$field) }}</textarea>
                    @elseif (($meta['type'] ?? '') === 'select' && !empty($meta['opts']))
                        <select name="{{ $field }}" class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm bg-white focus:border-brand focus:ring-1 focus:ring-brand transition">
                            <option value="">— pilih —</option>
                            @foreach ($meta['opts'] as $opt)
                                <option value="{{ $opt }}" @selected(old($field, $item?->$field) === $opt)>{{ $opt }}</option>
                            @endforeach
                        </select>
                    @elseif (($meta['type'] ?? '') === 'checkbox')
                        <label class="inline-flex items-center gap-2 mt-1">
                            <input type="checkbox" name="{{ $field }}" value="1" @checked(old($field, $item?->$field)) class="rounded border-slate-300">
                            <span class="text-sm text-slate-500">Ya</span>
                        </label>
                    @elseif (($meta['type'] ?? '') === 'date')
                        <input type="date" name="{{ $field }}" value="{{ old($field, optional($item?->$field)->format('Y-m-d') ?? $item?->$field) }}"
                               class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:border-brand focus:ring-1 focus:ring-brand transition">
                    @elseif (in_array($meta['type'] ?? '', ['number', 'money']))
                        <input type="number" step="0.01" name="{{ $field }}" value="{{ old($field, $item?->$field) }}"
                               class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm font-mono focus:border-brand focus:ring-1 focus:ring-brand transition">
                    @elseif (($meta['type'] ?? '') === 'file')
                        <input type="text" name="{{ $field }}" value="{{ old($field, $item?->$field) }}"
                               placeholder="Nama file (upload asli menyusul)"
                               class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:border-brand focus:ring-1 focus:ring-brand transition">
                    @else
                        <input type="text" name="{{ $field }}" value="{{ old($field, $item?->$field) }}"
                               class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:border-brand focus:ring-1 focus:ring-brand transition">
                    @endif

                    @if (!empty($meta['help']))
                        <p class="text-[12px] text-slate-400 mt-1">{{ $meta['help'] }}</p>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="pt-6 mt-2 border-t border-slate-100 flex gap-3">
            <button class="bg-brand text-white text-sm font-medium px-5 py-2.5 rounded-lg hover:bg-brand-light transition">Simpan</button>
            <a href="{{ route('modul.index', $key) }}" class="text-sm text-slate-500 px-5 py-2.5 hover:text-ink transition">Batal</a>
        </div>
    </form>
@endsection
```

**Seeder tambahan:**

`database/seeders/RefAkunSeeder.php`
```php
<?php

namespace Database\Seeders;

use App\Models\RefAkun;
use Illuminate\Database\Seeder;

class RefAkunSeeder extends Seeder
{
    // Referensi akun biaya, dipindahkan dari data Excel BIAYA 2026 yang
    // sebelumnya di-hardcode di portum.py.
    public function run(): void
    {
        $akun = [
            ['BEBAN BAHAN BAKAR UNTUK KANTOR', '000.00.6012109.001.360', 'Biaya Pembelian BBM Pertalite Kendaraan Operasional'],
            ['BEBAN JAMUAN KEPADA PEGAWAI', '000.00.6020123.001.360', 'Biaya Jamuan Kepada Pegawai dalam rangka Rapat'],
            ['BEBAN JAMUAN TAMU', '000.00.6020116.001.360', 'Biaya Jamuan Kepada Tamu'],
            ['BEBAN ALAT TULIS KANTOR', '000.00.6012101.001.360', 'Biaya Pembelian ATK'],
            ['BEBAN BARANG TERDAFTAR', '000.00.6012105.001.360', 'Biaya Pembelian Barang Terdaftar'],
            ['BEBAN TELFON/TELEX/FAX & TELEGRAM', '000.00.6012108.001.360', 'Biaya komunikasi'],
            ['PEMELIHARAAN PERBAIKAN INVENTARIS KANTOR', '000.00.6011703.001.360', 'Biaya Perbaikan/Service inventaris kantor'],
            ['PEMELIHARAAN PERBAIKAN KEND. BERMOTOR', '000.00.6011702.001.360', 'Biaya perbaikan/perawatan kendaraan bermotor'],
            ['BEBAN REKREASI & OLAHRAGA', '000.00.6020108.001.360', 'Biaya kegiatan olahraga/rekreasi'],
            ['BEBAN JARINGAN KOMUNIKASI DATA', '000.00.6012115.002.360', 'Biaya perangkat/jaringan komunikasi data'],
        ];
        foreach ($akun as [$nama, $rek, $ket]) {
            RefAkun::updateOrCreate(['nama_beban' => $nama], ['rekening_debet' => $rek, 'contoh_keterangan' => $ket]);
        }
    }
}
```

`database/seeders/DatabaseSeeder.php` — EDIT dari Step 8 — tambahkan RefAkunSeeder::class
```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            UserSeeder::class,
            RefAkunSeeder::class,
        ]);
    }
}
```

**Provider (butuh model Umum/Aset/Pengadaan di atas + ApprovalPolicy dari Step 8):**

`app/Providers/AppServiceProvider.php`
```php
<?php

namespace App\Providers;

use App\Models\AsMemoSewaCabang;
use App\Models\AsPks;
use App\Models\PgSpk;
use App\Models\UmBiayaHarian;
use App\Models\UmPermintaanCabang;
use App\Policies\ApprovalPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Semua model dengan alur Maker-Checker memakai satu ApprovalPolicy
        // yang sama (CPMK Blockchain: checker != maker).
        foreach ([UmBiayaHarian::class, UmPermintaanCabang::class, AsPks::class, AsMemoSewaCabang::class, PgSpk::class] as $model) {
            Gate::policy($model, ApprovalPolicy::class);
        }
    }
}
```

`bootstrap/providers.php` — EDIT dari Step 5 — daftarkan AppServiceProvider
```php
<?php

return [
    App\Providers\AppServiceProvider::class,
];
```

`routes/web.php` — EDIT dari Step 8 — tambahkan group modul/{key}
```php
<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ModuleController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/ganti-password-wajib', [LoginController::class, 'forceChangeForm'])->name('password.force-change');
    Route::post('/ganti-password-wajib', [LoginController::class, 'forceChange'])->name('password.force-change.submit');

    // Mesin CRUD generik untuk semua modul (lihat config/modules.php).
    Route::prefix('modul/{key}')->name('modul.')->group(function () {
        Route::get('/', [ModuleController::class, 'index'])->name('index');
        Route::get('/tambah', [ModuleController::class, 'create'])->name('create');
        Route::post('/', [ModuleController::class, 'store'])->name('store');
        Route::get('/{id}/ubah', [ModuleController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ModuleController::class, 'update'])->name('update');
        Route::delete('/{id}', [ModuleController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/setujui', [ModuleController::class, 'approve'])->name('approve');
        Route::post('/{id}/tolak', [ModuleController::class, 'reject'])->name('reject');
    });

    // Route dashboard & admin ditambahkan di Step 12.
});

```

```bash
php artisan migrate:fresh --seed
php artisan test
git add app/Models app/Http/Controllers/ModuleController.php resources/views/modules config/modules.php database/seeders app/Providers bootstrap/providers.php routes/web.php
git commit -m "feat(domain): implement Portum domain models and module engine"
git push -u origin feature/domain-models
```
PR → develop. Reviewer **Marsya**. Merge → Dirli pull develop.

> **Catatan buat kalian yang sudah sampai step 9 versi lama:** kalau branch/PR step 9 lama sudah kadung merge tanpa `ModuleController.php`/`config/modules.php`/`AppServiceProvider.php`, buat branch baru `fix/module-engine` dari develop, tambahkan 3 file itu di situ sebagai PR susulan — tidak perlu rewrite history yang sudah di-merge.

---

# STEP 10 — Zahra: Validation + Maker-Checker

```bash
git checkout develop
git pull origin develop
git checkout -b test/quality
```

`tests/Feature/MakerCheckerTest.php`
```php
<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\RolePermission;
use App\Models\UmBiayaHarian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MakerCheckerTest extends TestCase
{
    use RefreshDatabase;

    // Helper: setup role + permission + 2 user (maker & checker) sekali pakai
    private function setupUsers(): array
    {
        $role = Role::create([
            'nama'  => 'pimpinan',
            'label' => 'Pimpinan Divisi',
        ]);

        // Checker PERLU permission can_write agar lolos authorizeModule()
        RolePermission::create([
            'role_id'  => $role->id,
            'perm_key' => 'umum_rt',
            'can_write' => true,
        ]);

        $maker = User::factory()->create([
            'username' => 'adol',
            'role_id'  => $role->id,
        ]);

        $checker = User::factory()->create([
            'username' => 'pimpinan',
            'role_id'  => $role->id,
        ]);

        return [$maker, $checker];
    }

    /** Test 1: Maker TIDAK BISA self-approve */
    public function test_maker_cannot_approve_own_transaction(): void
    {
        [$maker] = $this->setupUsers();

        $transaksi = UmBiayaHarian::create([
            'tanggal'         => now()->toDateString(),
            'jumlah'          => 50000,
            'kategori'        => 'BBM',
            'uraian'          => 'Test self-approve',
            'maker_id'        => $maker->id,
            'approval_status' => 'Diajukan',
        ]);

        // Maker mencoba approve transaksinya sendiri → harus 403
        $response = $this->actingAs($maker)
            ->post("/modul/biaya_harian/{$transaksi->id}/setujui");

        $response->assertStatus(403);

        // Status di database harus tetap 'Diajukan'
        $this->assertDatabaseHas('um_biaya_harian', [
            'id'              => $transaksi->id,
            'approval_status' => 'Diajukan',
        ]);
    }

    /** Test 2: Checker YANG BERBEDA bisa approve */
    public function test_different_checker_can_approve(): void
    {
        [$maker, $checker] = $this->setupUsers();

        $transaksi = UmBiayaHarian::create([
            'tanggal'         => now()->toDateString(),
            'jumlah'          => 75000,
            'kategori'        => 'Perawatan',
            'uraian'          => 'Test normal approve',
            'maker_id'        => $maker->id,
            'approval_status' => 'Diajukan',
        ]);

        $response = $this->actingAs($checker)
            ->post("/modul/biaya_harian/{$transaksi->id}/setujui");

        // Harus redirect (berhasil)
        $response->assertRedirect();

        // Status di database harus berubah ke Disetujui
        $this->assertDatabaseHas('um_biaya_harian', [
            'id'              => $transaksi->id,
            'approval_status' => 'Disetujui',
            'checker_id'      => $checker->id,
        ]);
    }

    /** Test 3: Transaksi yang sudah disetujui tidak bisa disetujui lagi */
    public function test_already_approved_cannot_be_approved_again(): void
    {
        [$maker, $checker] = $this->setupUsers();

        // Langsung buat transaksi dengan status sudah 'Disetujui'
        $transaksi = UmBiayaHarian::create([
            'tanggal'         => now()->toDateString(),
            'jumlah'          => 200000,
            'kategori'        => 'Rumah Tangga',
            'uraian'          => 'Sudah disetujui',
            'maker_id'        => $maker->id,
            'approval_status' => 'Disetujui',
            'checker_id'      => $checker->id,
        ]);

        // Checker coba approve lagi → harus 403
        $response = $this->actingAs($checker)
            ->post("/modul/biaya_harian/{$transaksi->id}/setujui");

        $response->assertStatus(403);
    }
}
```

`tests/Feature/ValidasiInputTest.php`
```php
<?php

namespace Tests\Feature;

use App\Models\RolePermission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidasiInputTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

protected function setUp(): void
{
    parent::setUp();

    $role = Role::create([
        'nama' => 'umum_rt',
        'label' => 'Staf Umum & Rumah Tangga',
    ]);

    RolePermission::create([
        'role_id' => $role->id,
        'perm_key' => 'umum_rt',
        'can_write' => true,
    ]);

    $this->user = User::factory()->create(['role_id' => $role->id, 'is_active' => true]);
}
    public function test_biaya_harian_dengan_jumlah_negatif_ditolak(): void
    {
        $response = $this->actingAs($this->user)->post('/modul/biaya_harian', [
            'tanggal' => now()->toDateString(),
            'kategori' => 'BBM',
            'nama_beban' => 'Isi bensin',
            'jumlah' => -50000,
            'uraian' => 'Test input negatif',
        ]);

        $response->assertSessionHasErrors('jumlah');
    }

    public function test_biaya_harian_dengan_field_wajib_kosong_ditolak(): void
    {
        $response = $this->actingAs($this->user)->post('/modul/biaya_harian', [
            'tanggal' => now()->toDateString(),
            'jumlah' => 50000,
        ]);

        $response->assertSessionHasErrors('kategori');
    }

    public function test_biaya_harian_dengan_kategori_di_luar_opsi_ditolak(): void
    {
        $response = $this->actingAs($this->user)->post('/modul/biaya_harian', [
            'tanggal' => now()->toDateString(),
            'kategori' => 'Kategori-Tidak-Terdaftar-XYZ',
            'nama_beban' => 'Test',
            'jumlah' => 10000,
        ]);

        $response->assertSessionHasErrors('kategori');
    }

    public function test_biaya_harian_dengan_tanggal_tidak_valid_ditolak(): void
    {
        $response = $this->actingAs($this->user)->post('/modul/biaya_harian', [
            'tanggal' => 'bukan-tanggal',
            'kategori' => 'BBM',
            'jumlah' => 10000,
        ]);

        $response->assertSessionHasErrors('tanggal');
    }

    public function test_biaya_harian_dengan_input_valid_berhasil_tersimpan(): void
    {
        $response = $this->actingAs($this->user)->post('/modul/biaya_harian', [
            'tanggal' => now()->toDateString(),
            'kategori' => 'BBM',
            'nama_beban' => 'Isi bensin dinas',
            'jumlah' => 75000,
            'uraian' => 'Perjalanan dinas ke cabang',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('um_biaya_harian', ['jumlah' => 75000, 'kategori' => 'BBM']);
    }
}
```

```bash
php artisan test tests/Feature/MakerCheckerTest.php
php artisan test tests/Feature/ValidasiInputTest.php
git add tests
git commit -m "test: cover validation and maker checker"
git push -u origin test/quality
```
PR → develop. Reviewer **Dirli**. Kalau gagal karena bug di production code, buat Issue — jangan diam-diam mengubah kode orang lain.

---

# STEP 11 — Dirli: Audit + Hash Chain

```bash
git checkout develop
git pull origin develop
git checkout -b feature/audit-chain
```

`app/Models/AuditLog.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $table = 'audit_log';

    protected $casts = [
        'created_at' => 'datetime',
    ];
    
    protected $fillable = [
        'user_id',
        'username',
        'aksi',
        'modul',
        'entitas',
        'entitas_id',
        'keterangan',
        'ip_address',
        'user_agent',
        'prev_hash',
        'hash'
    ];
    public $timestamps = false;

    protected static function booted(): void
    {
        // CPMK Blockchain: setiap baris baru dirantai ke hash baris sebelumnya.
        static::creating(function (AuditLog $log) {
            $prev = static::orderByDesc('id')->first();
            $log->prev_hash = $prev?->hash;
            $log->created_at = $log->created_at ?? now();
            $log->hash = hash('sha256', $log->prev_hash . '|' . $log->aksi . '|' . $log->modul . '|'
                . $log->entitas . '|' . $log->entitas_id . '|' . $log->keterangan . '|' . $log->created_at);
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

`app/Concerns/LogsAudit.php`
```php
<?php

namespace App\Concerns;

use App\Models\AuditLog;

trait LogsAudit
{
    protected function audit(string $aksi, string $modul, string $entitas, int|string $entitasId, string $keterangan): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'username' => auth()->user()?->username,
            'aksi' => $aksi,
            'modul' => $modul,
            'entitas' => $entitas,
            'entitas_id' => (string) $entitasId,
            'keterangan' => $keterangan,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
```

`app/Services/AuditHasher.php`
```php
<?php

namespace App\Services;

class AuditHasher
{
    /**
     * Logika hash chain yang sama persis dengan AuditLog::booted(),
     * diekstrak ke sini supaya bisa di-unit-test tanpa Eloquent/database.
     */
    public function hitungHash(
        ?string $prevHash,
        string $aksi,
        string $modul,
        string $entitas,
        int|string|null $entitasId,
        ?string $keterangan,
        string $createdAt
    ): string {
        $payload = $prevHash . '|' . $aksi . '|' . $modul . '|'
            . $entitas . '|' . $entitasId . '|' . $keterangan . '|' . $createdAt;

        return hash('sha256', $payload);
    }

    /**
     * Verifikasi satu baris log terhadap hash yang tersimpan.
     * Dipakai AuditComplianceCheckCommand & bisa dipakai VerifyAuditChainCommand
     * kalau nanti mau direfaktor supaya tidak duplikasi logika hash.
     */
    public function cocok(string $hashTersimpan, string $hashHitung): bool
    {
        return hash_equals($hashHitung, $hashTersimpan);
    }
}
```

`app/Console/Commands/VerifyAuditChainCommand.php`
```php
<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;

// CPMK Teknologi Blockchain: verifikasi integritas rantai hash di audit_log.
// Menghitung ulang hash tiap baris dari datanya + prev_hash yang tersimpan,
// lalu membandingkan dengan hash yang tercatat -- kalau ada baris yang
// datanya diubah langsung di database (bypass Eloquent), rantai akan putus
// dan langsung ketahuan di baris tempat manipulasi terjadi.
class VerifyAuditChainCommand extends Command
{
    protected $signature = 'audit:verify-chain';
    protected $description = 'Verifikasi integritas rantai hash pada tabel audit_log';

    public function handle(): int
    {
        $expectedPrev = null;
        $rusak = 0;

        AuditLog::orderBy('id')->chunk(500, function ($logs) use (&$expectedPrev, &$rusak) {
            foreach ($logs as $log) {
                if ($log->prev_hash !== $expectedPrev) {
                    $this->error("Rantai putus di log #{$log->id}: prev_hash tidak sesuai urutan sebelumnya.");
                    $rusak++;
                }

                $hitung = hash('sha256', $log->prev_hash . '|' . $log->aksi . '|' . $log->modul . '|'
                    . $log->entitas . '|' . $log->entitas_id . '|' . $log->keterangan . '|' . $log->created_at);

                if (!hash_equals($hitung, $log->hash)) {
                    $this->error("Hash tidak cocok di log #{$log->id} -- kemungkinan data dimodifikasi langsung di database.");
                    $rusak++;
                }

                $expectedPrev = $log->hash;
            }
        });

        if ($rusak === 0) {
            $this->info('Rantai audit_log valid, tidak ada indikasi manipulasi.');
            return self::SUCCESS;
        }

        $this->warn("Ditemukan {$rusak} anomali pada rantai audit_log.");
        return self::FAILURE;
    }
}
```

`app/Console/Commands/RepairAuditChainCommand.php`
```php
<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;

// PERINGATAN: Command ini HANYA untuk keperluan demo/testing.
// Di production, JANGAN gunakan command ini karena akan mengubah hash seluruh rantai.
// Fungsi: Menghitung ulang hash semua baris dari baris tertentu ke atas,
// sehingga rantai kembali valid setelah simulasi manipulasi sengaja.
class RepairAuditChainCommand extends Command
{
    protected $signature   = 'audit:repair-chain {--from=1 : Mulai perbaiki dari ID berapa}';
    protected $description = '[DEMO ONLY] Hitung ulang hash seluruh rantai audit_log mulai dari ID tertentu';

    public function handle(): int
    {
        $fromId = (int) $this->option('from');

        $this->warn("⚠️  [DEMO] Menghitung ulang rantai hash mulai dari log id >= {$fromId} ...");

        if (!$this->confirm('Lanjutkan? (Ini akan mengubah hash di database)')) {
            $this->info('Dibatalkan.');
            return self::SUCCESS;
        }

        // Ambil hash dari baris sebelum titik perbaikan (sebagai prev_hash awal)
        $prevLog      = AuditLog::where('id', '<', $fromId)->orderByDesc('id')->first();
        $expectedPrev = $prevLog?->hash;

        $diperbaiki = 0;

        AuditLog::where('id', '>=', $fromId)
            ->orderBy('id')
            ->chunk(500, function ($logs) use (&$expectedPrev, &$diperbaiki) {
                foreach ($logs as $log) {
                    // Hitung ulang hash dari data mentah yang ada di baris ini
                    $hashBaru = hash(
                        'sha256',
                        $expectedPrev . '|' . $log->aksi . '|' . $log->modul . '|'
                        . $log->entitas . '|' . $log->entitas_id . '|' . $log->keterangan . '|' . $log->created_at
                    );

                    // Update prev_hash dan hash di database langsung (bypass Eloquent booted)
                    \Illuminate\Support\Facades\DB::table('audit_log')
                        ->where('id', $log->id)
                        ->update([
                            'prev_hash' => $expectedPrev,
                            'hash'      => $hashBaru,
                        ]);

                    $this->line("  ✔ log #{$log->id} hash diperbarui");

                    $expectedPrev = $hashBaru;
                    $diperbaiki++;
                }
            });

        $this->info("Selesai. {$diperbaiki} baris hash diperbarui.");
        $this->info("Jalankan 'php artisan audit:verify-chain' untuk konfirmasi.");

        return self::SUCCESS;
    }
}
```

`app/Http/Controllers/Admin/AuditLogController.php`
```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $items = AuditLog::with('user')
            ->when($request->modul, fn ($q) => $q->where('modul', 'like', "%{$request->modul}%"))
            ->when($request->username, fn ($q) => $q->where('username', 'like', "%{$request->username}%"))
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        return view('admin.audit-log.index', compact('items'));
    }
}
```

`resources/views/admin/audit-log/index.blade.php`
```blade
@extends('layouts.app')
@section('title', 'Audit Log')
@section('content')
    <div class="mb-5">
        <p class="text-[12px] font-semibold uppercase tracking-wide text-gold mb-0.5">Pengaturan</p>
        <h1 class="text-xl font-bold text-ink">Audit Log</h1>
    </div>

    <form method="GET" class="mb-4 flex flex-wrap gap-2">
        <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                @include('partials.icon', ['name' => 'search', 'class' => 'w-4 h-4'])
            </span>
            <input name="username" value="{{ request('username') }}" placeholder="Cari username"
                   class="border border-slate-300 rounded-lg pl-9 pr-3.5 py-2.5 text-sm focus:border-brand focus:ring-1 focus:ring-brand transition">
        </div>
        <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                @include('partials.icon', ['name' => 'search', 'class' => 'w-4 h-4'])
            </span>
            <input name="modul" value="{{ request('modul') }}" placeholder="Cari modul"
                   class="border border-slate-300 rounded-lg pl-9 pr-3.5 py-2.5 text-sm focus:border-brand focus:ring-1 focus:ring-brand transition">
        </div>
        <button class="bg-brand text-white text-sm font-medium px-4 py-2.5 rounded-lg hover:bg-brand-light transition">Cari</button>
    </form>

    <div class="bg-white rounded-xl border border-slate-200 shadow-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-left text-[12px] uppercase tracking-wide text-slate-500">
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Waktu</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">User</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Aksi</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Modul</th>
                        <th class="px-4 py-3 font-semibold">Keterangan</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Hash</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($items as $log)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-4 py-3 whitespace-nowrap text-slate-500 font-mono text-[12.5px]">{{ $log->created_at }}</td>
                            <td class="px-4 py-3 text-ink font-medium">{{ $log->username }}</td>
                            <td class="px-4 py-3">
                                <span @class([
                                    'px-2.5 py-1 rounded-full text-xs font-medium capitalize',
                                    'bg-emerald-50 text-emerald-700' => str_contains(strtolower($log->aksi), 'tambah') || str_contains(strtolower($log->aksi), 'create'),
                                    'bg-blue-50 text-blue-700' => str_contains(strtolower($log->aksi), 'ubah') || str_contains(strtolower($log->aksi), 'update'),
                                    'bg-red-50 text-red-700' => str_contains(strtolower($log->aksi), 'hapus') || str_contains(strtolower($log->aksi), 'delete'),
                                    'bg-slate-100 text-slate-600' => !str_contains(strtolower($log->aksi), 'tambah') && !str_contains(strtolower($log->aksi), 'create') && !str_contains(strtolower($log->aksi), 'ubah') && !str_contains(strtolower($log->aksi), 'update') && !str_contains(strtolower($log->aksi), 'hapus') && !str_contains(strtolower($log->aksi), 'delete'),
                                ])>{{ $log->aksi }}</span>
                            </td>
                            <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $log->modul }} / {{ $log->entitas }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ \Illuminate\Support\Str::limit($log->keterangan, 50) }}</td>
                            <td class="px-4 py-3">
                                <span class="font-mono text-[11.5px] bg-slate-100 text-slate-600 px-2 py-1 rounded-md">{{ \Illuminate\Support\Str::limit($log->hash, 12) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-14 text-center">
                                <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                                    @include('partials.icon', ['name' => 'clock', 'class' => 'w-5 h-5'])
                                </div>
                                <p class="text-slate-400 text-sm">Belum ada aktivitas tercatat.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $items->links() }}</div>
    <p class="text-xs text-slate-400 mt-3 flex items-center gap-1.5">
        @include('partials.icon', ['name' => 'lock', 'class' => 'w-3.5 h-3.5 flex-shrink-0'])
        Jalankan <code class="font-mono bg-slate-100 px-1.5 py-0.5 rounded text-[11px]">php artisan audit:verify-chain</code> untuk memverifikasi integritas seluruh rantai hash di atas.
    </p>
@endsection
```

`tests/Unit/AuditHasherTest.php`
```php
<?php

namespace Tests\Unit;

use App\Services\AuditHasher;
use PHPUnit\Framework\TestCase;

class AuditHasherTest extends TestCase
{
    public function test_hash_konsisten_untuk_input_sama(): void
    {
        $hasher = new AuditHasher();
        $waktu = '2026-01-01 10:00:00';

        $hash1 = $hasher->hitungHash('genesis', 'CREATE', 'aset_logistik', 'AsAset', 1, 'Aset baru', $waktu);
        $hash2 = $hasher->hitungHash('genesis', 'CREATE', 'aset_logistik', 'AsAset', 1, 'Aset baru', $waktu);

        $this->assertEquals($hash1, $hash2);
    }

    public function test_hash_berbeda_jika_prev_hash_berbeda(): void
    {
        $hasher = new AuditHasher();
        $waktu = '2026-01-01 10:00:00';

        $hashA = $hasher->hitungHash('hash-lama-1', 'CREATE', 'aset_logistik', 'AsAset', 1, 'Aset baru', $waktu);
        $hashB = $hasher->hitungHash('hash-lama-2', 'CREATE', 'aset_logistik', 'AsAset', 1, 'Aset baru', $waktu);

        $this->assertNotEquals($hashA, $hashB);
    }

    public function test_hash_berubah_jika_keterangan_diubah_sedikit_saja(): void
    {
        $hasher = new AuditHasher();
        $waktu = '2026-01-01 10:00:00';

        $hashAsli = $hasher->hitungHash('genesis', 'UPDATE', 'aset_logistik', 'AsTemuan', 5, 'Kondisi baik', $waktu);
        $hashDiubah = $hasher->hitungHash('genesis', 'UPDATE', 'aset_logistik', 'AsTemuan', 5, 'Kondisi baik.', $waktu);

        $this->assertNotEquals($hashAsli, $hashDiubah, 'Perubahan sekecil apa pun pada data harus mengubah hash -- ini bukti utama integritas hash chain.');
    }

    public function test_cocok_mendeteksi_hash_valid(): void
    {
        $hasher = new AuditHasher();
        $hash = $hasher->hitungHash('genesis', 'CREATE', 'pengadaan', 'PgSpk', 3, 'SPK baru', '2026-01-01 10:00:00');

        $this->assertTrue($hasher->cocok($hash, $hash));
    }

    public function test_cocok_mendeteksi_hash_tidak_valid(): void
    {
        $hasher = new AuditHasher();
        $hashAsli = $hasher->hitungHash('genesis', 'CREATE', 'pengadaan', 'PgSpk', 3, 'SPK baru', '2026-01-01 10:00:00');

        $this->assertFalse($hasher->cocok($hashAsli, 'hash-palsu-hasil-manipulasi'));
    }
}
```

`routes/web.php` — EDIT — tambahkan group admin (khusus audit-log dulu; users/roles menyusul di Step 12)
```php
<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ModuleController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/ganti-password-wajib', [LoginController::class, 'forceChangeForm'])->name('password.force-change');
    Route::post('/ganti-password-wajib', [LoginController::class, 'forceChange'])->name('password.force-change.submit');

    Route::prefix('modul/{key}')->name('modul.')->group(function () {
        Route::get('/', [ModuleController::class, 'index'])->name('index');
        Route::get('/tambah', [ModuleController::class, 'create'])->name('create');
        Route::post('/', [ModuleController::class, 'store'])->name('store');
        Route::get('/{id}/ubah', [ModuleController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ModuleController::class, 'update'])->name('update');
        Route::delete('/{id}', [ModuleController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/setujui', [ModuleController::class, 'approve'])->name('approve');
        Route::post('/{id}/tolak', [ModuleController::class, 'reject'])->name('reject');
    });

    Route::prefix('admin')->name('admin.')->middleware('superadmin')->group(function () {
        Route::get('/audit-log', [AuditLogController::class, 'index'])->name('audit-log.index');

        // Route users & roles ditambahkan di Step 12.
    });
});

```

```bash
php artisan test tests/Unit/AuditHasherTest.php
php artisan test
git add app/Models/AuditLog.php app/Concerns app/Services/AuditHasher.php app/Console/Commands/VerifyAuditChainCommand.php app/Console/Commands/RepairAuditChainCommand.php app/Http/Controllers/Admin/AuditLogController.php resources/views/admin/audit-log tests/Unit/AuditHasherTest.php routes/web.php
git commit -m "feat(audit): implement audit hash chain"
git push -u origin feature/audit-chain
```
PR → develop. Reviewer **Zahra**. Merge → semua pull.

---

# STEP 12 — Marsya: Dashboard + Admin

```bash
git checkout develop
git pull origin develop
git checkout -b feature/dashboard-admin
```

`app/Http/Controllers/DashboardController.php`
```php
<?php

namespace App\Http\Controllers;

use App\Models\AsAset;
use App\Models\AsPks;
use App\Models\AuditLog;
use App\Models\FactPengadaan;
use App\Models\PgReminder;
use App\Models\UmBiayaHarian;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $menunggu = UmBiayaHarian::where('approval_status', 'Diajukan')->count();
        $reminderAktif = PgReminder::where('status', 'Aktif')
            ->whereDate('tanggal_jatuh_tempo', '<=', now()->addDays(90))
            ->count();
        $pksJatuhTempo = AsPks::whereIn('status', ['Aktif', 'Akan Jatuh Tempo'])
            ->whereDate('jatuh_tempo', '<=', now()->addDays(90))
            ->count();

        $totalAset = AsAset::count();
        $totalPengadaan = (float) FactPengadaan::sum('total_nilai');
        $activities = AuditLog::latest('id')->take(5)->get();
        $lastLog = $activities->first();

        // Data chart 6 bulan terakhir. Jika belum ada data, tetap kirim 0 agar view tidak error.
        $chartLabels = [];
        $chartValues = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->copy()->subMonths($i);
            $chartLabels[] = $month->translatedFormat('M');
            $chartValues[] = (float) UmBiayaHarian::whereYear('tanggal', $month->year)
                ->whereMonth('tanggal', $month->month)
                ->sum('jumlah');
        }

        return view('dashboard', compact(
            'menunggu',
            'reminderAktif',
            'pksJatuhTempo',
            'totalAset',
            'totalPengadaan',
            'activities',
            'lastLog',
            'chartLabels',
            'chartValues'
        ));
    }
}
```

`app/Http/Controllers/AnalyticsController.php`
```php
<?php

namespace App\Http\Controllers;

use App\Models\FactBiayaBulanan;
use App\Models\FactPengadaan;
use App\Models\FactAmortisasiAset;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function index()
    {
        // 1. KPI Summary Cards
        $totalBiaya = (float) FactBiayaBulanan::sum('total_biaya');
        $totalPengadaan = (float) FactPengadaan::sum('total_nilai');
        
        // Latest total nilai buku
        $latestWaktuId = FactAmortisasiAset::max('dim_waktu_id');
        $totalNilaiBuku = $latestWaktuId 
            ? (float) FactAmortisasiAset::where('dim_waktu_id', $latestWaktuId)->sum('nilai_buku') 
            : 0.0;

        // 2. Biaya Bulanan per Kategori
        $biaya = FactBiayaBulanan::join('dim_waktu', 'fact_biaya_bulanan.dim_waktu_id', '=', 'dim_waktu.id')
            ->join('dim_kategori', 'fact_biaya_bulanan.dim_kategori_id', '=', 'dim_kategori.id')
            ->selectRaw('dim_waktu.nama_bulan, dim_waktu.tahun, dim_waktu.bulan, dim_kategori.nama_kategori, sum(fact_biaya_bulanan.total_biaya) as total')
            ->groupBy('dim_waktu.tahun', 'dim_waktu.bulan', 'dim_waktu.nama_bulan', 'dim_kategori.nama_kategori')
            ->orderBy('dim_waktu.tahun')
            ->orderBy('dim_waktu.bulan')
            ->get();

        $biayaLabels = [];
        $kategoriList = [];
        $biayaMatrix = [];

        foreach ($biaya as $row) {
            $label = $row->nama_bulan . ' ' . $row->tahun;
            if (!in_array($label, $biayaLabels)) {
                $biayaLabels[] = $label;
            }
            $kategori = $row->nama_kategori;
            if (!in_array($kategori, $kategoriList)) {
                $kategoriList[] = $kategori;
            }
            $biayaMatrix[$kategori][$label] = (float) $row->total;
        }

        $biayaDatasets = [];
        $colors = [
            'BBM' => '#3b82f6',          // blue
            'Perawatan' => '#f59e0b',    // amber
            'Rumah Tangga' => '#10b981', // emerald
            'Lainnya' => '#6b7280'       // gray
        ];
        $defaultColors = ['#3b82f6', '#f59e0b', '#10b981', '#ec4899', '#8b5cf6', '#6b7280'];
        $colorIndex = 0;

        foreach ($kategoriList as $kategori) {
            $data = [];
            foreach ($biayaLabels as $lbl) {
                $data[] = $biayaMatrix[$kategori][$lbl] ?? 0;
            }
            $color = $colors[$kategori] ?? ($defaultColors[$colorIndex++ % count($defaultColors)]);
            $biayaDatasets[] = [
                'label' => $kategori,
                'data' => $data,
                'backgroundColor' => $color,
                'borderColor' => $color,
                'borderWidth' => 1
            ];
        }

        // 3. Pengadaan per Vendor (Doughnut Chart)
        $pengadaan = FactPengadaan::join('dim_vendor', 'fact_pengadaan.dim_vendor_id', '=', 'dim_vendor.id')
            ->selectRaw('dim_vendor.nama_vendor, sum(fact_pengadaan.total_nilai) as total')
            ->groupBy('dim_vendor.nama_vendor')
            ->get();

        $vendorLabels = [];
        $vendorTotals = [];
        foreach ($pengadaan as $row) {
            $vendorLabels[] = $row->nama_vendor ?: 'Lainnya';
            $vendorTotals[] = (float) $row->total;
        }

        // 4. Amortisasi Aset (Line Chart)
        $amortisasi = FactAmortisasiAset::join('dim_waktu', 'fact_amortisasi_aset.dim_waktu_id', '=', 'dim_waktu.id')
            ->selectRaw('dim_waktu.nama_bulan, dim_waktu.tahun, dim_waktu.bulan, sum(fact_amortisasi_aset.nilai_penyusutan_bulan) as total_penyusutan, sum(fact_amortisasi_aset.nilai_buku) as total_nilai_buku')
            ->groupBy('dim_waktu.tahun', 'dim_waktu.bulan', 'dim_waktu.nama_bulan')
            ->orderBy('dim_waktu.tahun')
            ->orderBy('dim_waktu.bulan')
            ->get();

        $amortisasiLabels = [];
        $penyusutanData = [];
        $nilaiBukuData = [];

        foreach ($amortisasi as $row) {
            $label = $row->nama_bulan . ' ' . $row->tahun;
            $amortisasiLabels[] = $label;
            $penyusutanData[] = (float) $row->total_penyusutan;
            $nilaiBukuData[] = (float) $row->total_nilai_buku;
        }

        return view('analitik', compact(
            'totalBiaya',
            'totalPengadaan',
            'totalNilaiBuku',
            'biayaLabels',
            'biayaDatasets',
            'vendorLabels',
            'vendorTotals',
            'amortisasiLabels',
            'penyusutanData',
            'nilaiBukuData'
        ));
    }
}
```

`app/Http/Controllers/Admin/UserController.php`
```php
<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\LogsAudit;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserController extends Controller
{
    use LogsAudit;

    public function index()
    {
        $items = User::with('role')->orderBy('username')->get();
        $roles = Role::orderBy('label')->get();
        return view('admin.users.index', compact('items', 'roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'username' => 'required|string|max:100|unique:users,username',
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'jabatan' => 'nullable|string|max:255',
            'bagian' => 'nullable|string|max:255',
            'role_id' => 'required|exists:roles,id',
        ]);

        $plain = Str::random(12);
        $data['password'] = bcrypt($plain);
        $data['must_change_pwd'] = true;
        $data['is_active'] = true;

        $user = User::create($data);
        $this->audit('CREATE', 'Manajemen User', 'User', $user->id, "Menambah user {$user->username}");

        return back()->with('status', "User {$user->username} dibuat. Password sementara: {$plain}");
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'jabatan' => 'nullable|string|max:255',
            'bagian' => 'nullable|string|max:255',
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'boolean',
        ]);
        $user->update($data);
        $this->audit('UPDATE', 'Manajemen User', 'User', $user->id, "Mengubah data user {$user->username}");

        return back()->with('status', 'User diperbarui.');
    }

    public function resetPassword(User $user)
    {
        $plain = Str::random(12);
        $user->update(['password' => bcrypt($plain), 'must_change_pwd' => true]);
        $this->audit('UPDATE', 'Manajemen User', 'User', $user->id, "Reset password user {$user->username}");

        return back()->with('status', "Password {$user->username} direset. Password sementara: {$plain}");
    }

    public function destroy(User $user)
    {
        $id = $user->id;
        $username = $user->username;
        $user->delete();
        $this->audit('DELETE', 'Manajemen User', 'User', $id, "Menghapus user {$username}");

        return back()->with('status', 'User dihapus.');
    }
}
```

`app/Http/Controllers/Admin/RoleController.php`
```php
<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\LogsAudit;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    use LogsAudit;

    const PERM_KEYS = [
        'dashboard', 'analytics_dw', 'umum_rt', 'aset_logistik', 'pengadaan', 'risalah',
        'panduan', 'user_mgmt', 'role_mgmt', 'audit_log', 'ref_akun',
    ];

    public function index()
    {
        $roles = Role::with('permissions')->orderBy('id')->get();
        return view('admin.roles.index', ['roles' => $roles, 'permKeys' => self::PERM_KEYS]);
    }

    public function updatePermissions(Request $request, Role $role)
    {
        foreach (self::PERM_KEYS as $key) {
            RolePermission::updateOrCreate(
                ['role_id' => $role->id, 'perm_key' => $key],
                ['can_write' => $request->boolean("write_{$key}")]
            );
        }
        $this->audit('UPDATE', 'Manajemen Role', 'Role', $role->id, "Mengubah matriks permission role {$role->nama}");

        return back()->with('status', "Permission role {$role->label} diperbarui.");
    }
}
```

`app/Models/Panduan.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Panduan extends Model
{
    protected $table = 'panduan';

    protected $fillable = [
        'judul',
        'kategori',
        'konten',
        'urutan',
        'updated_by'
    ];
}
```

`app/Models/RisalahRapat.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RisalahRapat extends Model
{
    protected $table = 'risalah_rapat';

    protected $fillable = [
        'nomor',
        'judul',
        'tanggal',
        'waktu',
        'tempat',
        'pemimpin',
        'peserta',
        'agenda',
        'pembahasan',
        'keputusan',
        'tindak_lanjut',
        'lampiran',
        'dibuat_oleh'
    ];

    protected function casts(): array
    {
        return [
        'tanggal' => 'date'
        ];
    }
}
```

`app/Http/Controllers/PanduanController.php`
```php
<?php

namespace App\Http\Controllers;

use App\Concerns\LogsAudit;
use App\Models\Panduan;
use Illuminate\Http\Request;

class PanduanController extends Controller
{
    use LogsAudit;

    private array $rules = [
        'judul' => 'required|string|max:255',
        'kategori' => 'nullable|string|max:100',
        'konten' => 'nullable|string',
        'urutan' => 'nullable|integer',
    ];

    public function index()
    {
        $items = Panduan::orderBy('kategori')->orderBy('urutan')->get();
        return view('panduan.index', compact('items'));
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules);
        $data['updated_by'] = auth()->user()->nama_lengkap;
        $item = Panduan::create($data);
        $this->audit('CREATE', 'Panduan', 'Panduan', $item->id, 'Menambah panduan');
        return back()->with('status', 'Panduan ditambahkan.');
    }

    public function update(Request $request, Panduan $panduan)
    {
        $data = $request->validate($this->rules);
        $data['updated_by'] = auth()->user()->nama_lengkap;
        $panduan->update($data);
        $this->audit('UPDATE', 'Panduan', 'Panduan', $panduan->id, 'Mengubah panduan');
        return back()->with('status', 'Panduan diperbarui.');
    }

    public function destroy(Panduan $panduan)
    {
        $id = $panduan->id;
        $panduan->delete();
        $this->audit('DELETE', 'Panduan', 'Panduan', $id, 'Menghapus panduan');
        return back()->with('status', 'Panduan dihapus.');
    }
}
```

`app/Http/Controllers/RisalahRapatController.php`
```php
<?php

namespace App\Http\Controllers;

use App\Concerns\LogsAudit;
use App\Models\RisalahRapat;
use Illuminate\Http\Request;

class RisalahRapatController extends Controller
{
    use LogsAudit;

    private array $rules = [
        'nomor' => 'nullable|string|max:100',
        'judul' => 'required|string|max:255',
        'tanggal' => 'required|date',
        'waktu' => 'nullable|string|max:50',
        'tempat' => 'nullable|string|max:255',
        'pemimpin' => 'nullable|string|max:255',
        'peserta' => 'nullable|string',
        'agenda' => 'nullable|string',
        'pembahasan' => 'nullable|string',
        'keputusan' => 'nullable|string',
        'tindak_lanjut' => 'nullable|string',
        'lampiran' => 'nullable|string|max:255',
    ];

    public function index()
    {
        $items = RisalahRapat::orderByDesc('tanggal')->paginate(20);
        return view('risalah.index', compact('items'));
    }

    public function create()
    {
        return view('risalah.form', ['item' => null]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules);
        $data['dibuat_oleh'] = auth()->user()->nama_lengkap;
        $item = RisalahRapat::create($data);
        $this->audit('CREATE', 'Risalah Rapat', 'Risalah Rapat', $item->id, 'Menambah risalah rapat');
        return redirect()->route('risalah.index')->with('status', 'Risalah rapat ditambahkan.');
    }

    public function edit(RisalahRapat $risalah)
    {
        return view('risalah.form', ['item' => $risalah]);
    }

    public function update(Request $request, RisalahRapat $risalah)
    {
        $data = $request->validate($this->rules);
        $risalah->update($data);
        $this->audit('UPDATE', 'Risalah Rapat', 'Risalah Rapat', $risalah->id, 'Mengubah risalah rapat');
        return redirect()->route('risalah.index')->with('status', 'Risalah rapat diperbarui.');
    }

    public function destroy(RisalahRapat $risalah)
    {
        $id = $risalah->id;
        $risalah->delete();
        $this->audit('DELETE', 'Risalah Rapat', 'Risalah Rapat', $id, 'Menghapus risalah rapat');
        return redirect()->route('risalah.index')->with('status', 'Risalah rapat dihapus.');
    }
}
```

`resources/views/dashboard.blade.php`
```blade
@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
@php
    $firstName = explode(' ', auth()->user()->nama_lengkap)[0];
    $maxChart = max(max($chartValues ?: [0]), 1);
    $points = [];
    $chartWidth = 650;
    $chartHeight = 180;
    $left = 40;
    $right = 12;
    $top = 18;
    $bottom = 28;
    $plotW = $chartWidth - $left - $right;
    $plotH = $chartHeight - $top - $bottom;
    $count = max(count($chartValues), 1);
    foreach ($chartValues as $i => $value) {
        $x = $left + ($count === 1 ? $plotW / 2 : ($i * $plotW / ($count - 1)));
        $y = $top + ($plotH - (($value / $maxChart) * $plotH));
        $points[] = round($x, 2) . ',' . round($y, 2);
    }
    $polyline = implode(' ', $points);
@endphp

<div class="pt-1 mb-5">
    <h2 class="text-[22px] leading-tight font-bold text-ink">Dashboard</h2>
    <p class="text-[12px] text-slate-500 mt-1">Selamat datang kembali, {{ $firstName }}!</p>
</div>

{{-- KPI --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-3 mb-3">
    <a href="#" class="group bg-white rounded-xl border border-slate-200/90 p-4 min-h-[137px] hover:shadow-hover transition">
        <div class="flex items-start gap-3">
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center flex-shrink-0">
                @include('partials.icon', ['name' => 'clock', 'class' => 'w-5 h-5'])
            </div>
            <div class="min-w-0">
                <p class="text-[11.5px] leading-4 text-slate-600">Menunggu<br>Persetujuan</p>
                <p class="text-[23px] font-bold text-ink mt-2">{{ $menunggu }}</p>
            </div>
        </div>
        <p class="text-[11px] text-brand mt-2.5">Lihat detail <span class="ml-1">→</span></p>
    </a>

    <a href="#" class="group bg-white rounded-xl border border-slate-200/90 p-4 min-h-[137px] hover:shadow-hover transition">
        <div class="flex items-start gap-3">
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0">
                @include('partials.icon', ['name' => 'clock', 'class' => 'w-5 h-5'])
            </div>
            <div class="min-w-0">
                <p class="text-[11.5px] leading-4 text-slate-600">Reminder<br>&le; 90 Hari</p>
                <p class="text-[23px] font-bold text-ink mt-2">{{ $reminderAktif }}</p>
            </div>
        </div>
        <p class="text-[11px] text-brand mt-2.5">Lihat detail <span class="ml-1">→</span></p>
    </a>

    <a href="#" class="group bg-white rounded-xl border border-slate-200/90 p-4 min-h-[137px] hover:shadow-hover transition">
        <div class="flex items-start gap-3">
            <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center flex-shrink-0">
                @include('partials.icon', ['name' => 'file-text', 'class' => 'w-5 h-5'])
            </div>
            <div class="min-w-0">
                <p class="text-[11.5px] leading-4 text-slate-600">PKS Jatuh Tempo</p>
                <p class="text-[23px] font-bold text-ink mt-7">{{ $pksJatuhTempo }}</p>
            </div>
        </div>
        <p class="text-[11px] text-brand mt-2.5">Lihat detail <span class="ml-1">→</span></p>
    </a>

    <a href="{{ route('modul.index', 'aset') }}" class="group bg-white rounded-xl border border-slate-200/90 p-4 min-h-[137px] hover:shadow-hover transition">
        <div class="flex items-start gap-3">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0">
                @include('partials.icon', ['name' => 'building', 'class' => 'w-5 h-5'])
            </div>
            <div class="min-w-0">
                <p class="text-[11.5px] leading-4 text-slate-600">Total Aset</p>
                <p class="text-[23px] font-bold text-ink mt-7">{{ number_format($totalAset, 0, ',', '.') }}</p>
            </div>
        </div>
        <p class="text-[11px] text-brand mt-2.5">Lihat detail <span class="ml-1">→</span></p>
    </a>

    <a href="{{ route('analitik') }}" class="group bg-white rounded-xl border border-slate-200/90 p-4 min-h-[137px] hover:shadow-hover transition">
        <div class="flex items-start gap-3">
            <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center flex-shrink-0">
                @include('partials.icon', ['name' => 'building', 'class' => 'w-5 h-5'])
            </div>
            <div class="min-w-0">
                <p class="text-[11.5px] leading-4 text-slate-600">Total Pengadaan<br>Bulan Ini</p>
                <p class="text-[21px] font-bold text-ink mt-4">Rp {{ number_format($totalPengadaan / 1000000, 2, ',', '.') }} M</p>
            </div>
        </div>
        <p class="text-[11px] text-brand mt-2.5">Lihat detail <span class="ml-1">→</span></p>
    </a>
</div>

<div class="grid grid-cols-1 xl:grid-cols-[1.55fr_1fr] gap-3 mb-3">
    {{-- Trend --}}
    <section class="bg-white rounded-xl border border-slate-200/90 p-5 min-h-[235px]">
        <div class="flex items-center justify-between gap-3 mb-3">
            <h3 class="text-[14px] font-bold text-ink">Tren Biaya Operasional <span class="font-normal text-slate-500">(6 Bulan Terakhir)</span></h3>
            <select class="text-[11px] border-0 bg-slate-100 rounded-lg px-3 py-2 text-slate-600 focus:ring-0">
                <option>Semua Kategori</option>
            </select>
        </div>
        <div class="overflow-hidden">
            <svg viewBox="0 0 {{ $chartWidth }} {{ $chartHeight }}" class="w-full h-[175px]" preserveAspectRatio="none" role="img" aria-label="Tren biaya operasional enam bulan terakhir">
                @foreach ([0, .25, .5, .75, 1] as $ratio)
                    @php $gy = $top + ($plotH * $ratio); $value = $maxChart * (1 - $ratio); @endphp
                    <line x1="{{ $left }}" x2="{{ $chartWidth - $right }}" y1="{{ $gy }}" y2="{{ $gy }}" stroke="#E8EBF0" stroke-width="1" />
                    <text x="4" y="{{ $gy + 4 }}" font-size="10" fill="#8A93A3">{{ number_format($value / 1000000, 0, ',', '.') }} jt</text>
                @endforeach
                <polyline points="{{ $polyline }}" fill="none" stroke="#D99A1D" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                @foreach ($points as $i => $point)
                    @php [$px, $py] = explode(',', $point); @endphp
                    <circle cx="{{ $px }}" cy="{{ $py }}" r="4" fill="#D99A1D" />
                    <text x="{{ $px }}" y="{{ $chartHeight - 7 }}" text-anchor="middle" font-size="10" fill="#697386">{{ $chartLabels[$i] ?? '' }}</text>
                @endforeach
            </svg>
        </div>
    </section>

    {{-- Attention --}}
    <section class="bg-white rounded-xl border border-slate-200/90 p-5 min-h-[235px]">
        <h3 class="text-[14px] font-bold text-ink mb-4">Perlu Perhatian</h3>
        <div class="divide-y divide-slate-100">
            <a href="#" class="flex items-center gap-3 py-3 first:pt-0 group">
                <span class="w-2.5 h-2.5 rounded-full bg-red-500 flex-shrink-0"></span>
                <span class="text-[12px] font-medium flex-1">{{ $pksJatuhTempo }} PKS akan jatuh tempo dalam 30 hari</span>
                <span class="text-[11px] text-brand">Lihat&nbsp; →</span>
            </a>
            <a href="#" class="flex items-center gap-3 py-3 group">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500 flex-shrink-0"></span>
                <span class="text-[12px] font-medium flex-1">{{ $menunggu }} dokumen menunggu persetujuan</span>
                <span class="text-[11px] text-brand">Lihat&nbsp; →</span>
            </a>
            <a href="#" class="flex items-center gap-3 py-3 last:pb-0 group">
                <span class="w-2.5 h-2.5 rounded-full bg-blue-500 flex-shrink-0"></span>
                <span class="text-[12px] font-medium flex-1">{{ $reminderAktif }} permintaan dari cabang menunggu</span>
                <span class="text-[11px] text-brand">Lihat&nbsp; →</span>
            </a>
        </div>
    </section>
</div>

<div class="grid grid-cols-1 xl:grid-cols-[1.55fr_1fr] gap-3">
    {{-- Activity --}}
    <section class="bg-white rounded-xl border border-slate-200/90 p-5 min-h-[280px]">
        <h3 class="text-[14px] font-bold text-ink mb-4">Aktivitas Terbaru</h3>
        <div class="divide-y divide-slate-100">
            @forelse ($activities as $activity)
                <div class="flex items-center gap-3 py-2.5 first:pt-0">
                    <div class="w-8 h-8 rounded-full bg-slate-100 text-brand flex items-center justify-center text-[11px] font-bold flex-shrink-0">
                        {{ strtoupper(substr($activity->username ?: 'S', 0, 1)) }}
                    </div>
                    <p class="text-[11.5px] flex-1 min-w-0 truncate">
                        <span class="font-medium">{{ $activity->username ?: 'Sistem' }}</span>
                        {{ strtolower($activity->keterangan ?: $activity->aksi) }}
                    </p>
                    <span class="hidden sm:inline-flex px-2 py-1 rounded-md bg-slate-100 text-slate-600 text-[9.5px] whitespace-nowrap">{{ $activity->modul ?: 'Sistem' }}</span>
                    <span class="text-[10.5px] text-slate-500 whitespace-nowrap">{{ optional($activity->created_at)->format('H:i') }}</span>
                </div>
            @empty
                <div class="py-10 text-center text-[12px] text-slate-400">Belum ada aktivitas.</div>
            @endforelse
        </div>
        @if ($activities->isNotEmpty())
            <div class="text-center mt-4">
                <a href="{{ auth()->user()->role->nama === 'superadmin' ? route('admin.audit-log.index') : '#' }}" class="inline-flex items-center gap-2 border border-slate-300 rounded-lg px-4 py-2 text-[11px] text-brand hover:bg-slate-50 transition">Lihat semua aktivitas <span>→</span></a>
            </div>
        @endif
    </section>

    {{-- Audit --}}
    <section class="bg-white rounded-xl border border-slate-200/90 p-5 min-h-[280px]">
        <h3 class="text-[14px] font-bold text-ink mb-8">Rantai Audit</h3>
        <div class="flex items-center gap-5">
            <div class="w-16 h-16 rounded-full bg-blue-50 text-blue-700 flex items-center justify-center flex-shrink-0">
                @include('partials.icon', ['name' => 'shield', 'class' => 'w-8 h-8', 'stroke' => 1.5])
            </div>
            <div>
                <p class="text-[12px] font-bold text-ink">Sistem audit aktif dan terverifikasi</p>
                <p class="text-[10.5px] text-slate-500 mt-1">
                    Terakhir diverifikasi:
                    {{ $lastLog?->created_at?->format('d M Y') ?? 'Belum ada data' }}
                    @if ($lastLog) &nbsp;•&nbsp; {{ $lastLog->created_at->format('H:i') }} @endif
                </p>
            </div>
        </div>
        @if (auth()->user()->role->nama === 'superadmin')
            <a href="{{ route('admin.audit-log.index') }}" class="inline-flex mt-10 border border-slate-300 rounded-lg px-4 py-2 text-[11px] text-brand hover:bg-slate-50 transition">Lihat Audit Log</a>
        @endif
    </section>
</div>
@endsection
```

`resources/views/analitik.blade.php`
```blade
@extends('layouts.app')
@section('title', 'Analitik Data Warehouse')
@section('content')
    <!-- Header -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-ink">Analitik Data Warehouse</h1>
            <p class="text-sm text-slate-500">Visualisasi data ringkasan bulanan dari Tabel Fakta (Fact Tables).</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs text-slate-500 font-mono bg-white border border-slate-200 px-2.5 py-1.5 rounded-lg flex items-center gap-1.5 shadow-card">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                ETL Terakhir: Terjadwal Bulanan
            </span>
        </div>
    </div>

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-card hover:shadow-hover transition-shadow flex items-start gap-4">
            <div class="w-10 h-10 rounded-lg bg-gold-light text-gold flex items-center justify-center flex-shrink-0">
                @include('partials.icon', ['name' => 'bank', 'class' => 'w-5 h-5'])
            </div>
            <div class="min-w-0">
                <p class="text-[12.5px] font-semibold text-slate-500 mb-1">Total Biaya Operasional (ETL)</p>
                <p class="text-2xl font-bold text-ink">Rp {{ number_format($totalBiaya, 0, ',', '.') }}</p>
                <p class="text-[12px] text-slate-400 mt-1">Akumulasi seluruh biaya harian disetujui</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-card hover:shadow-hover transition-shadow flex items-start gap-4">
            <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0">
                @include('partials.icon', ['name' => 'wrench', 'class' => 'w-5 h-5'])
            </div>
            <div class="min-w-0">
                <p class="text-[12.5px] font-semibold text-slate-500 mb-1">Total Nilai Pengadaan (ETL)</p>
                <p class="text-2xl font-bold text-ink">Rp {{ number_format($totalPengadaan, 0, ',', '.') }}</p>
                <p class="text-[12px] text-slate-400 mt-1">Akumulasi nilai negosiasi pengadaan</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-card hover:shadow-hover transition-shadow flex items-start gap-4">
            <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0">
                @include('partials.icon', ['name' => 'trend-up', 'class' => 'w-5 h-5'])
            </div>
            <div class="min-w-0">
                <p class="text-[12.5px] font-semibold text-slate-500 mb-1">Total Nilai Buku Aset (Bulan Ini)</p>
                <p class="text-2xl font-bold text-ink">Rp {{ number_format($totalNilaiBuku, 0, ',', '.') }}</p>
                <p class="text-[12px] text-slate-400 mt-1">Sisa nilai buku seluruh amortisasi aktif</p>
            </div>
        </div>
    </div>

    <!-- Charts Layout Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- 1. Biaya Bulanan per Kategori (2/3 width on large screens) -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-card lg:col-span-2">
            <div class="mb-4">
                <h2 class="font-bold text-ink text-[15px]">Biaya Bulanan per Kategori</h2>
                <p class="text-[12px] text-slate-400">Total pengeluaran dikelompokkan per kategori biaya harian</p>
            </div>
            <div class="relative min-h-[300px] flex items-center justify-center">
                @if (empty($biayaLabels))
                    <div class="text-center text-slate-400 py-12">
                        <p class="text-sm font-medium">Belum ada data untuk ditampilkan</p>
                        <p class="text-[11.5px] mt-1">Silakan jalankan perintah <code class="bg-slate-100 px-1 py-0.5 rounded font-mono">php artisan dw:etl</code> di terminal Anda</p>
                    </div>
                @else
                    <canvas id="biayaChart" class="w-full max-h-[300px]"></canvas>
                @endif
            </div>
        </div>

        <!-- 2. Pengadaan per Vendor (1/3 width on large screens) -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-card">
            <div class="mb-4">
                <h2 class="font-bold text-ink text-[15px]">Distribusi Pengadaan per Vendor</h2>
                <p class="text-[12px] text-slate-400">Porsi pembagian nilai pengadaan kepada pihak rekanan</p>
            </div>
            <div class="relative min-h-[300px] flex items-center justify-center">
                @if (empty($vendorLabels))
                    <div class="text-center text-slate-400 py-12">
                        <p class="text-sm font-medium">Belum ada data vendor pengadaan</p>
                        <p class="text-[11.5px] mt-1">Input data negosiasi lalu jalankan ETL</p>
                    </div>
                @else
                    <canvas id="pengadaanChart" class="w-full max-h-[300px]"></canvas>
                @endif
            </div>
        </div>
    </div>

    <!-- 3. Amortisasi Aset bulanan (Full Width) -->
    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-card mb-6">
        <div class="mb-4">
            <h2 class="font-bold text-ink text-[15px]">Tren Amortisasi &amp; Nilai Buku Aset</h2>
            <p class="text-[12px] text-slate-400">Tren kumulatif penyusutan bulanan dibandingkan dengan nilai buku aset</p>
        </div>
        <div class="relative min-h-[250px] flex items-center justify-center">
            @if (empty($amortisasiLabels))
                <div class="text-center text-slate-400 py-12">
                    <p class="text-sm font-medium">Belum ada data tren amortisasi aset</p>
                    <p class="text-[11.5px] mt-1">Input data amortisasi lalu jalankan ETL</p>
                </div>
            @else
                <canvas id="amortisasiChart" class="w-full max-h-[260px]"></canvas>
            @endif
        </div>
    </div>

    @if (!empty($biayaLabels) || !empty($vendorLabels) || !empty($amortisasiLabels))
        <!-- ChartJS Library -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Konfigurasi format rupiah untuk tooltip
                const formatRupiah = (value) => {
                    return new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        minimumFractionDigits: 0
                    }).format(value);
                };

                // 1. Chart Biaya Bulanan
                @if(!empty($biayaLabels))
                const ctxBiaya = document.getElementById('biayaChart').getContext('2d');
                new Chart(ctxBiaya, {
                    type: 'bar',
                    data: {
                        labels: @json($biayaLabels),
                        datasets: @json($biayaDatasets)
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { boxWidth: 12, font: { family: 'Manrope', size: 11 } }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': ' + formatRupiah(context.raw);
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { font: { family: 'Manrope', size: 11 } }
                            },
                            y: {
                                grid: { color: '#f1f5f9' },
                                ticks: {
                                    font: { family: 'Manrope', size: 10 },
                                    callback: function(value) {
                                        return 'Rp ' + value.toLocaleString('id-ID');
                                    }
                                }
                            }
                        }
                    }
                });
                @endif

                // 2. Chart Pengadaan (Doughnut)
                @if(!empty($vendorLabels))
                const ctxPengadaan = document.getElementById('pengadaanChart').getContext('2d');
                new Chart(ctxPengadaan, {
                    type: 'doughnut',
                    data: {
                        labels: @json($vendorLabels),
                        datasets: [{
                            data: @json($vendorTotals),
                            backgroundColor: [
                                '#BF8F3D', '#16233D', '#10b981', '#3b82f6', '#ec4899', '#8b5cf6', '#6b7280'
                            ],
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { boxWidth: 10, font: { family: 'Manrope', size: 10 } }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return ' ' + context.label + ': ' + formatRupiah(context.raw);
                                    }
                                }
                            }
                        },
                        cutout: '65%'
                    }
                });
                @endif

                // 3. Chart Amortisasi (Line Chart)
                @if(!empty($amortisasiLabels))
                const ctxAmortisasi = document.getElementById('amortisasiChart').getContext('2d');
                new Chart(ctxAmortisasi, {
                    type: 'line',
                    data: {
                        labels: @json($amortisasiLabels),
                        datasets: [
                            {
                                label: 'Nilai Buku Aset',
                                data: @json($nilaiBukuData),
                                borderColor: '#16233D',
                                backgroundColor: 'rgba(22, 35, 61, 0.05)',
                                fill: true,
                                tension: 0.3,
                                borderWidth: 2.5,
                                pointBackgroundColor: '#16233D'
                            },
                            {
                                label: 'Nilai Penyusutan Bulanan',
                                data: @json($penyusutanData),
                                borderColor: '#BF8F3D',
                                backgroundColor: 'transparent',
                                tension: 0.3,
                                borderWidth: 2,
                                borderDash: [5, 5],
                                pointBackgroundColor: '#BF8F3D'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { boxWidth: 12, font: { family: 'Manrope', size: 11 } }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': ' + formatRupiah(context.raw);
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { font: { family: 'Manrope', size: 11 } }
                            },
                            y: {
                                grid: { color: '#f1f5f9' },
                                ticks: {
                                    font: { family: 'Manrope', size: 10 },
                                    callback: function(value) {
                                        return 'Rp ' + value.toLocaleString('id-ID');
                                    }
                                }
                            }
                        }
                    }
                });
                @endif
            });
        </script>
    @endif
@endsection
```

`resources/views/admin/users/index.blade.php`
```blade
@extends('layouts.app')
@section('title', 'Manajemen User')
@section('content')
    <div class="mb-5">
        <p class="text-[12px] font-semibold uppercase tracking-wide text-gold mb-0.5">Pengaturan</p>
        <h1 class="text-xl font-bold text-ink">Manajemen User</h1>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 shadow-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-left text-[12px] uppercase tracking-wide text-slate-500">
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">User</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Role</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Status</th>
                            <th class="px-4 py-3 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($items as $u)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-brand to-brand-light text-white flex items-center justify-center text-xs font-semibold flex-shrink-0">
                                            {{ strtoupper(substr($u->nama_lengkap, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0 leading-tight">
                                            <p class="text-ink font-medium truncate">{{ $u->nama_lengkap }}</p>
                                            <p class="text-slate-400 text-[12px] font-mono truncate">{{ $u->username }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600">{{ $u->role->label }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span @class([
                                        'px-2.5 py-1 rounded-full text-xs font-medium inline-flex items-center gap-1',
                                        'bg-emerald-50 text-emerald-700' => $u->is_active,
                                        'bg-slate-100 text-slate-500' => !$u->is_active,
                                    ])>
                                        <span @class(['w-1.5 h-1.5 rounded-full', 'bg-emerald-500' => $u->is_active, 'bg-slate-400' => !$u->is_active])></span>
                                        {{ $u->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-3 text-[13px]">
                                        <form method="POST" action="{{ route('admin.users.reset-password', $u) }}" class="inline" onsubmit="return confirm('Reset password user ini?')">
                                            @csrf
                                            <button class="text-brand font-medium hover:underline">Reset Password</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.users.destroy', $u) }}" class="inline" onsubmit="return confirm('Hapus user ini?')">
                                            @csrf @method('DELETE')
                                            <button class="text-slate-400 hover:text-red-600 transition">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 shadow-card p-5 h-fit">
            <h2 class="font-semibold text-ink mb-3 flex items-center gap-2">
                @include('partials.icon', ['name' => 'plus', 'class' => 'w-4 h-4 text-gold'])
                Tambah User
            </h2>
            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-3">
                @csrf
                <input name="username" placeholder="Username" required class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:border-brand focus:ring-1 focus:ring-brand transition">
                <input name="nama_lengkap" placeholder="Nama Lengkap" required class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:border-brand focus:ring-1 focus:ring-brand transition">
                <input name="email" placeholder="Email (opsional)" class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:border-brand focus:ring-1 focus:ring-brand transition">
                <input name="jabatan" placeholder="Jabatan" class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:border-brand focus:ring-1 focus:ring-brand transition">
                <input name="bagian" placeholder="Bagian" class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:border-brand focus:ring-1 focus:ring-brand transition">
                <select name="role_id" required class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:border-brand focus:ring-1 focus:ring-brand transition">
                    @foreach ($roles as $r) <option value="{{ $r->id }}">{{ $r->label }}</option> @endforeach
                </select>
                <button class="w-full bg-brand text-white text-sm font-medium px-4 py-2.5 rounded-lg hover:bg-brand-light transition">Buat User</button>
            </form>
        </div>
    </div>
@endsection
```

`resources/views/admin/roles/index.blade.php`
```blade
@extends('layouts.app')
@section('title', 'Manajemen Role')
@section('content')
    <div class="mb-5">
        <p class="text-[12px] font-semibold uppercase tracking-wide text-gold mb-0.5">Pengaturan</p>
        <h1 class="text-xl font-bold text-ink">Matriks Role &amp; Permission</h1>
    </div>
    <div class="space-y-4">
        @foreach ($roles as $role)
            <form method="POST" action="{{ route('admin.roles.permissions', $role) }}" class="bg-white rounded-xl border border-slate-200 shadow-card p-5">
                @csrf
                <div class="flex items-center gap-2.5 mb-4">
                    <div class="w-9 h-9 rounded-lg bg-gold-light text-gold flex items-center justify-center flex-shrink-0">
                        @include('partials.icon', ['name' => 'shield', 'class' => 'w-[18px] h-[18px]'])
                    </div>
                    <h2 class="font-semibold text-ink">{{ $role->label }} <span class="text-slate-400 text-sm font-normal font-mono">({{ $role->nama }})</span></h2>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-2 mb-4">
                    @foreach ($permKeys as $key)
                        @php $perm = $role->permissions->firstWhere('perm_key', $key); @endphp
                        <label @class([
                            'flex items-center gap-2 border rounded-lg px-3 py-2 text-[12.5px] cursor-pointer transition',
                            'border-brand bg-brand/[.04] text-ink font-medium' => $perm?->can_write,
                            'border-slate-200 text-slate-500 hover:border-slate-300' => !$perm?->can_write,
                        ])>
                            <input type="checkbox" name="write_{{ $key }}" value="1" @checked($perm?->can_write) class="rounded border-slate-300">
                            {{ $key }}
                        </label>
                    @endforeach
                </div>
                <button class="inline-flex items-center gap-1.5 bg-brand text-white text-sm font-medium px-4 py-2.5 rounded-lg hover:bg-brand-light transition">
                    @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-4 h-4'])
                    Simpan
                </button>
            </form>
        @endforeach
    </div>
@endsection
```

`resources/views/panduan/index.blade.php`
```blade
@extends('layouts.app')
@section('title', 'Panduan')
@section('content')
    <div class="mb-5">
        <p class="text-[12px] font-semibold uppercase tracking-wide text-gold mb-0.5">Rapat &amp; Referensi</p>
        <h1 class="text-xl font-bold text-ink">Panduan Penggunaan</h1>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-4">
            @forelse ($items->groupBy('kategori') as $kategori => $group)
                <div class="bg-white rounded-xl border border-slate-200 shadow-card p-5">
                    <h2 class="font-semibold text-ink mb-3 flex items-center gap-2">
                        @include('partials.icon', ['name' => 'book', 'class' => 'w-4 h-4 text-gold'])
                        {{ $kategori }}
                    </h2>
                    <div class="divide-y divide-slate-100">
                        @foreach ($group as $p)
                            <details class="group py-2.5">
                                <summary class="cursor-pointer flex items-center justify-between gap-3 text-sm font-medium text-ink hover:text-brand transition">
                                    {{ $p->judul }}
                                    <span class="chev text-slate-400 transition-transform flex-shrink-0">
                                        @include('partials.icon', ['name' => 'chevron-down', 'class' => 'w-4 h-4'])
                                    </span>
                                </summary>
                                <div class="text-sm text-slate-600 mt-2.5 leading-relaxed">{!! nl2br(e($p->konten)) !!}</div>
                                <form method="POST" action="{{ route('panduan.destroy', $p) }}" class="mt-2.5" onsubmit="return confirm('Hapus panduan ini?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs text-slate-400 hover:text-red-600 transition">Hapus panduan ini</button>
                                </form>
                            </details>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-xl border border-slate-200 shadow-card p-10 text-center">
                    <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                        @include('partials.icon', ['name' => 'book', 'class' => 'w-5 h-5'])
                    </div>
                    <p class="text-slate-400 text-sm">Belum ada panduan.</p>
                </div>
            @endforelse
        </div>
        <div class="bg-white rounded-xl border border-slate-200 shadow-card p-5 h-fit">
            <h2 class="font-semibold text-ink mb-3 flex items-center gap-2">
                @include('partials.icon', ['name' => 'plus', 'class' => 'w-4 h-4 text-gold'])
                Tambah Panduan
            </h2>
            <form method="POST" action="{{ route('panduan.store') }}" class="space-y-3">
                @csrf
                <input name="judul" placeholder="Judul" required class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:border-brand focus:ring-1 focus:ring-brand transition">
                <input name="kategori" placeholder="Kategori" class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:border-brand focus:ring-1 focus:ring-brand transition">
                <textarea name="konten" placeholder="Konten" rows="4" class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:border-brand focus:ring-1 focus:ring-brand transition"></textarea>
                <input type="number" name="urutan" placeholder="Urutan" class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:border-brand focus:ring-1 focus:ring-brand transition">
                <button class="w-full bg-brand text-white text-sm font-medium px-4 py-2.5 rounded-lg hover:bg-brand-light transition">Simpan</button>
            </form>
        </div>
    </div>
@endsection
```

`resources/views/risalah/index.blade.php`
```blade
@extends('layouts.app')
@section('title', 'Risalah Rapat')
@section('content')
    <div class="flex items-start justify-between mb-5">
        <div>
            <p class="text-[12px] font-semibold uppercase tracking-wide text-gold mb-0.5">Rapat &amp; Referensi</p>
            <h1 class="text-xl font-bold text-ink">Risalah Rapat</h1>
        </div>
        <a href="{{ route('risalah.create') }}"
           class="inline-flex items-center gap-1.5 bg-brand text-white text-sm font-medium px-4 py-2.5 rounded-lg hover:bg-brand-light transition shadow-card">
            @include('partials.icon', ['name' => 'plus', 'class' => 'w-4 h-4'])
            Tambah
        </a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-left text-[12px] uppercase tracking-wide text-slate-500">
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Tanggal</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Judul</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Pemimpin</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Tempat</th>
                        <th class="px-4 py-3 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($items as $r)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-4 py-3 text-ink whitespace-nowrap">{{ $r->tanggal->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-ink font-medium">{{ $r->judul }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $r->pemimpin }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $r->tempat }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-3 text-[13px]">
                                    <a href="{{ route('risalah.edit', $r) }}" class="text-brand font-medium hover:underline">Ubah</a>
                                    <form method="POST" action="{{ route('risalah.destroy', $r) }}" class="inline" onsubmit="return confirm('Hapus risalah ini?')">
                                        @csrf @method('DELETE')
                                        <button class="text-slate-400 hover:text-red-600 transition">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-14 text-center">
                                <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                                    @include('partials.icon', ['name' => 'book', 'class' => 'w-5 h-5'])
                                </div>
                                <p class="text-slate-400 text-sm mb-2">Belum ada risalah rapat.</p>
                                <a href="{{ route('risalah.create') }}" class="text-brand text-sm font-medium hover:underline">+ Tambah risalah pertama</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $items->links() }}</div>
@endsection
```

`resources/views/risalah/form.blade.php`
```blade
@extends('layouts.app')
@section('title', ($item ? 'Ubah' : 'Tambah') . ' Risalah Rapat')
@section('content')
    <p class="text-[12px] font-semibold uppercase tracking-wide text-gold mb-0.5">Rapat &amp; Referensi</p>
    <h1 class="text-xl font-bold text-ink mb-5">{{ $item ? 'Ubah' : 'Tambah' }} Risalah Rapat</h1>

    <form method="POST" action="{{ $item ? route('risalah.update', $item) : route('risalah.store') }}"
          class="bg-white rounded-xl border border-slate-200 shadow-card p-6 max-w-2xl space-y-4">
        @csrf
        @if ($item) @method('PUT') @endif
        @foreach (['nomor'=>'text','judul'=>'text','tanggal'=>'date','waktu'=>'text','tempat'=>'text','pemimpin'=>'text','peserta'=>'textarea','agenda'=>'textarea','pembahasan'=>'textarea','keputusan'=>'textarea','tindak_lanjut'=>'textarea'] as $field => $type)
            <div>
                <label class="block text-[13px] font-medium mb-1.5 text-slate-700">{{ ucwords(str_replace('_',' ',$field)) }}</label>
                @if ($type === 'textarea')
                    <textarea name="{{ $field }}" rows="3" class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:border-brand focus:ring-1 focus:ring-brand transition">{{ old($field, $item?->$field) }}</textarea>
                @else
                    <input type="{{ $type }}" name="{{ $field }}" value="{{ old($field, $item?->$field) }}" class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:border-brand focus:ring-1 focus:ring-brand transition">
                @endif
            </div>
        @endforeach
        <div class="pt-6 mt-2 border-t border-slate-100 flex gap-3">
            <button class="bg-brand text-white text-sm font-medium px-5 py-2.5 rounded-lg hover:bg-brand-light transition">Simpan</button>
            <a href="{{ route('risalah.index') }}" class="text-sm text-slate-500 px-5 py-2.5 hover:text-ink transition">Batal</a>
        </div>
    </form>
@endsection
```

`routes/web.php` — EDIT — versi final, tambahkan dashboard/analitik/panduan/risalah + lengkapi group admin
```php
<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\PanduanController;
use App\Http\Controllers\RisalahRapatController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/ganti-password-wajib', [LoginController::class, 'forceChangeForm'])->name('password.force-change');
    Route::post('/ganti-password-wajib', [LoginController::class, 'forceChange'])->name('password.force-change.submit');

    Route::get('/', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/analitik', [App\Http\Controllers\AnalyticsController::class, 'index'])->name('analitik');
    Route::get('/analitik/biaya/{kategori}', [App\Http\Controllers\AnalyticsController::class, 'detailKategori'])
    ->name('analitik.detail-kategori');

    // Mesin CRUD generik untuk 20 modul (lihat config/modules.php) --
    // setara routing dinamis {resource}?action=... di portum.py.
    Route::prefix('modul/{key}')->name('modul.')->group(function () {
        Route::get('/', [ModuleController::class, 'index'])->name('index');
        Route::get('/tambah', [ModuleController::class, 'create'])->name('create');
        Route::post('/', [ModuleController::class, 'store'])->name('store');
        Route::get('/{id}/ubah', [ModuleController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ModuleController::class, 'update'])->name('update');
        Route::delete('/{id}', [ModuleController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/setujui', [ModuleController::class, 'approve'])->name('approve');
        Route::post('/{id}/tolak', [ModuleController::class, 'reject'])->name('reject');
    });

    Route::get('/panduan', [PanduanController::class, 'index'])->name('panduan.index');
    Route::post('/panduan', [PanduanController::class, 'store'])->name('panduan.store');
    Route::put('/panduan/{panduan}', [PanduanController::class, 'update'])->name('panduan.update');
    Route::delete('/panduan/{panduan}', [PanduanController::class, 'destroy'])->name('panduan.destroy');

    Route::resource('risalah', RisalahRapatController::class)->except(['show']);

    Route::prefix('admin')->name('admin.')->middleware('superadmin')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::post('/roles/{role}/permissions', [RoleController::class, 'updatePermissions'])->name('roles.permissions');

        Route::get('/audit-log', [AuditLogController::class, 'index'])->name('audit-log.index');
    });
});
```

```bash
php artisan route:list
php artisan test
git add app/Http/Controllers/DashboardController.php app/Http/Controllers/AnalyticsController.php app/Http/Controllers/Admin/UserController.php app/Http/Controllers/Admin/RoleController.php app/Models/Panduan.php app/Models/RisalahRapat.php app/Http/Controllers/PanduanController.php app/Http/Controllers/RisalahRapatController.php resources/views/dashboard.blade.php resources/views/analitik.blade.php resources/views/admin resources/views/panduan resources/views/risalah routes/web.php
git commit -m "feat(admin): implement dashboard and administration"
git push -u origin feature/dashboard-admin
```
PR → develop. Reviewer **Dirli**. Merge → semua pull.

---

# STEP 13 — Dirli: Queue + Distributed

```bash
git checkout develop
git pull origin develop
git checkout -b feature/distributed
```

`database/migrations/2026_01_01_000012_create_notifications_table.php`
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
```

`app/Notifications/JatuhTempoNotification.php`
```php
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Queue\ShouldQueue;

class JatuhTempoNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $judul,
        public string $kategori,
        public string $tanggalJatuhTempo,
        public string $catatan = '',
    ) {}

    // Dikirim lewat channel database (muncul di dashboard) + mail (opsional,
    // aktif hanya kalau user punya email). Dijalankan di queue worker,
    // bukan di request HTTP yang membuka dashboard.
    public function via($notifiable): array
    {
        return $notifiable->email ? ['database', 'mail'] : ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'judul' => $this->judul,
            'kategori' => $this->kategori,
            'tanggal_jatuh_tempo' => $this->tanggalJatuhTempo,
            'catatan' => $this->catatan,
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Reminder jatuh tempo: {$this->judul}")
            ->line("Kategori: {$this->kategori}")
            ->line("Jatuh tempo: {$this->tanggalJatuhTempo}")
            ->line($this->catatan ?: 'Segera tindak lanjuti sebelum jatuh tempo.');
    }
}
```

`app/Jobs/CheckJatuhTempoReminderJob.php`
```php
<?php

namespace App\Jobs;

use App\Models\AsInvoiceSewa;
use App\Models\AsPks;
use App\Models\PgReminder;
use App\Models\Role;
use App\Models\User;
use App\Notifications\JatuhTempoNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

// CPMK Sistem Komputasi Terdistribusi: job ini dijalankan oleh queue worker
// terpisah dari proses web, dipicu oleh Task Scheduler (lihat routes/console.php).
// Di versi Python lama, reminder cuma dihitung on-the-fly saat halaman Dashboard
// dibuka -- kalau tidak ada yang buka dashboard, reminder tidak pernah "terkirim".
class CheckJatuhTempoReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const REMINDER_HARI = 90;

    public function handle(): void
    {
         Log::info('[distributed-proof] CheckJatuhTempoReminderJob diproses', [
            'hostname' => gethostname(),
            'pid' => getmypid(),
            'connection' => config('queue.default'),
        ]);

        $batas = now()->addDays(self::REMINDER_HARI)->toDateString();
        $penerima = User::whereHas('role', fn ($q) => $q->whereIn('nama', ['pimpinan', 'aset', 'pengadaan', 'superadmin']))
            ->where('is_active', true)
            ->get();

        PgReminder::where('status', 'Aktif')
            ->whereDate('tanggal_jatuh_tempo', '<=', $batas)
            ->each(fn ($r) => $this->notify($penerima, $r->judul, $r->kategori, $r->tanggal_jatuh_tempo, $r->catatan));

        AsPks::whereIn('status', ['Aktif', 'Akan Jatuh Tempo'])
            ->whereDate('jatuh_tempo', '<=', $batas)
            ->each(fn ($p) => $this->notify($penerima, "PKS: {$p->judul}", 'PKS', $p->jatuh_tempo, "Vendor: {$p->vendor}"));

        AsInvoiceSewa::where('status', '!=', 'Lunas')
            ->whereDate('jatuh_tempo', '<=', $batas)
            ->each(fn ($i) => $this->notify($penerima, "Invoice Sewa: {$i->no_invoice}", 'Sewa', $i->jatuh_tempo, "Vendor: {$i->vendor}"));
    }

    private function notify($penerima, string $judul, string $kategori, $tanggal, string $catatan): void
    {
        foreach ($penerima as $user) {
            $user->notify(new JatuhTempoNotification($judul, $kategori, (string) $tanggal, $catatan));
        }
    }
}
```

`app/Jobs/GenerateLaporanBiayaBulananJob.php`
```php
<?php

namespace App\Jobs;

use App\Models\UmBiayaHarian;
use App\Models\UmGenerateLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

// CPMK Sistem Komputasi Terdistribusi: rekap biaya bulanan sengaja dipindah
// ke background job, bukan dihitung langsung saat user klik "Generate" di
// halaman (seperti versi Python) -- supaya request HTTP tidak nge-block
// kalau datanya sudah besar, dan bisa di-retry otomatis kalau gagal.
class GenerateLaporanBiayaBulananJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public string $periodeAwal,
        public string $periodeAkhir,
        public ?string $kategori = null,
        public ?string $dibuatOleh = null,
    ) {}

    public function handle(): void
    {
        Log::info('[distributed-proof] GenerateLaporanBiayaBulananJob diproses', [
            'hostname' => gethostname(),
            'pid' => getmypid(),
            'connection' => config('queue.default'),
        ]);

        $query = UmBiayaHarian::whereBetween('tanggal', [$this->periodeAwal, $this->periodeAkhir])
            ->where('approval_status', 'Disetujui');

        if ($this->kategori) {
            $query->where('kategori', $this->kategori);
        }

        $items = $query->get();

        UmGenerateLog::create([
            'periode_awal' => $this->periodeAwal,
            'periode_akhir' => $this->periodeAkhir,
            'kategori' => $this->kategori,
            'jumlah_item' => $items->count(),
            'total' => $items->sum('jumlah'),
            'dibuat_oleh' => $this->dibuatOleh,
        ]);
    }
}
```

```bash
php artisan migrate
php artisan queue:work --once
php artisan test
git add database/migrations/2026_01_01_000012_create_notifications_table.php app/Notifications app/Jobs
git commit -m "feat(distributed): implement queued processing"
git push -u origin feature/distributed
```
PR → develop. Reviewer **Zahra**. Merge → semua pull.

---

# STEP 14 — Dirli: Data Warehouse + ETL

```bash
git checkout develop
git pull origin develop
git checkout -b feature/data-warehouse
```

`database/migrations/2026_01_01_000010_create_data_warehouse_tables.php`
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Star schema untuk CPMK Data Warehouse (pengganti Interaksi Manusia Komputer).
    // Tabel dim_* dan fact_* ini diisi lewat proses ETL terjadwal (Artisan
    // command via Task Scheduler) yang menarik data dari tabel OLTP di atas
    // (um_biaya_harian, as_amortisasi, pg_penawaran/pg_negosiasi/pg_spk),
    // meng-agregasi per bulan, lalu memuatnya ke fact table di bawah.
    public function up(): void
    {
        Schema::create('dim_waktu', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->unique();
            $table->integer('tahun');
            $table->integer('bulan');
            $table->string('nama_bulan');
            $table->integer('kuartal');
        });

        Schema::create('dim_unit_kerja', function (Blueprint $table) {
            $table->id();
            $table->string('nama_unit')->unique();
        });

        Schema::create('dim_kategori', function (Blueprint $table) {
            $table->id();
            $table->string('jenis_kategori'); // biaya | aset | pengadaan
            $table->string('nama_kategori');
        });

        Schema::create('dim_vendor', function (Blueprint $table) {
            $table->id();
            $table->string('nama_vendor')->unique();
        });

        Schema::create('fact_biaya_bulanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dim_waktu_id')->constrained('dim_waktu');
            $table->foreignId('dim_unit_kerja_id')->nullable()->constrained('dim_unit_kerja');
            $table->foreignId('dim_kategori_id')->nullable()->constrained('dim_kategori');
            $table->decimal('total_biaya', 18, 2)->default(0);
            $table->integer('jumlah_transaksi')->default(0);
            $table->timestamps();
        });

        Schema::create('fact_amortisasi_aset', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dim_waktu_id')->constrained('dim_waktu');
            $table->foreignId('as_amortisasi_id')->constrained('as_amortisasi');
            $table->decimal('nilai_penyusutan_bulan', 18, 2)->default(0);
            $table->decimal('akumulasi', 18, 2)->default(0);
            $table->decimal('nilai_buku', 18, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('fact_pengadaan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dim_waktu_id')->constrained('dim_waktu');
            $table->foreignId('dim_vendor_id')->nullable()->constrained('dim_vendor');
            $table->foreignId('dim_kategori_id')->nullable()->constrained('dim_kategori');
            $table->decimal('total_nilai', 18, 2)->default(0);
            $table->integer('jumlah_transaksi')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fact_pengadaan');
        Schema::dropIfExists('fact_amortisasi_aset');
        Schema::dropIfExists('fact_biaya_bulanan');
        Schema::dropIfExists('dim_vendor');
        Schema::dropIfExists('dim_kategori');
        Schema::dropIfExists('dim_unit_kerja');
        Schema::dropIfExists('dim_waktu');
    }
};
```

`app/Models/DimKategori.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DimKategori extends Model
{
    protected $table = 'dim_kategori';

    public $timestamps = false;

    protected $fillable = [
        'jenis_kategori',
        'nama_kategori'
    ];
}
```

`app/Models/DimUnitKerja.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DimUnitKerja extends Model
{
    protected $table = 'dim_unit_kerja';

    public $timestamps = false;

    protected $fillable = [
        'nama_unit'
    ];
}
```

`app/Models/DimVendor.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DimVendor extends Model
{
    protected $table = 'dim_vendor';

    public $timestamps = false;

    protected $fillable = [
        'nama_vendor'
    ];
}
```

`app/Models/DimWaktu.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DimWaktu extends Model
{
    protected $table = 'dim_waktu';

    public $timestamps = false;

    protected $fillable = [
        'tanggal',
        'tahun',
        'bulan',
        'nama_bulan',
        'kuartal'
    ];

    protected function casts(): array
    {
        return [
        'tanggal' => 'date'
        ];
    }
}
```

`app/Models/FactAmortisasiAset.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FactAmortisasiAset extends Model
{
    protected $table = 'fact_amortisasi_aset';

    protected $fillable = [
        'dim_waktu_id',
        'as_amortisasi_id',
        'nilai_penyusutan_bulan',
        'akumulasi',
        'nilai_buku'
    ];

    protected function casts(): array
    {
        return [
        'nilai_penyusutan_bulan' => 'decimal:2',
        'akumulasi' => 'decimal:2',
        'nilai_buku' => 'decimal:2'
        ];
    }

    public function waktu()
    {
        return $this->belongsTo(DimWaktu::class, 'dim_waktu_id');
    }

    public function amortisasi()
    {
        return $this->belongsTo(AsAmortisasi::class, 'as_amortisasi_id');
    }
}
```

`app/Models/FactBiayaBulanan.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FactBiayaBulanan extends Model
{
    protected $table = 'fact_biaya_bulanan';

    protected $fillable = [
        'dim_waktu_id',
        'dim_unit_kerja_id',
        'dim_kategori_id',
        'total_biaya',
        'jumlah_transaksi'
    ];

    protected function casts(): array
    {
        return [
        'total_biaya' => 'decimal:2'
        ];
    }

    public function waktu()
    {
        return $this->belongsTo(DimWaktu::class, 'dim_waktu_id');
    }

    public function unitKerja()
    {
        return $this->belongsTo(DimUnitKerja::class, 'dim_unit_kerja_id');
    }

    public function kategori()
    {
        return $this->belongsTo(DimKategori::class, 'dim_kategori_id');
    }
}
```

`app/Models/FactPengadaan.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FactPengadaan extends Model
{
    protected $table = 'fact_pengadaan';

    protected $fillable = [
        'dim_waktu_id',
        'dim_vendor_id',
        'dim_kategori_id',
        'total_nilai',
        'jumlah_transaksi'
    ];

    protected function casts(): array
    {
        return [
        'total_nilai' => 'decimal:2'
        ];
    }

    public function waktu()
    {
        return $this->belongsTo(DimWaktu::class, 'dim_waktu_id');
    }

    public function vendor()
    {
        return $this->belongsTo(DimVendor::class, 'dim_vendor_id');
    }

    public function kategori()
    {
        return $this->belongsTo(DimKategori::class, 'dim_kategori_id');
    }
}
```

`app/Console/Commands/EtlDataWarehouseCommand.php`
```php
<?php

namespace App\Console\Commands;

use App\Models\AsAmortisasi;
use App\Models\DimKategori;
use App\Models\DimUnitKerja;
use App\Models\DimVendor;
use App\Models\DimWaktu;
use App\Models\FactAmortisasiAset;
use App\Models\FactBiayaBulanan;
use App\Models\FactPengadaan;
use App\Models\PgNegosiasi;
use App\Models\UmBiayaHarian;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

// CPMK Data Warehouse: proses ETL (Extract dari tabel OLTP -> Transform
// agregasi bulanan -> Load ke fact table) untuk 3 domain: biaya, amortisasi
// aset, dan pengadaan. Dijadwalkan bulanan lewat routes/console.php.
class EtlDataWarehouseCommand extends Command
{
    protected $signature = 'dw:etl {--bulan=} {--tahun=}';
    protected $description = 'Jalankan ETL bulanan ke tabel data warehouse (fact_biaya_bulanan, fact_amortisasi_aset, fact_pengadaan)';

    public function handle(): int
    {
        $tahun = (int) ($this->option('tahun') ?: now()->subMonth()->year);
        $bulan = (int) ($this->option('bulan') ?: now()->subMonth()->month);
        $periodeAwal = Carbon::create($tahun, $bulan, 1)->startOfMonth();
        $periodeAkhir = $periodeAwal->copy()->endOfMonth();

        $this->info("ETL periode {$periodeAwal->translatedFormat('F Y')}");

        $dimWaktu = DimWaktu::updateOrCreate(
            ['tanggal' => $periodeAwal->toDateString()],
            [
                'tahun' => $tahun,
                'bulan' => $bulan,
                'nama_bulan' => $periodeAwal->translatedFormat('F'),
                'kuartal' => (int) ceil($bulan / 3),
            ]
        );

        $this->etlBiaya($dimWaktu, $periodeAwal, $periodeAkhir);
        $this->etlAmortisasi($dimWaktu, $periodeAwal);
        $this->etlPengadaan($dimWaktu, $periodeAwal, $periodeAkhir);

        $this->info('ETL selesai.');
        return self::SUCCESS;
    }

    private function etlBiaya(DimWaktu $dimWaktu, Carbon $awal, Carbon $akhir): void
    {
        UmBiayaHarian::whereBetween('tanggal', [$awal, $akhir])
            ->where('approval_status', 'Disetujui')
            ->selectRaw('kategori, count(*) as jml, sum(jumlah) as total')
            ->groupBy('kategori')
            ->get()
            ->each(function ($row) use ($dimWaktu) {
                $kategori = DimKategori::firstOrCreate(
                    ['jenis_kategori' => 'biaya', 'nama_kategori' => $row->kategori ?: 'Lainnya']
                );
                FactBiayaBulanan::updateOrCreate(
                    ['dim_waktu_id' => $dimWaktu->id, 'dim_kategori_id' => $kategori->id, 'dim_unit_kerja_id' => null],
                    ['total_biaya' => $row->total, 'jumlah_transaksi' => $row->jml]
                );
            });
        $this->line('  - fact_biaya_bulanan diperbarui');
    }

    private function etlAmortisasi(DimWaktu $dimWaktu, Carbon $periode): void
    {
        AsAmortisasi::all()->each(function (AsAmortisasi $a) use ($dimWaktu, $periode) {
            FactAmortisasiAset::updateOrCreate(
                ['dim_waktu_id' => $dimWaktu->id, 'as_amortisasi_id' => $a->id],
                [
                    'nilai_penyusutan_bulan' => $a->nilai_per_bulan,
                    'akumulasi' => $a->akumulasi,
                    'nilai_buku' => $a->nilai_buku,
                ]
            );
        });
        $this->line('  - fact_amortisasi_aset diperbarui');
    }

    private function etlPengadaan(DimWaktu $dimWaktu, Carbon $awal, Carbon $akhir): void
    {
        PgNegosiasi::whereBetween('tanggal', [$awal, $akhir])
            ->selectRaw('vendor, count(*) as jml, sum(nilai_nego) as total')
            ->groupBy('vendor')
            ->get()
            ->each(function ($row) use ($dimWaktu) {
                $vendor = DimVendor::firstOrCreate(['nama_vendor' => $row->vendor]);
                FactPengadaan::updateOrCreate(
                    ['dim_waktu_id' => $dimWaktu->id, 'dim_vendor_id' => $vendor->id, 'dim_kategori_id' => null],
                    ['total_nilai' => $row->total, 'jumlah_transaksi' => $row->jml]
                );
            });
        $this->line('  - fact_pengadaan diperbarui');
    }
}
```

`routes/console.php`
```php
<?php

use App\Jobs\CheckJatuhTempoReminderJob;
use App\Jobs\GenerateLaporanBiayaBulananJob;
use Illuminate\Support\Facades\Schedule;

// Laravel 13 Task Scheduler (menggantikan app/Console/Kernel.php lama).
// Jalankan `php artisan schedule:work` saat development, atau daftarkan
// satu baris cron `* * * * * php artisan schedule:run` di server produksi.

// CPMK Sistem Komputasi Terdistribusi: scheduler ini yang men-dispatch job
// ke queue -- worker (`php artisan queue:work`) yang benar-benar
// mengeksekusinya, proses terpisah dari scheduler maupun dari web server.
Schedule::job(new CheckJatuhTempoReminderJob)
    ->dailyAt('07:00')
    ->name('cek-reminder-jatuh-tempo')
    ->withoutOverlapping();

// CPMK Data Warehouse: ETL bulanan, jalan tanggal 1 tiap bulan untuk
// data bulan sebelumnya (default command tanpa opsi = bulan lalu).
Schedule::command('dw:etl')
    ->monthlyOn(1, '02:00')
    ->name('etl-data-warehouse')
    ->withoutOverlapping();

// CPMK Blockchain: verifikasi rantai hash audit_log tiap malam, supaya
// manipulasi data langsung di database (bypass Eloquent) cepat ketahuan.
Schedule::command('audit:verify-chain')
    ->dailyAt('23:30')
    ->name('verifikasi-rantai-audit')
    ->emailOutputOnFailure(config('mail.admin_address', 'admin@banksulteng.co.id'));
```

```bash
php artisan migrate
php artisan test
# jalankan ETL sesuai signature command aktual, contoh:
php artisan dw:etl
git add database/migrations/2026_01_01_000010_create_data_warehouse_tables.php app/Models/Dim* app/Models/Fact* app/Console/Commands/EtlDataWarehouseCommand.php routes/console.php
git commit -m "feat(dw): implement warehouse models and ETL"
git push -u origin feature/data-warehouse
```
PR → develop. Reviewer **Marsya**. Merge → semua pull.

---

# STEP 15 — Zahra: Regression + Race Condition

```bash
git checkout develop
git pull origin develop
git checkout -b test/regression
```

`tests/Feature/RaceConditionTest.php`
```php
<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\RolePermission;
use App\Models\UmBiayaHarian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RaceConditionTest extends TestCase
{
    use RefreshDatabase;

    public function test_double_approve_should_fail(): void
    {
        // 1. Buat role dengan permission write ke modul umum_rt (biaya_harian)
        $role = Role::create([
            'nama'  => 'pimpinan',
            'label' => 'Pimpinan Divisi',
        ]);

        RolePermission::create([
            'role_id'   => $role->id,
            'perm_key'  => 'umum_rt',  // perm_key sesuai config/modules.php biaya_harian
            'can_write' => true,
        ]);

        // 2. Buat 3 user: 1 maker + 2 checker
        $maker = User::factory()->create([
            'username' => 'adol',
            'role_id'  => $role->id,
        ]);

        $checker1 = User::factory()->create([
            'username' => 'pimpinan',
            'role_id'  => $role->id,
        ]);

        $checker2 = User::factory()->create([
            'username' => 'checker2',
            'role_id'  => $role->id,
        ]);

        // 3. Buat transaksi sebagai maker (status: Diajukan)
        $transaksi = UmBiayaHarian::create([
            'tanggal'         => now()->toDateString(),
            'jumlah'          => 100000,
            'kategori'        => 'BBM',
            'uraian'          => 'Test race condition',  // field yang benar: 'uraian' bukan 'keterangan'
            'maker_id'        => $maker->id,
            'approval_status' => 'Diajukan',
        ]);

        // 4. Checker1 approve duluan — harus BERHASIL
        $resp1 = $this->actingAs($checker1)
            ->post("/modul/biaya_harian/{$transaksi->id}/setujui");

        $resp1->assertRedirect(); // 302 redirect = berhasil

        // 5. Checker2 coba approve transaksi yang SAMA — harus DITOLAK
        //    karena approval_status sudah bukan 'Diajukan' lagi
        $resp2 = $this->actingAs($checker2)
            ->post("/modul/biaya_harian/{$transaksi->id}/setujui");

        $resp2->assertStatus(403); // ApprovalPolicy menolak karena status != 'Diajukan'
    }
}
```
File test lain (`MakerCheckerTest`, `ValidasiInputTest`, `AuditHasherTest`, `AmortisasiCalculatorTest` — yang terakhir ini dari track CPMK Penjaminan Mutu Zahra yang sudah kalian kerjakan terpisah) sudah ada; step ini fokus jalankan ulang semuanya sebagai regresi penuh.

```bash
php artisan test
git add tests/Feature/RaceConditionTest.php
git commit -m "test: complete regression and concurrency coverage"
git push -u origin test/regression
```
PR → develop. Reviewer **Dirli**. Semua failure dicatat sebagai Issue bila belum selesai.

---

# STEP 16 — Marsya: Final Integration

```bash
git checkout develop
git pull origin develop
php artisan optimize:clear
php artisan migrate:status
php artisan test
npm run build
git status
```
Marsya buka PR `develop → main`.

# STEP 17 — Bertiga: Final Smoke Test
Ketiganya menjalankan aplikasi lokal (`php artisan serve`), coba login, buat data di beberapa modul, cek dashboard/analitik, cek audit log — approve di GitHub PR kalau semua jalan normal.

# STEP 18 — Marsya: develop → main + tag

```bash
git checkout main
git pull origin main
git tag -a v1.0.0 -m "PORTUM v1.0.0"
git push origin v1.0.0
```

---

# Sisanya (tidak berubah dari dokumen asli)
Bagian **Aturan Anti-Conflict**, **Ritual Setelah Setiap Merge**, **Cara Menghindari `git add .` yang Berbahaya**, **Logbook yang Harus Dicatat**, **Definition of Done**, dan **Bukti untuk MK Manajemen Proyek** di panduan Word asli tetap berlaku persis seperti sebelumnya — tidak ada yang perlu diubah di bagian itu.
