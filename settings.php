<?php
//modulo delle KEYs per funzionamento dei bot (da non committare)

//Telegram
define('TELEGRAM_BOT','132929956:AAG7zRrYPqkmrTBf0Lu48z4PZCmgYGp3RKE');
define('BOT_WEBHOOK', 'https://teo-soft.com/132929956:AAG7zRrYPqkmrTBf0Lu48z4PZCmgYGp3RKE/start.php');
define('LOG_FILE', 'telegram.log');

// Your database
$db_path=dirname(__FILE__).'/./db.sqlite';
define ('DB_NAME', "sqlite:". $db_path);
define('DB_TABLE',"user");
define('DB_TABLE_GEO',"segnalazioni");
define('DB_CONF', 0666);
define('DB_ERR', "errore database SQLITE");

// Your Openstreetmap Query settings
define('AROUND', 2000);						//Number of meters to calculate radius to search
define('MAX', 3);							//max number of points to search
define('TAG','"tourism"="museum"');			//tag to search accoring to Overpass_API Query Language

?>
