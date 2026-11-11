
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (!function_exists('normalisasi_role')) {
    function normalisasi_role(string $role): string
    {
        $role = strtolower(trim($role));
        $role = str_replace(['-', ' '], '_', $role);

        $alias = [
            'administrator' => 'admin',
            'guru_sebagai_wali_kelas' => 'guru_wali_kelas',
            'wali_kelas_saja' => 'wali_kelas'
        ];

        return $alias[$role] ?? $role;
    }
}


if (!function_exists('wajib_login')) {
    function wajib_login(): void
    {
        if (empty($_SESSION['login'])) {
            header('Location: /ukk-2026/login.php');
            exit;
        }
    }
}


if (!function_exists('role_pengguna')) {
    function role_pengguna(): string
    {
        return normalisasi_role(
            (string) ($_SESSION['role'] ?? '')
        );
    }
}


if (!function_exists('wajib_role')) {
    function wajib_role(array $roles): void
    {
        wajib_login();

        $rolePengguna = role_pengguna();

        $rolesDiizinkan = array_map(
            static function ($role) {
                return normalisasi_role((string) $role);
            },
            $roles
        );

        if (!in_array($rolePengguna, $rolesDiizinkan, true)) {
            http_response_code(403);
            ?>
            <!DOCTYPE html>
            <html lang="id">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <title>Akses Ditolak | SI-TIB</title>

                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
                <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
            </head>

            <body class="bg-light">
                <div class="container min-vh-100 d-flex align-items-center justify-content-center">
                    <div class="card border-0 shadow-sm rounded-4 p-4 text-center w-100"
                         style="max-width: 450px;">

                        <i class="bi bi-shield-lock-fill text-danger display-3 mb-3"></i>

                        <h2 class="fw-bold">403 - Akses Ditolak</h2>

                        <p class="text-secondary">
                            Maaf, akun Anda tidak memiliki izin untuk membuka halaman ini.
                        </p>

                        <a href="/ukk-2026/dashboard.php" class="btn btn-primary">
                            <i class="bi bi-house-door me-1"></i>
                            Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </body>
            </html>
            <?php
            exit;
        }
    }
}