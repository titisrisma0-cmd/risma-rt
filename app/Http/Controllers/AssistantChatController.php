<?php

namespace App\Http\Controllers;

use App\Services\AssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssistantChatController extends Controller
{
    protected AssistantService $service;

    public function __construct(AssistantService $service)
    {
        $this->service = $service;
    }

    /**
     * Endpoint inisialisasi chat asisten
     * GET /api/assistant/init
     */
    public function init(): JsonResponse
    {
        $user = auth()->user();
        if ($user) {
            $user->loadMissing(['profile.wilayahRt', 'role']);
            $firstName = AssistantService::getFirstName(optional($user->profile)->nama_lengkap ?? $user->name);
            $greeting = "Halo, {$firstName}! Saya Asisten Smart RW. Ada yang bisa saya bantu?";
            $roleLabel = AssistantService::getRoleLabel($user);
            $isLoggedIn = true;
        } else {
            $firstName = null;
            $greeting = "Halo! Saya Asisten Smart RW. Masuk dulu supaya saya bisa membantu lebih personal.";
            $roleLabel = 'Tamu';
            $isLoggedIn = false;
        }

        return response()->json([
            'greeting' => $greeting,
            'is_logged_in' => $isLoggedIn,
            'first_name' => $firstName,
            'user_role' => $roleLabel,
            'login_url' => route('login'),
            'disclaimer' => AssistantService::DISCLAIMER,
            'quick_suggestions' => $this->service->getQuickSuggestions($user),
            'whatsapp_url' => AssistantService::getWhatsAppUrl(),
        ]);
    }

    /**
     * Endpoint pengiriman pesan ke asisten
     * POST /api/assistant/chat
     * Dibatasi maksimal 20 pesan per jam per IP
     */
    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:500',
        ], [
            'message.required' => 'Pesan tidak boleh kosong.',
            'message.max' => 'Pesan maksimal 500 karakter.',
        ]);

        $ip = $request->ip();
        $userId = auth()->id();
        $message = (string) $request->input('message');

        $result = $this->service->ask($message, $ip, $userId);

        return response()->json($result);
    }
}
