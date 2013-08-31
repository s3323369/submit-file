<html>
<head>
<title>Winestore search results</title>
</head>
<?php
   require_once('db.php');
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
   if($minCost != "")
   {
      if(!is_numeric($minCost))
      {
         echo "Min cost should be numeric";
         exit;
      }
      if($maxCost != "")
      {
         if(!is_numeric($maxCost))
         {
            echo "Max cost should be numeric";
            exit;
         }
         if($minCost <= $maxCost)
         {
            $query .= " AND cost <= {$maxCost}";
            $query .= " AND cost >= {$minCost}";
         }
         else
         {
            echo "min cost should be lower than max cost\n";
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

   $query .= " GROUP BY items.wine_id";
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
   $result = mysql_query("$query", $connection);
   if(!$result)
   {
      echo 'Could not run query: ' . mysql_error();
      exit;
   }   
?>
<body>
<?php
   if(mysql_num_rows($result))
   {
?>
<table>
<tr>
<td>Name </td>
<td>Grape Variety </td>
<td>Year </td>
<td>Winery </td>
<td>Region </td>
<td>Cost of the wine in the inventory </td>
<td>Total number of bottles available at any price </td>
<td>Total stock sold of the wine </td>
<td>Total sales revenue for the wine </td>
</tr>
<?php
while($row = @mysql_fetch_array($result))
{  
   $query2 = "SELECT wine_variety.variety_id, variety
              FROM wine_variety, grape_variety
              WHERE wine_variety.variety_id = grape_variety.variety_id";

   $wineID = $row["wine_id"];
   $query2 .= " AND wine_variety.wine_id = '{$wineID}'";
   $result2 = mysql_query("$query2", $connection);
   if(!$result2)
   {
      echo 'Could not run query: ' . mysql_error();
      exit;
   }
   $varietylist = "";
   while($row2 = @mysql_fetch_array($result2))
   {
      if($varietylist != "")
      {
         $varietylist .= ", ";
      }
      $varietylist .= $row2["variety"];      
   }
   echo "\n<tr>\n<td>{$row["wine_name"]}</td>"
   ."\n<td>{$varietylist}</td>"
   ."\n<td>{$row["year"]}</td>"
   ."\n<td>{$row["winery_name"]}</td>"
   ."\n<td>{$row["region_name"]}</td>"
   ."\n<td>{$row["cost"]}</td>"
   ."\n<td>{$row["on_hand"]}</td>"
   ."\n<td>{$row["orders"]}</td>"
   ."\n<td>{$row["totalprice"]}</td>\n</tr>";
}
   }
   else
   {
      echo "No records match the search criteria";
   }
?>
</table>
</body>
</html>
