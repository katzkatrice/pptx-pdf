# PPTX to PDF Converter

Web application PHP untuk mengubah file PowerPoint `.pptx` menjadi PDF menggunakan [Gotenberg](https://gotenberg.dev/) dan LibreOffice.

## Arsitektur

- `public/index.php` — UI converter + endpoint POST konversi.
- `api/index.php` — adapter tipis untuk Vercel Functions.
- `vercel.json` — routing dan runtime `vercel-php@0.9.0`.
- `composer.json` — Gotenberg PHP client dan PSR-18 Guzzle adapter.
- `.github/workflows/ci-cd.yml` — validasi PHP/Composer dan optional deployment ke Vercel.

Gotenberg adalah service terpisah. PHP mengirim file PPTX ke endpoint LibreOffice Gotenberg lalu mengembalikan PDF hasil konversi.

## Environment Vercel

Buat environment variable berikut pada Vercel:

```text
GOTENBERG_URL=https://alamat-gotenberg-anda
```

Jangan memasukkan token atau URL private ke source code.

## Batas upload Vercel

Aplikasi ini membatasi PPTX menjadi **4 MB** agar berada di bawah batas payload Serverless Function Vercel. Vercel saat ini mendokumentasikan batas request/response function sebesar 4.5 MB. Untuk file lebih besar, arsitektur perlu diubah menjadi direct-to-storage/client upload lalu PHP hanya mengirim URL/file reference ke worker converter.

## GitHub Actions CI/CD

Workflow menjalankan Composer validation, instalasi dependency, PHP syntax check, lalu deployment production ke Vercel pada push ke `main` jika secrets tersedia.

Tambahkan GitHub Actions secrets:

- `VERCEL_TOKEN`
- `VERCEL_ORG_ID`
- `VERCEL_PROJECT_ID`

Jika secrets belum dibuat, job deploy dilewati dan job CI tetap berjalan.

## Gotenberg

Untuk self-hosting, jalankan Gotenberg 8.x, misalnya:

```bash
docker run --rm -p 3000:3000 gotenberg/gotenberg:8
```

Kemudian arahkan `GOTENBERG_URL` ke server tersebut.

## Local development

```bash
composer install
```

Jalankan Gotenberg secara lokal, lalu set:

```text
GOTENBERG_URL=http://localhost:3000
```

## Deployment

Repository tetap kompatibel dengan konfigurasi Vercel yang sudah ada. Import repository ke Vercel untuk deployment berbasis Git, atau gunakan GitHub Actions dengan secrets Vercel di atas.
