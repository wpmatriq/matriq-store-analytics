module.exports = function ( grunt ) {
	var autoprefixer = require( 'autoprefixer' );
	var flexibility = require( 'postcss-flexibility' );

	// Project configuration.
	grunt.initConfig( {
		pkg: grunt.file.readJSON( 'package.json' ),

		copy: {
			main: {
				options: {
					mode: true,
				},
				src: [
					'**',
					'!.git/**',
					'!.gitignore',
					'!.gitattributes',
					'!*.sh',
					'!*.map',
					'!*.zip',
					'!eslintrc.json',
					'!README.md',
					'!Gruntfile.js',
					'!package.json',
					'!package-lock.json',
					'!composer.json',
					'!composer.lock',
					'!phpcs.xml',
					'!phpcs.xml.dist',
					'!phpunit.xml.dist',
					'!node_modules/**',
					'!vendor/**',
					'!tests/**',
					'!scripts/**',
					'!config/**',
					'!tests/**',
					'!bin/**',
					'!artifact/**',
					'!assets/css/unminified/**',
					'!assets/js/unminified/**',
					'!assets/fonts/google-fonts.json',
					'!assets/src/**',
					'!phpstan.neon',
					'!phpstan-baseline.neon',
					'!tailwind.config.js',
					'!webpack.config.js',
					'!postcss.config.js',
					'!.DS_Store',
					'!phpinsights.php',
					'!MATRIQ_MSA_ANALYSIS.md',
					'!AGENTS.md',
					'!STRATEGY.md',
					'!CLAUDE.md',
					'!GUIDE.md',
					'!jsconfig.json',
					'!playwright.config.ts',
					'!wp-env.json',
					// '!src/**',

				],
				dest: 'matriq-store-analytics/',
			},
		},
		compress: {
			main: {
				options: {
					archive: 'matriq-store-analytics-<%= pkg.version %>.zip',
					mode: 'zip',
				},
				files: [
					{
						src: [ './matriq-store-analytics/**' ],
					},
				],
			},
		},
		clean: {
			main: [ 'matriq-store-analytics' ],
			zip: [ '*.zip' ],
		},
		bumpup: {
			options: {
				updateProps: {
					pkg: 'package.json',
				},
			},
			file: 'package.json',
		},
		replace: {
			plugin_main: {
				src: [ 'matriq-store-analytics.php' ],
				overwrite: true,
				replacements: [
					{
						from: /Version: \bv?(?:0|[1-9]\d*)\.(?:0|[1-9]\d*)\.(?:0|[1-9]\d*)(?:-[\da-z-A-Z-]+(?:\.[\da-z-A-Z-]+)*)?(?:\+[\da-z-A-Z-]+(?:\.[\da-z-A-Z-]+)*)?\b/g,
						to: 'Version: <%= pkg.version %>',
					},
				],
			},
			plugin_readme: {
				src: [ 'readme.txt' ],
				overwrite: true,
				replacements: [
					{
						from: /Stable tag: \bv?(?:0|[1-9]\d*)\.(?:0|[1-9]\d*)\.(?:0|[1-9]\d*)(?:-[\da-z-A-Z-]+(?:\.[\da-z-A-Z-]+)*)?(?:\+[\da-z-A-Z-]+(?:\.[\da-z-A-Z-]+)*)?\b/g,
						to: 'Stable tag: <%= pkg.version %>',
					},
				],
			},
			plugin_const: {
				src: [ 'matriq-store-analytics.php' ],
				overwrite: true,
				replacements: [
					{
						from: /MATRIQ_MSA_VER', '.*?'/g,
						to: "MATRIQ_MSA_VER', '<%= pkg.version %>'",
					},
				],
			},
			plugin_function_comment: {
				src: [
					'*.php',
					'**/*.php',
					'!node_modules/**',
					'!php-tests/**',
					'!bin/**',
					'!vendor/**',
					'!tests/**',
					'!artifact/**',
				],
				overwrite: true,
				replacements: [
					{
						from: 'x.x.x',
						to: '<%=pkg.version %>',
					},
				],
			},
		},
		wp_readme_to_markdown: {
			your_target: {
				files: {
					'README.md': 'readme.txt',
				},
			},
		},
		rtlcss: {
			options: {
				// RTL options
				config: {
					preserveComments: true,
					greedy: true,
				},
				// generate source maps
				map: false,
			},
			dist: {
				files: [
					{
						expand: true,
						cwd: 'assets/build',
						src: [ '*.css', '!*-rtl.css' ],
						dest: 'assets/build',
						ext: '-rtl.css',
					},
					{
						expand: true,
						cwd: 'assets/css/unminified/',
						src: [ '*.css', '!*-rtl.css' ],
						dest: 'assets/css/unminified',
						ext: '-rtl.css',
					},
				],
			},
		},
		postcss: {
			options: {
				map: false,
				processors: [
					flexibility,
					autoprefixer( {
						browsers: [
							'> 1%',
							'ie >= 11',
							'last 1 Android versions',
							'last 1 ChromeAndroid versions',
							'last 2 Chrome versions',
							'last 2 Firefox versions',
							'last 2 Safari versions',
							'last 2 iOS versions',
							'last 2 Edge versions',
							'last 2 Opera versions',
						],
						cascade: false,
					} ),
				],
			},
			style: {
				expand: true,
				src: [ 'assets/css/unminified/*.css' ],
			},
		},
		uglify: {
			js: {
				files: [
					{
						// all .js to min.js.
						expand: true,
						src: [ '**.js', '!jodit.js' ],
						dest: 'assets/js/minified',
						cwd: 'assets/js/unminified',
						ext: '.min.js',
					},
				],
			},
		},
		cssmin: {
			options: {
				keepSpecialComments: 0,
			},
			css: {
				files: [
					// Generated '.min.css' files from '.css' files.
					// NOTE: Avoided '-rtl.css' files.
					{
						expand: true,
						src: [ '**/*.css', '**/*-rtl.css' ],
						dest: 'assets/css/minified',
						cwd: 'assets/css/unminified',
						ext: '.min.css',
					},
				],
			},
		},
		sass: {
			options: {
				implementation: require( 'sass' ), // Use Dart Sass for Sass compilation
				sourceMap: false,
				outputStyle: 'expanded',
				linefeed: 'lf',
				charset: false,
			},
			dist: {
				files: [
					/* Login Block Style */
					{
						'assets/css/unminified/login-block.css':
							'src/editor/blocks/Login/style.scss',
					},
					/* Registration Block Style */
					{
						'assets/css/unminified/register-block.css':
							'src/editor/blocks/Register/style.scss',
					},
				],
			},
		},
		watch: {
			scripts: {
				files: [ 'assets/sass/**/*.scss' ],
				tasks: [ 'sass' ],
				options: {
					spawn: false,
				},
			},
		},
	} );

	/* Load Tasks */
	grunt.loadNpmTasks( 'grunt-sass' );
	grunt.loadNpmTasks( 'grunt-rtlcss' );
	grunt.loadNpmTasks( 'grunt-bumpup' );
	grunt.loadNpmTasks( '@lodder/grunt-postcss' );
	grunt.loadNpmTasks( 'grunt-contrib-cssmin' );
	grunt.loadNpmTasks( 'grunt-contrib-uglify' );
	grunt.loadNpmTasks( 'grunt-contrib-copy' );
	grunt.loadNpmTasks( 'grunt-contrib-compress' );
	grunt.loadNpmTasks( 'grunt-contrib-clean' );
	grunt.loadNpmTasks( 'grunt-text-replace' );
	grunt.loadNpmTasks( 'grunt-wp-readme-to-markdown' );

	// Update google Fonts
	grunt.registerTask( 'google-fonts', function () {
		var done = this.async();
		var axios = require( 'axios' );
		var fs = require( 'fs' );

		// Fetch Google Fonts
		axios
			.get(
				'https://www.googleapis.com/webfonts/v1/webfonts?key=AIzaSyDu1nDK2o4FpxhrIlNXyPNckVW5YP9HRu8'
			)
			.then( function ( response ) {
				var fonts = response.data.items.map( function ( font ) {
					return {
						[ font.family ]: {
							variants: font.variants,
							category: font.category,
						},
					};
				} );

				// Write JSON file
				fs.writeFile(
					'assets/fonts/google-fonts.json',
					JSON.stringify( fonts, null, 4 ),
					function ( err ) {
						if ( err ) {
							console.log( 'Error writing JSON file:', err );
							done( err );
							return;
						}

						// Call the custom script to generate the PHP file
						require( './bin/json2php.js' ); // Make sure the script path is correct
						done();
					}
				);
			} )
			.catch( function ( error ) {
				console.error( 'Error fetching Google Fonts:', error );
				done( error );
			} );
	} );

	// SASS
	grunt.registerTask( 'scss', [ 'sass' ] );

	/* Generate Read MD file. */
	grunt.registerTask( 'readme', [ 'wp_readme_to_markdown' ] );

	/* Bump Version - `grunt version-bump --ver=<version-number>` */
	grunt.registerTask( 'version-bump', function ( ver ) {
		var newVersion = grunt.option( 'ver' );

		if ( newVersion ) {
			newVersion = newVersion ? newVersion : 'patch';

			grunt.task.run( 'bumpup:' + newVersion );
			grunt.task.run( 'replace' );
		}
	} );

	/* Register rtl task */
	grunt.registerTask( 'rtl', [ 'rtlcss' ] );

	/* Register styler task */
	grunt.registerTask( 'style', [ 'postcss:style', 'rtl' ] );

	/* Register minification task */
	grunt.registerTask( 'minify', [
		'scss',
		'style',
		'uglify:js',
		'cssmin:css',
	] );

	/* Register task started */
	grunt.registerTask( 'release', [
		'clean:zip',
		'copy:main',
		'compress:main',
		'clean:main',
	] );
	grunt.registerTask( 'release-no-clean', [ 'copy', 'compress' ] );
};
