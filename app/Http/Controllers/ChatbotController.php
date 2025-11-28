<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ChatbotService;
use Illuminate\Support\Facades\Auth;

class ChatbotController extends Controller
{
    protected $chatbot;

    public function __construct(ChatbotService $chatbot)
    {
        $this->chatbot = $chatbot;
    }

    public function message(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $user = Auth::user();
        $response = $this->chatbot->processMessage($user, $request->input('message'));

        return response()->json(['response' => $response]);
    }
}
