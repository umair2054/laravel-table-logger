# Laravel Table Logger

  

A Laravel package to automatically or manually log model events (create, update, delete) either to the database or file system, based on `.env` configuration.

  

---

  

## 🚀 Features

  

- 🔁 Automatically logs `create`, `update`, `delete` actions on models

- ⚙️ Supports both database and file logging via `.env`

- 🧠 Easily retrieve logs using model relationships

- 🔧 Manually log any action without relying on model events

- 📁 Structured file-based logs per table and record

- 📦 Simple to integrate via Eloquent traits

  

---

  

## 📦 Installation

  

```bash

composer  require  umairhanifdev/laravel-table-logger

```

  

---

  

## ⚙️ Configuration

  

Publish config (optional):

  

```bash

php  artisan  vendor:publish  --tag=config

```

  

Set logging driver in your `.env`:

  

```env

TABLE_LOGGER_DRIVER=database # or "file"

```

  

-  **database**: Logs are stored in `{table_name}_logs` tables

-  **file**: Logs are stored in `storage/logs/umairhanifdev/{table}/{id}.log`

  

---

  

## 📘 Usage

  

### 1. Eloquent Model Logging

  

#### Step 1: Add Traits

  

Add the following traits to your Eloquent model. Once added, logs will automatically be generated on `create`, `update`, and `delete` actions.

  

```php

use UmairHanif\LaravelTableLogger\Traits\Loggable;

use UmairHanif\LaravelTableLogger\Traits\HasLogs;

  

class  ExaminationReport  extends  Model

{

use  Loggable, HasLogs;

}

```

  

---

  

### 2. Retrieve Stored Logs

  

#### Logs for a Single Model Record

  

```php

$report = ExaminationReport::find($id);

$reportLogs = $report->logs()->get();

```

  

#### Logs for Multiple Records

  

```php

$examReports = ExaminationReport::where('user_id', 16)->get();

$examinationLogs = $examReports->logsBatch()

->where('this_log_action', 'update') // optional filter

->get();

```

  

---

  

### 3. Manual Logging (No Eloquent Required)

  

You can manually log an action without triggering Eloquent events:

  

```php

use UmairHanif\LaravelTableLogger\Helpers\LoggerLog;

  

LoggerLog::logAction('examination_reports', $id, 'delete');

DB::table('examination_reports')->where('id', $id)->delete();

```

  

---

  

### 4. Nested Logs via Relationships

  

If your model (e.g., `User`) has child models using logs (e.g., `ExaminationReport`), you can retrieve logs as:

  

```php

$user = User::with(['examination' => function ($q) {

$q->whereStatus(1)->orderBy('id', 'DESC');

}])->find($id);

  

// Logs for the parent model (User)

$usersLogs = $user->logs()->get();

  

// Logs for nested model (Examinations)

$examinationLogs = collect();

if ($user->examination) {

$examinationLogs = $user->examination->logsBatch()

->where('this_log_action', 'update') //optional - to filter the logs futher

->get();

}

```

  

---

  

## 🗃️ Log Table Naming Convention

  

Log tables are automatically named using the singular form of the model table name + `_logs`. Example: `users` → `user_logs`

  

You can define custom mappings for irregular plurals:

  

```php

'irregular_plurals' => [

'people' => 'person',

'children' => 'child',

'men' => 'man',

'women' => 'woman',

],

```

  

---

  

## 📂 File-based Log Format

  

When using `TABLE_LOGGER_DRIVER=file`, logs are saved as JSON files in:

  

```

storage/logs/umairhanifdev/{table}/{id}.log

```

  

Each file will contain all logs of a specific record.

  

---

  

## 📝 License

  

MIT

  

---

  

Built with ❤️ by Umair Hanif

🌐 [umairhanif.com](https://umairhanif.com)

📫 hello@umairhanif.com

🔗 [LinkedIn](https://www.linkedin.com/in/umair-hanif-a95179155/)
