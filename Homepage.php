<?php
$pageTitle = "Homepage";
include 'header.php';
include "cekcon.php";

$result = mysqli_query($conn, "SELECT * FROM tables");
?>

<div class="max-w-4xl mx-auto p-8">
    <h1 class="text-2xl font-semibold mb-6">Daftar Meja</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
        <?php while ($row = mysqli_fetch_assoc($result)) {
            $tersedia = trim($row['status']) === 'Available';
            $warna = $tersedia ? 'bg-green-200' : 'bg-red-200';
        ?>
            <div class="border rounded-lg overflow-hidden <?= $warna ?>">

                <img src="<?= $row['img'] ?>" alt="Meja <?= $row['table_number'] ?>" class="w-full h-48 object-cover">

                <div class="flex items-center justify-between p-4">
                    <div>
                        <p class="font-medium">Meja <?= $row['table_number'] ?></p>
                        <p class="text-sm"><?= $row['location'] ?> - <?= $row['status'] ?></p>
                    </div>

                    <?php if ($tersedia) { ?>
                        <a href="tap-produk.php?id=<?= $row['id'] ?>" class="border rounded px-4 py-1 text-sm">
                            Lihat
                        </a>
                    <?php } else { ?>
                        <span class="border rounded px-4 py-1 text-sm opacity-50 cursor-not-allowed">
                            Lihat
                        </span>
                    <?php } ?>
                </div>

            </div>
        <?php } ?>
    </div>
</div>

<?php include 'footer.php'; ?>