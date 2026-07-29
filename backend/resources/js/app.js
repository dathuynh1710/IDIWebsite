import Alpine from 'alpinejs';
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
    if (!response.ok) throw new Error('Không thể tải ảnh lên.');
    const data = await response.json();
    return data.url;
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
    const textarea = document.querySelector(`#panel-${locale} .rich-text-textarea`);
    if (!textarea || tinymce.get(textarea.id)) return;
    tinymce.init({ ...editorOptions, target: textarea });
};

document.addEventListener('DOMContentLoaded', () => {
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
});

Alpine.start();
