/* eslint-env node, commonjs */
/* eslint-disable @typescript-eslint/no-var-requires */

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

module.exports = function (grunt) {

  const sass = require('sass');

  /**
   * Grunt stylefmt task
   */
  grunt.registerMultiTask('formatsass', 'Grunt task for stylefmt', function () {
    let done = this.async(),
      stylefmt = require('@ronilaukkarinen/stylefmt'),
      postcss = require('postcss'),
      scss = require('postcss-scss'),
      files = this.filesSrc.filter(function (file) {
        return grunt.file.isFile(file);
      }),
      counter = 0;
    this.files.forEach(function (file) {
      file.src.filter(function (filepath) {
        let content = grunt.file.read(filepath);
        let settings = {
          from: filepath,
          syntax: scss
        };
        postcss([stylefmt]).process(content, settings).then(function (result) {
          grunt.file.write(file.dest, result.css);
          grunt.log.success('Source file "' + filepath + '" was processed.');
          counter++;
          if (counter >= files.length) {
            done(true);
          }
        });
      });
    });
  });

  // Project configuration.
  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    paths: {
      sources: 'Sources/',
      root: '../',
      sass: '<%= paths.sources %>Sass/',
      typescript: '<%= paths.sources %>TypeScript/',
      node_modules: 'node_modules/',
      deepl: '<%= paths.root %>',
    },
    stylelint: {
      options: {
        configFile: '<%= paths.root %>/Build/.stylelintrc',
      },
      sass: ['<%= paths.sass %>**/*.scss']
    },
    formatsass: {
      sass: {
        files: [{
          expand: true,
          cwd: '<%= paths.sass %>',
          src: ['**/*.scss'],
          dest: '<%= paths.sass %>'
        }]
      }
    },
    sass: {
      options: {
        implementation: sass,
        outputStyle: 'expanded',
        precision: 8
      },
      deepl: {
        files: {
          '<%= paths.deepl %>Public/Css/backend.css': '<%= paths.sass %>backend.scss'
        }
      },
    },
    postcss: {
      options: {
        map: false,
        processors: [
          require('autoprefixer')(),
          require('postcss-clean')({
            rebase: false,
            format: 'keep-breaks',
            level: {
              1: {
                specialComments: 0
              }
            }
          }),
          require('postcss-banner')({
            banner: 'This file is part of the TYPO3 CMS project.\n' +
              '\n' +
              'It is free software; you can redistribute it and/or modify it under\n' +
              'the terms of the GNU General Public License, either version 2\n' +
              'of the License, or any later version.\n' +
              '\n' +
              'For the full copyright and license information, please read the\n' +
              'LICENSE.txt file that was distributed with this source code.\n' +
              '\n' +
              'The TYPO3 project - inspiring people to share!',
            important: true,
            inline: false
          })
        ]
      },
      deepl: {
        src: '<%= paths.deepl %>Public/Css/*.css'
      }
    },
    exec: {
      ts: ((process.platform === 'win32') ? 'node_modules\\.bin\\tsc.cmd' : './node_modules/.bin/tsc') + ' --project tsconfig.json',
      'npm-install': 'npm install'
    },
    eslint: {
      options: {
        cache: true,
        cacheLocation: './.cache/eslintcache/',
        overrideConfigFile: '.eslintrc.json'
      },
      files: {
        src: [
          '<%= paths.typescript %>/**/*.ts',
          './types/**/*.ts'
        ]
      }
    },
    watch: {
      options: {
        livereload: true
      },
      sass: {
        files: '<%= paths.sass %>**/*.scss',
        tasks: ['css', 'bell']
      },
      ts: {
        files: '<%= paths.typescript %>/**/*.ts',
        tasks: ['scripts', 'bell']
      }
    },
    newer: {
      options: {
        cache: './.cache/grunt-newer/'
      }
    }
  });

  // Register tasks
  grunt.loadNpmTasks('grunt-sass');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-rollup');
  grunt.loadNpmTasks('grunt-npmcopy');
  grunt.loadNpmTasks('grunt-terser');
  grunt.loadNpmTasks('@lodder/grunt-postcss');
  grunt.loadNpmTasks('grunt-contrib-copy');
  grunt.loadNpmTasks('grunt-contrib-clean');
  grunt.loadNpmTasks('grunt-exec');
  grunt.loadNpmTasks('grunt-eslint');
  grunt.loadNpmTasks('grunt-stylelint');
  grunt.loadNpmTasks('grunt-newer');
  grunt.loadNpmTasks('grunt-concurrent');

  /**
   * grunt lint
   *
   * call "$ grunt lint"
   *
   * this task does the following things:
   * - eslint
   * - stylelint
   * - lintspaces
   */
  grunt.registerTask('lint', ['concurrent:lint']);

  /**
   * grunt css task
   *
   * call "$ grunt css"
   *
   * this task does the following things:
   * - formatsass
   * - sass
   * - postcss
   */
  grunt.registerTask('css', ['formatsass', 'newer:sass', 'newer:postcss']);

  /**
   * grunt compile-typescript task
   *
   * call "$ grunt compile-typescript"
   *
   * This task does the following things:
   * - 1) Check all TypeScript files (*.ts) with ESLint which are located in sysext/<EXTKEY>/Resources/Private/TypeScript/*.ts
   * - 2) Compiles all TypeScript files (*.ts) which are located in sysext/<EXTKEY>/Resources/Private/TypeScript/*.ts
   */
  grunt.registerTask('compile-typescript', ['tsconfig', 'eslint', 'exec:ts']);

  /**
   * grunt scripts task
   *
   * call "$ grunt scripts"
   *
   * this task does the following things:
   * - 1) Compiles TypeScript (see compile-typescript)
   * - 2) Copy all generated JavaScript files to public folders
   * - 3) Minify build
   */
  grunt.registerTask('scripts', ['compile-typescript', 'newer:terser:typescript', 'newer:copy:ts_files']);

  /**
   * grunt clear-build task
   *
   * call "$ grunt clear-build"
   *
   * Removes all build-related assets, e.g. cache and built files
   */
  grunt.registerTask('clear-build', function () {
    grunt.option('force');
    grunt.file.delete('.cache');
    grunt.file.delete('JavaScript');
  });

  /**
   * grunt tsconfig task
   *
   * call "$ grunt tsconfig"
   *
   * this task updates the tsconfig.json file with modules paths for all sysexts
   */
  grunt.task.registerTask('tsconfig', function () {
    const config = grunt.file.readJSON('tsconfig.json');
    const typescriptPath = grunt.config.get('paths.typescript');
    config.compilerOptions.paths = {};
    grunt.file.expand(typescriptPath + '*/').map(dir => dir.replace(typescriptPath, '')).forEach((path) => {
      const extname = path.match(/^([^/]+?)\//)[1].replace(/_/g, '-')
      config.compilerOptions.paths['@typo3/' + extname + '/*'] = [path + '*'];
    });

    grunt.file.write('tsconfig.json', JSON.stringify(config, null, 4) + '\n');
  });

  /**
   * Outputs a "bell" character. When output, modern terminals flash shortly or produce a notification (usually configurable).
   * This Grunt config uses it after the "watch" task finished compiling, signaling to the developer that her/his changes
   * are now compiled.
   */
  grunt.registerTask('bell', () => console.log('\u0007'));

  /**
   * grunt default task
   *
   * call "$ grunt default"
   *
   * this task does the following things:
   * - execute update task
   * - execute copy task
   * - compile sass files
   * - uglify js files
   * - minifies svg files
   * - compiles TypeScript files
   */
  grunt.registerTask('default', ['clear-build', 'clean', 'update', 'concurrent:copy_static', 'concurrent:compile_flags', 'concurrent:compile_assets', 'concurrent:minify_assets']);

  /**
   * grunt build task (legacy, for those used to it). Use `grunt default` instead.
   *
   * call "$ grunt build"
   *
   * this task does the following things:
   * - execute exec:npm-install task
   * - execute all task
   */
  grunt.registerTask('build', ['exec:npm-install', 'default']);
};
