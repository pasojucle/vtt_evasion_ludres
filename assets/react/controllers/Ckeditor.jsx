import { useEffect, useRef, useState } from 'react';
import { CKEditor } from '@ckeditor/ckeditor5-react';
import { ClassicEditor } from '@ckeditor/ckeditor5-editor-classic';
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

import 'ckeditor5/ckeditor5.css';


function App({id, label, name, value, upload_url, toolbar, environment}) {

    const editorRef = useRef();
    const [ isMounted, setMounted ] = useState( false );

    const [ editorData, setEditorData ] = useState( false );

    return (
        <div id="ckeditor-app">
        <label htmlFor={ id } className="form-label">{ label }</label>
        <div style={{position: 'relative'}}>
            <CKEditor
                editor={ ClassicEditor }
                config={ {
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
                    toolbar: toolbar,
                    initialData: value,
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
                        uploadUrl: upload_url,
                    },
                }}
                onReady={(editor) => {
                    editorRef.current = editor;
                    if (environment === 'dev') {
                        CKEditorInspector.attach(editor);
                    }
                }}
                onChange={() => {
                    setEditorData(editorRef.current?.getData())
                }}
            />
            <textarea style={{position: 'absolute', top: 20, left: 20, opacity: 0}} id={id} name={name} readOnly required value={editorData}></textarea>
        </div>
    </div>
    );
}

export default App;