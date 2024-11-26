<?php

namespace bld\ddosspelbord\classes\api\authentication;

use Backend\Classes\Controller;
use bld\ddosspelbord\helpers\hLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Db;
use GuzzleHttp\Client;
use BackendAuth;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $log = [
            'error' => 'Authenticated',
            'code' => 200,
        ];

        $credentials = $request->validate([
                                              'login' => 'required',
                                              'password' => 'required',
                                          ]);

        if (!Auth::once($credentials)) {
            $log = [
                'error' => 'The provided credentials do not match our records.',
                'code' => 401,
            ];
        } else {

            $user = BackendAuth::findUserByLogin($credentials['login']);
            if (!$user) {
                $log = [
                    'error' => 'WinterCMS Couldn\'t find a user with these credentials',
                    'code' => 401,
                ];
                //return response()->json(['error' => 'WinterCMS Couldn\'t find a user with these credentials'], 401);
            } else {

                if (!$user->hasAccess('bld.ddosspelbord.access_api')) {
                    $log = [
                        'error' => 'The provided credentials do not authenticate a user that has the correct API permissions set',
                        'code' => 401,
                    ];
                    //return response()->json(['error' => 'The provided credentials do not authenticate a user that has the correct API permissions set'], 401);
                } else {

                    // All is verified and found we can start with the actual Token request
                    $clientData = $this->getClientData();
                    if (empty($clientData)) {
                        $log = [
                            'error' => 'The server has no clientdata setup for passport oauth2 rest api',
                            'code' => 401,
                        ];
                        //return response()->json(['error' => 'The server has no clientdata setup for passport oauth2 rest api'], 401);
                    } else {

                        $client = new Client();  // Moved up for visibility

                        try {
                            $response = $client->post(url('/oauth/token'), [
                                'form_params' => [
                                    'grant_type' => 'password',
                                    'client_id' => $clientData->id,
                                    'client_secret' => $clientData->secret,
                                    'username' => $user->email,
                                    'password' => $request->password,
                                    'scope' => '',
                                ]
                            ]);

                            hLog::logLine("D-Login successful: API username={$user->email}");
                            return response()->json(json_decode($response->getBody()->getContents(), true), 200);

                        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
                            $log = [
                                'error' => 'Failed to obtain access token',
                                'code' => 500,
                            ];
                            //return response()->json(['error' => 'Failed to obtain access token'], 500);
                        }

                    }

                }

            }

        }

        // when here then error state
        hLog::logLine("E-Login error: ".print_r($log,true));
        return response()->json(['error' => $log['error']], $log['code']);
    }


    public function refreshToken(Request $request)
    {
        $refreshToken = $request->validate([
                                               'refresh_token' => 'required',
                                           ]);

        $clientData = $this->getClientData();
        // Creating a new Guzzle HTTP client instance
        $client = new Client();

        try {
            $response = $client->post(url('/oauth/token'), [
                'form_params' => [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $refreshToken['refresh_token'],
                    'client_id' => $clientData->id,
                    'client_secret' => $clientData->secret,
                    'scope' => '',
                ]
            ]);

            return response()->json(json_decode($response->getBody()->getContents(), true), 200);
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            return response()->json(['error' => 'Failed to refresh access token'], 500);
        }
    }

    private function getClientData(){
        return DB::table('oauth_clients')
            ->where('password_client', true)
            ->first();
    }
}
