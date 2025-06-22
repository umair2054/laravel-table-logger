# Laravel Table Logger

A flexible Laravel package to automatically log changes (create, update, delete) in your Eloquent models or manually via helper. Supports logging to either database or file system.

## Installation

```bash
composer require umairhanifdev/laravel-table-logger
```

## Configuration (Optional)

Publish the config file and migration:

```bash
php artisan vendor:publish --tag=config
```

In your `.env` file:

```
TABLE_LOGGER_DRIVER=database # or "file"
```

By default, it logs to the database. If set to `file`, it will store logs in `storage/logs/umairhanifdev/{table}` directory.

---

## Usage

### 1. Eloquent Model Logging

#### Step 1: Add Traits

```php
use UmairHanif\LaravelTableLogger\Traits\Loggable;
use UmairHanif\LaravelTableLogger\Traits\HasLogs;

class ExaminationReport extends Model
{
    use Loggable, HasLogs;
}
```

#### Step 2: Logs for a Single Model

```php
$report = ExaminationReport::find($id);
$reportLogs = $report->logs()->get();
```

#### Step 3: Logs for Multiple Records

```php
$examReports = ExaminationReport::where('user_id', 16)->get();
$examinationLogs = $examReports->logsBatch()
    ->where('this_log_action', 'update') // optional filter
    ->get();
```

---

### 2. Manually Log Action without Eloquent Events

```php
use UmairHanif\LaravelTableLogger\Helpers\LoggerLog;

LoggerLog::logAction('examination_reports', $id, 'delete');
DB::table('examination_reports')->where('id', $id)->delete();
```

---

### 3. Nested Logs Access via Relations

```php
$user = User::with(['examination' => function ($q) {
    $q->whereStatus(I_STATUS::IS_ACTIVE)->orderBy('id', 'DESC');
}])->find($id);

// All logs of user (if model uses HasLogs)
$usersLogs = $user->logs()->get();

// Logs for user examinations
$examinationLogs = collect();
if ($user->examination) {
    $examinationLogs = $user->examination->logsBatch()
        ->where('this_log_action', 'update')
        ->get();
}
```

---

## Log Table Naming

By default, the package creates log tables using singular table names followed by `_logs`. You can customize irregular naming via the config:

```php
'irregular_plurals' => [
    'people' => 'person',
    'children' => 'child',
    'men' => 'man',
    'women' => 'woman',
    // add more as needed
],
```

---

## Log Table Generation

Automatically generate log tables for all or specific models:

```bash
php artisan make:log-tables
php artisan make:log-tables --tables=users,posts --force
```

---

## File-based Logging

If `TABLE_LOGGER_DRIVER=file` is set in `.env`, logs will be stored in:

```
storage/logs/umairhanifdev/{table}/{id}.log
```

Each file contains structured logs (JSON) for the given record.

---

## License

MIT

---

Created with ❤️ by Umair Hanif

LinkedIn: [https://www.linkedin.com/in/umair-hanif-a95179155/](https://www.linkedin.com/in/umair-hanif-a95179155/)

web: [https://umairhanif.com](https://umairhanif.com)

email: hello\@umairhanif.com
