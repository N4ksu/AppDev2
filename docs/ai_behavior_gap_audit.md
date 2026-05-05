# AI/Behavior Gap Audit Matrix

## Scope

This matrix documents integration status for AI/behavior components before implementation work.

## Route and Controller Wiring

| Component | Expected | Current Status | Gap |
|---|---|---|---|
| `App\Http\Controllers\AiMonitorController@index` | Admin-only web route | Controller exists, no route in `routes/web.php` | Missing route and access control wiring |
| Calibration UI (`resources/views/calibrate.blade.php`) | `GET` route + named route `calibrate` | View exists; dashboard uses `route('calibrate')`; no route found | Missing route registration |
| Calibration submit (`/behavior/calibrate`) | `POST` endpoint to persist samples | Frontend calls endpoint; no route/controller action found | Missing endpoint implementation |
| Behavior ingestion canonical endpoint | One endpoint (`/api/behavior/verify`) | Exists in `routes/api.php` | Present |
| Legacy behavior submit (`/behavior/submit`) | Redirect/adapter or removal | Used by `resources/js/behavior-collector.js`; no route found | Orphaned caller path |
| `App\Http\Controllers\BehaviorController@store` | API behavior ingest | Wired to `/api/behavior/verify` | Present |

## Middleware Registration

| Middleware | Purpose | Current Status | Gap |
|---|---|---|---|
| `App\Http\Middleware\CheckBehaviorRisk` | Session block on behavior risk | Exists; not registered in bootstrap or routes | Missing registration |
| `App\Http\Middleware\TypingDNAMiddleware` | TypingDNA verification gate | Exists; not registered in bootstrap or routes | Missing registration |
| `App\Http\Middleware\EnsureAccountNotLocked` | Lock enforcement | Registered in `bootstrap/app.php` web stack | Present |
| `App\Http\Middleware\ContinuousSessionMonitor` | Session risk threshold enforcement | Registered in `bootstrap/app.php` web stack | Present |

## Service Configuration

| Service | Config Expected | Current Status | Gap |
|---|---|---|---|
| `App\Services\AiRiskService` | `services.gemini` | Currently uses `services.loginllama.api_key` | Provider migration needed |
| `App\Services\TypingDNAService` | `services.typingdna.key` and `.secret` | Service expects keys; keys absent from `config/services.php` | Missing config entries |

## Frontend/Backend Contract Mismatches

| Caller | Path | Backend Status | Gap |
|---|---|---|---|
| `resources/js/behavior-sensor.js` | `/api/behavior/verify` | Implemented | None |
| `resources/js/behavior-collector.js` | `/behavior/submit` | Not implemented | Must migrate to canonical endpoint or add temporary adapter |
| `resources/views/calibrate.blade.php` | `/behavior/calibrate` | Not implemented | Must add route + handler |

## Fortify Route Stability Baseline

The plan requires no Fortify route behavior changes except AI provider source migration. Existing route names and flows in `routes/web.php` and Fortify provider logic should be preserved.
