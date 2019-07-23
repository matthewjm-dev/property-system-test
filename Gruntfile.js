/*global module:false*/
module.exports = function(grunt) {

	grunt.initConfig({
        // CSS
        sass: {
            dev: {
                options: {
                    sourcemap: 'none',
                    //check: false
                },
				files: {
				    'public/css/src/site.css': 'apps/site/assets/sass/import.scss',
				    'public/css/src/admin.css': 'apps/admin/assets/sass/import.scss',
				}
            }
        },
        concat_css: {
            options: {},
            dev: {
                files: {
                    'public/css/src/site-lib.css': 'apps/site/assets/lib/**/*.css',
                    'public/css/src/admin-lib.css': 'apps/admin/assets/lib/**/*.css',
                }
            }
        },
        cssmin: {
            minify: {
                files: [
                    {
                        expand: true,
                        cwd: 'public/css/src/',
                        src: ['*.css'],
                        dest: 'public/css/dist/',
                        ext: '.min.css'
                    },
                ]
            }
        },
        // Javascript
        concat: {
            options: {
                separator: '\n\n'
            },
            dist: {
                files: {
                    'public/js/src/admin-lib.js': 'apps/admin/assets/lib/**/*.js',
                    'public/js/src/admin.js': 'apps/admin/assets/js/*.js',
                    'public/js/src/site-lib.js': 'apps/site/assets/lib/**/*.js',
                    'public/js/src/site.js': 'apps/site/assets/js/*.js',
                }
            }
        },
        uglify: {
            dist: {
                files: [{
                    expand: true,
                    cwd: 'public/js/src/',
                    src: ['*.js'],
                    dest: 'public/js/dist/',
                    ext: '.min.js'
                }]
            }
        },
		copy: {
        	jquery: {
        		files: {
                    'public/lib/jquery/jquery.min.js': 'node_modules/jquery/dist/jquery.min.js',
                    'public/lib/jquery-ui/jquery-ui.min.js': 'node_modules/jquery-ui-dist/jquery-ui.min.js',
                    'public/lib/jquery-ui/jquery-ui.min.css': 'node_modules/jquery-ui-dist/jquery-ui.min.css'
				}
			},
            ckeditor: {
        	    files: [{
                    expand: true,
                    cwd: 'node_modules/ckeditor/',
                    src: [ '**/*', '!**/samples/**', '!**/adapters/**', '!bower.json', '!CHANGES.md', '!composer.json', '!LICENSE.md', '!package.json', '!README.md', '!yarn.lock' ],
                    dest: 'public/lib/ckeditor/'
                }]
            },
			fontawesome: {
				files: [{
                    expand: true,
                    cwd: 'vendor/fortawesome/font-awesome/webfonts',
                    src: '*.*',
                    dest: 'public/fonts'
                }]
			},
			app_images: {
				files: [{
					expand: true,
					cwd: 'apps/admin/assets/img/',
					src: ['**/*'],
					dest: 'public/img/admin/'
				}]
			}
		}
	});

	// Get apps
    var app_templates = grunt.file.expand({
        filter: "isDirectory",
        cwd: "apps"
    },["*"]);

    var apps = app_templates.map(function (t) {
        return t;
    });

    console.log( 'Found Apps:' );
    console.log( apps );

	// These plugins provide necessary tasks
    grunt.loadNpmTasks('grunt-contrib-sass');
    grunt.loadNpmTasks('grunt-concat-css');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
	grunt.loadNpmTasks('grunt-contrib-uglify-es');
    grunt.loadNpmTasks('grunt-contrib-concat');
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-contrib-copy');
    //grunt.loadNpmTasks('grunt-contrib-imagemin');
    grunt.loadNpmTasks('grunt-autoprefixer');

	// Tasks
    grunt.registerTask('default', ['sass', 'concat_css', 'concat']);
	grunt.registerTask('production', ['copy', 'sass', 'concat_css', 'cssmin', 'concat', 'uglify', 'copy:app_images']);
};
