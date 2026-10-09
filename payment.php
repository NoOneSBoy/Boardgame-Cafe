<?php
ob_start();
$pageTitle = "Payment";
include_once 'header.php';
include_once 'cekcon.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$reservasi = mysqli_fetch_assoc(mysqli_query($conn, "SELECT r.*, t.table_number, t.img, t.price FROM reservations r JOIN tables t ON t.id = r.table_id WHERE r.id = $id"));

if (!$reservasi) {
    die("Reservasi tidak ditemukan");
}

$games = mysqli_fetch_all(mysqli_query($conn, "SELECT g.title, g.img, rg.amount FROM reservation_games rg JOIN games g ON g.id = rg.game_id WHERE rg.reservation_id = $id"), MYSQLI_ASSOC);

$metode = [];
foreach (mysqli_fetch_all(mysqli_query($conn, "SELECT id, name, img FROM payment_method ORDER BY id"), MYSQLI_ASSOC) as $m) {
    $metode[$m['id']] = $m;
}

$kelompok = [
    'E-Wallet' => [1, 3],
    'Transfer Bank' => [5, 6, 7],
    'Gerai & Bayar di Tempat' => [9],
];

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $metode_id = (int) ($_POST['metode'] ?? 0);

    if (!isset($metode[$metode_id])) {
        $error = "Pilih metode pembayaran";
    } else {
        $total = (int) $reservasi['total'];

        try {
            mysqli_query($conn, "INSERT INTO payments (reservation_id, payment_method_id, amount) VALUES ($id, $metode_id, $total)");
            header("Location: pembayaranberhasil.php?id=$id");
            exit;
        } catch (mysqli_sql_exception $e) {
            $error = "Gagal menyimpan pembayaran: " . $e->getMessage();
        }
    }
}
?>

<div class="max-w-5xl mx-auto p-8">

    <?php if (isset($_GET['sukses'])) { ?>
        <p class="border rounded-lg p-4 mb-8">Booking berhasil</p>
    <?php } ?>

    <?php if ($error != "") { ?>
        <p class="border rounded-lg p-4 mb-8"><?= $error ?></p>
    <?php } ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

        <div class="border rounded-lg self-start">

            <div class="p-4 border-b">
                <p>Tanggal: <?= $reservasi['reservation_date'] ?></p>
                <p>Waktu: <?= substr($reservasi['start_time'], 0, 5) ?> - <?= substr($reservasi['end_time'], 0, 5) ?></p>
            </div>

            <div class="p-4 border-b">
                <p class="text-sm text-gray-400">Meja:</p>
                <div class="flex items-center justify-between mt-2">
                    <div class="flex items-center gap-3">
                        <img src="<?= $reservasi['img'] ?>" alt="Meja <?= $reservasi['table_number'] ?>" class="w-16 h-16 object-cover rounded">
                        <p>Meja <?= $reservasi['table_number'] ?></p>
                    </div>
                    <p>Rp <?= number_format($reservasi['price'], 0, ',', '.') ?> / jam</p>
                </div>
            </div>

            <div class="p-4 border-b">
                <p class="text-sm text-gray-400">Games:</p>

                <?php if (count($games) == 0) { ?>
                    <p class="text-sm mt-2">Tidak ada games tambahan</p>
                <?php } ?>

                <?php foreach ($games as $g) { ?>
                    <div class="flex items-center justify-between mt-2">
                        <div class="flex items-center gap-3">
                            <img src="<?= $g['img'] ?>" alt="<?= $g['title'] ?>" class="w-16 h-16 object-cover rounded">
                            <p><?= $g['title'] ?></p>
                        </div>
                        <p>Rp <?= number_format($g['amount'], 0, ',', '.') ?></p>
                    </div>
                <?php } ?>
            </div>

            <div class="p-4 flex items-center justify-between">
                <p class="text-2xl">SUBTOTAL</p>
                <p class="text-xl">Rp <?= number_format($reservasi['total'], 0, ',', '.') ?></p>
            </div>

        </div>

        <div>
            <h2 class="text-xl font-semibold">Metode Pembayaran</h2>

            <form method="post" class="mt-4">
                <?php foreach ($kelompok as $judul => $daftar) { ?>
                    <p class="text-sm font-medium mt-6 mb-2"><?= $judul ?></p>

                    <div class="grid grid-cols-2 gap-3">
                        <?php foreach ($daftar as $mid) {
                            if (!isset($metode[$mid])) {
                                continue;
                            }
                            $m = $metode[$mid];
                        ?>
                            <label class="flex items-center gap-3 border border-gray-300 rounded-lg p-3 cursor-pointer has-[:checked]:border-black has-[:checked]:ring-1 has-[:checked]:ring-black">
                                <input type="radio" name="metode" value="<?= $m['id'] ?>" class="peer sr-only" required>
                                <span class="w-5 h-5 shrink-0 border border-gray-400 rounded flex items-center justify-center text-xs text-transparent peer-checked:bg-black peer-checked:border-black peer-checked:text-white">&#10003;</span>
                                <img src="<?= $m['img'] ?>" alt="" class="w-8 h-6 object-contain" onerror="this.style.display='none'">
                                <span class="text-sm"><?= $m['name'] ?></span>
                            </label>
                        <?php } ?>
                    </div>
                <?php } ?>

                <button type="submit" class="border rounded-lg w-full py-4 mt-8 text-lg">
                    Bayar Rp <?= number_format($reservasi['total'], 0, ',', '.') ?>
                </button>
            </form>
        </div>

    </div>
</div>

<?php include_once 'footer.php'; ?>