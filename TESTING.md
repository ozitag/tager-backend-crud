# Testing

The suite runs on [Orchestra Testbench](https://packages.tools/testbench), which
boots a real Laravel application around the package, so the tests exercise the
same code path a consuming project does — routing, form requests, the custom
validator, the repository layer and the resource serialiser.

## Running

```bash
composer install
composer test
```

`composer test` is `vendor/bin/phpunit`. The database is SQLite in memory; no
services are required.

> The packages require `php: ^8.3`. Until the `ozitag/tager-backend-*`
> dependencies are re-published from this source, Packagist still serves 2.0.0
> tags that declare `php: ^8.5`, so an install on PHP 8.3/8.4 needs
> `composer update --ignore-platform-req=php` until then.

## Layout

| Path | Purpose |
|---|---|
| `tests/TestCase.php` | Testbench base: providers, SQLite connection, fixture schema, fixture routes |
| `tests/Fixtures/` | A miniature module — model, repository, form request, two CRUD controllers, routes |
| `tests/Feature/` | End-to-end tests issuing real HTTP requests against the fixture routes |

The fixtures deliberately mirror a real module (compare
`BlogAdminCategoriesController`): declarative resource fields, a store/update
action backed by the generic `StoreJob`/`UpdateJob`, move and count enabled.

## Conventions

- Tests assert the **current** behaviour. Where that behaviour is a defect or a
  trap, it is pinned with a docblock explaining what is wrong and what the test
  should become once it is fixed — search for `KNOWN DEFECT` and the notes on
  `StoreJob` null-writes and static state.
- The Tager validation envelope (HTTP 400 plus a machine-readable `code` per
  field) is a contract the admin UI depends on, and is asserted as such.
