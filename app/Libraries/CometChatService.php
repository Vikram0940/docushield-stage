<?php namespace App\Libraries;

class CometChatService
{
    protected $client;
    protected $appId;
    protected $region;
    protected $apiKey;

    public function __construct()
    {
        $this->appId  = env('COMETCHAT_APP_ID');
        $this->region = env('COMETCHAT_REGION');
        $this->apiKey = env('COMETCHAT_API_KEY');

        $this->client = \Config\Services::curlrequest([
            'baseURI' => "https://{$this->appId}.api-{$this->region}.cometchat.io/v3/",
            'headers' => [
                'appId'       => $this->appId,
                'apiKey'      => $this->apiKey,
                'Content-Type'=> 'application/json',
                'Accept'      => 'application/json',
            ],
        ]);
    }

    public function createUser($email, $name) {
        $uid = date("Ymd").time();
        $response = $this->client->post('users', [
            'json' => [
                "uid"      => $uid,
                "name"     => $name,
                "metadata" => [
                    "@private" => [
                        "email" => $email
                    ]
                ],
                "withAuthToken" => true
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    public function getUser($uid)
    {
        $response = $this->client->get("users/{$uid}");
        return json_decode($response->getBody(), true);
    }
}