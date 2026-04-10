  function myalert() { 
    alert("Pilih pilih aja dibawah kak🤗🤗\n "); 
} 
  function myalert1() { 
  alert("Thank youu\n " + 
      "Email kamu Berhasil\n " + 
      "Ditunggu ya Diskonnya🤗🤗"); 
} 
document.querySelectorAll('.form-select').forEach(select => {
    select.addEventListener('change', function() {
        this.style.color = 'white';
    });
});
// Validasi form dan pemicu animasi
    document.querySelector('form').addEventListener('submit', function(e) {
        // 1. Cek apakah semua field yang required sudah diisi (bawaan HTML)
        if (!this.checkValidity()) {
            return; // Biarkan browser menampilkan peringatan merah default
        }

        // 2. Cek khusus untuk centang persetujuan
        if (!document.getElementById('agreeTerms').checked) {
            e.preventDefault();
            alert('Anda harus menyetujui syarat dan ketentuan terlebih dahulu');
            return;
        }

        // 3. Jika lolos semua validasi, tampilkan layar animasi!
        // Catatan: Kita TIDAK mencegah pengiriman data (e.preventDefault() tidak dipanggil di sini), 
        // sehingga form tetap terkirim ke server sementara layar ditutupi animasi.
        document.getElementById('processingOverlay').style.display = 'flex';
    });