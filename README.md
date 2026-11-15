# Keywords to Links Converter (Links Auto Replacer Lite)

The free WordPress.org plugin [`links-auto-replacer`](https://wordpress.org/plugins/links-auto-replacer/): it links keywords to URLs automatically, with short links. [Affiliate Butler Pro](https://wpruby.com/plugin/affiliate-butler-pro/) is the paid version; both store the links the same way (`lar_link` posts with `_lar_links_` meta).

The user documentation and the changelog are in [README.txt](README.txt), the WordPress.org readme.

## Development

Requires PHP 7.4+ and Composer. From 3.0 the plugin needs WordPress 6.6+ and PHP 7.4+ (2.2.5 was the last release for PHP 5.6). `composer install` adds the dev tools; none of them ship (see `.distignore`).

| Command | What it does |
| --- | --- |
| `composer lint` | PHP syntax check |
| `composer phpcs` | Security, i18n and PHP compatibility checks (`phpcs.xml.dist`) |
| `composer phpstan` | Static analysis (`phpstan.neon.dist`); `phpstan-baseline.neon` lists the errors of the 2.2.4 code |
| `composer test` | Integration tests against the WordPress test suite |
| `composer check` | All of the above |

The tests need an empty MySQL database that they can wipe. CI passes it through the `WP_TESTS_DB_*` environment variables. Locally, copy `tests/wp-tests-config-local.php.example` to `tests/wp-tests-config-local.php` and fill in the database (Local by Flywheel sites use a socket, the example shows the format).

`links-auto-replacer.php` defines the constants and `lar()` (the settings, `src/Settings.php`, saved in the `lar_settings` option) and boots `src/Plugin.php`, which registers every hook. `src/` uses the `LinksAutoReplacer` namespace, loaded by `src/autoload.php` since Composer isn't shipped:

| Class | What it does |
| --- | --- |
| `Links\PostType` | The `lar_link` post type (no public pages) |
| `Links\ContentFilter` | Links the keywords of `the_content` |
| `Links\KeywordMap`, `Links\Replacer` | The keyword map and the one-pass engine |
| `Links\ShortLinks` | `/go/{slug}` redirects and the click counts (`_lar_links_clicks` meta) |
| `Links\Repository` | Reads, validates and saves links |
| `Rest\Controller` | The REST API under `links-auto-replacer/v1` (links and settings) |
| `Activation` | Imports the links of version 1 and flushes the rewrite rules |

`admin/` still holds the CMB2 link editor and settings page, loaded in the admin only; the new admin replaces them.

Keywords are linked by `src/Links/Replacer.php` in one pass over the content, from the keyword map of `src/Links/KeywordMap.php` (a transient, rebuilt when a link, its meta or the settings change). The output is cached in the object cache, keyed by the content and the map version. Both are trimmed-down ports of Affiliate Butler Pro's engine; keep them in step.

`tests/integration/ReplacerTest.php` describes how keywords are linked today. Keep it passing when changing the replacement engine, and update a test only when the change in behaviour is intended.

CI also runs WordPress.org's [Plugin Check](https://wordpress.org/plugins/plugin-check/) on the files that ship. Locally: `wp plugin check Links-Auto-Replacer-Lite` with the Plugin Check plugin active.

`admin/cmb/` is a bundled copy of CMB2 2.0.4. It is excluded from the checks and will be removed.
