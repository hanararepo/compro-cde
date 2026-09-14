export default (init) => ({
    columns: (init.columns && init.columns.length) ? init.columns : ['Parameter', 'Typical', 'Rejection'],
    rows: (init.rows && init.rows.length) ? init.rows : [[]],

    init() {
        // Ensure each row has the same number of cells as columns
        this.syncRowLengths();
        this.$watch('columns', () => this.syncRowLengths());
    },

    syncRowLengths() {
        const len = this.columns.length;
        this.rows = this.rows.map(row => {
            const arr = Array.isArray(row) ? [...row] : [];
            while (arr.length < len) arr.push('');
            return arr.slice(0, len);
        });
    },

    addColumn() {
        if (this.columns.length >= 10) return;
        this.columns.push('');
        this.rows = this.rows.map(row => [...row, '']);
    },

    removeColumn(index) {
        if (this.columns.length <= 1) return;
        this.columns.splice(index, 1);
        this.rows = this.rows.map(row => {
            const arr = [...row];
            arr.splice(index, 1);
            return arr;
        });
    },

    addRow() {
        if (this.rows.length >= 200) return;
        this.rows.push(Array(this.columns.length).fill(''));
    },

    removeRow(index) {
        if (this.rows.length <= 1) return;
        this.rows.splice(index, 1);
    },
});
