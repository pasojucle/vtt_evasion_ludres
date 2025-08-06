import React from 'react'
import { useEditor, EditorContent } from '@tiptap/react'
import StarterKit from '@tiptap/starter-kit'
import Placeholder from '@tiptap/extension-placeholder'
import Link from '@tiptap/extension-link'
import Image from '@tiptap/extension-image'

import CodeBlockLowlight from '@tiptap/extension-code-block-lowlight'
import { createLowlight } from 'lowlight'
import javascript from 'highlight.js/lib/languages/javascript'
import xml from 'highlight.js/lib/languages/xml'
import css from 'highlight.js/lib/languages/css'

const lowlight = createLowlight()
lowlight.register({ javascript, xml, css })
import TextAlign from '@tiptap/extension-text-align';
import TiptapToolbar from '@/components/TiptapToolbar'

type TiptapEditorProps = {
  id?: string
  label?: string
  name?: string
  content?: string
  handleChange: (value: string) => void
  upload_url?: string
  environment?: string
  className?: string
}
function TiptapEditor({id, label, name, content, handleChange, upload_url, environment, className = ''}: TiptapEditorProps): React.JSX.Element|null {

  const editor = useEditor({
    extensions: [
      StarterKit.configure({
        codeBlock: false,
      }),
      Placeholder.configure({ placeholder: 'Écris ici...' }),
      Link.configure({ openOnClick: false }),
      Image,
      CodeBlockLowlight.configure({lowlight}),
      TextAlign.configure({ types: ['heading', 'paragraph'] }),
    ],
    content: content || '',
    editorProps: {
      attributes: {
        class: 'prose prose-sm sm:prose-base lg:prose-lg xl:prose-2xl focus:outline-none',
      },
    },
    onUpdate: ({ editor }) => {
        handleChange(editor.getHTML());
    },
  })

   if (!editor) return null

  return (
    <div>
      <TiptapToolbar editor={editor} />
      <EditorContent editor={editor} className='p-2 border border-gray-900 dark:border-gray-200 rounded-b-md bg-gray-100 dark:bg-gray-800 min-h-40'/>
    </div>
  )
}

export default TiptapEditor