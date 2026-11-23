{{-- Aset DataTables (jQuery + Bootstrap 5). Produksi: pasang lokal lewat npm dan hapus CDN. --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script>
    var DT_BAHASA = {
        search: 'Cari:', lengthMenu: 'Tampilkan _MENU_ data', processing: 'Memuat data...',
        info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data', infoEmpty: 'Tidak ada data',
        infoFiltered: '(disaring dari _MAX_ data)', zeroRecords: 'Data tidak ditemukan', emptyTable: 'Belum ada data',
        paginate: { first: 'Pertama', last: 'Terakhir', next: 'Berikutnya', previous: 'Sebelumnya' }
    };
</script>
<script>
    function rupiah(v) {
        if (v === null || v === undefined || v === '') return '-';
        return 'Rp' + new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(parseFloat(v));
    }
    function esc(t) {
        return String(t === null || t === undefined ? '' : t).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }
</script>