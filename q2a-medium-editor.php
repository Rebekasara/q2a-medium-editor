<?php

/*
    Plugin Name: Medium Editor
*/

class qa_medium_editor
{
    var $urltoroot;

    function load_module($directory, $urltoroot)
    {
        $this->urltoroot=$urltoroot;
    }

    function option_default($option)
    {
        if ($option=='medium_editor_upload_max_size') {
            require_once QA_INCLUDE_DIR.'qa-app-upload.php';
            return min(qa_get_max_upload_size(), 1048576);
        }
        switch($option) {
            case 'medium_editor_enabled':
                return 1; // true
            case 'medium_editor_height':
                return 350;
            case 'medium_editor_upload_images':
                return 1;
            case 'medium_editor_upload_maximgwidth':
                return 600;
            default:
                return null;
        }

    }

    function bytes_to_mega_html($bytes)
    {
        return qa_html(number_format($bytes/1048576, 1));
    }

    function admin_form($qa_content)
    {
        require_once QA_INCLUDE_DIR.'qa-app-upload.php';

        $ok = null;
        if (qa_clicked('medium_editor_save')) {

            qa_opt('medium_editor_height', (int)qa_post_text('medium_editor_height'));
            qa_opt('medium_editor_upload_images', (bool)qa_post_text('medium_editor_upload_images'));
            qa_opt('medium_editor_upload_max_size', min(qa_get_max_upload_size(), 1048576*(float)qa_post_text('medium_editor_upload_max_size')));
            qa_opt('medium_editor_upload_maximgwidth', (int)qa_post_text('medium_editor_upload_maximgwidth'));

            $ok = qa_lang('admin/options_saved');
        }
        $fields = array();
        $fields[] = array(
            'type' => 'number',
            'label' => qa_lang('q2a_medium_editor_lang/default_height'),
            'suffix' => 'px',
            'tags' => 'name="medium_editor_height"',
            'value' => (int)qa_opt('medium_editor_height'),
        );
        $fields[] = array(
            'type' => 'checkbox',
            'id' => 'medium_editor_upload_images',
            'label' => qa_lang('q2a_medium_editor_lang/upload_images'),
            'tags' => 'name="medium_editor_upload_images" id="medium_editor_upload_images"',
            'value' => (int)qa_opt('medium_editor_upload_images'),
        );
        $fields[] = array(
            'type' => 'number',
            'id' => 'medium_editor_upload_max_size',
            'label' => qa_lang('q2a_medium_editor_lang/upload_max_size'),
            'suffix' => 'MB (max '.$this->bytes_to_mega_html(qa_get_max_upload_size()).')',
            'tags' => 'name="medium_editor_upload_max_size" id="medium_editor_upload_max_size"',
            'value' => $this->bytes_to_mega_html(qa_opt('medium_editor_upload_max_size')),
        );
        $fields[] = array(
            'type' => 'number',
            'id' => 'medium_editor_upload_maximgwidth',
            'label' => qa_lang('q2a_medium_editor_lang/upload_max_img_width'),
            'suffix' => 'px',
            'tags' => 'name="medium_editor_upload_maximgwidth" id="medium_editor_upload_maximgwidth"',
            'value' => (int)qa_opt('medium_editor_upload_maximgwidth'),
        );
        return array(
            'ok' => ($ok && !isset($error)) ? $ok : null,
            'fields' => $fields,
            'buttons' => array(
                array(
                    'label' => qa_lang_html('main/save_button'),
                    'tags' => 'name="medium_editor_save"',
                ),
            ),
        );
    }

    public function calc_quality($content, $format)
    {
        if ($format == '')
            return 0.8;

        if ($format == 'html')
            return 1.0;

        return 0;
    }

    public function get_field(&$qa_content, $content, $format, $fieldname, $rows)
    {
        $html = '';

        if(empty($format)) {
            $content = nl2br($content);
        }
        $html = '<textarea name="'.$fieldname.'" id="'.$fieldname.'"  class="editable qa-form-tall-text">'.$content.'</textarea>';
        $html .= "
        <script type=\"text/javascript\">
        var editor = new MediumEditor('.editable', {
            placeholder: {
                text: '".qa_lang_html('q2a_medium_editor_lang/placeholder')."',
                hydOnClick: true
            },
            paste: {
                forcePlainText: true,
            }
        });
        $(function() {
            $('.editable').mediumInsert({
                editor: editor,
                addons: {
                    images: {
                        preview:false,
                        captions:false,
                        fileUploadOptions: {
                            url: '".qa_opt('site_url').'medium-editor-upload'."',
                            acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i
                        },
                    },
                    embeds: {
                        placeholder: '".qa_lang_html('q2a_medium_editor_lang/placeholder_embed')."',
                        captions:false,
                    },
                },
            });
        });
        function get_content() {
            var allContents = editor.serialize();
            var editorId = editor.elements[0].id;
            return allContents[editorId].value;
        }
        </script>";
        return array(
            'type' => 'custom',
            'html' => $html,
        );
    }

    public function focus_script($fieldname)
    {
        // return "document.getElementById('" . $fieldname . "').focus();";
    }

    function update_script($fieldname)
    {
        // write html text from sceditor-iframe to textarea - important!
        $jscode = "$('textarea[name=\'".$fieldname."\']').val(get_content());";
        // debugging:
        // $jscode .= "console.log( 'textfield: '+ $('textarea[name=\'".$fieldname."\']').val() ); return false;";
        return $jscode;
    }

    public function read_post($fieldname)
    {
        $html = qa_post_text($fieldname);
        return array(
            'format' => 'html',
            'content' => qa_sanitize_html($html, false, true),
        );
    }
}

/*
    Omit PHP closing tag to help avoid accidental output
*/
