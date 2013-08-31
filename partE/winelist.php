<?php
   require_once('MiniTemplator.class.php');
   $t = new MiniTemplator;
   $ok = $t->readTemplateFromFile("winelist.html");
   if (!$ok) die ("MiniTemplator.readTemplateFromFile failed.");
   //for twitter
   require_once('twitterOAuth/twitteroauth.php');
   // Twitter Connection Info
   $twitter_access_token = '1714604370-1R4L93KvCnpfPsoAxU4p4wR0PBorC1Vv24HuxJr';
   $twitter_access_token_secret = 'JRzyJq3ED0L975hlVK72RXSTyD3MKVgRIDJfrQHU';
   $twitter_consumer_key = 'Kjrv329YCn7MbmPyK64g';
   $twitter_consumer_secret = 'sJeeCD8YHXalsBVk0e5rUwJS5rvcI0VMxT7tr3zeDh4';
   // Connect to Twitter
   $connection = new TwitterOAuth($twitter_consumer_key, $twitter_consumer_secret, $twitter_access_token, $twitter_access_token_secret);
   // Post Update
  // $content = $connection->get('account/verify_credentials');
//   $connection->post('statuses/update', array('status' => 'Test Tweet'));
 
   session_start();
   if($_SESSION['session'] == "active" && (isset($_SESSION['wines'])))
   {
      $t->setVariable("back_button", "<a href='searchScreen.php'>Back to search screen</a>");
      $t->addBlock("back_block");
      $i=1;
      foreach($_SESSION['wines'] as &$name)
      {
         $t->setVariable("results", $i.") ".$name);
         $t->addBlock("results_block");         
         $i+=1;
      }
   }
   else
   {
      header('Location: searchScreen.php');
   }
   $t->generateOutput();
?>
