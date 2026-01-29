import { Controller } from '@hotwired/stimulus';
import { ICONS } from '../js/tiptap_icons.js';
import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Link from '@tiptap/extension-link';
import Image from '@tiptap/extension-image';
import TextAlign from '@tiptap/extension-text-align';
import CodeBlockLowlight from '@tiptap/extension-code-block-lowlight';
import { common, createLowlight } from 'lowlight';

// Si tu as toujours ton helper d'upload
import { imageUpload } from '../js/imageUploadTiptap.js';

export default class extends Controller {
    static targets = ['editor', 'toolbar', 'input', 'imageUploader'];
    static values = {
        content: String,
        token: String,
        uploadUrl: String
    };

    connect() {
        const lowlight = createLowlight(common);

        this.editor = new Editor({
            element: this.editorTarget,
            extensions: [
                StarterKit.configure({
                codeBlock: false,
                link: false,
            }),
            Link.configure({ openOnClick: false }),
                TextAlign.configure({ types: ['heading', 'paragraph'] }),
                Image,
                CodeBlockLowlight.configure({ lowlight }),
            ],
            content: this.contentValue,
            onUpdate: ({ editor }) => {
                this.inputTarget.value = editor.getHTML();
            },
            onSelectionUpdate: () => {
                this.renderToolbar();
            },
        });

        this.renderToolbar();
    }

    createButton({name, action, active, value}) {
        const btnBase = 'px-1 py-1 rounded-md border text-xs font-medium transition';
        const btnActive = 'bg-blue-500 text-white border-blue-600';
        const btnInactive = 'bg-white text-gray-700 border-gray-300';
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = [btnBase, active ? btnActive : btnInactive].join(' ');;

        btn.innerHTML = ICONS[name];
        if (value) {
            btn.addEventListener('click', () => this[action](value));
            return btn;
        }
        btn.addEventListener('click', () => this[action]());
        return btn;
    }

    createImageUploader() {
        const inputFile = document.createElement('input');
        inputFile.type = 'file';
        inputFile.className = 'hidden';
        inputFile.dataset.tiptapTarget="imageUploader";
        inputFile.dataset.action = "change->tiptap#uploadImage";
        return inputFile;
    }

    // Cette fonction remplace le composant TiptapToolbar
    renderToolbar() {

        // Configuration des boutons
        const buttons = [
            { name: 'bold', action: 'toggleBold', active: this.editor.isActive('bold') },
            { name: 'italic', action: 'toggleItalic', active: this.editor.isActive('italic') },
            { name: 'strike', action: 'toggleStrike', active: this.editor.isActive('strike') },
            { name: 'h1', action: 'toggleHeader', active: this.editor.isActive('heading', { level: 1 }), value: 1 },
            { name: 'h2', action: 'toggleHeader', active: this.editor.isActive('heading', { level: 2 }), value: 2 },
            { name: 'h3', action: 'toggleHeader', active: this.editor.isActive('heading', { level: 3 }), value: 3 },
            { name: 'left', action: 'setTextAlign', active: this.editor.isActive({ textAlign: 'left' }), value: 'left' },
            { name: 'center', action: 'setTextAlign', active: this.editor.isActive({ textAlign: 'center' }), value: 'center' },
            { name: 'right', action: 'setTextAlign', active: this.editor.isActive({ textAlign: 'right' }), value: 'right'},
            { name: 'justify', action: 'setTextAlign', active: this.editor.isActive({ textAlign: 'justify' }), value: 'justify' },
            { name: 'bullet_list', action: 'toggleBulletList', active: this.editor.isActive('bulletList') },
            { name: 'ordered_list', action: 'toggleOrderedList', active: this.editor.isActive('orderedList') },
            { name: 'link', action: 'addLink', active: this.editor.isActive('link') },
            { name: 'blockquote', action: 'toggleBlockquote', active: this.editor.isActive('blockquote') },
            { name: 'image', action: 'addImage', active: false },
            { name: 'undo', action: 'undo', active: false },
            { name: 'redo', action: 'redo', active: false },
        ];

        this.toolbarTarget.replaceChildren();
        buttons.forEach(data => {
            const button = this.createButton(data);
            this.toolbarTarget.appendChild(button);
            if ("addImage" === data.action) {
                const inputFile = this.createImageUploader();
                this.toolbarTarget.appendChild(inputFile)
            }
        });
        
    }

    // Actions
    toggleBold() {
        this.editor.chain().focus().toggleBold().run();
        this.renderToolbar();
    }

    toggleItalic() {
        this.editor.chain().focus().toggleItalic().run();
        this.renderToolbar();
    }

    addLink() {
        const url = window.prompt('URL:', this.editor.getAttributes('link').href);
        if (url) this.editor.chain().focus().setLink({ href: url }).run();
        this.renderToolbar();
    }

    toggleHeader(level) {
        console.log("level", level);
        this.editor.chain().focus().toggleHeading({level: level}).run();
        this.renderToolbar();
    }

    setTextAlign(align) {
        console.log("align", align);
        this.editor.chain().focus().setTextAlign(align).run();
        this.renderToolbar();
    }

    toggleItalic() {
        this.editor.chain().focus().toggleItalic().run();
        this.renderToolbar();
    }

    toggleStrike() {
        this.editor.chain().focus().toggleStrike().run();
        this.renderToolbar();
    }

    toggleBulletList() {
        this.editor.chain().focus().toggleBulletList().run();
        this.renderToolbar();
    }

    toggleOrderedList() {
        this.editor.chain().focus().toggleOrderedList().run();
        this.renderToolbar();
    }

    toggleBlockquote() {
        this.editor.chain().focus().toggleBlockquote().run();
        this.renderToolbar();
    }

    undo() {
        this.editor.chain().focus().undo().run();
        this.renderToolbar();
    }

    redo() {
        this.editor.chain().focus().redo().run();
        this.renderToolbar();
    }

    addImage() {
        if (this.hasImageUploaderTarget) {
            this.imageUploaderTarget.click();
        } else {
            console.error("L'élément imageUploader n'a pas été trouvé dans le toolbar");
        }
    }

    async uploadImage(event) {
        const file = event.target.files[0];
        if (!file) return;

        // Utilisation de ton helper existant
        const url = await imageUpload(file, this.tokenValue);
        this.editor.chain().focus().setImage({ src: url }).run();
    }

    disconnect() {
        this.editor.destroy();
    }
}