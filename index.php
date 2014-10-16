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

    $app->group('/phone', function () use ($app) {
    	
    	$app->get('/create', 'PHONE:create');
    	$app->get('/recover', 'PHONE:recover');

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

	
?>