<?php
   require_once('db.php');
   require_once('MiniTemplator.class.php');
   
   $t = new MiniTemplator;
   $ok = $t->readTemplateFromFile("searchScreen.html");
   if (!$ok) die ("MiniTemplator.readTemplateFromFile failed.");  
   try
   {
      $dsn = DB_ENGINE .':host='. DB_HOST .';dbname='. DB_NAME;
      $pdo = new PDO($dsn, DB_USER, DB_PW);
      //all errors will throw exceptions
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $query1 = 'SELECT * FROM region'; 
      $result1 = $pdo->query($query1);
      
      $query2 = 'SELECT * FROM grape_variety ORDER BY variety ASC';
      $result2 = $pdo->query($query2);

      $query3 = 'SELECT DISTINCT year FROM wine ORDER BY year ASC';
      $result3 = $pdo->query($query3);
   
      $query4 = 'SELECT DISTINCT year FROM wine ORDER BY year DESC';
      $result4 = $pdo->query($query4);
  
      foreach ($result1 as $row1)
      {
         $t->setVariable("region", $row1['region_name']);
	 $t->addBlock("region_block");
      }
   
      foreach ($result2 as $row2)
      { 
         $t->setVariable("variety_id",$row2['variety_id']);
         $t->setVariable("grape_variety",$row2['variety']);
         $t->addBlock("grape_block");
      }

      foreach ($result3 as $row3)
      {
         $t->setVariable("lowerYear", $row3['year']);
         $t->addBlock("lowerYear_block");
      }
   
      foreach ($result4 as $row4)
      {
         $t->setVariable("upperYear", $row4['year']);
         $t->addBlock("upperYear_block");
      }   
      $pdo=null;
   }
   catch(PDOException $e)
   {
      echo $e->getMessage();
      exit;
   }
   $t->generateOutput();
?>
