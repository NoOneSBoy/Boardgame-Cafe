<?php
$pageTitle = "Detail Meja";
include 'header.php';
include 'cekcon.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$meja = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM tables WHERE id = $id"));

if (!$meja) {
    die("Meja tidak ditemukan");
}

$games = mysqli_fetch_all(mysqli_query($conn, "SELECT title, img FROM games LIMIT 8"), MYSQLI_ASSOC);
$jadwal = mysqli_query($conn, "SELECT reservation_date, start_time, end_time, guest_count FROM reservations WHERE table_id = $id ORDER BY reservation_date, start_time");
?>

<div class="max-w-6xl mx-auto p-8">

    <a href="homepage.php" class="text-sm">&laquo; Kembali</a>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-4">

        <div class="lg:col-span-2">
            <img src="<?= $meja['img'] ?>" alt="Meja <?= $meja['table_number'] ?>" class="w-full h-96 object-cover rounded-lg">

            <div class="grid grid-cols-4 gap-4 mt-4">
                <?php foreach ($games as $g) { ?>
                    <div>
                        <img src="<?= $g['img'] ?>" alt="<?= $g['title'] ?>" class="w-full h-24 object-cover rounded">
                        <p class="text-sm text-center mt-1"><?= $g['title'] ?></p>
                    </div>
                <?php } ?>
            </div>

            <div class="grid grid-cols-3 gap-4 mt-4">
                <?php foreach (array_slice($games, 0, 3) as $g) { ?>
                    <div>
                        <img src="<?= $g['img'] ?>" alt="<?= $g['title'] ?>" class="w-full h-40 object-cover rounded">
                        <p class="text-sm text-center mt-1"><?= $g['title'] ?></p>
                    </div>
                <?php } ?>
            </div>
        </div>

        <div>
            <h1 class="text-2xl font-semibold">Meja <?= $meja['table_number'] ?></h1>

            <div class="mt-3 space-y-1">
                <p>Lokasi: <?= $meja['location'] ?></p>
                <p>Kapasitas: <?= $meja['max_capacity'] ?> orang</p>
                <p>Status: <?= $meja['status'] ?></p>
                <p>Harga: <?= $meja['price'] ?></p>
            </div>

            <div class="border rounded-lg p-4 mt-6">
                <h3 class="font-semibold mb-2">Jadwal yang sudah dibooking</h3>

                <?php if (mysqli_num_rows($jadwal) == 0) { ?>
                    <p class="text-sm">Belum ada booking</p>
                <?php } ?>

                <?php while ($j = mysqli_fetch_assoc($jadwal)) { ?>
                    <p class="text-sm py-1">
                        <?= $j['reservation_date'] ?>,
                        <?= substr($j['start_time'], 0, 5) ?> - <?= substr($j['end_time'], 0, 5) ?>
                        (<?= $j['guest_count'] ?> orang)
                    </p>
                <?php } ?>
            </div>

            <a href="booking.php?id=<?= $meja['id'] ?>" class="flex items-center justify-center w-48 aspect-square border rounded-lg mt-6">
                Start Booking
            </a>
        </div>

    </div>
</div>

<?php include 'footer.php'; ?>