<?php

session_start();

/* =========================================================
   CEK LOGIN
========================================================= */

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}


/* =========================================================
   DATA USER
========================================================= */

$nama  = $_SESSION['user_name'] ?? '';
$email = $_SESSION['user_email'] ?? '';
$role  = $_SESSION['role'] ?? '';


/* =========================================================
   FORMAT ROLE
========================================================= */

if ($role === 'admin') {
    $nama_role = 'Administrator';
} elseif ($role === 'guru') {
    $nama_role = 'Guru';
} elseif ($role === 'wali_kelas') {
    $nama_role = 'Wali Kelas';
} else {
    $nama_role = 'Tidak Dikenal';
}


/* =========================================================
   INISIAL NAMA
========================================================= */

$inisial = strtoupper(substr($nama, 0, 1));

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Dashboard - Sistem Pelanggaran Siswa</title>


<style>

/* =========================================================
   RESET
========================================================= */

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html {
    scroll-behavior: smooth;
}

body {
    font-family:
        Arial,
        Helvetica,
        sans-serif;

    background: #f5f7fb;
    color: #222;
}

a {
    text-decoration: none;
    color: inherit;
}


/* =========================================================
   LAYOUT
========================================================= */

.dashboard-layout {
    min-height: 100vh;
    display: flex;
}


/* =========================================================
   SIDEBAR
========================================================= */

.dashboard-sidebar {
    width: 260px;
    min-height: 100vh;

    position: fixed;
    left: 0;
    top: 0;
    bottom: 0;

    display: flex;
    flex-direction: column;

    background:
        linear-gradient(
            180deg,
            #e91e63 0%,
            #c2185b 55%,
            #ad1457 100%
        );

    color: #fff;

    box-shadow:
        4px 0 20px rgba(0,0,0,0.08);

    z-index: 100;
}


/* =========================================================
   BRAND
========================================================= */

.dashboard-brand {
    display: flex;
    align-items: center;
    gap: 12px;

    padding: 25px 22px;

    border-bottom:
        1px solid rgba(255,255,255,0.15);
}

.brand-logo {
    width: 48px;
    height: 48px;

    display: flex;
    align-items: center;
    justify-content: center;

    background:
        rgba(255,255,255,0.18);

    border:
        1px solid rgba(255,255,255,0.20);

    border-radius: 14px;

    font-size: 25px;

    box-shadow:
        0 5px 15px rgba(0,0,0,0.08);
}

.brand-text {
    display: flex;
    flex-direction: column;
}

.brand-text strong {
    font-size: 17px;
    letter-spacing: 0.5px;
}

.brand-text span {
    margin-top: 3px;

    font-size: 12px;

    color:
        rgba(255,255,255,0.75);
}


/* =========================================================
   SIDEBAR USER
========================================================= */

.sidebar-user {
    display: flex;
    align-items: center;

    gap: 12px;

    margin: 18px 15px;
    padding: 13px;

    background:
        rgba(255,255,255,0.10);

    border:
        1px solid rgba(255,255,255,0.10);

    border-radius: 14px;
}

.sidebar-avatar {
    width: 43px;
    height: 43px;

    flex-shrink: 0;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 50%;

    background: #fff;

    color: #c2185b;

    font-weight: bold;
    font-size: 18px;
}

.sidebar-user-info {
    min-width: 0;

    display: flex;
    flex-direction: column;
}

.sidebar-user-info strong {
    font-size: 13px;

    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.sidebar-user-info span {
    margin-top: 3px;

    font-size: 11px;

    color:
        rgba(255,255,255,0.72);
}


/* =========================================================
   SIDEBAR HEADING
========================================================= */

.sidebar-heading {
    padding:
        13px 20px 7px;

    font-size: 10px;

    font-weight: bold;

    letter-spacing: 1.2px;

    color:
        rgba(255,255,255,0.58);
}


/* =========================================================
   NAVIGATION
========================================================= */

.dashboard-nav {
    padding:
        0 12px;

    overflow-y: auto;
}

.dashboard-nav-item {
    display: flex;
    align-items: center;

    gap: 12px;

    margin-bottom: 5px;

    padding:
        12px 14px;

    border-radius: 11px;

    color:
        rgba(255,255,255,0.88);

    font-size: 13px;

    transition:
        all 0.2s ease;
}

.dashboard-nav-item:hover {
    background:
        rgba(255,255,255,0.13);

    color: #fff;

    transform:
        translateX(2px);
}

.dashboard-nav-item.active {
    background: #fff;

    color: #c2185b;

    font-weight: bold;

    box-shadow:
        0 5px 15px
        rgba(0,0,0,0.10);
}

.nav-icon {
    width: 25px;

    display: inline-flex;

    justify-content: center;

    font-size: 16px;
}


/* =========================================================
   LOGOUT
========================================================= */

.sidebar-bottom {
    margin-top: auto;

    padding: 15px;
}

.sidebar-logout {
    display: flex;
    align-items: center;
    justify-content: center;

    gap: 8px;

    width: 100%;

    padding: 12px;

    background:
        rgba(255,255,255,0.10);

    border:
        1px solid rgba(255,255,255,0.12);

    border-radius: 11px;

    color: #fff;

    font-size: 13px;

    transition: 0.2s;
}

.sidebar-logout:hover {
    background: #fff;

    color: #c2185b;
}


/* =========================================================
   MAIN
========================================================= */

.dashboard-main {
    width: calc(100% - 260px);

    margin-left: 260px;

    min-height: 100vh;

    background: #f5f7fb;
}


/* =========================================================
   TOPBAR
========================================================= */

.dashboard-topbar {
    height: 80px;

    padding:
        0 35px;

    display: flex;
    align-items: center;
    justify-content: space-between;

    background: #fff;

    border-bottom:
        1px solid #eeeeee;

    position: sticky;
    top: 0;

    z-index: 50;
}

.topbar-small {
    font-size: 10px;

    font-weight: bold;

    letter-spacing: 1px;

    color: #e91e63;

    margin-bottom: 3px;
}

.dashboard-topbar h1 {
    font-size: 22px;

    color: #222;
}

.topbar-profile {
    display: flex;
    align-items: center;

    gap: 10px;
}

.topbar-avatar {
    width: 42px;
    height: 42px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 50%;

    background: #fce4ec;

    color: #c2185b;

    font-weight: bold;
}

.topbar-user {
    display: flex;
    flex-direction: column;
}

.topbar-user strong {
    font-size: 13px;

    color: #222;
}

.topbar-user span {
    margin-top: 3px;

    font-size: 11px;

    color: #888;
}


/* =========================================================
   CONTENT
========================================================= */

.dashboard-content {
    padding: 30px 35px;
}


/* =========================================================
   WELCOME
========================================================= */

.welcome-card {
    min-height: 175px;

    padding: 30px;

    display: flex;
    align-items: center;
    justify-content: space-between;

    border-radius: 20px;

    background:
        linear-gradient(
            135deg,
            #e91e63,
            #c2185b
        );

    color: #fff;

    box-shadow:
        0 12px 30px
        rgba(194,24,91,0.20);

    overflow: hidden;
}

.welcome-content {
    max-width: 700px;
}

.welcome-label {
    font-size: 11px;

    font-weight: bold;

    letter-spacing: 1px;

    color:
        rgba(255,255,255,0.85);
}

.welcome-content h2 {
    margin-top: 9px;

    font-size: 27px;

    color: #fff;
}

.welcome-content p {
    margin-top: 9px;

    font-size: 13px;

    line-height: 1.7;

    color:
        rgba(255,255,255,0.82);
}

.welcome-icon {
    width: 90px;
    height: 90px;

    display: flex;
    align-items: center;
    justify-content: center;

    flex-shrink: 0;

    border-radius: 25px;

    background:
        rgba(255,255,255,0.14);

    font-size: 42px;
}


/* =========================================================
   STATISTICS
========================================================= */

.dashboard-stats {
    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 18px;

    margin-top: 22px;
}

.stat-box {
    min-height: 125px;

    padding: 20px;

    display: flex;
    align-items: center;

    gap: 15px;

    background: #fff;

    border:
        1px solid #eeeeee;

    border-radius: 16px;

    box-shadow:
        0 5px 18px
        rgba(0,0,0,0.04);
}

.stat-box-icon {
    width: 52px;
    height: 52px;

    flex-shrink: 0;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 14px;

    font-size: 23px;
}

.stat-box-icon.pink {
    background: #fce4ec;
    color: #c2185b;
}

.stat-box-icon.blue {
    background: #e3f2fd;
    color: #1976d2;
}

.stat-box-icon.purple {
    background: #f3e5f5;
    color: #7b1fa2;
}

.stat-box-icon.orange {
    background: #fff3e0;
    color: #ef6c00;
}

.stat-box-content {
    display: flex;
    flex-direction: column;
}

.stat-box-content span {
    font-size: 11px;
    color: #888;
}

.stat-box-content strong {
    margin-top: 4px;

    font-size: 25px;

    color: #222;
}

.stat-box-content small {
    margin-top: 2px;

    font-size: 10px;

    color: #aaa;
}


/* =========================================================
   PROFILE
========================================================= */

.profile-card {
    margin-top: 22px;

    padding: 25px;

    background: #fff;

    border:
        1px solid #eeeeee;

    border-radius: 18px;

    box-shadow:
        0 5px 18px
        rgba(0,0,0,0.04);
}

.profile-header {
    display: flex;
    align-items: center;

    gap: 13px;

    margin-bottom: 20px;
}

.profile-header-icon {
    width: 45px;
    height: 45px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 13px;

    background: #fce4ec;

    color: #c2185b;

    font-size: 20px;
}

.profile-header h2 {
    font-size: 18px;

    color: #222;
}

.profile-header p {
    margin-top: 4px;

    font-size: 11px;

    color: #999;
}

.profile-grid {
    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 15px;
}

.profile-item {
    padding: 15px;

    background: #f8f9fb;

    border-radius: 12px;
}

.profile-item span {
    display: block;

    margin-bottom: 7px;

    font-size: 10px;

    color: #888;
}

.profile-item strong {
    font-size: 13px;

    color: #333;

    word-break: break-word;
}

.role-badge {
    display: inline-block;

    padding: 6px 10px;

    border-radius: 20px;

    background: #fce4ec;

    color: #c2185b !important;

    font-size: 11px !important;
}


/* =========================================================
   SECTION
========================================================= */

.dashboard-menu-section {
    margin-top: 30px;
}

.section-heading {
    margin-bottom: 17px;
}

.section-heading span {
    font-size: 10px;

    font-weight: bold;

    letter-spacing: 1px;

    color: #e91e63;
}

.section-heading h2 {
    margin-top: 4px;

    font-size: 21px;

    color: #222;
}


/* =========================================================
   SYSTEM CARDS
========================================================= */

.dashboard-menu-grid {
    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 17px;
}

.system-card {
    min-height: 165px;

    padding: 21px;

    display: flex;
    align-items: flex-start;

    gap: 15px;

    position: relative;

    background: #fff;

    border:
        1px solid #eeeeee;

    border-radius: 17px;

    box-shadow:
        0 5px 18px
        rgba(0,0,0,0.04);

    transition:
        all 0.2s ease;
}

.system-card:hover {
    transform:
        translateY(-3px);

    border-color: #f48fb1;

    box-shadow:
        0 10px 25px
        rgba(194,24,91,0.12);
}

.system-card-icon {
    width: 50px;
    height: 50px;

    flex-shrink: 0;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 14px;

    font-size: 21px;
}

.system-card-icon.pink {
    background: #fce4ec;
    color: #c2185b;
}

.system-card-icon.red {
    background: #ffebee;
    color: #c62828;
}

.system-card-icon.blue {
    background: #e3f2fd;
    color: #1976d2;
}

.system-card-icon.orange {
    background: #fff3e0;
    color: #ef6c00;
}

.system-card-icon.green {
    background: #e8f5e9;
    color: #2e7d32;
}

.system-card-icon.purple {
    background: #f3e5f5;
    color: #7b1fa2;
}

.system-card-icon.yellow {
    background: #fff8e1;
    color: #f57f17;
}

.system-card-icon.dark {
    background: #eeeeee;
    color: #424242;
}

.system-card-content {
    padding-right: 20px;
}

.system-card-content h3 {
    font-size: 15px;

    color: #222;
}

.system-card-content p {
    margin-top: 7px;

    font-size: 11px;

    line-height: 1.6;

    color: #888;
}

.card-arrow {
    position: absolute;

    right: 18px;
    bottom: 16px;

    font-size: 20px;

    color: #ccc;

    transition: 0.2s;
}

.system-card:hover .card-arrow {
    color: #e91e63;

    transform:
        translateX(3px);
}


/* =========================================================
   FOOTER
========================================================= */

.dashboard-footer {
    margin-top: 35px;

    padding:
        22px 5px;

    text-align: center;

    border-top:
        1px solid #eaeaea;

    color: #999;

    font-size: 11px;

    line-height: 1.7;
}

.dashboard-footer strong {
    color: #555;
}


/* =========================================================
   TABLET
========================================================= */

@media (max-width: 1100px) {

    .dashboard-sidebar {
        width: 230px;
    }

    .dashboard-main {
        width: calc(100% - 230px);
        margin-left: 230px;
    }

    .dashboard-stats {
        grid-template-columns:
            repeat(2, 1fr);
    }

    .dashboard-menu-grid {
        grid-template-columns:
            repeat(2, 1fr);
    }

}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 800px) {

    .dashboard-layout {
        display: block;
    }

    .dashboard-sidebar {
        width: 100%;
        min-height: auto;

        position: relative;

        border-radius: 0;
    }

    .dashboard-brand {
        padding: 18px;
    }

    .sidebar-user {
        margin: 12px 15px;
    }

    .dashboard-nav {
        display: flex;

        gap: 7px;

        padding: 5px 12px 12px;

        overflow-x: auto;
    }

    .sidebar-heading {
        display: none;
    }

    .dashboard-nav-item {
        flex-shrink: 0;

        padding:
            10px 13px;

        margin: 0;
    }

    .sidebar-bottom {
        padding-top: 5px;
    }

    .sidebar-logout {
        margin-bottom: 10px;
    }

    .dashboard-main {
        width: 100%;

        margin-left: 0;
    }

    .dashboard-topbar {
        height: 70px;

        padding:
            0 18px;
    }

    .dashboard-content {
        padding:
            20px 15px;
    }

    .welcome-card {
        padding: 23px;

        min-height: auto;
    }

    .welcome-content h2 {
        font-size: 22px;
    }

    .welcome-icon {
        width: 65px;
        height: 65px;

        font-size: 30px;
    }

    .dashboard-stats {
        grid-template-columns:
            repeat(2, 1fr);

        gap: 12px;
    }

    .stat-box {
        padding: 15px;
    }

    .stat-box-icon {
        width: 43px;
        height: 43px;

        font-size: 18px;
    }

    .profile-grid {
        grid-template-columns: 1fr;
    }

    .dashboard-menu-grid {
        grid-template-columns: 1fr;
    }

}


/* =========================================================
   HP KECIL
========================================================= */

@media (max-width: 500px) {

    .dashboard-topbar {
        padding: 0 14px;
    }

    .topbar-user {
        display: none;
    }

    .dashboard-topbar h1 {
        font-size: 19px;
    }

    .welcome-card {
        padding: 20px;

        border-radius: 16px;
    }

    .welcome-content h2 {
        font-size: 19px;
    }

    .welcome-content p {
        font-size: 11px;
    }

    .welcome-icon {
        width: 52px;
        height: 52px;

        border-radius: 15px;

        font-size: 23px;
    }

    .dashboard-stats {
        grid-template-columns: 1fr;
    }

    .stat-box {
        min-height: 95px;
    }

    .profile-card {
        padding: 18px;
    }

    .system-card {
        min-height: 140px;

        padding: 17px;
    }

}

</style>

</head>


<body class="dashboard-page">


<div class="dashboard-layout">


<!-- =====================================================
     SIDEBAR
====================================================== -->

<aside class="dashboard-sidebar">


    <div class="dashboard-brand">

        <div class="brand-logo">
            ⚖️
        </div>

        <div class="brand-text">

            <strong>
                SISWA
            </strong>

            <span>
                Pelanggaran
            </span>

        </div>

    </div>


    <div class="sidebar-user">

        <div class="sidebar-avatar">
            <?= htmlspecialchars($inisial); ?>
        </div>

        <div class="sidebar-user-info">

            <strong>
                <?= htmlspecialchars($nama); ?>
            </strong>

            <span>
                <?= htmlspecialchars($nama_role); ?>
            </span>

        </div>

    </div>


    <div class="sidebar-heading">
        MENU UTAMA
    </div>


    <nav class="dashboard-nav">


        <a href="dashboard.php"
           class="dashboard-nav-item active">

            <span class="nav-icon">
                🏠
            </span>

            <span>
                Dashboard
            </span>

        </a>


        <?php if ($role === 'admin'): ?>

            <div class="sidebar-heading">
                ADMINISTRATOR
            </div>


            <a href="data_master.php"
               class="dashboard-nav-item">

                <span class="nav-icon">
                    🗂️
                </span>

                <span>
                    Data Master
                </span>

            </a>


            <a href="pelanggaran.php"
               class="dashboard-nav-item">

                <span class="nav-icon">
                    ⚠️
                </span>

                <span>
                    Data Pelanggaran
                </span>

            </a>

        <?php endif; ?>


        <div class="sidebar-heading">
            PELANGGARAN
        </div>


        <?php if ($role === 'admin' || $role === 'guru'): ?>

            <a href="catat_pelanggaran.php"
               class="dashboard-nav-item">

                <span class="nav-icon">
                    📝
                </span>

                <span>
                    Catat Pelanggaran
                </span>

            </a>


            <a href="tindakan.php"
               class="dashboard-nav-item">

                <span class="nav-icon">
                    ⚖️
                </span>

                <span>
                    Tindakan
                </span>

            </a>

        <?php endif; ?>


        <a href="laporan.php"
           class="dashboard-nav-item">

            <span class="nav-icon">
                📊
            </span>

            <span>
                Laporan
            </span>

        </a>


        <a href="riwayat_pelanggaran.php"
           class="dashboard-nav-item">

            <span class="nav-icon">
                📋
            </span>

            <span>
                Riwayat
            </span>

        </a>


        <a href="rekap_poin.php"
           class="dashboard-nav-item">

            <span class="nav-icon">
                ⭐
            </span>

            <span>
                Rekap Poin
            </span>

        </a>


        <?php if ($role === 'admin'): ?>

            <div class="sidebar-heading">
                ADMINISTRASI
            </div>


            <a href="cetak_laporan.php"
               class="dashboard-nav-item">

                <span class="nav-icon">
                    🖨️
                </span>

                <span>
                    Cetak / Export
                </span>

            </a>

        <?php endif; ?>


    </nav>


    <div class="sidebar-bottom">

        <a href="logout.php"
           class="sidebar-logout"
           onclick="return confirm('Yakin ingin logout?');">

            <span>
                🚪
            </span>

            Logout

        </a>

    </div>


</aside>


<!-- =====================================================
     MAIN
====================================================== -->

<main class="dashboard-main">


<header class="dashboard-topbar">


    <div>

        <div class="topbar-small">
            SISTEM PENGELOLAAN
        </div>

        <h1>
            Dashboard
        </h1>

    </div>


    <div class="topbar-profile">

        <div class="topbar-avatar">
            <?= htmlspecialchars($inisial); ?>
        </div>

        <div class="topbar-user">

            <strong>
                <?= htmlspecialchars($nama); ?>
            </strong>

            <span>
                <?= htmlspecialchars($nama_role); ?>
            </span>

        </div>

    </div>


</header>


<div class="dashboard-content">


<!-- =====================================================
     WELCOME
====================================================== -->

<section class="welcome-card">

    <div class="welcome-content">

        <span class="welcome-label">
            SELAMAT DATANG 👋
        </span>

        <h2>
            Halo, <?= htmlspecialchars($nama); ?>!
        </h2>

        <p>
            Kelola dan pantau data pelanggaran
            siswa dengan mudah melalui sistem ini.
        </p>

    </div>


    <div class="welcome-icon">
        ⚖️
    </div>

</section>


<!-- =====================================================
     STATISTICS
====================================================== -->

<section class="dashboard-stats">


    <div class="stat-box">

        <div class="stat-box-icon pink">
            👨‍🎓
        </div>

        <div class="stat-box-content">

            <span>
                Data Siswa
            </span>

            <strong>
                —
            </strong>

            <small>
                Terdaftar
            </small>

        </div>

    </div>


    <div class="stat-box">

        <div class="stat-box-icon blue">
            ⚠️
        </div>

        <div class="stat-box-content">

            <span>
                Pelanggaran
            </span>

            <strong>
                —
            </strong>

            <small>
                Tercatat
            </small>

        </div>

    </div>


    <div class="stat-box">

        <div class="stat-box-icon purple">
            ⭐
        </div>

        <div class="stat-box-content">

            <span>
                Total Poin
            </span>

            <strong>
                —
            </strong>

            <small>
                Poin siswa
            </small>

        </div>

    </div>


    <div class="stat-box">

        <div class="stat-box-icon orange">
            📋
        </div>

        <div class="stat-box-content">

            <span>
                Laporan
            </span>

            <strong>
                —
            </strong>

            <small>
                Tersedia
            </small>

        </div>

    </div>


</section>


<!-- =====================================================
     PROFILE
====================================================== -->

<section class="profile-card">


    <div class="profile-header">

        <div class="profile-header-icon">
            👤
        </div>

        <div>

            <h2>
                Informasi Akun
            </h2>

            <p>
                Data pengguna yang sedang login
            </p>

        </div>

    </div>


    <div class="profile-grid">


        <div class="profile-item">

            <span>
                Nama Lengkap
            </span>

            <strong>
                <?= htmlspecialchars($nama); ?>
            </strong>

        </div>


        <div class="profile-item">

            <span>
                Email
            </span>

            <strong>
                <?= htmlspecialchars($email); ?>
            </strong>

        </div>


        <div class="profile-item">

            <span>
                Hak Akses
            </span>

            <strong class="role-badge">
                <?= htmlspecialchars($nama_role); ?>
            </strong>

        </div>


    </div>

</section>


<!-- =====================================================
     MENU
====================================================== -->

<section class="dashboard-menu-section">


    <div class="section-heading">

        <div>

            <span>
                AKSES CEPAT
            </span>

            <h2>
                Menu Sistem
            </h2>

        </div>

    </div>


    <div class="dashboard-menu-grid">


        <?php if ($role === 'admin'): ?>


            <a href="data_master.php"
               class="system-card">

                <div class="system-card-icon pink">
                    🗂️
                </div>

                <div class="system-card-content">

                    <h3>
                        Data Master
                    </h3>

                    <p>
                        Kelola guru, siswa, kelas,
                        tahun ajaran dan wali kelas.
                    </p>

                </div>

                <span class="card-arrow">
                    →
                </span>

            </a>


            <a href="pelanggaran.php"
               class="system-card">

                <div class="system-card-icon red">
                    ⚠️
                </div>

                <div class="system-card-content">

                    <h3>
                        Data Pelanggaran
                    </h3>

                    <p>
                        Kelola kategori dan jenis
                        pelanggaran siswa.
                    </p>

                </div>

                <span class="card-arrow">
                    →
                </span>

            </a>


        <?php endif; ?>


        <?php if ($role === 'admin' || $role === 'guru'): ?>


            <a href="catat_pelanggaran.php"
               class="system-card">

                <div class="system-card-icon blue">
                    📝
                </div>

                <div class="system-card-content">

                    <h3>
                        Catat Pelanggaran
                    </h3>

                    <p>
                        Catat pelanggaran yang
                        dilakukan oleh siswa.
                    </p>

                </div>

                <span class="card-arrow">
                    →
                </span>

            </a>


            <a href="tindakan.php"
               class="system-card">

                <div class="system-card-icon orange">
                    ⚖️
                </div>

                <div class="system-card-content">

                    <h3>
                        Tindakan
                    </h3>

                    <p>
                        Kelola tindakan terhadap
                        pelanggaran siswa.
                    </p>

                </div>

                <span class="card-arrow">
                    →
                </span>

            </a>


        <?php endif; ?>


        <a href="laporan.php"
           class="system-card">

            <div class="system-card-icon green">
                📊
            </div>

            <div class="system-card-content">

                <h3>
                    Laporan
                </h3>

                <p>
                    Lihat laporan pelanggaran
                    siswa secara lengkap.
                </p>

            </div>

            <span class="card-arrow">
                →
            </span>

        </a>


        <a href="riwayat_pelanggaran.php"
           class="system-card">

            <div class="system-card-icon purple">
                📋
            </div>

            <div class="system-card-content">

                <h3>
                    Riwayat
                </h3>

                <p>
                    Lihat riwayat pelanggaran
                    siswa.
                </p>

            </div>

            <span class="card-arrow">
                →
            </span>

        </a>


        <a href="rekap_poin.php"
           class="system-card">

            <div class="system-card-icon yellow">
                ⭐
            </div>

            <div class="system-card-content">

                <h3>
                    Rekap Poin
                </h3>

                <p>
                    Lihat total poin pelanggaran
                    setiap siswa.
                </p>

            </div>

            <span class="card-arrow">
                →
            </span>

        </a>


        <?php if ($role === 'admin'): ?>


            <a href="cetak_laporan.php"
               class="system-card">

                <div class="system-card-icon dark">
                    🖨️
                </div>

                <div class="system-card-content">

                    <h3>
                        Cetak / Export
                    </h3>

                    <p>
                        Cetak atau export laporan
                        pelanggaran siswa.
                    </p>

                </div>

                <span class="card-arrow">
                    →
                </span>

            </a>


        <?php endif; ?>


    </div>

</section>


<!-- =====================================================
     FOOTER
====================================================== -->

<footer class="dashboard-footer">

    <strong>
        Sistem Pelanggaran Siswa
    </strong>

    <br>

    <span>
        Dashboard Pengelolaan Pelanggaran Siswa
    </span>

</footer>


</div>

</main>

</div>


</body>

</html>