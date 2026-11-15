=== Keywords to Links Converter ===
Contributors: waseem_senjer, wprubyplugins
Donate link: https://wpruby.com/plugin/affiliate-butler-pro/
Tags: affiliate, links, keywords, automation, marketing
Requires at least: 6.6
Tested up to: 7.1
Stable tag: 3.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Link your keywords to URLs automatically in every post, with short links that hide affiliate URLs and count their clicks.

== Description ==

Add a keyword once, choose the URL it links to, and Keywords to Links Converter links it in every post and page where it appears: in what you have already written and in what you write next. Change the URL later and every post follows.

It's made for affiliate marketers who link the same products again and again, and for anyone who wants their favorite links in place without editing old posts.

= Features =

* **Auto links:** one or more keywords per link, linked wherever they appear. Keywords inside existing links, code blocks and the HTML tags you exclude are left alone.
* **Short links:** `yoursite.com/go/amazon` style links that redirect to the URL, hide long affiliate URLs and stay out of search results.
* **Click counts:** see how many times each short link was clicked. Bots aren't counted, and no personal data is stored.
* **SEO-friendly links:** nofollow and `rel="sponsored"` for affiliate links, and new-window links get `noopener`.
* **Control:** case-sensitive keywords, a limit of links per keyword in a post, excluded HTML tags, and a switch to disable the auto links of a single post in the block editor.
* **Fast:** the keywords are linked in one pass over the post, and the result is cached until a link or a setting changes.

= Affiliate Butler Pro =

[Affiliate Butler Pro](https://wpruby.com/plugin/affiliate-butler-pro/) adds links to your own posts and pages, popups, image galleries, videos, maps and share boxes, click stats per day, country and post, URL rotation for split tests, QR codes, import and export, and more. It uses the same links: activate it and everything you made here keeps working.

== Installation ==

1. Go to **Plugins → Add New Plugin** and search for "Keywords to Links Converter".
2. Click **Install Now**, then **Activate**.
3. Go to **Auto Links → Add New**, type a keyword, paste the URL and click **Publish**.

== Frequently Asked Questions ==

= What's new in 3.0? =

A new admin with a faster editor, a much faster linking engine, click counts for short links, `rel="sponsored"`, and a block editor panel to disable the auto links of a post. Your links and settings are kept as they are. 3.0 needs WordPress 6.6 and PHP 7.4; older sites keep version 2.2.5.

= Why isn't my keyword linked? =

Check that the link is published, that "Link the keywords" is on in **Auto Links → Settings**, and that the keyword is written the same way in the post (including the letter case when "Case sensitive" is on). Keywords inside links, code blocks, excluded HTML tags and posts where auto links are disabled are not linked.

= My short links show a "page not found" error. =

Go to **Settings → Permalinks** in WordPress and click **Save Changes** without changing anything.

= Does the plugin change my posts? =

No. The links are added when a post is shown, and your content stays as you wrote it.

= What does the plugin save about visitors? =

Only a number: how many times each short link was clicked. No IP address, cookie or anything else about the visitor.

= What happens when I install Affiliate Butler Pro? =

Your links keep working, Pro brings over your settings and click counts, and this plugin pauses itself while Pro is active. You can then deactivate it.

= Are my links deleted when I delete the plugin? =

Its settings are deleted, but your links are kept, so they're there if you install it again or move to Affiliate Butler Pro.

== Screenshots ==

1. The keywords linked in a post.
2. All your auto links, with their short links and clicks.
3. The link editor.
4. The settings.
5. Disable the auto links of a post in the block editor.

== Changelog ==

= 3.0.0 =
* New: A new admin under **Auto Links**: a links list with search, filters and click counts, a link editor and a settings page. The old screens open the new ones.
* New: Short links count their clicks (bots aren't counted, nothing about the visitor is saved).
* New: `rel="sponsored"` for affiliate links, as a link option and a default for new links. Links that open in a new window get `rel="noopener"`.
* New: A "Disable auto links in this post" panel in the block editor (the classic editor keeps its box).
* New: A WPRuby header on the plugin screens with links to the documentation and the support forum, and an "Upgrade to Pro" box in the link editor.
* New: When Affiliate Butler Pro is active, this plugin pauses itself instead of linking every keyword twice; Pro takes over its links, settings and click counts.
* Improved: A much faster linking engine: one pass over the post, cached until a link or a setting changes. When keywords overlap, the longest one wins.
* Improved: Short links redirect without an extra step and are kept out of search results; the post type has no public pages any more.
* Removed: The bundled CMB2 library and the jQuery script that was loaded on every page of the site.
* Changed: Requires WordPress 6.6 and PHP 7.4.

= 2.2.5 =
* Security: Escaped text in posts is no longer turned into HTML when keywords are linked.
* Security: The link check in the editor needs the right capability, and its input is sanitized and its messages escaped.
* Security: The "Disable for this post" box checks its nonce and the user's capability.
* Security: Short links only redirect published links to http(s) URLs, and are kept out of search results.
* Security: Admin screens escape their output.
* Fixed: Unchecking "Enable" in the settings now stops linking keywords.
* Fixed: Keywords that are not case sensitive are linked even when the post never writes them in the case you typed.
* Fixed: Links no longer get a stray attribute with their ID.
* Fixed: Accented letters stay as they are in linked posts.
* Fixed: Keywords in code blocks are not linked.
* Fixed: Wrong text domain in a few strings.
* Removed: The About page no longer loads the plugin icon from WordPress.org.

= 2.2.4 =
* Added: WordPress 6.6 compatibility.

= 2.2.3 =
* Improved: Keywords to links replacement.
* Fixed: Update base62 encoding to be compatible with PHP 8.0+


= 2.2.2 =
* Removed: For performance issues, we removed replacement of the excerpt text.

= 2.2.1 =
* Fixed: suppress preg_match_all warnings.
* Fixed: words with hyphens were not replaced.

= 2.2.0 =
* Added: support of Japanese, Chinese languages.

= 2.1.11 =
* Fixed: Performance of the of the keywords replacement.

= 2.1.10 =
* Fixed: unnecessary loading of css and js files

= 2.1.9 =
* Fixed: dofollow linking did not reflect the settings

= 2.1.8 =
* Added: CSS class `lar-automated-link` for the generated links

= 2.1.7 =
* Fixed: PHP 5.2 compatibility.

= 2.1.6 =
* Fixed: The escaping of the keywords replacement.

= 2.1.5 =
* Improved: The keywords replacement process

= 2.1.3 =
* Fixed: Pro URL changing

= 2.1.2 =
* Fixed: Letters case sensitivity

= 2.1.0 =
* Fixed on activation bug
* Fixed compatibility checks
* Improving replacing keywords functionality
* Renaming the plugin

= 2.0.0 =
* A complete rewrite.
* Stats Feature (Map, Countries, Browsers, OS) (PRO)
* Internal linking (PRO)
* Fixing some 5 bugs and issues.

= 1.2.2 =
* Stopping the replacement inside html tags.


= 1.2.1 =
* Add "Disable Auto Links" meta box to the pages.

= 1.2.0 =
* User Interface fixes.
* Validation enhancements.
* Fixing the comma and quotes problem.
* Adding case sensitivity feature

= 1.1.3 =
* Fixing the comma replacing problem

= 1.1.2 =
* Fixing a critical bug.


= 1.1.1 =
* Fixing the GMP problem when the GMP module is not loaded.


= 1.1 =
* Fix the UTF-8 encoding for keywords.
* Fix the sensitivity of the keywords letters.
* Add auto generated slugs when the user choose to shrink the link.
* Changing the UI for better user experience.
* Now you can add multiple keywords for one URL.
* Meta Box is added to disable the auto replacement for individual posts.

= 1.0 =
* Initial release.

== Upgrade Notice ==

= 3.0.0 =
A new admin, a much faster linking engine and click counts for short links. Requires WordPress 6.6 and PHP 7.4. Your links and settings are kept.

= 2.2.5 =
Security release, please update: fixes stored XSS in linked posts and several admin checks.
