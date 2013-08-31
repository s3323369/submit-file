<?php
   require_once('db.php');
   require_once('MiniTemplator.class.php');
   
   $t = new MiniTemplator;
   $ok = $t->readTemplateFromFile("searchScreen.html");
   if (!$ok) die ("MiniTemplator.readTemplateFromFile failed.");  
   
   if(!($connection = mysql_connect(DB_HOST, DB_USER, DB_PW)))
   {
      echo 'Could not connect to mysql on '. DB_HOST."\n";
      exit;
   }
   if(!(mysql_select_db("winestore", $connection)))
   {
      echo 'Could not connect to database winestore\n';
      exit;
   }
   $result = mysql_query("SELECT * FROM region", $connection);
   if (!$result)
   {
      echo 'Could not run query: ' . mysql_error();
      exit;
   }

   $result2 = mysql_query("SELECT * FROM grape_variety ORDER BY variety ASC", $connection);
   if (!$result2)
   {
      echo 'Could not run query: ' . mysql_error();
      exit;
   }
   $result3 = mysql_query("SELECT DISTINCT year FROM wine ORDER BY year ASC",
 $connection);
   if (!$result3)
   {
      echo 'Could not run query: ' . mysql_error();
      exit;
   }
   $result4 = mysql_query("SELECT DISTINCT year FROM wine ORDER BY year DESC",
 $connection);
   if (!$result3)
   {
      echo 'Could not run query: ' . mysql_error();
      exit;
   }
   
   while($row1 = mysql_fetch_row($result))
   {
	  $t->setVariable("region", $row1[1]);
	  $t->addBlock("region_block");
   }
   
   while($row2 = mysql_fetch_row($result2))
   { 
      $t->setVariable("variety_id",$row2[0]);
      $t->setVariable("grape_variety",$row2[1]);
      $t->addBlock("grape_block");
   }

   while($row3 = mysql_fetch_row($result3))
   {
      $t->setVariable("lowerYear", $row3[0]);
      $t->addBlock("lowerYear_block");
   }
   
   while($row4 = mysql_fetch_row($result4))
   {
      $t->setVariable("upperYear", $row4[0]);
      $t->addBlock("upperYear_block");
   }
   $t->generateOutput();
?>
