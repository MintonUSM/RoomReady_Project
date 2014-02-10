<?php
/*
	Our database info has been removed for obvious reasons. This script is how the Android app communicated
	with the web based database.
*/
$hostname = "";
$username = "";
$password = "";
$dbname = "";
$con = mysqli_connect($hostname, $username, $password, $dbname) or die("Unable to connect");


$obj = json_decode(file_get_contents("php://input"));
$method = $obj -> method;

//Login method
if( $method == "LogIn")
{
	$id = $obj -> employee_id;
	$pass = $obj -> login_pass;
	$array = array();
	
	$result = mysqli_query($con, "SELECT login_pass
								  FROM employee
								  WHERE employee_id = '$id'");
	
	while($row = mysqli_fetch_assoc($result))
	{
		$array[] = $row;
	}
	
	if(($array[0]['login_pass'] == $pass) and ($pass != ''))
	{
		$jsonReturn = '{ "logIn" : "true" }';
		print $jsonReturn;
	}
	else
	{
		$jsonReturn = '{ "logIn" : "false" }';
		print $jsonReturn;
	}
}

//Main Search method
if( $method == "MainSearch")
{
	$search = $obj -> searchString;
	$array = array();
	
	if($search == "Arrivals")
	{
		$result = mysqli_query($con, "SELECT last_name, first_name, all_rooms.lot_id, arrive_date, depart_date, book_hist.reservation_id, hotel_guest.home_phone, email, corporation, book_hist.guest_status
									  FROM all_rooms
									  LEFT JOIN booking_rooms ON all_rooms.lot_id = booking_rooms.lot_id
									  LEFT JOIN book_hist ON booking_rooms.reservation_id = book_hist.reservation_id
									  LEFT JOIN hotel_guest ON book_hist.guest_id = hotel_guest.guest_id
									  WHERE arrive_date = CURRENT_DATE");
	
		while($row = mysqli_fetch_assoc($result))
		{
			$array[] = $row;
		}
		print json_encode($array);
	}
	
	if($search == "Departures")
	{
		$result = mysqli_query($con, "SELECT last_name, first_name, all_rooms.lot_id, arrive_date, depart_date, book_hist.reservation_id, hotel_guest.home_phone, email, corporation, book_hist.guest_status
									  FROM all_rooms
									  LEFT JOIN booking_rooms ON all_rooms.lot_id = booking_rooms.lot_id
									  LEFT JOIN book_hist ON booking_rooms.reservation_id = book_hist.reservation_id
									  LEFT JOIN hotel_guest ON book_hist.guest_id = hotel_guest.guest_id
									  WHERE depart_date = CURRENT_DATE");
	
		while($row = mysqli_fetch_assoc($result))
		{
			$array[] = $row;
		}
		print json_encode($array);
	}
	
	if($search == "InHouse")
	{
		$result = mysqli_query($con, "SELECT last_name, first_name, all_rooms.lot_id, arrive_date, depart_date, book_hist.reservation_id, hotel_guest.home_phone, email, corporation, book_hist.guest_status
									  FROM all_rooms
									  LEFT JOIN booking_rooms ON all_rooms.lot_id = booking_rooms.lot_id
									  LEFT JOIN book_hist ON booking_rooms.reservation_id = book_hist.reservation_id
									  LEFT JOIN hotel_guest ON book_hist.guest_id = hotel_guest.guest_id
									  WHERE guest_status = 'In House';");
	
		while($row = mysqli_fetch_assoc($result))
		{
			$array[] = $row;
		}
		print json_encode($array);		
	}
	
	if($search == "All")
	{
		$result = mysqli_query($con, "SELECT last_name, first_name, all_rooms.lot_id, arrive_date, depart_date, book_hist.reservation_id, hotel_guest.home_phone, email, corporation, book_hist.guest_status 
									  FROM all_rooms
									  LEFT JOIN booking_rooms ON all_rooms.lot_id = booking_rooms.lot_id
									  LEFT JOIN book_hist ON booking_rooms.reservation_id = book_hist.reservation_id
									  LEFT JOIN hotel_guest ON book_hist.guest_id = hotel_guest.guest_id
									  WHERE guest_status = 'In House'
									  OR depart_date = CURRENT_DATE
									  OR arrive_date = CURRENT_DATE
									  OR check_out = CURRENT_DATE");
	
		while($row = mysqli_fetch_assoc($result))
		{
			$array[] = $row;
		}
		print json_encode($array);	
	}
}

if( $method == "Totals")
{
	$array = array();
	
	$result = mysqli_query($con, "SELECT COUNT(DISTINCT all_rooms.lot_id) AS room_remaining
								  FROM all_rooms
								  LEFT JOIN booking_rooms ON all_rooms.lot_id = booking_rooms.lot_id
								  LEFT JOIN book_hist ON booking_rooms.reservation_id = book_hist.reservation_id
								  WHERE arrive_date is NULL
								  OR CURRENT_DATE not between arrive_date and depart_date
								  OR book_hist.guest_status = 'Cancelled'");

	while($row = mysqli_fetch_assoc($result))
	{
		$array = $row;
	}	
	$room_remaining = $array['room_remaining'];
	
	$result = mysqli_query($con, "SELECT COUNT(DISTINCT all_rooms.lot_id) AS num_kings
								  FROM all_rooms
								  LEFT JOIN booking_rooms ON all_rooms.lot_id = booking_rooms.lot_id
								  LEFT JOIN book_hist ON booking_rooms.reservation_id = book_hist.reservation_id
								  WHERE (arrive_date is NULL
								  OR CURRENT_DATE not between arrive_date and depart_date
								  OR book_hist.guest_status = 'Cancelled')
								  AND room_code = 'KING'");
								  
	while($row = mysqli_fetch_assoc($result))
	{
		$array = $row;
	}
	$num_kings = $array['num_kings'];
	
	$result = mysqli_query($con, "SELECT COUNT(DISTINCT all_rooms.lot_id) AS num_doubles
								  FROM all_rooms
								  LEFT JOIN booking_rooms ON all_rooms.lot_id = booking_rooms.lot_id
								  LEFT JOIN book_hist ON booking_rooms.reservation_id = book_hist.reservation_id
								  WHERE (arrive_date is NULL
								  OR CURRENT_DATE not between arrive_date and depart_date
								  OR book_hist.guest_status = 'Cancelled')
								  AND room_code = 'DBLE'");
								  
	while($row = mysqli_fetch_assoc($result))
	{
		$array = $row;
	}
	$num_doubles = $array['num_doubles'];
	
	$result = mysqli_query($con, "SELECT COUNT(DISTINCT all_rooms.lot_id) AS num_kings_couch
								  FROM all_rooms
								  LEFT JOIN booking_rooms ON all_rooms.lot_id = booking_rooms.lot_id
								  LEFT JOIN book_hist ON booking_rooms.reservation_id = book_hist.reservation_id
								  WHERE (arrive_date is NULL
								  OR CURRENT_DATE not between arrive_date and depart_date
								  OR book_hist.guest_status = 'Cancelled')
								  AND room_code = 'KCOU'");
								  
	while($row = mysqli_fetch_assoc($result))
	{
		$array = $row;
	}
	$num_kings_couch = $array['num_kings_couch'];
	
	$result = mysqli_query($con, "SELECT COUNT(DISTINCT all_rooms.lot_id) AS num_kings_whirlpool
								  FROM all_rooms
								  LEFT JOIN booking_rooms ON all_rooms.lot_id = booking_rooms.lot_id
								  LEFT JOIN book_hist ON booking_rooms.reservation_id = book_hist.reservation_id
								  WHERE (arrive_date is NULL
								  OR CURRENT_DATE not between arrive_date and depart_date
								  OR book_hist.guest_status = 'Cancelled')
								  AND room_code = 'KWHI'");
								  
	while($row = mysqli_fetch_assoc($result))
	{
		$array = $row;
	}
	$num_kings_whirlpool = $array['num_kings_whirlpool'];
	
	$result = mysqli_query($con, "SELECT COUNT(DISTINCT all_rooms.lot_id) AS num_kings_handicapped
								  FROM all_rooms
								  LEFT JOIN booking_rooms ON all_rooms.lot_id = booking_rooms.lot_id
								  LEFT JOIN book_hist ON booking_rooms.reservation_id = book_hist.reservation_id
								  WHERE (arrive_date is NULL
								  OR CURRENT_DATE not between arrive_date and depart_date
								  OR book_hist.guest_status = 'Cancelled')
								  AND room_code = 'KHAN'");
								  
	while($row = mysqli_fetch_assoc($result))
	{
		$array = $row;
	}
	$num_kings_handicapped = $array['num_kings_handicapped'];
	
	$result = mysqli_query($con, "SELECT COUNT( DISTINCT book_hist.reservation_id ) AS num_arrivals
								  FROM all_rooms
								  LEFT JOIN booking_rooms ON all_rooms.lot_id = booking_rooms.lot_id
								  LEFT JOIN book_hist ON booking_rooms.reservation_id = book_hist.reservation_id
								  LEFT JOIN hotel_guest ON book_hist.guest_id = hotel_guest.guest_id
								  WHERE arrive_date = CURRENT_DATE");
								  
	while($row = mysqli_fetch_assoc($result))
	{
		$array = $row;
	}
	$num_arrivals = $array['num_arrivals'];		
	
	$result = mysqli_query($con, "SELECT COUNT( DISTINCT book_hist.reservation_id ) AS num_departures
								  FROM all_rooms
								  LEFT JOIN booking_rooms ON all_rooms.lot_id = booking_rooms.lot_id
								  LEFT JOIN book_hist ON booking_rooms.reservation_id = book_hist.reservation_id
								  LEFT JOIN hotel_guest ON book_hist.guest_id = hotel_guest.guest_id
								  WHERE depart_date = CURRENT_DATE");
								  
	while($row = mysqli_fetch_assoc($result))
	{
		$array = $row;
	}
	$num_departures = $array['num_departures'];
	
	print json_encode(array(
		'num_departures' => $num_departures,
		'num_arrivals' => $num_arrivals,
		'room_remaining' => $room_remaining,
		'num_doubles' => $num_doubles,
		'num_kings' => $num_kings,
		'num_kings_couch' => $num_kings_couch,
		'num_kings_whirlpool' => $num_kings_whirlpool,
		'num_king_handicapped' => $num_kings_handicapped));
	
	
}

//Folio search
if( $method == "FolioSearch" )
{
	$reservation_id = $obj -> reservation_id;
	$array = array();
	
	$result = mysqli_query($con, "SELECT credit_card.exp_date_year, credit_card.exp_date_month, hotel_guest.last_name, hotel_guest.first_name, hotel_guest.home_addr, hotel_guest.city, hotel_guest.state, hotel_guest.zip_code,
								  hotel_guest.home_phone, hotel_guest.corporation, hotel_guest.email, all_rooms.lot_id, book_hist.book_price,book_hist.children,
								  book_hist.adults, book_hist.rollaways, credit_card.card_number, credit_card.card_type, room_info.description, book_hist.arrive_date, book_hist.depart_date, book_hist.rate_type, book_hist.guest_status
								  FROM all_rooms
								  LEFT JOIN booking_rooms ON all_rooms.lot_id = booking_rooms.lot_id
								  LEFT JOIN book_hist ON booking_rooms.reservation_id = book_hist.reservation_id
								  LEFT JOIN hotel_guest ON book_hist.guest_id = hotel_guest.guest_id
								  LEFT JOIN credit_card ON book_hist.reservation_id = credit_card.reservation_id
								  LEFT JOIN room_info ON all_rooms.room_code = room_info.room_code
								  WHERE book_hist.reservation_id = '$reservation_id'");
	
	while($row = mysqli_fetch_assoc($result))
	{
		$array[] = $row;
	}
	print json_encode($array);
}

//MainSearchSpecific

if( $method == "MainSearchSpecific" )
{
	$searchFor = $obj -> searchFor;
	$searchBy = $obj -> searchBy;
	$array = array();
	
	if( $searchFor == "first_name" )
	{
		$result = mysqli_query($con, "SELECT last_name, first_name, all_rooms.lot_id, arrive_date, depart_date, book_hist.reservation_id, home_phone, email, corporation, book_hist.guest_status 
									  FROM all_rooms
									  LEFT JOIN booking_rooms ON all_rooms.lot_id = booking_rooms.lot_id
									  LEFT JOIN book_hist ON booking_rooms.reservation_id = book_hist.reservation_id
									  LEFT JOIN hotel_guest ON book_hist.guest_id = hotel_guest.guest_id
									  WHERE first_name = '$searchBy'");
	
		while($row = mysqli_fetch_assoc($result))
		{
			$array[] = $row;
		}
		print json_encode($array);
	}
	
	if( $searchFor == "last_name" )
	{
		$result = mysqli_query($con, "SELECT last_name, first_name, all_rooms.lot_id, arrive_date, depart_date, book_hist.reservation_id, home_phone, email, corporation, book_hist.guest_status 
									  FROM all_rooms
									  LEFT JOIN booking_rooms ON all_rooms.lot_id = booking_rooms.lot_id
									  LEFT JOIN book_hist ON booking_rooms.reservation_id = book_hist.reservation_id
									  LEFT JOIN hotel_guest ON book_hist.guest_id = hotel_guest.guest_id
									  WHERE last_name = '$searchBy'");
	
		while($row = mysqli_fetch_assoc($result))
		{
			$array[] = $row;
		}
		print json_encode($array);
	}
	
	if( $searchFor == "reservation_id" )
	{
		$result = mysqli_query($con, "SELECT last_name, first_name, all_rooms.lot_id, arrive_date, depart_date, book_hist.reservation_id, home_phone, email, corporation, book_hist.guest_status 
									  FROM all_rooms
									  LEFT JOIN booking_rooms ON all_rooms.lot_id = booking_rooms.lot_id
									  LEFT JOIN book_hist ON booking_rooms.reservation_id = book_hist.reservation_id
									  LEFT JOIN hotel_guest ON book_hist.guest_id = hotel_guest.guest_id
									  WHERE reservation_id = '$searchBy'");
	
		while($row = mysqli_fetch_assoc($result))
		{
			$array[] = $row;
		}
		print json_encode($array);
	}	

	if( $searchFor == "lot_id" )
	{
		$result = mysqli_query($con, "SELECT last_name, first_name, all_rooms.lot_id, arrive_date, depart_date, book_hist.reservation_id, home_phone, email, corporation, book_hist.guest_status 
									  FROM all_rooms
									  LEFT JOIN booking_rooms ON all_rooms.lot_id = booking_rooms.lot_id
									  LEFT JOIN book_hist ON booking_rooms.reservation_id = book_hist.reservation_id
									  LEFT JOIN hotel_guest ON book_hist.guest_id = hotel_guest.guest_id
									  WHERE all_rooms.lot_id = '$searchBy'");
	
		while($row = mysqli_fetch_assoc($result))
		{
			$array[] = $row;
		}
		print json_encode($array);
	}

	if( $searchFor == "card_number" )
	{
		$result = mysqli_query($con, "SELECT last_name, first_name, all_rooms.lot_id, arrive_date, depart_date, book_hist.reservation_id, home_phone, email, corporation, book_hist.guest_status									  
									  FROM all_rooms									  
									  LEFT JOIN booking_rooms ON all_rooms.lot_id = booking_rooms.lot_id
									  LEFT JOIN book_hist ON booking_rooms.reservation_id = book_hist.reservation_id									  
									  LEFT JOIN hotel_guest ON book_hist.guest_id = hotel_guest.guest_id
									  LEFT JOIN credit_card ON book_hist.reservation_id = credit_card.reservation_id									  
									  WHERE card_number = '$searchBy'");
	
		while($row = mysqli_fetch_assoc($result))
		{
			$array[] = $row;
		}
		print json_encode($array);
	}	
}
//Folio update/insert

if ( $method == "FolioUpdate" )
{
	$reservation_id = $obj -> reservation_id;

	$first_name = $obj -> first_name;
	$last_name = $obj -> last_name;
	$home_addr = $obj -> home_addr;
	$apt_no = $obj -> apt_no;
	$city = $obj -> city;
	$state = $obj -> state;
	$zip_code = $obj -> zip_code;
	$home_phone = $obj -> home_phone;
	$email = $obj -> email;
	$corporation = $obj -> corporation;
	
	$arrive_date = $obj -> arrive_date;
	$depart_date = $obj -> depart_date;
	$rate_type = $obj -> rate_type;
	$book_price = $obj -> book_price;
	$adults = $obj -> adults;
	$children = $obj -> children;
	$rollaways = $obj -> rollaways;
	
	
	$lot_id = $obj -> lot_id;
	
	$card_number = $obj -> card_number;
	$card_type = $obj -> card_type;
	$exp_date_year = $obj -> exp_date_year;
	$exp_date_month = $obj -> exp_date_month;
	
	if( $reservation_id == 0 )
	{

		mysqli_query($con, "INSERT INTO hotel_guest (first_name, last_name, home_addr, apt_no, city, state, zip_code, home_phone, email, corporation) 
							VALUES ('$first_name', '$last_name', '$home_addr', '$apt_no', '$city', '$state', '$zip_code', '$home_phone', '$email', '$corporation')");
							
		$guest_id = mysqli_insert_id($con);

		
		mysqli_query($con, "INSERT INTO book_hist (	guest_id, book_date, arrive_date, depart_date, rate_type, book_price, adults, children, rollaways)	
							VALUES ('$guest_id', CURRENT_TIMESTAMP, '$arrive_date', '$depart_date', '$rate_type', '$book_price', '$adults', '$children', '$rollaways')");
							
		$new_reservation_id = mysqli_insert_id($con);

		
		mysqli_query($con, "INSERT INTO booking_rooms (reservation_id, lot_id)
							VALUES ('$new_reservation_id', '$lot_id')");
							
							
		mysqli_query($con, "INSERT INTO credit_card ( reservation_id, card_number, card_type, exp_date_year, exp_date_month )
							VALUES ( '$new_reservation_id', '$card_number', '$card_type', '$exp_date_year', '$exp_date_month' )");
				
	}
	
	else //( $reservation_id != 0 )
	{
		$arrray = array();

		
		$result = mysqli_query($con, "SELECT book_hist.guest_id
							FROM book_hist
							WHERE book_hist.reservation_id = '$reservation_id'");
		
		while($row = mysqli_fetch_assoc($result))
		{
			$array = $row;
		}
		
		$guest_id = $array['guest_id'];

		
		mysqli_query($con, "UPDATE hotel_guest 
							SET first_name = '$first_name', last_name = '$last_name', home_addr = '$home_addr', apt_no = '$apt_no', city = '$city', state = '$state', zip_code = '$zip_code', home_phone = '$home_phone', email = '$email', corporation = '$corporation'
							WHERE hotel_guest.guest_id = '$guest_id'");
							
		
		
		mysqli_query($con, "UPDATE book_hist 
							SET arrive_date = '$arrive_date', depart_date = '$depart_date', rate_type = '$rate_type', book_price = '$book_price', adults = '$adults', children = '$children', rollaways = '$rollaways'	
							WHERE book_hist.reservation_id = '$reservation_id'");
							
		
		mysqli_query($con, "INSERT INTO booking_rooms (reservation_id, lot_id)
							VALUES ('$new_reservation_id', '$lot_id')");
							
						
		mysqli_query($con, "UPDATE credit_card 
							SET  card_number = '$card_number', card_type = '$card_type', exp_date_year = '$exp_date_year', exp_date_month = '$exp_date_month'
							WHERE credit_card.reservation_id = '$reservation_id'");
	}

}

//assign room method
if( $method == RoomNumbers )
{
	$array = array();
	$arrive_date = $obj -> arrive_date;
	$depart_date = $obj -> depart_date;
	$room_type = $obj -> searchString;
	

	
	$result = mysqli_query($con,"SELECT DISTINCT all_rooms.lot_id
					   FROM all_rooms
					   LEFT JOIN booking_rooms ON all_rooms.lot_id = booking_rooms.lot_id
					   LEFT JOIN book_hist ON booking_rooms.reservation_id = book_hist.reservation_id
					   WHERE (arrive_date IS NULL
					   OR ('$arrive_date' not between arrive_date and depart_date and '$depart_date' not between arrive_date and depart_date)
					   AND (arrive_date not between '$arrive_date' and '$depart_date' and depart_date not between '$arrive_date' and '$depart_date')
					   OR book_hist.guest_status = 'Cancelled')
					   AND room_code = '$room_type'");	
	while($row = mysqli_fetch_assoc($result))
		{
			$array[] = $row;
		}
		print json_encode($array);

}

//checkIn, checkOut, and cancel methods

if( $method == "checkIn" )
{
	$reservation_id = $obj -> reservation_id;
	$array = array();
	
	$result = mysqli_query($con,"SELECT guest_status
								FROM book_hist
								WHERE reservation_id = '$reservation_id'");
								
	while($row = mysqli_fetch_assoc($result))
		{
			$array = $row;
		}
		
	$guest_status = $array['guest_status'];	
	
	if( $guest_status == "Reserved" )
	{
	mysqli_query($con, "UPDATE book_hist
						SET guest_status = 'In House', check_in = CURRENT_TIMESTAMP
						WHERE book_hist.reservation_id = '$reservation_id'");	
	}
	
	if( $guest_status == "In House" )
	{
	mysqli_query($con, "UPDATE book_hist
						SET guest_status = 'Checked Out', check_out = CURRENT_TIMESTAMP
						WHERE book_hist.reservation_id = '$reservation_id'");
	}
}

if( $method == "cancel" )
{
	$reservation_id = $obj -> reservation_id;
	mysqli_query($con, "UPDATE book_hist
						SET guest_status = 'Cancelled'
						WHERE book_hist.reservation_id = '$reservation_id'");
}

?>