<?php
  require 'vendor/autoload.php';

  date_default_timezone_set('America/New_York');

  $app = new \Slim\Slim(array(
    'debug' => true
  ));

  $app->setName('fugu');

  //Routes
  $app->get('/', 'home');


  $app->group('/api', function () use ($app) {

    $app->group('/state', function () use ($app) {
    	$app->get('/create', 'STATE:create');
    	$app->get('/recover', 'STATE:recover');
    });

    $app->group('/phone', function () use ($app) {
      $app->get('/create', 'PHONE:create');
      $app->get('/recover', 'PHONE:recover');
    });

    $app->group('/coinbase', function () use ($app) {

    	$app->post('/create', 'COINBASE:create');
    	$app->get('/address', 'COINBASE:address');

    });

  });

  $app->contentType('application/json');

  # lets go
  $app->run();

  function home() {
    echo "Hello";
  }

  /**
  * ATM
  */
  class STATE
  {
    
    function create()
    {

      header('Access-Control-Allow-Origin: *');
      header('Content-type: application/json;');

      try {

        $json = array('sessionID' => 'qui', 'licenseId' => 'alla');

        $file = fopen('data.json','w');
        fwrite($file, json_encode($json));
        fclose($file);

        $status = '200';
         
      } catch(PDOException $e) {
        $status = '500';
        print 'Exception : '.$e->getMessage();
      }

      $response = array(
        'status' => $status
      );

      echo json_encode($response);
    }

    function recover()
    {

      header('Access-Control-Allow-Origin: *');
      header('Content-type: application/json;');
      
      try {
        $string = file_get_contents("data.json");
        $json_a = json_decode($string, true);
        $status = '200';
      } catch (PDOException $e) {
        $status = '500';
        print 'Exception : '.$e->getMessage();
      }

      $response = array(
        'status' => $status,
        'data' => $json_a
      );

      echo json_encode($response);

    }
  }


	class PHONE
	{
		
		function create()
		{

			header('Access-Control-Allow-Origin: *');
  		header('Content-type: application/json;');

			try {
				$phone = $_GET['phone'];
				$db = new PDO('sqlite:phone.sqlite');

				$db->exec("CREATE TABLE customer (Id INTEGER PRIMARY KEY, phone TEXT)"); 

				$delete = $db->prepare("DELETE FROM customer"); 
				$delete->execute();
				
				$db->exec("CREATE TABLE customer (Id INTEGER PRIMARY KEY, phone TEXT)");   
				$db->exec("INSERT INTO customer (phone) VALUES ($phone);");
				$status = '200';
			} catch(PDOException $e) {
				$status = '500';
				print 'Exception : '.$e->getMessage();
			}

			$response = array(
        'status' => $status
      );

      echo json_encode($response);

		}

		function recover()
		{

			header('Access-Control-Allow-Origin: *');
  		header('Content-type: application/json;');
			
			try {

				$dbh = new PDO('sqlite:phone.sqlite'); 
				$stmt = $dbh->prepare("SELECT phone FROM customer ORDER BY Id DESC LIMIT 1"); 
				$stmt->execute(); 
				$row = $stmt->fetch();

				$status = '200';
				$phone = $row['phone'];

				$delete = $dbh->prepare("DELETE FROM customer"); 
				$delete->execute(); 
				
			} catch (PDOException $e) {
				$status = '500';
				print 'Exception : '.$e->getMessage();
			}

			$response = array(
        'status' => $status,
        'phone' => $phone
      );

			echo json_encode($response);

		}

	} 

	/**
	* COINBASE
	*/
	class COINBASE
	{
		
		function create()
		{
			header('Access-Control-Allow-Origin: *');
  		header('Content-type: application/json;');

  		try {
  			
  			$status = '200';
  			$email = $_POST['email'];
  			$password = $_POST['password'];

  			$url = 'http://api.l3t.in/v1/atm/coinbase/newuser';
				$data = array('email' => $email, 'password' => $password);
				$options = array(
				  'http' => array(
				    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
				    'method'  => 'POST',
				    'content' => http_build_query($data),
				  ),
				);
				$context  = stream_context_create($options);
				$result = file_get_contents($url, false, $context);

				$result = json_decode($result, TRUE);

				if ($result['status'] == '200') {
					$dbh = new PDO('sqlite:coinbase.sqlite');
					$status = '200';
					$address = $result['data']['address'];

					$dbh->exec("CREATE TABLE account (Id INTEGER PRIMARY KEY, address TEXT)");   

					$delete = $dbh->prepare("DELETE FROM account"); 
					$delete->execute();

					$dbh->exec("CREATE TABLE account (Id INTEGER PRIMARY KEY, address TEXT)");   
					$stmt = $dbh->prepare("INSERT INTO account (address) VALUES (?)");
 					$stmt->bindParam(1, $address);
 					$stmt->execute();

					$response = array(
        		'status' => $status,
        		'address' => $address
      		);
				} elseif ($result['status'] == '500') {
					if ($result['data']['error'] == 'Email is not available') {
						$status = '500';

						$response = array(
	        		'status' => $status,
	        		'error' => 'Email is not available'
	      		);
					}
				} else {
					$status = '500';

					$response = array(
        		'status' => $status,
        		'error' => 'Try again'
      		);
				}

  		} catch (PDOException $e) {
				$status = '500';

				$response = array(
        	'status' => $status,
        	'error' => $e->getMessage()
      	);
			}

			echo json_encode($response);
		}

		function address()
		{
			header('Access-Control-Allow-Origin: *');
  		header('Content-type: application/json;');

  		try {
  			$dbh = new PDO('sqlite:coinbase.sqlite'); 
				$sdsdsdd = $dbh->prepare("SELECT address FROM account ORDER BY Id DESC LIMIT 1"); 
				$sdsdsdd->execute(); 
				$row = $sdsdsdd->fetch();

				$status = '200';
				$address = $row['address'];

				if($address === NULL) {
					$status = '500';

					$response = array(
	        	'status' => $status,
	        	'error' => 'No address'
	      	);
				} else {
					$response = array(
        		'status' => $status,
        		'address' => $address
      		);
				}

				$delete = $dbh->prepare("DELETE FROM account"); 
				$delete->execute(); 

  		} catch (PDOException $e) {
				$status = '500';

				$response = array(
        	'status' => $status,
        	'error' => $e->getMessage()
      	);
      }

      echo json_encode($response);
		}
	}
	
?>