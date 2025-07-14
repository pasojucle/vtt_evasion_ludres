import React from 'react';
import { useAuth } from '../hooks/useAuth'
import { imageUpload } from '../helpers/imageUploaderHelper'

export default function TiptapToolbar({ editor }) {
    if (!editor) return null;

    const { token } = useAuth();
    const base = 'px-1 py-1 rounded-md border text-xs font-medium transition';
    const active = 'bg-blue-500 text-white border-blue-600 hover:bg-blue-600';
    const inactive = 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100';

    const ALIGNMENTS = [
        { value: 'left', icon: <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="size-6"><path strokeLinecap="round" strokeLinejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12" /></svg> },
        { value: 'center', icon: <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="size-6"><path strokeLinecap="round" strokeLinejoin="round" d="M3.75 9h16.5m-16.5 6.75h16.5" /></svg> },
        { value: 'right', icon: <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="size-6"><path strokeLinecap="round" strokeLinejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M12 17.25h8.25" /></svg> },
        { value: 'justify', icon: <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="size-6"><path strokeLinecap="round" strokeLinejoin="round" d="M3.75 5.25h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5" /></svg> },

    ];
    const HEADINGS = [
        { value: 1, icon: <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="size-6"><path strokeLinecap="round" strokeLinejoin="round" d="M2.243 4.493v7.5m0 0v7.502m0-7.501h10.5m0-7.5v7.5m0 0v7.501m4.501-8.627 2.25-1.5v10.126m0 0h-2.25m2.25 0h2.25" /></svg> },
        { value: 2, icon: <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="size-6"><path strokeLinecap="round" strokeLinejoin="round" d="M21.75 19.5H16.5v-1.609a2.25 2.25 0 0 1 1.244-2.012l2.89-1.445c.651-.326 1.116-.955 1.116-1.683 0-.498-.04-.987-.118-1.463-.135-.825-.835-1.422-1.668-1.489a15.202 15.202 0 0 0-3.464.12M2.243 4.492v7.5m0 0v7.502m0-7.501h10.5m0-7.5v7.5m0 0v7.501" /></svg> },
        { value: 3, icon: <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="size-6"><path strokeLinecap="round" strokeLinejoin="round" d="M20.905 14.626a4.52 4.52 0 0 1 .738 3.603c-.154.695-.794 1.143-1.504 1.208a15.194 15.194 0 0 1-3.639-.104m4.405-4.707a4.52 4.52 0 0 0 .738-3.603c-.154-.696-.794-1.144-1.504-1.209a15.19 15.19 0 0 0-3.639.104m4.405 4.708H18M2.243 4.493v7.5m0 0v7.502m0-7.501h10.5m0-7.5v7.5m0 0v7.501" /></svg> },
    ];

    const LANGUAGES = ['javascript', 'html', 'css', 'php', 'python']

    const getBtnClass = (isActive) =>
        `${base} ${isActive ? active : inactive}`;

    const addImage = () => {
        const imageUploaderEl = document.getElementById('imageUploader');
        if (imageUploaderEl) {
            imageUploaderEl.click();
        }
    }

    const uploadImage = async (event) => {
        const file = event.target.files[0]
        if (!file) return
        decodeURIComponent(imageUpload);
        await imageUpload(file, token).then((url) => {
            editor.chain().focus().setImage({ src: url }).run()
        })
    }

    const addLink = () => {
        const previousUrl = editor.getAttributes('link').href;
        const url = window.prompt('URL du lien :', previousUrl);

        if (url === null) return;
        if (url === '') {
            editor.chain().focus().unsetLink().run();
            return;
        }

        editor.chain().focus().setLink({ href: url }).run();
    };

    const setLanguage = (lang) => {
        const { state, view } = editor
        const { $from, to } = state.selection
        const node = $from.node()

        if (node.type.name === 'codeBlock') {
            editor.commands.updateAttributes('codeBlock', { language: lang })
        }
    }

    return (
        <div className="flex flex-wrap gap-1 content-stretch bg-gray-50 p-1 border rounded-t-md shadow-sm">
            {/* Text Style */}
            <button type="button" title="Bold" className={getBtnClass(editor.isActive('bold'))} onClick={() => editor.chain().focus().toggleBold().run()}>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="size-6">
                    <path strokeLinejoin="round" d="M6.75 3.744h-.753v8.25h7.125a4.125 4.125 0 0 0 0-8.25H6.75Zm0 0v.38m0 16.122h6.747a4.5 4.5 0 0 0 0-9.001h-7.5v9h.753Zm0 0v-.37m0-15.751h6a3.75 3.75 0 1 1 0 7.5h-6m0-7.5v7.5m0 0v8.25m0-8.25h6.375a4.125 4.125 0 0 1 0 8.25H6.75m.747-15.38h4.875a3.375 3.375 0 0 1 0 6.75H7.497v-6.75Zm0 7.5h5.25a3.75 3.75 0 0 1 0 7.5h-5.25v-7.5Z" />
                </svg>
            </button>
            <button type="button" title="Italic" className={getBtnClass(editor.isActive('italic'))} onClick={() => editor.chain().focus().toggleItalic().run()}>
                <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M10 4v3h2.21l-3.42 10H6v3h8v-3h-2.21l3.42-10H18V4z" />
                </svg>
            </button>
            <button type="button" title="Barré" className={getBtnClass(editor.isActive('strike'))} onClick={() => editor.chain().focus().toggleStrike().run()}>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="size-6">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M12 12a8.912 8.912 0 0 1-.318-.079c-1.585-.424-2.904-1.247-3.76-2.236-.873-1.009-1.265-2.19-.968-3.301.59-2.2 3.663-3.29 6.863-2.432A8.186 8.186 0 0 1 16.5 5.21M6.42 17.81c.857.99 2.176 1.812 3.761 2.237 3.2.858 6.274-.23 6.863-2.431.233-.868.044-1.779-.465-2.617M3.75 12h16.5" />
                </svg>

            </button>

            {/* Headings */}
            {HEADINGS.map((level) => (
                <button type="button" title={`Header H${level.value}`}
                    key={level.value}
                    className={getBtnClass(editor.isActive('heading', {level: level.value}))}
                    onClick={() => editor.chain().focus().toggleHeading({level: level.value}).run()}
                >
                    {level.icon}
                </button>
            ))}

            {/* Lists */}
            <button type="button" title="Liste à puces" className={getBtnClass(editor.isActive('bulletList'))} onClick={() => editor.chain().focus().toggleBulletList().run()}>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="size-6">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                </svg>
            </button>
            <button type="button" title="Liste numérotées" className={getBtnClass(editor.isActive('orderedList'))} onClick={() => editor.chain().focus().toggleOrderedList().run()}>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="size-6">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M8.242 5.992h12m-12 6.003H20.24m-12 5.999h12M4.117 7.495v-3.75H2.99m1.125 3.75H2.99m1.125 0H5.24m-1.92 2.577a1.125 1.125 0 1 1 1.591 1.59l-1.83 1.83h2.16M2.99 15.745h1.125a1.125 1.125 0 0 1 0 2.25H3.74m0-.002h.375a1.125 1.125 0 0 1 0 2.25H2.99" />
                </svg>
            </button>

            {/* Quote, Code block */}
            <button type="button" title="Citation" className={getBtnClass(editor.isActive('blockquote'))} onClick={() => editor.chain().focus().toggleBlockquote().run()}>
                <svg className="w-5 h-5" fill="none" stroke="currentColor" strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" viewBox="0 0 24 24">
                    <path d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2c1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V20c0 1 0 1 1 1m12 0c3 0 7-1 7-8V5c0-1.25-.757-2.017-2-2h-4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2h.75c0 2.25.25 4-2.75 4v3c0 1 0 1 1 1" />
                </svg>
            </button>
            <button type="button" title="Code block" className={getBtnClass(editor.isActive('codeBlock'))} onClick={() => editor.chain().focus().toggleCodeBlock().run()}>
                <svg className="w-5 h-5" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
                    <path d="M16 18l6-6-6-6M8 6L2 12l6 6" />
                </svg>
            </button>
            {editor.isActive('codeBlock') && (
                <select
                    onChange={(e) => setLanguage(e.target.value)}
                    value={editor.getAttributes('codeBlock').language || ''}
                    className={getBtnClass(editor.isActive('codeBlock'))}
                >
                    <option value="">Langage</option>
                    {LANGUAGES.map((lang) => (
                        <option key={lang} value={lang}>
                            {lang}
                        </option>
                    ))}
                </select>
            )}

            {/* Alignment */}
            {ALIGNMENTS.map((align) => (
                <button
                    type="button"
                    key={align.value}
                    className={getBtnClass(editor.isActive({ textAlign: align.value }))}
                    title={align.value}
                    onClick={() => editor.chain().focus().setTextAlign(align.value).run()}
                >
                    {align.icon}
                </button>
            ))}

            {/* Link & Image */}
            <button type="button" title="Lien" className={getBtnClass(editor.isActive('link'))} onClick={addLink}>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="size-6">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
                </svg>
            </button>
            <button type="button" title="Importer une image" className={getBtnClass(false)} onClick={addImage}>
                <svg className="w-5 h-5" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
                    <path d="M3 5a2 2 0 012-2h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5z" />
                    <circle cx="8" cy="8" r="2" />
                    <path d="M21 15l-5-5L5 21" />
                </svg>
                <input id="imageUploader" type="file" accept="image/*" style={{ display: 'none' }} onChange={uploadImage} />
            </button>

            {/* Undo/Redo */}
            <button type="button" title="Annuler" className={getBtnClass(false)} onClick={() => editor.chain().focus().undo().run()}>
                <svg className="w-5 h-5" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
                    <path d="M9 14l-4-4 4-4M5 10h11a4 4 0 110 8h-1" />
                </svg>
            </button>
            <button type="button" title="Rétablir" className={getBtnClass(false)} onClick={() => editor.chain().focus().redo().run()}>
                <svg className="w-5 h-5" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
                    <path d="M15 14l4-4-4-4M19 10H8a4 4 0 100 8h1" />
                </svg>
            </button>
        </div>
    );
}
