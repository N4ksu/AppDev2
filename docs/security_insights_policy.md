# Security Insights Policy

## Retention Window

- Keep `security_insights` records for 180 days in application storage.
- For legal or incident response hold, records may be retained longer under administrator override.

## Redaction Rules

- Do not store raw credentials, session tokens, or full authentication payloads.
- Do not expose raw behavioral vectors (`avg_*` values, event-level metrics) in end-user UI.
- End-user pages must display only aggregate state summaries and recommendations.
- Detailed event payloads are restricted to security administrators.
- Truncate user-agent strings to operationally useful length when needed.
- Avoid storing raw prompt inputs that include unnecessary personal data.
- Persist only minimal fields required for risk reasoning, forensic review, and auditability.

## Required Audit Fields

Each insight record and related logs must include:

- actor identity (`user_id` when known),
- source event (`login_log_id`),
- event timestamp (`created_at`),
- correlation identifiers (request id and/or job id),
- provider metadata (`model_name`, `provider_status`),
- decision path (`local_risk_band`, `severity`, `final_action`, `recommendation`),
- response classification code where applicable.
