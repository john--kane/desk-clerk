const wordpressConfig = require( '@wordpress/scripts/config/jest-unit.config' );

module.exports = {
	...wordpressConfig,
	// Clerk publishes its theme presets as ESM; test the real presets through Babel.
	transformIgnorePatterns: [ '/node_modules/(?!@clerk/ui/dist/themes/)' ],
};
