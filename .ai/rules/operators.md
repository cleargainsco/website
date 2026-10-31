---
paths:
  - 'app/Actions/Operators/**'
---

# Operators

## A stale package manifest orphans media files on delete, silently
medialibrary removes a file from disk in `MediaObserver::deleted`, and that observer is attached in `MediaLibraryServiceProvider::packageBooted()`. If `bootstrap/cache/packages.php` predates the package's installation, the provider never boots, the observer is never attached, and `$media->delete()` drops the row while leaving the bytes on disk.

The symptom is misleading: UPLOADS KEEP WORKING, because `HasMedia`/`InteractsWithMedia` need no provider. Only deletion breaks, and it breaks in silence — `DefaultFileRemover` wraps its work in `catch (Exception) { report($e); }`, so even a real failure is a log line rather than an error.

Fix: `php artisan package:discover`. `composer install` does it on deploy, so this is a local-environment trap.

Why it matters here: deleting is how an operator withdraws a document they should not have sent (the wrong client's contract, a colleague's passport), so a row that vanishes while the file remains honours the request only in appearance. `OperatorDocumentTest` now asserts `Storage::disk('local')->exists($path)` is false after a delete — that assertion is what catches this, and the row-only assertion it replaced did not.
