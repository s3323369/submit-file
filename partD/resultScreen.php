<?php
   require_once('db.php');
   require_once('MiniTemplator.class.php');

   $t = new MiniTemplator;
   $ok = $t->readTemplateFromFile("resultScreen.html");
   if (!$ok) die ("MiniTemplator.readTemplateFromFile failed."); 
   try
   {
      $dsn = DB_ENGINE .':host='. DB_HOST .';dbname='. DB_NAME;     
      $pdo = new PDO($dsn, DB_USER, DB_PW);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   
      $wineName = $_GET['wineName'];
      $wineryName = $_GET['wineryName'];
      $regionName = $_GET['regionTable'];
      $grapeVariety = $_GET['grapeTable'];
      $minYear = $_GET['minYearTable'];
      $maxYear = $_GET['maxYearTable'];
      $minStock = $_GET['minStock'];
      $minOrdered = $_GET['minOrdered'];
      $minCost = $_GET['minCost'];
      $maxCost = $_GET['maxCost'];
      $query = "SELECT wine.wine_id, wine_name, year,
	     winery_name, region_name, on_hand, cost, SUM(qty) AS orders,
	     SUM(price) AS totalprice
                  FROM wine, winery, region, wine_variety,
		  inventory, items
                  WHERE wine.winery_id = winery.winery_id
		  AND region.region_id = winery.region_id
	          AND inventory.wine_id = wine.wine_id
		  AND items.wine_id = wine.wine_id
		  AND wine_variety.wine_id = wine.wine_id";

      if($wineName != "")
      {
         $query .= " AND wine_name LIKE '%{$wineName}%'";
      }
      if($wineryName != "")
      {
         $query .= " AND winery_name LIKE '%{$wineryName}%'";
      }
      if($regionName != "" && $regionName != "All")
      {
         $query .= " AND region_name = '{$regionName}'";
      }
      if($grapeVariety != "" && $grapeVariety != "All")
      {
         $query .= " AND variety_id = '{$grapeVariety}'";
      }
      if($minYear<=$maxYear)
      {
         $query .= " AND year <= {$maxYear}";
         $query .= " AND year >= {$minYear}";
      }
      else
      {
         echo "Min year must be smaller than max year\n";
         exit;
      }
      if($minCost != "")
      {
         if(!is_numeric($minCost))
         {
            echo "Min cost must be numeric";
            exit;
         }
         if($maxCost != "")
         {
            if(!is_numeric($maxCost))
            {
               echo "Max cost must be numeric";
               exit;
            }
            if($minCost <= $maxCost)
            {
               $query .= " AND cost <= {$maxCost}";
               $query .= " AND cost >= {$minCost}";
            }
            else
            {
               echo "Min cost should be lower than max cost\n";
               exit;
            }
         }
         else
         {
            $query .= " AND cost >= {$minCost}";
         }
      }
      else if($maxCost != "")
      {
         if(!is_numeric($maxCost))
         {
            echo "Max cost should be numeric";
            exit;
         }
         $query .= " AND cost <= {$maxCost}";
      }
      if($minStock != "")
      {
         if(!is_numeric($minStock))
         {
            echo "Min stock should be numeric";
            exit;
         }
         $query .= " AND on_hand >= {$minStock}";
      }
      $query .= " GROUP BY wine.wine_id";
      if($minOrdered != "")
      {
         if(!is_numeric($minOrdered))
         {
            echo "Min orders should be numeric";
            exit;
         }
         $query .= " HAVING orders >= {$minOrdered}";
      }
      $query .= " ORDER BY wine_name ASC";
      
      $result = $pdo->query($query);      
      if($result->rowCount() > 0)
      {
         $t -> addBlock("table_block");

         foreach($result as $row)
         {
            $query2 ="SELECT wine_variety.variety_id, variety
	              FROM wine_variety, grape_variety
		      WHERE wine_variety.variety_id = grape_variety.variety_id";
     
	    $wineID = $row["wine_id"];
            $query2 .= " AND wine_variety.wine_id = '{$wineID}'";
            $result2 = $pdo->query($query2);
           
	    $varietylist = "";
            foreach($result2 as $row2)
	    {
	       if($varietylist != "")
	       {
	          $varietylist .= ", ";
	       }
	       $varietylist .= $row2["variety"];
	    }
            $t -> setVariable("wine_name",$row["wine_name"]);
            $t -> setVariable("variety",$varietylist);
            $t -> setVariable("year",$row["year"]);
            $t -> setVariable("winery_name",$row["winery_name"]);
            $t -> setVariable("region_name",$row["region_name"]);
            $t -> setVariable("cost",$row["cost"]);
            $t -> setVariable("on_hand", $row["on_hand"]);
            $t -> setVariable("orders",$row["orders"]);
            $t -> setVariable("totalprice",$row["totalprice"]);
            $t -> addBlock("results_block");
         }  
      }
      else
      {
         echo "No records match the search criteria";
      }
      $pdo=null;
   }
   catch(PDOException $e)
   {
      echo $e->getMessage();
      exit;
   }
   $t -> generateOutput();   
?>
