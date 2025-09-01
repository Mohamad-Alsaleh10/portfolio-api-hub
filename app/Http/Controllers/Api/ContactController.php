<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ContactMessage;
use Illuminate\Validation\ValidationException;

class ContactController extends Controller
{
    /**
     * Store a new contact message.
     * تخزين رسالة اتصال جديدة.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitMessage(Request $request)
    {
        $request->validate([
            'name' => 'nullable|string|max:255', // الاسم يمكن أن يكون اختيارياً
            'email' => 'required|string|email|max:255', // البريد الإلكتروني مطلوب
            'message' => 'required|string|max:2000', // نص الرسالة مطلوب
        ]);

        ContactMessage::create([
            'name' => $request->name,
            'email' => $request->email,
            'message' => $request->message,
        ]);

        return response()->json(['message' => 'Your message has been sent successfully!'], 201);
    }
}
