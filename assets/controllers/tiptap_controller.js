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
import { Table } from '@tiptap/extension-table';
import { TableRow } from '@tiptap/extension-table-row';
import { TableCell } from '@tiptap/extension-table-cell';
import { TableHeader } from '@tiptap/extension-table-header';
import { TextStyle } from '@tiptap/extension-text-style';
import { Color } from '@tiptap/extension-color';


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
                TextStyle,
                Color,
                Table.configure({ resizable: true }),
                TableRow,
                TableHeader,
                TableCell,
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

    createButtonFromData(data) {
        const item = data.item;
        const icon = this.createIconEl(item.icon);
        return this.createButton(data.action, icon, item.active, data.title, item.value);
    }
   
    createButton( action, content, isActive, title=null, value=null) {
        const btnBase = 'px-1 py-1 rounded-md text-xs font-medium transition disabled:text-neutral-200';
        const btnActive = 'bg-blue-500 text-white border border-blue-600';
        const btnInactive = 'bg-white text-neutral-500 hover:bg-neutral-200';
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = [btnBase, isActive ? btnActive : btnInactive].join(' ');
        if (title) btn.setAttribute('title', title);
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

    createSeparator() {
        const separator = document.createElement('div');
        separator.className = 'mx-1 h-6 w-px bg-neutral-200 self-center';
        return separator;
    }

    createHR() {
        const hr = document.createElement('div');
        hr.className = 'my-1 border-t border-gray-200';
        return hr;
    }

    createMenuItem(item, action, menu) {
        const menuItem = document.createElement('button');
        menuItem.type = 'button';
        menuItem.className = `w-full text-left px-3 py-1 text-xs transition-colors disabled:text-neutral-200 ${
            item.isActive ? 'bg-blue-500 text-white' : 'text-gray-700 hover:bg-blue-50'
        }`;
        if (item.icon) {
            const icon = ICONS[item.icon];           
            menuItem.innerHTML = icon;
        }
        if (item.label) {
            menuItem.innerText = item.label;
        }
        if (!item.enabled) {
            menuItem.setAttribute('disabled', true);
        }
        menuItem.addEventListener('click', () => {
            this[action](item.value);
            menu.classList.add('hidden');
        });

        return menuItem;
    }

    createDropdown(data) {
        const action = data.action;
        const container = document.createElement('div');
        container.className = 'relative inline-block';
        const btnContent = document.createElement('div');
        btnContent.classList.add('flex', 'gap-1', 'items-center');
        btnContent.append(this.createIconEl(data.icon));
        btnContent.append(this.createIconEl('chevron_down'));

        const btn = this.createButton(null, btnContent, data.isActive, data.title, true);
        if (!data.enabled) {
            btn.setAttribute('disabled', true);
        }
        const menu = document.createElement('div');
        menu.className = 'hidden absolute z-25 mt-1 min-w-12 bg-white border border-gray-200 rounded-md shadow-lg px-1';
        data.items.forEach(item => {
            if (item.type === 'separator') {
                menu.appendChild(this.createHR());
                return;
            }
            menu.appendChild(this.createMenuItem(item, data.action, menu));
        });

        btn.addEventListener('click', () => menu.classList.toggle('hidden'));
        
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

    renderToolbar() {
        const isInTable = this.editor.isActive('table');
        console.log("isInTable", isInTable)
        const actions = [
            { type: 'button', title: 'Bold', action: 'toggleBold', item: {icon: 'bold', isActive: this.editor.isActive('bold') } },
            { type: 'button', title: 'Italique', action: 'toggleItalic', item: {icon: 'italic', isActive: this.editor.isActive('italic') }},
            { type: 'button', title: 'Barré', action: 'toggleStrike', item: {icon: 'strike', isActive: this.editor.isActive('strike') }},
            { type: 'button', title: 'Sousligner', action: 'toggleUnderline', item: {icon: 'underline', isActive: this.editor.isActive('underline') }},
            { type: 'separator' },
            { type: 'menu', title: 'Couleur de texte', action: 'setTextColor', icon: 'color', isActive: this.editor.isActive('textStyle'), enabled: true, items: [
                { type: 'item', icon: 'square_red', value: '#fb2c36', isActive: this.editor.getAttributes('textStyle').color === '#fb2c36', enabled: true },
                { type: 'item', icon: 'square_orange', value: '#ff6900', isActive: this.editor.getAttributes('textStyle').color === '#ff6900', enabled: true },
                { type: 'item', icon: 'square_yellow', value: '#f0b100', isActive: this.editor.getAttributes('textStyle').color === '#f0b100', enabled: true },
                { type: 'item', icon: 'square_green', value: '#00c950', isActive: this.editor.getAttributes('textStyle').color === '#00c950', enabled: true },
                { type: 'item', icon: 'square_cyan', value: '#00b8db', isActive: this.editor.getAttributes('textStyle').color === '#00b8db', enabled: true },
                { type: 'item', icon: 'square_blue', value: '#2b7fff', isActive: this.editor.getAttributes('textStyle').color === '#2b7fff', enabled: true },
                { type: 'item', icon: 'square_violet', value: '#8e51ff', isActive: this.editor.getAttributes('textStyle').color === '#8e51ff', enabled: true },
                { type: 'item', icon: 'square_fuchsia', value: '#e12afb', isActive: this.editor.getAttributes('textStyle').color === '#e12afb', enabled: true },
                { type: 'item', icon: 'square_pink', value: '#f6339a', isActive: this.editor.getAttributes('textStyle').color === '#f6339a', enabled: true },
                { type: 'item', icon: 'eraser', value: '', isActive: false, enabled: true }
            ]},
            { type: 'menu', title: 'Surlignage', action: 'toggleHighlight', icon: 'highlighter', isActive: this.editor.isActive('highlight'), enabled: true, items: [
                { type: 'item', icon: 'square_yellow', value: '#f0b100', isActive: this.editor.getAttributes('highlight').color === '#f0b100', enabled: true },
                { type: 'item', icon: 'square_green', value: '#00c950', isActive: this.editor.getAttributes('highlight').color === '#00c950', enabled: true },
                { type: 'item', icon: 'eraser', value: null, isActive: false }
            ]},
            { type: 'separator' }, 
            { type: 'menu', title: 'Titre', action: 'toggleHeader', icon: 'heading', isActive: this.editor.isActive('heading'), enabled: true, items: [
                { type: 'item', icon: 'h1',  isActive: this.editor.isActive('heading', { level: 1 }), value: 1, enabled: true },
                { type: 'item', icon: 'h2', isActive: this.editor.isActive('heading', { level: 2 }), value: 2, enabled: true },
                { type: 'item', icon: 'h3', isActive: this.editor.isActive('heading', { level: 3 }), value: 3, enabled: true },
                { type: 'item', icon: 'eraser',  isActive: !this.editor.isActive('heading'), value: 0, enabled: true },
            ]},
            { type: 'menu', title: 'Allignement', action: 'setTextAlign', icon: 'justify', isActive: this.editor.getAttributes('heading').textAlign, enabled: true, items: [
                { type: 'item', icon: 'left', isActive: this.editor.isActive({ textAlign: 'left' }), value: 'left', enabled: true },
                { type: 'item', icon: 'center', isActive: this.editor.isActive({ textAlign: 'center' }), value: 'center', enabled: true },
                { type: 'item', icon: 'right', isActive: this.editor.isActive({ textAlign: 'right' }), value: 'right', enabled: true},
                { type: 'item', icon: 'justify', isActive: this.editor.isActive({ textAlign: 'justify' }), value: 'justify', enabled: true },
            ]},
            { type: 'button', title: 'Liste à puce', action: 'toggleBulletList', item: { icon: 'bullet_list', isActive: this.editor.isActive('bulletList')}},
            { type: 'button', title: 'Liste ordonnée', action: 'toggleOrderedList', item: { icon: 'ordered_list', isActive: this.editor.isActive('orderedList') }},
            { type: 'separator' }, 
            { type: 'button', title: 'Inserer un lien', action: 'addLink', item: { icon: 'link', isActive: this.editor.isActive('link') }},
            { type: 'button', title: 'Citation', action: 'toggleBlockquote', item: { icon: 'blockquote', isActive: this.editor.isActive('blockquote') }},
            { type: 'menu', title: 'Tableau', action: 'manageTable', icon: 'table', isActive: this.editor.isActive('table'), enabled: true, items: [
                { type: 'item', label: 'Insérer Tableau', value: 'insert', isActive: false, enabled: true},
                { type: 'separator'},
                { type: 'item', label: 'Ajouter Colonne', value: 'addColumn', isActive: false, enabled: isInTable },
                { type: 'item', label: 'Supprimer Colonne', value: 'deleteColumn', isActive: false, enabled: isInTable },
                { type: 'item', label: 'Ajouter Ligne', value: 'addRow', isActive: false, enabled: isInTable },
                { type: 'item', label: 'Supprimer Ligne', value: 'deleteRow', isActive: false, enabled: isInTable },
                { type: 'item', label: 'Supprimer Tableau', value: 'delete', isActive: false, enabled: true },
            ]},
            { type: 'button', title: 'Insérer une image', action: 'addImage', item: { icon: 'image', isActive: false }},
            { type: 'menu', title: 'Redimentionner une image',action: 'setImageSize', icon: 'image_upscale', isActive: ['25%', '50%', '75%'].includes(this.editor.getAttributes('image').width), enabled: this.editor.isActive('image'), items: [
                { type: 'item', label: 'Petit (25%)', isActive: this.editor.getAttributes('image').width ==='25%', value: '25%', enabled: true },
                { type: 'item', label: 'Moyen (50%)', isActive: this.editor.getAttributes('image').width ==='50%' ,value: '50%', enabled: true },
                { type: 'item', label: 'Large (75%)', isActive: this.editor.getAttributes('image').width ==='75%', value: '75%', enabled: true },
                { type: 'item', label: 'Full (100%)', isActive: this.editor.getAttributes('image').width ==='100%', value: '100%', enabled: true },
            ]},
            { type: 'button', title: 'Insérer une vidéo Youtube', action: 'addYoutubeVideo', item: { icon: 'youtube', isActive: this.editor.isActive('youtube') }},
            { type: 'separator' }, 
            { type: 'button', title: 'Annuler', action: 'undo', item: { icon: 'undo', isActive: false }},
            { type: 'button', title: 'Refaire', action: 'redo', item: { icon: 'redo', action: 'redo', isActive: false }},
        ];

        this.toolbarTarget.replaceChildren();
        actions.forEach(data => {
            switch(data.type) {
                case 'separator':
                    this.toolbarTarget.appendChild(this.createSeparator());
                    break;
                case 'menu':
                   this.toolbarTarget.appendChild(this.createDropdown(data));
                    break;
                case 'button':
                    this.toolbarTarget.appendChild(this.createButtonFromData(data));
                    break;
            }
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
                width: '100%', 
            });
        }
    }
    setTextColor(color) {
        if (!color) {
            this.editor.chain().focus().unsetColor().run();
        } else {
            this.editor.chain().focus().setColor(color).run();
        }
        this.renderToolbar();
    }

    manageTable(type) {
        if (!type) return;
        
        const chain = this.editor.chain().focus();
        
        switch (type) {
            case 'insert':
                chain.insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run();
                break;
            case 'addColumn':
                chain.addColumnAfter().run();
                break;
            case 'deleteColumn':
                chain.deleteColumn().run();
                break;
            case 'addRow':
                chain.addRowAfter().run();
                break;
            case 'deleteRow':
                chain.deleteRow().run();
                break;
            case 'delete':
                chain.deleteTable().run();
                break;
        }
        this.renderToolbar();
    }

    disconnect() {
        this.editor.destroy();
    }
}