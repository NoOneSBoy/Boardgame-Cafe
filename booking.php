<?php
ob_start();
$pageTitle = "Booking";
include_once 'header.php';
include_once 'cekcon.php';

date_default_timezone_set('Asia/Pontianak');

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$meja = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM tables WHERE id = $id"));

if (!$meja) {
    die("Meja tidak ditemukan");
}

$games = mysqli_fetch_all(mysqli_query($conn, "SELECT id, title, img, price FROM games ORDER BY title"), MYSQLI_ASSOC);

$pesan = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'] ?? 1;
    $tanggal = mysqli_real_escape_string($conn, $_POST['reservation_date']);
    $mulai = (int) $_POST['start_time'];
    $selesai = (int) $_POST['end_time'];
    $tamu = (int) $_POST['guest_count'];
    $pilih = isset($_POST['games']) ? array_unique(array_map('intval', $_POST['games'])) : [];

    $start_time = sprintf("%02d:00:00", $mulai);
    $end_time = sprintf("%02d:00:00", $selesai);

    $bentrok = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS jml FROM reservations WHERE table_id = $id AND reservation_date = '$tanggal' AND start_time < '$end_time' AND end_time > '$start_time'"));

    if ($tanggal < date('Y-m-d')) {
        $pesan = "Tanggal tidak boleh sebelum hari ini";
    } elseif ($mulai < 9 || $selesai > 20 || $selesai <= $mulai) {
        $pesan = "Jam selesai harus lebih besar dari jam mulai (09:00 - 20:00)";
    } elseif ($tamu < 1 || $tamu > $meja['max_capacity']) {
        $pesan = "Jumlah tamu maksimal " . $meja['max_capacity'] . " orang";
    } elseif ($bentrok['jml'] > 0) {
        $pesan = "Jadwal tersebut sudah dibooking, pilih jam lain";
    } else {
        $harga_game = [];
        foreach ($games as $g) {
            $harga_game[$g['id']] = $g['price'];
        }

        $total = $meja['price'] * ($selesai - $mulai);
        foreach ($pilih as $gid) {
            if (isset($harga_game[$gid])) {
                $total += $harga_game[$gid];
            }
        }

        $simpan = mysqli_query($conn, "INSERT INTO reservations (user_id, table_id, reservation_date, start_time, end_time, guest_count, created_at, total) VALUES ($user_id, $id, '$tanggal', '$start_time', '$end_time', $tamu, NOW(), $total)");

        if (!$simpan) {
            $pesan = "Gagal menyimpan: " . mysqli_error($conn);
        } else {
            $reservation_id = mysqli_insert_id($conn);

            foreach ($pilih as $gid) {
                if (isset($harga_game[$gid])) {
                    $amount = $harga_game[$gid];
                    mysqli_query($conn, "INSERT INTO reservation_games (reservation_id, game_id, amount) VALUES ($reservation_id, $gid, $amount)");
                }
            }

            header("Location: payment.php?id=$reservation_id&sukses=1");
            exit;
        }
    }
}
?>

<div class="max-w-6xl mx-auto p-8">

    <a href="tap-produk.php?id=<?= $meja['id'] ?>" class="text-sm">&laquo; Kembali</a>

    <?php if ($pesan != "") { ?>
        <p class="border rounded-lg p-4 mt-4"><?= $pesan ?></p>
    <?php } ?>

    <form method="post" class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-4">

        <div class="lg:col-span-2">
            <h2 class="text-xl font-semibold">Games tambahan</h2>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mt-4">
                <?php foreach ($games as $g) { ?>
                    <label class="border rounded-lg p-2 block cursor-pointer">
                        <img src="<?= $g['img'] ?>" alt="<?= $g['title'] ?>" class="w-full h-28 object-cover rounded">
                        <div class="flex items-center gap-2 mt-2">
                            <input type="checkbox" name="games[]" value="<?= $g['id'] ?>" <?= in_array($g['id'], $_POST['games'] ?? []) ? 'checked' : '' ?>>
                            <span class="text-sm"><?= $g['title'] ?></span>
                        </div>
                        <p class="text-sm mt-1">Rp <?= number_format($g['price'], 0, ',', '.') ?></p>
                    </label>
                <?php } ?>
            </div>
        </div>

        <div>
            <h1 class="text-2xl font-semibold">Meja <?= $meja['table_number'] ?></h1>
            <p class="mt-2">Harga: Rp <?= number_format($meja['price'], 0, ',', '.') ?> / jam</p>
            <p>Kapasitas: <?= $meja['max_capacity'] ?> orang</p>

            <label class="block mt-6 text-sm">Tanggal</label>
            <input type="date" name="reservation_date" min="<?= date('Y-m-d') ?>" value="<?= $_POST['reservation_date'] ?? '' ?>" required class="border rounded w-full p-2">

            <label class="block mt-4 text-sm">Start time</label>
            <select name="start_time" class="border rounded w-full p-2">
                <?php for ($h = 9; $h <= 19; $h++) { ?>
                    <option value="<?= $h ?>" <?= (($_POST['start_time'] ?? 9) == $h) ? 'selected' : '' ?>><?= sprintf("%02d:00", $h) ?></option>
                <?php } ?>
            </select>

            <label class="block mt-4 text-sm">Until</label>
            <select name="end_time" class="border rounded w-full p-2">
                <?php for ($h = 10; $h <= 20; $h++) { ?>
                    <option value="<?= $h ?>" <?= (($_POST['end_time'] ?? 10) == $h) ? 'selected' : '' ?>><?= sprintf("%02d:00", $h) ?></option>
                <?php } ?>
            </select>

            <label class="block mt-4 text-sm">Jumlah tamu</label>
            <input type="number" name="guest_count" min="1" max="<?= $meja['max_capacity'] ?>" value="<?= $_POST['guest_count'] ?? 1 ?>" required class="border rounded w-full p-2">

            <button type="submit" class="border rounded-lg w-full py-3 mt-6">Booking</button>
        </div>

    </form>
</div>

<?php include_once 'footer.php'; ?>