module.exports = function (grunt) {
    require('load-grunt-tasks')(grunt);
    var pkg = grunt.file.readJSON('package.json');
    var bannerTemplate = '/**\n' + ' * <%= pkg.title %> - v<%= pkg.version %> - <%= grunt.template.today("yyyy-mm-dd") %>\n' + ' * <%= pkg.author.url %>\n' + ' *\n' + ' * Copyright (c) <%= grunt.template.today("yyyy") %>;\n' + ' * Licensed GPLv2+\n' + ' */\n';
    var compactBannerTemplate = '/** ' + '<%= pkg.title %> - v<%= pkg.version %> - <%= grunt.template.today("yyyy-mm-dd") %> | <%= pkg.author.url %> | Copyright (c) <%= grunt.template.today("yyyy") %>; | Licensed GPLv2+' + ' **/\n';
    // Project configuration
    grunt.initConfig({
        pkg: pkg,
        watch: {
            styles: {
                files: [
                    'assets/**/*.css',
                    'assets/**/*.scss'
                ],
                tasks: ['styles'],
                options: {
                    spawn: false,
                    livereload: true,
                    debounceDelay: 500
                }
            },
            scripts: {
                files: ['assets/**/*.js'],
                tasks: ['scripts'],
                options: {
                    spawn: false,
                    livereload: true,
                    debounceDelay: 500
                }
            },
            php: {
                files: [
                    '**/*.php',
                    '!vendor/**.*.php'
                ],
                tasks: ['php'],
                options: {
                    spawn: false,
                    debounceDelay: 500
                }
            }
        },
        makepot: {
            dist: {
                options: {
                    domainPath: '/languages/',
                    potFilename: pkg.name + '.pot',
                    type: 'wp-plugin'
                }
            }
        },
        addtextdomain: {
            dist: {
                options: { textdomain: pkg.name },
                target: { files: { src: ['**/*.php'] } }
            }
        },
        jshint: {
            all: [
                'assets/js/components/**/*.js',
                '!**/*.min.js'
            ],
            options: {
                browser: true,
                predef: [
                    'document',
                    'window',
                    'jQuery',
                    'require',
                    'undefined'
                ]
            }
        },
        concat: {
            options: {
                stripBanners: true,
                banner: bannerTemplate
            },
            dist: { files: { 'assets/js/minecraft-suite.js': 'assets/js/components/**/*.js' } }
        },
        uglify: {
            dist: {
                files: { 'assets/js/minecraft-suite.min.js': 'assets/js/minecraft-suite.js' },
                options: { banner: compactBannerTemplate }
            }
        },
        sass: {
            dist: {
                options: { sourceMap: true },
                files: { 'assets/css/minecraft-suite.css': 'assets/css/sass/styles.scss' }
            }
        },
        cssmin: { dist: { files: { 'assets/css/minecraft-suite.min.css': 'assets/css/minecraft-suite.css' } } },
        usebanner: {
            taskName: {
                options: {
                    position: 'top',
                    banner: bannerTemplate,
                    linebreak: true
                },
                files: { src: ['assets/css/minecraft-suite.min.css'] }
            }
        }
    });
    // Default task.
    grunt.registerTask('scripts', [
        'jshint',
        'concat',
        'uglify'
    ]);
    grunt.registerTask('styles', [
        'sass',
        'cssmin',
        'usebanner'
    ]);
    grunt.registerTask('php', [
        'addtextdomain',
        'makepot'
    ]);
    grunt.registerTask('default', [
        'styles',
        'scripts',
        'php'
    ]);
    grunt.util.linefeed = '\n';
};