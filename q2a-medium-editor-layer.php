<?php

/*
    Plugin Name: Medium Editor
*/


class qa_html_theme_layer extends qa_html_theme_base
{

    const EDITOR_NAME = 'Medium Editor';

    private function is_medium_editor_active() {
      if(($this->template === 'ask' && qa_opt('editor_for_qs') === self::EDITOR_NAME)
        || ($this->template === 'question' && qa_opt('editor_for_as') === self::EDITOR_NAME)
        || ($this->template === 'question' && qa_opt('editor_for_cs') === self::EDITOR_NAME)
        || ($this->template === 'blog-new' && qa_opt('qas_blog_editor_for_ps') === self::EDITOR_NAME)
        || ($this->template === 'blog' && qa_opt('qas_blog_editor_for_cs') === self::EDITOR_NAME)) {
          return true;
      }
      return false;

    }

    function head_script()
    {
        qa_html_theme_base::head_script();
        if($this->is_medium_editor_active()) {
          $this->output_css();
          $this->output_js();
        }
    }

    function q_view_content($q_view)
    {
        if (isset($q_view['content'])){
            $q_view['content'] = $this->embed_replace($q_view['content']);
        }
        qa_html_theme_base::q_view_content($q_view);
    }

    function a_item_content($a_item)
    {
        if (isset($a_item['content'])) {
            $a_item['content'] = $this->embed_replace($a_item['content']);
        }
        qa_html_theme_base::a_item_content($a_item);
    }

    function c_item_content($c_item)
    {
        if (isset($c_item['content'])) {
            $c_item['content'] = $this->embed_replace($c_item['content']);
        }
        qa_html_theme_base::c_item_content($c_item);
    }

    function body_footer()
    {
        qa_html_theme_base::body_footer();

        if($this->is_medium_editor_active()) {
            if (strpos(qa_opt('site_theme'), 'q2a-material-lite') !== false) {
                $this->output_dialog();
            }
        }
    }

    private function output_css()
    {
        $components = QA_HTML_THEME_LAYER_URLTOROOT . 'bower_components/';
        // CSS files
        $this->output('<link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet">');
        $css_files = array(
            'medium-editor/dist/css/medium-editor.min.css',
            'medium-editor/dist/css/themes/default.min.css',
            'medium-editor-insert-plugin/dist/css/medium-editor-insert-plugin.min.css'
        );
        foreach ($css_files as $css) {
            $this->output('<link rel="stylesheet" type="text/css" href="' . $components . $css .'" />');
        }
        $this->output('<link rel="stylesheet" type="text/css" href="'.QA_HTML_THEME_LAYER_URLTOROOT.'css/custom.css" />');if (strpos(qa_opt('site_theme'), 'q2a-material-lite') !== false) {
            $this->output('<link rel="stylesheet" type="text/css" href="'.QA_HTML_THEME_LAYER_URLTOROOT.'css/dialog-polyfill.css" />');
        }
    }

    private function output_js()
    {
        $components = QA_HTML_THEME_LAYER_URLTOROOT . 'bower_components/';
        // JS files
        $js_files = array(
            'medium-editor/dist/js/medium-editor.js',
            'handlebars/handlebars.runtime.min.js',
            'jquery-sortable/source/js/jquery-sortable-min.js',
            'jquery-sortable/source/js/jquery-sortable-min.js',
            'blueimp-file-upload/js/vendor/jquery.ui.widget.js',
            'blueimp-file-upload/js/jquery.iframe-transport.js',
            'blueimp-file-upload/js/jquery.fileupload.js',
            'medium-editor-insert-plugin/dist/js/medium-editor-insert-plugin.min.js',
        );
        foreach ($js_files as $js) {
            $this->output('<script src="'. $components . $js . '"></script>');
        }
        $this->output('<script src="'. QA_HTML_THEME_LAYER_URLTOROOT . 'js/q2a-embeds.js' . '"></script>');
        $this->output('<script src="'. QA_HTML_THEME_LAYER_URLTOROOT . 'js/q2a-videos.js' . '"></script>');
        $this->output('<script src="'. QA_HTML_THEME_LAYER_URLTOROOT . 'js/q2a-editor.js' . '"></script>');
        $this->output('<script src="//assets.transloadit.com/js/jquery.transloadit2-v2-latest.js"></script>');
        $this->output('<script src="'. QA_HTML_THEME_LAYER_URLTOROOT . 'js/transloadit.js' . '"></script>');
        if (strpos(qa_opt('site_theme'), 'q2a-material-lite') !== false) {
            $this->output('<script src="'. QA_HTML_THEME_LAYER_URLTOROOT . 'js/q2a-images.js' . '"></script>');
            $this->output('<script src="'. QA_HTML_THEME_LAYER_URLTOROOT . 'js/dialog-polyfill.js' . '"></script>');
        }
    }

    function embed_replace($text)
    {
        $types = array(
            'youtube' => array(
                array(
                    'https{0,1}:\/\/w{0,3}\.*youtube\.com\/watch\?\S*v=([A-Za-z0-9_-]+)[^< ]*',
                    '<iframe width="420" height="315" src="//www.youtube.com/embed/$1" frameborder="0" allowfullscreen></iframe>'
                ),
                array(
                    'https{0,1}:\/\/w{0,3}\.*youtu\.be\/([A-Za-z0-9_-]+)[^< ]*',
                    '<iframe width="420" height="315" src="//www.youtube.com/embed/$1" frameborder="0" allowfullscreen></iframe>'
                ),
            ),
        );

        foreach($types as $t => $ra) {
            foreach($ra as $r) {
                $text = preg_replace('/<a[^>]+>'.$r[0].'<\/a>/i',$r[1],$text);
                $text = preg_replace('/(?<![\'"=])'.$r[0].'/i',$r[1],$text);
            }
        }
        $text = preg_replace('/class="plain_url"/i','class="video video-youtube"',$text);
        return $text;
    }

    function output_dialog()
    {
        $html = file_get_contents(QA_PLUGIN_DIR . 'q2a-medium-editor/html/image_error_dialog.html');
        $html .= file_get_contents(QA_PLUGIN_DIR . 'q2a-medium-editor/html/video_dialog.html');
        $this->output($html);
    }

} // end qa_html_theme_layer


/*
    Omit PHP closing tag to help avoid accidental output
*/
