<?php


namespace App\Services;
use GuzzleHttp\Client;

class RetrieveToken
{
public function getToken(){
    $client=new Client();
    $username="CBZ";
    $password="34578901234";
    $authorisation=" Basic YmF6OjEyMzQ1";
    $grant_type="password";
    $response = $client->request('POST', 'https://secure.zimswitch.co.zw/lab/bfis/v1/oauth/token', [
        'headers' => [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Authorization' => $authorisation
        ],
        'form_params' => [
            'username' => $username,
            'password' => $password,
            'grant_type' => $grant_type
        ]
    ])->getBody()->getContents();
    $res=json_decode($response);
    $token=$res->access_token;
    return $token;
}
}
