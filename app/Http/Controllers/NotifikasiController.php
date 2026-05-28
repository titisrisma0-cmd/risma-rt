<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notifikasi;
use Illuminate\Support\Facades\Auth;

class NotifikasiController extends Controller
{
    public function index()
    {
        $notifikasis = Notifikasi::where('user_id', Auth::id())->orderBy('created_at', 'desc')->get();
        return view('notifikasi.index', compact('notifikasis'));
    }

    public function markRead($id)
    {
        $n = Notifikasi::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $n->dibaca = true;
        $n->save();
        return redirect()->back()->with('success', 'Notifikasi ditandai dibaca');
    }

    public function markAllRead()
    {
        Notifikasi::where('user_id', Auth::id())
            ->where('dibaca', false)
            ->update(['dibaca' => true]);

        return redirect()->back()->with('success', 'Semua notifikasi telah ditandai dibaca');
    }
}
