<?php
declare(strict_types=1);

use Gotenberg\Gotenberg;
use Gotenberg\Stream;

require_once dirname(__DIR__) . '/vendor/autoload.php';

const MAX_UPLOAD_BYTES = 4 * 1024 * 1024;
const ALLOWED_EXTENSIONS = ['pptx'];
const ALLOWED_MIME_TYPES = [
    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'application/zip',
    'application/octet-stream',
];

function jsonResponse(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function failPage(string $message, int $status = 400): never
{
    http_response_code($status);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Error - PPTX to PDF</title></head><body><p>'
        . htmlspecialchars($message, ENT_QUOTES, 'UTF-8')
        . '</p><p><a href="/">Kembali</a></p></body></html>';
    exit;
}

function convertPptxToPdf(): never
{
    if (!class_exists(Gotenberg::class)) {
        jsonResponse(['error' => 'Gotenberg PHP library belum terpasang.'], 500);
    }

    $apiUrl = rtrim((string) (getenv('GOTENBERG_URL') ?: ''), '/');
    if ($apiUrl === '') {
        jsonResponse(['error' => 'GOTENBERG_URL belum dikonfigurasi di environment Vercel.'], 500);
    }

    if (!isset($_FILES['pptx']) || !is_array($_FILES['pptx'])) {
        jsonResponse(['error' => 'Silakan pilih file PPTX terlebih dahulu.'], 422);
    }

    $file = $_FILES['pptx'];
    $uploadError = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

    if ($uploadError !== UPLOAD_ERR_OK) {
        $message = match ($uploadError) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Ukuran file terlalu besar.',
            UPLOAD_ERR_NO_FILE => 'Tidak ada file yang dikirim.',
            default => 'Upload file gagal. Silakan coba lagi.',
        };
        jsonResponse(['error' => $message], 422);
    }

    $size = (int) ($file['size'] ?? 0);
    $tmpPath = (string) ($file['tmp_name'] ?? '');
    $originalName = (string) ($file['name'] ?? 'presentation.pptx');
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    if ($size <= 0 || $size > MAX_UPLOAD_BYTES) {
        jsonResponse(['error' => 'Ukuran PPTX maksimal 4 MB untuk deployment Vercel ini.'], 413);
    }

    if (!in_array($extension, ALLOWED_EXTENSIONS, true)) {
        jsonResponse(['error' => 'Format file harus PPTX.'], 415);
    }

    if (!is_uploaded_file($tmpPath) || !is_readable($tmpPath)) {
        jsonResponse(['error' => 'File upload tidak dapat dibaca oleh server.'], 422);
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmpPath) ?: '';
    if (!in_array($mime, ALLOWED_MIME_TYPES, true)) {
        jsonResponse(['error' => 'File yang dikirim bukan PPTX yang valid.'], 415);
    }

    $baseName = pathinfo($originalName, PATHINFO_FILENAME);
    $safeBaseName = preg_replace('/[^A-Za-z0-9._-]+/', '-', $baseName) ?: 'presentation';
    $outputName = substr($safeBaseName, 0, 100);

    try {
        $request = Gotenberg::libreOffice($apiUrl)
            ->convert(Stream::path($tmpPath))
            ->outputFilename($outputName);

        $response = Gotenberg::send($request);
        $pdf = (string) $response->getBody();

        if ($pdf === '') {
            throw new RuntimeException('Gotenberg mengembalikan PDF kosong.');
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $outputName . '.pdf"');
        header('Content-Length: ' . strlen($pdf));
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');
        echo $pdf;
    } catch (Throwable $e) {
        error_log('PPTX conversion failed: ' . $e->getMessage());
        jsonResponse(['error' => 'Konversi gagal. Pastikan layanan Gotenberg aktif dan file PPTX tidak rusak.'], 502);
    } finally {
        if (is_file($tmpPath)) {
            @unlink($tmpPath);
        }
    }

    exit;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'POST') {
    convertPptxToPdf();
}

if ($method !== 'GET') {
    header('Allow: GET, POST');
    failPage('Method tidak didukung.', 405);
}

$gotenbergConfigured = (string) (getenv('GOTENBERG_URL') ?: '') !== '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Konversi PowerPoint PPTX menjadi PDF dengan cepat menggunakan Gotenberg.">
    <meta name="theme-color" content="#2563eb">

    <link rel="icon" type="image/png" href="/assets/images/favicon.png">
    <link rel="shortcut icon" href="/assets/images/favicon.png">

    <title>PPTX to PDF Converter</title>

    <style>
        :root {
            color-scheme: light;
            --bg: #f5f7fb;
            --card: #ffffff;
            --text: #172033;
            --muted: #64748b;
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --border: #e2e8f0;
            --success: #15803d;
            --danger: #dc2626;
            --shadow: 0 24px 70px rgba(15, 23, 42, .10);
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: var(--text);
            background: radial-gradient(circle at top, #dbeafe 0, var(--bg) 42%, #eef2ff 100%);
            display: grid;
            place-items: center;
            padding: 24px;
        }

        .shell { width: min(720px, 100%); }
        .brand { text-align: center; margin-bottom: 24px; }
        .brand-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 58px;
            height: 58px;
            border-radius: 18px;
            background: linear-gradient(135deg, #2563eb, #4f46e5);
            color: #fff;
            font-size: 25px;
            font-weight: 800;
            box-shadow: 0 14px 35px rgba(37, 99, 235, .28);
        }
        h1 { margin: 14px 0 8px; font-size: clamp(28px, 5vw, 42px); letter-spacing: -.03em; }
        .subtitle { margin: 0; color: var(--muted); font-size: 16px; }

        .card {
            background: rgba(255,255,255,.94);
            border: 1px solid rgba(226,232,240,.9);
            border-radius: 26px;
            padding: clamp(22px, 5vw, 38px);
            box-shadow: var(--shadow);
            backdrop-filter: blur(12px);
        }

        .dropzone {
            border: 2px dashed #bfdbfe;
            border-radius: 20px;
            padding: 42px 24px;
            text-align: center;
            cursor: pointer;
            background: #f8fbff;
            transition: .2s ease;
        }
        .dropzone:hover, .dropzone.dragging { border-color: var(--primary); background: #eff6ff; transform: translateY(-1px); }
        .dropzone input { display: none; }
        .icon { font-size: 44px; margin-bottom: 12px; }
        .drop-title { font-size: 18px; font-weight: 750; }
        .drop-help { margin-top: 8px; color: var(--muted); font-size: 14px; }

        .file-info {
            display: none;
            margin-top: 16px;
            padding: 14px 16px;
            border-radius: 14px;
            background: #f1f5f9;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .file-info.visible { display: flex; }
        .file-name { min-width: 0; font-weight: 650; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .file-size { color: var(--muted); white-space: nowrap; font-size: 13px; }

        button {
            width: 100%;
            border: 0;
            border-radius: 14px;
            padding: 15px 20px;
            margin-top: 18px;
            background: linear-gradient(135deg, var(--primary), #4f46e5);
            color: white;
            font-size: 16px;
            font-weight: 750;
            cursor: pointer;
            transition: .2s ease;
        }
        button:hover:not(:disabled) { background: linear-gradient(135deg, var(--primary-dark), #4338ca); transform: translateY(-1px); }
        button:disabled { opacity: .55; cursor: not-allowed; }

        .status { min-height: 24px; margin-top: 16px; text-align: center; color: var(--muted); font-size: 14px; }
        .status.error { color: var(--danger); }
        .status.success { color: var(--success); }
        .notice {
            margin-top: 20px;
            padding: 12px 14px;
            border-radius: 12px;
            background: #fff7ed;
            color: #9a3412;
            font-size: 13px;
        }
        .footer { text-align: center; color: #94a3b8; font-size: 12px; margin-top: 18px; }
    </style>
</head>
<body>
<main class="shell">
    <header class="brand">
        <div class="brand-badge">P→PDF</div>
        <h1>PPTX to PDF Converter</h1>
        <p class="subtitle">Ubah presentasi PowerPoint menjadi PDF dalam beberapa detik.</p>
    </header>

    <section class="card">
        <form id="converterForm" method="post" enctype="multipart/form-data">
            <label class="dropzone" id="dropzone" for="pptx">
                <input id="pptx" name="pptx" type="file" accept=".pptx,application/vnd.openxmlformats-officedocument.presentationml.presentation">
                <div class="icon">📊</div>
                <div class="drop-title">Pilih atau tarik file PPTX ke sini</div>
                <div class="drop-help">Format PPTX · Maksimal 4 MB</div>
            </label>

            <div class="file-info" id="fileInfo">
                <span class="file-name" id="fileName"></span>
                <span class="file-size" id="fileSize"></span>
            </div>

            <button id="convertButton" type="submit" disabled>Convert to PDF</button>
            <div class="status" id="status" aria-live="polite"></div>
        </form>

        <?php if (!$gotenbergConfigured): ?>
            <div class="notice">Layanan belum siap: administrator perlu mengatur environment variable <strong>GOTENBERG_URL</strong>.</div>
        <?php endif; ?>
    </section>

    <div class="footer">Powered by PHP · Gotenberg · LibreOffice</div>
</main>

<script>
const form = document.getElementById('converterForm');
const input = document.getElementById('pptx');
const dropzone = document.getElementById('dropzone');
const fileInfo = document.getElementById('fileInfo');
const fileName = document.getElementById('fileName');
const fileSize = document.getElementById('fileSize');
const button = document.getElementById('convertButton');
const status = document.getElementById('status');

const maxBytes = 4 * 1024 * 1024;

function formatBytes(bytes) {
    return `${(bytes / 1024 / 1024).toFixed(2)} MB`;
}

function setFile(file) {
    if (!file) return;
    if (!file.name.toLowerCase().endsWith('.pptx')) {
        status.textContent = 'File harus berformat .pptx.';
        status.className = 'status error';
        input.value = '';
        button.disabled = true;
        return;
    }
    if (file.size > maxBytes) {
        status.textContent = 'Ukuran file melebihi batas 4 MB.';
        status.className = 'status error';
        input.value = '';
        button.disabled = true;
        return;
    }
    fileName.textContent = file.name;
    fileSize.textContent = formatBytes(file.size);
    fileInfo.classList.add('visible');
    button.disabled = false;
    status.textContent = '';
    status.className = 'status';
}

input.addEventListener('change', () => setFile(input.files[0]));
['dragenter', 'dragover'].forEach(eventName => dropzone.addEventListener(eventName, e => {
    e.preventDefault();
    dropzone.classList.add('dragging');
}));
['dragleave', 'drop'].forEach(eventName => dropzone.addEventListener(eventName, e => {
    e.preventDefault();
    dropzone.classList.remove('dragging');
}));
dropzone.addEventListener('drop', e => {
    const file = e.dataTransfer.files[0];
    if (!file) return;
    try {
        const transfer = new DataTransfer();
        transfer.items.add(file);
        input.files = transfer.files;
    } catch (_) {}
    setFile(file);
});

form.addEventListener('submit', async event => {
    event.preventDefault();
    if (!input.files[0]) return;

    button.disabled = true;
    button.textContent = 'Converting...';
    status.textContent = 'Mengirim PPTX ke Gotenberg...';
    status.className = 'status';

    try {
        const response = await fetch(window.location.href, {
            method: 'POST',
            body: new FormData(form),
            headers: { 'Accept': 'application/pdf, application/json' }
        });

        const contentType = response.headers.get('content-type') || '';
        if (!response.ok || contentType.includes('application/json')) {
            let message = 'Konversi gagal.';
            try {
                const data = await response.json();
                message = data.error || message;
            } catch (_) {}
            throw new Error(message);
        }

        const blob = await response.blob();
        const url = URL.createObjectURL(blob);
        const anchor = document.createElement('a');
        anchor.href = url;
        anchor.download = (input.files[0].name.replace(/\.pptx$/i, '') || 'presentation') + '.pdf';
        document.body.appendChild(anchor);
        anchor.click();
        anchor.remove();
        URL.revokeObjectURL(url);
        status.textContent = 'Konversi berhasil. PDF sedang diunduh.';
        status.className = 'status success';
    } catch (error) {
        status.textContent = error.message || 'Terjadi kesalahan saat konversi.';
        status.className = 'status error';
    } finally {
        button.disabled = false;
        button.textContent = 'Convert to PDF';
    }
});
</script>
</body>
</html>
