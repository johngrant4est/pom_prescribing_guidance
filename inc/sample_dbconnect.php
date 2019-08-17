<?php
  // sample db connect file - craete a user and pw with appropriate acces to your DB
  $user = '';
  $pw = '';
try {
  $dbh = new PDO('mysql:host=localhost;dbname=',$user,$pw);
} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
}
?>
