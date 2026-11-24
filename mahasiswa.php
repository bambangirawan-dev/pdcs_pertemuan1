<?php
/**
 * REST API Mahasiswa - Pertemuan 1
 * Pemrograman Client-Server
 *
 * Endpoint : /pertemuan_1/mahasiswa.php
 * GET      : ambil semua data mahasiswa (atau satu data dengan ?id=1)
 * POST     : tambah data mahasiswa baru (body JSON: nim, nama, jurusan)
 *
 * Data disimpan di file data.json agar bisa dijalankan tanpa database.
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

const DATA_FILE = __DIR__ . '/data.json';

function bacaData(): array
{
    if (!file_exists(DATA_FILE)) {
        return [];
    }
    $isi = json_decode(file_get_contents(DATA_FILE), true);
    return is_array($isi) ? $isi : [];
}

function simpanData(array $data): void
{
    file_put_contents(
        DATA_FILE,
        json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        LOCK_EX
    );
}

function kirimResponse(int $kode, array $body): void
{
    http_response_code($kode);
    echo json_encode($body, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'OPTIONS':
        kirimResponse(200, ['status' => 'success']);

    case 'GET':
        $data = bacaData();

        if (isset($_GET['id'])) {
            $id = (int) $_GET['id'];
            foreach ($data as $mhs) {
                if ($mhs['id'] === $id) {
                    kirimResponse(200, [
                        'status' => 'success',
                        'message' => 'Data mahasiswa berhasil diambil',
                        'data' => $mhs,
                    ]);
                }
            }
            kirimResponse(404, [
                'status' => 'error',
                'message' => "Mahasiswa dengan id $id tidak ditemukan",
            ]);
        }

        kirimResponse(200, [
            'status' => 'success',
            'message' => 'Data mahasiswa berhasil diambil',
            'data' => $data,
        ]);

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);

        if (!is_array($input)) {
            kirimResponse(400, [
                'status' => 'error',
                'message' => 'Body harus berupa JSON yang valid',
            ]);
        }

        $kurang = [];
        foreach (['nim', 'nama', 'jurusan'] as $field) {
            if (!isset($input[$field]) || trim((string) $input[$field]) === '') {
                $kurang[] = $field;
            }
        }
        if ($kurang) {
            kirimResponse(400, [
                'status' => 'error',
                'message' => 'Field wajib diisi: ' . implode(', ', $kurang),
            ]);
        }

        $data = bacaData();
        $idBaru = $data ? max(array_column($data, 'id')) + 1 : 1;

        $mhsBaru = [
            'id' => $idBaru,
            'nim' => trim((string) $input['nim']),
            'nama' => trim((string) $input['nama']),
            'jurusan' => trim((string) $input['jurusan']),
        ];

        $data[] = $mhsBaru;
        simpanData($data);

        kirimResponse(201, [
            'status' => 'success',
            'message' => 'Data mahasiswa berhasil ditambahkan',
            'data' => $mhsBaru,
        ]);

    default:
        header('Allow: GET, POST, OPTIONS');
        kirimResponse(405, [
            'status' => 'error',
            'message' => "Method $method tidak didukung",
        ]);
}
