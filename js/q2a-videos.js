;(function ($, window, document, undefined) {

    'use strict';

    /** Default values */
    var pluginName = 'mediumInsert',
        addonName = 'Videos', // first char is uppercase
        defaults = {
            label: '<span class="fa fa-video-camera"></span>'
        };

    /**
     * Custom Addon object
     *
     * Sets options, variables and calls init() function
     *
     * @constructor
     * @param {DOM} el - DOM element to init the plugin on
     * @param {object} options - Options to override defaults
     * @return {void}
     */

    function Videos (el, options) {
        this.el = el;
        this.$el = $(el);
        this.templates = window.MediumInsert.Templates;
        this.core = this.$el.data('plugin_'+ pluginName);

        this.options = $.extend(true, {}, defaults, options);

        this._defaults = defaults;
        this._name = pluginName;

        this.init();
    }

    /**
     * Initialization
     *
     * @return {void}
     */

    Videos.prototype.init = function () {
        this.events();
    };

    /**
     * Event listeners
     *
     * @return {void}
     */

    Videos.prototype.events = function () {
    };

    /**
     * Get the Core object
     *
     * @return {object} Core object
     */
    Videos.prototype.getCore = function () {
        return this.core;
    };

    /**
     * Add custom content
     *
     * This function is called when user click on the addon's icon
     *
     * @return {void}
     */

    Videos.prototype.add = function () {
        var $place = this.$el.find('.medium-insert-active');

        if ($place.is('p')) { // replace p to div because p cannot have children
            $place.replaceWith('<div class="medium-insert-active">' + $place.html() + '</div>');
            $place = this.$el.find('.medium-insert-active');
            if ($place.next().is('p')) {
                this.core.moveCaret($place.next());
            } else {
                $place.after('<p><br></p>'); // add empty paragraph so we can move the caret to the next line.
                this.core.moveCaret($place.next());
            }
        }
        $place.addClass('medium-insert-videos');
        if ($place.find('progress').length === 0) {
            // $place.append(this.templates['src/js/templates/images-progressbar.hbs']());
            window.componentHandler.upgradeDom();
        }
        videoDialog.showModal();
        this.core.hideButtons();
    };


    /** Addon initialization */

    $.fn[pluginName + addonName] = function (options) {
        return this.each(function () {
            if (!$.data(this, 'plugin_' + pluginName + addonName)) {
                $.data(this, 'plugin_' + pluginName + addonName, new Videos(this, options));
            }
        });
    };
})(jQuery, window, document);


$(function() {
  var Const = {};
  Const.MAX_FILE_SIZE_MB = 300;
  var uploadedData = {};

  var uploadDisableFlg = true;
  $videoEl = $('#movie-file')
  $videoEl.on('change', function() {
    console.log('file changed');
    var file = this.files[0];
    if(file) {
      console.log('file size:' + file.size);
      console.log('limit:' + Const.MAX_FILE_SIZE_MB * 1024 * 1024);
      var fileSizeMB = (file.size / (1024 * 1024)).toFixed(0);
      $('#video-size').text(fileSizeMB);
      if(file.size > Const.MAX_FILE_SIZE_MB * 1024 * 1024) {
        $('#video-size-error').text('サイズが大きすぎます。');
        uploadDisableFlg = true;
      } else {
        uploadDisableFlg = false;
        $('#video-size-error').text('');
      }
    } else {
      uploadDisableFlg = true;
    }
    $('#upload-button').prop('disabled', uploadDisableFlg);
  });

  $videoFormEl = $('#upload-form');
  $videoFormEl.transloadit({
    wait: true, // wait encoding after uploading
    params: {
      auth: { key: "01468700276c11e7935dd5bfdcd1978a" },
      template_id: "0c671ee0276e11e7bbdd13fca8166c81"
    },
    modal: false,
    onError: function(assembly) {
      alert(assembly.error + ': ' + assembly.message);
    },
    onStart: function (assembly) {
      console.log(">>> onStart", assembly);
      $('#v-upload-message').text('アップロード中です。');
    },
    onProgress: function (bytesIn, totalBytes) {
      console.log('>>>onProgress', bytesIn, totalBytes);
      var progress = (bytesIn / totalBytes * 100).toFixed(2);
      if(progress == 100) {
          return;
      }

      progressbar = document.querySelector('#v-upload-progress');
      progressbar.MaterialProgress.setProgress(progress);
      progressbar.MaterialProgress.setBuffer(87);
    },
    onSuccess: function (assembly) {
      progressbar = document.querySelector('#v-upload-progress');
      progressbar.parentNode.removeChild(progressbar);
      $('#v-upload-message').text('');

      console.log('Assembly finished successfully with', assembly.ok);
      console.log(assembly);
      $('.medium-insert-videos').html(
        '<video class="video-js" controls preload="auto" width="640" poster="' + uploadedData['image_thumb'] + '" data-setup="{}">' +
          '<source id="mp4-source" type="video/mp4" src="' + uploadedData['video_mp4'] + '">' +
          '<source id="webm-source" src="' + uploadedData['video_webm'] + '" type="video/webm">' +
        '</video>'
        + '[uploaded-video="' + assembly.assembly_id + '"]'
      );

      videoDialog.close();
    },
    onExecuting: function () {
      console.log('>>Uploading finished!');
    },
    onUpload: function (uploadedFile) {
      console.log('>>Upload added', uploadedFile);
      $('#v-upload-progress').addClass("mdl-progress__indeterminate");
      $('#v-upload-message').text('アップロードが完了しました。動画を処理しています。この処理には数分かかることがあります');
    },
    onResult: function (stepName, result) {
      console.log('Result added', stepName, result);
      uploadedData[stepName] = result['ssl_url'];
    },
    onError: function (error) {
    },
    onDisconnect: function () {
      console.log('Disconnected!');
    },
    onReconnect: function (error) {
      console.log('Reconnected!');
    }
  });
});
