<?php
/**
 * Telegram Bot example per ricerca luoghi (l'esempio è per i musei) nei dintorni tramite piattaforma openstreetmap
 * @author Matteo Tempestini 
	Funzionamento
	- invio location
	- risposta dai dati openstreetmap
 */
include("Telegram.php");
include("QueryLocation.php");

class mainloop{
	public $log=LOG_FILE;
	
 function start($telegram,$update)
	{

		date_default_timezone_set('Europe/Rome');
		$today = date("Y-m-d H:i:s");
		
		// Instances the class
		$db = new PDO(DB_NAME);

		/* If you need to manually take some parameters
		*  $result = $telegram->getData();
		*  $text = $result["message"] ["text"];
		*  $chat_id = $result["message"] ["chat"]["id"];
		*/
		
		$text = $update["message"] ["text"];
		$chat_id = $update["message"] ["chat"]["id"];
		$user_id=$update["message"]["from"]["id"];
		$location=$update["message"]["location"];
		
		$this->shell($telegram,$db,$text,$chat_id,$user_id,$location);

	}

	//gestisce l'interfaccia utente
	 function shell($telegram,$db,$text,$chat_id,$user_id,$location)
	{
		date_default_timezone_set('Europe/Rome');
		$today = date("Y-m-d H:i:s");

		if ($text == "/start") {
				$reply = utf8_encode("Ciao! Questo robot ti indica i musei attorno alla tua posizione.
							Invia la tua posizione tramite apposita molletta che trovi in basso a sinistra nella chat.
							Tutti i dati sono prelevati da Openstreetmap.Data in licenza ODbL.
							© OpenStreetMap contributors
							http://www.openstreetmap.org/copyright");
				$content = array('chat_id' => $chat_id, 'text' => $reply);
				$telegram->sendMessage($content);
				$log=$today. ";new chat started;" .$chat_id. "\n";
			}
			//gestione invio posizione
			elseif($location!=null)
			{
				$this->location_manager($db,$telegram,$user_id,$chat_id,$location);
				$log=$today. ";location command sent;" .$chat_id. "\n";
			}
			//comando errato
			else{
				 $reply = utf8_encode("Hai selezionato un comando non previsto, questo robot ti indica i musei attorno alla tua posizione. 
							Invia la tua posizione tramite l'apposita molletta che trovi in basso a sinistra nella chat. 
							Tutti i dati sono prelevati da Openstreetmap. Data in licenza ODbL.
							© OpenStreetMap contributors http://www.openstreetmap.org/copyright");
				 $content = array('chat_id' => $chat_id, 'text' => $reply);
				 $telegram->sendMessage($content);
				 $log=$today. ";wrong command sent;" .$chat_id. "\n";
			 }		
			//aggiorna tastiera
			//$this->create_keyboard($telegram,$chat_id);
			
			//for debug
			//$this->location_manager($db,$telegram,$user_id,$chat_id,$location);
			
			//aggiorna log			
			file_put_contents(LOG_FILE, $log, FILE_APPEND | LOCK_EX);
			
	}


	// Crea la tastiera
	 function create_keyboard($telegram, $chat_id)
		{
				//TBD
		}
		
	function location_manager($db,$telegram,$user_id,$chat_id,$location)
		{
				$lon=$location["longitude"];
				$lat=$location["latitude"];
				
				//for debug Prato coordinates
				//$lon=11.0952;
				//$lat=43.8807;
				
				//prelevo dati da OSM sulla base della mia posizione
				$osm_data=give_osm_data($lat,$lon);
				//echo $osm_data;
				
				//rispondo inviando i dati di Openstreetmap
				$osm_data_dec = simplexml_load_string($osm_data);
				
				//per ogni nodo prelevo coordinate e nome
				foreach ($osm_data_dec->node as $osm_element) {

					foreach ($osm_element->tag as $key) {
						if ($key['k']=='name')
						{
							$nome=utf8_encode($key['v']);
							$content = array('chat_id' => $chat_id, 'text' =>$nome);
							$telegram->sendMessage($content);
						}
					} 
					$content_geo = array('chat_id' => $chat_id, 'latitude' =>$osm_element['lat'], longitude =>$osm_element['lon']);
					$telegram->sendLocation($content_geo);
					//print_r($osm_element['lat']);
					//print_r($osm_element['lon']);
				 } 
				
				//crediti dei dati
				$content = array('chat_id' => $chat_id, 'text' => utf8_encode("questi sono i musei più vicini (dati forniti tramite OpenStreetMap. Licenza ODbL © OpenStreetMap contributors)"));
				$bot_request_message=$telegram->sendMessage($content);				
				
				//memorizzare nel DB
				$obj=json_decode($bot_request_message);
				$id=$obj->result;
				$id=$id->message_id;
				$statement = "INSERT INTO ". DB_TABLE_GEO. " (lat,lng,user,text,bot_request_message) VALUES ('" . $lat . "','" . $lon . "','" . $user_id . "',' ','". $id ."')";
            	$db->exec($statement);
		}
		
		
}

?>
