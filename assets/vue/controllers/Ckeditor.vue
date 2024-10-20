<template>
    <div id="ckeditor-app">
        <label for="{{ formId }}" class="form-label">{{ label }}</label>
        <div style="position: relative;">
            <ckeditor :editor="editor" v-model="editorData" @ready="onReady" @input="onChange" :config="editorConfig"></ckeditor>
            <textarea style="position: absolute; top: 20px; left: 20px; opacity: 0;" :id="id" :name="name" required :value="editorData"></textarea>
        </div>
    </div>
</template>

<script>
    import { ClassicEditor } from '@ckeditor/ckeditor5-editor-classic';
    import CKEditor from '@ckeditor/ckeditor5-vue';
    import CKEditorInspector from '@ckeditor/ckeditor5-inspector';
    import { Alignment } from '@ckeditor/ckeditor5-alignment';
    import { Bold, Italic, Strikethrough, Underline } from '@ckeditor/ckeditor5-basic-styles';
    import { Link } from '@ckeditor/ckeditor5-link';
    import { FontBackgroundColor, FontColor, FontSize, FontFamily, } from '@ckeditor/ckeditor5-font';
    import { Table, TableCellProperties, TableProperties, TableToolbar } from '@ckeditor/ckeditor5-table';
    import { ImageUpload, Image, ImageCaption, ImageStyle, ImageToolbar, ImageResizeEditing, ImageResizeButtons } from '@ckeditor/ckeditor5-image';
    import { SimpleUploadAdapter } from '@ckeditor/ckeditor5-upload';
    import { List } from '@ckeditor/ckeditor5-list';
    import { MediaEmbed } from '@ckeditor/ckeditor5-media-embed';
    import { Heading } from '@ckeditor/ckeditor5-heading';
    import { Undo } from '@ckeditor/ckeditor5-undo';

    export default {
        name: 'ckeditor-app',
        components: {
            ckeditor: CKEditor.component,
        },
        props: {
            id: String,
            label: String,
            name: String,
            value: String,
            upload_url: String,
            toolbar: Array,
        },
        data() {
            return {
                editor: ClassicEditor,
                editorData: this.value,
                id: this.formId,
                editorConfig: {
                    plugins: [
                        Heading,
                        Bold,
                        Italic,
                        Strikethrough,
                        Underline,
                        Alignment,
                        Link,
                        FontBackgroundColor,
                        FontColor,
                        FontSize,
                        FontFamily,
                        Table, TableCellProperties, TableProperties, TableToolbar,
                        List,
                        ImageUpload, Image, SimpleUploadAdapter, ImageCaption, ImageStyle, ImageToolbar, ImageResizeEditing, ImageResizeButtons,
                        MediaEmbed,
                        Undo
                    ],
                    toolbar: this.toolbar,
                    image: {
                        resizeOptions: [
                            {
                                name: 'resizeImage:original',
                                value: null,
                                label: 'Original',
                                icon: 'original'
                            },
                            {
                                name: 'resizeImage:25',
                                value: '25',
                                label: '25%',
                                icon: 'small'
                            },
                            {
                                name: 'resizeImage:50',
                                value: '50',
                                label: '50%',
                                icon: 'medium'
                            },
                            {
                                name: 'resizeImage:75',
                                value: '75',
                                label: '75%',
                                icon: 'large'
                            }
                        ],
                        styles: {
                            options: ['alignLeft', 'alignRight', 'block']
                        },
                        toolbar: [
                            'resizeImage:25',
                            'resizeImage:50',
                            'resizeImage:75',
                            'resizeImage:original',
                            'imageTextAlternative',
                            'toggleImageCaption',
                            'imageStyle:alignLeft',
                            'imageStyle:alignRight',
                            'imageStyle:block'
                        ]
                    },
                    table: {
                        contentToolbar: [ 'tableColumn', 'tableRow', 'mergeTableCells' ]
                    },
                    alignment: {
                        options: [
                            { name: 'left', className: 'my-align-left' },
                            { name: 'right', className: 'my-align-right' }
                        ]
                    },
                    language: 'fr',
                    simpleUpload: {
                        uploadUrl: this.upload_url,
                    },
                  },
            };
        },
        methods: {
            onReady(editor) {
                CKEditorInspector.attach(editor);
            },
            onChange(data) {
                console.log('onChange', data);
            },
        },
        created() {
            console.log('created', this);
        }
    };
</script>
  