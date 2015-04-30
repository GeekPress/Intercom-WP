<?php
/**
 * Intercom is a customer relationship management and messaging tool for web app owners
 * This library provides connectivity with the Intercom API (http://doc.intercom.io/api/)
 *
 * @author    Jonathan Buttigieg <jonathan@wp-rocket.me>
 * @link      http://wp-rocket.me
 * @license   http://opensource.org/licenses/MIT
 **/

/**
 * Create a user on your Intercom account.
 *
 * @param  array $data All user data. Details here: http://doc.intercom.io/api/#create-or-update-user
 * @return object
 **/
function add_intercom_user( $data ) {
	return Intercom()->createUser( $data );
}

/**
 * Delete an existing user from your Intercom account
 * A user can be fetched using a user_id or email
 *
 * @param  string $id The ID of the user to retrieve (user_id or email)
 * @return object
 **/
function delete_intercom_user( $id ) {
	return Intercom()->deleteUser( $id );
}

/**
 * Update an existing user on your Intercom account.
 *
 * @param  string $data All user data. Details here: http://doc.intercom.io/api/#create-or-update-user
 * @return object
 **/
function update_intercom_user( $data ) {
	return add_intercom_user( $data );
}

/**
 * Create a conversation on your Intercom account.
 *
 * @param string $message_type	The kind of message being created. Values: inapp or email.
 * @param string $subject		Optional unless message_type is email. The title of the email.
 * @param string $body			The content of the message.
 * @param string $template		The style of the outgoing message. Only valid for email messages.
 								Possible values plain or personal.
 * @param array  $from			An admin object containing the admin's id.
 								The type field must have a value of admin.
 * @param array  $to			A user object containing the user’s id, email or user_id.
 								The type field must have a value of user.
 * @return object
 */
function add_intercom_conversation( $message_type, $subject, $body, $template, $from, $to ) {
	$data                 = array();
	$data['message_type'] = $message_type;
	$data['subject']      = $subject;
	$data['body']         = $body;
	$data['template']     = $template;
	$data['from']         = $from;
	$data['to']           = $to;

	return Intercom()->createConversation( $data );
}

/**
 * Create an email conversation initiated by admin on your Intercom account.
 *
 * @param string $subject		Optional unless message_type is email. The title of the email.
 * @param string $body			The content of the message.
 * @param string $template		The style of the outgoing message. Only valid for email messages.
 * @param string $user_id		The user id. A user can be fetched using a id, user_id or email
 * @param string $admin_id		The admin id.
 * @return object
 */
function add_intercom_email_conversation_by_admin( $subject, $body, $template, $user_id, $admin_id ) {
	$data                 = array();
	$data['message_type'] = 'email';
	$data['subject']      = $subject;
	$data['body']         = $body;
	$data['template']     = $template;
	$data['from']         = array(
		'type' => 'admin',
		'id'   => $admin_id
	);
	$data['to'] 		  = array(
		'type' 		=> 'user',
		'user_id'   => $user_id
	);

	return Intercom()->createConversation( $data );
}

/**
 * Create an event associated with a user on your Intercom account.
 *
 * @param int 		$user_id	Your identifier for the the user.
 * @param string 	$event_name	The name of the event that occurred.
 * @param array 	$metadata	Optional metadata about the event.
 * @param long 		$created_at	The time the event occurred as a UTC Unix timestamp.
 * @return obj
 */
function add_intercom_event( $user_id, $event_name, $metadata = array(), $created_at = null ) {
	$data               = array();
	$data['user_id']    = $user_id;
	$data['event_name'] = $event_name;
	$data['metadata']   = $metadata;
	$data['created_at'] = ( $created_at ) ? $created_at : time();

	return Intercom()->createEvent( $data );
}

/**
 * Get all admins from your Intercom account.
 *
 * @return object
 **/
function get_intercom_admins() {
	return Intercom()->getAdmins()->admins;
}

/**
 * Get a specific user from your Intercom account.
 * A user can be fetched using a user_id or email
 *
 * @param  string $id The ID of the user to retrieve (user_id or email)
 * @return object
 **/
function get_intercom_user( $id ) {
	return Intercom()->getUser( $id );
}

/**
 * Get all users from your Intercom account.
 *
 * @param  int $page     What page of results to fetch defaults to first page.
 * @param  int $per_page How many results per page defaults to 50.
 * @param  int $order    asc or desc. Return the users in ascending or descending order. Defaults to desc.
 * @return object
 **/
function get_intercom_users( $page = 0, $per_page = null, $order = 'desc' ) {
	return Intercom()->getUsers( $page, $per_page, $order )->users;
}

/**
 * Get a specific segment by its ID from your Intercom account.
 *
 * @param  integer $id The ID of the segment to retrieve.
 * @return object
 **/
function get_intercom_segment( $id ) {
	return Intercom()->getSegment( $id );
}

/**
 * Intercom.io API
 */
class Intercom
{
    /**
     * The Intercom API endpoint
     */
    private $apiEndpoint = 'https://api.intercom.io/';

    /**
     * The Intercom application ID
     */
    private $appId = 'INTERCOM_APP_ID';

    /**
     * The Intercom API key
     */
    private $apiKey = 'INTERCOM_API_KEY';

	/**
     * HTTP headers
     */
    private $headers = array();

    /**
	 * @var The single instance of the class
	 */
	protected static $_instance = null;

    /**
     * The constructor
     *
     * @param  string $appId  The Intercom application ID
     * @param  string $apiKey The Intercom API key
     * @return void
     **/
    public function __construct()
    {
        $this->headers['Accept']  	    = 'application/json';
        $this->headers['Content-Type']  = 'application/json';
        $this->headers['Authorization'] = 'Basic ' . base64_encode( $this->appId . ':' . $this->apiKey );
    }

	/**
	 * Main Intercom Instance
	 *
	 * Ensures only one instance of class is loaded or can be loaded.
	 *
	 * @static
	 * @return Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
     * Get all admins from your Intercom account.
     *
     * @return object
     **/
	public function getAdmins() {
		return self::getData( 'admins/' );
	}

	/**
     * Get a specific user from your Intercom account.
     * A user can be fetched using a user_id or email
     *
     * @param  string $id The ID of the user to retrieve (user_id or email)
     * @return object
     **/
    public function getUser( $id )
    {
        $path = 'users/';
        $path .= ( is_email( $id ) ) ? '?email=' : '?user_id=';
        $path .= urlencode( $id );

		return self::getData( $path );
    }

    /**
     * Get all users from your Intercom account.
     *
     * @param  integer $page    What page of results to fetch defaults to first page.
     * @param  integer $perPage How many results per page defaults to 50.
     * @param  integer $order   asc or desc. Return the users in ascending or descending order. defaults to desc.
     * @return object
     **/
    public function getUsers( $page = 0, $perPage = null, $order = 'desc' )
    {
        $path = 'users/?page=' . (int) $page;

        if ( ! empty( $perPage ) ) {
            $path .= '&per_page=' . (int) $perPage;
        }

		if ( ! empty( $order ) ) {
            $path .= '&order=' . $order;
        }

		return self::getData( $path );
    }

    /**
     * Get a specific segment by its ID from your Intercom account.
     *
     * @param  integer $id The ID of the segment to retrieve.
     * @return object
     **/
    public function getSegment( $id ) {
	    return self::getData( 'segments/' . $id );
    }

    /**
     * Create a user on your Intercom account.
     *
     * @param  array $data All user data. Details here: http://doc.intercom.io/api/#create-or-update-user
     * @return object
     **/
    public function createUser( $data )
    {
        return self::sendData( 'users/', $data, 'POST' );
    }

	/**
     * Delete an existing user from your Intercom account
     * A user can be fetched using a user_id or email
     *
     * @param  string $id The ID of the user to retrieve (user_id or email)
     * @return object
     **/
    public function deleteUser( $id )
    {
		$path = 'users/';
		$path .= ( is_email( $id ) ) ? '?email=' : '?user_id=';
		$path .= urlencode( $id );

		return self::sendData( $path, array(), 'DELETE' );
    }

    /**
     * Update an existing user on your Intercom account.
     *
     * @param  string $data All user data. Details here: http://doc.intercom.io/api/#create-or-update-user
     * @return object
     **/
    public function updateUser( $data )
    {
        return self::createUser( $data );
    }

	/**
     * Create a conversation on your Intercom account.
     *
     * @param  string $data All conversation data. Details here: http://doc.intercom.io/api/#conversations
     * @return object
     **/
	public function createConversation( $data ) {
		return self::sendData( 'messages/', $data, 'POST' );
	}

    /**
     * Create an event associated with a user on your Intercom account
     *
     * @param  string $data All event data. Details here: http://doc.intercom.io/api/#event-model
     * @return object
     **/
    public function createEvent( $data )
    {
		if ( ! isset( $data['created_at'] ) ) {
		    $data['created_at'] = time();
		}

		return self::sendData( 'events/', $data, 'POST' );
    }

	/**
	 * Get data from Intercom.com
	 *
	 * @access private
	 * @param  string $path
	 * @return object
	 */
	private function getData( $path ) {
	    $response = wp_remote_get( $this->apiEndpoint . $path, array( 'headers' => $this->headers ) );

	    return self::getHTTPResponse( $response );
    }

    /**
     * Get HTTP response.
     *
     * @access private
     * @param  array $response
     * @return object
     */
    private function getHTTPResponse( $response ) {
	    if ( is_wp_error( $response ) ) {
		   return $response->get_error_message();
		}

		$response = json_decode( wp_remote_retrieve_body( $response ) );

		if ( isset( $response->errors ) ) {
			return $response->errors;
		}

		return $response;
    }

	/**
	 * Send data to Intercom.com
	 *
	 * @access private
	 * @param  string $path
	 * @return object
	 */
    private function sendData( $path, $data = array(), $method = 'POST' ) {
        $response = wp_remote_post(
        	$this->apiEndpoint . $path,
        	array(
        		'method'  => $method,
        		'headers' => $this->headers,
        		'body'    => json_encode( $data )
        	)
        );

	    return self::getHTTPResponse( $response );
    }
}

/**
 * Returns the main instance of Intercom to prevent the need to use globals.
 */
function Intercom() {
	return Intercom::instance();
}
$GLOBALS['intercom'] = Intercom();