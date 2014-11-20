(function() {
  tinymce.create('tinymce.plugins.Picatic', {
    /**
         * Initializes the plugin, this will be executed after the plugin has been created.
         * This call is done before the editor instance has finished it's initialization so use the onInit event
         * of the editor instance to intercept that event.
         *
         * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
         * @param {string} url Absolute URL to where the plugin is located.
         */

    init : function(ed, url) {
      // Register commands
      ed.addCommand('pushortcodes', function() {
        ed.windowManager.open({
          file : url + '/../../picatic-shortcodes-panel.php', // file that contains HTML for our modal window
          width : 500 + parseInt(ed.getLang('button.delta_width', 0)), // size of our window
          height : 400 + parseInt(ed.getLang('button.delta_height', 0)), // size of our window
          inline : 1
        }, {
          plugin_url : url
        });
      });
      // Register buttons
      ed.addButton('pushortcodes', {title : 'Picatic Shortcodes', cmd : 'pushortcodes', image : url + '/images/picatic-icon.png' });
    }
  });


  // Register plugin
  tinymce.PluginManager.add( 'pushortcodes', tinymce.plugins.Picatic );

})();

