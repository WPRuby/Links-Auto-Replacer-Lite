/**
 * ESLint config: the @wordpress/scripts defaults, plus the WordPress packages that
 * WordPress provides at runtime (they are not installed, see build/*.asset.php).
 */
const defaults = require( '@wordpress/scripts/config/eslint.config.cjs' );

const wordpressPackages = [
	'@wordpress/api-fetch',
	'@wordpress/block-editor',
	'@wordpress/components',
	'@wordpress/compose',
	'@wordpress/data',
	'@wordpress/date',
	'@wordpress/dom-ready',
	'@wordpress/editor',
	'@wordpress/element',
	'@wordpress/html-entities',
	'@wordpress/i18n',
	'@wordpress/notices',
	'@wordpress/plugins',
	'@wordpress/rich-text',
	'@wordpress/url',
];

module.exports = [
	...defaults,
	{
		settings: {
			'import/core-modules': wordpressPackages,
		},
		rules: {
			'import/no-extraneous-dependencies': [
				'error',
				{ packageDir: __dirname, includeTypes: false },
			],
		},
	},
	{
		ignores: [ 'vendor/**', 'build/**', 'eslint.config.cjs' ],
	},
];
