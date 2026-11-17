<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class NotificacionController extends Controller
{
    // Listado compacto para la campana (ultimas 12 + contador de no leidas)
    public function index(): JsonResponse
    {
        $usuario = Auth::user();

        $todas = $usuario->notifications()
            ->orderBy('created_at', 'desc')
            ->limit(12)
            ->get()
            ->map(fn ($n) => [
                'id'     => $n->id,
                'leida'  => !is_null($n->read_at),
                'titulo' => $n->data['titulo'] ?? '',
                'texto'  => $n->data['texto'] ?? '',
                'icono'  => $n->data['icono'] ?? 'bi-bell',
                'url'    => $n->data['url'] ?? '#',
                'fecha'  => $n->created_at->format('d/m/Y H:i'),
            ])
            ->values();

        return response()->json([
            'unread' => $usuario->unreadNotifications()->count(),
            'items'  => $todas,
        ]);
    }

    // Marcar una notificacion como leida
    public function leer(string $id): JsonResponse
    {
        $notificacion = Auth::user()->notifications()->where('id', $id)->first();

        if ($notificacion && is_null($notificacion->read_at)) {
            $notificacion->markAsRead();
        }

        return response()->json([
            'ok'     => true,
            'unread' => Auth::user()->unreadNotifications()->count(),
        ]);
    }

    // Marcar todas como leidas
    public function leerTodas(): JsonResponse
    {
        Auth::user()->unreadNotifications->markAsRead();

        return response()->json([
            'ok'     => true,
            'unread' => 0,
        ]);
    }
}
