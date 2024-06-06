<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Auth;
use App\Models\Contact;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        // Verifica se há um token na requisição
        $token = $request->input('token') ?? $request->query('token');
        
        if (!$token) {
            return response()->json(['error' => 'Token não fornecido'], 401);
        }

        // Autentica o usuário com base no token
        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken) {
            return response()->json(['error' => 'Token inválido'], 401);
        }

        $user = $accessToken->tokenable;

        // Verificação para garantir que o usuário foi autenticado corretamente
        if (!$user) {
            return response()->json(['error' => 'Usuário não encontrado'], 401);
        }

        // Adicionando log para verificar o usuário autenticado
        Log::info('Usuário autenticado:', ['id' => $user->id, 'name' => $user->name]);

        // Define o usuário autenticado
        Auth::setUser($user);

        // Filtra os contatos com base na consulta e user_id do usuário autenticado
        $query = $request->input('query');
        $contacts = Contact::where('user_id', $user->id)
                            ->where(function ($q) use ($query) {
                                $q->where('name', 'like', "%{$query}%")
                                  ->orWhere('phone', 'like', "%{$query}%")
                                  ->orWhere('address', 'like', "%{$query}%")
                                  ->orWhere('city', 'like', "%{$query}%")
                                  ->orWhere('state', 'like', "%{$query}%");
                            })
                            ->get();

        // Adicionando log para verificar os contatos retornados
        Log::info('Contatos encontrados:', ['contacts' => $contacts]);

        // Retorna os contatos encontrados
        return response()->json($contacts);
    }

    public function showMap(Request $request)
    {

        // Define um token fixo retornado ao fazer login = > via postman para testes
        $token = '7|Fl45EAqKN2QP14l92R4JxGjqRgF5313bp490wumv2f08b66e'; // Defina o token aqui

        // Passa o token para a view
        return view('map', compact('token'));
    }
}