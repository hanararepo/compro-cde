export default function careerApplications(rows = []) {
    return {
        rows,
        selected: [],
        pendingIds: [],
        detail: null,
        deleting: false,
        returnFocus: null,
        get selectableIds() { return this.rows.filter(row => row.canDelete).map(row => String(row.id)); },
        get allSelected() { return this.selectableIds.length > 0 && this.selectableIds.every(id => this.selected.includes(id)); },
        get someSelected() { return this.selected.length > 0 && !this.allSelected; },
        toggleAll() { this.selected = this.allSelected ? [] : [...this.selectableIds]; },
        openDetail(id) {
            this.detail = this.rows.find(row => String(row.id) === String(id));
            this.returnFocus = document.activeElement;
            this.$refs.detailDialog.showModal();
        },
        confirmDelete(ids) {
            this.pendingIds = [...new Set(ids.map(String))].filter(id => this.selectableIds.includes(id));
            if (!this.pendingIds.length) return;
            this.returnFocus = document.activeElement;
            this.deleting = false;
            this.$refs.deleteDialog.showModal();
            this.$nextTick(() => this.$refs.cancelDelete.focus());
        },
        closeDialog(dialog) {
            if (this.deleting) return;
            dialog.close();
            this.returnFocus?.focus();
        },
        cancelDialog(event) {
            if (this.deleting) event.preventDefault();
        },
        submitDeletion(event) {
            if (this.deleting || !this.pendingIds.length) {
                event.preventDefault();
                return;
            }
            this.deleting = true;
        },
        get deleteDescription() {
            if (this.pendingIds.length === 1) {
                const row = this.rows.find(row => String(row.id) === this.pendingIds[0]);
                return `The application from ${row?.name ?? ''} and any stored CV will be deleted.`;
            }
            return `${this.pendingIds.length} selected applications and any stored CVs will be deleted.`;
        },
    };
}
