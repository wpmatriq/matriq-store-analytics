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
			'@Hooks': path.resolve( __dirname, 'src/dashboard/hooks' ),
			'@Utils': path.resolve( __dirname, 'src/utils' ),
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
