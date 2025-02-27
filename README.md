<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## DB

1. proses penggajian itu seperti apa pak?
2. saat spm di tolak masih blm mau

kabid lianto
kaban edward

ALTER TABLE `ta_kasda` ADD `is_proses` INT(1) NOT NULL DEFAULT '0' AFTER `Cair`;


# belum selesai
- kode rekening bank (RKUD)

rekening skpdnya 1010100044 ya pak

8203181679
oke => 556801033308533 => bri => 002

gunakan no rekening ini saja pak
1742102983 => brks => 119


PPh22 => pihak ke tiga nama_npwp
PPh23, PPN dan PPh21 => nama dinas

wajib cek mapping potongan
wajib cek npwp pajak, krn tidak boleh kosong (sudah di verifikasi)
wajib cek balance nilai SP2D = nilai potongan = nilai transaksi

# data testing
kubectl exec -it asis-evi-559477fc65-sfkt4 /bin/bash

KEUANGAN :
GAJI
SPP : 21.05/02.0/000655/LS/5.02.0.00.0.00.01.0000/P6/9/2024
SPM :
SP2D : 21.05/04.0/000616/LS/5.02.0.00.0.00.01.0000/P6/9/2024
21.05/04.0/000616/LS/5.02.0.00.0.00.01.0000/P6/9/2024

TPP (KEUANGAN)
SPP : 21.05/02.0/000659/LS/5.02.0.00.0.00.01.0000/P6/9/2024
SPPD : 21.05/04.0/000620/LS/5.02.0.00.0.00.01.0000/P6/10/2024

=====


KEUANGAN LS PEMELIHARAAN
SPM : 21.05/03.0/000663/LS/5.02.0.00.0.00.01.0000/P6/10/2024
SP2D : 21.05/03.0/000663/LS/5.02.0.00.0.00.01.0000/P6/10/2024
21.05/04.0/000623/LS/5.02.0.00.0.00.01.0000/P6/10/2024


'Persyaratan Minimal:
A. Pencairan LS:
- Verifikasi Dokumen SPP
- Verifikasi Dokumen SPM;
- Verifikasi ID Billing;
- Verifikasi Perikatan: (Resume Kontrak/Kwitansi/Invoice/BAST/BA PHO/BA Persetujuan Pembayaran/Laporan Progres);
- Upload DPA/DPPA.
B. Pencairan GU:
- Dokumen SPP
- Dokumen SPM
- Dokumen BKU
- Dokumen SPJ Fungsional
- Dokumen SPJ Administrator
- Dokumen Bukti Pajak
- Dokumen DPA/DPPA


KEUANGAN GU :
SPM : 21.05/03.0/000702/GU/5.02.0.00.0.00.01.0000/P6/10/2024
SP2D : 21.05/04.0/000659/GU/5.02.0.00.0.00.01.0000/P6/10/2024

21.05/04.0/000623/LS/5.02.0.00.0.00.01.0000/P6/10/2024

#INTEGRASI BANK :
1.
2. transaction id = originalPartnerReferenceNo = tx_partner_id
3. berarti X-EXTERNAL-ID ini dinamis
4. originalExternalId  =  X-EXTERNAL-ID
5. X-EXTERNAL-ID untuk transaksi yang satu OB pasti sama pak

## CATATAN

jenis_ls_sp_2_d
bulan_gaji

21.05/04.0/000191/LS/2.16.2.21.2.20.01.0000/P6/10/2024

jumlah_potongan
jumlah_ditransfer
SELECT sum(jumlah_ditransfer) FROM `ta_gaji` WHERE jenis_gaji=1;


https://service.sipd.kemendagri.go.id/pengeluaran/strict/gaji-pegawai/cetak?id_skpd=157&bulan=10&jenis_pegawai=pns

    url: config.service_url+'pengeluaran/strict/gaji-pegawai/cetak?id_skpd='+id_skpd+'&bulan=1'+bulan_gaji,


1. beda pajak dan potongan
2. kalau bank selain brks



- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains over 2000 video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the Laravel [Patreon page](https://patreon.com/taylorotwell).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Cubet Techno Labs](https://cubettech.com)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[Many](https://www.many.co.uk)**
- **[Webdock, Fast VPS Hosting](https://www.webdock.io/en)**
- **[DevSquad](https://devsquad.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[OP.GG](https://op.gg)**
- **[WebReinvent](https://webreinvent.com/?utm_source=laravel&utm_medium=github&utm_campaign=patreon-sponsors)**
- **[Lendio](https://lendio.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
