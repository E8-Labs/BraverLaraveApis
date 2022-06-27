<?php
//67c27040-907e-429f-b7b4-b13b24e73eb2
require "../Database.php";
require "../ShopieStripe.php";
$db = new Database();
$st = new ShopieStripe();

$user = $db->user;
$host = 'localhost';
//$host = 'mysql.hostinger.com';
$pass = $db->pass;
$name = $db->dbname;

// $yachtid = uniqid();

$invoiceid = $_POST["invoice_id"]; // this is the invoice message id
$reservation_id = $_POST["reservation_id"]; // this is the invoice message id
$userid = $_POST["userid"];
$amount = $_POST["amount"];

$name = 'Crypto';
if (array_key_exists("name",$_POST))
    {
        $name = $_POST["name"];
    }
$description = "";
if (array_key_exists("description",$_POST))
    {
        $description = $_POST["description"];
    }

    $conn = $db->getDBConnection();
    $sql = "select * from invoice where invoice_id = '$invoiceid' limit 1";
    $result = $conn->query($sql);
    $rows = array();
    if ($result != null && (mysqli_num_rows($result) >= 1 )){
        
        while ($row = $result->fetch_assoc()) {
            
            $rows[] = $row;
            
        }
        
    }
    else{
        
        
    }
    $charge = array();
    $message = "";
    if(count($rows) > 0){
        //already an invoice exists with this invoice id
        $dbCharge = $rows[0];
        $chargeid = $dbCharge["crypto_charge_code"];
        $charge = $st->getCryptoCharge($chargeid);
        $charge = $st->getRequiredDataFromCharge($charge);
        $timeline_status = $charge["timeline_status"];
        $payment_status = $charge["payments_status"];
        if($timeline_status == "EXPIRED" || $payment_status == "CANCELLED"){
            $charge = null;
            $message = "Charge expired or cancelled. Generating new";
        }
    }
    else{
        
    }
    
    
 
    
    if($charge == null){
        $charge = $st->createCryptoCharge($amount, $description, $name);
    }
    else{
        $message = "Charge already exists and not expired and not cancelled";
    }
    // echo "this is charge";
    $code = $charge["code"];
    $url = $charge["payment_url"];
    $charge_id = $charge["charge_id"];
    $price = $charge["price"];
    $payment_status = $charge["payments_status"];
    $timeline_status = $charge["timeline_status"];
    $sql = "INSERT INTO `invoice` (`invoice_id`, `amount`, `invoice_by`, `reservation_id`, `crypto_charge_code`, `crypto_charge_id`, `crypto_charge_url`, `payment_status`, `timeline_status`) VALUES ('$invoiceid', $amount, '$userid', '$reservation_id', '$code', '$charge_id', '$url', '$payment_status', '$timeline_status') ON DUPLICATE KEY Update payment_status = '$payment_status', timeline_status='$timeline_status', crypto_charge_code = '$code', crypto_charge_id = '$charge_id', crypto_charge_url='$url'";
    
    if($conn->query($sql) == true){
        echo (json_encode(["data" => $charge, 'status'=>'1', 'message' => "Crypto charge created", "Sql" => $sql, "Charge_Message" => $message], JSON_PRETTY_PRINT));
    }
    else{
        echo (json_encode(["data" => null, 'status'=>'0', 'message' => "Crypto charge not created", "Charge_Message" => $message, "Post" => $_POST, "charge" => $charge, "sql" => $sql], JSON_PRETTY_PRINT));
    }
    
    

    die();







?>