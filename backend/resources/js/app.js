import { Alpine, Livewire } from '../../vendor/livewire/livewire/dist/livewire.esm';
import tinymce from 'tinymce/tinymce';
import 'tinymce/icons/default';
import 'tinymce/themes/silver';
import 'tinymce/models/dom';
import 'tinymce/skins/ui/oxide/skin.js';
import 'tinymce/skins/ui/oxide/content.js';
import 'tinymce/skins/content/default/content.js';
import 'tinymce/plugins/autolink';
import 'tinymce/plugins/code';
import 'tinymce/plugins/image';
import 'tinymce/plugins/link';
import 'tinymce/plugins/lists';
import 'tinymce/plugins/table';
import {
    Alignment,
    Autoformat,
    AutoImage,
    BlockQuote,
    Bold,
    ClassicEditor,
    Code,
    CodeBlock,
    Essentials,
    FindAndReplace,
    FontBackgroundColor,
    FontColor,
    FontFamily,
    FontSize,
    Fullscreen,
    GeneralHtmlSupport,
    Heading,
    Highlight,
    HorizontalLine,
    Image,
    ImageCaption,
    ImageInsert,
    ImageResize,
    ImageStyle,
    ImageToolbar,
    ImageUpload,
    Indent,
    IndentBlock,
    Italic,
    Link,
    LinkImage,
    List,
    MediaEmbed,
    Paragraph,
    PasteFromOffice,
    RemoveFormat,
    SelectAll,
    SourceEditing,
    SpecialCharacters,
    SpecialCharactersEssentials,
    Strikethrough,
    Subscript,
    Superscript,
    Table,
    TableCellProperties,
    TableProperties,
    TableToolbar,
    TodoList,
    Underline,
} from 'ckeditor5';
import viTranslations from 'ckeditor5/translations/vi.js';
import 'ckeditor5/ckeditor5.css';
import { notify } from './toast';

window.Alpine = Alpine;

window.adminLayout = () => ({
    sidebarOpen: false,
    sidebarCollapsed: localStorage.getItem('idi-admin-sidebar-collapsed') === 'true',
    openSidebar() {
        this.sidebarOpen = true;
        document.body.style.overflow = 'hidden';
    },
    closeSidebar() {
        this.sidebarOpen = false;
        document.body.style.overflow = '';
    },
    toggleSidebar() {
        this.sidebarCollapsed = !this.sidebarCollapsed;
        localStorage.setItem('idi-admin-sidebar-collapsed', this.sidebarCollapsed);
    },
});

window.languageTabs = (initial = 'vi') => ({
    active: initial,
    select(locale) {
        this.active = locale;
        this.$nextTick(() => {
            window.initEditorForPanel?.(locale);
            document.getElementById(`panel-${locale}`)?.querySelector('input, textarea, select')?.focus();
        });
    },
});

const slugify = (value) => value
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/[đĐ]/g, 'd')
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '')
    .replace(/-{2,}/g, '-');

window.productSlug = (initialTitle = '', initialSlug = '', published = false) => ({
    title: initialTitle,
    slug: initialSlug,
    originalSlug: initialSlug,
    slugEdited: Boolean(initialSlug),
    published,
    get changed() {
        return this.slug !== this.originalSlug;
    },
    onTitle() {
        if (!this.slugEdited || !this.slug) this.slug = slugify(this.title);
    },
    markSlugEdited() {
        this.slugEdited = true;
        this.slug = slugify(this.slug);
    },
    regenerate() {
        this.slug = slugify(this.title);
        this.slugEdited = true;
    },
});

window.categorySlug = (initialName = '', initialSlug = '') => ({
    name: initialName || '',
    slug: initialSlug || '',
    slugEdited: Boolean(initialSlug),
    onName() {
        if (!this.slugEdited || !this.slug) this.slug = slugify(this.name);
    },
    markSlugEdited() {
        this.slugEdited = true;
        this.slug = slugify(this.slug);
    },
    regenerate() {
        this.slug = slugify(this.name);
        this.slugEdited = true;
    },
});

window.bulkCategories = () => ({
    selected: 0,
    get selectedLabel() {
        return this.selected > 0 ? `Đã chọn ${this.selected} danh mục` : 'Chưa chọn danh mục';
    },
    checkboxes() {
        return [...document.querySelectorAll('.category-row-checkbox')];
    },
    toggleAll(event) {
        this.checkboxes().forEach((checkbox) => {
            checkbox.checked = event.target.checked;
        });
        this.syncSelection();
    },
    syncSelection() {
        const checkboxes = this.checkboxes();
        this.selected = checkboxes.filter((checkbox) => checkbox.checked).length;
        if (this.$refs.selectAll) {
            this.$refs.selectAll.checked = this.selected === checkboxes.length && checkboxes.length > 0;
            this.$refs.selectAll.indeterminate = this.selected > 0 && this.selected < checkboxes.length;
        }
    },
    validateSelection(event) {
        if (this.selected > 0) return;
        event.preventDefault();
        notify('Vui lòng chọn ít nhất một danh mục.', 'warning');
    },
    confirmDelete(event) {
        if (this.selected === 0) return;
        if (!window.confirm(`Chuyển ${this.selected} danh mục đã chọn vào thùng rác?`)) {
            event.preventDefault();
        }
    },
});

window.mediaPicker = (initialPreview = null) => ({
    preview: initialPreview,
    objectUrl: null,
    removed: false,
    pick(event) {
        const [file] = event.target.files;
        if (!file) return;
        if (this.objectUrl) URL.revokeObjectURL(this.objectUrl);
        this.objectUrl = URL.createObjectURL(file);
        this.preview = this.objectUrl;
        this.removed = false;
    },
    remove() {
        if (this.objectUrl) URL.revokeObjectURL(this.objectUrl);
        this.objectUrl = null;
        this.preview = null;
        this.removed = true;
        this.$refs.input.value = '';
    },
});

window.confirmDialog = () => ({
    open: false,
    opener: null,
    openDialog(event) {
        this.opener = event.target.closest('button');
        this.open = true;
        this.$nextTick(() => this.$refs.dialog?.querySelector('button')?.focus());
    },
    close() {
        if (!this.open) return;
        this.open = false;
        this.$nextTick(() => this.opener?.focus());
    },
});

const uploadEditorImage = async (file) => {
    const body = new FormData();
    body.append('file', file);
    const response = await fetch('/admin/media/editor-image', {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body,
    });
    if (!response.ok) {
        const error = await response.json().catch(() => ({}));
        const validationMessage = Object.values(error.errors || {}).flat().find(Boolean);
        throw new Error(validationMessage || error.message || 'Không thể tải ảnh lên.');
    }
    const data = await response.json();
    if (data.toast?.message) notify(data.toast.message, data.toast.type);
    return data.url;
};

const ckeditorInstances = new Map();
const ckeditorPending = new Set();

function ckeditorUploadAdapter(editor) {
    editor.plugins.get('FileRepository').createUploadAdapter = (loader) => ({
        async upload() {
            const file = await loader.file;
            try {
                return { default: await uploadEditorImage(file) };
            } catch (error) {
                notify(error.message, 'error');
                throw error;
            }
        },
        abort() {},
    });
}

const ckeditorOptions = {
    licenseKey: 'GPL',
    plugins: [
        Essentials,
        Autoformat,
        AutoImage,
        Paragraph,
        Heading,
        Bold,
        Italic,
        Underline,
        Strikethrough,
        Subscript,
        Superscript,
        Code,
        Link,
        List,
        TodoList,
        BlockQuote,
        CodeBlock,
        Alignment,
        Indent,
        IndentBlock,
        FontFamily,
        FontSize,
        FontColor,
        FontBackgroundColor,
        Highlight,
        HorizontalLine,
        FindAndReplace,
        SelectAll,
        SpecialCharacters,
        SpecialCharactersEssentials,
        Image,
        ImageCaption,
        ImageInsert,
        ImageResize,
        ImageStyle,
        ImageToolbar,
        ImageUpload,
        LinkImage,
        Table,
        TableToolbar,
        TableProperties,
        TableCellProperties,
        MediaEmbed,
        GeneralHtmlSupport,
        SourceEditing,
        Fullscreen,
        PasteFromOffice,
        RemoveFormat,
    ],
    extraPlugins: [ckeditorUploadAdapter],
    toolbar: {
        items: [
            'undo', 'redo', '|',
            'findAndReplace', 'selectAll', '|',
            'heading', 'fontFamily', 'fontSize', '|',
            'bold', 'italic', 'underline', 'strikethrough',
            'subscript', 'superscript', 'code', 'removeFormat', '|',
            'fontColor', 'fontBackgroundColor', 'highlight', '|',
            'alignment', '|',
            'bulletedList', 'numberedList', 'todoList',
            'outdent', 'indent', '|',
            'link', 'insertImage', 'insertTable', 'mediaEmbed', '|',
            'blockQuote', 'codeBlock', 'horizontalLine', 'specialCharacters', '|',
            'sourceEditing', 'fullscreen',
        ],
        shouldNotGroupWhenFull: true,
    },
    language: 'vi',
    translations: [viTranslations],
    link: {
        addTargetToExternalLinks: true,
        defaultProtocol: 'https://',
    },
    fontFamily: {
        options: [
            'default',
            'Arial, Helvetica, sans-serif',
            'Georgia, serif',
            'Tahoma, Geneva, sans-serif',
            'Times New Roman, Times, serif',
            'Verdana, Geneva, sans-serif',
        ],
        supportAllValues: true,
    },
    fontSize: {
        options: [9, 11, 13, 'default', 16, 18, 20, 24, 28, 32, 36],
        supportAllValues: true,
    },
    image: {
        toolbar: [
            'toggleImageCaption', 'imageTextAlternative', '|',
            'imageStyle:inline', 'imageStyle:wrapText', 'imageStyle:breakText', '|',
            'resizeImage', 'linkImage',
        ],
        resizeOptions: [
            { name: 'resizeImage:original', value: null, label: 'Kích thước gốc' },
            { name: 'resizeImage:75', value: '75', label: '75%' },
            { name: 'resizeImage:50', value: '50', label: '50%' },
            { name: 'resizeImage:25', value: '25', label: '25%' },
        ],
    },
    table: {
        contentToolbar: [
            'tableColumn', 'tableRow', 'mergeTableCells', '|',
            'tableProperties', 'tableCellProperties',
        ],
    },
    htmlSupport: {
        allow: [{ name: /.*/, attributes: true, classes: true, styles: true }],
    },
};

const initCkeditor = async (textarea) => {
    if (!textarea?.id || ckeditorInstances.has(textarea.id) || ckeditorPending.has(textarea.id)) return;
    ckeditorPending.add(textarea.id);

    try {
        const editor = await ClassicEditor.create({
            ...ckeditorOptions,
            attachTo: textarea,
            root: {
                placeholder: textarea.dataset.placeholder || 'Nhập nội dung...',
            },
        });
        ckeditorInstances.set(textarea.id, editor);
        editor.model.document.on('change:data', () => {
            textarea.value = editor.getData();
            textarea.dispatchEvent(new Event('input', { bubbles: true }));
        });
    } catch (error) {
        console.error('Không thể khởi tạo CKEditor 5.', error);
    } finally {
        ckeditorPending.delete(textarea.id);
    }
};

const destroyCkeditors = () => {
    const pendingDestroy = [...ckeditorInstances.values()].map((editor) => editor.destroy());
    ckeditorInstances.clear();
    return Promise.allSettled(pendingDestroy);
};

const editorOptions = {
    license_key: 'gpl',
    height: 340,
    menubar: false,
    branding: false,
    promotion: false,
    skin_url: 'default',
    content_css: 'default',
    plugins: 'autolink code image link lists table',
    toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist | link image table | code',
    toolbar_mode: 'wrap',
    content_style: 'body { font-family: Inter, Arial, sans-serif; font-size: 14px; line-height: 1.6; } img { max-width: 100%; height: auto; }',
    automatic_uploads: true,
    images_file_types: 'jpg,jpeg,png,webp,gif',
    images_upload_handler: (blobInfo) => uploadEditorImage(blobInfo.blob()),
    setup(editor) {
        editor.on('change input undo redo', () => {
            editor.save();
            editor.getElement().dispatchEvent(new Event('input', { bubbles: true }));
        });
    },
};

window.initEditorForPanel = (locale) => {
    const panel = document.querySelector(`#panel-${locale}`);
    if (!panel) return;

    panel.querySelectorAll('.ckeditor5-textarea').forEach(initCkeditor);
    const textarea = panel.querySelector('.rich-text-textarea');
    if (textarea && !tinymce.get(textarea.id)) tinymce.init({ ...editorOptions, target: textarea });
};

const initializeAdminPage = () => {
    document.querySelectorAll('.sidebar-nav a[href]').forEach((link) => {
        if (link.getAttribute('href') === '#') {
            link.classList.remove('is-active');
            return;
        }

        const target = new URL(link.href, window.location.origin);
        const active = target.pathname === window.location.pathname;
        link.classList.toggle('is-active', active);
    });

    const initialLocale = document.querySelector('.language-tabs')?.dataset.initial;
    if (initialLocale) window.initEditorForPanel(initialLocale);

    document.querySelectorAll('[data-dirty-form]').forEach((form) => {
        let dirty = false;
        form.addEventListener('input', () => { dirty = true; });
        form.addEventListener('submit', () => {
            tinymce.triggerSave();
            dirty = false;
        });
        window.addEventListener('beforeunload', (event) => {
            if (!dirty) return;
            event.preventDefault();
        });
    });
};

document.addEventListener('livewire:navigating', () => {
    tinymce.remove();
    destroyCkeditors();
});
document.addEventListener('DOMContentLoaded', initializeAdminPage);
document.addEventListener('livewire:navigated', initializeAdminPage);

Livewire.start();
