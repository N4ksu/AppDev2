# Security Decision Merge Matrix

This matrix defines deterministic final actions using:

- Local risk band: `safe`, `suspicious`, `high_risk`
- AI severity: `low`, `medium`, `high`, `critical`
- Provider status: `success`, `degraded`, `error`

## Provider Status Rule

- If provider status is `degraded` or `error`, use local-only fallback:
  - `safe` -> `normal`
  - `suspicious` -> `monitor`
  - `high_risk` -> `alert`

## Success Matrix (`provider_status = success`)

| local_risk_band | low | medium | high | critical |
|---|---|---|---|---|
| safe | normal | monitor | step_up_auth | step_up_auth |
| suspicious | monitor | step_up_auth | alert | deny |
| high_risk | step_up_auth | alert | deny | deny |

## Precedence and Constraints

- Local engine remains enforcement source-of-truth.
- AI-only restrictive actions are not allowed without local corroboration.
- Every valid input combination maps to exactly one output action.
- User-facing dashboards should show calibrated/not-calibrated/degraded state, not raw biometric payload data.
