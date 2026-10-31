---
paths:
    - 'app/Providers/**'
---

# Providers

## One deferred provider per vendor client, named for the vendor

Every third-party API client is bound by its own provider named for the vendor — `JetNetServiceProvider`, `AviowikiServiceProvider` — never in `AppServiceProvider`, which registers nothing vendor-specific. AvioWiki's binding lived there until it was moved out; it made AvioWiki the one vendor whose wiring you could not find by its own name.

These providers implement `DeferrableProvider` and list every bound interface in `provides()`. That is the trap: a deferred provider missing from `bootstrap/providers.php`, or with an interface absent from `provides()`, does NOT fail at boot. It fails the first time something resolves the client — which for an on-demand path can be long after the change. `tests/Feature/Providers/VendorClientBindingTest.php` resolves each client and checks each `provides()`; add a row there when a vendor is added.

Use `bind`, not `singleton`, unless the client holds state worth sharing. Both current clients cache in the Laravel cache instead, which a fresh instance reads just as well.
