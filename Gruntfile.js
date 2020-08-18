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
                files: { // One day this will use the "apps" array
                    'public/css/src/site.css': 'apps/site/assets/sass/import.scss',
                }
            }
        },
        concat_css: {
            options: {},
            dev: {
                files: { // One day this will use the "apps" array
                    'public/css/src/site-lib.css': 'apps/site/assets/lib/**/*.css',
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
                files: { // One day this will use the "apps" array (+ core)
                    'public/js/src/ipscore.js': 'core/core-v1.00/assets/js/*.js',
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
            select2: {
                files: {
                    'public/lib/select2/select2.min.js': 'node_modules/select2/dist/js/select2.min.js',
                    'public/lib/select2/select2.min.css': 'node_modules/select2/dist/css/select2.min.css'
                }
            },
            fontawesome: {
                files: [{
                    expand: true,
                    cwd: 'node_modules/@fortawesome/fontawesome-free/webfonts',
                    src: '*.*',
                    dest: 'public/fonts'
                }]
            },
            app_images: {
                files: [{
					expand: true,
					cwd: 'apps/site/assets/img/',
					src: ['**/*'],
					dest: 'public/img/site/'
				}]
            }
        },
        watch: {
            javascript: {
                files: [
                    'apps/*/assets/*/**/*.js'
                ],
                tasks: ['concat'],
                options : {
                    spawn: false
                }
            },
            sass: {
                files: [
                    'apps/*/assets/*/**/*.scss'
                ],
                tasks: ['sass', 'concat_css'],
                options : {
                    spawn: false
                }
            },
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
