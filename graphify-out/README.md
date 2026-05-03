# Graphify Output

This folder contains the current `graphify` extraction artifacts for the SplitMate API codebase.

The existing run already produced raw machine-readable output:

- `.graphify_ast.json`
- `.graphify_chunk_*.json`
- `cache/ast/*.json`
- `.graphify_detect.json`

What was missing was a human-readable view of that graph. This README and the Mermaid files in this folder are the continuation of that work.

## Current Architecture

SplitMate currently has two application surfaces:

1. `API v1` for the mobile/client app in [routes/api.php](/Applications/XAMPP/xamppfiles/htdocs/pro/splitmate/SplitMate-api/routes/api.php:1)
2. `Admin + legacy web` routes in [routes/web.php](/Applications/XAMPP/xamppfiles/htdocs/pro/splitmate/SplitMate-api/routes/web.php:1)

The active domain flow is centered on:

- `Group`
- `User`
- `Expense`
- `Settlement`
- `StatementRecord`
- `BalanceService`

## Files Added Here

- [architecture.mmd](/Applications/XAMPP/xamppfiles/htdocs/pro/splitmate/SplitMate-api/graphify-out/architecture.mmd:1): high-level system flow
- [domain-model.mmd](/Applications/XAMPP/xamppfiles/htdocs/pro/splitmate/SplitMate-api/graphify-out/domain-model.mmd:1): core entity relationships

## Key Findings From The Existing Graph

- The newer API flow delegates balance and statement generation to [app/Services/BalanceService.php](/Applications/XAMPP/xamppfiles/htdocs/pro/splitmate/SplitMate-api/app/Services/BalanceService.php:1).
- The older web flow still keeps balance logic directly inside [app/Http/Controllers/ExpenseController.php](/Applications/XAMPP/xamppfiles/htdocs/pro/splitmate/SplitMate-api/app/Http/Controllers/ExpenseController.php:14).
- `Group` is the central aggregate root for members, expenses, settlements, and statement records via [app/Models/Group.php](/Applications/XAMPP/xamppfiles/htdocs/pro/splitmate/SplitMate-api/app/Models/Group.php:13).
- API group membership is enforced in routing with `auth:sanctum` plus `ensure.group.member` in [routes/api.php](/Applications/XAMPP/xamppfiles/htdocs/pro/splitmate/SplitMate-api/routes/api.php:19).

## Suggested Next Graphify Steps

1. Generate rendered SVG or PNG diagrams from the Mermaid files for quick sharing.
2. Add a small regeneration script so `graphify-out/README.md` and the Mermaid diagrams stay aligned with future code changes.
3. Optionally split the graph into focused views:
   - auth and membership
   - expense and settlement lifecycle
   - statement and balance calculation
