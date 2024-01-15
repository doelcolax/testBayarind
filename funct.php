<?php


$kemungkinanBayar = array();
$pecahanUang = array(100, 200, 500, 1000, 2000, 5000, 10000, 20000, 50000, 100000);


function cariPecahan($nilaiUang, $index, $kombinasi)
{
    $count = 0;
    global $output,$pecahanUang;
    if ($nilaiUang == 0) {
        $count++;
        $output[]=array_count_values($kombinasi);
       
    }

    for ($i = $index; $i < count($pecahanUang); $i++) {
        if ($nilaiUang >= $pecahanUang[$i]) {
            $kombinasi[] = $pecahanUang[$i];
            cariPecahan($nilaiUang - $pecahanUang[$i], $i, $kombinasi);
            array_pop($kombinasi);
        }
    }
 
    return $output;
}


function detailPecahan ($totalBelanja) {
    global $pecahanUang;
    switch (true) {

        case  $totalBelanja >= 100  && $totalBelanja <= 1000:
            $pecahanUang = array(100000, 50000, 20000, 10000, 5000,2000,1000,500, 200,100);
            break;
        case  $totalBelanja > 1000  && $totalBelanja <= 5000 :
            $pecahanUang = array(100000, 50000, 20000, 10000, 5000,2000,1000,500);
            break;
        case  $totalBelanja > 5000  && $totalBelanja <= 10000 :
            $pecahanUang = array(100000, 50000, 20000, 10000, 5000,2000,1000);
            break;
        case  $totalBelanja > 10000  && $totalBelanja <= 50000 :
                $pecahanUang = array(100000, 50000, 20000, 10000, 5000,2000);
                break;
        case  $totalBelanja > 50000  && $totalBelanja < 100000 :
                $pecahanUang = array(100000, 50000, 20000, 10000, 5000);
                break;
    }

    $data = cariPecahan($totalBelanja,  0, array());
    foreach ($data as $innerArray) {
        $tempArray = array();
        
        foreach ($innerArray as $key => $value) {
             
                if($key === 1000) {
                    $satuan = "lembar/koin";
                }elseif($key > 1000) {
                    $satuan = "lembar";
                }else{
                    $satuan = "koin";
                }
            
            $dat= $value.  " ". $satuan . " Rp. " . $key ;
            array_push($tempArray,$dat);
        }
        // $outputArray[] = $tempArray;
        $sad = implode(" <br> ",$tempArray);
        $outputArray[]['detail'] = $sad;
    }
    echo json_encode($outputArray);
}




function kemungkinanNominal($nilaiBelanja) {
    global $pecahanUang,$kemungkinanBayar;
    $arrLebihBesar = array();
 
    if ($nilaiBelanja >= 100000) {
        array_push($kemungkinanBayar,0);
        $arr1 = rebuildArray($kemungkinanBayar,1);
        return json_encode($arr1);
    }

     //CARI DALAM MATA UANG YG NOMINALNYA LEBIH BESAR DARI TOTAL BELANJA TAMPUNG KE DALAM ARRAY arrLebihBesar UNTUK DIPAKAI NANTI
     $arrLebihBesar = array_values(array_filter($pecahanUang, function($el) use ($nilaiBelanja) {
        return $el > $nilaiBelanja;
     }));


    $nPembulatan =cariPembulat($nilaiBelanja);  //CARI NILAI PEMBULATAN BERDSARKAN INPUTAN USER
    $pembulatan = ceil($nilaiBelanja / $nPembulatan) * $nPembulatan; // PEMBULATAN NILAI BELANJA MENNGACU PADA VALUE $nPembulatan
   
     //INSERT NLAI 0 DI INDEX AWAL array KemungkinanBayar Untuk di Olah di sisi VIEW Sebaggi UANG PAS
    array_push($kemungkinanBayar,0);


      /// INSERT DULU NILAI PEMBULATANYA KEDALAM Array kemungkinanBayar JIKA NILAINYA SAMA JANGAN DIINSERT 
    //  Ex. 1250 AKAN DIBULATKAN 1300 MASUK KE DALAM ARRAY KEMUNGKINAN BAYAR. NAMUN JIKA 1000 KAREANA TIDAK ADA PEMBULTAB JANGAN DIINSERT
    if ($pembulatan != $nilaiBelanja  && $pembulatan != $arrLebihBesar[0]) {
        array_push($kemungkinanBayar,$pembulatan);
    }

    //ULANGI PROSES PEMBUALTAANNYA
    $output = ceil($pembulatan / $nPembulatan) * $nPembulatan; // PEMBULATAN NILAI BELANJA MENNGACU PADA VALUE $nPembulatan
    while( $output < $arrLebihBesar[0]) {
        $nPembulatan =cariPembulat($output); 
     if ($output != $pembulatan && $output != $arrLebihBesar[0] ) {
         array_push($kemungkinanBayar,$output);
     }
     

     $output += $nPembulatan;
 
    }

    //REBUILD ARRAY kemungkinanBayar dan arrLebihBesar AGAR PENGOLAHAN DATA DISIS VIEW LEBIH MUDAH
    $arr1 = rebuildArray($kemungkinanBayar,1);
    $arr2 =rebuildArray($arrLebihBesar,0);
    $posibility =array_merge($arr1,$arr2); // gabungkan array kemungkana dan array nominal lebih besar dari nilai belnajanya

    return json_encode($posibility);
   
  
}


function cariPembulat ($nilaiBelanja) {
    //SEMI HARDCODE UNTUK MENGHTIUNG KELIPATANYA DITIAP NOMINAL
    //SEKLIAGUS UNTUK MEMBATASI NOMINAL YG KAN DIKELUARKAN TIDAK HARUS DARI PECAHAN TERKECIL (100)
    // HARUSNY DIKEMBANGKAN LAGI DENGAN MEMBANDINGKAN TIAP NLAI PECAHAN
    switch (true) {
        case  $nilaiBelanja >= 1000  && $nilaiBelanja <= 5000 :

                if (substr($nilaiBelanja,-3)>0){
                    return 500; // JIKA NILAI BELANJA DIATAS 1000 dan DIBAWAH 10.0000 DAN TERDAPAT PECAHAN 3 DIGIT DIBELAKANGNYA MKA NILAI PEMBUALTAN 500
                }else{
                    return 1000; // JIKA NILAI BELANJA DIATAS 1000 dan DIBAWAH 10.0000 DAN  TIDAK TERDAPAT PECAHAN  MKA NILAI PEMBUALTAN 500
                }
            break;
        case  $nilaiBelanja >= 5000  && $nilaiBelanja <= 10000 :
            if (substr($nilaiBelanja,-3)>0){
                return 1000;
            }else{
                return 5000;
            }
           
            break;
        case  $nilaiBelanja > 10000  && $nilaiBelanja < 20000 :
            if (substr($nilaiBelanja,-3)>0){
                return 1000;
            }else{
                return 5000;
            }
            
            break;

            case  $nilaiBelanja >= 20000  && $nilaiBelanja < 50000 :
                if (substr($nilaiBelanja,-3)>0){
                    return 5000;
                }else{
                    return 10000;
                }
                
                break;
        case  $nilaiBelanja >= 50000  && $nilaiBelanja < 100000 :
            if (substr($nilaiBelanja,-3)>0){
                return 5000;
            }else{
                return 20000;
            }
               
            break;

        default :
            return 100;
            break;
    }
}

function rebuildArray ($arr,$jenis) {
    $newData = [];
    for ($i = 0; $i < count($arr); $i++) {
       $newData[$i] = array(
          "nominal" => $arr[$i],
          "jenis" => $jenis
        );
    }

    return $newData;
}


if (isset($_POST['kemungkinanNominal'])) {
    echo kemungkinanNominal($_POST['kemungkinanNominal']);
}

if (isset($_POST['detailPecahan'])) {
    echo detailPecahan($_POST['detailPecahan']);
}

?>