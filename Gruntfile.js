module.exports = function(grunt) {

  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    pot: {
      options: {
        text_domain: 'public-post-preview-configurator',
        dest: 'build/public-post-preview-configurator.pot',
        keywords: ['__', '_e', '_x:1,2c']
      },
      files: {
        src:  [ 'src/**/*.php' ],
        expand: true
      }
    },
    replace: {
      plugin_description: {
        src: ['src/public-post-preview-configurator.php'],
        dest: 'build/project_description_pot.txt',             // destination directory or file
        replacements: [{
          from: /[\s\S]*\* Description:       (.*)[\s\S]*/g,
          to: '\n#: Project description\nmsgid "$1"\nmsgstr ""\n'
        }]
      }
    },
    concat: {
      dist: {
        src: ['build/public-post-preview-configurator.pot', 'build/project_description_pot.txt'],
        dest: 'src/languages/public-post-preview-configurator.pot'
      }
    },
  });
  grunt.loadNpmTasks('grunt-pot');
  grunt.loadNpmTasks('grunt-text-replace');
  grunt.loadNpmTasks('grunt-contrib-concat');
  grunt.registerTask('wppot', ['pot', 'replace', 'concat']);
};