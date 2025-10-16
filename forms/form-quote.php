<?php

if (isset($_POST['sendMailbtn'])) {
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $company = $_POST['company'];
    $companySize = $_POST['selectSize'];
    $companyService = $_POST['selectService'];
    $companyBudget = $_POST['selectBudget'];
    $comment = $_POST['comment'];
    $name = $fname .' '.$lname;

    $encoding = "utf-8";

    // Preferences for Subject field
    $subject_preferences = array(
        "input-charset" => $encoding,
        "output-charset" => $encoding,
        "line-length" => 76,
        "line-break-chars" => "\r\n"
    );

echo "<pre>";
print_r($_POST);
echo '</pre>';



 $subject = $companyService; 
 $message = "<b>Customer:</b>$name</br><b>Phone:</b>$phone</br><b>Company Name:</b> $company<br><b>Company Size:</b> $companySize<br><b>Budget:</b> $companyBudget<br><b>Comments:</b> $comment<br>";
 $to = "support@izendestudioweb.com"; 

// Mail header
$header = "Content-type: text/html; charset=".$encoding." \r\n";
$header .= "From: ".$name."<".$email."> \r\n";
$header .= "MIME-Version: 1.0 \r\n";
$header .= "Content-Transfer-Encoding: 8bit \r\n";
$header .= "Date: ".date("r (T)")." \r\n";
$header .= iconv_mime_encode("Subject", $subject);
mail($to, $subject, $message, $header);
}
echo '<script>window.location.href="http://izendestudioweb.com/quote.php";</script>';
?>
