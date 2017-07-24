<?php

namespace Shahrukh\Instagram;

class Instagram
{
    /**
     * @var string
     */
    private static $baseUrl = 'https://api.instagram.com/v1/';

    /**
     * @var string
     */
    private static $authUrl = 'https://api.instagram.com/oauth/authorize/';

    /**
     * Instagram constructor.
     */
    public function __construct()
    {
        if (session('instagram_access_token')) {
            $this->accessToken = session('instagram_access_token');
        }
    }

    /**
     * @return string
     * @throws InvalidCredentialsException
     */
    public static function auth($scopes = [])
    {
        $clientId    = env('INSTAGRAM_CLIENT_ID');
        $redirectUri = env('INSTAGRAM_REDIRECT_URI');
        if (!$clientId) {
            throw new InvalidCredentialsException('Client id not found');
        }

        if(!$redirectUri) {
            throw new InvalidCredentialsException('Redirect URI not found');
        }
        $url = self::$authUrl. "?client_id=$clientId&redirect_uri=$redirectUri&response_type=code";
        if($scopes)
        {
            $url = $url.'&scope='. implode('+', $scopes);
        }
        return $url;
    }


    /**
     * @param $accessToken
     * @throws TokenNotFoundException
     * @return mixed
     */
    public static function getSelf($accessToken)
    {
        if(!$accessToken) {
            throw new \TokenNotFoundException('Please provide a valid access token');
        }

        $endPoint = 'users/self/';
        $url      = self::$baseUrl . $endPoint . '?access_token=' . $accessToken;
        return self::getRequest($url);
    }

    public static function getSelfMedia()
    {
        $endPoint = 'users/self/media/recent?access_token='.self::accessToken;
        $url = self::$baseUrl.$endPoint;
        return self::getRequest($url);
    }

    /**
     * @param $code
     * @return mixed
     */
    public static function exchangeCodeForToken($code)
    {
        $client   = new \GuzzleHttp\Client();
        $response = $client->request('POST', 'https://api.instagram.com/oauth/access_token', [
            'form_params' => [
                'client_id' => env('INSTAGRAM_CLIENT_ID'),
                'client_secret' => env('INSTAGRAM_CLIENT_SECRET'),
                'grant_type' => 'authorization_code',
                'redirect_uri' => 'http://twitter.dev/instagram-callback',
                'code' => $code
            ]
        ]);
        return json_decode($response->getBody());
    }

    /**
     * Get the list of users this user is followed by.
     * scope: follower_list
     * @param $accessToken
     * @param string $user
     * @return \Psr\Http\Message\StreamInterface
     */
    public static function getFollowedBy($accessToken, $user = 'self')
    {
        $endPoint = "users/$user/followed-by?access_token=$accessToken";
        $url      = self::$baseUrl . $endPoint;
        return self::getRequest($url);
    }

    /**
     * Get the list of users this user follows.
     * scope: follower_list
     * @param $accessToken
     * @param string $user
     * @return \Psr\Http\Message\StreamInterface
     */
    public static function getFollows($accessToken, $user = 'self')
    {
        $endPoint = "users/$user/follows?access_token=$accessToken";
        $url      = self::$baseUrl . $endPoint;
        return self::getRequest($url);
    }

    /**
     * List the users who have requested this user's permission to follow.
     * scope: follower_list
     * @param $accessToken
     * @param string $user
     * @return \Psr\Http\Message\StreamInterface
     */
    public static function getFollowRequest($accessToken, $user = 'self')
    {
        $endPoint = "users/$user/requested-by?access_token=$accessToken";
        $url      = self::$baseUrl . $endPoint;
        return self::getRequest($url);
    }

    /**
     * Get information about a relationship to another user.
     * Relationships are expressed using the following terms in the response:
     * outgoing_status: Your relationship to the user. Can be 'follows', 'requested', 'none'.
     * incoming_status: A user's relationship to you. Can be 'followed_by', 'requested_by', 'blocked_by_you', 'none'.
     * scope: follower_list
     * @param $accessToken
     * @param $userId
     * @return \Psr\Http\Message\StreamInterface
     */
    public static function getRelationship($accessToken, $userId)
    {
        $endPoint = "users/$userId/relationship?access_token=$accessToken";
        $url      = self::$baseUrl . $endPoint;
        return self::getRequest($url);
    }

    /**
     * Modify the relationship between the current user and the target user.
     * You need to include an action parameter to specify the relationship action you want to perform.
     * Valid actions are: 'follow', 'unfollow' 'approve' or 'ignore'.
     * Relationships are expressed using the following terms in the response:
     * outgoing_status: Your relationship to the user. Can be 'follows', 'requested', 'none'.
     * incoming_status: A user's relationship to you. Can be 'followed_by', 'requested_by', 'blocked_by_you', 'none'.
     * scope: relationships
     * @param $accessToken
     * @param string $action
     * @param $userId
     * @return \Psr\Http\Message\StreamInterface
     */
    public static function changeRelationship($accessToken, $action = 'follow', $userId)
    {
        $endPoint = "users/$userId/relationship?access_token=$accessToken";
        $url = self::$baseUrl.$endPoint;
        $params = ['action' => $action];

        return self::postRequest($url, $params);
    }

    /**
     * Get information about a media object.
     * Use the type field to differentiate between image and video media in the response.
     * You will also receive the user_has_liked field which tells you whether the owner of the
     * access_token has liked this media.
     * scope: public_content
     * @param $accessToken
     * @param $mediaId
     * @return \Psr\Http\Message\StreamInterface
     */
    public static function getMedia($accessToken, $mediaId)
    {
        $endPoint = "media/$mediaId?access_token=$accessToken";
        $url = self::$baseUrl. $endPoint;

        return self::getRequest($url);
    }

    /**
     * Search for recent media in a given area.
     * scope: public_content
     * @param $accessToken
     * @param $area
     * @param $distance
     * @return \Psr\Http\Message\StreamInterface
     */
    public static function searchMedia($accessToken, $area, $distance = '1000')
    {
        $coordinates = self::getAreaCordinates($area);
        $endPoint = "media/search?lat=$coordinates->lat&lng=$coordinates->lng&distance=$distance&access_token=$accessToken";
        $url = self::$baseUrl.$endPoint;
        return self::getRequest($url);
    }

    /**
     * @param $url
     * @return \Psr\Http\Message\StreamInterface
     */
    public static function getRequest($url)
    {
        $client     = new \GuzzleHttp\Client();
        $res        = $client->request('GET', $url);
        $statusCode = $res->getStatusCode();
        $headers    = $res->getHeaderLine('content-type');
        $response   = $res->getBody();

        return json_decode($response);
    }

    /**
     * @param $url
     * @param array $params
     * @return \Psr\Http\Message\StreamInterface
     */
    public static function postRequest($url, $params = [])
    {
        $client = new \GuzzleHttp\Client();
        $res = $client->request('POST', $url, $params);
        $statusCode = $res->getStatusCode();
        $headers = $res->getHeaderLine('content-type');
        $response = $res->getBody();

        return json_decode($response);
    }

    public static function getAreaCordinates($area)
    {
        $coordinates = new \stdClass();
        $googleMapsKey = env('GOOGLE_MAPS_KEY');
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address=$area&key=$googleMapsKey";
        $response = self::getRequest($url);
        $coordinates->lat = $response->results->gemoetry->location->lat;
        $coordinates->lng = $response->results->gemoetry->location->lng;

        return $coordinates;
    }
}
