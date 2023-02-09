<?php 
include("connection.php");
// Check Logged
if(!isset($_SESSION['loggedUser'])){
    header("location: login.php");
}
// Initialize Session
if(!isset($_SESSION['tempPembelian'])){
    $_SESSION['tempPembelian'] = [];
}

// Sidebar Menu Choice
$currentMenu = "Dashboard";
$titleDisplay = "Dashboard";
if(isset($_GET['changeMenu'])){
    $currentMenu = $_GET['changeMenu'];

    // Display Title
    if($currentMenu == "Dashboard"){
        $titleDisplay = "Dashboard";
    }
    if($currentMenu == "Pembelian"){
        $titleDisplay = "Pembelian";
    }
    if($currentMenu == "TambahProduk"){
        $titleDisplay = "Tambah Produk";
    }
    if($currentMenu == "Stok"){
        $titleDisplay = "Stok Barang";
    }
    if($currentMenu == "Sejarah"){
        $titleDisplay = "Sejarah Pembelian";
    }
}
// Edit Tabel Produk
if(isset($_GET['editProduk'])){
    $inputKode = $_GET['editProduk'];
    $inputNama = $_POST['inputNama'];
    $inputStok = $_POST['inputStok'];
    $inputHarga = $_POST['inputHarga'];

    // Update produk
    $editProduk = $conn->prepare("update produk set nama = ?, stok = ?, harga = ? where produk_id = ? ");
    $editProduk->bind_param("ssss", $inputNama, $inputStok, $inputHarga, $inputKode);
    $editProduk->execute();
}
// Delete Tabel Produk
if(isset($_GET['deleteProduk'])){
    $inputKode = $_GET['deleteProduk'];
    $deleteProduk = $conn->prepare("delete from produk where produk_id = ? ");
    $deleteProduk->bind_param("s", $inputKode);
    $deleteProduk->execute();
}
// Counts
$kodeUnikProduk = 1;
$fetchProduk = $conn->query("select * from produk");
$arrProduk = $fetchProduk->fetch_all(MYSQLI_BOTH);
foreach($arrProduk as $x){
    if($x['produk_id'] == $kodeUnikProduk){
        $kodeUnikProduk++;
    }
}
$countPembelian = 1;
$fetchPembelian = $conn->query("select * from pembelian");
$arrPembelian = $fetchPembelian->fetch_all(MYSQLI_BOTH);
foreach($arrPembelian as $x){
    if($x['pembelian_id'] == $countPembelian){
        $countPembelian++;
    }
}
// Tambah Row Pembelian
if(isset($_POST['tambahRowPembelian'])){
    $inputKode = $_POST['inputKode'];
    $inputJumlah = $_POST['inputJumlah'];
    $inputNama = "";
    $subTotal = 0;

    // Cek Database
    foreach($arrProduk as $x){
        if(strcmp($x['produk_id'],$inputKode) == 0){
            $inputNama = $x['nama'];
            $subTotal += $x['harga'] * $inputJumlah;
        }
    }

    // Cek Session
    $isInSession = false;
    foreach($_SESSION['tempPembelian'] as $i =>$x){
        if($x['inputKode'] == $inputKode){
            $_SESSION['tempPembelian'][$i]['inputJumlah'] += $inputJumlah;
            $_SESSION['tempPembelian'][$i]['subTotal'] += $subTotal;
            $isInSession = true;
        }
    }

    if(!$isInSession){
        $_SESSION['tempPembelian'][] = [
            'inputKode' => $inputKode,
            'inputNama' => $inputNama,
            'inputJumlah' => $inputJumlah,
            'subTotal' => $subTotal
        ];
    }
    
}
// Remove Row
if(isset($_GET['deleteRowPembelian'])){
    unset($_SESSION['tempPembelian'][$_GET['deleteRowPembelian']]);
}

// Tambah Produk
if(isset($_POST['tambahProduk'])){
    $inputNama = $_POST['inputNama'];
    $inputStok = $_POST['inputStok'];
    $inputHarga = $_POST['inputHarga'];
    // Tambah ke database
    $insertProduk = $conn->prepare("insert into produk(produk_id, nama, stok, harga) values('',?,?,?)");
    $insertProduk->bind_param("sss",$inputNama, $inputStok, $inputHarga);
    $insertProduk->execute();
}

// Tambah Pembelian
if(isset($_POST{'tambahPembelian'})){
    $inputTanggal = date("Y-m-d");
    $inputNoPembelian = $countPembelian;
    $inputPembelianTotal = 0;
    foreach($_SESSION['tempPembelian'] as $i => $x){
        $inputKode = $x['inputKode'];
        $inputJumlah = $x['inputJumlah'];
        $inputSubtotal = $x['subTotal'];
        $inputPembelianTotal += $inputSubtotal;

        // Insert to pembeliandata
        $insertPembelianData = $conn->prepare("insert into pembeliandata(pembelian_id, pembeliandata_produk_id, pembeliandata_jumlah) values(?,?,?)");
        $insertPembelianData->bind_param("sss", $inputNoPembelian, $inputKode, $inputJumlah);
        $insertPembelianData->execute();
    }
    // Insert to pembelian
    $insertPembelian = $conn->prepare("insert into pembelian(pembelian_id, pembelian_tanggal, pembelian_total) values(?,?,?)");
    $insertPembelian->bind_param("sss", $inputNoPembelian, $inputTanggal, $inputPembelianTotal);
    $insertPembelian->execute(); 
    $_SESSION['tempPembelian'] = [];
}



?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <!-- Icon -->
    <link rel="apple-touch-icon" sizes="76x76" href="./assets/img/apple-icon.png">
    <link rel="icon" type="image/png" href="./assets/img/logo.png">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title>Admin Dashboard</title>
    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no' name='viewport' />

    <!--     Fonts and icons     -->
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700,200" rel="stylesheet" />
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" rel="stylesheet">

    <!-- CSS Files -->
    <link href="./assets/css/bootstrap.min.css" rel="stylesheet" />
    <link href="./assets/css/paper-dashboard.css?v=2.0.1" rel="stylesheet" />
    <link href="./assets/css/style.css" rel="stylesheet" />
</head>

<body class="">
    <div class="wrapper ">
        <!-- Sidebar -->
        <div class="sidebar" data-color="white" data-active-color="danger">
            <!-- Name -->
            <div class="logo">
                <a href="#" class="simple-text logo-mini">
                    <div class="logo-image-small">
                        <img src="./assets/img/logo.png">
                    </div>
                </a>
                <a href="#" class="simple-text logo-normal">Admin</a>
            </div>
            
            <!-- Items -->
            <div class="sidebar-wrapper">
                <ul class="nav">
                    <!-- Dashboard -->
                    <li <?php echo (substr($currentMenu,0,9) == "Dashboard"? "class='active'": "");?>>
                        <a href="index.php?changeMenu=Dashboard">
                            <i class="nc-icon nc-tile-56"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>

                    <!-- Pembelian -->
                    <li <?php echo (substr($currentMenu,0,9) == "Pembelian"? "class='active'": "");?>>
                        <a href="index.php?changeMenu=Pembelian">
                            <i class="nc-icon nc-cart-simple"></i>
                            <p>Pembelian</p>
                        </a>
                    </li>
                    
                    <!-- Tambah Produk -->
                    <li <?php echo (substr($currentMenu,0,12) == "TambahProduk"? "class='active'": "");?>>
                        <a href="index.php?changeMenu=TambahProduk">
                            <i class="nc-icon nc-box-2"></i>
                            <p>Tambah Produk</p>
                        </a>
                    </li>
                    
                    <!-- Stok --> 
                    <li <?php echo (substr($currentMenu,0,4) == "Stok"? "class='active'": "");?>>
                        <a href="index.php?changeMenu=Stok">
                            <i class="nc-icon nc-app"></i>
                            <p>Stok</p>
                        </a>
                    </li>
                    <!-- Sejarah --> 
                    <li <?php echo (substr($currentMenu,0,7) == "Sejarah"? "class='active'": "");?>>
                        <a href="index.php?changeMenu=Sejarah">
                            <i class="nc-icon nc-paper"></i>
                            <p>Sejarah</p>
                        </a>
                    </li>
                    <!-- Logout -->
                    <li>
                        <a href="login.php">
                            <i class="nc-icon nc-single-02"></i>
                            <p>Logout</p>
                        </a>
                    </li>
                    
                </ul>
            </div>
        </div>

        <!-- Main Panel -->
        <div class="main-panel" style="height: 100vh;">

        <!-- Navbar -->
            <nav class="navbar navbar-expand-lg navbar-absolute fixed-top navbar-transparent">
                <div class="container-fluid">
                    <div class="navbar-wrapper">
                        <div class="navbar-toggle">
                            <button type="button" class="navbar-toggler">
                                <span class="navbar-toggler-bar bar1"></span>
                                <span class="navbar-toggler-bar bar2"></span>
                                <span class="navbar-toggler-bar bar3"></span>
                            </button>
                        </div>
                        <a class="navbar-brand" href="javascript:;"><?php echo $titleDisplay;?></a>
                    </div>
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navigation" aria-controls="navigation-index" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-bar navbar-kebab"></span>
                        <span class="navbar-toggler-bar navbar-kebab"></span>
                        <span class="navbar-toggler-bar navbar-kebab"></span>
                    </button>
                    
                </div>
            </nav>

            <!-- Contents -->

            <!-- Dashboard -->
            <div class="content <?php echo ($currentMenu != "Dashboard"? "d-none": "");?>">

                <!-- Content -->
                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4">
                    <!-- Cards -->

                    <!-- Pembelian -->
                    <div class="col">
                        <div class="card radius-10 border-start border-0 border-3 border-success">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <p class="mb-0 text-secondary"></p>
                                        <h4 class="my-1 text-success">Pembelian</h4>
                                        <p class="mb-0 font-13"> Menambahkan data pembelian</p>
                                    </div>
                                </div>
                                <a href="index.php?changeMenu=Pembelian" class="stretched-link"></a>
                            </div>
                        </div>
                    </div>
                    <!-- Tambah Produk -->
                    <div class="col">
                        <div class="card radius-10 border-start border-0 border-3 border-info">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <p class="mb-0 text-secondary"></p>
                                        <h4 class="my-1 text-info">Tambah Produk</h4>
                                        <p class="mb-0 font-13">Menambah produk baru</p>
                                    </div>
                                </div>
                                <a href="index.php?changeMenu=TambahProduk" class="stretched-link"></a>
                            </div>
                        </div>
                    </div>
                    <!-- Stok -->
                    <div class="col">
                        <div class="card radius-10 border-start border-0 border-3 border-danger">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <p class="mb-0 text-secondary"></p>
                                        <h4 class="my-1 text-danger">Stok</h4>
                                        <p class="mb-0 font-13">Melihat data produk</p>
                                    </div>
                                </div>
                                <a href="index.php?changeMenu=Stok" class="stretched-link"></a>
                            </div>
                        </div>
                    </div>
                    <!-- Sejarah -->
                    <div class="col">
                        <div class="card radius-10 border-start border-0 border-3 border-warning">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <p class="mb-0 text-secondary"></p>
                                        <h4 class="my-1 text-warning">Sejarah</h4>
                                        <p class="mb-0 font-13">Melihat sejarah pembelian</p>
                                    </div>
                                </div>
                                <a href="index.php?changeMenu=Sejarah" class="stretched-link"></a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Pembelian -->
            <div class="content <?php echo ($currentMenu != "Pembelian"? "d-none": "");?>">
                <div class="row">
                    <form class = "col-12" method="post">
                        <div class="mb-3">
                            <p>Tanggal : <?php echo date("Y-m-d");?></p>
                            <p>No. Pembelian : <?php echo $countPembelian?></p>
                        </div>
                        <div class="ml-1 row">
                            <!-- Tabel Input -->
                            <table class="table table-bordered border-dark">
                                <tr>
                                    <th class="col-1 text-center border-dark">Kode Produk</th>
                                    <th class="col-3 text-center border-dark">Nama Produk</th>
                                    <th class="col-1 text-center border-dark">Jumlah</th>
                                    <th class="col-1 text-center border-dark">Action</th>
                                </tr>

                                <?php 
                                foreach($_SESSION['tempPembelian'] as $i => $x){
                                ?>
                                <tr>
                                    <td class="col-1 text-center border-dark"><?php echo $x['inputKode'];?></td>
                                    <td class="col-3 border-dark"><?php echo $x['inputNama'];?></td>
                                    <td class="col-1 text-center border-dark"><?php echo $x['inputJumlah'];?></td>
                                    <td class="col-1 text-center border-dark"><button class="btn btn-primary form-control" formaction="index.php?changeMenu=Pembelian&deleteRowPembelian=<?php echo $i;?>">Hapus</button></th>
                                </tr>
                                <?php } ?>

                                <!-- Input Row -->
                                <tr>
                                    <!-- Kode -->
                                    <td class="col-1 text-center border-dark"><?php echo $kodeUnikProduk;?></td>
                                    <!-- Nama -->
                                    <td class="col-3 border-dark">
                                        <select name="inputKode" id="" class="form-control">
                                            <?php foreach($arrProduk as $y){?>
                                                <option value="<?php echo $y['produk_id'];?>"><?php echo $y['nama'];?></option>
                                            <?php }?>
                                        </select>
                                    </td>    
                                    <!-- Jumlah -->
                                    <td class="col-1 text-center border-dark">
                                        <input type="text" class="form-control" name="inputJumlah">
                                    </td>
                                    <!-- Action -->
                                    <td class="col-1 text-center border-dark">
                                        <button type="submit" class="btn btn-primary form-control" formaction="index.php?changeMenu=Pembelian" name ="tambahRowPembelian">Tambah</button>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <button name="tambahPembelian"type="submit" class="btn btn-primary" formaction="index.php?changeMenu=Pembelian">Tambahkan</button>
                    </form>
                </div>
            </div>

            <!-- Tambah Produk -->
            <div class="content <?php echo ($currentMenu != "TambahProduk"? "d-none": "");?>">
                <div class="row">
                    <form class = "col-12" method="post">
                        <div class="mb-3">
                            <p>Nama <input type="text" class="form-control col-3" name="inputNama"></p>
                            <p>Stok <input type="text" class="form-control col-3" name="inputStok"></p>
                            <p>Harga <input type="text" class="form-control col-3" name="inputHarga"></p>
                        </div>
                        <button name="tambahProduk" type="submit" class="btn btn-primary" formaction="index.php?changeMenu=TambahProduk">Tambahkan</button>
                    </form>
                </div>
            </div>

            <!-- Stok -->
            <div class="content <?php echo ($currentMenu != "Stok"? "d-none": "");?>">
                <div class="row">
                    <!-- Table -->
                    <table class="table table-bordered">
                        <tr>
                            <th class="col-1 text-center border-dark">ID Produk</th>
                            <th class="col-3 text-center border-dark">Nama Produk</th>
                            <th class="col-1 text-center border-dark">Stok</th>
                            <th class="col-2 text-center border-dark">Harga</th>
                            <th class="col-2 text-center border-dark">Action</th>
                            <!-- <th class="col-1">Action</th> -->
                        </tr>

                        <?php 
                        foreach($arrProduk as $i => $x){
                        ?>
                        <form method="post">
                            <tr>
                                <td class="col-1 text-center border-dark"><?php echo $x['produk_id'];?></td>
                                <td class="col-3 border-dark"><input type="text" value="<?php echo $x['nama'];?>" class="form-control" name="inputNama"></td>
                                <td class="col-1 text-center border-dark"><input type="text" value="<?php echo $x['stok'];?>" class="form-control" name="inputStok"></td>
                                <td class="col-2 text-center border-dark"><input type="text" value="<?php echo $x['harga'];?>" class="form-control" name="inputHarga"></td>
                                <td class="col-2 text-center border-dark">
                                    <button type="submit" class="btn btn-primary form-control" formaction="index.php?changeMenu=Stok&editProduk=<?php echo $x['produk_id'];?>">Edit</button>
                                    <button type="submit" class="btn btn-primary form-control" formaction="index.php?changeMenu=Stok&deleteProduk=<?php echo $x['produk_id'];?>">Delete</button>  
                                </td>
                            </tr>
                        </form>

                        <?php } ?>
                    </table>
                </div>
            </div>

            <!-- Sejarah -->
            <div class="content <?php echo ($currentMenu != "Sejarah"? "d-none": "");?>">
                
                <?php 
                    foreach($arrPembelian as $i => $x){
                ?>
                <div class="row">

                    <!-- Header -->
                    <div class="col">
                        <div class="row"><h4><b>Kode Pembelian <?php echo $x['pembelian_id'];?></b></h4></div>
                        <div class="row"><?php echo $x['pembelian_tanggal'];?></div>
                        <div class="row">Total: <?php echo $x['pembelian_total'];?></div>
                    </div>

                    <!-- Table -->
                    <table class="table table-bordered">
                        <tr>
                            <th class="col-1 text-center border-dark">ID Produk</th>
                            <th class="col-3 text-center border-dark">Nama Produk</th>
                            <th class="col-1 text-center border-dark">Jumlah</th>
                            <th class="col-1 text-center border-dark">Subtotal</th>
                        </tr>

                        <?php 
                            $queryPembelianDataRow = "select * from pembeliandata left join produk on produk.produk_id = pembeliandata_produk_id where pembelian_id = ".$x['pembelian_id'];
                            $fetchPembelianDataRow = $conn->query($queryPembelianDataRow);
                            $arrPembelianDataRow = $fetchPembelianDataRow->fetch_all(MYSQLI_BOTH);
                            foreach($arrPembelianDataRow as $y){
                        ?>
                        <tr>
                            <td class="col-1 text-center border-dark"><?php echo $y['pembeliandata_produk_id'];?></td>
                            <td class="col-3 text-center border-dark"><?php echo $y['nama'];?></td>
                            <td class="col-1 text-center border-dark"><?php echo $y['pembeliandata_jumlah'];?></td>
                            <td class="col-1 text-center border-dark"><?php echo $y['pembeliandata_jumlah'] * $y['harga'];?></td>
                        </tr>

                        <?php }?>
                    </table>
                </div>


                <?php }?>

                
            </div>

            

        </div>
        
    </div>
    <!--   Core JS Files   -->
    <script src="./assets/js/core/jquery.min.js"></script>
    <script src="./assets/js/core/popper.min.js"></script>
    <script src="./assets/js/core/bootstrap.min.js"></script>
    <script src="./assets/js/plugins/perfect-scrollbar.jquery.min.js"></script>
    <!-- Chart JS -->
    <script src="./assets/js/plugins/chartjs.min.js"></script>
    <!--  Notifications Plugin    -->
    <script src="./assets/js/plugins/bootstrap-notify.js"></script>
    <!-- Control Center -->
    <script src="./assets/js/paper-dashboard.min.js?v=2.0.1" type="text/javascript"></script>
</body>

</html>
