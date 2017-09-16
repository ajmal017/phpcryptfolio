<?php class DBAXX {
	public function db_connect() {
	    // Define connection as a static variable, to avoid connecting more than once
	    static $connection;
	    // Try and connect to the database, if a connection has not been established yet
	    if(!isset($connection)) {
       	    // Load configuration as an array. Use the actual location of your configuration file
    	    $config = parse_ini_file(__DIR__ . '/config.ini');
    	    $connection = mysqli_connect('localhost',$config['username'],$config['password'],$config['dbname']);
   	 }
	    // If connection was not successful, handle the error
	    if($connection === false) {
	        print "connection failed";
	        // Handle error - notify administrator, log to a file, show an error screen, etc.
	        return mysqli_connect_error();
	    }
	    return $connection;
	}
	private function db_error() {
	    $connection = $this->db_connect();
	    print "failure";
	    return mysqli_error($connection);
	}
	public function db_query($query) {
	    // Connect to the database
	    $connection = $this->db_connect();
	    // Query the database
	    $result = mysqli_query($connection,$query);
	    return $result;
	}
	public function db_select($query) {
	    $rows = array();
	    $result = $this->db_query($query);
	    // If query failed, return `false`
	    if($result === false) {
	        print "fail";
	        return false;
	    }
	    // If query was successful, retrieve all the rows into an array
	    while ($row = mysqli_fetch_assoc($result)) {
	        $rows[] = $row;
	    }
	    return $rows;
	}
	public function db_insert($query) {
	    $result = $this->db_query($query);
	    // If query failed, return `false`
	    if($result === false) {
		print "fail";
		return false;
	    }
	}
}
?>
