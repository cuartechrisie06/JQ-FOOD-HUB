# JQ Food Hub

Stack: Laravel (PHP 8), MySQL 8, Bootstrap 5, JavaScript, FullCalendar, Chart.js, DomPDF, Laravel Excel.
Theme: yellow, black, white, red.
Source of truth: /docs/proposal.md (schema, roles, features F1-F9).
Roles: Guest, Customer, Admin, Owner. Enforce with middleware.
Rules:

- Use migrations, models, form requests, and policies. No raw SQL.
- Follow table/column names in proposal Section 8 exactly.
- Hash passwords. Validate all input.
- Add seeders and feature tests for each module.
- Work on one phase at a time. Don't touch unrelated files.
