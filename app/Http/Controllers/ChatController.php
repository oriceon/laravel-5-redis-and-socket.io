<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Request;
use L5Redis;
 
class ChatController extends Controller {

	public function __construct()
	{
		$this->middleware('auth');
	}

	public function index()
	{
		return view('home');
	}

	public function systemMessage()
	{
        $redis = L5Redis::connection();

        $redis->publish('chat.message', json_encode([
            'msg'      => 'System message',
            'nickname' => 'System',
            'system'   => true,
        ]));
	}

}