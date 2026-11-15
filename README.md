# Keywords to Links Converter (Links Auto Replacer Lite)

The free WordPress.org plugin [`links-auto-replacer`](https://wordpress.org/plugins/links-auto-replacer/): it links keywords to URLs automatically, with short links. [Affiliate Butler Pro](https://wpruby.com/plugin/affiliate-butler-pro/) is the paid version; both store the links the same way (`lar_link` posts with `_lar_links_` meta).

The user documentation and the changelog are in [README.txt](README.txt), the WordPress.org readme.

## Development

Requires PHP 7.4+ for the tools (the plugin itself still runs on PHP 5.6 in 2.x) and Composer. `composer install` adds the dev tools; none of them ship (see `.distignore`).

| Command | What it does |
| --- | --- |
| `composer lint` | PHP syntax check |
| `composer phpcs` | Security, i18n and PHP compatibility checks (`phpcs.xml.dist`) |
| `composer phpstan` | Static analysis (`phpstan.neon.dist`); `phpstan-baseline.neon` lists the errors of the 2.2.4 code |
| `composer test` | Integration tests against the WordPress test suite |
| `composer check` | All of the above |

The tests need an empty MySQL database that they can wipe. CI passes it through the `WP_TESTS_DB_*` environment variables. Locally, copy `tests/wp-tests-config-local.php.example` to `tests/wp-tests-config-local.php` and fill in the database (Local by Flywheel sites use a socket, the example shows the format).

`tests/integration/ReplacerTest.php` describes how keywords are linked today. Keep it passing when changing the replacement engine, and update a test only when the change in behaviour is intended.

CI also runs WordPress.org's [Plugin Check](https://wordpress.org/plugins/plugin-check/) on the files that ship. Locally: `wp plugin check Links-Auto-Replacer-Lite` with the Plugin Check plugin active.

`admin/cmb/` is a bundled copy of CMB2 2.0.4. It is excluded from the checks and will be removed.
