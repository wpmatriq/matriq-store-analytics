const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const path = require( 'path' );

module.exports = {
	...defaultConfig,
	entry: {
		'wc-sma-app': path.resolve(
			__dirname,
			'src/dashboard/DashboardApp.js'
		),
	},
	externals: {
		react: 'React',
		'react-dom': 'ReactDOM',
	},
	resolve: {
		alias: {
			...defaultConfig.resolve.alias,
			'@DashboardApp': path.resolve( __dirname, 'src/dashboard' ),
			'@Components': path.resolve( __dirname, 'src/dashboard/Components' ),
			'@Pages': path.resolve( __dirname, 'src/dashboard/Pages' ),
			'@Tabs': path.resolve( __dirname, 'src/dashboard/Tabs' ),
			'@Hooks': path.resolve( __dirname, 'src/dashboard/Hooks' ),
			'@Store': path.resolve( __dirname, 'src/store' ),
			'@Utils': path.resolve( __dirname, 'src/utils' ),
			'@Onboarding': path.resolve( __dirname, 'src/dashboard/Onboarding' ),
			'@PortalStore': path.resolve( __dirname, 'src/dashboard/Store/' ),
			'@AppImages': path.resolve( __dirname, 'src/images/' ),
		},
	},
	output: {
		...defaultConfig.output,
		filename: '[name].js',
		path: path.resolve( __dirname, 'assets/build' ),
	},
	performance: {
		hints: 'warning',
	},
};
