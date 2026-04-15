<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TransferRequest;
use App\Models\Wallet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
{
    /**
     * GET /api/contacts
     * Ambil semua kontak = user yang pernah kirim/terima transfer
     * + tandai mana yang difavoritkan user ini
     */
    public function index(Request $request)
    {
        $user   = $request->user();
        $wallet = $user->wallet;

        if (!$wallet) {
            return response()->json(['data' => [], 'favorites' => []]);
        }

        // Ambil semua wallet_number yang pernah dikirimi oleh user ini
        $sentTo = TransferRequest::where('sender_wallet_id', $wallet->id)
            ->pluck('receiver_wallet_id')
            ->unique();

        // Ambil semua wallet_number yang pernah mengirim ke user ini
        $receivedFrom = TransferRequest::where('receiver_wallet_id', $wallet->id)
            ->pluck('sender_wallet_id')
            ->unique();

        // Gabung, hapus duplikat, hapus wallet sendiri
        $contactWalletIds = $sentTo->merge($receivedFrom)
            ->unique()
            ->filter(fn($id) => $id !== $wallet->id)
            ->values();

        // Ambil data wallet + user
        $contacts = Wallet::with('user')
            ->whereIn('id', $contactWalletIds)
            ->get()
            ->map(function ($w) use ($user) {
                $avatarUrl = null;
                if ($w->user?->avatar) {
                    $avatar = $w->user->avatar;
                    $avatarUrl = str_starts_with($avatar, 'http')
                        ? $avatar
                        : asset('storage/' . $avatar);
                }
                return [
                    'wallet_number' => $w->wallet_number,
                    'wallet_name'   => $w->wallet_name ?? $w->wallet_number,
                    'owner_name'    => $w->user?->name ?? 'Unknown',
                    'owner_avatar'  => $avatarUrl,
                    'is_favorite'   => in_array($w->wallet_number,
                        $user->favorite_contacts ?? []),
                ];
            })
            ->sortBy('owner_name')
            ->values();

        return response()->json(['data' => $contacts]);
    }

    /**
     * POST /api/contacts/{wallet_number}/favorite
     * Toggle favorit
     */
    public function toggleFavorite(Request $request, $walletNumber)
    {
        $user      = $request->user();
        $favorites = $user->favorite_contacts ?? [];

        if (in_array($walletNumber, $favorites)) {
            $favorites = array_values(array_filter(
                $favorites, fn($w) => $w !== $walletNumber
            ));
            $isFavorite = false;
        } else {
            $favorites[] = $walletNumber;
            $isFavorite  = true;
        }

        $user->favorite_contacts = $favorites;
        $user->save();

        return response()->json([
            'message'     => $isFavorite ? 'Ditambahkan ke favorit' : 'Dihapus dari favorit',
            'is_favorite' => $isFavorite,
        ]);
    }
}