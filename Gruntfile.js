/*global module:false*/
module.exports = function(grunt) {

	grunt.initConfig({
		sass: {
			dev: {
                options: {
                    sourcemap: 'none',
                    check: false
                },
                files: {
                    './assets/css/src/core.css': './assets_src/sass/core.scss'
                }
			},
			dist: {
                options: {
                    sourcemap: 'none'
                },
                files: [{
                    expand: true,
                    cwd: 'assets_src/sass',
                    src: ['*.scss'],
                    dest: 'assets/css/src',
                    ext: '.css'
                }]
			}
		},
		uglify: {
			dist: {
				files: [{
					expand: true,
					cwd: 'assets_src/js',
					src: ['**/*.js'],
					dest: 'assets/js/src/',
					ext: '.js'
				},{
					expand: true,
					cwd: 'assets_src/lib',
					src: ['**/*.js'],
					dest: 'assets/js/src/lib/',
					ext: '.js'
				}]
			}
		},
		concat: {
			options: {
				separator: ';'
			},
			dist: {
				files: {
					'assets/js/lib.min.js': [
						'assets/js/src/lib/**/*.js'
					],
					'assets/js/core.min.js': [
						'assets/js/src/*.js'
					]
				}
			}
		},
		concat_css: {
			options: {},
			dist: {
				files: [
					{
						src: 'assets_src/lib/**/*.css',
						dest: 'assets/css/src/lib.css'
					}
				]
			}
		},
		copy: {
			fontawesome: {
				files: [
					{
						expand: true,
						cwd: 'node_modules/font-awesome/fonts',
						src: '*.*',
						dest: 'assets/fonts'
					}
				]
			}
		},
		cssmin: {
			minify: {
				files: [
					{
						expand: true,
						cwd: 'assets/css/src/',
						src: ['*.css'],
						dest: 'assets/css/dist/',
						ext: '.min.css'
					},
				]
			}
		},
		watch: {
			javascript: {
				files: [
					'assets_src/*/**/*.js'
				],
				tasks: ['uglify', 'concat'],
				options : {
					spawn: false
				}
			},
			sass: {
				files: [
					'assets_src/sass/**/*.scss'
				],
				tasks: ['sass:dev'],
				options : {
					spawn: false
				}
			},
			css: {
				files: [
					'assets_src/*/**/*.css'
				],
				tasks: ['concurrent:target', 'concat_css'],
				options : {
					spawn: false
				}
			},
		},
		concurrent: {
			target: ['uglify', 'cssmin']
		},
        autoprefixer: {
            options: {
                browsers: ['> 5%', 'ie >= 7', 'Safari 8']
            },
            prefix: {
                src: 'assets/css/src/core.css',
                dest: 'assets/css/src/core.css'
            }
        },
		// imagemin: {
		// 	dynamic: {
		// 		files: [{
		// 			expand: true,
		// 			cwd: 'assets_src/img',
		// 			src: ['**/*.{png,jpg,gif}'],
		// 			dest: 'assets/img/'
		// 		}]
		// 	}
		// }
	});

	// These plugins provide necessary tasks
	grunt.loadNpmTasks('grunt-contrib-concat');
	grunt.loadNpmTasks('grunt-concat-css');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-contrib-copy');
	grunt.loadNpmTasks('grunt-contrib-cssmin');
	grunt.loadNpmTasks('grunt-concurrent');
	grunt.loadNpmTasks('grunt-contrib-sass');
    grunt.loadNpmTasks('grunt-contrib-imagemin');
    grunt.loadNpmTasks('grunt-autoprefixer');

	// Default task
	grunt.registerTask('default', ['sass:dev', 'concurrent:target', 'concat', 'concat_css', 'copy:fontawesome']);
	grunt.registerTask('production', ['sass:dist', 'autoprefixer', 'concurrent:target', 'concat', 'concat_css', 'copy:fontawesome']);
};
