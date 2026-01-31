import { Controller } from '@hotwired/stimulus';
import { ICONS } from '../js/tiptap_icons.js';
import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Link from '@tiptap/extension-link';
import Image from '@tiptap/extension-image';
import TextAlign from '@tiptap/extension-text-align';
import CodeBlockLowlight from '@tiptap/extension-code-block-lowlight';
import Youtube from '@tiptap/extension-youtube';
import Highlight from '@tiptap/extension-highlight';
import { common, createLowlight } from 'lowlight';


export default class extends Controller {
    static targets = ['editor', 'toolbar', 'input', 'imageUploader'];
    static values = {
        content: String,
        token: String,
        uploadUrl: String
    };

    connect() {
        const lowlight = createLowlight(common);

        const CustomImage = Image.configure({
            inline: true,
            allowBase64: true,
        }).extend({
            addAttributes() {
                return {
                    ...this.parent?.(),
                    width: {
                        default: '100%',
                        parseHTML: element => element.style.width || element.getAttribute('width') || '100%',
                        renderHTML: attributes => {
                            return {
                                style: `width: ${attributes.width}; height: auto; display: block;`,
                                width: attributes.width
                            };
                        },
                    },
                };
            },
        });

        const CustomYoutube = Youtube.configure({
            controls: true,
            nocookie: true,
            allowFullscreen: true,
            HTMLAttributes: {
                class: 'youtube',
            },
        });

        this.editor = new Editor({
            element: this.editorTarget,
            extensions: [
                StarterKit.configure({
                codeBlock: false,
                link: false,
                
            }),
            Link.configure({ openOnClick: false }),
                TextAlign.configure({ types: ['heading', 'paragraph'] }),
                CustomImage,
                CustomYoutube,
                CodeBlockLowlight.configure({ lowlight }),
                Highlight.configure({ multicolor: true }),
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

    createButtonFromItem(action, item) {
        const icon = this.createIconEl(item.icon);
        return this.createButton(action, icon, item.active, item.value);
    }
   
    createButton( action, content, isActive, value=null) {
        const btnBase = 'px-1 py-1 rounded-md border text-xs font-medium transition disabled:bg-neutral-200';
        const btnActive = 'bg-blue-500 text-white border-blue-600';
        const btnInactive = 'bg-white text-gray-700 border-gray-300';
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = [btnBase, isActive ? btnActive : btnInactive].join(' ');;

        btn.append(content);
        if(action) {
            if (value) {
                btn.addEventListener('click', () => this[action](value));
                return btn;
            }
            btn.addEventListener('click', () => this[action]());
        }
        
        return btn;
    }

    createIconEl(name) {
        const icon = ICONS[name];
        const iconEl = document.createElement('div');
        iconEl.innerHTML = icon;
        return iconEl;
    }

    createDropdown(data) {
        const action = data.action;
        const container = document.createElement('div');
        container.className = 'relative inline-block';
        const btnContent = document.createElement('div');
        btnContent.classList.add('flex', 'gap-2', 'items-center');
        btnContent.append(this.createIconEl(data.icon));
        btnContent.append(this.createIconEl('chevron_down'));

        const btn = this.createButton(null, btnContent, data.isActive, true);
        if (!data.enabled) {
            btn.setAttribute('disabled', true);
        }
        const menu = document.createElement('div');
        menu.className = 'hidden absolute z-25 mt-1 min-w-12 bg-white border border-gray-200 rounded-md shadow-lg px-1';
        data.items.forEach(item => {
            const itemEl = document.createElement('button');
            itemEl.type = 'button';
            itemEl.className = `w-full text-left px-3 py-1 text-xs transition-colors ${
                item.isActive ? 'bg-blue-500 text-white' : 'text-gray-700 hover:bg-blue-50'
            }`;
            if (item.icon) {
                const icon = ICONS[item.icon];           
                itemEl.innerHTML = icon;
            }
            if (item.label) {
                itemEl.innerText = item.label;
            }
            itemEl.addEventListener('click', () => {
            this[action](item.value);
                menu.classList.add('hidden');
            });
            menu.appendChild(itemEl);
        });

        btn.addEventListener('click', () => menu.classList.toggle('hidden'));
        
        // Fermer le menu si on clique ailleurs
        document.addEventListener('click', (e) => {
            if (!container.contains(e.target)) menu.classList.add('hidden');
        });

        container.appendChild(btn);
        container.appendChild(menu);
        return container;
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
        console.log("iamge width", this.editor.getAttributes('image').width);
        // Configuration des boutons #feff66
        const actions = [
            { action: 'toggleBold', item: {icon: 'bold', isActive: this.editor.isActive('bold') } },
            { action: 'toggleItalic', item: {icon: 'italic', isActive: this.editor.isActive('italic') }},
            { action: 'toggleStrike', item: {icon: 'strike', isActive: this.editor.isActive('strike') }},
            { action: 'toggleUnderline', item: {icon: 'underline', isActive: this.editor.isActive('underline') }},
            { action: 'toggleHighlight', icon: 'highlighter', isActive: this.editor.isActive('highlight'), enabled: true, items: [
                { icon: 'square_yellow', value: '#feff66', isActive: this.editor.getAttributes('highlight').color === '#feff66' },
                { icon: 'square_green', value: '#bbf7d0', isActive: this.editor.getAttributes('highlight').color === '#bbf7d0' },
                { icon: 'eraser', value: null, isActive: false }
            ]},            
            { action: 'toggleHeader', icon: 'heading', isActive: this.editor.isActive('heading'), enabled: true, items: [
                { icon: 'text',  isActive: !this.editor.isActive('heading'), value: 0 },
                { icon: 'h1',  isActive: this.editor.isActive('heading', { level: 1 }), value: 1 },
                { icon: 'h2', isActive: this.editor.isActive('heading', { level: 2 }), value: 2 },
                { icon: 'h3', isActive: this.editor.isActive('heading', { level: 3 }), value: 3 },
            ]},
            { action: 'setTextAlign', icon: 'justify', isActive: this.editor.getAttributes('heading').textAlign, enabled: true, items: [
                { icon: 'left', isActive: this.editor.isActive({ textAlign: 'left' }), value: 'left' },
                { icon: 'center', isActive: this.editor.isActive({ textAlign: 'center' }), value: 'center' },
                { icon: 'right', isActive: this.editor.isActive({ textAlign: 'right' }), value: 'right'},
                { icon: 'justify', isActive: this.editor.isActive({ textAlign: 'justify' }), value: 'justify' },
            ]},
            { action: 'toggleBulletList', item: { icon: 'bullet_list', isActive: this.editor.isActive('bulletList')}},
            { action: 'toggleOrderedList', item: { icon: 'ordered_list', isActive: this.editor.isActive('orderedList') }},
            { action: 'addLink', item: { icon: 'link', isActive: this.editor.isActive('link') }},
            { action: 'toggleBlockquote', item: { icon: 'blockquote', isActive: this.editor.isActive('blockquote') }},
            { action: 'addImage', item: { icon: 'image', isActive: false }},
            { action: 'setImageSize', icon: 'image_upscale', isActive: ['25%', '50%', '75%'].includes(this.editor.getAttributes('image').width), enabled: this.editor.isActive('image'), items: [
                { label: 'Petit (25%)', isActive: this.editor.getAttributes('image').width ==='25%', value: '25%' },
                { label: 'Moyen (50%)', isActive: this.editor.getAttributes('image').width ==='50%' ,value: '50%' },
                { label: 'Large (75%)', isActive: this.editor.getAttributes('image').width ==='75%', value: '75%' },
                { label: 'Full (100%)', isActive: this.editor.getAttributes('image').width ==='100%', value: '100%' },
            ]},
            { action: 'addYoutubeVideo', item: { icon: 'youtube', isActive: this.editor.isActive('youtube') }},
            { action: 'undo', item: { icon: 'undo', isActive: false }},
            { action: 'redo', item: { icon: 'redo', action: 'redo', isActive: false }},
        ];

        this.toolbarTarget.replaceChildren();
        actions.forEach(data => {
            const component = (data.items) 
                ? this.createDropdown(data)
                : this.createButtonFromItem(data.action, data.item);
            this.toolbarTarget.appendChild(component);
            if ("addImage" === data.action) {
                this.toolbarTarget.appendChild(this.createImageUploader());
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
    toggleStrike() {
        this.editor.chain().focus().toggleStrike().run();
        this.renderToolbar();
    }

    toggleUnderline() {
        this.editor.chain().focus().toggleUnderline().run();
        this.renderToolbar();
    }

    toggleHighlight(color) {
        if (!color) {
            this.editor.chain().focus().unsetHighlight().run();
        } else {
            this.editor.chain().focus().toggleHighlight({ color: color }).run();
        }
        this.renderToolbar();
    }

    addLink() {
        const url = window.prompt('URL:', this.editor.getAttributes('link').href);
        if (url) this.editor.chain().focus().setLink({ href: url }).run();
        this.renderToolbar();
    }

    toggleHeader(level) {
        console.log("level", level)
        if (!level || level === 0) {
            this.editor.chain().focus().setParagraph().run();
        } else {
            this.editor.chain().focus().toggleHeading({ level: level }).run();
        }
        this.renderToolbar();
    }

    setTextAlign(align) {
        this.editor.chain().focus().setTextAlign(align).run();
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

        const formData = new FormData()
            formData.append('upload', file)

        try {
            const response = await fetch(this.uploadUrlValue, {
                method: 'POST',
                body: formData,
            })
            const result = await response.json();
            if (result['url']) {
                const url =  decodeURIComponent(result['url']);
                this.editor.chain().focus().setImage({ src: url }).run();
            } else {
                alert('Erreur : URL manquante')
            }
        } catch (err) {
            console.error(err)
            alert('Échec de l’envoi')
        }
    }

    setImageSize(size) {
        console.log("Size", size)
        this.editor.chain().focus().updateAttributes('image', { width: size }).run();
    }

    addYoutubeVideo() {
        const url = window.prompt('Entrez l\'URL de la vidéo YouTube :');

        if (url) {
            this.editor.commands.setYoutubeVideo({
                src: url,
                width: '100%', // Tu peux aussi forcer une largeur par défaut
            });
        }
    }

    disconnect() {
        this.editor.destroy();
    }
}