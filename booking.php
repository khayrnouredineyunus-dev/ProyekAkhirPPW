<?php
$host = 'localhost';
$db   = 'minifut_db'; 
$user = 'root';       
$pass = '';          

// Harga fallback (tetap dipertahankan untuk keamanan validasi awal)
$FIELD_PRICES = [
    '1' => 1000000,
    '2' => 1200000,
    '3' => 1000000
];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Sistem sedang dalam pemeliharaan. Silakan coba beberapa saat lagi.");
}

// === HANDLER API BACK-END DENGAN 5 TABEL ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action'])) {
    header('Content-Type: application/json');
    $data = json_decode(file_get_contents('php://input'), true);
    
    // 1. Ambil jadwal yang sudah dibooking
    if ($_GET['action'] === 'get_booked_slots') {
        $fieldId = $data['field_id'] ?? '';
        $date = $data['date'] ?? '';
        
        if (!$fieldId || !$date) {
            echo json_encode(['booked' => []]);
            exit;
        }

        // QUERY DIUBAH: Mengambil data dari tabel Jadwal berdasarkan status
        $stmt = $pdo->prepare("SELECT JAM_MULAI FROM Jadwal WHERE ID_LAPANGAN = ? AND TANGGAL = ? AND STATUS_JADWAL = 'TIDAK'");
        $stmt->execute([$fieldId, $date]);
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $bookedSlots = [];
        foreach ($bookings as $b) {
            $slots = explode(',', $b['JAM_MULAI']);
            foreach ($slots as $s) {
                $bookedSlots[] = (int)trim($s);
            }
        }
        echo json_encode(['booked' => array_unique($bookedSlots)]);
        exit;
    }

    // 2. Proses Insert Booking ke 5 Tabel
    if ($_GET['action'] === 'submit_booking') {
        try {
            $fieldId = $data['field_id'] ?? '';
            $date = $data['date'] ?? '';
            $reqSlots = $data['slots'] ?? [];
            
            if (!$fieldId || !isset($FIELD_PRICES[$fieldId]) || !$date || empty($reqSlots)) {
                throw new Exception("Data booking tidak tidak valid atau kurang lengkap.");
            }

            // --- CEK RACE CONDITION DI TABEL JADWAL ---
            $stmt = $pdo->prepare("SELECT JAM_MULAI FROM Jadwal WHERE ID_LAPANGAN = ? AND TANGGAL = ? AND STATUS_JADWAL = 'TIDAK'");
            $stmt->execute([$fieldId, $date]);
            $existingBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $bookedSlots = [];
            foreach ($existingBookings as $b) {
                $slots = explode(',', $b['JAM_MULAI']);
                foreach ($slots as $s) {
                    $bookedSlots[] = (int)trim($s);
                }
            }

            foreach ($reqSlots as $s) {
                if (in_array((int)$s, $bookedSlots)) {
                    throw new Exception("Maaf, slot jam " . sprintf("%02d:00", $s) . " baru saja dipesan oleh orang lain.");
                }
            }

            // Kalkulasi Harga
            $pricePerHour = $FIELD_PRICES[$fieldId];
            $actualTotalPrice = $pricePerHour * count($reqSlots);
            
            $code = 'MF-' . strtoupper(substr(md5(uniqid('', true)), 0, 6)); 

            // ==========================================
            // MEMULAI TRANSAKSI UNTUK 5 TABEL
            // ==========================================
            $pdo->beginTransaction();

            // 1. TABEL PELANGGAN
            $email = htmlspecialchars($data['email'] ?? '');
            $nama = htmlspecialchars($data['name'] ?? '');
            $notelp = htmlspecialchars($data['phone'] ?? '');

            // Cek apakah pelanggan sudah pernah booking
            $stmt = $pdo->prepare("SELECT ID_PELANGGAN FROM Pelanggan WHERE U_EMAIL = ?");
            $stmt->execute([$email]);
            $pelanggan = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($pelanggan) {
                $id_pelanggan = $pelanggan['ID_PELANGGAN'];
            } else {
                // Buat password dummy karena fitur belum butuh login password
                $dummyPassword = password_hash('password123', PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO Pelanggan (U_NAMA, U_EMAIL, U_PASSWORD, U_NOTELP) VALUES (?, ?, ?, ?)");
                $stmt->execute([$nama, $email, $dummyPassword, $notelp]);
                $id_pelanggan = $pdo->lastInsertId();
            }

            // 2. TABEL JADWAL
            $slotsStr = implode(',', $reqSlots);
            // Kalkulasi jam selesai (cth: jam 10 -> selesai 11)
            $endSlotsArr = array_map(function($val) { return $val + 1; }, $reqSlots);
            $endSlotsStr = implode(',', $endSlotsArr);

            $stmt = $pdo->prepare("INSERT INTO Jadwal (ID_LAPANGAN, TANGGAL, JAM_MULAI, JAM_SELESAI, STATUS_JADWAL) VALUES (?, ?, ?, ?, 'TIDAK')");
            $stmt->execute([$fieldId, $date, $slotsStr, $endSlotsStr]);
            $id_jadwal = $pdo->lastInsertId();

            // 3. TABEL BOOKING
            $payType = in_array($data['pay_type'], ['dp', 'lunas']) ? strtoupper($data['pay_type']) : 'LUNAS';

            $stmt = $pdo->prepare("INSERT INTO Booking (ID_BOOKING, ID_PELANGGAN, TANGGAL_BOOKING, STATUS_BOOKING, ID_JADWAL) VALUES (?, ?, NOW(), ?, ?)");
            $stmt->execute([$code, $id_pelanggan, $payType, $id_jadwal]);

            // 4. TABEL PEMBAYARAN
            // Kita set awal metode sebagai TRANSFER dan status PENDING menunggu konfirmasi admin
            $stmt = $pdo->prepare("INSERT INTO Pembayaran (ID_BOOKING, TANGGAL_BAYAR, METODE_PEMBAYARAN, STATUS_PEMBAYARAN) VALUES (?, NOW(), 'TRANSFER', 'PENDING')");
            $stmt->execute([$code]);

            // Catatan: Kolom 'team_name' dan 'notes' dari frontend diabaikan karena tidak ada di ERD.
            // Jika butuh disimpan, ERD harus direvisi dengan menambahkan kolom tersebut di tabel Booking.

            $pdo->commit();
            // ==========================================
            
            echo json_encode([
                'success' => true, 
                'code' => $code,
                'actual_price' => $actualTotalPrice 
            ]);

        } catch(Exception $e) {
            // Jika ada error/gagal di tengah proses, batalkan semua insert ke 5 tabel
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MiniFut — Book Arena</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@300;400;500;600;700&family=Barlow:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
<style>
:root{
  --black:#060608;--dark:#0c0d10;--card:#111318;--card2:#161820;
  --border:rgba(255,255,255,.06);--border2:rgba(255,255,255,.1);
  --green:#00ff88;--green2:#00e676;--glow:rgba(0,255,136,.18);--glow-sm:rgba(0,255,136,.07);
  --gray:#6b7080;--gray2:#9aa0b0;--white:#eceef2;
  --red:#ff3b5c;--amber:#ffb600;
}
*{margin:0;padding:0;box-sizing:border-box;}
html{scroll-behavior:smooth;}
body{background:var(--black);color:var(--white);font-family:'Barlow',sans-serif;cursor:none;overflow-x:hidden;min-height:100vh;}
@media (hover: none) and (pointer: coarse) {
  #cur, #cur-r { display: none !important; }
  body, a, button, .fc, .cal-day.available, .time-slot.avail, label, .step-item, .nav-back { cursor: auto !important; }
}
#cur{position:fixed;width:10px;height:10px;background:var(--green);border-radius:50%;pointer-events:none;z-index:9999;transform:translate(-50%,-50%);}
#cur-r{position:fixed;width:34px;height:34px;border:1px solid var(--green);border-radius:50%;pointer-events:none;z-index:9998;transform:translate(-50%,-50%);opacity:.4;transition:all .13s ease;}
#noise{position:fixed;inset:0;opacity:.015;pointer-events:none;z-index:8000;
  background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='300' height='300'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.75' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='300' height='300' filter='url(%23n)'/%3E%3C/svg%3E");}
nav{position:fixed;top:0;left:0;right:0;z-index:1000;padding:18px 64px;display:flex;align-items:center;justify-content:space-between;background:rgba(6,6,8,.9);backdrop-filter:blur(24px);border-bottom:1px solid rgba(0,255,136,.08);}
.logo{font-family:'Orbitron',monospace;font-size:1.5rem;font-weight:900;color:var(--green);letter-spacing:5px;text-decoration:none;}
.logo em{color:var(--white);font-style:normal;}
.nav-back{font-family:'Rajdhani',sans-serif;font-size:.75rem;font-weight:600;letter-spacing:2px;text-transform:uppercase;color:var(--gray2);text-decoration:none;display:flex;align-items:center;gap:8px;transition:color .25s;cursor:none;}
.nav-back:hover{color:var(--green);}
.nav-step-indicator{font-family:'Rajdhani',sans-serif;font-size:.7rem;letter-spacing:2px;color:var(--gray);text-transform:uppercase;}
.bg-grid{position:fixed;inset:0;pointer-events:none;z-index:0;
  background-image:linear-gradient(rgba(0,255,136,.025) 1px,transparent 1px),linear-gradient(90deg,rgba(0,255,136,.025) 1px,transparent 1px);
  background-size:52px 52px;}
.booking-wrap{position:relative;z-index:1;padding-top:74px;min-height:100vh;display:grid;grid-template-columns:1fr 380px;max-width:1340px;margin:0 auto;gap:0;}
.booking-main{padding:40px 40px 80px 64px;border-right:1px solid var(--border);}
.booking-sidebar{padding:40px 40px 80px 40px;position:sticky;top:74px;height:calc(100vh - 74px);overflow-y:auto;}
.booking-sidebar::-webkit-scrollbar{width:3px;}
.booking-sidebar::-webkit-scrollbar-thumb{background:rgba(0,255,136,.2);}
@media(max-width:900px){
  .booking-wrap{grid-template-columns:1fr;}
  .booking-main{padding:20px; border-right:none;}
  .booking-sidebar{position:static;height:auto;}
}
.steps-bar{display:flex;gap:0;margin-bottom:44px;}
.step-item{display:flex;align-items:center;gap:0;flex:1;}
.step-num{width:32px;height:32px;border:1px solid var(--border2);display:flex;align-items:center;justify-content:center;font-family:'Orbitron',monospace;font-size:.72rem;font-weight:700;color:var(--gray);transition:all .35s;flex-shrink:0;}
.step-num.done{background:var(--green);border-color:var(--green);color:var(--black);}
.step-num.active{border-color:var(--green);color:var(--green);box-shadow:0 0 14px rgba(0,255,136,.3);}
.step-label{font-family:'Rajdhani',sans-serif;font-size:.68rem;font-weight:600;letter-spacing:2px;text-transform:uppercase;color:var(--gray);margin-left:10px;transition:color .35s;white-space:nowrap;}
.step-label.active{color:var(--white);}
.step-connector{flex:1;height:1px;background:var(--border);margin:0 14px;transition:background .35s;}
.step-connector.done{background:var(--green);}
.sec-label{font-family:'Rajdhani',sans-serif;font-size:.65rem;font-weight:700;letter-spacing:5px;text-transform:uppercase;color:var(--green);margin-bottom:8px;}
.sec-title{font-family:'Orbitron',monospace;font-size:1.6rem;font-weight:700;color:var(--white);margin-bottom:28px;}
#step1{display:block;}
#step2,#step3,#step4{display:none;}
.field-cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:3px;margin-bottom:10px;}
.fc{background:var(--card);border:1px solid var(--border);cursor:none;transition:all .3s;position:relative;overflow:hidden;}
.fc:hover{border-color:rgba(0,255,136,.3);}
.fc.selected{border-color:var(--green);background:rgba(0,255,136,.04);}
.fc.selected::before{content:'';position:absolute;inset:0;border:2px solid var(--green);pointer-events:none;z-index:3;}
.fc-img{height:160px;overflow:hidden;position:relative;}
.fc-img img{width:100%;height:100%;object-fit:cover;filter:brightness(.6) saturate(.65);transition:transform .55s,filter .55s;}
.fc:hover .fc-img img,.fc.selected .fc-img img{transform:scale(1.05);filter:brightness(.55) saturate(1);}
.fc-img-ov{position:absolute;inset:0;background:linear-gradient(to top,rgba(6,6,8,.9) 0%,transparent 55%);}
.fc-sel-mark{position:absolute;top:10px;right:10px;z-index:2;width:22px;height:22px;border:1px solid rgba(0,255,136,.3);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.75rem;color:var(--gray);transition:all .3s;}
.fc.selected .fc-sel-mark{background:var(--green);border-color:var(--green);color:var(--black);}
.fc-body{padding:16px;}
.fc-name{font-family:'Orbitron',monospace;font-size:.95rem;font-weight:700;color:var(--white);margin-bottom:10px;}
.fc-attrs{display:flex;flex-wrap:wrap;gap:5px;margin-bottom:12px;}
.fc-attr{font-family:'Rajdhani',sans-serif;font-size:.6rem;letter-spacing:1px;color:var(--gray2);background:rgba(255,255,255,.04);border:1px solid var(--border);padding:2px 7px;}
.fc-price{font-family:'Orbitron',monospace;font-size:1rem;font-weight:700;color:var(--green);}
.fc-price-unit{font-family:'Rajdhani',sans-serif;font-size:.68rem;color:var(--gray);margin-left:3px;}
.field-detail{background:var(--card2);border:1px solid rgba(0,255,136,.2);padding:24px;margin-top:3px;display:none;}
.field-detail.open{display:block;}
.fd-layout{display:grid;grid-template-columns:1fr 1fr;gap:24px;}
@media(max-width:768px){ .fd-layout{grid-template-columns:1fr;} }
.fd-imgs{display:grid;grid-template-columns:1fr 1fr;gap:3px;}
.fd-imgs img{width:100%;height:100px;object-fit:cover;filter:brightness(.7) saturate(.7);transition:filter .3s;cursor:none;}
.fd-imgs img:hover{filter:brightness(.9) saturate(1);}
.fd-info-list{display:flex;flex-direction:column;gap:10px;}
.fd-info-row{display:flex;justify-content:space-between;padding-bottom:10px;border-bottom:1px solid var(--border);}
.fd-info-row:last-child{border-bottom:none;}
.fd-key{font-family:'Rajdhani',sans-serif;font-size:.72rem;letter-spacing:1px;color:var(--gray);}
.fd-val{font-family:'Rajdhani',sans-serif;font-size:.78rem;font-weight:600;color:var(--white);}
.calendar-wrap{margin-bottom:28px;}
.cal-nav{display:flex;justify-content:space-between;align-items:center;margin-bottom:22px;}
.cal-month{font-family:'Orbitron',monospace;font-size:1rem;font-weight:700;color:var(--white);}
.cal-btn{width:34px;height:34px;border:1px solid var(--border2);background:none;color:var(--gray2);cursor:none;display:flex;align-items:center;justify-content:center;transition:all .25s;font-size:.9rem;}
.cal-btn:hover{border-color:var(--green);color:var(--green);}
.cal-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:2px;}
.cal-day-name{font-family:'Rajdhani',sans-serif;font-size:.65rem;font-weight:700;letter-spacing:2px;text-align:center;padding:8px 0;color:var(--gray);text-transform:uppercase;}
.cal-day{aspect-ratio:1;display:flex;align-items:center;justify-content:center;font-family:'Rajdhani',sans-serif;font-size:.88rem;font-weight:600;color:var(--gray2);cursor:none;border:1px solid transparent;transition:all .25s;position:relative;}
.cal-day.empty{background:transparent;cursor:default;}
.cal-day.past{color:var(--gray);opacity:.35;pointer-events:none;}
.cal-day.today::after{content:'';position:absolute;bottom:4px;left:50%;transform:translateX(-50%);width:4px;height:4px;border-radius:50%;background:var(--gray);}
.cal-day.available:hover{border-color:rgba(0,255,136,.4);color:var(--green);background:var(--glow-sm);}
.cal-day.selected{background:var(--green);color:var(--black);font-weight:700;border-color:var(--green);}
.cal-day.selected::after{display:none;}
.time-legend{display:flex;gap:20px;margin-bottom:20px;}
.tleg-item{display:flex;align-items:center;gap:7px;font-family:'Rajdhani',sans-serif;font-size:.7rem;letter-spacing:1.5px;text-transform:uppercase;color:var(--gray2);}
.tleg-dot{width:12px;height:12px;border:1px solid;}
.tleg-dot.avail{border-color:rgba(0,255,136,.5);background:rgba(0,255,136,.07);}
.tleg-dot.booked{border-color:var(--border2);background:rgba(255,255,255,.06);}
.tleg-dot.sel{background:var(--green);border-color:var(--green);}
.time-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:3px;}
.time-slot{padding:14px 10px;background:var(--card);border:1px solid var(--border);cursor:none;text-align:center;transition:all .25s;position:relative;overflow:hidden;}
.time-slot.avail:hover{border-color:rgba(0,255,136,.4);background:var(--glow-sm);}
.time-slot.sel{background:rgba(0,255,136,.08);border-color:var(--green);}
.time-slot.booked{opacity:.4;pointer-events:none;}
.time-slot.booked::after{content:'BOOKED';position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-family:'Rajdhani',sans-serif;font-size:.6rem;font-weight:700;letter-spacing:2px;color:var(--gray);background:rgba(6,6,8,.6);}
.ts-time{font-family:'Orbitron',monospace;font-size:.82rem;font-weight:700;color:var(--white);margin-bottom:4px;}
.time-slot.sel .ts-time{color:var(--green);}
.ts-price{font-family:'Rajdhani',sans-serif;font-size:.65rem;color:var(--gray2);}
.ts-check{position:absolute;top:6px;right:8px;font-size:.7rem;color:var(--green);opacity:0;transition:opacity .2s;}
.time-slot.sel .ts-check{opacity:1;}
.multi-note{font-family:'Rajdhani',sans-serif;font-size:.75rem;letter-spacing:1px;color:var(--gray2);margin-top:16px;line-height:1.6;}
.multi-note span{color:var(--green); font-weight:700;}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
.form-group{display:flex;flex-direction:column;gap:6px;}
.form-group.full{grid-column:1/-1;}
.form-label{font-family:'Rajdhani',sans-serif;font-size:.68rem;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;color:var(--green);}
.form-input{background:var(--card);border:1px solid var(--border2);color:var(--white);font-family:'Barlow',sans-serif;font-size:.9rem;padding:12px 16px;outline:none;transition:border-color .25s;cursor:text;}
.form-input::placeholder{color:var(--gray);}
.form-input:focus{border-color:var(--green);}
.form-note{font-family:'Barlow',sans-serif;font-size:.78rem;color:var(--gray);margin-top:4px;font-style:italic;}
.sidebar-title{font-family:'Orbitron',monospace;font-size:1rem;font-weight:700;color:var(--white);margin-bottom:22px;display:flex;align-items:center;gap:10px;}
.sidebar-title::after{content:'';flex:1;height:1px;background:var(--border);}
.summary-block{background:var(--card2);border:1px solid var(--border);padding:20px;margin-bottom:3px;}
.sum-field-img{height:110px;overflow:hidden;position:relative;margin-bottom:16px;}
.sum-field-img img{width:100%;height:100%;object-fit:cover;filter:brightness(.65);}
.sum-field-img-ov{position:absolute;inset:0;background:linear-gradient(to top,rgba(6,6,8,.8) 0%,transparent 60%);}
.sum-field-name{position:absolute;bottom:10px;left:12px;font-family:'Orbitron',monospace;font-size:.88rem;font-weight:700;color:var(--white);}
.sum-row{display:flex;justify-content:space-between;margin-bottom:10px;align-items:flex-start;}
.sum-key{font-family:'Rajdhani',sans-serif;font-size:.72rem;letter-spacing:1.5px;color:var(--gray);text-transform:uppercase;}
.sum-val{font-family:'Rajdhani',sans-serif;font-size:.78rem;font-weight:600;color:var(--white);text-align:right;}
.sum-val.green{color:var(--green);}
.sum-divider{height:1px;background:var(--border);margin:14px 0;}
.sum-total-key{font-family:'Rajdhani',sans-serif;font-size:.72rem;letter-spacing:2px;text-transform:uppercase;color:var(--gray2);}
.sum-total-val{font-family:'Orbitron',monospace;font-size:1.3rem;font-weight:700;color:var(--green);}
.sum-empty{text-align:center;padding:30px 10px;font-family:'Rajdhani',sans-serif;font-size:.75rem;letter-spacing:1.5px;color:var(--gray);text-transform:uppercase;}
.action-row{display:flex;gap:8px;margin-top:28px;}
.btn-next{flex:1;font-family:'Rajdhani',sans-serif;font-size:.82rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--black);background:var(--green);border:none;padding:15px;cursor:none;clip-path:polygon(8px 0%,100% 0%,calc(100% - 8px) 100%,0 100%);transition:all .3s;position:relative;overflow:hidden;}
.btn-next::after{content:'';position:absolute;inset:0;background:rgba(255,255,255,.18);transform:translateX(-100%);transition:.3s;}
.btn-next:hover::after{transform:translateX(0);}
.btn-next:disabled{background:var(--border2);color:var(--gray);cursor:default;pointer-events:none;clip-path:none;}
.btn-back{font-family:'Rajdhani',sans-serif;font-size:.82rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--gray2);background:none;border:1px solid var(--border2);padding:15px 20px;cursor:none;transition:all .3s;}
.btn-back:hover{border-color:var(--green);color:var(--green);}
#success-overlay{display:none;position:fixed;inset:0;z-index:5000;background:var(--black);align-items:center;justify-content:center;flex-direction:column;padding:20px;overflow-y:auto;}
#success-overlay.show{display:flex;}
.success-card{background:var(--card);border:1px solid rgba(0,255,136,.35);text-align:center;max-width:480px;position:relative;width:100%;margin:auto;display:flex;flex-direction:column;max-height:90vh;}
.success-card-content{padding:24px 32px;overflow-y:auto;flex:1;min-height:0;}
.success-title{font-family:'Orbitron',monospace;font-size:1.2rem;font-weight:900;color:var(--green);margin-bottom:6px;}
.success-sub{font-family:'Barlow',sans-serif;font-size:.85rem;color:var(--gray2);line-height:1.4;margin-bottom:16px;}
.success-detail{background:var(--card2);padding:14px;text-align:left;margin-bottom:16px;}
.success-detail-row{display:flex;justify-content:space-between;margin-bottom:6px;}
.success-detail-row:last-child{margin-bottom:0;}
.sdk{font-family:'Rajdhani',sans-serif;font-size:.7rem;letter-spacing:2px;text-transform:uppercase;color:var(--gray);}
.sdv{font-family:'Rajdhani',sans-serif;font-size:.8rem;font-weight:600;color:var(--white);}
.success-code{font-family:'Orbitron',monospace;font-size:.75rem;letter-spacing:3px;color:var(--green);background:rgba(0,255,136,.07);border:1px solid rgba(0,255,136,.2);padding:6px 14px;display:inline-block;margin-bottom:16px;}
.success-corner{position:absolute;width:20px;height:20px;border-color:var(--green);border-style:solid;}
.success-corner.tl{top:-1px;left:-1px;border-width:2px 0 0 2px;}
.success-corner.tr{top:-1px;right:-1px;border-width:2px 2px 0 0;}
.success-corner.bl{bottom:-1px;left:-1px;border-width:0 0 2px 2px;}
.success-corner.br{bottom:-1px;right:-1px;border-width:0 2px 2px 0;}
@keyframes pulse-green{0%,100%{box-shadow:0 0 0 0 rgba(0,255,136,.4)}50%{box-shadow:0 0 0 8px rgba(0,255,136,0)}}
.btn-next:not(:disabled){animation:pulse-green 2.5s infinite;}
::-webkit-scrollbar{width:3px;height:3px;}
::-webkit-scrollbar-thumb{background:rgba(0,255,136,.15);}

/* Reactive Grid & 3D Tilt */
.bg-grid {
  transition: opacity 0.3s;
  -webkit-mask-image: radial-gradient(circle at var(--mouse-x, 50%) var(--mouse-y, 50%), black 0%, transparent 20%);
  mask-image: radial-gradient(circle at var(--mouse-x, 50%) var(--mouse-y, 50%), black 0%, transparent 20%);
  opacity: 0.7;
}

.fc {
  transform-style: preserve-3d;
  will-change: transform;
  transition: transform 0.4s ease-out, border-color 0.3s;
}

/* Efek Glare  */
.fc::after {
  content: '';
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at var(--x, 50%) var(--y, 50%), rgba(255,255,255,0.1) 0%, transparent 50%);
  opacity: 0;
  transition: opacity 0.3s;
  pointer-events: none;
  z-index: 5;
}
.fc:hover::after { opacity: 1; }
</style>

/* INI CONTOH REFRENSI STRUKTUR HTML UNTUK HALAMAN BOOKING, SILAHKAN SESUAIKAN DENGAN LOGIKA FRONTEND YANG AKAN DIBUAT */
</head>
<body>
<div id="noise"></div>
<div id="cur"></div>
<div id="cur-r"></div>
<div class="bg-grid"></div>

<div id="success-overlay">
  <div class="success-card">
    <div class="success-corner tl"></div>
    <div class="success-corner tr"></div>
    <div class="success-corner bl"></div>
    <div class="success-corner br"></div>
    
    <div class="success-card-content">
      <h2 class="success-title">BOOKING MENUNGGU PEMBAYARAN!</h2>
      <p class="success-sub">Selesaikan pembayaran manual via transfer bank. Setelah transfer, kirimkan bukti ke WhatsApp kami.</p>
      <div class="success-code" id="booking-code">MF-000000</div>
      <div class="success-detail" id="success-detail"></div>
      
      <div style="background:var(--card2); padding:14px; text-align:left; margin-bottom:20px; border:1px solid rgba(255,182,0,.3);">
        <div style="font-family:'Rajdhani',sans-serif;font-size:.75rem;letter-spacing:2px;text-transform:uppercase;color:var(--amber);margin-bottom:12px;font-weight:700;">Instruksi Pembayaran</div>
        <div style="margin-bottom:10px;">
          <span style="font-family:'Barlow',sans-serif;font-size:.8rem;color:var(--gray);">Bank Tujuan:</span><br>
          <strong style="font-family:'Orbitron',monospace;font-size:1.1rem;color:var(--white);">BCA - 1234567890</strong><br>
          <span style="font-family:'Barlow',sans-serif;font-size:.8rem;color:var(--gray);">a.n. PT MiniFut Indonesia</span>
        </div>
        <div style="margin-bottom:10px;">
          <span style="font-family:'Barlow',sans-serif;font-size:.8rem;color:var(--gray);">Jumlah Transfer:</span><br>
          <strong style="font-family:'Orbitron',monospace;font-size:1.3rem;color:var(--green);" id="transfer-amount">Rp 0</strong>
        </div>
        <div id="sisa-bayar-info" style="display:none;font-family:'Barlow',sans-serif;font-size:.8rem;color:var(--gray2);margin-bottom:12px; font-style:italic;">
          *Sisa pembayaran <span id="sisa-amount" style="color:var(--amber);font-weight:bold;">Rp 0</span> dilunasi di lokasi (Cash/QRIS).
        </div>
        <div style="border-top:1px solid var(--border); padding-top:12px; margin-top:12px;">
          <span style="font-family:'Barlow',sans-serif;font-size:.8rem;color:var(--gray);">Konfirmasi via WhatsApp (Kirim Bukti Transfer & Kode Booking):</span><br>
          <strong style="font-family:'Orbitron',monospace;font-size:1rem;color:var(--white);">+62 812-3456-7890</strong>
        </div>
      </div>
      <div id="payment-timer-box" style="background:var(--card2); padding:18px; text-align:center; margin-bottom:28px; border:1px solid var(--red);">
        <div style="font-family:'Rajdhani',sans-serif;font-size:.8rem;letter-spacing:1px;text-transform:uppercase;color:var(--gray);margin-bottom:8px;">Batas Waktu Pembayaran</div>
        <div id="payment-timer" style="font-family:'Orbitron',monospace;font-size:2rem;font-weight:700;color:var(--red);margin-bottom:8px;text-shadow:0 0 10px rgba(255,59,92,.4);">05:00</div>
        <div id="payment-timer-msg" style="font-family:'Barlow',sans-serif;font-size:.8rem;color:var(--gray2);">Jika dalam 5 menit belum melakukan pembayaran, booking otomatis dibatalkan.</div>
      </div>

      <div style="display:flex;gap:8px;justify-content:center">
        <button onclick="window.location.reload()" style="font-family:'Rajdhani',sans-serif;font-size:.78rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--black);background:var(--green);border:none;padding:12px 24px;cursor:none;">Selesai & Tutup</button>
      </div>
    </div> </div>
</div>

<nav>
  <a href="index.php" class="logo">MINI<em>FUT</em></a>
  <div class="nav-step-indicator" id="stepIndicator">STEP 1 / 4 — PILIH LAPANGAN</div>
  <a href="index.php" class="nav-back">← Kembali ke Beranda</a>
</nav>

<div class="booking-wrap">
  <div class="booking-main">

    <div class="steps-bar">
      <div class="step-item" onclick="goStep(1)">
        <div class="step-num active" id="sn1">1</div>
        <span class="step-label active" id="sl1">Pilih Lapangan</span>
      </div>
      <div class="step-connector" id="sc1"></div>
      <div class="step-item" onclick="goStep(2)">
        <div class="step-num" id="sn2">2</div>
        <span class="step-label" id="sl2">Pilih Tanggal</span>
      </div>
      <div class="step-connector" id="sc2"></div>
      <div class="step-item" onclick="goStep(3)">
        <div class="step-num" id="sn3">3</div>
        <span class="step-label" id="sl3">Pilih Jam</span>
      </div>
      <div class="step-connector" id="sc3"></div>
      <div class="step-item" onclick="goStep(4)">
        <div class="step-num" id="sn4">4</div>
        <span class="step-label" id="sl4">Data Diri</span>
      </div>
    </div>

    <div id="step1">
      <div class="sec-label">⬡ Step 01</div>
      <div class="sec-title">PILIH LAPANGAN</div>

      <div class="field-cards">
        <div class="fc" id="fc-1" onclick="selectField('1')">
          <div class="fc-sel-mark">✓</div>
          <div class="fc-img">
            <img src="https://images.unsplash.com/photo-1575361204480-aadea25e6e68?w=600&q=80" alt="Lapangan 1" loading="lazy">
            <div class="fc-img-ov"></div>
          </div>
          <div class="fc-body">
            <div class="fc-name">LAPANGAN 1</div>
            <div class="fc-attrs">
              <span class="fc-attr">16-20 Org</span>
              <span class="fc-attr">Sintetis Pro</span>
              <span class="fc-attr">Outdoor</span>
            </div>
            <div><span class="fc-price">Rp 1.000.000</span><span class="fc-price-unit">/jam</span></div>
          </div>
        </div>
        <div class="fc" id="fc-2" onclick="selectField('2')">
          <div class="fc-sel-mark">✓</div>
          <div class="fc-img">
            <img src="https://images.unsplash.com/photo-1529900748604-07564a03e7a6?w=600&q=80" alt="Lapangan 2" loading="lazy">
            <div class="fc-img-ov"></div>
          </div>
          <div class="fc-body">
            <div class="fc-name">LAPANGAN 2</div>
            <div class="fc-attrs">
              <span class="fc-attr">16-20 Org</span>
              <span class="fc-attr">Sintetis Premium</span>
              <span class="fc-attr">Tribun</span>
            </div>
            <div><span class="fc-price">Rp 1.200.000</span><span class="fc-price-unit">/jam</span></div>
          </div>
        </div>
        <div class="fc" id="fc-3" onclick="selectField('3')">
          <div class="fc-sel-mark">✓</div>
          <div class="fc-img">
            <img src="https://images.unsplash.com/photo-1518604666860-9ed391f76460?w=600&q=80" alt="Lapangan 3" loading="lazy">
            <div class="fc-img-ov"></div>
          </div>
          <div class="fc-body">
            <div class="fc-name">LAPANGAN 3</div>
            <div class="fc-attrs">
              <span class="fc-attr">16-20 Org</span>
              <span class="fc-attr">Sintetis Elite</span>
              <span class="fc-attr">Outdoor</span>
            </div>
            <div><span class="fc-price">Rp 1.000.000</span><span class="fc-price-unit">/jam</span></div>
          </div>
        </div>
      </div>

      <div class="field-detail" id="field-detail">
        <div class="fd-layout">
          <div class="fd-imgs" id="fd-imgs"></div>
          <div class="fd-info-list" id="fd-info"></div>
        </div>
      </div>

      <div class="action-row">
        <button class="btn-next" id="btn1" onclick="goStep(2)" disabled>Lanjut: Pilih Tanggal →</button>
      </div>
    </div>

    <div id="step2">
      <div class="sec-label">⬡ Step 02</div>
      <div class="sec-title">PILIH TANGGAL</div>
      <div class="calendar-wrap">
        <div class="cal-nav">
          <button class="cal-btn" id="prevMonth" onclick="changeMonth(-1)">‹</button>
          <div class="cal-month" id="calMonth"></div>
          <button class="cal-btn" id="nextMonth" onclick="changeMonth(1)">›</button>
        </div>
        <div class="cal-grid" id="calGrid"></div>
      </div>
      <div class="action-row">
        <button class="btn-back" onclick="goStep(1)">← Kembali</button>
        <button class="btn-next" id="btn2" onclick="goStep(3)" disabled>Lanjut: Pilih Jam →</button>
      </div>
    </div>

    <div id="step3">
      <div class="sec-label">⬡ Step 03</div>
      <div class="sec-title">PILIH JAM SESI</div>

      <div class="time-legend">
        <div class="tleg-item"><div class="tleg-dot avail"></div>Tersedia</div>
        <div class="tleg-item"><div class="tleg-dot sel"></div>Dipilih</div>
        <div class="tleg-item"><div class="tleg-dot booked"></div>Sudah Dipesan</div>
      </div>

      <p class="multi-note" style="margin-bottom:16px;">Anda dapat memilih <span>lebih dari satu jam</span> dengan langsung mengklik kotak-kotak yang tersedia secara bebas.</p>

      <div class="time-grid" id="timeGrid"></div>

      <div class="action-row" style="margin-top:20px;">
        <button class="btn-back" onclick="goStep(2)">← Kembali</button>
        <button class="btn-next" id="btn3" onclick="goStep(4)" disabled>Lanjut: Data Diri →</button>
      </div>
    </div>

    <div id="step4">
      <div class="sec-label">⬡ Step 04</div>
      <div class="sec-title">DATA PEMESANAN</div>

      <form id="bookForm" onsubmit="return false;">
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Nama Lengkap *</label>
            <input type="text" class="form-input" id="f-nama" placeholder="Masukkan nama lengkap">
          </div>
          <div class="form-group">
            <label class="form-label">No. Telepon *</label>
            <input type="tel" class="form-input" id="f-telp" placeholder="+62 8xx-xxxx-xxxx">
          </div>
          <div class="form-group full">
            <label class="form-label">Email *</label>
            <input type="email" class="form-input" id="f-email" placeholder="email@kamu.com">
            <p class="form-note">Konfirmasi booking akan dikirim ke email ini.</p>
          </div>
          <div class="form-group full">
            <label class="form-label">Nama Tim / Komunitas</label>
            <input type="text" class="form-input" id="f-tim" placeholder="Opsional — nama tim kamu">
          </div>
          
          <div class="form-group full" style="margin-top:8px;">
            <label class="form-label">Tipe Pembayaran *</label>
            <div style="display:flex; gap:20px; margin-top:6px;">
              <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                <input type="radio" name="pay_type" value="lunas" checked style="accent-color:var(--green);width:16px;height:16px;" onclick="updatePaymentUI()" onchange="updatePaymentUI()">
                <span style="font-family:'Barlow',sans-serif;font-size:.9rem;">Bayar Lunas</span>
              </label>
              <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                <input type="radio" name="pay_type" value="dp" style="accent-color:var(--green);width:16px;height:16px;" onclick="updatePaymentUI()" onchange="updatePaymentUI()">
                <span style="font-family:'Barlow',sans-serif;font-size:.9rem;">DP 50%</span>
              </label>
            </div>
            <p class="form-note" id="dp-note" style="display:none; color:var(--amber); margin-top:8px;">*Sisa pembayaran wajib dilunasi di lokasi sebelum bermain.</p>
          </div>

          <div class="form-group full" style="margin-top:8px;">
            <label class="form-label">Catatan Tambahan</label>
            <textarea class="form-input" id="f-catatan" rows="3" placeholder="Permintaan khusus, keperluan tambahan, dll..." style="resize:vertical;"></textarea>
          </div>
          <div class="form-group full">
            <label class="form-label" style="display:flex;align-items:center;gap:10px;cursor:none;">
              <input type="checkbox" id="f-agree" style="accent-color:var(--green);width:14px;height:14px;">
              <span style="font-weight:400;letter-spacing:1px;font-size:.72rem;color:var(--gray2);line-height:1.4;">Saya menyetujui syarat & ketentuan MiniFut dan bersedia menjaga fasilitas, kebersihan, serta menaati aturan jam bermain.</span>
            </label>
          </div>
        </div>
      </form>

      <div class="action-row">
        <button class="btn-back" onclick="goStep(3)">← Kembali</button>
        <button class="btn-next" id="btn4" onclick="submitBooking()" disabled>Konfirmasi Booking ✓</button>
      </div>
    </div>

  </div>

  <div class="booking-sidebar">
    <div class="sidebar-title">Ringkasan</div>
    <div class="summary-block" id="sum-field-block">
      <div class="sum-empty">Belum ada lapangan dipilih</div>
    </div>
    <div class="summary-block" id="sum-dt-block" style="display:none">
      <div class="sum-row"><span class="sum-key">Tanggal</span><span class="sum-val" id="sum-date">—</span></div>
      <div class="sum-row"><span class="sum-key">Jam Sesi</span><span class="sum-val green" id="sum-time" style="font-family:'Orbitron',monospace; text-align:right;">—</span></div>
      <div class="sum-row"><span class="sum-key">Durasi Total</span><span class="sum-val" id="sum-dur">—</span></div>
    </div>
    <div class="summary-block" id="sum-total-block" style="display:none">
      <div class="sum-row"><span class="sum-key">Harga per Jam</span><span class="sum-val" id="sum-perjam">—</span></div>
      <div class="sum-row" id="row-subtotal" style="display:none; margin-top:8px;"><span class="sum-key">Total Harga</span><span class="sum-val" id="sum-subtotal">—</span></div>
      
      <div class="sum-row" id="row-dp" style="display:none; margin-top:8px;"><span class="sum-key">DP (50%)</span><span class="sum-val" id="sum-dp-val" style="color:var(--amber);">—</span></div>
      <div class="sum-row" id="row-sisa" style="display:none;"><span class="sum-key">Sisa di Lokasi</span><span class="sum-val" id="sum-sisa-val">—</span></div>
      <div class="sum-divider"></div>
      <div class="sum-row">
        <span class="sum-total-key" id="sum-total-label" style="color:var(--white); font-weight:700;">TOTAL BIAYA</span>
        <span class="sum-total-val" id="sum-total">—</span>
      </div>
    </div>
    <div style="margin-top:16px;padding:16px;border:1px solid rgba(0,255,136,.15);background:rgba(0,255,136,.03);">
      <div style="font-family:'Rajdhani',sans-serif;font-size:.65rem;letter-spacing:2px;text-transform:uppercase;color:var(--green);margin-bottom:10px;">Info Lapangan</div>
      <ul style="list-style:none;display:flex;flex-direction:column;gap:8px;">
        <li style="font-family:'Barlow',sans-serif;font-size:.78rem;color:var(--gray2);line-height:1.5;">• Pelunasan bisa dilakukan di lokasi</li>
        <li style="font-family:'Barlow',sans-serif;font-size:.78rem;color:var(--gray2);line-height:1.5;">• Harap hadir 15 menit sebelum sesi dimulai</li>
        <li style="font-family:'Barlow',sans-serif;font-size:.78rem;color:var(--gray2);line-height:1.5;">• Dilarang membawa makanan dari luar</li>
        <li style="font-family:'Barlow',sans-serif;font-size:.78rem;color:var(--gray2);line-height:1.5;">• Tersedia area Food Stand dan Ruang Ganti</li>
      </ul>
    </div>
    <div style="margin-top:8px;padding:14px 16px;border:1px solid var(--border);background:var(--card2);display:flex;align-items:center;gap:12px;">
      <div>
        <div style="font-family:'Rajdhani',sans-serif;font-size:.65rem;letter-spacing:2px;color:var(--gray);text-transform:uppercase;">Bantuan & Informasi</div>
        <div style="font-family:'Orbitron',monospace;font-size:.85rem;color:var(--white);margin-top:4px;">+62 812-3456-7890</div>
      </div>
    </div>
  </div>
</div>

<script>
/* ═══ CURSOR ═══ */
const cur=document.getElementById('cur'),curR=document.getElementById('cur-r');
let mx=0,my=0,rx=0,ry=0;
document.addEventListener('mousemove',e=>{mx=e.clientX;my=e.clientY;cur.style.left=mx+'px';cur.style.top=my+'px';});
(function t(){rx+=(mx-rx)*.11;ry+=(my-ry)*.11;curR.style.left=rx+'px';curR.style.top=ry+'px';requestAnimationFrame(t);})();
document.querySelectorAll('a,button,.fc,.cal-day.available,.time-slot.avail,.f-links a,label,.step-item').forEach(el=>{
  el.addEventListener('mouseenter',()=>{cur.style.width='15px';cur.style.height='15px';curR.style.width='50px';curR.style.height='50px';curR.style.opacity='.65';});
  el.addEventListener('mouseleave',()=>{cur.style.width='10px';cur.style.height='10px';curR.style.width='34px';curR.style.height='34px';curR.style.opacity='.4';});
});

/* ═══ DATA ═══ */
const FIELDS={
  '1':{name:'LAPANGAN 1',type:'Rumput Sintetis Pro',price:1000000,cap:'16-20 Pemain (8v8 / 10v10)',surface:'Rumput Sintetis Grade Pro',lighting:'LED Penuh',location:'Outdoor',imgs:['https://images.unsplash.com/photo-1575361204480-aadea25e6e68?w=400&q=80','https://images.unsplash.com/photo-1529900748604-07564a03e7a6?w=400&q=80']},
  '2':{name:'LAPANGAN 2',type:'Rumput Sintetis Premium',price:1200000,cap:'16-20 Pemain (8v8 / 10v10)',surface:'Rumput Sintetis Grade Premium',lighting:'LED Penuh',location:'Outdoor + Tribun Penonton',imgs:['https://images.unsplash.com/photo-1529900748604-07564a03e7a6?w=400&q=80','https://images.unsplash.com/photo-1575361204480-aadea25e6e68?w=400&q=80']},
  '3':{name:'LAPANGAN 3',type:'Rumput Sintetis Elite',price:1000000,cap:'16-20 Pemain (8v8 / 10v10)',surface:'Rumput Sintetis Grade Elite',lighting:'LED Penuh',location:'Outdoor',imgs:['https://images.unsplash.com/photo-1518604666860-9ed391f76460?w=400&q=80','https://images.unsplash.com/photo-1543326727-cf6c39e8f84c?w=400&q=80']},
};
const fieldDetail={
  '1':{info:[['Kapasitas','16-20 Pemain'],['Jenis Rumput','Sintetis Grade Pro'],['Pencahayaan','LED Standar Kompetisi'],['Fasilitas','Ruang Ganti, Toilet, Food Stand, Parkir Luas']]},
  '2':{info:[['Kapasitas','16-20 Pemain'],['Jenis Rumput','Sintetis Premium'],['Pencahayaan','LED Standar Kompetisi'],['Fasilitas','Tribun, Ruang Ganti, Toilet, Food Stand, Parkir Luas']]},
  '3':{info:[['Kapasitas','16-20 Pemain'],['Jenis Rumput','Sintetis Grade Elite'],['Pencahayaan','LED Standar Kompetisi'],['Fasilitas','Ruang Ganti, Toilet, Food Stand, Parkir Luas']]},
};

let state={field:null,date:null,slots:[],step:1,calYear:0,calMonth:0};
const NOW=new Date(); state.calYear=NOW.getFullYear(); state.calMonth=NOW.getMonth();

// === MENGHUBUNGKAN FETCH API DATABASE MYSQL === //
let currentBookedSlots = [];

async function fetchAndRenderTimeGrid() {
  const grid = document.getElementById('timeGrid');
  grid.innerHTML = '<div style="color:var(--green); grid-column:1/-1; text-align:center; padding: 20px;">Memuat jadwal dari database...</div>';
  
  try {
    let res = await fetch('?action=get_booked_slots', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({field_id: state.field, date: state.date})
    });
    let data = await res.json();
    currentBookedSlots = data.booked || [];
  } catch(e) {
    console.error(e);
    currentBookedSlots = [];
  }
  renderTimeGridSync();
}

function renderTimeGridSync(){
  const grid=document.getElementById('timeGrid');grid.innerHTML='';
  const booked=currentBookedSlots;

  const now=new Date();
  const todayStr=`${now.getFullYear()}-${String(now.getMonth()+1).padStart(2,'0')}-${String(now.getDate()).padStart(2,'0')}`;
  const isToday=state.date===todayStr;
  const currentHour=now.getHours();

  for(let h=8;h<=23;h++){
    const isBooked=booked.includes(h);
    const isPastHour=isToday&&h<=currentHour;
    const isSel=state.slots.includes(h);
    const label=`${String(h).padStart(2,'0')}:00 – ${String(h+1).padStart(2,'0')}:00`;
    const price=`Rp ${FIELDS[state.field].price.toLocaleString('id-ID')}`;
    
    let cls='time-slot '+(isBooked||isPastHour?'booked':(isSel?'sel avail':'avail'));
    
    grid.innerHTML+=`<div class="${cls}" id="ts-${h}" onclick="toggleSlot(${h})">
      <div class="ts-time">${label}</div>
      <div class="ts-price">${price}</div>
      <div class="ts-check">✓</div>
    </div>`;
  }
  
  setTimeout(()=>{
    grid.querySelectorAll('.time-slot.avail').forEach(el=>{
      el.addEventListener('mouseenter',()=>{cur.style.width='15px';cur.style.height='15px';});
      el.addEventListener('mouseleave',()=>{cur.style.width='10px';cur.style.height='10px';});
    });
  },0);
}

function toggleSlot(h){
  if(currentBookedSlots.includes(h))return;
  const now=new Date();
  const todayStr=`${now.getFullYear()}-${String(now.getMonth()+1).padStart(2,'0')}-${String(now.getDate()).padStart(2,'0')}`;
  if(state.date===todayStr&&h<=now.getHours())return;
  const idx=state.slots.indexOf(h);
  if(idx > -1) { state.slots.splice(idx, 1); } else { state.slots.push(h); }
  renderTimeGridSync();
  document.getElementById('btn3').disabled = state.slots.length === 0;
  updateSidebarTime();
}

const stepLabels=['','PILIH LAPANGAN','PILIH TANGGAL','PILIH JAM','DATA DIRI'];
function goStep(n){
  if(n===2&&!state.field)return;
  if(n===3&&!state.date)return;
  if(n===4&&state.slots.length===0)return;

  if (n === 1 && state.step !== 1) {
    state.field = null;
    state.date = null;
    state.slots = [];
    
    document.querySelectorAll('.fc').forEach(f => f.classList.remove('selected'));
    document.getElementById('field-detail').classList.remove('open');
    
    document.getElementById('sum-field-block').innerHTML = '<div class="sum-empty">Belum ada lapangan dipilih</div>';
    document.getElementById('sum-dt-block').style.display = 'none';
    document.getElementById('sum-total-block').style.display = 'none';
    
    document.getElementById('btn1').disabled = true;
  }

  document.getElementById('step'+state.step).style.display='none';
  document.getElementById('step'+n).style.display='block';
  state.step=n;

  [1,2,3,4].forEach(i=>{
    const sn=document.getElementById('sn'+i),sl=document.getElementById('sl'+i),sc=document.getElementById('sc'+i);
    if(i<n){sn.classList.add('done');sn.classList.remove('active');sl.classList.remove('active');}
    else if(i===n){sn.classList.remove('done');sn.classList.add('active');sl.classList.add('active');}
    else{sn.classList.remove('done','active');sl.classList.remove('active');}
    if(sc&&i<n)sc.classList.add('done');
    else if(sc)sc.classList.remove('done');
  });

  document.getElementById('stepIndicator').textContent=`STEP ${n} / 4 — ${stepLabels[n]}`;

  if(n===2)renderCalendar();
  if(n===3)fetchAndRenderTimeGrid(); 
  if(n===4)setupFormListeners();
  window.scrollTo({top:0,behavior:'smooth'});
}

function selectField(id){
  state.field=id;state.date=null;state.slots=[];
  ['1','2','3'].forEach(f=>{ document.getElementById('fc-'+f).classList.toggle('selected',f===id); });
  document.getElementById('field-detail').classList.add('open');
  const fd=FIELDS[id];
  document.getElementById('fd-imgs').innerHTML=fd.imgs.map(s=>`<img src="${s}" alt="" loading="lazy">`).join('');
  document.getElementById('fd-info').innerHTML=fieldDetail[id].info.map(([k,v])=>`<div class="fd-info-row"><span class="fd-key">${k}</span><span class="fd-val">${v}</span></div>`).join('');
  updateSidebarField(id);
  document.getElementById('btn1').disabled=false;
}

function updateSidebarField(id){
  const fd=FIELDS[id];
  document.getElementById('sum-field-block').innerHTML=`
    <div class="sum-field-img">
      <img src="${fd.imgs[0]}" alt="">
      <div class="sum-field-img-ov"></div>
      <div class="sum-field-name">${fd.name}</div>
    </div>
    <div class="sum-row"><span class="sum-key">Kapasitas</span><span class="sum-val">${fd.cap}</span></div>
    <div class="sum-row"><span class="sum-key">Harga</span><span class="sum-val green">Rp ${fd.price.toLocaleString('id-ID')}/jam</span></div>`;
}

const MONTHS=['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
const DAYS=['Min','Sen','Sel','Rab','Kam','Jum','Sab'];

function changeMonth(d){
  state.calMonth+=d;
  if(state.calMonth>11){state.calMonth=0;state.calYear++;}
  if(state.calMonth<0){state.calMonth=11;state.calYear--;}
  renderCalendar();
}

function renderCalendar(){
  document.getElementById('calMonth').textContent=`${MONTHS[state.calMonth]} ${state.calYear}`;
  const grid=document.getElementById('calGrid');
  grid.innerHTML=DAYS.map(d=>`<div class="cal-day-name">${d}</div>`).join('');
  
  const first=new Date(state.calYear,state.calMonth,1).getDay();
  const days=new Date(state.calYear,state.calMonth+1,0).getDate();
  const today=new Date();today.setHours(0,0,0,0);
  
  for(let i=0;i<first;i++)grid.innerHTML+=`<div class="cal-day empty"></div>`;
  
  for(let d=1;d<=days;d++){
    const date=new Date(state.calYear,state.calMonth,d);
    const isPast=date<today;
    const isToday=date.toDateString()===today.toDateString();
    const dateStr=`${state.calYear}-${String(state.calMonth+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
    const isSel=state.date===dateStr;
    
    let cls='cal-day';
    if(isPast) cls+=' past'; else cls+=' available';
    if(isToday) cls+=' today';
    if(isSel) cls+=' selected';
    
    grid.innerHTML+=`<div class="${cls}" onclick="selectDate('${dateStr}',this)">${d}</div>`;
  }
  
  setTimeout(()=>{
    grid.querySelectorAll('.cal-day.available').forEach(el=>{
      el.addEventListener('mouseenter',()=>{cur.style.width='15px';cur.style.height='15px';});
      el.addEventListener('mouseleave',()=>{cur.style.width='10px';cur.style.height='10px';});
    });
  },0);
}

function selectDate(dateStr,el){
  state.date=dateStr;state.slots=[];
  document.querySelectorAll('.cal-day').forEach(d=>d.classList.remove('selected'));
  el.classList.add('selected');
  const dt=new Date(dateStr+'T00:00:00');
  const dayNames=['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
  document.getElementById('sum-date').textContent=`${dayNames[dt.getDay()]}, ${dt.getDate()} ${MONTHS[dt.getMonth()]} ${dt.getFullYear()}`;
  document.getElementById('sum-dt-block').style.display='block';
  document.getElementById('btn2').disabled=false;
  updateSidebarTime();
}

function updateSidebarTime(){
  if(state.slots.length===0){
    document.getElementById('sum-time').textContent='—';
    document.getElementById('sum-dur').textContent='—';
    document.getElementById('sum-total-block').style.display='none';
    return;
  }
  const sorted=[...state.slots].sort((a,b)=>a-b);
  const timeLabels = sorted.map(h => `${String(h).padStart(2,'0')}:00`).join('<br>');
  document.getElementById('sum-time').innerHTML=timeLabels;
  document.getElementById('sum-dur').textContent=`${sorted.length} Jam`;
  document.getElementById('sum-total-block').style.display='block';
  updatePaymentUI();
}

function updatePaymentUI() {
  if(state.slots.length === 0) return;
  const price = FIELDS[state.field].price;
  const total = price * state.slots.length;
  const payTypeEle = document.querySelector('input[name="pay_type"]:checked');
  const payType = payTypeEle ? payTypeEle.value : 'lunas';

  document.getElementById('sum-perjam').textContent = `Rp ${price.toLocaleString('id-ID')}`;

  if(payType === 'dp') {
    document.getElementById('dp-note').style.display = 'block';
    
    if(document.getElementById('row-subtotal')) {
        document.getElementById('row-subtotal').style.display = 'flex';
        document.getElementById('sum-subtotal').textContent = `Rp ${total.toLocaleString('id-ID')}`;
    }
    
    document.getElementById('row-dp').style.display = 'flex';
    document.getElementById('row-sisa').style.display = 'flex';
    document.getElementById('sum-total-label').textContent = 'TOTAL DP DIBAYAR (50%)';
    
    const dp = total / 2;
    document.getElementById('sum-dp-val').textContent = `Rp ${dp.toLocaleString('id-ID')}`;
    document.getElementById('sum-sisa-val').textContent = `Rp ${dp.toLocaleString('id-ID')}`;
    document.getElementById('sum-total').textContent = `Rp ${dp.toLocaleString('id-ID')}`;
  } else {
    document.getElementById('dp-note').style.display = 'none';
    
    if(document.getElementById('row-subtotal')) {
        document.getElementById('row-subtotal').style.display = 'none';
    }
    
    document.getElementById('row-dp').style.display = 'none';
    document.getElementById('row-sisa').style.display = 'none';
    document.getElementById('sum-total-label').textContent = 'TOTAL BIAYA';
    document.getElementById('sum-total').textContent = `Rp ${total.toLocaleString('id-ID')}`;
  }
}

function setupFormListeners(){
  ['f-nama','f-telp','f-email','f-agree'].forEach(id=>{
    const el=document.getElementById(id);
    if(el)el.addEventListener('change',checkForm);
    if(el)el.addEventListener('input',checkForm);
  });
  checkForm();
}

function checkForm(){
  const n=document.getElementById('f-nama').value.trim(), t=document.getElementById('f-telp').value.trim(), e=document.getElementById('f-email').value.trim(), a=document.getElementById('f-agree').checked;
  const valid=n&&t&&e&&a&&/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(e);
  document.getElementById('btn4').disabled=!valid;
}

// === SUBMIT BOOKING MENGGUNAKAN FETCH KE DATABASE === //
async function submitBooking(){
  const btn = document.getElementById('btn4');
  const originalText = btn.innerHTML;
  btn.innerHTML = "Memproses...";
  btn.disabled = true;

  const fd=FIELDS[state.field];
  const sorted=[...state.slots].sort((a,b)=>a-b);
  const total=fd.price*sorted.length; // Harga dari frontend (sebagai fallback)
  const payType = document.querySelector('input[name="pay_type"]:checked').value;
  
  const payload = {
      field_id: state.field,
      date: state.date,
      slots: sorted,
      name: document.getElementById('f-nama').value,
      phone: document.getElementById('f-telp').value,
      email: document.getElementById('f-email').value,
      team: document.getElementById('f-tim').value,
      pay_type: payType,
      total_price: total,
      notes: document.getElementById('f-catatan').value
  };

  try {
    let res = await fetch('?action=submit_booking', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(payload)
    });
    let data = await res.json();
    
    if(data.success) {
      // Menggunakan harga aktual dari server jika ada, mencegah manipulasi dari frontend
      const serverTotal = data.actual_price ? data.actual_price : total;

      const dt=new Date(state.date+'T00:00:00');
      const dayNames=['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
      const MONTHS2=['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
      
      const dateLabel=`${dayNames[dt.getDay()]}, ${dt.getDate()} ${MONTHS2[dt.getMonth()]} ${dt.getFullYear()}`;
      const timeLabel = sorted.map(h => `${String(h).padStart(2,'0')}:00`).join(', ');
      
      let transferAmount = serverTotal, sisaAmount = 0;
      if(payType === 'dp') { transferAmount = serverTotal / 2; sisaAmount = serverTotal / 2; }
      
      document.getElementById('booking-code').textContent = data.code; 
      
      document.getElementById('success-detail').innerHTML=`
        <div class="success-detail-row"><span class="sdk">Lapangan</span><span class="sdv">${fd.name}</span></div>
        <div class="success-detail-row"><span class="sdk">Tanggal</span><span class="sdv">${dateLabel}</span></div>
        <div class="success-detail-row"><span class="sdk">Waktu Bermain</span><span class="sdv" style="text-align:right;">${timeLabel} <br>(${sorted.length} jam)</span></div>
        <div class="success-detail-row"><span class="sdk">Tipe Bayar</span><span class="sdv">${payType === 'dp' ? 'DP 50%' : 'Lunas'}</span></div>
        <div class="success-detail-row" style="margin-top:12px; border-top:1px solid rgba(255,255,255,0.1); padding-top:12px;">
          <span class="sdk">Total Harga Lapangan</span>
          <span class="sdv" style="color:var(--white);font-family:'Orbitron',monospace;font-size:1rem;">Rp ${serverTotal.toLocaleString('id-ID')}</span>
        </div>`;
        
      document.getElementById('transfer-amount').textContent = `Rp ${transferAmount.toLocaleString('id-ID')}`;
      
      if(payType === 'dp') {
        document.getElementById('sisa-bayar-info').style.display = 'block';
        document.getElementById('sisa-amount').textContent = `Rp ${sisaAmount.toLocaleString('id-ID')}`;
      } else {
        document.getElementById('sisa-bayar-info').style.display = 'none';
      }
        
      document.getElementById('success-overlay').classList.add('show');
    } else {
      alert("Gagal melakukan booking: " + data.error);
      btn.innerHTML = originalText;
      btn.disabled = false;
    }
  } catch(e) {
      alert("Terjadi kesalahan koneksi server.");
      btn.innerHTML = originalText;
      btn.disabled = false;
  }
  confetti({
    particleCount: 150,        
    spread: 100,              
    origin: { y: 0.5 },        
    colors: ['#00ff88', '#ffffff'], 
    zIndex: 6000,              
    disableForReducedMotion: true
  });
  // --- KODE TIMER LOGIC DITAMBAHKAN DI SINI ---
  let timeLeft = 300; 
  const timerDisplay = document.getElementById('payment-timer');
  const timerMsg = document.getElementById('payment-timer-msg');
  
  // Reset UI Timer setiap 
  timerDisplay.textContent = "05:00";
  timerDisplay.style.color = "var(--red)";
  timerDisplay.style.textShadow = "0 0 10px rgba(255,59,92,.4)";
  timerMsg.innerHTML = "Jika dalam 5 menit belum melakukan pembayaran, booking otomatis dibatalkan.";
  
  const timerInterval = setInterval(() => {
    timeLeft--;
    let m = Math.floor(timeLeft / 60);
    let s = Math.floor(timeLeft % 60);
    timerDisplay.textContent = `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
    
    if (timeLeft <= 0) {
      clearInterval(timerInterval);
      timerDisplay.textContent = "00:00";
      timerDisplay.style.color = "var(--gray)";
      timerDisplay.style.textShadow = "none";
      timerMsg.innerHTML = "Waktu pembayaran telah habis. <br><span style='color:var(--red);font-weight:700;'>Booking otomatis dibatalkan.</span>";
    }
  }, 1000);
}

document.addEventListener('DOMContentLoaded', () => {
  
  // 1. REACTIVE GRID BACKGROUND
  document.addEventListener('mousemove', (e) => {
    document.documentElement.style.setProperty('--mouse-x', `${e.clientX}px`);
    document.documentElement.style.setProperty('--mouse-y', `${e.clientY}px`);
  });

  // 2. 3D TILT EFFECT PADA KARTU LAPANGAN
  const cards = document.querySelectorAll('.fc');
  cards.forEach(card => {
    card.addEventListener('mousemove', (e) => {
      const rect = card.getBoundingClientRect();
      const x = e.clientX - rect.left; // x position within the element.
      const y = e.clientY - rect.top;  // y position within the element.
      
      const centerX = rect.width / 2;
      const centerY = rect.height / 2;
      
      const rotateX = ((y - centerY) / centerY) * -10; // Max tilt 10 deg
      const rotateY = ((x - centerX) / centerX) * 10;
      
      card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(1.02, 1.02, 1.02)`;
      
      // Update glare position
      card.style.setProperty('--x', `${x}px`);
      card.style.setProperty('--y', `${y}px`);
    });
    
    card.addEventListener('mouseleave', () => {
      card.style.transform = `perspective(1000px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)`;
      card.style.setProperty('--x', `50%`);
      card.style.setProperty('--y', `50%`);
    });
  });

  // 3. CYBERPUNK SYNTH SOUNDS (Web Audio API)
  const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
  
  function playBeep(freq, type, duration, vol) {
    if(audioCtx.state === 'suspended') audioCtx.resume();
    const osc = audioCtx.createOscillator();
    const gainNode = audioCtx.createGain();
    
    osc.type = type; 
    osc.frequency.setValueAtTime(freq, audioCtx.currentTime);
    
    gainNode.gain.setValueAtTime(vol, audioCtx.currentTime);
    gainNode.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + duration);
    
    osc.connect(gainNode);
    gainNode.connect(audioCtx.destination);
    
    osc.start();
    osc.stop(audioCtx.currentTime + duration);
  }

  const hoverElements = document.querySelectorAll('button, .fc, .step-item, .time-slot, .cal-day');
  hoverElements.forEach(el => {
    el.addEventListener('mouseenter', () => {
      playBeep(800, 'sine', 0.05, 0.02); 
    });
  });

  document.addEventListener('click', (e) => {
    const el = e.target.closest('button, .fc, .time-slot.avail, .cal-day.available');
    if(el) {
      if(el.id === 'btn4') {
        // Suara sukses saat submit
        playBeep(440, 'sine', 0.1, 0.1);
        setTimeout(() => playBeep(660, 'sine', 0.2, 0.1), 100);
        setTimeout(() => playBeep(880, 'triangle', 0.4, 0.15), 200);
      } else {
        // Suara klik biasa
        playBeep(1200, 'square', 0.08, 0.03);
      }
    }
  });

});
</script>
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
</body>
</html>