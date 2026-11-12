<script>
    (() => {
        const selectAll = document.getElementById('selectAll');
        const rows = Array.from(document.querySelectorAll('.row-check'));
        const counter = document.getElementById('selectedCount');
        const bulkButton = document.getElementById('bulkBtn');
        const bulkForm = document.getElementById('bulkForm');

        const checkedCount = () => rows.filter((row) => row.checked).length;

        function refresh() {
            const count = checkedCount();

            counter.textContent = count;
            bulkButton.disabled = count === 0;
            selectAll.checked = rows.length > 0 && count === rows.length;
            selectAll.indeterminate = count > 0 && count < rows.length;
        }

        selectAll.addEventListener('change', () => {
            rows.forEach((row) => (row.checked = selectAll.checked));
            refresh();
        });

        rows.forEach((row) => row.addEventListener('change', refresh));

        bulkForm.addEventListener('submit', (event) => {
            const count = checkedCount();

            if (count === 0 || ! confirm(`Ubah status ${count} pesanan yang dipilih?`)) {
                event.preventDefault();
            }
        });

        refresh();
    })();
</script>
