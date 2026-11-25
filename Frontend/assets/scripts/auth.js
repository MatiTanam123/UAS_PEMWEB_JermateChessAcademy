// Tampilkan/sembunyikan password
document.querySelectorAll('.pw-toggle').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var input = document.getElementById(btn.dataset.target);
        var tampil = input.type === 'password';
        input.type = tampil ? 'text' : 'password';
        btn.textContent = tampil ? 'Sembunyikan' : 'Tampilkan';
    });
});

// Cek kecocokan password & konfirmasi secara langsung (validasi utama tetap di server)
var pw = document.getElementById('password');
var konf = document.getElementById('konfirmasi');
var info = document.getElementById('pw-match');

if (pw && konf && info) {
    function cek() {
        if (konf.value === '') { info.textContent = ''; info.className = 'pw-match'; return; }
        var sama = pw.value === konf.value;
        info.textContent = sama ? 'Password cocok.' : 'Password belum sama.';
        info.className = 'pw-match ' + (sama ? 'ok' : 'bad');
    }
    pw.addEventListener('input', cek);
    konf.addEventListener('input', cek);
}
