module.exports = function(grunt) {

	grunt.initConfig({

		jshint: {
			all: ['assets/js/src/*.js']
		},

		uglify: {
			options: {
        sourceMap: true
      },
			admin: {
				files: {
					'assets/js/admin.min.js': [
						'node_modules/jquery-ui-timepicker-addon/dist/jquery-ui-timepicker-addon.js',
						'assets/js/src/admin.js'
					]
				}
			}
		},

		sass: {
			dist: {
				options: {
					style: 'expanded',
					sourceMap: true,
				},
				files: {
					'assets/css/admin.css': 'assets/css/src/admin.scss'
				}
			}
		},

		autoprefixer: {
			options: {
				browsers: ['> 1%', 'last 2 versions', 'Firefox ESR', 'Opera 12.1']
			},
			multiple_files: {
				src: ['assets/css/*.css']
			}
		},

		cssmin: {
			minify: {
				expand: true,
				files: {
					'assets/css/admin.min.css': 'assets/css/admin.css'
				}
			}
		},

		copy: {
			main: {
				src: [
					'assets/**',
					'!assets/*/src/**',
					'!assets/*/src',
					'includes/**',
					'languages/*',
					'anthology.php',
					'CHANGELOG.md',
					'LICENSE.txt',
					'readme.txt'
				],
				dest: 'dist/'
			},
		},

		makepot: {
			target: {
				options: {
					domainPath: 'languages/',
					mainFile: 'anthology.php',
					type: 'wp-plugin',
					updateTimestamp: false,
					updatePoFiles: true
				}
			}
		}
	});

	grunt.loadNpmTasks('grunt-contrib-jshint');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-sass');
	grunt.loadNpmTasks('grunt-autoprefixer');
	grunt.loadNpmTasks('grunt-contrib-cssmin');
	grunt.loadNpmTasks('grunt-contrib-copy');
	grunt.loadNpmTasks('grunt-wp-i18n');

	grunt.registerTask('i18n', ['makepot']);
	grunt.registerTask('scripts', ['jshint', 'uglify']);
	grunt.registerTask('styles', ['sass', 'autoprefixer', 'cssmin']);
	grunt.registerTask('build', ['scripts', 'styles', 'i18n', 'copy']);
	grunt.registerTask('default', ['scripts', 'styles']);
};
