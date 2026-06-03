<script setup>
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import Link from '@tiptap/extension-link'
import Image from '@tiptap/extension-image'
import Placeholder from '@tiptap/extension-placeholder'
import { watch } from 'vue'

const props = defineProps({
    modelValue: { type: String, default: '' },
    placeholder: { type: String, default: 'Начните писать...' },
})

const emit = defineEmits(['update:modelValue'])

const editor = useEditor({
    content: props.modelValue,
    extensions: [
        StarterKit.configure({
            heading: { levels: [2, 3, 4] },
        }),
        Link.configure({ openOnClick: false }),
        Image,
        Placeholder.configure({ placeholder: props.placeholder }),
    ],
    onUpdate: ({ editor }) => {
        emit('update:modelValue', editor.getHTML())
    },
})

watch(() => props.modelValue, (val) => {
    if (editor.value && val !== editor.value.getHTML()) {
        editor.value.commands.setContent(val, false)
    }
})

function addImage() {
    const url = window.prompt('URL изображения:')
    if (url) editor.value.chain().focus().setImage({ src: url }).run()
}

function addLink() {
    const url = window.prompt('URL ссылки:')
    if (url) editor.value.chain().focus().setLink({ href: url }).run()
}
</script>

<template>
    <div class="border border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden">
        <div v-if="editor" class="flex flex-wrap gap-1 p-2 border-b border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800">
            <button type="button" @click="editor.chain().focus().toggleBold().run()"
                :class="{ 'bg-indigo-100 dark:bg-indigo-900': editor.isActive('bold') }"
                class="px-2 py-1 rounded text-sm font-bold hover:bg-gray-200 dark:hover:bg-gray-700 dark:text-gray-200">
                B
            </button>
            <button type="button" @click="editor.chain().focus().toggleItalic().run()"
                :class="{ 'bg-indigo-100 dark:bg-indigo-900': editor.isActive('italic') }"
                class="px-2 py-1 rounded text-sm italic hover:bg-gray-200 dark:hover:bg-gray-700 dark:text-gray-200">
                I
            </button>
            <span class="w-px h-6 bg-gray-300 dark:bg-gray-600 self-center mx-1"></span>
            <button type="button" @click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
                :class="{ 'bg-indigo-100 dark:bg-indigo-900': editor.isActive('heading', { level: 2 }) }"
                class="px-2 py-1 rounded text-sm hover:bg-gray-200 dark:hover:bg-gray-700 dark:text-gray-200">
                H2
            </button>
            <button type="button" @click="editor.chain().focus().toggleHeading({ level: 3 }).run()"
                :class="{ 'bg-indigo-100 dark:bg-indigo-900': editor.isActive('heading', { level: 3 }) }"
                class="px-2 py-1 rounded text-sm hover:bg-gray-200 dark:hover:bg-gray-700 dark:text-gray-200">
                H3
            </button>
            <span class="w-px h-6 bg-gray-300 dark:bg-gray-600 self-center mx-1"></span>
            <button type="button" @click="editor.chain().focus().toggleBulletList().run()"
                :class="{ 'bg-indigo-100 dark:bg-indigo-900': editor.isActive('bulletList') }"
                class="px-2 py-1 rounded text-sm hover:bg-gray-200 dark:hover:bg-gray-700 dark:text-gray-200">
                • Список
            </button>
            <button type="button" @click="editor.chain().focus().toggleOrderedList().run()"
                :class="{ 'bg-indigo-100 dark:bg-indigo-900': editor.isActive('orderedList') }"
                class="px-2 py-1 rounded text-sm hover:bg-gray-200 dark:hover:bg-gray-700 dark:text-gray-200">
                1. Список
            </button>
            <button type="button" @click="editor.chain().focus().toggleBlockquote().run()"
                :class="{ 'bg-indigo-100 dark:bg-indigo-900': editor.isActive('blockquote') }"
                class="px-2 py-1 rounded text-sm hover:bg-gray-200 dark:hover:bg-gray-700 dark:text-gray-200">
                Цитата
            </button>
            <span class="w-px h-6 bg-gray-300 dark:bg-gray-600 self-center mx-1"></span>
            <button type="button" @click="addLink"
                class="px-2 py-1 rounded text-sm hover:bg-gray-200 dark:hover:bg-gray-700 dark:text-gray-200">
                🔗 Ссылка
            </button>
            <button type="button" @click="addImage"
                class="px-2 py-1 rounded text-sm hover:bg-gray-200 dark:hover:bg-gray-700 dark:text-gray-200">
                🖼 Картинка
            </button>
            <span class="w-px h-6 bg-gray-300 dark:bg-gray-600 self-center mx-1"></span>
            <button type="button" @click="editor.chain().focus().toggleCodeBlock().run()"
                :class="{ 'bg-indigo-100 dark:bg-indigo-900': editor.isActive('codeBlock') }"
                class="px-2 py-1 rounded text-sm font-mono hover:bg-gray-200 dark:hover:bg-gray-700 dark:text-gray-200">
                Код
            </button>
        </div>
        <EditorContent :editor="editor" class="prose dark:prose-invert max-w-none p-4 min-h-[300px] focus:outline-none dark:bg-gray-900 dark:text-gray-100" />
    </div>
</template>

<style>
.ProseMirror {
    min-height: 300px;
    outline: none;
}
.ProseMirror p.is-editor-empty:first-child::before {
    content: attr(data-placeholder);
    float: left;
    color: #adb5bd;
    pointer-events: none;
    height: 0;
}
</style>
